<?php

class BucketFeController {

    private $pdo;
    private $visualBuckets = ['0', '1-7', '8-14', '15-21', '22-30', 'BE'];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- HELPER 1: SQL Case Bucket ---
    private function getBucketSqlCase($colName) {
        return "CASE 
            WHEN $colName <= 0 THEN '0'
            WHEN $colName BETWEEN 1 AND 7 THEN '1-7'
            WHEN $colName BETWEEN 8 AND 14 THEN '8-14'
            WHEN $colName BETWEEN 15 AND 21 THEN '15-21'
            WHEN $colName BETWEEN 22 AND 30 THEN '22-30'
            ELSE 'BE' 
        END";
    }

    // --- HELPER 2: SQL Where Condition (Fixed String '0') ---
    private function getBucketConditionSql($colName, $bucketLabel) {
        $lbl = (string)$bucketLabel;
        if ($lbl === '0')     return "$colName <= 0";
        if ($lbl === '1-7')   return "$colName BETWEEN 1 AND 7";
        if ($lbl === '8-14')  return "$colName BETWEEN 8 AND 14";
        if ($lbl === '15-21') return "$colName BETWEEN 15 AND 21";
        if ($lbl === '22-30') return "$colName BETWEEN 22 AND 30";
        if ($lbl === 'BE')    return "$colName > 30";
        return "1=1"; 
    }

    // --- HELPER 3: Range Tanggal ---
    private function getDayRange($date) {
        return [$date . ' 00:00:00', $date . ' 23:59:59'];
    }

    // --- HELPER 4: PHP Visual Label ---
    private function getVisualLabel($dpd) {
        $d = (int)$dpd;
        if ($d <= 0) return '0';
        if ($d <= 7) return '1-7';
        if ($d <= 14) return '8-14';
        if ($d <= 21) return '15-21';
        if ($d <= 30) return '22-30';
        return 'BE';
    }

