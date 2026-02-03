<?php

class BucketFeController {

    private $pdo;
    private $visualBuckets = ['0', '1-7', '8-14', '15-21', '22-30', 'FE', 'BE'];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- HELPER 1: Range Tanggal ---
    private function getDayRange($date) {
        return [$date . ' 00:00:00', $date . ' 23:59:59'];
    }

    // --- HELPER 2: Bucket Logic ---
    private function getBucketLabel($dpd) {
        $d = (int)$dpd;
        if ($d <= 0)  return '0';
        if ($d <= 7)  return '1-7';
        if ($d <= 14) return '8-14';
        if ($d <= 21) return '15-21';
        if ($d <= 30) return '22-30';
        if ($d <= 90) return 'FE';
        return 'BE';
    }

    // --- HELPER 3: SQL Filter Detail (FIXED) ---
    // Perbaikan: Return 1=0 jika tidak match, biar data tidak bocor
    private function getBucketConditionSql($colName, $bucketLabel) {
        $lbl = trim((string)$bucketLabel); // Hapus spasi bahaya
        
        if ($lbl === '0')     return "$colName <= 0";
        if ($lbl === '1-7')   return "$colName BETWEEN 1 AND 7";
        if ($lbl === '8-14')  return "$colName BETWEEN 8 AND 14";
        if ($lbl === '15-21') return "$colName BETWEEN 15 AND 21";
        if ($lbl === '22-30') return "$colName BETWEEN 22 AND 30";
        if ($lbl === 'FE')    return "$colName BETWEEN 31 AND 90";
        if ($lbl === 'BE')    return "$colName > 90";
        
        // PENTING: Jika label ngawur, jangan return 1=1 (semua data), 
        // tapi return 1=0 (kosong) agar user tau ada error filter.
        return "1=0"; 
    }

    private function getVisualLabel($dpd) { return $this->getBucketLabel($dpd); }

