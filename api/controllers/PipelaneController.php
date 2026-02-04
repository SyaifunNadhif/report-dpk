<?php

class PipelineController {
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

    private function preparePipelineQuery($closing_date, $harian_date, $tahun_jt, $kc = null, $filter_ao = null, $filter_status = null) {
        $sql = "FROM nominatif t1
                LEFT JOIN kode_kantor k ON t1.kode_cabang = k.kode_kantor
                LEFT JOIN nominatif t2 ON t1.no_rekening = t2.no_rekening AND t2.created = :harian_1
                LEFT JOIN nominatif t3 ON t1.nasabah_id = t3.nasabah_id 
                                       AND t3.created = :harian_2
                                       AND t3.no_rekening != t1.no_rekening 
                                       AND t3.tgl_realisasi > :closing_1      
                                       AND t3.tgl_realisasi <= :harian_3
                LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
                WHERE t1.created = :closing_2 
                AND YEAR(t1.tgl_jatuh_tempo) = :tahun 
                AND t1.kolektibilitas = 'L' 
                AND t1.baki_debet > 0";

        $bindings = [
            ':harian_1'  => $harian_date, ':harian_2' => $harian_date, ':harian_3' => $harian_date,
            ':closing_1' => $closing_date, ':closing_2' => $closing_date, ':tahun' => $tahun_jt
        ];

        if ($kc) { $sql .= " AND t1.kode_cabang = :kc"; $bindings[':kc'] = str_pad((string)$kc, 3, '0', STR_PAD_LEFT); }
        if ($filter_ao) { $sql .= " AND t1.kode_group2 = :ao"; $bindings[':ao'] = $filter_ao; }

        if ($filter_status === 'sudah') $sql .= " AND t3.no_rekening IS NOT NULL"; 
        elseif ($filter_status === 'lunas') $sql .= " AND t3.no_rekening IS NULL AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0)";
        elseif ($filter_status === 'topup') $sql .= " AND t3.no_rekening IS NULL AND t2.kolektibilitas = 'L' AND t2.baki_debet > 0 AND (t2.baki_debet / t1.jml_pinjaman) <= 0.5";
        elseif ($filter_status === 'retensi') $sql .= " AND t3.no_rekening IS NULL AND t2.kolektibilitas = 'L' AND t2.baki_debet > 0 AND (t2.baki_debet / t1.jml_pinjaman) > 0.5";
        elseif ($filter_status === 'drop') $sql .= " AND t3.no_rekening IS NULL AND t2.no_rekening IS NOT NULL AND t2.kolektibilitas != 'L' AND t2.baki_debet > 0";

        return ['query' => $sql, 'params' => $bindings];
    }

    // --- REKAP ---
    public function getRekapPipeline($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? '2025-12-31';
        $harian  = $b['harian_date'] ?? date('Y-m-d');
        $tahun   = $b['tahun_jt'] ?? date('Y');
        $kc      = $b['kode_kantor'] ?? null;

        try {
            $base = $this->preparePipelineQuery($closing, $harian, $tahun, $kc);

            $sql = "SELECT 
                        t1.kode_cabang,
                        COALESCE(k.nama_kantor, CONCAT('CABANG ', t1.kode_cabang)) as nama_kantor,
                        COUNT(t1.no_rekening) as noa_target,
                        SUM(t1.jml_pinjaman) as plafon_closing,
                        
                        -- SUDAH AMBIL
                        SUM(CASE WHEN t3.no_rekening IS NOT NULL THEN 1 ELSE 0 END) as noa_sudah,
                        SUM(CASE WHEN t3.no_rekening IS NOT NULL THEN t3.jml_pinjaman ELSE 0 END) as nominal_sudah,
                        
                        -- LUNAS
                        SUM(CASE WHEN t3.no_rekening IS NULL AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0) THEN 1 ELSE 0 END) as noa_lunas,
                        SUM(CASE WHEN t3.no_rekening IS NULL AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0) THEN t1.jml_pinjaman ELSE 0 END) as nominal_lunas,

