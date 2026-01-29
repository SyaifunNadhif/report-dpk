<?php
declare(strict_types=1);

class CkpnUtils
{
    private PDO $pdo;
    
    // Cache sederhana untuk performa dalam satu request
    private array $pdCache = [];
    private float $lgdCache = -1.0;
    
    // Definisi Bucket Sesuai DB (ref_dpd_bucket)
    // Digunakan untuk mencari Kode Bucket (A, B, C...) dari Hari Menunggak
    // Agar bisa mengambil % PD yang tepat dari tabel pd_current
    private array $dbBucketDefs = [
        ['code'=>'A', 'min'=>0,  'max'=>0],    // Lancar
        ['code'=>'B', 'min'=>1,  'max'=>30],   // Dalam Perhatian Khusus (1-30)
        ['code'=>'C', 'min'=>31, 'max'=>60],   // Kurang Lancar
        ['code'=>'D', 'min'=>61, 'max'=>90],
        ['code'=>'E', 'min'=>91, 'max'=>120],
        ['code'=>'F', 'min'=>121,'max'=>150],
        ['code'=>'G', 'min'=>151,'max'=>180],
        ['code'=>'H', 'min'=>181,'max'=>210],
        ['code'=>'I', 'min'=>211,'max'=>240],
        ['code'=>'J', 'min'=>241,'max'=>270],
        ['code'=>'K', 'min'=>271,'max'=>300],
        ['code'=>'L', 'min'=>301,'max'=>330],
        ['code'=>'M', 'min'=>331,'max'=>360],
        ['code'=>'N', 'min'=>361,'max'=>9999], // Macet > 360
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 1. Mendapatkan KODE BUCKET dari Hari Menunggak (DPD)
     * Contoh: DPD 5 -> Return 'B', DPD 45 -> Return 'C'
     * Digunakan untuk lookup PD Map.
     */
    public function getPDBucketCode(int $dpd): string 
    {
        foreach ($this->dbBucketDefs as $b) {
            // Cek range. Jika max=9999 atau null, berarti unbounded upper limit
            if ($dpd >= $b['min'] && ($dpd <= $b['max'] || $b['max'] >= 9999)) {
                return $b['code'];
            }
        }
        return 'A'; // Default jika 0 atau negatif
    }

    /**
     * 2. Mendapatkan semua daftar kode bucket (A..N)
     * Berguna untuk looping kolom di Controller
     */
    public function getAllBucketCodes(): array {
        return array_column($this->dbBucketDefs, 'code');
    }

    /**
     * 3. Ambil LGD (Loss Given Default) Global
     * Mengambil nilai LGD terakhir sebelum/pada tanggal laporan.
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
            $this->lgdCache = ($v !== false) ? (float)$v : 59.48; // Default value
        } catch (\PDOException $e) {
            $this->lgdCache = 59.48;
        }
        return $this->lgdCache;
    }

    /**
     * 4. Ambil Peta PD (Probability of Default)
     * Output: Array [kode_produk => [kode_bucket => persen_pd]]
     * Contoh: $pdMap[101]['B'] = 2.5;
     */
    public function loadPdMap(string $onDate): array
    {
        if (!empty($this->pdCache)) return $this->pdCache;

        try {
            // Ambil data PD versi terakhir pada tanggal tersebut
            $st = $this->pdo->prepare("
                SELECT p.product_code, p.dpd_code, p.pd_percent
                FROM pd_current p
                JOIN (
                  SELECT product_code, dpd_code, MAX(created) AS created
                  FROM pd_current WHERE created <= ?
                  GROUP BY product_code, dpd_code
                ) x ON x.product_code=p.product_code 
                   AND x.dpd_code=p.dpd_code 
                   AND x.created=p.created
            ");
            $st->execute([$onDate]);
            
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $this->pdCache[(int)$r['product_code']][$r['dpd_code']] = (float)$r['pd_percent'];
            }
        } catch (\PDOException $e) {}
        
        return $this->pdCache;
    }

    /**
     * 5. Ambil Daftar Rekening Restruktur
     * Mengembalikan array asosiatif ['norek1' => true, 'norek2' => true]
     */
    public function fetchRestrukSet(string $date, array $accs): array
    {
        if (empty($accs)) return [];
        $set = [];
        
        // Chunking per 500 akun biar query optimal
        foreach (array_chunk($accs, 500) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));
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
     * 6. FUNGSI UTAMA: HITUNG CKPN PER BARIS
     * * Logika Prioritas:
     * 1. Individual (Plafond >= 3M): AMBIL DARI DB (nilai_ckpn).
     * 2. Data M-1 (Closing): AMBIL DARI DB (nilai_ckpn).
     * 3. Data Harian (Kolektif): HITUNG MANUAL (EAD * PD * LGD).
     * * @param array $row Data akun (wajib ada: saldo_bank, hari_menunggak, kode_produk, jml_pinjaman, nilai_ckpn)
     * @param bool $isM1Snapshot True jika ini data M-1 (Closing)
     * @param array $pdMap Peta PD dari loadPdMap()
     * @param float $LGD Nilai LGD dari loadGlobalLGD()
     * @param array $restrukSet Daftar akun restruk
     */
    public function computeCkpnForRow(
        array $row, 
        bool $isM1Snapshot, 
        array $pdMap, 
        float $LGD, 
        array $restrukSet
    ): int {
        $acc         = $row['no_rekening'] ?? '';
        $nilaiCkpnDB = (float)($row['nilai_ckpn'] ?? 0);
        $plafond     = (float)($row['jml_pinjaman'] ?? 0);

        // --- RULE 1: INDIVIDUAL (Plafond >= 3 Milyar) ---
        // Selalu ambil nilai CKPN yang sudah diinput manual di DB (Analisa AO)
        if ($plafond >= 3000000000) {
            return (int)round($nilaiCkpnDB);
        }

        // --- RULE 2: DATA M-1 (CLOSING) ---
        // Data masa lalu sudah final, ambil dari DB.
        if ($isM1Snapshot) {
            return (int)round($nilaiCkpnDB);
        }

        // --- RULE 3: HITUNGAN HARIAN (KOLEKTIF) ---
        // Hitung estimasi CKPN hari ini berdasarkan parameter
        
        $ead  = (float)($row['saldo_bank'] ?? 0); // EAD = Baki Debet
        $dpd  = (int)($row['hari_menunggak'] ?? 0);
        $prod = isset($row['kode_produk']) ? (int)$row['kode_produk'] : null;

        // 1. Cari Kode Bucket untuk PD (Misal DPD 5 -> 'B')
        $bucketCode = $this->getPDBucketCode($dpd);

        // 2. Cek Flag Restruk
        $isRestruk = isset($restrukSet[$acc]);

        // 3. Logic Dasar: Lancar (0-7 hari) & Tidak Restruk => CKPN 0
        // (Sesuaikan jika bank punya aturan beda)
        if ($dpd <= 7 && !$isRestruk) {
            return 0;
        }

        // 4. Cari % PD dari Map
        $pd = 0.0;
        if ($prod !== null && isset($pdMap[$prod][$bucketCode])) {
            $pd = (float)$pdMap[$prod][$bucketCode];
        }

        // 5. Rumus CKPN = EAD * PD% * LGD%
        return (int)round($ead * ($pd / 100.0) * ($LGD / 100.0));
    }
}