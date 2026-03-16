<?php

// require_once __DIR__ . '/../helpers/response.php';

class MonevController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * =================================================================
     * 1. FUNGSI: GET DATA KOMITMEN 
     * =================================================================
     */
    public function getMonevData($input) {
        $tahun = $input['tahun'] ?? date('Y');
        $bulan = $input['bulan'] ?? date('m');
        $kode_kantor = $input['kode_kantor'] ?? '000';

        try {
            // A. Cek Setting Akses (Buka/Tutup Form dari Pusat)
            $sqlAkses = "SELECT w1_open, w2_open, w3_open, w4_open FROM monev_setting_akses WHERE tahun = ? AND bulan = ?";
            $stmtAkses = $this->pdo->prepare($sqlAkses);
            $stmtAkses->execute([$tahun, $bulan]);
            $akses = $stmtAkses->fetch(PDO::FETCH_ASSOC) ?: ['w1_open'=>0, 'w2_open'=>0, 'w3_open'=>0, 'w4_open'=>0];

            // B. Cek Header Monev (Tanda tangan per minggu, status)
            $sqlHeader = "SELECT * FROM monev_header WHERE tahun = ? AND bulan = ? AND kode_kantor = ?";
            $stmtH = $this->pdo->prepare($sqlHeader);
            $stmtH->execute([$tahun, $bulan, $kode_kantor]);
            $header = $stmtH->fetch(PDO::FETCH_ASSOC);

            // C. Cek Detail Monev (Data Angka/Narasi Komitmen)
            $detail = [];
            if ($header) {
                $sqlDetail = "SELECT kode_indikator, komit_w1, komit_w2, komit_w3, komit_w4 FROM monev_detail WHERE header_id = ?";
                $stmtD = $this->pdo->prepare($sqlDetail);
                $stmtD->execute([$header['id']]);
                
                while ($row = $stmtD->fetch(PDO::FETCH_ASSOC)) {
                    $detail[$row['kode_indikator']] = [
                        'w1' => $row['komit_w1'] ?? '', 
                        'w2' => $row['komit_w2'] ?? '',
                        'w3' => $row['komit_w3'] ?? '',
                        'w4' => $row['komit_w4'] ?? '',
                    ];
                }
            }

            // Kirim ke FE
            $data = [
                'setting_akses' => $akses,
                'header'        => $header ?: null,
                'komitmen'      => $detail,
                'realisasi'     => [] 
            ];

            sendResponse(200, "Berhasil memuat data MONEV", $data);

        } catch (PDOException $e) {
            error_log("Error getMonevData: " . $e->getMessage());
            sendResponse(500, "Gagal memuat data: " . $e->getMessage(), null);
        }
    }


    /**
     * =================================================================
     * 2. FUNGSI: SIMPAN KOMITMEN (Cek Gembok Minggu & Pejabat Dinamis)
     * =================================================================
     */
    public function saveMonev($input, $userToken) {
        
        // 1. TARIK DATA USER FULL DARI DB PAKAI ID TOKEN
        $userId = $userToken['id'] ?? null;
        if (!$userId) {
            sendResponse(401, "Token tidak valid, ID tidak ditemukan.");
        }

        $sqlUser = "SELECT * FROM users WHERE id = :id"; 
        $stmtU = $this->pdo->prepare($sqlUser);
        $stmtU->execute(['id' => $userId]);
        $user = $stmtU->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendResponse(401, "User tidak ditemukan di database.");
        }

        // 2. VALIDASI JABATAN & KODE KANTOR
        $jabatan = strtolower($user['job_position'] ?? '');
        $isKacab = (strpos($jabatan, 'kepala cabang') !== false || strpos($jabatan, 'pemimpin cabang') !== false || strpos($jabatan, 'kacab') !== false);
        $isAdmin = (strtolower($user['role'] ?? '') === 'admin');

        if (!$isKacab && !$isAdmin) {
            sendResponse(403, "Akses Ditolak! Hanya Kepala Cabang yang berhak menyimpan form komitmen.");
        }

        $kode_kantor = $input['kode_kantor'] ?? '';
        if (!$isAdmin && $user['kode'] !== $kode_kantor) {
            sendResponse(403, "Akses Ditolak! Anda tidak dapat mengedit data milik cabang lain.");
        }

        $tahun        = (int) $input['tahun'];
        $bulan        = $input['bulan'];
        $minggu       = (int) ($input['minggu'] ?? 1); 
        $status_input = $input['status_input'] ?? 'Draft';

        // 3. CEK AKSES MINGGU DARI PUSAT (GEMBOK MINGGU)
        $sqlAkses = "SELECT * FROM monev_setting_akses WHERE tahun = :tahun AND bulan = :bulan";
        $stmtAkses = $this->pdo->prepare($sqlAkses);
        $stmtAkses->execute(['tahun' => $tahun, 'bulan' => $bulan]);
        $akses = $stmtAkses->fetch(PDO::FETCH_ASSOC);

        if (!$akses) {
            sendResponse(400, "Gagal! Form periode ini belum dibuka oleh Pusat.");
        }

        // Pastikan minggu yang di-request sesuai dengan yang dibuka
        $kolom_open = "w{$minggu}_open";
        if (!isset($akses[$kolom_open]) || $akses[$kolom_open] != 1) {
            sendResponse(400, "Akses Ditolak! Form untuk Minggu ke-{$minggu} sedang ditutup.");
        }

        // 4. PROSES SIMPAN KE DATABASE
        try {
            $this->pdo->beginTransaction();

            // A. Simpan/Update Header (Tanda Tangan Pejabat Dinamis)
            $pejabat = $input['pejabat'] ?? [];
            $kacab = !empty($pejabat['kepala_cabang']) ? $pejabat['kepala_cabang'] : $user['full_name'];
            $kabid_pem = $pejabat['kabid_pemasaran'] ?? '';
            $kabid_ops = $pejabat['kabid_operasional'] ?? '';

            // 🔥 BIKIN NAMA KOLOM DINAMIS SESUAI MINGGU
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

            // Ambil Header ID (Primary Key)
            $sqlGetId = "SELECT id FROM monev_header WHERE kode_kantor = ? AND tahun = ? AND bulan = ?";
            $stmtId = $this->pdo->prepare($sqlGetId);
            $stmtId->execute([$kode_kantor, $tahun, $bulan]);
            $headerId = $stmtId->fetchColumn();

            // B. Simpan/Update Detail Komitmen 
            $details = $input['detail_data'] ?? [];
            $kolom_komit = "komit_w{$minggu}"; // Dinamis: komit_w1, komit_w2, dll

            $sqlDetail = "
                INSERT INTO monev_detail (header_id, kode_indikator, {$kolom_komit})
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE {$kolom_komit} = VALUES({$kolom_komit})
            ";
            $stmtD = $this->pdo->prepare($sqlDetail);

            foreach ($details as $kode_indikator => $nilai) {
                $clean_nilai = $nilai;

                // LOGIKA PINTAR: Hapus titik ribuan khusus untuk tipe angka uang
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
            error_log("Error saveMonev: " . $e->getMessage());
            sendResponse(500, "Gagal menyimpan data: " . $e->getMessage(), null);
        }
    }
}