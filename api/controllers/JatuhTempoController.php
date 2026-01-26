<?php

class JatuhTempoController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * API 1: REKAP JATUH TEMPO (Per Cabang + Grand Total)
     */
    public function getRekapProspek($input = []) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $closing_date = $b['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $b['harian_date'] ?? date('Y-m-d');
        $bulan_filter = $b['bulan'] ?? date('m');
        $tahun_filter = $b['tahun'] ?? date('Y');

        // --- OPTIMASI: Hitung Range Tanggal Jatuh Tempo ---
        $jt_start = $tahun_filter . '-' . str_pad($bulan_filter, 2, '0', STR_PAD_LEFT) . '-01';
        $jt_end   = date('Y-m-t', strtotime($jt_start));

        // QUERY SQL (OPTIMIZED - Index Friendly)
        $sql = "
            SELECT 
                t1.kode_cabang,
                COALESCE(kk.nama_kantor, CONCAT('CABANG ', t1.kode_cabang)) as nama_kantor,
                
                -- DATA LAMA (Jatuh Tempo)
                COUNT(t1.id) as noa_lama,
                SUM(t1.jml_pinjaman) as plafon_lama,
                
                -- SISA HUTANG SAAT INI (Dari data harian t2)
                SUM(COALESCE(t2.baki_debet, 0)) as total_baki_debet,
                
                -- DATA BARU (Yg Sudah Top Up di data harian t3)
                COUNT(t3.id) as noa_baru,
                SUM(COALESCE(t3.jml_pinjaman, 0)) as plafon_baru

            FROM nominatif t1
            
            -- JOIN 1: Nama Kantor
            LEFT JOIN kode_kantor kk ON t1.kode_cabang = kk.kode_kantor

            -- JOIN 2: Cek Saldo Harian (Langsung tanggal)
            LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening 
                AND t2.created = :harian_date_1
            
            -- JOIN 3: Cek Top Up (Langsung tanggal, Nasabah sama, Rekening beda)
            LEFT JOIN nominatif t3 ON t1.nasabah_id = t3.nasabah_id 
                AND t3.created = :harian_date_2
                AND t3.no_rekening != t1.no_rekening

            -- WHERE: Range Tanggal (Index Friendly)
            WHERE t1.created = :closing_date
            AND t1.kolektibilitas IN ('L', 'DP')
            AND t1.tgl_jatuh_tempo >= :jt_start 
            AND t1.tgl_jatuh_tempo <= :jt_end
            
            GROUP BY t1.kode_cabang, kk.nama_kantor
            ORDER BY t1.kode_cabang ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Binding Parameters
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':jt_start', $jt_start);
            $stmt->bindValue(':jt_end', $jt_end);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // POST-PROCESSING
            $finalData = [];
            $grand = [
                'kode_kantor' => 'ALL', 'nama_kantor' => 'GRAND TOTAL',
                'noa_lama' => 0, 'plafon_lama' => 0, 'baki_debet' => 0, 
                'noa_baru' => 0, 'plafon_baru' => 0, 'persentase' => 0
            ];

            foreach ($data as $row) {
                $p_lama = (float)$row['plafon_lama'];
                $p_baru = (float)$row['plafon_baru'];
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
            
            // Hitung % Grand Total
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

    /**
     * API 2: DETAIL PROSPEK (Support Pagination)
     */
    public function getDetailProspek($input = []) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $closing_date = $b['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $b['harian_date'] ?? date('Y-m-d');
        $bulan        = $b['bulan'] ?? date('m');
        $tahun        = $b['tahun'] ?? date('Y');
        
        $jt_start_date = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $jt_end_date   = date('Y-m-t', strtotime($jt_start_date));

        // Pagination Params
        $page   = isset($b['page']) ? (int)$b['page'] : 1;
        $limit  = isset($b['limit']) ? (int)$b['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $kc_filter = isset($b['kode_kantor']) && $b['kode_kantor'] !== '' 
                      ? str_pad((string)$b['kode_kantor'], 3, '0', STR_PAD_LEFT) 
                      : null;

        // Base Where Clause
        $whereClause = "
            WHERE t1.created = :closing_date
            AND t1.kolektibilitas IN ('L', 'DP')
            AND t1.tgl_jatuh_tempo >= :jt_start AND t1.tgl_jatuh_tempo <= :jt_end
        ";

        if ($kc_filter) {
            $whereClause .= " AND t1.kode_cabang = :kc";
        }

        try {
            // 1. HITUNG TOTAL DATA (Untuk Pagination Info)
            $countSql = "SELECT COUNT(t1.id) FROM nominatif t1 " . $whereClause;

            $stmtCount = $this->pdo->prepare($countSql);
            $stmtCount->bindValue(':closing_date', $closing_date);
            $stmtCount->bindValue(':jt_start', $jt_start_date);
            $stmtCount->bindValue(':jt_end', $jt_end_date);
            if ($kc_filter) $stmtCount->bindValue(':kc', $kc_filter);
            
            $stmtCount->execute();
            $total_records = $stmtCount->fetchColumn();
            $total_pages = ceil($total_records / $limit);

            // 2. AMBIL DATA DETAIL
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
                
                LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening 
                    AND t2.created = :harian_date_1
                
                LEFT JOIN nominatif t3 ON t1.nasabah_id = t3.nasabah_id 
                    AND t3.created = :harian_date_2
                    AND t3.no_rekening != t1.no_rekening
                
                " . $whereClause . "
                
                ORDER BY t1.tgl_jatuh_tempo ASC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->pdo->prepare($sql);
            
            // Bind Params Main Query
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':jt_start', $jt_start_date);
            $stmt->bindValue(':jt_end', $jt_end_date);
            
            // Bind Params Join
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);

            if ($kc_filter) $stmt->bindValue(':kc', $kc_filter);

            // Bind Pagination
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return sendResponse(200, "Detail Jatuh Tempo Prospek", [
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_records' => $total_records,
                    'total_pages' => $total_pages
                ],
                'data' => $data
            ]);

        } catch (PDOException $e) {
            return sendResponse(500, "Database Error: " . $e->getMessage());
        }
    }
}