<?php

class JatuhTempoController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getRekapProspek($input = []) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $closing_date = $b['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $b['harian_date'] ?? date('Y-m-d');
        $bulan_filter = $b['bulan'] ?? date('m');
        $tahun_filter = $b['tahun'] ?? date('Y');

        // SQL LOGIC:
        // 1. Ambil data Nominatif (Closing) -> t1
        // 2. Join ke tabel kode_kantor -> kk (Ambil Nama Kantor)
        // 3. Join ke Nominatif (Harian) by Norek -> t2 (Cek Sisa Hutang/Baki Debet saat ini)
        // 4. Join ke Nominatif (Harian) by Nasabah ID -> t3 (Cek apakah Top Up/Rekening Baru)
        
        $sql = "
            SELECT 
                t1.kode_cabang,
                COALESCE(kk.nama_kantor, CONCAT('CABANG ', t1.kode_cabang)) as nama_kantor,
                
                -- DATA LAMA (Jatuh Tempo)
                COUNT(t1.no_rekening) as noa_lama,
                SUM(t1.jml_pinjaman) as plafon_lama,
                
                -- SISA HUTANG SAAT INI (Dari data harian t2)
                SUM(COALESCE(t2.baki_debet, 0)) as total_baki_debet,
                
                -- DATA BARU (Yg Sudah Top Up di data harian t3)
                COUNT(t3.no_rekening) as noa_baru,
                SUM(COALESCE(t3.jml_pinjaman, 0)) as plafon_baru

            FROM nominatif t1
            
            -- Join Nama Kantor (Sesuai screenshot tabel kode_kantor)
            LEFT JOIN kode_kantor kk ON TRIM(t1.kode_cabang) = TRIM(kk.kode_kantor)

            -- Join Cek Saldo Harian (t2)
            LEFT JOIN nominatif t2 ON TRIM(t1.no_rekening) = TRIM(t2.no_rekening) 
                AND DATE(t2.created) = :harian_date_1
            
            -- Join Cek Top Up (t3) -> Nasabah sama, Rekening beda
            LEFT JOIN nominatif t3 ON TRIM(t1.nasabah_id) = TRIM(t3.nasabah_id) 
                AND DATE(t3.created) = :harian_date_2
                AND TRIM(t3.no_rekening) != TRIM(t1.no_rekening)

            WHERE DATE(t1.created) = :closing_date
            AND t1.kolektibilitas IN ('L', 'DP')
            AND MONTH(t1.tgl_jatuh_tempo) = :bulan
            AND YEAR(t1.tgl_jatuh_tempo) = :tahun
            
            -- Filter 50% SUDAH DIHAPUS

            GROUP BY t1.kode_cabang, kk.nama_kantor
            ORDER BY t1.kode_cabang ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Binding params
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':bulan', $bulan_filter);
            $stmt->bindValue(':tahun', $tahun_filter);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // POST-PROCESSING (Hitung Persentase & Grand Total)
            $finalData = [];
            $grand = [
                'kode_kantor' => 'ALL',
                'nama_kantor' => 'GRAND TOTAL',
                'noa_lama' => 0, 
                'plafon_lama' => 0, 
                'baki_debet' => 0, // ini baki debet sisa hutang lama
                'noa_baru' => 0, 
                'plafon_baru' => 0
            ];

            foreach ($data as $row) {
                $p_lama = (float)$row['plafon_lama'];
                $p_baru = (float)$row['plafon_baru'];
                
                // Hitung Persentase (Plafon Baru / Plafon Lama)
                $persen = ($p_lama > 0) ? ($p_baru / $p_lama) * 100 : 0;

                $finalData[] = [
                    'kode_kantor'   => $row['kode_cabang'],
                    'nama_kantor'   => $row['nama_kantor'],
                    'plafon_lama'   => $p_lama,
                    'noa_lama'      => (int)$row['noa_lama'],
                    'baki_debet'    => (float)$row['total_baki_debet'],
                    'plafon_baru'   => $p_baru,
                    'noa_baru'      => (int)$row['noa_baru'],
                    'persentase'    => round($persen, 2)
                ];

                // Akumulasi Grand Total
                $grand['noa_lama']    += (int)$row['noa_lama'];
                $grand['plafon_lama'] += $p_lama;
                $grand['baki_debet']  += (float)$row['total_baki_debet'];
                $grand['noa_baru']    += (int)$row['noa_baru'];
                $grand['plafon_baru'] += $p_baru;
            }
            
            // Hitung Persentase Grand Total
            $grand_persen = ($grand['plafon_lama'] > 0) ? ($grand['plafon_baru'] / $grand['plafon_lama']) * 100 : 0;
            $grand['persentase'] = round($grand_persen, 2);

            return sendResponse(200, "Rekap Jatuh Tempo & Top Up", [
                'grand_total' => $grand,
                'rekap_per_cabang' => $finalData
            ]);

        } catch (PDOException $e) {
            return sendResponse(500, "Database Error: " . $e->getMessage());
        }
    }

    public function getDetailProspek($input = []) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $closing_date = $b['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $b['harian_date'] ?? date('Y-m-d');
        $bulan_filter = $b['bulan'] ?? date('m');
        $tahun_filter = $b['tahun'] ?? date('Y');
        
        $kc_filter = isset($b['kode_kantor']) && $b['kode_kantor'] !== '' 
                     ? str_pad((string)$b['kode_kantor'], 3, '0', STR_PAD_LEFT) 
                     : null;

        // PERBAIKAN:
        // 1. Menggunakan param :harian_date_1 dan :harian_date_2 agar jumlah parameter pas.
        // 2. Menghapus komentar SQL (--) agar parsing lebih aman.
        
        $sql = "
            SELECT 
                t1.kode_cabang,
                t1.no_rekening AS no_rekening_lama,
                t1.nama_nasabah,
                t1.jml_pinjaman AS plafond_lama,
                COALESCE(t2.baki_debet, 0) as baki_debet_lama,
                t1.tgl_realisasi,
                t1.tgl_jatuh_tempo,
                t1.kolektibilitas AS kol_lama,
                t1.alamat,
                t1.hp, 
                t1.kode_group2,
                t3.no_rekening AS no_rekening_baru,
                t3.jml_pinjaman AS plafond_baru,
                t3.tgl_realisasi AS tgl_realisasi_baru,
                CASE 
                    WHEN t2.no_rekening IS NOT NULL AND t2.baki_debet > 0 THEN 'BELUM LUNAS'
                    WHEN t3.no_rekening IS NOT NULL THEN 'SUDAH TOP UP'
                    ELSE 'LUNAS (POTENSI)'
                END AS keterangan_status
            FROM nominatif t1
            LEFT JOIN nominatif t2 ON TRIM(t1.no_rekening) = TRIM(t2.no_rekening) 
                AND DATE(t2.created) = :harian_date_1
            LEFT JOIN nominatif t3 ON TRIM(t1.nasabah_id) = TRIM(t3.nasabah_id) 
                AND DATE(t3.created) = :harian_date_2
                AND TRIM(t3.no_rekening) != TRIM(t1.no_rekening)
            WHERE DATE(t1.created) = :closing_date
            AND t1.kolektibilitas IN ('L', 'DP')
            AND MONTH(t1.tgl_jatuh_tempo) = :bulan
            AND YEAR(t1.tgl_jatuh_tempo) = :tahun
          
        ";

        if ($kc_filter) {
            $sql .= " AND t1.kode_cabang = :kc";
        }

        $sql .= " ORDER BY t1.tgl_jatuh_tempo ASC LIMIT 1000";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            // BINDING PARAMETER (Harus sesuai jumlah di query)
            $stmt->bindValue(':harian_date_1', $harian_date); // Untuk t2
            $stmt->bindValue(':harian_date_2', $harian_date); // Untuk t3
            
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':bulan', $bulan_filter);
            $stmt->bindValue(':tahun', $tahun_filter);
            
            if ($kc_filter) {
                $stmt->bindValue(':kc', $kc_filter);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return sendResponse(200, "Detail Jatuh Tempo Prospek (Fixed Param)", [
                'total_row' => count($data),
                'data' => $data
            ]);

        } catch (PDOException $e) {
            return sendResponse(500, "Database Error: " . $e->getMessage());
        }
    }

    // --- FUNGSI BANTUAN CEK DATA ---
    private function debugQuery($closing, $harian, $kc) {
        // Cek 1: Apakah data t1 (Closing) ada?
        $cek1 = $this->pdo->prepare("SELECT COUNT(*) FROM nominatif WHERE DATE(created) = ?");
        $cek1->execute([$closing]);
        $count1 = $cek1->fetchColumn();

        // Cek 2: Apakah data t2 (Harian) ada?
        $cek2 = $this->pdo->prepare("SELECT COUNT(*) FROM nominatif WHERE DATE(created) = ?");
        $cek2->execute([$harian]);
        $count2 = $cek2->fetchColumn();

        return sendResponse(200, "Data Kosong. Hasil Pengecekan:", [
            "info" => "Query utama tidak menemukan data yang cocok.",
            "debug_langkah_1" => "Jumlah data Closing ($closing): $count1 baris. (Jika 0, berarti tanggal salah)",
            "debug_langkah_2" => "Jumlah data Harian ($harian): $count2 baris. (Jika 0, berarti tanggal salah)",
            "tips" => "Pastikan tanggal created di database sesuai. Coba hapus filter OS 50% dulu untuk tes."
        ]);
    }
}