                        -- TOP UP
                        SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas = 'L' AND t2.baki_debet > 0 AND (t2.baki_debet / t1.jml_pinjaman) <= 0.5 THEN 1 ELSE 0 END) as noa_topup,
                        SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas = 'L' AND t2.baki_debet > 0 AND (t2.baki_debet / t1.jml_pinjaman) <= 0.5 THEN t2.baki_debet ELSE 0 END) as os_topup,

                        -- RETENSI
                        SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas = 'L' AND t2.baki_debet > 0 AND (t2.baki_debet / t1.jml_pinjaman) > 0.5 THEN 1 ELSE 0 END) as noa_retensi,
                        SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas = 'L' AND t2.baki_debet > 0 AND (t2.baki_debet / t1.jml_pinjaman) > 0.5 THEN t2.baki_debet ELSE 0 END) as os_retensi,

                        -- DROP
                        SUM(CASE WHEN t3.no_rekening IS NULL AND t2.no_rekening IS NOT NULL AND t2.kolektibilitas != 'L' AND t2.baki_debet > 0 THEN 1 ELSE 0 END) as noa_drop,
                        SUM(CASE WHEN t3.no_rekening IS NULL AND t2.no_rekening IS NOT NULL AND t2.kolektibilitas != 'L' AND t2.baki_debet > 0 THEN t2.baki_debet ELSE 0 END) as os_drop

                    " . $base['query'] . " 
                    GROUP BY t1.kode_cabang, k.nama_kantor 
                    ORDER BY t1.kode_cabang ASC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($base['params'] as $key => $val) $stmt->bindValue($key, $val);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->send(200, "Sukses Rekap", $rows);

        } catch (Exception $e) {
            return $this->send(500, "Error Rekap: " . $e->getMessage());
        }
    }

    // --- DETAIL ---
    public function getDetailPipeline($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? '2025-12-31';
        $harian  = $b['harian_date'] ?? date('Y-m-d');
        $tahun   = $b['tahun_jt'] ?? date('Y');
        $kc      = $b['kode_kantor'] ?? null;
        $ao      = $b['kode_ao'] ?? null;
        $status  = $b['filter_status'] ?? null;
        $page    = $b['page'] ?? 1;
        $limit   = $b['limit'] ?? 10;
        $offset  = ($page - 1) * $limit;

        try {
            $base = $this->preparePipelineQuery($closing, $harian, $tahun, $kc, $ao, $status);

            // Statistik Header
            $sqlStats = "SELECT 
                COUNT(DISTINCT t1.no_rekening) as total_data,
                SUM(CASE WHEN t3.no_rekening IS NOT NULL THEN 1 ELSE 0 END) as cnt_sudah,
                SUM(CASE WHEN t3.no_rekening IS NULL AND (t2.no_rekening IS NULL OR t2.baki_debet <= 0) THEN 1 ELSE 0 END) as cnt_lunas,
                SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas='L' AND t2.baki_debet>0 AND (t2.baki_debet/t1.jml_pinjaman) <= 0.5 THEN 1 ELSE 0 END) as cnt_topup,
                SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas='L' AND t2.baki_debet>0 AND (t2.baki_debet/t1.jml_pinjaman) > 0.5 THEN 1 ELSE 0 END) as cnt_retensi,
                SUM(CASE WHEN t3.no_rekening IS NULL AND t2.kolektibilitas != 'L' AND t2.baki_debet > 0 THEN 1 ELSE 0 END) as cnt_drop
             " . $base['query'];
            
            $stmtStats = $this->pdo->prepare($sqlStats);
            foreach ($base['params'] as $key => $val) $stmtStats->bindValue($key, $val);
            $stmtStats->execute();
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

            // Data List
            $sql = "SELECT DISTINCT
                        t1.no_rekening, 
                        t1.nama_nasabah, 
                        t1.jml_pinjaman as plafon_awal,
                        t1.tgl_jatuh_tempo,
                        COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao,
                        COALESCE(t2.baki_debet, 0) as os_actual,
                        COALESCE(t2.kolektibilitas, 'Lunas') as kol_actual,
                        t3.no_rekening as rek_baru,
                        t3.jml_pinjaman as plafon_baru, -- alias plafon_baru di detail
                        t3.tgl_realisasi as tgl_baru
                    " . $base['query'] . " 
                    ORDER BY t1.tgl_jatuh_tempo ASC
                    LIMIT :lim OFFSET :off";

            $stmt = $this->pdo->prepare($sql);
            foreach ($base['params'] as $key => $val) $stmt->bindValue($key, $val);
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $plafon = (float)$r['plafon_awal'];
                $os     = (float)$r['os_actual'];
                $kol    = $r['kol_actual'];
                $persen = $plafon > 0 ? ($os / $plafon) * 100 : 0;
                $r['persen_os'] = round($persen, 1);

                if (!empty($r['rek_baru'])) {
                    $r['status_ket'] = "SUDAH AMBIL";
                    $r['badge'] = "bg-green-100 text-green-800";
                    $r['enable'] = false;
                } else {
                    $r['rek_baru'] = '-'; $r['plafon_baru'] = 0; $r['tgl_baru'] = '-';
                    if ($os <= 0 || $kol === 'Lunas') {
                        $r['status_ket'] = "LUNAS"; $r['badge'] = "bg-blue-100 text-blue-800"; $r['enable'] = true;
                    } elseif ($kol === 'L') {
                        if ($persen <= 50) { $r['status_ket'] = "TOP UP (≤50%)"; $r['badge'] = "bg-purple-100 text-purple-800"; } 
                        else { $r['status_ket'] = "RETENSI"; $r['badge'] = "bg-yellow-50 text-yellow-700"; }
                        $r['enable'] = true;
                    } else {
                        $r['status_ket'] = "DROP ($kol)"; $r['badge'] = "bg-red-100 text-red-800"; $r['enable'] = false;
                    }
                }
            }

            // List AO
            $sqlAO = "SELECT DISTINCT t1.kode_group2, COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao " . $base['query'] . " ORDER BY nama_ao ASC";
            $stmtAO = $this->pdo->prepare($sqlAO);
            foreach ($base['params'] as $key => $val) $stmtAO->bindValue($key, $val);
            $stmtAO->execute();
            $list_ao = $stmtAO->fetchAll(PDO::FETCH_ASSOC);

            return $this->send(200, "Sukses Detail", [
                'pagination' => ['total_records' => (int)$stats['total_data'], 'total_pages' => ceil($stats['total_data']/$limit), 'current_page' => (int)$page],
                'stats' => $stats, 'list_ao' => $list_ao, 'data' => $rows
            ]);

        } catch (Exception $e) {
            return $this->send(500, $e->getMessage());
        }
    }
}
?>