<?php
// require_once 'Helpers/CkpnUtils.php'; // Utils optional

class BucketFeController {

    private $pdo;
    private $visualBuckets = ['0', '1-7', '8-14', '15-21', '22-30', 'BE'];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Helper: Range Tanggal (Index Friendly)
     */
    private function getDayRange($date) {
        return [$date . ' 00:00:00', $date . ' 23:59:59'];
    }

    /**
     * Helper SQL: Bucket Case
     */
    private function getBucketSql($col) {
        return "CASE 
            WHEN $col <= 0 THEN '0'
            WHEN $col BETWEEN 1 AND 7 THEN '1-7'
            WHEN $col BETWEEN 8 AND 14 THEN '8-14'
            WHEN $col BETWEEN 15 AND 21 THEN '15-21'
            WHEN $col BETWEEN 22 AND 30 THEN '22-30'
            ELSE 'BE' 
        END";
    }

    /**
     * ENDPOINT 1: REKAP MATRIKS (SUPER JET SPEED - SINGLE PASS)
     */
    public function migrasiBucketOsc($input = null) {
        // 1. Setup Time Limit & Memory (Jaga-jaga)
        set_time_limit(300); 
        ini_set('memory_limit', '512M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib diisi.");

        // 2. Prepare Tanggal
        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // 3. Prepare Wadah Data
        $summary = [];
        $matrix  = [];
        $grandTotal = [
            'm1' => ['noa'=>0, 'os'=>0],
            'buckets' => [],
            'angsuran' => 0,
            'lunas' => ['noa'=>0, 'os'=>0],
            'runoff_total' => ['noa'=>0, 'os'=>0]
        ];

        // Init Default Values
        foreach ($this->visualBuckets as $lbl) {
            $summary[$lbl] = ['noa_m1'=>0, 'os_m1'=>0];
            $grandTotal['buckets'][$lbl] = ['noa'=>0, 'os'=>0];
            foreach (array_merge($this->visualBuckets, ['O']) as $t) {
                $matrix[$lbl][$t] = ['noa'=>0, 'os'=>0, 'angsuran'=>0, 'pelunasan'=>0];
            }
        }

        // --- QUERY UTAMA (THE JET ENGINE) ---
        // Menggabungkan M1 dan Current dalam satu tarikan data pakai LEFT JOIN
        // Logic:
        // 1. Ambil semua data M1 (T1)
        // 2. Left Join ke Current (T2)
        // 3. Jika T2 NULL -> Lunas
        // 4. Jika T2 Ada  -> Migrasi/Stay
        
        $bucketM1 = $this->getBucketSql("t1.hari_menunggak");
        $bucketCur = $this->getBucketSql("t2.hari_menunggak");

        $sql = "
            SELECT 
                $bucketM1 as from_bucket,
                IF(t2.no_rekening IS NULL, 'O', $bucketCur) as to_bucket,
                COUNT(1) as noa,
                SUM(t1.baki_debet) as os_m1,
                SUM(COALESCE(t2.baki_debet, 0)) as os_curr,
                SUM(CASE 
                    WHEN t2.no_rekening IS NOT NULL AND t1.baki_debet > t2.baki_debet 
                    THEN (t1.baki_debet - t2.baki_debet) 
                    ELSE 0 
                END) as angsuran_murni
            FROM nominatif t1
            LEFT JOIN nominatif t2 
                ON t1.no_rekening = t2.no_rekening 
                AND (t2.created BETWEEN :s2 AND :e2) -- Filter Join Current
            WHERE (t1.created BETWEEN :s1 AND :e1)   -- Filter M1
        ";

        if ($kc) $sql .= " AND t1.kode_cabang = :kc ";
        $sql .= " GROUP BY 1, 2";

        // Eksekusi Query Utama
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->execute();

        // Proses Hasil Query
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $f = $r['from_bucket'];
            $t = $r['to_bucket']; // Bisa '0'..'BE' atau 'O' (Lunas)
            
            // Isi Summary M-1
            if (isset($summary[$f])) {
                $summary[$f]['noa_m1'] += (int)$r['noa'];
                $summary[$f]['os_m1']  += (float)$r['os_m1'];
            }

            // Isi Grand Total M-1
            $grandTotal['m1']['noa'] += (int)$r['noa'];
            $grandTotal['m1']['os']  += (float)$r['os_m1'];

            // Cabang Logika: Lunas vs Aktif
            if ($t === 'O') {
                // Lunas
                $matrix[$f]['O']['noa'] = (int)$r['noa'];
                $matrix[$f]['O']['pelunasan'] = (float)$r['os_m1']; // Full OS Lunas

                $grandTotal['lunas']['noa'] += (int)$r['noa'];
                $grandTotal['lunas']['os']  += (float)$r['os_m1'];
            } else {
                // Aktif (Migrasi / Stay)
                $matrix[$f][$t]['noa'] = (int)$r['noa'];
                $matrix[$f][$t]['os']  = (float)$r['os_curr'];
                
                // Logic Angsuran: 
                // Di sini kita pakai angsuran murni dari query (penurunan saldo)
                // Nanti dihitung ulang per baris agar balance
                $matrix[$f][$t]['angsuran'] = (float)$r['angsuran_murni']; 

                // Grand Total Buckets Current
                if (isset($grandTotal['buckets'][$t])) {
                    $grandTotal['buckets'][$t]['noa'] += (int)$r['noa'];
                    $grandTotal['buckets'][$t]['os']  += (float)$r['os_curr'];
                }
            }
        }

        // --- HITUNG ANGSURAN AGAR BALANCE (Netting Row) ---
        // Agar Growth - Realisasi = Runoff
        $rowActiveOS = [];
        // Hitung total OS Aktif per baris (yang sudah diisi di atas)
        foreach ($this->visualBuckets as $f) {
            $rowActiveOS[$f] = 0;
            foreach ($this->visualBuckets as $t) {
                $rowActiveOS[$f] += $matrix[$f][$t]['os'];
            }
        }

        foreach ($this->visualBuckets as $f) {
            $osM1    = $summary[$f]['os_m1'];
            $osLunas = $matrix[$f]['O']['pelunasan'];
            $osAktif = $rowActiveOS[$f];

            // Rumus Balance: OS_M1 - OS_Aktif - OS_Lunas = Net Angsuran (Bisa minus jika Topup)
            $netAngsuran = $osM1 - $osAktif - $osLunas;

            // Masukkan ke kolom Angsuran (kita taruh di diagonal/dummy cell agar JSON struktur tetap)
            // Saya taruh di cell $matrix[$f][$f] atau loop pertama
            foreach ($this->visualBuckets as $t) {
                if ($f == $t) {
                    $matrix[$f][$t]['angsuran'] = $netAngsuran;
                } else {
                    $matrix[$f][$t]['angsuran'] = 0; // Reset yang lain agar tidak double
                }
            }
            $grandTotal['angsuran'] += $netAngsuran;
        }

        // --- QUERY REALISASI (Terpisah karena logicnya beda) ---
        // Akun ada di Current tapi TIDAK ADA di M1
        $sqlReal = "
            SELECT COUNT(1) as noa, SUM(t2.baki_debet) as os
            FROM nominatif t2
            LEFT JOIN nominatif t1 
                ON t2.no_rekening = t1.no_rekening 
                AND (t1.created BETWEEN :s1 AND :e1)
            WHERE (t2.created BETWEEN :s2 AND :e2)
              AND t1.no_rekening IS NULL
        ";
        if ($kc) $sqlReal .= " AND t2.kode_cabang = :kc ";

        $stmtReal = $this->pdo->prepare($sqlReal);
        $stmtReal->bindValue(':s1', $s1); $stmtReal->bindValue(':e1', $e1);
        $stmtReal->bindValue(':s2', $s2); $stmtReal->bindValue(':e2', $e2);
        if ($kc) $stmtReal->bindValue(':kc', $kc);
        $stmtReal->execute();
        $resReal = $stmtReal->fetch(PDO::FETCH_ASSOC);

        $realisasi = [
            'noa' => (int)($resReal['noa'] ?? 0),
            'os'  => (float)($resReal['os'] ?? 0)
        ];

        // Final Runoff
        $grandTotal['runoff_total']['os'] = $grandTotal['angsuran'] + $grandTotal['lunas']['os'];
        $grandTotal['runoff_total']['noa'] = $grandTotal['lunas']['noa'];

        $this->send(200, "Sukses", [
            'meta'      => ['kc'=>$kc, 'm1'=>$closing, 'cur'=>$harian],
            'summary_m1'=> $summary,
            'matrix'    => $matrix,
            'realisasi' => $realisasi,
            'grand_total' => $grandTotal
        ]);
    }

