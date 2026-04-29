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
        $lastDayOfMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
        $effectiveDay = min($originalDay, $lastDayOfMonth);
        
        if ($effectiveDay == $lastDayOfMonth) {
            $dateString = "$year-$month-$effectiveDay";
            $dayOfWeek  = date('w', strtotime($dateString)); 
            if ($dayOfWeek == 0) { 
                $effectiveDay = $effectiveDay - 1; 
            }
        }
        return $effectiveDay;
    }

    /**
     * 1. REKAP UTAMA (Summary Per Tanggal)
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

        $sqlM1 = "SELECT no_rekening, baki_debet, DAY(tgl_jatuh_tempo) as tgl_ori 
                  FROM nominatif 
                  WHERE created BETWEEN :s1 AND :e1 
                  AND kolektibilitas = 'L' 
                  AND baki_debet > 0
                  AND hari_menunggak = 0"; 

        if ($kc) $sqlM1 .= " AND kode_cabang = :kc";

        $stmt1 = $this->pdo->prepare($sqlM1);
        $stmt1->bindValue(':s1', $s1); $stmt1->bindValue(':e1', $e1);
        if ($kc) $stmt1->bindValue(':kc', $kc);
        $stmt1->execute();
        $dataM1 = $stmt1->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $sqlCur = "SELECT no_rekening, baki_debet, hari_menunggak 
                   FROM nominatif 
                   WHERE created BETWEEN :s2 AND :e2";
        if ($kc) $sqlCur .= " AND kode_cabang = :kc";

        $stmt2 = $this->pdo->prepare($sqlCur);
        $stmt2->bindValue(':s2', $s2); $stmt2->bindValue(':e2', $e2);
        if ($kc) $stmt2->bindValue(':kc', $kc);
        $stmt2->execute();
        $dataCur = $stmt2->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

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
            'target_noa'=>0, 'target_os'=>0, 'lancar_noa'=>0, 'lancar_os'=>0, 
            'macet_noa'=>0,  'macet_os'=>0,  'lunas_noa'=>0,  'lunas_os'=>0, 
            'angsuran'=>0,   'total_bayar'=>0, 'persen'=>0
        ];

        foreach ($dataM1 as $norek => $row) {
            $tglOri = (int)$row['tgl_ori']; 
            if ($tglOri < 1 || $tglOri > 31) continue;

            $tglMap = $this->getMappedDay($tglOri, $curMonth, $curYear);
            $osTarget = (float)$row['baki_debet'];

            $report[$tglMap]['target_noa']++; $report[$tglMap]['target_os'] += $osTarget;
            $grandTotal['target_noa']++; $grandTotal['target_os'] += $osTarget;

            if (isset($dataCur[$norek])) {
                $osActual  = (float)$dataCur[$norek]['baki_debet'];
                $dpdActual = (int)$dataCur[$norek]['hari_menunggak'];

                if ($osActual <= 0) {
                    $report[$tglMap]['lunas_noa']++; $report[$tglMap]['lunas_os'] += $osTarget;
                    $grandTotal['lunas_noa']++; $grandTotal['lunas_os'] += $osTarget;
                } else {
                    if ($dpdActual == 0) {
                        $report[$tglMap]['lancar_noa']++; $report[$tglMap]['lancar_os'] += $osActual;
                        $grandTotal['lancar_noa']++; $grandTotal['lancar_os'] += $osActual;
                    } else {
                        $report[$tglMap]['macet_noa']++; $report[$tglMap]['macet_os'] += $osActual;
                        $grandTotal['macet_noa']++; $grandTotal['macet_os'] += $osActual;
                    }
                    if ($osTarget > $osActual) {
                        $bayar = $osTarget - $osActual;
                        $report[$tglMap]['angsuran'] += $bayar;
                        $grandTotal['angsuran'] += $bayar;
                    }
                }
            } else {
                $report[$tglMap]['lunas_noa']++; $report[$tglMap]['lunas_os'] += $osTarget;
                $grandTotal['lunas_noa']++; $grandTotal['lunas_os'] += $osTarget;
            }
        }

        foreach ($report as &$r) {
            $performance = $r['lancar_os'] + $r['lunas_os'] + $r['angsuran'];
            $r['total_bayar'] = $r['lunas_os'] + $r['angsuran'];
            if ($r['target_os'] > 0) $r['persen'] = round(($performance / $r['target_os']) * 100, 2);
        }

        $gtPerformance = $grandTotal['lancar_os'] + $grandTotal['lunas_os'] + $grandTotal['angsuran'];
        $grandTotal['total_bayar'] = $grandTotal['lunas_os'] + $grandTotal['angsuran'];
        if ($grandTotal['target_os'] > 0) $grandTotal['persen'] = round(($gtPerformance / $grandTotal['target_os']) * 100, 2);

        $this->send(200, "Sukses Rekap RR", [
            'meta' => ['m1' => $closing, 'cur' => $harian],
            'grand_total' => $grandTotal,
            'data' => array_values($report)
        ]);
    }

    /**
     * 2. DETAIL DATA
     * Menampilkan data Lancar & Menunggak
     */
    public function getDetailRepaymentRate($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;
        $kankas  = $b['kode_kankas'] ?? null; // Filter Kankas
        $ao      = $b['kode_ao'] ?? null;     // 🔥 Menangkap Filter AO
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

        // Filter Logic Status Akhir
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

        // Base Query Dengan Relasi Tabungan dan Kankas
        // 🔥 FIX: Tambah LEFT JOIN kankas
        $baseQuery = "FROM nominatif t1 
                      $joinType nominatif t2 ON t1.no_rekening = t2.no_rekening 
                          AND (t2.created BETWEEN :s2 AND :e2)
                      LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
                      LEFT JOIN tabungan tb ON t1.norek_tabungan = tb.no_rekening
                      LEFT JOIN kankas kn ON t1.kode_group1 = kn.kode_group1
                      WHERE (t1.created BETWEEN :s1 AND :e1)
                      AND t1.kolektibilitas = 'L' 
                      AND t1.baki_debet > 0
                      AND t1.hari_menunggak = 0 
                      AND DAY(t1.tgl_jatuh_tempo) IN ($daysStr)
                      $whereStatus";
        
        if ($kc) $baseQuery .= " AND t1.kode_cabang = :kc";
        if ($kankas) $baseQuery .= " AND t1.kode_group1 = :kankas"; 
        if ($ao) $baseQuery .= " AND t1.kode_group2 = :ao"; // 🔥 Terapkan query Filter AO

        // Count
        $stmtCnt = $this->pdo->prepare("SELECT COUNT(1) $baseQuery");
        $stmtCnt->bindValue(':s1', $s1); $stmtCnt->bindValue(':e1', $e1);
        $stmtCnt->bindValue(':s2', $s2); $stmtCnt->bindValue(':e2', $e2);
        if ($kc) $stmtCnt->bindValue(':kc', $kc);
        if ($kankas) $stmtCnt->bindValue(':kankas', $kankas); 
        if ($ao) $stmtCnt->bindValue(':ao', $ao); // 🔥 Bind nilai AO
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        // 🔥 FIX KANKAS: Ambil nama dari kn.deskripsi_group1 dengan alias kankas
        $cols = "t1.no_rekening, t1.nama_nasabah, 
                 t1.alamat, t1.hp as no_hp, 
                 COALESCE(kn.deskripsi_group1, t1.kode_group1) as kankas,
                 COALESCE(tb.saldo_akhir, 0) as tabungan,
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
        if ($kankas) $stmt->bindValue(':kankas', $kankas); 
        if ($ao) $stmt->bindValue(':ao', $ao); // 🔥 Bind nilai AO
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $osM1 = (float)$r['os_m1']; $osCur = (float)$r['os_curr']; $dpd = (int)$r['dpd_curr'];
            $totung = (float)$r['totung']; $tabungan = (float)$r['tabungan'];
            
            if ($osCur <= 0) { $r['status_ket'] = 'LUNAS'; }
            elseif ($dpd == 0) { $r['status_ket'] = 'LANCAR'; }
            else { $r['status_ket'] = 'MENUNGGAK'; }

            $r['os_m1']=$osM1; $r['os_curr']=$osCur; 
            $r['bayar_pokok'] = ($osM1 > $osCur) ? ($osM1 - $osCur) : 0;

            // LOGIC STATUS TABUNGAN (0.015 adalah 1.5%)
            if (($tabungan * 0.015) > $totung) {
                $r['status_tabungan'] = 'Aman';
            } else {
                $r['status_tabungan'] = 'Belum Aman';
            }
        }

        $this->send(200, "Detail Data RR", [
            'pagination' => ['current_page' => $page, 'total_records' => (int)$total, 'total_pages' => ceil($total / $limit)],
            'data' => $rows
        ]);
    }

    /**
     * 3. DETAIL LUNAS (REFINANCING CHECK)
     */
    public function getDetailLunasRR($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = $b['kode_kantor'] ?? null;
        $kankas  = $b['kode_kankas'] ?? null; // Filter Kankas
        $ao      = $b['kode_ao'] ?? null;     // 🔥 Menangkap Filter AO
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
            if ($this->getMappedDay($d, $month, $year) == $tglMap) $includedDays[] = $d;
        }
        if (empty($includedDays)) $includedDays = [$tglMap];
        $daysStr = implode(',', $includedDays);

        // 🔥 FIX: Tambah LEFT JOIN kankas
        $baseQuery = "FROM nominatif t1 
                      LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening 
                          AND (t2.created BETWEEN :s2 AND :e2)
                      LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
                      LEFT JOIN kankas kn ON t1.kode_group1 = kn.kode_group1
                      WHERE (t1.created BETWEEN :s1 AND :e1)
                      AND t1.kolektibilitas = 'L' 
                      AND t1.baki_debet > 0
                      AND t1.hari_menunggak = 0 
                      AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0)
                      AND DAY(t1.tgl_jatuh_tempo) IN ($daysStr)";

        if ($kc) $baseQuery .= " AND t1.kode_cabang = :kc";
        if ($kankas) $baseQuery .= " AND t1.kode_group1 = :kankas"; 
        if ($ao) $baseQuery .= " AND t1.kode_group2 = :ao"; // 🔥 Terapkan query Filter AO

        // Count
        $stmtCnt = $this->pdo->prepare("SELECT COUNT(1) $baseQuery");
        $stmtCnt->bindValue(':s1', $s1); $stmtCnt->bindValue(':e1', $e1);
        $stmtCnt->bindValue(':s2', $s2); $stmtCnt->bindValue(':e2', $e2);
        if ($kc) $stmtCnt->bindValue(':kc', $kc);
        if ($kankas) $stmtCnt->bindValue(':kankas', $kankas); 
        if ($ao) $stmtCnt->bindValue(':ao', $ao); // 🔥 Bind nilai AO
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        // 🔥 FIX KANKAS: Ambil nama dari kn.deskripsi_group1 dengan alias kankas
        $sqlData = "SELECT t1.nasabah_id, t1.no_rekening, t1.nama_nasabah, 
                           t1.alamat, t1.hp as no_hp, 
                           COALESCE(kn.deskripsi_group1, t1.kode_group1) as kankas,
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
        if ($kankas) $stmt->bindValue(':kankas', $kankas); 
        if ($ao) $stmt->bindValue(':ao', $ao); // 🔥 Bind nilai AO
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

