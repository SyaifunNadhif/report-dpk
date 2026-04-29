<?php

// Panggil file helper response
require_once __DIR__ . '/../helpers/response.php';

class ProspekController {
    
    private $pdo;

    // Injeksi PDO dari routing
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



    /**
     * BANTUAN INTERCEPT KORWIL
     * Ubah string "Korwil Semarang" menjadi array cabang
     */
    private function parseKodeKantor($kc) {
        if (is_string($kc)) {
            $kc_lower = strtolower(trim($kc));
            
            // Jika FE kirim value "korwil semarang" atau sejenisnya
            if ($kc_lower === 'korwil semarang' || $kc_lower === 'korwil_semarang') {
                return [1, 2, 3, 4, 5, 6, 7];
            } 
            // Silakan tambahkan korwil lain di sini nanti kalau sudah tau ID cabangnya
            elseif ($kc_lower === 'korwil solo' || $kc_lower === 'korwil_solo') {
                return [8, 9, 10]; // Contoh ID cabang Solo
            } 
            elseif ($kc_lower === 'korwil banyumas' || $kc_lower === 'korwil_banyumas') {
                return [11, 12];   // Contoh ID cabang Banyumas
            } 
            elseif ($kc_lower === 'korwil pekalongan' || $kc_lower === 'korwil_pekalongan') {
                return [13, 14];   // Contoh ID cabang Pekalongan
            }
        }
        return $kc;
    }

    /**
     * BANTUAN BINDING DINAMIS (Agar support Array Korwil)
     */
    private function buildCabangFilter(&$baseWhere, &$binds, $kc) {
        if (is_array($kc) && !empty($kc)) {
            // Jika dikirim Array (Mode Korwil: [1,2,3,4,5,6,7])
            $inParams = [];
            foreach ($kc as $i => $id) {
                $p = ":cb_$i";
                $inParams[] = $p;
                $binds[$p] = (int)$id;
            }
            $baseWhere .= " AND p.cabang_id IN (" . implode(',', $inParams) . ")";
        } elseif (!is_array($kc) && $kc !== '000' && $kc !== '') {
            // Jika dikirim String spesifik (Mode Cabang: "001")
            $baseWhere .= " AND p.cabang_id = :cb_single";
            $binds[':cb_single'] = (int)$kc;
        }
    }

