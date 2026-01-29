<?php
declare(strict_types=1);

/**
 * CKPN utilities: snapshot > individual > compute (PD × LGD).
 * Dibuat generic: butuh PDO dan callback dayRange('YYYY-MM-DD') -> [start, end].
 */
class CkpnUtils
{
    private PDO $pdo;
    /** @var callable(string): array{0:string,1:string} */
    private $dayRange;

    /**
     * @param callable $dayRangeFn function(string $dateYmd): array{start:string, end:string}
     */
    public function __construct(PDO $pdo, callable $dayRangeFn)
    {
        $this->pdo      = $pdo;
        $this->dayRange = $dayRangeFn;
    }

    /** Ambil LGD global (fallback 59.48 bila tidak ada) */
    public function loadGlobalLGD(string $onDate): float
    {
        try {
            $st = $this->pdo->prepare("
                SELECT lgd_percent FROM lgd_current
                WHERE created <= ? ORDER BY created DESC LIMIT 1
            ");
            $st->execute([$onDate]);
            $v = $st->fetchColumn();
            return ($v!==false) ? (float)$v : 59.48;
        } catch (\PDOException $e) {
            return 59.48;
        }
    }

    /** Ambil peta PD per (product_code, dpd_code) yang berlaku pada tanggal tsb */
    public function loadPdMap(string $onDate): array
    {
        $pdMap=[];
        try {
            $st=$this->pdo->prepare("
                SELECT p.product_code, p.dpd_code, p.pd_percent
                FROM pd_current p
                JOIN (
                  SELECT product_code, dpd_code, MAX(created) AS created
                  FROM pd_current WHERE created <= ?
                  GROUP BY product_code, dpd_code
                ) x ON x.product_code=p.product_code AND x.dpd_code=p.dpd_code AND x.created=p.created
            ");
            $st->execute([$onDate]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $pdMap[(int)$r['product_code']][$r['dpd_code']] = (float)$r['pd_percent'];
            }
        } catch (\PDOException $e) {}
        return $pdMap;
    }

    /** Ambil nilai CKPN snapshot untuk kumpulan rekening (pagi-sore window created) */
    public function fetchSnapCkpnMap(string $date, ?string $kc, array $accs): array
    {
        if (!$accs) return [];
        [$ds,$de] = ($this->dayRange)($date);
        $out = [];
        foreach (array_chunk($accs, 500) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $sql = "SELECT no_rekening, nilai_ckpn
                    FROM nominatif_ckpn
                    WHERE created >= ? AND created < ? AND no_rekening IN ($ph)";
            $params = array_merge([$ds,$de], $chunk);
            if ($kc !== null && $kc !== '000') { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
            else { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }
            $st = $this->pdo->prepare($sql); $st->execute($params);
            while($r = $st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (float)$r['nilai_ckpn'];
        }
        return $out;
    }

    /** Ambil CKPN individual terakhir <= tanggal untuk kumpulan rekening */
    public function fetchIndivCkpnMap(string $date, array $accs): array
    {
        if (!$accs) return [];
        $out = [];
        foreach (array_chunk($accs, 500) as $chunk){
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $sql = "SELECT ci.no_rekening, ci.nilai_ckpn
                    FROM ckpn_individual ci
                    JOIN (
                      SELECT no_rekening, MAX(created) AS created
                      FROM ckpn_individual WHERE created <= ? AND no_rekening IN ($ph)
                      GROUP BY no_rekening
                    ) x ON x.no_rekening=ci.no_rekening AND x.created=ci.created";
            $params = array_merge([$date], $chunk);
            $st = $this->pdo->prepare($sql); $st->execute($params);
            while($r=$st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (float)$r['nilai_ckpn'];
        }
        return $out;
    }

    /** Ambil set rekening yang restruktur terakhir <= tanggal */
    public function fetchRestrukSet(string $date, array $accs): array
    {
        if (!$accs) return [];
        $set = [];
        foreach (array_chunk($accs, 500) as $chunk){
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $sql = "SELECT nr.no_rekening
                    FROM nom_restruk nr
                    JOIN (
                      SELECT no_rekening, MAX(created) AS created
                      FROM nom_restruk WHERE created <= ? AND no_rekening IN ($ph)
                      GROUP BY no_rekening
                    ) x ON x.no_rekening=nr.no_rekening AND x.created=nr.created";
            $params = array_merge([$date], $chunk);
            $st = $this->pdo->prepare($sql); $st->execute($params);
            while($r=$st->fetch(PDO::FETCH_ASSOC)) $set[$r['no_rekening']] = true;
        }
        return $set;
    }

    /**
     * Hitung CKPN satu baris rekening.
     * Prioritas: $snapVal (snapshot) → CKPN individual → compute (PD×LGD).
     * $bucketDefs dipakai kalau bucket belum ditentukan; struktur sama dengan loadBuckets()[0].
     */
    public function computeCkpnForRow(
        array $row, string $onDate, array $pdMap, float $LGD,
        array $indivMap, array $restrukSet, $snapVal,
        array $bucketDefs
    ): int {
        if ($snapVal !== null) return (int)round((float)$snapVal);

        $acc  = $row['no_rekening'] ?? null;
        if ($acc && isset($indivMap[$acc])) return (int)round((float)$indivMap[$acc]);

        $ead  = (float)($row['saldo_bank'] ?? 0);
        $dpd  = (int)($row['hari_menunggak'] ?? 0);
        $prod = isset($row['kode_produk']) && $row['kode_produk']!=='' ? (int)$row['kode_produk'] : null;

        // Tentukan bucket (boleh sudah diisi to_bucket/from_bucket)
        $bucket = $row['to_bucket'] ?? $row['from_bucket'] ?? null;
        if (!$bucket) {
            foreach ($bucketDefs as $b) {
                if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) { $bucket = $b['code']; break; }
            }
            if (!$bucket) $bucket = 'A';
        }

        $isRestruk = $acc ? isset($restrukSet[$acc]) : false;
        if ($dpd <= 7 && !$isRestruk) return 0;

        $pd = 0.0;
        if ($prod !== null && isset($pdMap[$prod][$bucket])) $pd = (float)$pdMap[$prod][$bucket];

        return (int)round($ead * ($pd/100.0) * ($LGD/100.0));
    }
}