    /**
     * =========================================================================
     * ENDPOINT 1: REKAP MATRIKS (Standard Baki Debet)
     * =========================================================================
     */
    public function migrasiBucketOsc($input = null) {
        // Setup System
        set_time_limit(300); ini_set('memory_limit', '512M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib diisi.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // Init Data Structure
        $summary = []; $matrix  = [];
        $grandTotal = [
            'm1' => ['noa'=>0, 'os'=>0],
            'buckets' => [],
            'angsuran' => 0,
            'lunas' => ['noa'=>0, 'os'=>0],
            'runoff_total' => ['noa'=>0, 'os'=>0]
        ];

        foreach ($this->visualBuckets as $lbl) {
            $summary[$lbl] = ['noa_m1'=>0, 'os_m1'=>0];
            $grandTotal['buckets'][$lbl] = ['noa'=>0, 'os'=>0];
            foreach (array_merge($this->visualBuckets, ['O']) as $t) {
                $matrix[$lbl][$t] = ['noa'=>0, 'os'=>0, 'angsuran'=>0, 'pelunasan'=>0];
            }
        }

        // --- QUERY UTAMA (SINGLE PASS AGGREGATION) ---
        $bucketM1  = $this->getBucketSqlCase("t1.hari_menunggak");
        $bucketCur = $this->getBucketSqlCase("t2.hari_menunggak");

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
                AND (t2.created BETWEEN :s2 AND :e2)
            WHERE (t1.created BETWEEN :s1 AND :e1)
        ";

        if ($kc) $sql .= " AND t1.kode_cabang = :kc ";
        $sql .= " GROUP BY 1, 2";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->execute();

        // Processing Data
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $f = $r['from_bucket'];
            $t = $r['to_bucket'];

            // Isi Summary & GT M-1
            if (isset($summary[$f])) {
                $summary[$f]['noa_m1'] += (int)$r['noa'];
                $summary[$f]['os_m1']  += (float)$r['os_m1'];
            }
            $grandTotal['m1']['noa'] += (int)$r['noa'];
            $grandTotal['m1']['os']  += (float)$r['os_m1'];

            // Cabang: Lunas vs Aktif
            if ($t === 'O') {
                $matrix[$f]['O']['noa'] = (int)$r['noa'];
                $matrix[$f]['O']['pelunasan'] = (float)$r['os_m1'];
                
                $grandTotal['lunas']['noa'] += (int)$r['noa'];
                $grandTotal['lunas']['os']  += (float)$r['os_m1'];
            } else {
                $matrix[$f][$t]['noa'] = (int)$r['noa'];
                $matrix[$f][$t]['os']  = (float)$r['os_curr'];
                $matrix[$f][$t]['angsuran'] = (float)$r['angsuran_murni']; // Raw

                if (isset($grandTotal['buckets'][$t])) {
                    $grandTotal['buckets'][$t]['noa'] += (int)$r['noa'];
                    $grandTotal['buckets'][$t]['os']  += (float)$r['os_curr'];
                }
            }
        }

        // --- HITUNG ANGSURAN AGAR BALANCE ---
        foreach ($this->visualBuckets as $f) {
            $rowM1    = $summary[$f]['os_m1'];
            $rowLunas = $matrix[$f]['O']['pelunasan'];
            $rowAktif = 0;
            
            foreach ($this->visualBuckets as $t) {
                $rowAktif += $matrix[$f][$t]['os'];
            }

            // Net Difference (Angsuran - Top Up)
            $netAngsuran = $rowM1 - ($rowAktif + $rowLunas);
            
            // Masukkan ke Matrix (diagonal cell)
            foreach ($this->visualBuckets as $t) {
                if ($f == $t) {
                    $matrix[$f][$t]['angsuran'] = $netAngsuran;
                } else {
                    $matrix[$f][$t]['angsuran'] = 0;
                }
            }
            $grandTotal['angsuran'] += $netAngsuran;
        }

        // --- QUERY REALISASI (New Accounts) ---
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

        $stmtR = $this->pdo->prepare($sqlReal);
        $stmtR->bindValue(':s1', $s1); $stmtR->bindValue(':e1', $e1);
        $stmtR->bindValue(':s2', $s2); $stmtR->bindValue(':e2', $e2);
        if ($kc) $stmtR->bindValue(':kc', $kc);
        $stmtR->execute();
        $resR = $stmtR->fetch(PDO::FETCH_ASSOC);

        $realisasi = [
            'noa' => (int)($resR['noa'] ?? 0),
            'os'  => (float)($resR['os'] ?? 0)
        ];

        // Final GT Runoff
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
     * =========================================================================
     * ENDPOINT 2: DETAIL DATA (Lengkap dengan Kolek, Saldo Bank, Totung)
     * =========================================================================
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

        if (!$closing || !$harian) return $this->send(400, "Tanggal kurang lengkap.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // Vars
        $sqlData = ""; $sqlCount = ""; $total = 0;
        
        // --- 1. DETAIL REALISASI ---
        if ($fromLbl === 'REALISASI') {
            $baseWhere = "t2.created BETWEEN :s2 AND :e2 
                          AND NOT EXISTS (SELECT 1 FROM nominatif t1 WHERE t1.no_rekening = t2.no_rekening AND t1.created BETWEEN :s1 AND :e1)";
            
            if ($kc) $baseWhere .= " AND t2.kode_cabang = :kc";
            
            if ($toLbl !== null) {
                $baseWhere .= " AND " . $this->getBucketConditionSql("t2.hari_menunggak", $toLbl);
            }

            $sqlCount = "SELECT COUNT(1) FROM nominatif t2 WHERE $baseWhere";
            $sqlData  = "SELECT t2.no_rekening, t2.nama_nasabah, t2.baki_debet, t2.hari_menunggak, t2.kode_produk,
                                t2.saldo_bank, t2.kolektibilitas, t2.tunggakan_pokok, t2.tunggakan_bunga,
                                0 as os_m1, 0 as dpd_m1, 'New' as status_migrasi 
                         FROM nominatif t2 WHERE $baseWhere";
        } 
        // --- 2. DETAIL LUNAS ---
        elseif ($toLbl === 'O') {
            $baseWhere = "t1.created BETWEEN :s1 AND :e1 
                          AND NOT EXISTS (SELECT 1 FROM nominatif t2 WHERE t2.no_rekening = t1.no_rekening AND t2.created BETWEEN :s2 AND :e2)";
            
            if ($kc) $baseWhere .= " AND t1.kode_cabang = :kc";

            if ($fromLbl !== null) {
                $baseWhere .= " AND " . $this->getBucketConditionSql("t1.hari_menunggak", $fromLbl);
            }

            $sqlCount = "SELECT COUNT(1) FROM nominatif t1 WHERE $baseWhere";
            
            // Ambil info detail dari M-1 (t1) karena di Current sudah hilang
            $sqlData  = "SELECT t1.no_rekening, t1.nama_nasabah, 0 as baki_debet, 0 as hari_menunggak, t1.kode_produk,
                                t1.saldo_bank, t1.kolektibilitas, t1.tunggakan_pokok, t1.tunggakan_bunga,
                                t1.baki_debet as os_m1, t1.hari_menunggak as dpd_m1, 'Lunas' as status_migrasi 
                         FROM nominatif t1 WHERE $baseWhere";
        } 
        // --- 3. MIGRASI NORMAL ---
        else {
            $baseWhere = "t1.created BETWEEN :s1 AND :e1 
                          AND t2.created BETWEEN :s2 AND :e2 
                          AND t1.no_rekening = t2.no_rekening";
            
            if ($kc) $baseWhere .= " AND t1.kode_cabang = :kc";

            if ($fromLbl !== null) {
                $baseWhere .= " AND " . $this->getBucketConditionSql("t1.hari_menunggak", $fromLbl);
            }
            if ($toLbl !== null) {
                $baseWhere .= " AND " . $this->getBucketConditionSql("t2.hari_menunggak", $toLbl);
            }

            $sqlCount = "SELECT COUNT(1) FROM nominatif t1, nominatif t2 WHERE $baseWhere";
            $sqlData  = "SELECT t2.no_rekening, t2.nama_nasabah, t2.baki_debet, t2.hari_menunggak, t2.kode_produk,
                                t2.saldo_bank, t2.kolektibilitas, t2.tunggakan_pokok, t2.tunggakan_bunga,
                                t1.baki_debet as os_m1, t1.hari_menunggak as dpd_m1, 'Active' as status_migrasi 
                         FROM nominatif t1, nominatif t2 WHERE $baseWhere";
        }

        // EKSEKUSI COUNT
        $stmtCnt = $this->pdo->prepare($sqlCount);
        $stmtCnt->bindValue(':s1', $s1); $stmtCnt->bindValue(':e1', $e1);
        $stmtCnt->bindValue(':s2', $s2); $stmtCnt->bindValue(':e2', $e2);
        if ($kc) $stmtCnt->bindValue(':kc', $kc);
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        // EKSEKUSI DATA
        // Default sort by baki_debet terbesar
        $sqlData .= " ORDER BY baki_debet DESC LIMIT :lim OFFSET :off";
        
        $stmt = $this->pdo->prepare($sqlData);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Post-Processing
        foreach ($rows as &$r) {
            // Label Status Migrasi
            if ($r['status_migrasi'] === 'Active') {
                $f = $this->getVisualLabel($r['dpd_m1'] ?? 0);
                $t = $this->getVisualLabel($r['hari_menunggak'] ?? 0);
                $r['status_migrasi'] = "$f -> $t";
            }
            // Format Angka (Float)
            $r['baki_debet']      = (float)$r['baki_debet'];
            $r['os_m1']           = (float)$r['os_m1'];
            $r['saldo_bank']      = (float)($r['saldo_bank'] ?? 0);
            $r['tunggakan_pokok'] = (float)($r['tunggakan_pokok'] ?? 0);
            $r['tunggakan_bunga'] = (float)($r['tunggakan_bunga'] ?? 0);
        }

        $this->send(200, "Detail Data", [
            'pagination' => ['current_page' => $page, 'total_records' => (int)$total, 'total_pages' => ceil($total / $limit)],
            'data' => $rows
        ]);
    }

    private function send($status, $msg, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $msg, 'data' => $data]);
        exit;
    }
}