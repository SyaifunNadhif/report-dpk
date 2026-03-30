<?php

// require_once __DIR__ . '/../helpers/response.php';

class MonevController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * =================================================================
     * 1. FUNGSI GET DATA KOMITMEN & HEADER (Buat Tampilan Awal Form)
     * =================================================================
     */
    public function getMonevData($input) {
        $tahun = $input['tahun'] ?? date('Y');
        $bulan = $input['bulan'] ?? date('m');
        $kode_kantor = $input['kode_kantor'] ?? '000';

        try {
            // A. Cek Setting Akses
            $sqlAkses = "SELECT w1_open, w2_open, w3_open, w4_open FROM monev_setting_akses WHERE tahun = ? AND bulan = ?";
            $stmtAkses = $this->pdo->prepare($sqlAkses);
            $stmtAkses->execute([$tahun, $bulan]);
            $akses = $stmtAkses->fetch(PDO::FETCH_ASSOC) ?: ['w1_open'=>0, 'w2_open'=>0, 'w3_open'=>0, 'w4_open'=>0];

            // B. Cek Header Monev
            $sqlHeader = "SELECT * FROM monev_header WHERE tahun = ? AND bulan = ? AND kode_kantor = ?";
            $stmtH = $this->pdo->prepare($sqlHeader);
            $stmtH->execute([$tahun, $bulan, $kode_kantor]);
            $header = $stmtH->fetch(PDO::FETCH_ASSOC);

            // C. Tarik Data Komitmen dari Database
            $komitmen = [];
            if ($header) {
                $sqlDetail = "SELECT kode_indikator, komit_w1, komit_w2, komit_w3, komit_w4 FROM monev_detail WHERE header_id = ?";
                $stmtD = $this->pdo->prepare($sqlDetail);
                $stmtD->execute([$header['id']]);
                
                while ($row = $stmtD->fetch(PDO::FETCH_ASSOC)) {
                    $komitmen[$row['kode_indikator']] = [
                        'w1' => $row['komit_w1'] ?? 0, 
                        'w2' => $row['komit_w2'] ?? 0,
                        'w3' => $row['komit_w3'] ?? 0,
                        'w4' => $row['komit_w4'] ?? 0,
                    ];
                }
            }

            // D. Kirim ke FE (Murni Header & Komitmen Saja)
            $data = [
                'setting_akses' => $akses,
                'header'        => $header ?: null,
                'komitmen'      => $komitmen 
            ];

            sendResponse(200, "Berhasil memuat data Komitmen MONEV", $data);

        } catch (PDOException $e) {
            error_log("Error getMonevData: " . $e->getMessage());
            sendResponse(500, "Gagal memuat data: " . $e->getMessage(), null);
        }
    }


    /**
     * =================================================================
     * 2. FUNGSI: SIMPAN KOMITMEN 
     * =================================================================
     */
    public function saveMonev($input, $userToken) {
        $userId = $userToken['id'] ?? null;
        if (!$userId) sendResponse(401, "Token tidak valid.");

        $sqlUser = "SELECT * FROM users WHERE id = :id"; 
        $stmtU = $this->pdo->prepare($sqlUser);
        $stmtU->execute(['id' => $userId]);
        $user = $stmtU->fetch(PDO::FETCH_ASSOC);

        if (!$user) sendResponse(401, "User tidak ditemukan.");

        $jabatan = strtolower($user['job_position'] ?? '');
        $isKacab = (strpos($jabatan, 'kepala cabang') !== false || strpos($jabatan, 'pemimpin cabang') !== false || strpos($jabatan, 'kacab') !== false);
        $isAdmin = (strtolower($user['role'] ?? '') === 'admin');

        if (!$isKacab && !$isAdmin) sendResponse(403, "Akses Ditolak! Hanya Kacab yang berhak.");

        $kode_kantor = $input['kode_kantor'] ?? '';
        if (!$isAdmin && $user['kode'] !== $kode_kantor) sendResponse(403, "Akses Ditolak ke cabang lain.");

        $tahun        = (int) $input['tahun'];
        $bulan        = $input['bulan'];
        $minggu       = (int) ($input['minggu'] ?? 1); 
        $status_input = $input['status_input'] ?? 'Draft';

        $sqlAkses = "SELECT * FROM monev_setting_akses WHERE tahun = :tahun AND bulan = :bulan";
        $stmtAkses = $this->pdo->prepare($sqlAkses);
        $stmtAkses->execute(['tahun' => $tahun, 'bulan' => $bulan]);
        $akses = $stmtAkses->fetch(PDO::FETCH_ASSOC);

        if (!$akses) sendResponse(400, "Form belum dibuka oleh Pusat.");
        
        $kolom_open = "w{$minggu}_open";
        if (!isset($akses[$kolom_open]) || $akses[$kolom_open] != 1) sendResponse(400, "Form Minggu ke-{$minggu} ditutup.");

        try {
            $this->pdo->beginTransaction();

            $pejabat = $input['pejabat'] ?? [];
            $kacab = !empty($pejabat['kepala_cabang']) ? $pejabat['kepala_cabang'] : $user['full_name'];
            $kabid_pem = $pejabat['kabid_pemasaran'] ?? '';
            $kabid_ops = $pejabat['kabid_operasional'] ?? '';

            $col_kacab = "pejabat_kacab_w{$minggu}";
            $col_pem   = "pejabat_pemasaran_w{$minggu}";
            $col_ops   = "pejabat_operasional_w{$minggu}";

            $sqlHeader = "
                INSERT INTO monev_header (kode_kantor, tahun, bulan, {$col_kacab}, {$col_pem}, {$col_ops}, status_input)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    {$col_kacab} = VALUES({$col_kacab}),
                    {$col_pem} = VALUES({$col_pem}),
                    {$col_ops} = VALUES({$col_ops}),
                    status_input = VALUES(status_input),
                    updated_at = CURRENT_TIMESTAMP
            ";
            $stmtH = $this->pdo->prepare($sqlHeader);
            $stmtH->execute([$kode_kantor, $tahun, $bulan, $kacab, $kabid_pem, $kabid_ops, $status_input]);

            $sqlGetId = "SELECT id FROM monev_header WHERE kode_kantor = ? AND tahun = ? AND bulan = ?";
            $stmtId = $this->pdo->prepare($sqlGetId);
            $stmtId->execute([$kode_kantor, $tahun, $bulan]);
            $headerId = $stmtId->fetchColumn();

            $details = $input['detail_data'] ?? [];
            $kolom_komit = "komit_w{$minggu}"; 

            $sqlDetail = "
                INSERT INTO monev_detail (header_id, kode_indikator, {$kolom_komit})
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE {$kolom_komit} = VALUES({$kolom_komit})
            ";
            $stmtD = $this->pdo->prepare($sqlDetail);

            foreach ($details as $kode_indikator => $nilai) {
                $clean_nilai = $nilai;
                if (!preg_match('/[a-zA-Z]/', $nilai) && $nilai !== '') {
                    $clean_nilai = str_replace('.', '', $nilai);
                    $clean_nilai = str_replace(',', '.', $clean_nilai);
                }
                $stmtD->execute([$headerId, $kode_indikator, $clean_nilai]);
            }

            $this->pdo->commit();
            sendResponse(200, "Data MONEV Minggu ke-{$minggu} berhasil disimpan!", null);

        } catch (Exception $e) {
            $this->pdo->rollBack();
            sendResponse(500, "Gagal menyimpan data: " . $e->getMessage(), null);
        }
    }

    /**
     * =================================================================
     * FUNGSI MANDIRI 1: GET REALISASI KREDIT (RBB = TOTAL KOMITMEN SEMENTARA)
     * =================================================================
     */
    public function getRealisasiKredit($input) {
        $tahun = $input['tahun'];
        $bulan = $input['bulan'];
        $kode_kantor = $input['kode_kantor'];
        $minggu_req = isset($input['minggu']) ? (int) $input['minggu'] : null;

        // 1. TARIK KOMITMEN SAJA TANPA JOIN TABEL RBB
        $komitmen = [];
        $sqlKomit = "
            SELECT d.kode_indikator, d.komit_w1, d.komit_w2, d.komit_w3, d.komit_w4 
            FROM monev_detail d
            JOIN monev_header h ON d.header_id = h.id
            WHERE h.tahun = ? AND h.bulan = ? AND h.kode_kantor = ?
              AND d.kode_indikator IN ('10601', '0005', '0006')
        ";
        $stmtK = $this->pdo->prepare($sqlKomit);
        $stmtK->execute([$tahun, $bulan, $kode_kantor]);
        while ($row = $stmtK->fetch(PDO::FETCH_ASSOC)) {
            // 🔥 TRIK SEMENTARA: RBB = W1 + W2 + W3 + W4 🔥
            $w1 = (float) ($row['komit_w1'] ?? 0);
            $w2 = (float) ($row['komit_w2'] ?? 0);
            $w3 = (float) ($row['komit_w3'] ?? 0);
            $w4 = (float) ($row['komit_w4'] ?? 0);
            
            $row['rbb_bulan'] = $w1 + $w2 + $w3 + $w4; 
            $komitmen[$row['kode_indikator']] = $row;
        }

        // 2. SIAPKAN KERANJANG REALISASI
        $data = [];
        $indikator_kredit = [
            '10601' => 'Baki Debet',
            '0005'  => 'Pencairan Kredit (Rp)',
            '0006'  => 'Jumlah Rekening (NOA)'
        ]; 
        
        foreach ($indikator_kredit as $kode => $nama) {
            $data[$kode] = ['nama_indikator' => $nama]; 
            if (!$minggu_req) {
                $data[$kode]['real_bln']   = 0; 
                $data[$kode]['rbb_bulan']  = 0; 
                $data[$kode]['persen_bln'] = 0; 
            }
        }

        $start_w = $minggu_req ? $minggu_req : 1;
        $end_w   = $minggu_req ? $minggu_req : 4;

        for ($w = $start_w; $w <= $end_w; $w++) {
            $tgl = $this->getRentangTanggalMinggu($tahun, $bulan, $w); 
            $start = $tgl['start'];
            $end   = $tgl['end'];

            $today = date('Y-m-d');
            if ($end > $today) {
                $end = $today; 
            }

            $data['10601']["komit_w{$w}"] = (float) ($komitmen['10601']["komit_w{$w}"] ?? 0);
            $data['0005']["komit_w{$w}"]  = (float) ($komitmen['0005']["komit_w{$w}"] ?? 0);
            $data['0006']["komit_w{$w}"]  = (int)   ($komitmen['0006']["komit_w{$w}"] ?? 0);

            try {
                $sqlP = "SELECT SUM(COALESCE(realisasi_pokok, 0)) AS total_rp, COUNT(DISTINCT no_rekening) AS total_noa 
                         FROM update_realisasi_kredit WHERE kode_kantor = :cabang AND tanggal_realisasi BETWEEN :start AND :end";
                $stmtP = $this->pdo->prepare($sqlP);
                $stmtP->execute(['cabang' => $kode_kantor, 'start' => $start, 'end' => $end]);
                $resP = $stmtP->fetch(PDO::FETCH_ASSOC);

                $rp  = (float) ($resP['total_rp'] ?? 0);
                $noa = (int) ($resP['total_noa'] ?? 0);

                $data['0005']["real_w{$w}"] = $rp;
                $data['0006']["real_w{$w}"] = $noa;
                $data['0005']["persen_w{$w}"] = $this->hitungPersen($rp, $data['0005']["komit_w{$w}"]);
                $data['0006']["persen_w{$w}"] = $this->hitungPersen($noa, $data['0006']["komit_w{$w}"]);
                
                if (!$minggu_req) {
                    $data['0005']['real_bln'] += $rp;
                    $data['0006']['real_bln'] += $noa;
                }

                $sqlB = "SELECT SUM(COALESCE(baki_debet, 0)) AS baki_debet FROM nominatif 
                         WHERE kode_cabang = :cabang AND created = :end_date";
                $stmtB = $this->pdo->prepare($sqlB);
                $stmtB->execute(['cabang' => $kode_kantor, 'end_date' => $end]);
                $resB = $stmtB->fetch(PDO::FETCH_ASSOC);

                $baki = (float) ($resB['baki_debet'] ?? 0);

                $data['10601']["real_w{$w}"] = $baki;
                $data['10601']["persen_w{$w}"] = $this->hitungPersen($baki, $data['10601']["komit_w{$w}"]);

            } catch (Exception $e) { error_log("Error Realisasi Kredit M{$w}: " . $e->getMessage()); }
        }

        if (!$minggu_req) {
            $today = date('Y-m-d');
            $last_day = date('Y-m-t', strtotime("{$tahun}-{$bulan}-01"));
            
            $db_end = ($last_day > $today) ? $today : $last_day;

            try {
                $sqlB_Bln = "SELECT SUM(COALESCE(baki_debet, 0)) AS baki_debet FROM nominatif 
                             WHERE kode_cabang = :cabang AND created = :end_date";
                $stmtB_Bln = $this->pdo->prepare($sqlB_Bln);
                $stmtB_Bln->execute(['cabang' => $kode_kantor, 'end_date' => $db_end]);
                $resB_Bln = $stmtB_Bln->fetch(PDO::FETCH_ASSOC);

                $data['10601']['real_bln'] = (float) ($resB_Bln['baki_debet'] ?? 0);

            } catch (Exception $e) { error_log("Error Total Baki Debet Bln: " . $e->getMessage()); }

            // 🔥 HITUNG % CAPAIAN BULANAN (PAKAI RBB DUMMY) 🔥
            foreach ($indikator_kredit as $kode => $nama) {
                $rbb = (float) ($komitmen[$kode]['rbb_bulan'] ?? 0);
                $data[$kode]['rbb_bulan']  = $rbb;
                $data[$kode]['persen_bln'] = $this->hitungPersen($data[$kode]['real_bln'], $rbb);
            }
        }

        $pesan = $minggu_req ? "Berhasil memuat Realisasi Kredit (Minggu ke-{$minggu_req})" : "Berhasil memuat Realisasi Kredit (Full Bulan)";
        sendResponse(200, $pesan, $data);
    }

    /**
     * =================================================================
     * FUNGSI MANDIRI 2: GET REALISASI DPK (RBB = TOTAL KOMITMEN SEMENTARA)
     * =================================================================
     */
    public function getRealisasiDPK($input) {
        $tahun = $input['tahun'];
        $bulan = $input['bulan'];
        $kode_kantor = $input['kode_kantor'];
        $minggu_req = isset($input['minggu']) ? (int) $input['minggu'] : null;

        // 1. TARIK KOMITMEN KHUSUS DPK SAJA TANPA JOIN TABEL RBB
        $komitmen = [];
        $sqlKomit = "
            SELECT d.kode_indikator, d.komit_w1, d.komit_w2, d.komit_w3, d.komit_w4 
            FROM monev_detail d
            JOIN monev_header h ON d.header_id = h.id
            WHERE h.tahun = ? AND h.bulan = ? AND h.kode_kantor = ?
              AND d.kode_indikator IN ('0007', '0008', '0009', '0010', '0011', '0012', '10602', '10603', '10604', '0013')
        ";
        $stmtK = $this->pdo->prepare($sqlKomit);
        $stmtK->execute([$tahun, $bulan, $kode_kantor]);
        while ($row = $stmtK->fetch(PDO::FETCH_ASSOC)) {
            // 🔥 TRIK SEMENTARA: RBB = W1 + W2 + W3 + W4 🔥
            $w1 = (float) ($row['komit_w1'] ?? 0);
            $w2 = (float) ($row['komit_w2'] ?? 0);
            $w3 = (float) ($row['komit_w3'] ?? 0);
            $w4 = (float) ($row['komit_w4'] ?? 0);
            
            $row['rbb_bulan'] = $w1 + $w2 + $w3 + $w4; 
            $komitmen[$row['kode_indikator']] = $row;
        }

        // 2. SIAPKAN KERANJANG REALISASI
        $data = [];
        $indikator_dpk = [
            '0007'  => 'Masuk DAMAS (Rp)', '0008'  => 'Masuk DAMAS (NOA)',
            '0009'  => 'Masuk Deposito (Rp)', '0010'  => 'Masuk Deposito (NOA)',
            '0011'  => 'Masuk Tabungan (Rp)', '0012'  => 'Masuk Tabungan (NOA)',
            '10602' => 'Nominal DAMAS', '10603' => 'Nominal Tabungan', '10604' => 'Nominal Deposito',
            '0013'  => '% CASA'
        ]; 
        
        foreach ($indikator_dpk as $kode => $nama) {
            $data[$kode] = ['nama_indikator' => $nama]; 
            if (!$minggu_req) {
                $data[$kode]['real_bln']   = 0; 
                $data[$kode]['rbb_bulan']  = 0; 
                $data[$kode]['persen_bln'] = 0; 
            }
        }

        $start_w = $minggu_req ? $minggu_req : 1;
        $end_w   = $minggu_req ? $minggu_req : 4;
        $today   = date('Y-m-d'); 

        for ($w = $start_w; $w <= $end_w; $w++) {
            
            if ($w == 1) {
                $d_start = date('Y-m-t', strtotime("{$tahun}-{$bulan}-01 -1 month"));
            } else {
                $tgl_prev = $this->getRentangTanggalMinggu($tahun, $bulan, $w - 1);
                $d_start  = $tgl_prev['end'];
            }

            $tgl = $this->getRentangTanggalMinggu($tahun, $bulan, $w); 
            $d_end = $tgl['end'];
            
            if ($d_end >= $today) $d_end = date('Y-m-d', strtotime('-1 day'));
            if ($d_start >= $today) $d_start = date('Y-m-d', strtotime('-1 day'));

            foreach ($indikator_dpk as $kode => $nama) {
                $data[$kode]["komit_w{$w}"] = (float) ($komitmen[$kode]["komit_w{$w}"] ?? 0);
            }

            try {
                // TABUNGAN
                $sqlTab = "
                    WITH rekap_rek AS (
                        SELECT no_rekening,
                            SUM(CASE WHEN created = :d_start1 THEN 1 ELSE 0 END) AS is_prev,
                            SUM(CASE WHEN created = :d_end1 THEN 1 ELSE 0 END) AS is_curr,
                            SUM(CASE WHEN created = :d_end2 THEN saldo ELSE 0 END) AS saldo_curr
                        FROM nominatif_tabungan
                        WHERE kode_kantor = :cabang AND created IN (:d_start2, :d_end3)
                        GROUP BY no_rekening
                    )
                    SELECT SUM(CASE WHEN is_prev = 0 AND is_curr > 0 THEN 1 ELSE 0 END) AS masuk_noa,
                           SUM(CASE WHEN is_prev = 0 AND is_curr > 0 THEN saldo_curr ELSE 0 END) AS masuk_rp,
                           SUM(saldo_curr) AS nominal_rp FROM rekap_rek
                ";
                $stmtTab = $this->pdo->prepare($sqlTab);
                $stmtTab->execute(['d_start1'=>$d_start, 'd_start2'=>$d_start, 'd_end1'=>$d_end, 'd_end2'=>$d_end, 'd_end3'=>$d_end, 'cabang'=>$kode_kantor]);
                $resTab = $stmtTab->fetch(PDO::FETCH_ASSOC);

                $tab_masuk_rp  = (float)($resTab['masuk_rp'] ?? 0); 
                $tab_masuk_noa = (int)($resTab['masuk_noa'] ?? 0); 
                $tab_nom       = (float)($resTab['nominal_rp'] ?? 0);

                // DEPOSITO
                $sqlDep = str_replace('saldo ELSE', 'saldo_akhir ELSE', str_replace('nominatif_tabungan', 'nominatif_deposito', $sqlTab));
                $stmtDep = $this->pdo->prepare($sqlDep);
                $stmtDep->execute(['d_start1'=>$d_start, 'd_start2'=>$d_start, 'd_end1'=>$d_end, 'd_end2'=>$d_end, 'd_end3'=>$d_end, 'cabang'=>$kode_kantor]);
                $resDep = $stmtDep->fetch(PDO::FETCH_ASSOC);

                $dep_masuk_rp  = (float)($resDep['masuk_rp'] ?? 0); 
                $dep_masuk_noa = (int)($resDep['masuk_noa'] ?? 0); 
                $dep_nom       = (float)($resDep['nominal_rp'] ?? 0);

                // DAMAS & CASA
                $damas_masuk_rp  = $tab_masuk_rp + $dep_masuk_rp;
                $damas_masuk_noa = $tab_masuk_noa + $dep_masuk_noa;
                $damas_nom       = $tab_nom + $dep_nom;
                $casa_real = ($damas_nom > 0) ? round(($tab_nom / $damas_nom) * 100, 2) : 0;

                // ASSIGN W MINGGU INI
                $data['0011']["real_w{$w}"] = $tab_masuk_rp;  $data['0012']["real_w{$w}"] = $tab_masuk_noa; $data['10603']["real_w{$w}"] = $tab_nom;
                $data['0009']["real_w{$w}"] = $dep_masuk_rp;  $data['0010']["real_w{$w}"] = $dep_masuk_noa; $data['10604']["real_w{$w}"] = $dep_nom;
                $data['0007']["real_w{$w}"] = $damas_masuk_rp; $data['0008']["real_w{$w}"] = $damas_masuk_noa; $data['10602']["real_w{$w}"] = $damas_nom;
                $data['0013']["real_w{$w}"] = $casa_real;

                foreach (['0011', '0012', '10603', '0009', '0010', '10604', '0007', '0008', '10602', '0013'] as $k) {
                    $data[$k]["persen_w{$w}"] = $this->hitungPersen($data[$k]["real_w{$w}"], $data[$k]["komit_w{$w}"]);
                }

                if (!$minggu_req && $w == 4) {
                    $data['10603']['real_bln'] = $tab_nom; $data['10604']['real_bln'] = $dep_nom; 
                    $data['10602']['real_bln'] = $damas_nom; $data['0013']['real_bln'] = $casa_real;
                }

            } catch (Exception $e) { error_log("Error DPK M{$w}: " . $e->getMessage()); }
        }

        if (!$minggu_req) {
            $db_start = date('Y-m-t', strtotime("{$tahun}-{$bulan}-01 -1 month"));
            $last_day = date('Y-m-t', strtotime("{$tahun}-{$bulan}-01"));
            
            $db_end = ($last_day >= $today) ? date('Y-m-d', strtotime('-1 day')) : $last_day;

            try {
                $stmtTB = $this->pdo->prepare($sqlTab);
                $stmtTB->execute(['d_start1'=>$db_start, 'd_start2'=>$db_start, 'd_end1'=>$db_end, 'd_end2'=>$db_end, 'd_end3'=>$db_end, 'cabang'=>$kode_kantor]);
                $resTB = $stmtTB->fetch(PDO::FETCH_ASSOC);

                $stmtDB = $this->pdo->prepare($sqlDep); 
                $stmtDB->execute(['d_start1'=>$db_start, 'd_start2'=>$db_start, 'd_end1'=>$db_end, 'd_end2'=>$db_end, 'd_end3'=>$db_end, 'cabang'=>$kode_kantor]);
                $resDB = $stmtDB->fetch(PDO::FETCH_ASSOC);

                $data['0011']['real_bln'] = (float) ($resTB['masuk_rp'] ?? 0);
                $data['0012']['real_bln'] = (int) ($resTB['masuk_noa'] ?? 0);
                $data['0009']['real_bln'] = (float) ($resDB['masuk_rp'] ?? 0);
                $data['0010']['real_bln'] = (int) ($resDB['masuk_noa'] ?? 0);
                $data['0007']['real_bln'] = $data['0011']['real_bln'] + $data['0009']['real_bln']; 
                $data['0008']['real_bln'] = $data['0012']['real_bln'] + $data['0010']['real_bln']; 

            } catch (Exception $e) { error_log("Error Total Bln DPK: " . $e->getMessage()); }

            // 🔥 HITUNG % CAPAIAN BULANAN (PAKAI RBB DUMMY) 🔥
            foreach ($indikator_dpk as $kode => $nama) {
                $rbb = (float) ($komitmen[$kode]['rbb_bulan'] ?? 0);
                $data[$kode]['rbb_bulan']  = $rbb;
                $data[$kode]['persen_bln'] = $this->hitungPersen($data[$kode]['real_bln'], $rbb);
            }
        }

        $pesan = $minggu_req ? "Berhasil memuat Realisasi DPK (Minggu ke-{$minggu_req})" : "Berhasil memuat Realisasi DPK (Full Bulan)";
        sendResponse(200, $pesan, $data);
    }





    /**
     * =================================================================
     * HELPER FUNGSI (Untuk Tanggal & Persen)
     * =================================================================
     */
    private function getRentangTanggalMinggu($tahun, $bulan, $minggu) {
        $bln = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $thn_bln = "{$tahun}-{$bln}";
        $last_day  = date('Y-m-t', strtotime("{$thn_bln}-01")); 

        switch ((int) $minggu) {
            case 1: return ['start' => "{$thn_bln}-01", 'end' => "{$thn_bln}-07"];
            case 2: return ['start' => "{$thn_bln}-08", 'end' => "{$thn_bln}-14"];
            case 3: return ['start' => "{$thn_bln}-15", 'end' => "{$thn_bln}-21"];
            case 4: return ['start' => "{$thn_bln}-22", 'end' => $last_day];
            default: return ['start' => "{$thn_bln}-01", 'end' => $last_day];
        }
    }

    private function hitungPersen($realisasi, $komitmen) {
        if (!is_numeric($realisasi) || !is_numeric($komitmen)) return null; 
        $real = (float) $realisasi;
        $komit = (float) $komitmen;
        if ($komit == 0) return 0; 
        return round(($real / $komit) * 100, 2);
    }
}