    /**
     * ENDPOINT 1: REKAP MATRIKS (Fast & Balanced)
     */
    public function migrasiBucketOsc($input = null) {
        set_time_limit(300); ini_set('memory_limit', '2048M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib diisi.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // Fetch Data Raw (Hanya kolom penting)
        $sql = "SELECT no_rekening, baki_debet, hari_menunggak 
                FROM nominatif WHERE created BETWEEN ? AND ?";
        if ($kc) $sql .= " AND kode_cabang = ?";

        // M-1
        $p1 = [$s1, $e1]; if ($kc) $p1[] = $kc;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($p1);
        $dataM1 = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // Current
        $p2 = [$s2, $e2]; if ($kc) $p2[] = $kc;
        $stmt->execute($p2);
        $dataCur = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // Init Structure
        $summary = []; $matrix = []; $rowActiveTotals = [];
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
            $rowActiveTotals[$lbl] = 0;
            foreach (array_merge($this->visualBuckets, ['O']) as $t) {
                $matrix[$lbl][$t] = ['noa'=>0, 'os'=>0, 'angsuran'=>0, 'pelunasan'=>0];
            }
        }

        // Logic Mapping
        foreach ($dataM1 as $norek => $rowM1) {
            $osM1  = (float)$rowM1['baki_debet'];
            $dpdM1 = (int)$rowM1['hari_menunggak'];
            $from  = $this->getBucketLabel($dpdM1);

            $summary[$from]['noa_m1']++;
            $summary[$from]['os_m1'] += $osM1;
            $grandTotal['m1']['noa']++;
            $grandTotal['m1']['os'] += $osM1;

            $isLunas = true;
            if (isset($dataCur[$norek])) {
                $rowCur = $dataCur[$norek];
                $osCur  = (float)$rowCur['baki_debet'];
                
                // Logic Lunas diperketat: Jika saldo <= 0, anggap lunas
                if ($osCur > 0) {
                    $isLunas = false;
                    $dpdCur = (int)$rowCur['hari_menunggak'];
                    $to     = $this->getBucketLabel($dpdCur);

                    $matrix[$from][$to]['noa']++;
                    $matrix[$from][$to]['os'] += $osCur;
                    $rowActiveTotals[$from] += $osCur;

                    $grandTotal['buckets'][$to]['noa']++;
                    $grandTotal['buckets'][$to]['os'] += $osCur;
                }
                unset($dataCur[$norek]);
            }

            if ($isLunas) {
                $matrix[$from]['O']['noa']++;
                $matrix[$from]['O']['pelunasan'] += $osM1;
                $grandTotal['lunas']['noa']++;
                $grandTotal['lunas']['os'] += $osM1;
            }
        }

        // Calc Angsuran (Balancing)
        foreach ($this->visualBuckets as $f) {
            $osStart  = $summary[$f]['os_m1'];
            $osLunas  = $matrix[$f]['O']['pelunasan'];
            $osActive = $rowActiveTotals[$f];
            $netAngsuran = $osStart - ($osActive + $osLunas);

            foreach ($this->visualBuckets as $t) {
                if ($f == $t) $matrix[$f][$t]['angsuran'] = $netAngsuran;
            }
            $grandTotal['angsuran'] += $netAngsuran;
        }

        // Realisasi
        $realisasi = ['noa' => 0, 'os' => 0];
        foreach ($dataCur as $norek => $rowCur) {
            $osR = (float)$rowCur['baki_debet'];
            if ($osR > 0) {
                $realisasi['noa']++;
                $realisasi['os'] += $osR;
            }
        }

        // Final Totals
        $grandTotal['runoff_total']['os'] = $grandTotal['angsuran'] + $grandTotal['lunas']['os'];
        $grandTotal['runoff_total']['noa'] = $grandTotal['lunas']['noa'];

        unset($dataM1, $dataCur);

        $this->send(200, "Sukses", [
            'meta'      => ['kc'=>$kc, 'm1'=>$closing, 'cur'=>$harian],
            'summary_m1'=> $summary,
            'matrix'    => $matrix,
            'realisasi' => $realisasi,
            'grand_total' => $grandTotal
        ]);
    }