    /**
     * ENDPOINT 1: REKAP PROSPEK (MATRIX)
     */

/**
     * ENDPOINT 1: REKAP PROSPEK (MATRIX)
     * Menampilkan SEMUA cabang meskipun prospeknya kosong (0)
     */
    public function getRekapProspek($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        
        $raw_kc  = isset($b['kode_kantor']) ? $b['kode_kantor'] : '000'; 
        $kc      = $this->parseKodeKantor($raw_kc);
        
        $produk_filter = isset($b['jenis_produk']) ? strtolower(trim((string)$b['jenis_produk'])) : null;

        if (!$closing || !$harian) {
            sendResponse(400, "Tanggal closing dan harian wajib diisi.");
        }

        $binds = [':closing' => $closing, ':harian' => $harian];
        
        // Kondisi Prospek dipisah untuk dimasukkan ke dalam LEFT JOIN (agar cabang tetap muncul)
        $prospekCond = "p.tanggal_prospek > :closing AND p.tanggal_prospek <= :harian AND p.deleted_at IS NULL";
        
        if ($produk_filter) {
            $prospekCond .= " AND LOWER(p.jenis_produk) = :produk_filter";
            $binds[':produk_filter'] = $produk_filter;
        }

        $is_mode_cabang = (!is_array($kc) && $kc !== '000');

        try {
            if (!$is_mode_cabang) {
                // ==========================================
                // MODE KONSOLIDASI & KORWIL (Baris = Cabang)
                // ==========================================
                
                // Kondisi Filter untuk membatasi list cabang (misal: Korwil)
                $cabangWhere = "1=1"; 
                if (is_array($kc) && !empty($kc)) {
                    $inParams = [];
                    foreach ($kc as $i => $id) {
                        $p = ":cb_$i";
                        $inParams[] = $p;
                        $binds[$p] = (int)$id;
                    }
                    $cabangWhere .= " AND c.id IN (" . implode(',', $inParams) . ")";
                }

                // Perhatikan: FROM cabangs LEFT JOIN prospects. 
                // COUNT(p.id) dipakai agar data yg kosong terhitung 0 (bukan 1).
                $sql = "SELECT 
                            c.kode_cabang as kode,
                            c.nama_cabang as nama_label,
                            SUM(CASE WHEN LOWER(p.status) = 'open' THEN 1 ELSE 0 END) as total_open,
                            SUM(CASE WHEN LOWER(p.status) = 'follow up' THEN 1 ELSE 0 END) as total_follow_up,
                            SUM(CASE WHEN LOWER(p.status) = 'closing' THEN 1 ELSE 0 END) as total_closing,
                            SUM(CASE WHEN LOWER(p.status) = 'rejected' THEN 1 ELSE 0 END) as total_rejected,
                            COUNT(p.id) as grand_total
                        FROM cabangs c
                        LEFT JOIN prospects p ON p.cabang_id = c.id AND $prospekCond
                        WHERE $cabangWhere
                        GROUP BY c.id, c.kode_cabang, c.nama_cabang
                        ORDER BY c.kode_cabang ASC";
                
                $stmt = $this->pdo->prepare($sql);
                foreach ($binds as $param => $val) {
                    $stmt->bindValue($param, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                $matrix = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } else {
                // ==========================================
                // MODE CABANG SPESIFIK (Baris = Produk)
                // ==========================================
                $binds[':cb_single'] = (int)$kc;

                $sql = "SELECT 
                            LOWER(p.jenis_produk) as kode,
                            p.jenis_produk as nama_label,
                            SUM(CASE WHEN LOWER(p.status) = 'open' THEN 1 ELSE 0 END) as total_open,
                            SUM(CASE WHEN LOWER(p.status) = 'follow up' THEN 1 ELSE 0 END) as total_follow_up,
                            SUM(CASE WHEN LOWER(p.status) = 'closing' THEN 1 ELSE 0 END) as total_closing,
                            SUM(CASE WHEN LOWER(p.status) = 'rejected' THEN 1 ELSE 0 END) as total_rejected,
                            COUNT(p.id) as grand_total
                        FROM prospects p
                        WHERE $prospekCond AND p.cabang_id = :cb_single
                        GROUP BY LOWER(p.jenis_produk), p.jenis_produk";

                $stmt = $this->pdo->prepare($sql);
                foreach ($binds as $param => $val) {
                    $stmt->bindValue($param, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Template agar produk yang nol tetap muncul 
                $master_produk = $produk_filter ? [ucwords($produk_filter)] : ['Tabungan', 'Deposito', 'Kredit', 'Aset'];
                $result = [];
                foreach ($master_produk as $produk) {
                    $result[strtolower($produk)] = [
                        'kode' => strtolower($produk), 'nama_label' => $produk,
                        'total_open' => 0, 'total_follow_up' => 0, 'total_closing' => 0, 'total_rejected' => 0, 'grand_total' => 0
                    ];
                }

                foreach ($rows as $r) {
                    $jp = strtolower($r['kode']);
                    if (isset($result[$jp])) {
                        $result[$jp]['total_open'] = (int)$r['total_open'];
                        $result[$jp]['total_follow_up'] = (int)$r['total_follow_up'];
                        $result[$jp]['total_closing'] = (int)$r['total_closing'];
                        $result[$jp]['total_rejected'] = (int)$r['total_rejected'];
                        $result[$jp]['grand_total'] = (int)$r['grand_total'];
                    }
                }
                $matrix = array_values($result);
            }

            // Hitung Total Keseluruhan (Paling Bawah)
            $total_all = [
                'kode' => '-', 'nama_label' => 'TOTAL',
                'total_open' => array_sum(array_column($matrix, 'total_open')),
                'total_follow_up' => array_sum(array_column($matrix, 'total_follow_up')),
                'total_closing' => array_sum(array_column($matrix, 'total_closing')),
                'total_rejected' => array_sum(array_column($matrix, 'total_rejected')),
                'grand_total' => array_sum(array_column($matrix, 'grand_total'))
            ];

            sendResponse(200, "Berhasil memuat rekap", ['matrix' => $matrix, 'total' => $total_all]);

        } catch (PDOException $e) {
            sendResponse(500, "Database Error: " . $e->getMessage()); 
        }
    }
    /**
     * ENDPOINT 2: DETAIL PROSPEK PAGING
     */
    public function getDetailProspek($input = null) {
        $b = is_array($input) ? $input : [];
        $closing = $b['closing_date'] ?? null;
        $harian  = $b['harian_date'] ?? null;
        
        // Tangkap value dan proses jika bentuknya "Korwil"
        $raw_kc = isset($b['kode_kantor']) ? $b['kode_kantor'] : '000';
        $kc     = $this->parseKodeKantor($raw_kc);
        
        $produk  = isset($b['jenis_produk']) ? strtolower(trim((string)$b['jenis_produk'])) : '';
        $status  = isset($b['status']) ? strtolower(trim((string)$b['status'])) : '';

        $page    = isset($b['page']) ? (int)$b['page'] : 1;
        $limit   = isset($b['limit']) ? (int)$b['limit'] : 50;
        $offset  = ($page - 1) * $limit;

        if (!$closing || !$harian) sendResponse(400, "Tanggal wajib diisi.");

        $binds = [':closing' => $closing, ':harian' => $harian];
        $baseWhere = "p.tanggal_prospek > :closing AND p.tanggal_prospek <= :harian AND p.deleted_at IS NULL";
        
        // Pasang filter dinamis cabang/korwil
        $this->buildCabangFilter($baseWhere, $binds, $kc);

        if ($produk !== '') {
            $baseWhere .= " AND LOWER(p.jenis_produk) = :produk";
            $binds[':produk'] = $produk;
        }
        if ($status !== '') {
            $baseWhere .= " AND LOWER(p.status) = :status";
            $binds[':status'] = $status;
        }

        $sqlCount = "SELECT COUNT(1) FROM prospects p WHERE $baseWhere";
        
        $sqlData  = "SELECT p.id, p.tanggal_prospek, p.nama, p.nik, p.no_hp, p.alamat, p.jenis_usaha, 
                            p.keterangan_usaha, p.jenis_produk, p.status, p.cabang_id, p.no_rekening, p.catatan,
                            COALESCE(u_ao.nama_lengkap, p.diambil_oleh) as nama_ao,
                            COALESCE(u_ref.nama_lengkap, p.referral_user_id) as nama_referral
                     FROM prospects p 
                     LEFT JOIN users u_ao ON p.diambil_oleh = u_ao.employee_id
                     LEFT JOIN users u_ref ON p.referral_user_id = u_ref.employee_id
                     WHERE $baseWhere 
                     ORDER BY p.tanggal_prospek DESC, p.id DESC 
                     LIMIT :lim OFFSET :off";

        try {
            // Hitung Total
            $stmtCnt = $this->pdo->prepare($sqlCount);
            foreach ($binds as $param => $val) {
                $stmtCnt->bindValue($param, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmtCnt->execute();
            $total = $stmtCnt->fetchColumn();

            // Ambil Data
            $stmt = $this->pdo->prepare($sqlData);
            foreach ($binds as $param => $val) {
                $stmt->bindValue($param, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $r['status_label'] = ucwords($r['status']);
                $r['produk_label'] = ucwords($r['jenis_produk']);
            }

            sendResponse(200, "Berhasil memuat detail prospek", [
                'pagination' => [
                    'current_page'  => $page, 
                    'total_records' => (int)$total, 
                    'total_pages'   => ceil($total / $limit)
                ],
                'data' => $rows
            ]);

        } catch (PDOException $e) {
            sendResponse(500, "Database Error: " . $e->getMessage()); 
        }
    }
}
?>