/*
     * 4. REKAP RR (Summary M-1 vs Actual)
     * Kolom: Total OS, Total NOA.
     * Persen: (OS DPD=0 / Total OS) * 100
     */
    public function getRekapRr($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $userKode = $b['kode_kantor'] ?? '000'; 

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib diisi.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        $isPusat = ($userKode === '000');
        $groupByCol = $isPusat ? 'kode_cabang' : 'kode_group1';

        // 1. QUERY M-1 (CLOSING)
        // 🔥 FIX: all_noa diisi dengan jumlah rekening yang masuk kriteria Lancar/RR
        $sqlM1 = "SELECT 
                    $groupByCol as grp,
                    SUM(CASE WHEN COALESCE(hari_menunggak, 0) = 0 AND kolektibilitas = 'L' THEN 1 ELSE 0 END) as all_noa,
                    SUM(baki_debet) as all_os,
                    SUM(CASE WHEN COALESCE(hari_menunggak, 0) = 0 AND kolektibilitas = 'L' THEN baki_debet ELSE 0 END) as lancar_os
                  FROM nominatif
                  WHERE created BETWEEN :s1 AND :e1 
                  AND baki_debet > 0";
                  
        if (!$isPusat) $sqlM1 .= " AND kode_cabang = :kc";
        $sqlM1 .= " GROUP BY $groupByCol";

        $stmt1 = $this->pdo->prepare($sqlM1);
        $stmt1->bindValue(':s1', $s1); $stmt1->bindValue(':e1', $e1);
        if (!$isPusat) $stmt1->bindValue(':kc', $userKode);
        $stmt1->execute();
        $dataM1 = $stmt1->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // 2. QUERY ACTUAL (HARIAN)
        // 🔥 FIX: all_noa diisi dengan jumlah rekening yang masuk kriteria Lancar/RR
        $sqlCur = "SELECT 
                    $groupByCol as grp,
                    SUM(CASE WHEN COALESCE(hari_menunggak, 0) = 0 AND kolektibilitas = 'L' THEN 1 ELSE 0 END) as all_noa,
                    SUM(baki_debet) as all_os,
                    SUM(CASE WHEN COALESCE(hari_menunggak, 0) = 0 AND kolektibilitas = 'L' THEN baki_debet ELSE 0 END) as lancar_os
                   FROM nominatif
                   WHERE created BETWEEN :s2 AND :e2 
                   AND baki_debet > 0";

        if (!$isPusat) $sqlCur .= " AND kode_cabang = :kc";
        $sqlCur .= " GROUP BY $groupByCol";

        $stmt2 = $this->pdo->prepare($sqlCur);
        $stmt2->bindValue(':s2', $s2); $stmt2->bindValue(':e2', $e2);
        if (!$isPusat) $stmt2->bindValue(':kc', $userKode);
        $stmt2->execute();
        $dataCur = $stmt2->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // 3. FETCH MASTER NAMA
        $namaMap = [];
        if ($isPusat) {
            try {
                $stmtN = $this->pdo->query("SELECT kode_kantor, nama_kantor FROM kode_kantor");
                while ($r = $stmtN->fetch(PDO::FETCH_ASSOC)) $namaMap[$r['kode_kantor']] = $r['nama_kantor'];
            } catch (Exception $e) {}
        } else {
            try {
                $stmtN = $this->pdo->prepare("SELECT kode_group1, deskripsi_group1 FROM kankas WHERE kode_group1 LIKE ?");
                $stmtN->execute([$userKode . '%']);
                while ($r = $stmtN->fetch(PDO::FETCH_ASSOC)) $namaMap[$r['kode_group1']] = $r['deskripsi_group1'];
            } catch (Exception $e) {}
        }

        // 4. MENGGABUNGKAN DATA M-1 DAN ACTUAL
        $finalData = [];
        $allKeys = array_unique(array_merge(array_keys($dataM1), array_keys($dataCur)));

        $grandTotal = [
            'm1_all_noa' => 0, 'm1_all_os' => 0, 'm1_lancar_os' => 0,
            'cur_all_noa' => 0, 'cur_all_os' => 0, 'cur_lancar_os' => 0,
            'delta_noa' => 0, 'delta_os' => 0, 'delta_os_lancar' => 0,
            'm1_pct' => 0, 'cur_pct' => 0, 'delta_pct' => 0
        ];

        foreach ($allKeys as $grpId) {
            if (!$grpId) continue; 

            $nama = $namaMap[$grpId] ?? ($isPusat ? "Kc. $grpId" : "Kas $grpId");
            
            $m1  = $dataM1[$grpId] ?? ['all_noa'=>0, 'all_os'=>0, 'lancar_os'=>0];
            $cur = $dataCur[$grpId] ?? ['all_noa'=>0, 'all_os'=>0, 'lancar_os'=>0];

            $m1AllOs = (float)$m1['all_os'];
            $curAllOs = (float)$cur['all_os'];

            $m1LancarOs = (float)$m1['lancar_os'];
            $curLancarOs = (float)$cur['lancar_os'];

            // Kalkulasi persentase RR per Cabang/Kankas
            $m1Pct  = $m1AllOs > 0 ? ($m1LancarOs / $m1AllOs) * 100 : 0;
            $curPct = $curAllOs > 0 ? ($curLancarOs / $curAllOs) * 100 : 0;

            $finalData[] = [
                'kode' => $grpId,
                'nama' => $nama,
                
                'm1_all_noa'    => (int)$m1['all_noa'],
                'm1_all_os'     => $m1AllOs,
                'm1_lancar_os'  => $m1LancarOs, 
                'm1_pct'        => round($m1Pct, 2),
                
                'cur_all_noa'   => (int)$cur['all_noa'],
                'cur_all_os'    => $curAllOs,
                'cur_lancar_os' => $curLancarOs, 
                'cur_pct'       => round($curPct, 2),

                'delta_noa'       => (int)$cur['all_noa'] - (int)$m1['all_noa'],
                'delta_os'        => $curAllOs - $m1AllOs,
                'delta_os_lancar' => $curLancarOs - $m1LancarOs, 
                'delta_pct'       => round($curPct - $m1Pct, 2)
            ];

            // Akumulasi Grand Total
            $grandTotal['m1_all_noa']   += (int)$m1['all_noa'];
            $grandTotal['m1_all_os']    += $m1AllOs;
            $grandTotal['m1_lancar_os'] += $m1LancarOs;

            $grandTotal['cur_all_noa']   += (int)$cur['all_noa'];
            $grandTotal['cur_all_os']    += $curAllOs;
            $grandTotal['cur_lancar_os'] += $curLancarOs;
        }

        // Urutkan ASC by Kode
        usort($finalData, function($a, $b) { return strcmp($a['kode'], $b['kode']); });

        // Kalkulasi Persentase Grand Total
        $gtM1Pct  = $grandTotal['m1_all_os'] > 0 ? ($grandTotal['m1_lancar_os'] / $grandTotal['m1_all_os']) * 100 : 0;
        $gtCurPct = $grandTotal['cur_all_os'] > 0 ? ($grandTotal['cur_lancar_os'] / $grandTotal['cur_all_os']) * 100 : 0;

        $grandTotal['m1_pct']          = round($gtM1Pct, 2);
        $grandTotal['cur_pct']         = round($gtCurPct, 2);
        $grandTotal['delta_noa']       = $grandTotal['cur_all_noa'] - $grandTotal['m1_all_noa'];
        $grandTotal['delta_os']        = $grandTotal['cur_all_os'] - $grandTotal['m1_all_os'];
        $grandTotal['delta_os_lancar'] = $grandTotal['cur_lancar_os'] - $grandTotal['m1_lancar_os'];
        $grandTotal['delta_pct']       = round($gtCurPct - $gtM1Pct, 2);

        $this->send(200, "Sukses", [
            'meta' => [
                'level'      => $isPusat ? 'PUSAT' : 'CABANG',
                'label_kode' => $isPusat ? 'KODE CABANG' : 'KODE KANKAS',
                'label_nama' => $isPusat ? 'NAMA CABANG' : 'NAMA KANKAS'
            ],
            'grand_total' => $grandTotal,
            'data'        => $finalData
        ]);
    }

    /**
     * 1. REKAP UTAMA OTP BUCKET (Group by Tgl Jatuh Tempo + Migration)
     */
    public function getRekapOtpBucket($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = !empty($b['kode_kantor']) ? str_pad($b['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        $korwil  = !empty($b['korwil']) ? strtoupper($b['korwil']) : null;
        $typeB   = $b['type_bucket'] ?? 'fe_all'; // fe_all, 31-60, 61-90

        if (!$closing || !$harian) return $this->send(400, "Tanggal wajib diisi.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        $curTime  = strtotime($harian);
        $curMonth = date('n', $curTime);
        $curYear  = date('Y', $curTime);

        $kw_start = null; $kw_end = null;
        if ($korwil) {
            switch ($korwil) {
                case 'SEMARANG':   $kw_start = '001'; $kw_end = '007'; break;
                case 'SOLO':       $kw_start = '008'; $kw_end = '014'; break;
                case 'BANYUMAS':   $kw_start = '015'; $kw_end = '021'; break;
                case 'PEKALONGAN': $kw_start = '022'; $kw_end = '028'; break;
            }
        }

        // Tentukan Filter Bucket di M-1
        $whereBucket = "AND hari_menunggak BETWEEN 31 AND 90"; 
        if ($typeB === '31-60') $whereBucket = "AND hari_menunggak BETWEEN 31 AND 60";
        elseif ($typeB === '61-90') $whereBucket = "AND hari_menunggak BETWEEN 61 AND 90";

        // AMBIL DATA CLOSING (M1)
        $sqlM1 = "SELECT no_rekening, baki_debet, hari_menunggak as dpd_ori, DAY(tgl_jatuh_tempo) as tgl_ori 
                  FROM nominatif 
                  WHERE created BETWEEN :s1 AND :e1 
                  AND baki_debet > 0
                  $whereBucket"; 
        
        if ($kc && $kc !== '000') $sqlM1 .= " AND kode_cabang = :kc";
        elseif ($korwil && $kw_start && $kw_end) $sqlM1 .= " AND kode_cabang BETWEEN :kw_start AND :kw_end";

        $stmt1 = $this->pdo->prepare($sqlM1);
        $stmt1->bindValue(':s1', $s1); $stmt1->bindValue(':e1', $e1);
        if ($kc && $kc !== '000') $stmt1->bindValue(':kc', $kc);
        elseif ($korwil && $kw_start && $kw_end) { $stmt1->bindValue(':kw_start', $kw_start); $stmt1->bindValue(':kw_end', $kw_end); }
        $stmt1->execute();
        $dataM1 = $stmt1->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // AMBIL DATA HARIAN (CURRENT)
        $sqlCur = "SELECT no_rekening, baki_debet, hari_menunggak 
                   FROM nominatif 
                   WHERE created BETWEEN :s2 AND :e2";
        if ($kc && $kc !== '000') $sqlCur .= " AND kode_cabang = :kc";
        elseif ($korwil && $kw_start && $kw_end) $sqlCur .= " AND kode_cabang BETWEEN :kw_start AND :kw_end";

        $stmt2 = $this->pdo->prepare($sqlCur);
        $stmt2->bindValue(':s2', $s2); $stmt2->bindValue(':e2', $e2);
        if ($kc && $kc !== '000') $stmt2->bindValue(':kc', $kc);
        elseif ($korwil && $kw_start && $kw_end) { $stmt2->bindValue(':kw_start', $kw_start); $stmt2->bindValue(':kw_end', $kw_end); }
        $stmt2->execute();
        $dataCur = $stmt2->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // INISIALISASI BUCKET TANGGAL (1 - 31)
        $report = [];
        for ($i = 1; $i <= 31; $i++) {
            $report[$i] = [
                'tgl' => $i, 'm1_noa' => 0, 'm1_os' => 0,
                'btc_noa' => 0, 'btc_os' => 0, 'btc_pct' => 0,
                'backflow_noa' => 0, 'backflow_os' => 0, 'backflow_pct' => 0,
                'stay_noa' => 0, 'stay_os' => 0, 'stay_pct' => 0,
                'migrasi_noa' => 0, 'migrasi_os' => 0, 'migrasi_pct' => 0,
                'runoff_noa' => 0, 'runoff_os' => 0, 'runoff_pct' => 0
            ];
        }

        $grandTotal = [
            'm1_noa'=>0, 'm1_os'=>0, 'btc_noa'=>0, 'btc_os'=>0, 'btc_pct'=>0,
            'backflow_noa'=>0, 'backflow_os'=>0, 'backflow_pct'=>0,
            'stay_noa'=>0, 'stay_os'=>0, 'stay_pct'=>0,
            'migrasi_noa'=>0, 'migrasi_os'=>0, 'migrasi_pct'=>0,
            'runoff_noa'=>0, 'runoff_os'=>0, 'runoff_pct'=>0
        ];

        foreach ($dataM1 as $norek => $row) {
            $tglOri = (int)$row['tgl_ori']; 
            if ($tglOri < 1 || $tglOri > 31) continue;
            
            // Gunakan fungsi map tanggal yang Anda miliki
            $tglMap = $this->getMappedDay($tglOri, $curMonth, $curYear);
            $dpdOri = (int)$row['dpd_ori']; 
            $osM1   = (float)$row['baki_debet'];

            // Tentukan dynamic limit per nasabah
            if ($dpdOri >= 31 && $dpdOri <= 60) { $minB = 31; $maxB = 60; }
            else { $minB = 61; $maxB = 90; }

            $report[$tglMap]['m1_noa']++; $report[$tglMap]['m1_os'] += $osM1;
            $grandTotal['m1_noa']++; $grandTotal['m1_os'] += $osM1;

            if (!isset($dataCur[$norek])) {
                $report[$tglMap]['runoff_noa']++; $report[$tglMap]['runoff_os'] += $osM1;
                $grandTotal['runoff_noa']++; $grandTotal['runoff_os'] += $osM1;
            } else {
                $osCur  = (float)$dataCur[$norek]['baki_debet'];
                $dpdCur = (int)$dataCur[$norek]['hari_menunggak'];

                if ($osCur <= 0) {
                    $report[$tglMap]['runoff_noa']++; $report[$tglMap]['runoff_os'] += $osM1;
                    $grandTotal['runoff_noa']++; $grandTotal['runoff_os'] += $osM1;
                } else {
                    if ($dpdCur == 0) {
                        $report[$tglMap]['btc_noa']++; $report[$tglMap]['btc_os'] += $osM1;
                        $grandTotal['btc_noa']++; $grandTotal['btc_os'] += $osM1;
                    } elseif ($dpdCur > 0 && $dpdCur < $minB) {
                        $report[$tglMap]['backflow_noa']++; $report[$tglMap]['backflow_os'] += $osM1;
                        $grandTotal['backflow_noa']++; $grandTotal['backflow_os'] += $osM1;
                    } elseif ($dpdCur >= $minB && $dpdCur <= $maxB) {
                        $report[$tglMap]['stay_noa']++; $report[$tglMap]['stay_os'] += $osM1;
                        $grandTotal['stay_noa']++; $grandTotal['stay_os'] += $osM1;
                    } elseif ($dpdCur > $maxB) {
                        $report[$tglMap]['migrasi_noa']++; $report[$tglMap]['migrasi_os'] += $osM1;
                        $grandTotal['migrasi_noa']++; $grandTotal['migrasi_os'] += $osM1;
                    }
                }
            }
        }

        // Kalkulasi Persentase
        $calcPct = function($val, $tot) { return $tot > 0 ? round(($val / $tot) * 100, 2) : 0; };
        
        foreach ($report as &$r) {
            $r['btc_pct']      = $calcPct($r['btc_os'], $r['m1_os']);
            $r['backflow_pct'] = $calcPct($r['backflow_os'], $r['m1_os']);
            $r['stay_pct']     = $calcPct($r['stay_os'], $r['m1_os']);
            $r['migrasi_pct']  = $calcPct($r['migrasi_os'], $r['m1_os']);
            $r['runoff_pct']   = $calcPct($r['runoff_os'], $r['m1_os']);
        }

        $grandTotal['btc_pct']      = $calcPct($grandTotal['btc_os'], $grandTotal['m1_os']);
        $grandTotal['backflow_pct'] = $calcPct($grandTotal['backflow_os'], $grandTotal['m1_os']);
        $grandTotal['stay_pct']     = $calcPct($grandTotal['stay_os'], $grandTotal['m1_os']);
        $grandTotal['migrasi_pct']  = $calcPct($grandTotal['migrasi_os'], $grandTotal['m1_os']);
        $grandTotal['runoff_pct']   = $calcPct($grandTotal['runoff_os'], $grandTotal['m1_os']);

        return $this->send(200, "Sukses Rekap OTP Per Tanggal & Migration", [
            'meta' => ['m1' => $closing, 'cur' => $harian, 'type_bucket' => $typeB],
            'grand_total' => $grandTotal,
            'data' => array_values($report)
        ]);
    }

    /**
     * 2. DETAIL DATA OTP BUCKET (Filter per Tanggal Tagih + Status Migration)
     */
    public function getDetailOtpBucket($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        $kc      = !empty($b['kode_kantor']) ? str_pad($b['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        $korwil  = !empty($b['korwil']) ? strtoupper($b['korwil']) : null;
        $kankas  = $b['kode_kankas'] ?? null; 
        $ao      = $b['kode_ao'] ?? null;     
        
        $tglMap  = isset($b['tgl_tagih']) ? (int)$b['tgl_tagih'] : null;
        $typeB   = $b['type_bucket'] ?? 'fe_all'; 
        $status  = strtoupper($b['status'] ?? 'ALL'); 
        $page    = $b['page'] ?? 1;
        $limit   = $b['limit'] ?? 10;
        $offset  = ($page - 1) * $limit;

        if (!$closing || !$harian) return $this->send(400, "Data kurang lengkap.");

        [$s1, $e1] = $this->getDayRange($closing);
        [$s2, $e2] = $this->getDayRange($harian);

        // Logika Map Hari
        $daysStr = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31";
        if ($tglMap) {
            $curTime = strtotime($harian);
            $month = date('n', $curTime); $year = date('Y', $curTime);
            $includedDays = [];
            for ($d = 1; $d <= 31; $d++) {
                if ($this->getMappedDay($d, $month, $year) == $tglMap) {
                    $includedDays[] = $d;
                }
            }
            if (!empty($includedDays)) $daysStr = implode(',', $includedDays);
            else $daysStr = $tglMap;
        }

        $kw_start = null; $kw_end = null;
        if ($korwil) {
            switch ($korwil) {
                case 'SEMARANG':   $kw_start = '001'; $kw_end = '007'; break;
                case 'SOLO':       $kw_start = '008'; $kw_end = '014'; break;
                case 'BANYUMAS':   $kw_start = '015'; $kw_end = '021'; break;
                case 'PEKALONGAN': $kw_start = '022'; $kw_end = '028'; break;
            }
        }

        // Tentukan Filter Type Bucket Asal (M-1)
        $whereBucket = "AND t1.hari_menunggak BETWEEN 31 AND 90"; 
        if ($typeB === '31-60') $whereBucket = "AND t1.hari_menunggak BETWEEN 31 AND 60";
        elseif ($typeB === '61-90') $whereBucket = "AND t1.hari_menunggak BETWEEN 61 AND 90";

        // Filter Logic Roll Rate / Migration Status pakai SQL Logic Aman
        $joinType = "LEFT JOIN";
        $whereStatus = "";
        
        if ($status === 'RUNOFF') {
            $whereStatus = "AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0)";
        } elseif ($status === 'BTC') {
            $joinType = "JOIN";
            $whereStatus = "AND t2.baki_debet > 0 AND t2.hari_menunggak = 0";
        } elseif ($status === 'BACKFLOW') {
            $joinType = "JOIN";
            $whereStatus = "AND t2.baki_debet > 0 AND (
                (t1.hari_menunggak BETWEEN 31 AND 60 AND t2.hari_menunggak > 0 AND t2.hari_menunggak < 31) 
                OR 
                (t1.hari_menunggak BETWEEN 61 AND 90 AND t2.hari_menunggak > 0 AND t2.hari_menunggak < 61)
            )";
        } elseif ($status === 'STAY') {
            $joinType = "JOIN";
            $whereStatus = "AND t2.baki_debet > 0 AND (
                (t1.hari_menunggak BETWEEN 31 AND 60 AND t2.hari_menunggak BETWEEN 31 AND 60) 
                OR 
                (t1.hari_menunggak BETWEEN 61 AND 90 AND t2.hari_menunggak BETWEEN 61 AND 90)
            )";
        } elseif ($status === 'MIGRASI') {
            $joinType = "JOIN";
            $whereStatus = "AND t2.baki_debet > 0 AND (
                (t1.hari_menunggak BETWEEN 31 AND 60 AND t2.hari_menunggak > 60) 
                OR 
                (t1.hari_menunggak BETWEEN 61 AND 90 AND t2.hari_menunggak > 90)
            )";
        }

        // Base Query 
        $baseQuery = "FROM nominatif t1 
                      $joinType nominatif t2 ON t1.no_rekening = t2.no_rekening 
                          AND (t2.created BETWEEN :s2 AND :e2)
                      LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
                      LEFT JOIN tabungan tb ON t1.norek_tabungan = tb.no_rekening
                      LEFT JOIN kankas kn ON t1.kode_group1 = kn.kode_group1
                      WHERE (t1.created BETWEEN :s1 AND :e1)
                      AND t1.baki_debet > 0
                      AND DAY(t1.tgl_jatuh_tempo) IN ($daysStr)
                      $whereBucket
                      $whereStatus";
        
        if ($kc && $kc !== '000') $baseQuery .= " AND t1.kode_cabang = :kc";
        elseif ($korwil && $kw_start && $kw_end) $baseQuery .= " AND t1.kode_cabang BETWEEN :kw_start AND :kw_end";
        
        if ($kankas) $baseQuery .= " AND t1.kode_group1 = :kankas"; 
        if ($ao) $baseQuery .= " AND t1.kode_group2 = :ao"; 

        $bindParams = function($stmt) use ($s1, $e1, $s2, $e2, $kc, $korwil, $kw_start, $kw_end, $kankas, $ao) {
            $stmt->bindValue(':s1', $s1); $stmt->bindValue(':e1', $e1);
            $stmt->bindValue(':s2', $s2); $stmt->bindValue(':e2', $e2);
            if ($kc && $kc !== '000') $stmt->bindValue(':kc', $kc);
            elseif ($korwil && $kw_start && $kw_end) { $stmt->bindValue(':kw_start', $kw_start); $stmt->bindValue(':kw_end', $kw_end); }
            if ($kankas) $stmt->bindValue(':kankas', $kankas); 
            if ($ao) $stmt->bindValue(':ao', $ao); 
        };

        // Count Total Row
        $stmtCnt = $this->pdo->prepare("SELECT COUNT(1) $baseQuery");
        $bindParams($stmtCnt);
        $stmtCnt->execute();
        $total = $stmtCnt->fetchColumn();

        $cols = "t1.no_rekening, t1.nama_nasabah, t1.hari_menunggak as dpd_m1,
                 t1.alamat, t1.hp as no_hp, 
                 COALESCE(kn.deskripsi_group1, t1.kode_group1) as kankas,
                 COALESCE(tb.saldo_akhir, 0) as tabungan,
                 COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao,
                 t1.tgl_jatuh_tempo, t1.jml_pinjaman,
                 t1.baki_debet as os_m1, 
                 COALESCE(t2.baki_debet, 0) as os_curr, 
                 COALESCE(t2.hari_menunggak, 0) as dpd_curr,
                 t2.kolektibilitas,
                 t2.tunggakan_pokok,
                 t2.tunggakan_bunga,
                 t2.hari_menunggak_pokok as dpd_pokok,
                 t2.hari_menunggak_bunga as dpd_bunga,
                 (COALESCE(t2.tunggakan_pokok, 0) + COALESCE(t2.tunggakan_bunga, 0)) as totung";
        
        $sqlData = "SELECT $cols $baseQuery ORDER BY t1.baki_debet DESC LIMIT :lim OFFSET :off";
        $stmt = $this->pdo->prepare($sqlData);
        $bindParams($stmt);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $osM1 = (float)$r['os_m1']; $osCur = (float)$r['os_curr']; 
            $dpdCur = (int)$r['dpd_curr']; $dpdM1 = (int)$r['dpd_m1'];
            $totung = (float)$r['totung']; $tabungan = (float)$r['tabungan'];
            
            if ($dpdM1 >= 31 && $dpdM1 <= 60) { $cMin = 31; $cMax = 60; }
            else { $cMin = 61; $cMax = 90; }

            if ($osCur <= 0) { $r['status_ket'] = 'RUNOFF (LUNAS)'; }
            elseif ($dpdCur == 0) { $r['status_ket'] = 'BTC (LANCAR)'; }
            elseif ($dpdCur > 0 && $dpdCur < $cMin) { $r['status_ket'] = 'BACKFLOW'; }
            elseif ($dpdCur >= $cMin && $dpdCur <= $cMax) { $r['status_ket'] = 'STAY'; }
            elseif ($dpdCur > $cMax) { $r['status_ket'] = 'MIGRASI (MEMBURUK)'; }

            $r['os_m1']=$osM1; $r['os_curr']=$osCur; 
            $r['bayar_pokok'] = ($osM1 > $osCur) ? ($osM1 - $osCur) : 0;
            $r['status_tabungan'] = (($tabungan * 0.015) > $totung) ? 'Aman' : 'Belum Aman';
        }

        return $this->send(200, "Detail Data OTP By Tgl & Migration", [
            'pagination' => ['current_page' => $page, 'total_records' => (int)$total, 'total_pages' => ceil($total / $limit)],
            'data' => $rows
        ]);
    }

}
?>