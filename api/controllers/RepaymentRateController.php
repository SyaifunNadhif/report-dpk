<?php

class RepaymentRateController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function send($status, $msg, $data = []) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode(['status' => $status, 'message' => $msg, 'data' => $data]);
        exit;
    }

    private function getDayRange($date) {
        return [$date . ' 00:00:00', $date . ' 23:59:59'];
    }

    private function getMappedDay($originalDay, $month, $year) {
        $lastDayOfMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $effectiveDay = min($originalDay, $lastDayOfMonth);
        if ($effectiveDay == $lastDayOfMonth) {
            $dateString = "$year-$month-$effectiveDay";
            $dayOfWeek  = date('w', strtotime($dateString)); 
            if ($dayOfWeek == 0) { $effectiveDay = $effectiveDay - 1; }
        }
        return $effectiveDay;
    }

    /**
     * 1. REKAP UTAMA (Summary)
     */
    public function getRepaymentRate($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib diisi.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        $curTime  = strtotime($harian);
        $curMonth = date('n', $curTime);
        $curYear  = date('Y', $curTime);

        // A. TARGET (M-1)
        $sqlM1 = "SELECT no_rekening, baki_debet, DAY(tgl_realisasi) as tgl_ori 
                  FROM nominatif 
                  WHERE created BETWEEN :s1 AND :e1 
                  AND kolektibilitas = 'L' AND baki_debet > 0";
        if ($kc) $sqlM1 .= " AND kode_cabang = :kc";

        $stmt1 = $this->pdo->prepare($sqlM1);
        $stmt1->bindValue(':s1', $s1); $stmt1->bindValue(':e1', $e1);
        if ($kc) $stmt1->bindValue(':kc', $kc);
        $stmt1->execute();
        $dataM1 = $stmt1->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // B. ACTUAL (Current)
        $sqlCur = "SELECT no_rekening, baki_debet, hari_menunggak 
                   FROM nominatif 
                   WHERE created BETWEEN :s2 AND :e2";
        if ($kc) $sqlCur .= " AND kode_cabang = :kc";

        $stmt2 = $this->pdo->prepare($sqlCur);
        $stmt2->bindValue(':s2', $s2); $stmt2->bindValue(':e2', $e2);
        if ($kc) $stmt2->bindValue(':kc', $kc);
        $stmt2->execute();
        $dataCur = $stmt2->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // C. MAPPING
        $report = [];
        for ($i = 1; $i <= 31; $i++) {
            $report[$i] = [
                'tgl' => $i,
                'target_noa' => 0, 'target_os' => 0,
                'lancar_noa' => 0, 'lancar_os' => 0, 
                'macet_noa'  => 0, 'macet_os'  => 0,
                'lunas_noa'  => 0, 'lunas_os'  => 0,
                'angsuran'   => 0, 'total_bayar'=> 0, 'persen' => 0
            ];
        }
        $grandTotal = [
            'target_noa'=>0, 'target_os'=>0, 
            'lancar_noa'=>0, 'lancar_os'=>0, 
            'macet_noa'=>0,  'macet_os'=>0, 
            'lunas_noa'=>0,  'lunas_os'=>0, 
            'angsuran'=>0,   'total_bayar'=>0, 'persen'=>0
        ];

        foreach ($dataM1 as $norek => $row) {
            $tglOri = (int)$row['tgl_ori'];
            if ($tglOri < 1 || $tglOri > 31) continue;

            $tglMap = $this->getMappedDay($tglOri, $curMonth, $curYear);
            $osTarget = (float)$row['baki_debet'];

            $report[$tglMap]['target_noa']++;
            $report[$tglMap]['target_os'] += $osTarget;
            
            $grandTotal['target_noa']++;
            $grandTotal['target_os'] += $osTarget;

            if (isset($dataCur[$norek])) {
                $osActual  = (float)$dataCur[$norek]['baki_debet'];
                $dpdActual = (int)$dataCur[$norek]['hari_menunggak'];

                if ($osActual <= 0) {
                    // LUNAS
                    $report[$tglMap]['lunas_noa']++;
                    $report[$tglMap]['lunas_os'] += $osTarget;
                    $grandTotal['lunas_noa']++; 
                    $grandTotal['lunas_os'] += $osTarget;
                } else {
                    if ($dpdActual == 0) {
                        // LANCAR
                        $report[$tglMap]['lancar_noa']++;
                        $report[$tglMap]['lancar_os'] += $osActual;
                        $grandTotal['lancar_noa']++;
                        $grandTotal['lancar_os'] += $osActual;
                    } else {
                        // DITAGIH
                        $report[$tglMap]['macet_noa']++;
                        $report[$tglMap]['macet_os'] += $osActual;
                        $grandTotal['macet_noa']++;
                        $grandTotal['macet_os'] += $osActual;
                    }
                    // ANGSURAN
                    if ($osTarget > $osActual) {
                        $bayar = $osTarget - $osActual;
                        $report[$tglMap]['angsuran'] += $bayar;
                        $grandTotal['angsuran'] += $bayar;
                    }
                }
            } else {
                // LUNAS (Hilang)
                $report[$tglMap]['lunas_noa']++;
                $report[$tglMap]['lunas_os'] += $osTarget;
                $grandTotal['lunas_noa']++;
                $grandTotal['lunas_os'] += $osTarget;
            }
        }

        foreach ($report as &$r) {
            $performance = $r['lancar_os'] + $r['lunas_os'] + $r['angsuran'];
            $r['total_bayar'] = $r['lunas_os'] + $r['angsuran'];
            if ($r['target_os'] > 0) {
                $r['persen'] = round(($performance / $r['target_os']) * 100, 2);
            }
        }

        $gtPerformance = $grandTotal['lancar_os'] + $grandTotal['lunas_os'] + $grandTotal['angsuran'];
        $grandTotal['total_bayar'] = $grandTotal['lunas_os'] + $grandTotal['angsuran'];
        
        if ($grandTotal['target_os'] > 0) {
            $grandTotal['persen'] = round(($gtPerformance / $grandTotal['target_os']) * 100, 2);
        }

        $this->send(200, "Sukses Rekap RR", [
            'meta' => ['m1' => $closing, 'cur' => $harian],
            'grand_total' => $grandTotal,
            'data' => array_values($report)
        ]);
    }

    /**
     * 2. DETAIL DATA (NORMAL: Target, Lancar, Ditagih)
     * -> JOIN AO via kode_group2
     */
    public function getDetailRepaymentRate($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;
        $tglMap  = isset($b['tgl_tagih']) ? (int)$b['tgl_tagih'] : null;
        $status  = $b['status'] ?? 'ALL';
        $page    = $b['page'] ?? 1;
        $limit   = $b['limit'] ?? 10;
        $offset  = ($page - 1) * $limit;

        if (!$closing || !$harian || !$tglMap) return $this->send(400, "Data kurang lengkap.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        $curTime  = strtotime($harian);
        $month = date('n', $curTime); $year = date('Y', $curTime);
        
        $includedDays = [];
        for ($d = 1; $d <= 31; $d++) {
            if ($this->getMappedDay($d, $month, $year) == $tglMap) {
                $includedDays[] = $d;
            }
        }
        if (empty($includedDays)) $includedDays = [$tglMap];
        $daysStr = implode(',', $includedDays);

        // Filter Logic
        $joinType = "LEFT JOIN";
        $whereStatus = "";
        if ($status === 'LUNAS') {
            $whereStatus = "AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0)";
        } elseif ($status === 'LANCAR') {
            $joinType = "JOIN";
            $whereStatus = "AND t2.baki_debet > 0 AND t2.hari_menunggak = 0";
        } elseif ($status === 'MENUNGGAK') {
            $joinType = "JOIN";
            $whereStatus = "AND t2.baki_debet > 0 AND t2.hari_menunggak > 0";
        }

        $baseQuery = "FROM nominatif t1 
                      $joinType nominatif t2 ON t1.no_rekening = t2.no_rekening 
                          AND (t2.created BETWEEN :s2 AND :e2)
                      LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
                      WHERE (t1.created BETWEEN :s1 AND :e1)
                      AND t1.kolektibilitas = 'L' AND t1.baki_debet > 0
                      AND DAY(t1.tgl_realisasi) IN ($daysStr) 
                      $whereStatus";
        
        if ($kc) $baseQuery .= " AND t1.kode_cabang = :kc";

        // Count
        $stmtCnt = $this->pdo->prepare("SELECT COUNT(1) $baseQuery");
        $stmtCnt->bindValue(':s1', $s1); $stmtCnt->bindValue(':e1', $e1);
        $stmtCnt->bindValue(':s2', $s2); $stmtCnt->bindValue(':e2', $e2);
        if ($kc) $stmtCnt->bindValue(':kc', $kc);
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        // Data (SELECT Nama AO)
        $cols = "t1.no_rekening, t1.nama_nasabah, 
                 COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao,
                 t1.tgl_jatuh_tempo, t1.jml_pinjaman,
                 t1.baki_debet as os_m1, 
                 COALESCE(t2.baki_debet, 0) as os_curr, 
                 COALESCE(t2.hari_menunggak, 0) as dpd_curr,
                 (COALESCE(t2.tunggakan_pokok, 0) + COALESCE(t2.tunggakan_bunga, 0)) as totung";
        
        $sqlData = "SELECT $cols $baseQuery ORDER BY t1.baki_debet DESC LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sqlData);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $osM1 = (float)$r['os_m1']; $osCur = (float)$r['os_curr']; $dpd = (int)$r['dpd_curr'];
            
            if ($osCur <= 0) { $r['status_ket'] = 'LUNAS'; }
            elseif ($dpd == 0) { $r['status_ket'] = 'LANCAR'; }
            else { $r['status_ket'] = 'MENUNGGAK'; }

            $r['os_m1']=$osM1; $r['os_curr']=$osCur; 
            $r['bayar_pokok'] = ($osM1 > $osCur) ? ($osM1 - $osCur) : 0;
        }

        $this->send(200, "Detail Data RR", [
            'pagination' => ['current_page' => $page, 'total_records' => (int)$total, 'total_pages' => ceil($total / $limit)],
            'data' => $rows
        ]);
    }

    /**
     * 3. DETAIL LUNAS (REFINANCING CHECK)
     * -> JOIN AO via kode_group2
     */
    public function getDetailLunasRR($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;
        $tglMap  = isset($b['tgl_tagih']) ? (int)$b['tgl_tagih'] : null;
        $page    = $b['page'] ?? 1;
        $limit   = $b['limit'] ?? 10;
        $offset  = ($page - 1) * $limit;

        if (!$closing || !$harian || !$tglMap) return $this->send(400, "Parameter kurang.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        $curTime  = strtotime($harian);
        $month = date('n', $curTime); $year = date('Y', $curTime);

        $includedDays = [];
        for ($d = 1; $d <= 31; $d++) {
            if ($this->getMappedDay($d, $month, $year) == $tglMap) {
                $includedDays[] = $d;
            }
        }
        if (empty($includedDays)) $includedDays = [$tglMap];
        $daysStr = implode(',', $includedDays);

        $baseQuery = "FROM nominatif t1 
                      LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening 
                          AND (t2.created BETWEEN :s2 AND :e2)
                      LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
                      WHERE (t1.created BETWEEN :s1 AND :e1)
                      AND t1.kolektibilitas = 'L' AND t1.baki_debet > 0
                      AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0)
                      AND DAY(t1.tgl_realisasi) IN ($daysStr)";

        if ($kc) $baseQuery .= " AND t1.kode_cabang = :kc";

        // Count
        $stmtCnt = $this->pdo->prepare("SELECT COUNT(1) $baseQuery");
        $stmtCnt->bindValue(':s1', $s1); $stmtCnt->bindValue(':e1', $e1);
        $stmtCnt->bindValue(':s2', $s2); $stmtCnt->bindValue(':e2', $e2);
        if ($kc) $stmtCnt->bindValue(':kc', $kc);
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        // Data (Select Nama AO)
        $sqlData = "SELECT t1.nasabah_id, t1.no_rekening, t1.nama_nasabah, 
                           COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao,
                           t1.jml_pinjaman as plafon_lama, 
                           t1.baki_debet as os_lunas, t1.tgl_realisasi as tgl_lama
                    $baseQuery 
                    ORDER BY t1.baki_debet DESC 
                    LIMIT :lim OFFSET :off";

        $stmt = $this->pdo->prepare($sqlData);
        $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
        $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
        if ($kc) $stmt->bindValue(':kc', $kc);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check Refinancing
        $closingDateStr = date('Y-m-d', strtotime($closing));
        $harianDateStr  = date('Y-m-d', strtotime($harian));

        $sqlCheck = "SELECT no_rekening, jml_pinjaman, tgl_realisasi 
                     FROM nominatif 
                     WHERE created BETWEEN :s2 AND :e2
                     AND nasabah_id = :nid
                     AND no_rekening != :old_rek
                     AND tgl_realisasi > :closing_date 
                     AND tgl_realisasi <= :harian_date
                     LIMIT 1";
        $stmtCheck = $this->pdo->prepare($sqlCheck);

        foreach ($rows as &$r) {
            $stmtCheck->bindValue(':s2', $s2);
            $stmtCheck->bindValue(':e2', $e2);
            $stmtCheck->bindValue(':nid', $r['nasabah_id']);
            $stmtCheck->bindValue(':old_rek', $r['no_rekening']);
            $stmtCheck->bindValue(':closing_date', $closingDateStr);
            $stmtCheck->bindValue(':harian_date', $harianDateStr);
            $stmtCheck->execute();
            $newLoan = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($newLoan) {
                $r['status_lunas'] = 'REFINANCING / Top Up';
                $r['rek_baru']     = $newLoan['no_rekening'];
                $r['plafond_baru'] = $newLoan['jml_pinjaman'];
                $r['tgl_baru']     = $newLoan['tgl_realisasi'];
            } else {
                $r['status_lunas'] = 'PROSPEK (PELUNASAN)';
                $r['rek_baru']     = '-';
                $r['plafond_baru'] = 0;
                $r['tgl_baru']     = '-';
            }
        }

        $this->send(200, "Detail Lunas RR", [
            'pagination' => ['current_page' => $page, 'total_records' => (int)$total, 'total_pages' => ceil($total / $limit)],
            'data' => $rows
        ]);
    }
}
?>