    /**
     * ENDPOINT 2: DETAIL DATA (Sama seperti sebelumnya, aman)
     */
    public function getMigrasiDetail($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;
        
        $fromLbl = (isset($b['from_bucket']) && $b['from_bucket'] !== '') ? (string)$b['from_bucket'] : null;
        $toLbl   = (isset($b['to_bucket']) && $b['to_bucket'] !== '') ? (string)$b['to_bucket'] : null;
        $page    = isset($b['page']) ? (int)$b['page'] : 1;
        $limit   = isset($b['limit']) ? (int)$b['limit'] : 10;
        $offset  = ($page - 1) * $limit;

        if (!$closing || !$harian) { $this->send(400, "Tanggal wajib."); return; }

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // SQL Helpers
        $bucketM1  = $this->getBucketSql("t1.hari_menunggak");
        $bucketCur = $this->getBucketSql("t2.hari_menunggak");

        $sqlData = ""; $sqlCount = ""; $total = 0;

        // Logic Filter SQL
        $bucketWhere = function($col, $lbl) {
            $l = (string)$lbl;
            if ($l==='0') return "$col <= 0";
            if ($l==='1-7') return "$col BETWEEN 1 AND 7";
            if ($l==='8-14') return "$col BETWEEN 8 AND 14";
            if ($l==='15-21') return "$col BETWEEN 15 AND 21";
            if ($l==='22-30') return "$col BETWEEN 22 AND 30";
            if ($l==='BE') return "$col > 30";
            return "1=1";
        };

        if ($fromLbl === 'REALISASI') {
            $base = "FROM nominatif t2 WHERE (t2.created BETWEEN :s2 AND :e2) 
                     AND NOT EXISTS (SELECT 1 FROM nominatif t1 WHERE t1.no_rekening=t2.no_rekening AND (t1.created BETWEEN :s1 AND :e1))";
            if ($kc) $base .= " AND t2.kode_cabang = :kc";
            if ($toLbl) $base .= " AND " . $bucketWhere("t2.hari_menunggak", $toLbl);

            $sqlCount = "SELECT COUNT(1) $base";
            $sqlData  = "SELECT t2.no_rekening, t2.nama_nasabah, t2.baki_debet, t2.hari_menunggak, t2.kode_produk, 
                         0 as os_m1, 0 as dpd_m1, 'New' as status_migrasi $base";
        } elseif ($toLbl === 'O') {
            $base = "FROM nominatif t1 WHERE (t1.created BETWEEN :s1 AND :e1) 
                     AND NOT EXISTS (SELECT 1 FROM nominatif t2 WHERE t2.no_rekening=t1.no_rekening AND (t2.created BETWEEN :s2 AND :e2))";
            if ($kc) $base .= " AND t1.kode_cabang = :kc";
            if ($fromLbl) $base .= " AND " . $bucketWhere("t1.hari_menunggak", $fromLbl);

            $sqlCount = "SELECT COUNT(1) $base";
            $sqlData  = "SELECT t1.no_rekening, t1.nama_nasabah, 0 as baki_debet, 0 as hari_menunggak, t1.kode_produk, 
                         t1.baki_debet as os_m1, t1.hari_menunggak as dpd_m1, 'Lunas' as status_migrasi $base";
        } else {
            $base = "FROM nominatif t1 JOIN nominatif t2 ON t1.no_rekening=t2.no_rekening 
                     WHERE (t1.created BETWEEN :s1 AND :e1) AND (t2.created BETWEEN :s2 AND :e2)";
            if ($kc) $base .= " AND t1.kode_cabang = :kc";
            if ($fromLbl) $base .= " AND " . $bucketWhere("t1.hari_menunggak", $fromLbl);
            if ($toLbl)   $base .= " AND " . $bucketWhere("t2.hari_menunggak", $toLbl);

            $sqlCount = "SELECT COUNT(1) $base";
            $sqlData  = "SELECT t2.no_rekening, t2.nama_nasabah, t2.baki_debet, t2.hari_menunggak, t2.kode_produk, 
                         t1.baki_debet as os_m1, t1.hari_menunggak as dpd_m1, 'Active' as status_migrasi $base";
        }

        // Exec Count
        $stmt = $this->pdo->prepare($sqlCount);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->execute();
        $total = $stmt->fetchColumn();

        // Exec Data
        $sqlData .= " ORDER BY baki_debet DESC LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sqlData);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Visual Label Fix
        foreach($rows as &$r) {
            if ($r['status_migrasi'] === 'Active') {
                $f = $this->getVisualLabelPHP($r['dpd_m1']);
                $t = $this->getVisualLabelPHP($r['hari_menunggak']);
                $r['status_migrasi'] = "$f -> $t";
            }
        }

        $this->send(200, "Detail Data", [
            'pagination' => ['current_page' => $page, 'total_records' => (int)$total, 'total_pages' => ceil($total / $limit)],
            'data' => $rows
        ]);
    }

    private function getVisualLabelPHP($dpd) {
        if ($dpd <= 0) return '0';
        if ($dpd <= 7) return '1-7';
        if ($dpd <= 14) return '8-14';
        if ($dpd <= 21) return '15-21';
        if ($dpd <= 30) return '22-30';
        return 'BE';
    }

    private function send($status, $msg, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $msg, 'data' => $data]);
        exit;
    }
}