    /**
     * ENDPOINT 2: DETAIL DATA (Fixed Filter & Index Usage)
     */
    public function getMigrasiDetail($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;
        
        // Bersihkan Input String (Trim spasi)
        $fromLbl = isset($b['from_bucket']) ? trim((string)$b['from_bucket']) : '';
        $toLbl   = isset($b['to_bucket']) ? trim((string)$b['to_bucket']) : '';
        
        $page    = isset($b['page']) ? (int)$b['page'] : 1;
        $limit   = isset($b['limit']) ? (int)$b['limit'] : 10;
        $offset  = ($page - 1) * $limit;

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // Base Query
        $cols = "t2.no_rekening, t2.nama_nasabah, t2.baki_debet, t2.hari_menunggak, t2.kode_produk, 
                 t2.kolektibilitas, t2.tunggakan_pokok, t2.tunggakan_bunga";
        
        $sqlCount = ""; $sqlData = "";

        // 1. DETAIL REALISASI
        if ($fromLbl === 'REALISASI') {
            $baseWhere = "t2.created >= :s2 AND t2.created <= :e2 
                          AND NOT EXISTS (SELECT 1 FROM nominatif t1 WHERE t1.no_rekening = t2.no_rekening AND t1.created >= :s1 AND t1.created <= :e1)";
            
            if ($kc) $baseWhere .= " AND t2.kode_cabang = :kc";
            // Cek string kosong (bukan isset, karena '0' itu valid)
            if ($toLbl !== '') $baseWhere .= " AND " . $this->getBucketConditionSql("t2.hari_menunggak", $toLbl);

            $sqlCount = "SELECT COUNT(1) FROM nominatif t2 WHERE $baseWhere";
            $sqlData  = "SELECT $cols, 0 as os_m1, 0 as dpd_m1, 'New' as status_migrasi FROM nominatif t2 WHERE $baseWhere";

        // 2. DETAIL LUNAS
        } elseif ($toLbl === 'O') {
            // Kita pakai logic: Ada di M1, tapi (Tidak ada di Current OR Saldonya <= 0)
            // Tapi untuk performa SQL, EXISTS jauh lebih cepat drpd NOT IN
            $baseWhere = "t1.created >= :s1 AND t1.created <= :e1 
                          AND NOT EXISTS (
                              SELECT 1 FROM nominatif t2 
                              WHERE t2.no_rekening = t1.no_rekening 
                              AND t2.created >= :s2 AND t2.created <= :e2 
                              AND t2.baki_debet > 0
                          )";
            
            if ($kc) $baseWhere .= " AND t1.kode_cabang = :kc";
            if ($fromLbl !== '') $baseWhere .= " AND " . $this->getBucketConditionSql("t1.hari_menunggak", $fromLbl);

            $sqlCount = "SELECT COUNT(1) FROM nominatif t1 WHERE $baseWhere";
            // Map t1 columns as snapshot
            $sqlData  = "SELECT t1.no_rekening, t1.nama_nasabah, 0 as baki_debet, 0 as hari_menunggak, t1.kode_produk,
                                t1.kolektibilitas, t1.tunggakan_pokok, t1.tunggakan_bunga,
                                t1.baki_debet as os_m1, t1.hari_menunggak as dpd_m1, 'Lunas' as status_migrasi 
                         FROM nominatif t1 WHERE $baseWhere"; 

        // 3. DETAIL ACTIVE
        } else {
            $baseWhere = "t1.created >= :s1 AND t1.created <= :e1 
                          AND t2.created >= :s2 AND t2.created <= :e2 
                          AND t1.no_rekening = t2.no_rekening
                          AND t2.baki_debet > 0"; // Pastikan masih punya saldo
            
            if ($kc) $baseWhere .= " AND t1.kode_cabang = :kc";
            if ($fromLbl !== '') $baseWhere .= " AND " . $this->getBucketConditionSql("t1.hari_menunggak", $fromLbl);
            if ($toLbl !== '')   $baseWhere .= " AND " . $this->getBucketConditionSql("t2.hari_menunggak", $toLbl);

            $sqlCount = "SELECT COUNT(1) FROM nominatif t1 JOIN nominatif t2 ON t1.no_rekening=t2.no_rekening WHERE $baseWhere";
            $sqlData  = "SELECT $cols, t1.baki_debet as os_m1, t1.hari_menunggak as dpd_m1, 'Active' as status_migrasi 
                         FROM nominatif t1 JOIN nominatif t2 ON t1.no_rekening=t2.no_rekening WHERE $baseWhere";
        }

        // EXEC COUNT
        $stmtCnt = $this->pdo->prepare($sqlCount);
        $stmtCnt->bindValue(':s1', $s1); $stmtCnt->bindValue(':e1', $e1);
        $stmtCnt->bindValue(':s2', $s2); $stmtCnt->bindValue(':e2', $e2);
        if ($kc) $stmtCnt->bindValue(':kc', $kc);
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        // EXEC DATA (Sorted by OS Current Descending)
        $sqlData .= " ORDER BY " . ($toLbl === 'O' ? "t1.baki_debet" : "t2.baki_debet") . " DESC LIMIT :lim OFFSET :off";
        
        $stmt = $this->pdo->prepare($sqlData);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Visual Format
        foreach ($rows as &$r) {
            if ($r['status_migrasi'] === 'Active') {
                $f = $this->getVisualLabel($r['dpd_m1']);
                $t = $this->getVisualLabel($r['hari_menunggak']);
                $r['status_migrasi'] = "$f -> $t";
            }
            $r['baki_debet'] = (float)$r['baki_debet'];
            $r['os_m1'] = (float)$r['os_m1'];
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