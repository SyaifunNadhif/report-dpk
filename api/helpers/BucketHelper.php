<?php

class BucketHelper {

    private $pdo;
    private $pdMapCache = [];
    private $bucketDefs = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadBuckets(); 
    }

    // --- 1. DEFINISI BUCKET (Sama seperti sebelumnya) ---
    private function loadBuckets() {
        // Mockup definisi bucket
        $this->bucketDefs = [
            ['code'=>'A', 'min'=>0,  'max'=>0],
            ['code'=>'B', 'min'=>1,  'max'=>7],
            ['code'=>'C', 'min'=>8,  'max'=>14],
            ['code'=>'D', 'min'=>15, 'max'=>21],
            ['code'=>'E', 'min'=>22, 'max'=>30],
            ['code'=>'F', 'min'=>31, 'max'=>60],
            ['code'=>'G', 'min'=>61, 'max'=>90],
            ['code'=>'H', 'min'=>91, 'max'=>9999],
        ];
    }

    public function getBucketCode($dpd) {
        foreach ($this->bucketDefs as $b) {
            if ($dpd >= $b['min'] && ($dpd <= $b['max'] || $b['max'] === 9999)) {
                return $b['code'];
            }
        }
        return 'A'; 
    }

    public function getAllBucketCodes() {
        return array_column($this->bucketDefs, 'code');
    }

    // --- 2. LOGIKA CKPN (CORE UPDATE) ---
    
    /**
     * @param array $row Data akun (harus ada baki_debet, nilai_ckpn, jml_pinjaman, dll)
     * @param string $context 'closing' (M-1) atau 'harian' (Current)
     * @param array $pdMap Map PD
     * @param float $lgdPercent Nilai LGD
     */
    public function getCkpnValue($row, $context, $pdMap, $lgdPercent) {
        $nilaiCkpnDB = (float) ($row['nilai_ckpn'] ?? 0);
        $jmlPinjaman = (float) ($row['jml_pinjaman'] ?? 0);
        
        // --- ATURAN 1: INDIVIDUAL (Pladond >= 3 Milyar) ---
        // Kalau pinjaman jumbo, selalu percaya nilai dari DB (Nominatif)
        // Baik itu M-1 maupun Harian.
        if ($jmlPinjaman >= 3000000000) {
            return $nilaiCkpnDB;
        }

        // --- ATURAN 2: DATA M-1 (Closing) ---
        // Data masa lalu sudah final, ambil langsung dari DB.
        if ($context === 'closing') {
            return $nilaiCkpnDB;
        }

        // --- ATURAN 3: DATA HARIAN (KOLEKTIF) ---
        // Jika bukan Individual DAN data Hari Ini, kita HITUNG ULANG.
        
        $dpd  = (int) ($row['hari_menunggak'] ?? 0);
        $ead  = (float) ($row['baki_debet'] ?? 0);
        $prod = $row['kode_produk'] ?? null;
        $bucket = $this->getBucketCode($dpd);

        // Logic Lancar (0-7 hari) biasanya CKPN 0 (Kecuali restruk, disederhanakan)
        if ($dpd <= 7) return 0;

        // Ambil PD
        $pd = 0.0;
        if ($prod && isset($pdMap[$prod][$bucket])) {
            $pd = (float) $pdMap[$prod][$bucket];
        }

        // Rumus: EAD * PD * LGD
        return round($ead * ($pd / 100) * ($lgdPercent / 100));
    }

    // --- 3. LOAD PARAMETER (PD & LGD) ---
    
    public function loadPdMap($date) {
        if (!empty($this->pdMapCache)) return $this->pdMapCache;
        // Contoh Query PD (Sesuaikan tabelmu)
        // $sql = "SELECT product_code, dpd_code, pd_percent FROM pd_current ...";
        
        // Mockup Data PD
        $this->pdMapCache = [
            '001' => ['A'=>0.5, 'B'=>2.5, 'C'=>10, 'F'=>50, 'H'=>100],
            // ... produk lain
        ];
        return $this->pdMapCache;
    }

    public function loadGlobalLGD($date) {
        return 45.0; // Contoh LGD 45%
    }
}