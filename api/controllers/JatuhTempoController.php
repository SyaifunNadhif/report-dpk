<?php

class JatuhTempoController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function sendResponse($status, $message, $data = []) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit;
    }

    private function getDayRange($date) {
        return [$date . ' 00:00:00', $date . ' 23:59:59'];
    }

    /**
     * API 1: REKAP JATUH TEMPO (Per Cabang)
     */
    public function getRekapProspek($input = []) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $closing_date = $b['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $b['harian_date'] ?? date('Y-m-d');
        $bulan        = $b['bulan'] ?? date('m');
        $tahun        = $b['tahun'] ?? date('Y');
        $kc_filter    = $b['kode_kantor'] ?? null;

        $jt_start = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $jt_end   = date('Y-m-t', strtotime($jt_start));

        // Base Query
        $sql = "SELECT 
                    t1.kode_cabang,
                    COALESCE(kk.nama_kantor, CONCAT('CABANG ', t1.kode_cabang)) as nama_kantor,
                    
                    -- JT LAMA
                    COUNT(t1.id) as noa_lama,
                    SUM(t1.jml_pinjaman) as plafon_lama,
                    
                    -- SISA SALDO
                    SUM(COALESCE(t2.baki_debet, 0)) as total_baki_debet,
                    
                    -- TOP UP (Logic Baru: Realisasi > Closing AND <= Harian)
                    COUNT(t3.id) as noa_baru,
                    SUM(COALESCE(t3.jml_pinjaman, 0)) as plafon_baru

                FROM nominatif t1
                LEFT JOIN kode_kantor kk ON t1.kode_cabang = kk.kode_kantor
                
                -- Cek Saldo Harian
                LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening 
                    AND t2.created = :harian_date_1
                
                -- Cek Top Up (Realisasi Range)
                LEFT JOIN nominatif t3 ON t1.nasabah_id = t3.nasabah_id 
                    AND t3.created = :harian_date_2
                    AND t3.no_rekening != t1.no_rekening
                    AND t3.tgl_realisasi > :closing_limit 
                    AND t3.tgl_realisasi <= :harian_limit

                WHERE t1.created = :closing_date
                AND t1.kolektibilitas IN ('L', 'DP')
                AND t1.tgl_jatuh_tempo BETWEEN :jt_start AND :jt_end
        ";

        if ($kc_filter) {
            $sql .= " AND t1.kode_cabang = :kc ";
        }

        $sql .= " GROUP BY t1.kode_cabang, kk.nama_kantor ORDER BY t1.kode_cabang ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':closing_date', $closing_date);
            
            // Parameter Top Up Logic
            $stmt->bindValue(':closing_limit', $closing_date);
            $stmt->bindValue(':harian_limit', $harian_date);
            
            $stmt->bindValue(':jt_start', $jt_start);
            $stmt->bindValue(':jt_end', $jt_end);

            if ($kc_filter) $stmt->bindValue(':kc', $kc_filter);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format Data
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
                    'kode_kantor' => $row['kode_cabang'],
                    'nama_kantor' => $row['nama_kantor'],
                    'plafon_lama' => $p_lama,
                    'noa_lama'    => (int)$row['noa_lama'],
                    'baki_debet'  => (float)$row['total_baki_debet'],
                    'plafon_baru' => $p_baru,
                    'noa_baru'    => (int)$row['noa_baru'],
                    'persentase'  => round($persen, 2)
                ];

                $grand['noa_lama']    += (int)$row['noa_lama'];
                $grand['plafon_lama'] += $p_lama;
                $grand['baki_debet']  += (float)$row['total_baki_debet'];
                $grand['noa_baru']    += (int)$row['noa_baru'];
                $grand['plafon_baru'] += $p_baru;
            }
            
            $grand_persen = ($grand['plafon_lama'] > 0) ? ($grand['plafon_baru'] / $grand['plafon_lama']) * 100 : 0;
            $grand['persentase'] = round($grand_persen, 2);

            return $this->sendResponse(200, "Sukses", ['grand_total' => $grand, 'rekap_per_cabang' => $finalData]);

        } catch (PDOException $e) {
            return $this->sendResponse(500, "Error: " . $e->getMessage());
        }
    }

    /**
     * API 2: DETAIL PROSPEK (With AO Name & Filter)
     */
    public function getDetailProspek($input = []) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $closing_date = $b['closing_date'] ?? date('Y-m-d');
        $harian_date  = $b['harian_date'] ?? date('Y-m-d');
        $bulan        = $b['bulan'] ?? date('m');
        $tahun        = $b['tahun'] ?? date('Y');
        
        $jt_start = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $jt_end   = date('Y-m-t', strtotime($jt_start));

        $page   = isset($b['page']) ? (int)$b['page'] : 1;
        $limit  = isset($b['limit']) ? (int)$b['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $kc_filter = isset($b['kode_kantor']) && $b['kode_kantor'] !== '' ? $b['kode_kantor'] : null;
        $ao_filter = isset($b['kode_ao']) && $b['kode_ao'] !== '' ? $b['kode_ao'] : null;

        // Base Conditions
        $where = " WHERE t1.created = :closing_date
                   AND t1.kolektibilitas IN ('L', 'DP')
                   AND t1.tgl_jatuh_tempo BETWEEN :jt_start AND :jt_end ";

        if ($kc_filter) $where .= " AND t1.kode_cabang = :kc ";
        if ($ao_filter) $where .= " AND t1.kode_group2 = :ao ";

        try {
            // Count Total
            $countSql = "SELECT COUNT(t1.id) FROM nominatif t1 " . $where;
            $stmtC = $this->pdo->prepare($countSql);
            $stmtC->bindValue(':closing_date', $closing_date);
            $stmtC->bindValue(':jt_start', $jt_start);
            $stmtC->bindValue(':jt_end', $jt_end);
            if ($kc_filter) $stmtC->bindValue(':kc', $kc_filter);
            if ($ao_filter) $stmtC->bindValue(':ao', $ao_filter);
            $stmtC->execute();
            $total_records = $stmtC->fetchColumn();
            $total_pages = ceil($total_records / $limit);

            // Fetch Data
            $sql = "SELECT 
                        t1.kode_cabang,
                        t1.no_rekening AS no_rekening_lama,
                        t1.nama_nasabah,
                        t1.jml_pinjaman AS plafond_lama,
                        COALESCE(t2.baki_debet, 0) as baki_debet_lama,
                        t1.tgl_jatuh_tempo,
                        t1.kode_group2,
                        
                        -- JOINED AO NAME
                        COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao,

                        -- TOP UP INFO
                        t3.jml_pinjaman AS plafond_baru,
                        
                        -- STATUS
                        CASE 
                            WHEN t3.no_rekening IS NOT NULL THEN 'SUDAH TOP UP'
                            WHEN t2.no_rekening IS NOT NULL AND t2.baki_debet > 0 THEN 'BELUM LUNAS'
                            ELSE 'LUNAS (POTENSI)'
                        END AS keterangan_status

                    FROM nominatif t1
                    LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening AND t2.created = :hd1
                    
                    -- Top Up Logic
                    LEFT JOIN nominatif t3 ON t1.nasabah_id = t3.nasabah_id 
                        AND t3.created = :hd2
                        AND t3.no_rekening != t1.no_rekening
                        AND t3.tgl_realisasi > :climit 
                        AND t3.tgl_realisasi <= :hlimit

                    -- Join Table AO
                    LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2

                    $where
                    ORDER BY t1.tgl_jatuh_tempo ASC
                    LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':jt_start', $jt_start);
            $stmt->bindValue(':jt_end', $jt_end);
            $stmt->bindValue(':hd1', $harian_date);
            $stmt->bindValue(':hd2', $harian_date);
            $stmt->bindValue(':climit', $closing_date);
            $stmt->bindValue(':hlimit', $harian_date);
            
            if ($kc_filter) $stmt->bindValue(':kc', $kc_filter);
            if ($ao_filter) $stmt->bindValue(':ao', $ao_filter);
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch List AO for Dropdown (Unique per context query)
            // Note: Idealnya ini API terpisah, tapi kita inject disini untuk kemudahan
            $aoList = [];
            if(!empty($data)){
                // Query distinct AO based on filters (tanpa limit)
                $sqlAO = "SELECT DISTINCT t1.kode_group2, COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao 
                          FROM nominatif t1 
                          LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2 
                          $where ORDER BY nama_ao ASC";
                $stmtAO = $this->pdo->prepare($sqlAO);
                $stmtAO->bindValue(':closing_date', $closing_date);
                $stmtAO->bindValue(':jt_start', $jt_start);
                $stmtAO->bindValue(':jt_end', $jt_end);
                if ($kc_filter) $stmtAO->bindValue(':kc', $kc_filter);
                if ($ao_filter) $stmtAO->bindValue(':ao', $ao_filter);
                $stmtAO->execute();
                $aoList = $stmtAO->fetchAll(PDO::FETCH_ASSOC);
            }

            return $this->sendResponse(200, "Detail Data", [
                'pagination' => ['current_page' => $page, 'total_pages' => $total_pages, 'total_records' => $total_records],
                'data' => $data,
                'ao_list' => $aoList // Kirim list AO untuk dropdown
            ]);

        } catch (PDOException $e) {
            return $this->sendResponse(500, "Error: " . $e->getMessage());
        }
    }
}