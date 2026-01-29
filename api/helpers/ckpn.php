<?php
declare(strict_types=1);

/**
 * CKPN Utilities
 * Logic: Snapshot (M-1) > Individual (>3M) > Compute (PD x LGD x EAD).
 */
class CkpnUtils
{
    private PDO $pdo;
    
    /** @var callable(string): array{0:string,1:string} */
    private $dayRange;

    // Cache sederhana untuk performa dalam satu request
    private array $pdCache = [];
    private float $lgdCache = -1.0;

    /**
     * @param PDO $pdo Koneksi Database
     * @param callable $dayRangeFn Callback function(string $dateYmd): array{start, end}
     */
    public function __construct(PDO $pdo, callable $dayRangeFn)
    {
        $this->pdo      = $pdo;
        $this->dayRange = $dayRangeFn;
    }

    /** * 1. Ambil LGD Global 
     * (Fallback 59.48% jika belum disetting di DB) 
     */
    public function loadGlobalLGD(string $onDate): float
    {
        if ($this->lgdCache >= 0) return $this->lgdCache;

        try {
            $st = $this->pdo->prepare("
                SELECT lgd_percent FROM lgd_current
                WHERE created <= ? ORDER BY created DESC LIMIT 1
            ");
            $st->execute([$onDate]);
            $v = $st->fetchColumn();
            $this->lgdCache = ($v !== false) ? (float)$v : 59.48;
        } catch (\PDOException $e) {
            $this->lgdCache = 59.48;
        }
        return $this->lgdCache;
    }

    /** * 2. Ambil Peta PD (Probability of Default) 
     * Format: [kode_produk => [kode_bucket => persen]]
     */
    public function loadPdMap(string $onDate): array
    {
        if (!empty($this->pdCache)) return $this->pdCache;

        try {
            $st = $this->pdo->prepare("
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
                $this->pdCache[(int)$r['product_code']][$r['dpd_code']] = (float)$r['pd_percent'];
            }
        } catch (\PDOException $e) {}
        
        return $this->pdCache;
    }

    /** * 3. Fetch Rekening Restruktur (Flagging) 
     */
    public function fetchRestrukSet(string $date, array $accs): array
    {
        if (empty($accs)) return [];
        $set = [];
        // Pecah chunk 500 agar query tidak terlalu panjang
        foreach (array_chunk($accs, 500) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            // Ambil data restruk terakhir sebelum/pada tanggal tersebut
            $sql = "SELECT nr.no_rekening
                    FROM nom_restruk nr
                    JOIN (
                      SELECT no_rekening, MAX(created) AS created
                      FROM nom_restruk WHERE created <= ? AND no_rekening IN ($ph)
                      GROUP BY no_rekening
                    ) x ON x.no_rekening=nr.no_rekening AND x.created=nr.created";
            
            $params = array_merge([$date], $chunk);
            $st = $this->pdo->prepare($sql); 
            $st->execute($params);
            
            while($r = $st->fetch(PDO::FETCH_ASSOC)) {
                $set[$r['no_rekening']] = true;
            }
        }
        return $set;
    }

    /**
     * 4. Menentukan Kode Bucket berdasarkan DPD
     */
    public function getBucketCode(int $dpd, array $bucketDefs): string 
    {
        foreach ($bucketDefs as $b) {
            if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) {
                return $b['code'];
            }
        }
        return 'A'; // Default Lancar
    }

    /**
     * 5. CORE LOGIC: Hitung CKPN satu baris rekening.
     * * Prioritas:
     * 1. Individual (Plafond >= 3M) -> Pakai nilai DB (nilai_ckpn).
     * 2. Snapshot M-1 (Jika $isM1Snapshot = true) -> Pakai nilai DB.
     * 3. Harian (Kolektif) -> Hitung Rumus (EAD * PD * LGD).
     * * @param array $row Data akun (wajib ada: baki_debet, hari_menunggak, kode_produk, jml_pinjaman, nilai_ckpn)
     * @param bool $isM1Snapshot Apakah ini konteks data Closing M-1?
     * @param array $pdMap Peta PD
     * @param float $LGD Nilai LGD
     * @param array $restrukSet Daftar akun restruk
     * @param array $bucketDefs Definisi bucket untuk lookup
     */
    public function computeCkpnForRow(
        array $row, 
        bool $isM1Snapshot,
        array $pdMap, 
        float $LGD,
        array $restrukSet, 
        array $bucketDefs
    ): int {
        $acc         = $row['no_rekening'] ?? '';
        $nilaiCkpnDB = (float)($row['nilai_ckpn'] ?? 0);
        $plafond     = (float)($row['jml_pinjaman'] ?? 0);

        // --- RULE 1: INDIVIDUAL (Plafond >= 3 Milyar) ---
        // Selalu percaya nilai dari database, baik itu M-1 maupun Harian.
        if ($plafond >= 3000000000) {
            return (int)round($nilaiCkpnDB);
        }

        // --- RULE 2: SNAPSHOT M-1 ---
        // Jika ini data closing bulan lalu, pakai nilai yang sudah tersimpan (Final).
        if ($isM1Snapshot) {
            return (int)round($nilaiCkpnDB);
        }

        // --- RULE 3: HITUNG MANUAL (Harian / Kolektif) ---
        
        $ead  = (float)($row['baki_debet'] ?? 0); // EAD = Baki Debet
        $dpd  = (int)($row['hari_menunggak'] ?? 0);
        $prod = isset($row['kode_produk']) && $row['kode_produk'] !== '' ? (int)$row['kode_produk'] : null;

        // Tentukan bucket (cek kalau sudah ada di row, kalau belum cari via helper)
        $bucket = $row['to_bucket'] ?? $row['from_bucket'] ?? null;
        if (!$bucket) { 
            $bucket = $this->getBucketCode($dpd, $bucketDefs);
        }

        // Cek Status Restrukturisasi
        $isRestruk = isset($restrukSet[$acc]);

        // Logic Dasar: Lancar (0-7 hari) & Tidak Restruk => CKPN 0
        if ($dpd <= 7 && !$isRestruk) {
            return 0;
        }

        // Ambil PD
        $pd = 0.0;
        if ($prod !== null && isset($pdMap[$prod][$bucket])) {
            $pd = (float)$pdMap[$prod][$bucket];
        }

        // Rumus CKPN = EAD * PD% * LGD%
        return (int)round($ead * ($pd / 100.0) * ($LGD / 100.0));
    }
}