<?php

require_once __DIR__ . '/../helpers/response.php';
// require_once __DIR__ . '/../helpers/MobHelper.php'; // Aktifkan jika butuh helper lain

class DashboardController{
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * =================================================================
     * FUNGSI MANDOR (EXECUTIVE DASHBOARD)
     * =================================================================
     * Fungsi ini yang dipanggil oleh API Front-End.
     * Dia akan mengumpulkan data dari fungsi-fungsi kecil di bawahnya.
     */
/**
     * =================================================================
     * FUNGSI MANDOR (EXECUTIVE DASHBOARD ULTIMATE)
     * =================================================================
     */
    public function getExecutiveDashboard($input = []) {
        try {
            // Kita kumpulkan semua puzzle-nya di sini!
            $data = [
                // 1. Metrik NPL & Kolektibilitas
                'tren_npl'                => $this->getTrenNPL($input),
                'top_bottom_npl'          => $this->getTopBottomNPL($input),
                'kenaikan_penurunan_npl'  => $this->getTopKenaikanPenurunanNPL($input),
                'flow_vs_recovery_npl'    => $this->getFlowVsRecoveryNPL($input),
                
                // 2. Metrik Kredit & Realisasi
                'runoff_vs_realisasi'     => $this->getRunOffVsRealisasiKorwil($input),
                'top_bottom_realisasi'    => $this->getTopBottomRealisasi($input),
                'repayment_rate'          => $this->getRepaymentRateCabang($input),
                
                // 3. Metrik DPK (Dana Pihak Ketiga)
                'perkembangan_deposito'   => $this->getPerkembanganDeposito($input),
                'perkembangan_tabungan'   => $this->getPerkembanganTabungan($input)
            ];

            // Kirim responsenya ke Front-End dengan penuh gaya
            sendResponse(200, "Berhasil memuat Executive Dashboard Ultimate", $data);

        } catch (Exception $e) {
            // Kalau ada error level dewa, tangkap di sini
            error_log("Error Executive Dashboard: " . $e->getMessage());
            sendResponse(500, "Gagal memuat dashboard: " . $e->getMessage(), null);
        }
    }

    /**
     * =================================================================
     * HELPER FILTER KORWIL & CABANG
     * =================================================================
     * Biar tidak perlu nulis if-else korwil & cabang berulang-ulang
     */
    private function buildFilterQuery($input, $alias = 't') {
        $kode_kantor  = !empty($input['kode_kantor']) ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        $korwil_input = !empty($input['korwil']) ? strtoupper($input['korwil']) : null;
        
        $sqlFilter = "";
        $params = [];
        $prefix = $alias ? "{$alias}." : "";

        if ($kode_kantor && $kode_kantor !== '000') {
            $sqlFilter = " AND {$prefix}kode_cabang = :kode_kantor ";
            $params[':kode_kantor'] = $kode_kantor;
        } elseif ($korwil_input) {
            $kw_start = null; $kw_end = null;
            switch ($korwil_input) {
                case 'SEMARANG':   $kw_start = '001'; $kw_end = '007'; break;
                case 'SOLO':       $kw_start = '008'; $kw_end = '014'; break;
                case 'BANYUMAS':   $kw_start = '015'; $kw_end = '021'; break;
                case 'PEKALONGAN': $kw_start = '022'; $kw_end = '028'; break;
            }
            if ($kw_start && $kw_end) {
                $sqlFilter = " AND {$prefix}kode_cabang BETWEEN :kw_start AND :kw_end ";
                $params[':kw_start'] = $kw_start;
                $params[':kw_end'] = $kw_end;
            }
        }

        return ['sql' => $sqlFilter, 'params' => $params];
    }

    /**
     * =================================================================
     * FUNGSI-FUNGSI MODULAR (PECAHAN)
     * =================================================================
     */

    public function getRunOffVsRealisasi($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');
        
        $filter = $this->buildFilterQuery($input, 't');

        // Contoh kerangka query (tinggal sesuaikan dengan logic runoff dari sepuh)
        $sql = "
            SELECT 
                COALESCE(SUM(t.baki_debet), 0) as total_run_off,
                (SELECT SUM(plafond) FROM nominatif t2 WHERE t2.created = :harian_date AND t2.tgl_realisasi > :closing_date {$filter['sql']}) as total_realisasi
            FROM nominatif t
            WHERE t.created = :closing_date
            {$filter['sql']}
            /* Tambahkan logic runoff_calc di sini seperti di KreditController */
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_run_off' => 0, 'total_realisasi' => 0];
        } catch (Exception $e) {
            return []; // Kembalikan array kosong jika error agar dashboard tidak mati total
        }
    }

    public function getTrenNPL($input) {
        $harian_date = $input['harian_date'] ?? date('Y-m-d');
        $periode = $input['periode'] ?? 'bulanan'; 
        
        $dates = [$harian_date]; // Selalu masukkan tanggal hari ini (ACTUAL)
        
        // 1. Generate Tanggal Secara Dinamis
        if ($periode === 'mingguan') {
            for ($i = 1; $i <= 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i week", strtotime($harian_date)));
            }
        } elseif ($periode === '7_hari') {
            for ($i = 1; $i <= 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
            }
        } elseif ($periode === '14_hari') {
            for ($i = 1; $i <= 13; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
            }
        } elseif ($periode === '30_hari') {
            for ($i = 1; $i <= 29; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
            }
        } elseif ($periode === 'tahunan') {
            // LOGIKA BARU: Mundur 12 bulan ke belakang (ambil data closing akhir bulan)
            $patokan_mundur = strtotime(date('Y-m-01', strtotime($harian_date))); 
            for ($i = 1; $i <= 12; $i++) {
                $dates[] = date('Y-m-d', strtotime("last day of -$i month", $patokan_mundur));
            }
        } else {
            // Default: Bulanan (Mundur 6 bulan ke belakang)
            $patokan_mundur = strtotime(date('Y-m-01', strtotime($harian_date))); 
            for ($i = 1; $i <= 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("last day of -$i month", $patokan_mundur));
            }
        }
        
        // 2. Siapkan Binding Parameter untuk Klausa IN (...)
        $inParams = [];
        $inQueryParts = [];
        foreach ($dates as $i => $date) {
            $paramName = ":date_$i";
            $inParams[$paramName] = $date;
            $inQueryParts[] = $paramName;
        }
        $inString = implode(', ', $inQueryParts); 

        // 3. Ambil Filter Cabang/Korwil
        $filter = $this->buildFilterQuery($input, 't');

        // 4. Susun Query SQL
        $sql = "
            SELECT 
                t.created AS tanggal,
                SUM(CASE WHEN t.kolektibilitas IN ('KL','D','M') THEN t.baki_debet ELSE 0 END) AS npl_amt,
                SUM(t.baki_debet) AS total_kredit,
                ROUND((SUM(CASE WHEN t.kolektibilitas IN ('KL','D','M') THEN t.baki_debet ELSE 0 END) / NULLIF(SUM(t.baki_debet), 0) * 100), 2) AS npl_persen
            FROM nominatif t
            WHERE t.created IN ($inString)
            {$filter['sql']}
            GROUP BY t.created
            ORDER BY t.created ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($inParams as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 5. Format data untuk output
            $formattedData = [];
            foreach ($rows as $r) {
                // Formatting label biar cantik di sumbu X pada chart
                if ($periode === 'bulanan' || $periode === 'tahunan') {
                    // Untuk tahunan dan bulanan, labelnya pake Bulan & Tahun
                    // Kalau tanggalnya sama dengan harian_date, tambahkan label "(Act)"
                    if ($r['tanggal'] === $harian_date) {
                        $label = date('d M Y', strtotime($r['tanggal'])) . ' (Act)';
                    } else {
                        $label = date('M Y', strtotime($r['tanggal'])); // cth: Mar 2025
                    }
                } elseif ($periode === 'mingguan') {
                    $label = date('d M Y', strtotime($r['tanggal'])); 
                } else {
                    // Untuk harian (7, 14, 30), tanggal & bulan aja biar nggak kepanjangan
                    $label = date('d M', strtotime($r['tanggal'])); // cth: 10 Mar
                }

                $formattedData[] = [
                    'tanggal'      => $r['tanggal'],
                    'label'        => $label,
                    'npl_amt'      => (float) $r['npl_amt'],
                    'total_kredit' => (float) $r['total_kredit'],
                    'npl_persen'   => (float) $r['npl_persen']
                ];
            }

            return $formattedData;

        } catch (PDOException $e) {
            error_log("Error getTrenNPL: " . $e->getMessage());
            return [];
        }
    }

    public function getTopBottomNPL($input) {
        $harian_date = $input['harian_date'] ?? date('Y-m-d');
        
        // Ambil filter (misalnya Front-End minta Top/Bottom khusus area Korwil Semarang)
        // Catatan: Kalau mau ranking seluruh cabang (Nasional), pastikan kode_kantor dan korwil kosong/000
        $filter = $this->buildFilterQuery($input, 't');

        // Susun Base Query SQL (Mengelompokkan per Cabang)
        $sqlBase = "
            SELECT 
                t.kode_cabang,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', t.kode_cabang)) AS nama_cabang,
                SUM(CASE WHEN t.kolektibilitas IN ('KL','D','M') THEN t.baki_debet ELSE 0 END) AS npl_amt,
                SUM(t.baki_debet) AS total_kredit,
                ROUND((SUM(CASE WHEN t.kolektibilitas IN ('KL','D','M') THEN t.baki_debet ELSE 0 END) / NULLIF(SUM(t.baki_debet), 0) * 100), 2) AS npl_persen
            FROM nominatif t
            LEFT JOIN kode_kantor k ON t.kode_cabang = k.kode_kantor
            WHERE t.created = :harian_date
            {$filter['sql']}
            GROUP BY t.kode_cabang, k.nama_kantor
            HAVING SUM(t.baki_debet) > 0 
        ";

        try {
            // 1. Eksekusi TOP 5 NPL Tertinggi (Urut NPL % Descending)
            $stmtTop = $this->pdo->prepare($sqlBase . " ORDER BY npl_persen DESC LIMIT 5");
            $stmtTop->bindValue(':harian_date', $harian_date);
            foreach ($filter['params'] as $key => $val) {
                $stmtTop->bindValue($key, $val);
            }
            $stmtTop->execute();
            $topData = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

            // 2. Eksekusi BOTTOM 5 NPL Terendah (Urut NPL % Ascending)
            $stmtBot = $this->pdo->prepare($sqlBase . " ORDER BY npl_persen ASC LIMIT 5");
            $stmtBot->bindValue(':harian_date', $harian_date);
            foreach ($filter['params'] as $key => $val) {
                $stmtBot->bindValue($key, $val);
            }
            $stmtBot->execute();
            $bottomData = $stmtBot->fetchAll(PDO::FETCH_ASSOC);

            // Fungsi helper kecil untuk merapikan format angka jadi Float (biar enak dibaca Front-End)
            $formatData = function($rows) {
                return array_map(function($r) {
                    return [
                        'kode_cabang'  => $r['kode_cabang'],
                        'nama_cabang'  => $r['nama_cabang'],
                        'npl_amt'      => (float) $r['npl_amt'],
                        'total_kredit' => (float) $r['total_kredit'],
                        'npl_persen'   => (float) $r['npl_persen']
                    ];
                }, $rows);
            };

            // Kembalikan datanya dalam 2 kelompok
            return [
                'top'    => $formatData($topData),
                'bottom' => $formatData($bottomData)
            ];

        } catch (PDOException $e) {
            error_log("Error getTopBottomNPL: " . $e->getMessage());
            return [
                'top'    => [],
                'bottom' => []
            ];
        }
    }

    public function getTopKenaikanPenurunanNPL($input) {
        $harian_date  = $input['harian_date'] ?? date('Y-m-d');
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        
        $filter = $this->buildFilterQuery($input, 't');

        // Query super efisien: Tarik data 2 tanggal sekaligus, lalu pisahkan dengan CASE WHEN
        $sql = "
            SELECT 
                t.kode_cabang,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', t.kode_cabang)) AS nama_cabang,
                
                -- Data Current (Harian)
                SUM(CASE WHEN t.created = :harian_date_1 AND t.kolektibilitas IN ('KL','D','M') THEN t.baki_debet ELSE 0 END) AS npl_curr,
                SUM(CASE WHEN t.created = :harian_date_2 THEN t.baki_debet ELSE 0 END) AS baki_curr,
                
                -- Data Previous (Closing Bulan Lalu)
                SUM(CASE WHEN t.created = :closing_date_1 AND t.kolektibilitas IN ('KL','D','M') THEN t.baki_debet ELSE 0 END) AS npl_prev,
                SUM(CASE WHEN t.created = :closing_date_2 THEN t.baki_debet ELSE 0 END) AS baki_prev
                
            FROM nominatif t
            LEFT JOIN kode_kantor k ON t.kode_cabang = k.kode_kantor
            WHERE t.created IN (:harian_date_3, :closing_date_3)
            {$filter['sql']}
            GROUP BY t.kode_cabang, k.nama_kantor
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameter tanggal berulang untuk amannya di semua versi PDO
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':harian_date_3', $harian_date);
            
            $stmt->bindValue(':closing_date_1', $closing_date);
            $stmt->bindValue(':closing_date_2', $closing_date);
            $stmt->bindValue(':closing_date_3', $closing_date);
            
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $kenaikan  = [];
            $penurunan = [];

            // Proses perhitungannya di PHP agar lebih ringan
            foreach ($rows as $r) {
                $npl_curr = (float)$r['npl_curr'];
                $baki_curr = (float)$r['baki_curr'];
                $npl_prev = (float)$r['npl_prev'];
                $baki_prev = (float)$r['baki_prev'];

                // Hitung persentase
                $persen_curr = $baki_curr > 0 ? ($npl_curr / $baki_curr) * 100 : 0;
                $persen_prev = $baki_prev > 0 ? ($npl_prev / $baki_prev) * 100 : 0;

                // Hitung Delta (Selisih)
                $delta = $persen_curr - $persen_prev;

                $dataCabang = [
                    'kode_cabang' => $r['kode_cabang'],
                    'nama_cabang' => $r['nama_cabang'],
                    'npl_persen_prev' => round($persen_prev, 2),
                    'npl_persen_curr' => round($persen_curr, 2),
                    'delta_npl'       => round($delta, 2)
                ];

                // Pisahkan mana yang naik, mana yang turun (hanya yang tidak 0)
                if ($delta > 0) {
                    $kenaikan[] = $dataCabang;
                } elseif ($delta < 0) {
                    $penurunan[] = $dataCabang;
                }
            }

            // Urutkan Kenaikan dari yang terburuk (Delta terbesar ke terkecil)
            usort($kenaikan, function($a, $b) {
                return $b['delta_npl'] <=> $a['delta_npl'];
            });

            // Urutkan Penurunan dari yang terbaik (Delta paling minus ke kurang minus)
            usort($penurunan, function($a, $b) {
                return $a['delta_npl'] <=> $b['delta_npl'];
            });

            // Ambil Top 5 saja (kalau isinya cuma 1, array_slice otomatis nampilin 1 doang)
            return [
                'top_kenaikan'  => array_slice($kenaikan, 0, 5),
                'top_penurunan' => array_slice($penurunan, 0, 5)
            ];

        } catch (PDOException $e) {
            error_log("Error getTopKenaikanPenurunanNPL: " . $e->getMessage());
            return ['top_kenaikan' => [], 'top_penurunan' => []];
        }
    }

    public function getRunOffVsRealisasiKorwil($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');
        $awal_bulan   = date('Y-m-01', strtotime($harian_date));

        // Query CTE dengan logika lama: 
        // 1. Filter kolektibilitas IN ('L','DP','KL','D','M')
        // 2. Angsuran = baki_closed - baki_harian (membiarkan nilai minus ikut terhitung agar balance)
        $sql = "
            WITH 
            closing AS (
                SELECT no_rekening, kode_cabang, baki_debet AS baki_closed
                FROM nominatif
                WHERE created = :closing_date
                AND kolektibilitas IN ('L','DP','KL','D','M')
            ),
            harian AS (
                SELECT no_rekening, baki_debet AS baki_harian
                FROM nominatif
                WHERE created = :harian_date
                AND kolektibilitas IN ('L','DP','KL','D','M')
            ),
            runoff_korwil AS (
                SELECT 
                    CASE 
                        WHEN c.kode_cabang BETWEEN '001' AND '007' THEN 'SEMARANG'
                        WHEN c.kode_cabang BETWEEN '008' AND '014' THEN 'SOLO'
                        WHEN c.kode_cabang BETWEEN '015' AND '021' THEN 'BANYUMAS'
                        WHEN c.kode_cabang BETWEEN '022' AND '028' THEN 'PEKALONGAN'
                        ELSE 'LAINNYA' 
                    END AS nama_korwil,
                    -- Kalau di data harian tidak ada (NULL), berarti LUNAS
                    SUM(CASE WHEN h.no_rekening IS NULL THEN c.baki_closed ELSE 0 END) AS total_lunas,
                    -- Kalau masih ada di harian, hitung selisihnya sebagai ANGSURAN (termasuk penambahan baki/minus)
                    SUM(CASE WHEN h.no_rekening IS NOT NULL THEN (c.baki_closed - h.baki_harian) ELSE 0 END) AS total_angsuran
                FROM closing c
                LEFT JOIN harian h ON c.no_rekening = h.no_rekening
                GROUP BY 
                    CASE 
                        WHEN c.kode_cabang BETWEEN '001' AND '007' THEN 'SEMARANG'
                        WHEN c.kode_cabang BETWEEN '008' AND '014' THEN 'SOLO'
                        WHEN c.kode_cabang BETWEEN '015' AND '021' THEN 'BANYUMAS'
                        WHEN c.kode_cabang BETWEEN '022' AND '028' THEN 'PEKALONGAN'
                        ELSE 'LAINNYA' 
                    END
            ),
            realisasi_korwil AS (
                SELECT 
                    CASE 
                        WHEN kode_cabang BETWEEN '001' AND '007' THEN 'SEMARANG'
                        WHEN kode_cabang BETWEEN '008' AND '014' THEN 'SOLO'
                        WHEN kode_cabang BETWEEN '015' AND '021' THEN 'BANYUMAS'
                        WHEN kode_cabang BETWEEN '022' AND '028' THEN 'PEKALONGAN'
                        ELSE 'LAINNYA' 
                    END AS nama_korwil,
                    SUM(plafond) AS total_realisasi
                FROM nominatif
                WHERE created = :harian_date_rl
                  AND tgl_realisasi >= :awal_bulan 
                  AND tgl_realisasi <= :harian_date_rl2
                GROUP BY 
                    CASE 
                        WHEN kode_cabang BETWEEN '001' AND '007' THEN 'SEMARANG'
                        WHEN kode_cabang BETWEEN '008' AND '014' THEN 'SOLO'
                        WHEN kode_cabang BETWEEN '015' AND '021' THEN 'BANYUMAS'
                        WHEN kode_cabang BETWEEN '022' AND '028' THEN 'PEKALONGAN'
                        ELSE 'LAINNYA' 
                    END
            ),
            master_korwil AS (
                SELECT 'SEMARANG' AS nama_korwil, 1 as sort_order UNION ALL
                SELECT 'SOLO', 2 UNION ALL
                SELECT 'BANYUMAS', 3 UNION ALL
                SELECT 'PEKALONGAN', 4
            )
            
            SELECT 
                mk.nama_korwil,
                COALESCE(rl.total_realisasi, 0) AS realisasi,
                COALESCE(ro.total_lunas, 0) AS lunas,
                COALESCE(ro.total_angsuran, 0) AS angsuran,
                (COALESCE(ro.total_lunas, 0) + COALESCE(ro.total_angsuran, 0)) AS total_runoff,
                (COALESCE(rl.total_realisasi, 0) - (COALESCE(ro.total_lunas, 0) + COALESCE(ro.total_angsuran, 0))) AS growth
            FROM master_korwil mk
            LEFT JOIN runoff_korwil ro ON mk.nama_korwil = ro.nama_korwil
            LEFT JOIN realisasi_korwil rl ON mk.nama_korwil = rl.nama_korwil
            ORDER BY mk.sort_order;
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':harian_date_rl', $harian_date);
            $stmt->bindValue(':harian_date_rl2', $harian_date);
            $stmt->bindValue(':awal_bulan', $awal_bulan);
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Hitung Grand Total Konsolidasi
            $grand_total = [
                'nama_korwil'  => 'TOTAL KONSOLIDASI',
                'realisasi'    => 0,
                'lunas'        => 0,
                'angsuran'     => 0,
                'total_runoff' => 0,
                'growth'       => 0
            ];

            $formattedData = [];
            foreach ($rows as $r) {
                $realisasi = (float) $r['realisasi'];
                $lunas     = (float) $r['lunas'];
                $angsuran  = (float) $r['angsuran'];
                $runoff    = (float) $r['total_runoff'];
                $growth    = (float) $r['growth'];

                $formattedData[] = [
                    'nama_korwil'  => $r['nama_korwil'],
                    'realisasi'    => $realisasi,
                    'lunas'        => $lunas,
                    'angsuran'     => $angsuran,
                    'total_runoff' => $runoff,
                    'growth'       => $growth
                ];

                $grand_total['realisasi']    += $realisasi;
                $grand_total['lunas']        += $lunas;
                $grand_total['angsuran']     += $angsuran;
                $grand_total['total_runoff'] += $runoff;
                $grand_total['growth']       += $growth;
            }

            return [
                'detail_korwil' => $formattedData,
                'grand_total'   => $grand_total
            ];

        } catch (PDOException $e) {
            error_log("Error getRunOffVsRealisasiKorwil: " . $e->getMessage());
            return ['detail_korwil' => [], 'grand_total' => []];
        }
    }

    public function getFlowVsRecoveryNPL($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');

        $sql = "
            WITH 
            closing AS (
                SELECT no_rekening, kode_cabang, kolektibilitas AS kolek_prev, baki_debet AS baki_prev
                FROM nominatif
                WHERE created = :closing_date
            ),
            harian AS (
                SELECT no_rekening, kolektibilitas AS kolek_curr, baki_debet AS baki_curr
                FROM nominatif
                WHERE created = :harian_date
            ),
            gabung AS (
                SELECT 
                    c.no_rekening,
                    CASE 
                        WHEN c.kode_cabang BETWEEN '001' AND '007' THEN 'SEMARANG'
                        WHEN c.kode_cabang BETWEEN '008' AND '014' THEN 'SOLO'
                        WHEN c.kode_cabang BETWEEN '015' AND '021' THEN 'BANYUMAS'
                        WHEN c.kode_cabang BETWEEN '022' AND '028' THEN 'PEKALONGAN'
                        ELSE 'LAINNYA' 
                    END AS nama_korwil,
                    c.kolek_prev,
                    c.baki_prev,
                    h.kolek_curr,
                    COALESCE(h.baki_curr, 0) AS baki_curr,
                    CASE WHEN h.no_rekening IS NULL THEN 1 ELSE 0 END AS is_lunas
                FROM closing c
                LEFT JOIN harian h ON c.no_rekening = h.no_rekening
            ),
            kalkulasi AS (
                SELECT 
                    nama_korwil,
                    -- FLOW NPL (Bulan lalu L/DP, bulan ini KL/D/M) -> Dihitung dari Baki Harian
                    SUM(CASE WHEN kolek_prev IN ('L','DP') AND kolek_curr IN ('KL','D','M') 
                             THEN baki_curr ELSE 0 END) AS flow_npl,
                             
                    -- BACKFLOW (Bulan lalu KL/D/M, bulan ini sembuh ke L/DP) -> Dihitung dari Baki Harian
                    SUM(CASE WHEN kolek_prev IN ('KL','D','M') AND kolek_curr IN ('L','DP') 
                             THEN baki_curr ELSE 0 END) AS backflow,
                             
                    -- LUNAS NPL (Bulan lalu KL/D/M, bulan ini hilang/lunas) -> Dihitung dari Baki Prev
                    SUM(CASE WHEN kolek_prev IN ('KL','D','M') AND is_lunas = 1 
                             THEN baki_prev ELSE 0 END) AS lunas_npl,
                             
                    -- ANGSURAN NPL (Selisih baki pada NPL yang tidak lunas)
                    SUM(CASE WHEN kolek_prev IN ('KL','D','M') AND is_lunas = 0 
                             THEN (baki_prev - baki_curr) ELSE 0 END) AS angsuran_npl
                FROM gabung
                GROUP BY nama_korwil
            ),
            master_korwil AS (
                SELECT 'SEMARANG' AS nama_korwil, 1 as sort_order UNION ALL
                SELECT 'SOLO', 2 UNION ALL
                SELECT 'BANYUMAS', 3 UNION ALL
                SELECT 'PEKALONGAN', 4
            )
            
            SELECT 
                mk.nama_korwil,
                COALESCE(k.flow_npl, 0) AS flow_npl,
                COALESCE(k.backflow, 0) AS backflow,
                COALESCE(k.lunas_npl, 0) AS lunas_npl,
                COALESCE(k.angsuran_npl, 0) AS angsuran_npl,
                (COALESCE(k.backflow, 0) + COALESCE(k.lunas_npl, 0) + COALESCE(k.angsuran_npl, 0)) AS total_recovery
            FROM master_korwil mk
            LEFT JOIN kalkulasi k ON mk.nama_korwil = k.nama_korwil
            ORDER BY mk.sort_order;
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Grand Total Konsolidasi
            $grand_total = [
                'nama_korwil'    => 'TOTAL KONSOLIDASI',
                'flow_npl'       => 0,
                'backflow'       => 0,
                'lunas_npl'      => 0,
                'angsuran_npl'   => 0,
                'total_recovery' => 0
            ];

            $formattedData = [];
            foreach ($rows as $r) {
                $flow_npl     = (float) $r['flow_npl'];
                $backflow     = (float) $r['backflow'];
                $lunas_npl    = (float) $r['lunas_npl'];
                $angsuran_npl = (float) $r['angsuran_npl'];
                $recovery     = (float) $r['total_recovery'];

                $formattedData[] = [
                    'nama_korwil'    => $r['nama_korwil'],
                    'flow_npl'       => $flow_npl,
                    'backflow'       => $backflow,
                    'lunas_npl'      => $lunas_npl,
                    'angsuran_npl'   => $angsuran_npl,
                    'total_recovery' => $recovery
                ];

                $grand_total['flow_npl']       += $flow_npl;
                $grand_total['backflow']       += $backflow;
                $grand_total['lunas_npl']      += $lunas_npl;
                $grand_total['angsuran_npl']   += $angsuran_npl;
                $grand_total['total_recovery'] += $recovery;
            }

            return [
                'detail_korwil' => $formattedData,
                'grand_total'   => $grand_total
            ];

        } catch (PDOException $e) {
            error_log("Error getFlowVsRecoveryNPL: " . $e->getMessage());
            return ['detail_korwil' => [], 'grand_total' => []];
        }
    }

    public function getTopBottomRealisasi($input) {
        $harian_date = $input['harian_date'] ?? date('Y-m-d');
        $awal_bulan  = date('Y-m-01', strtotime($harian_date));
        
        // Ambil filter Korwil (jika ada request dari Front-End)
        $filter = $this->buildFilterQuery($input, 't');

        // ==========================================
        // 1. QUERY REALISASI CABANG (Top & Bottom)
        // ==========================================
        $sqlCabang = "
            SELECT 
                t.kode_cabang,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', t.kode_cabang)) AS nama_cabang,
                SUM(t.plafond) AS total_realisasi,
                COUNT(t.no_rekening) AS noa_realisasi
            FROM nominatif t
            LEFT JOIN kode_kantor k ON t.kode_cabang = k.kode_kantor
            WHERE t.created = :harian_date
              AND t.tgl_realisasi >= :awal_bulan
              AND t.tgl_realisasi <= :harian_date2
            {$filter['sql']}
            GROUP BY t.kode_cabang, k.nama_kantor
            HAVING SUM(t.plafond) > 0
        ";

        // ==========================================
        // 2. QUERY REALISASI AO (Top 5 Saja)
        // ==========================================
        $sqlAO = "
            SELECT 
                t.kode_group2,
                COALESCE(ao.nama_ao, t.kode_group2) AS nama_ao,
                t.kode_cabang,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', t.kode_cabang)) AS nama_cabang,
                SUM(t.plafond) AS total_realisasi,
                COUNT(t.no_rekening) AS noa_realisasi
            FROM nominatif t
            LEFT JOIN ao_kredit ao ON t.kode_group2 = ao.kode_group2
            LEFT JOIN kode_kantor k ON t.kode_cabang = k.kode_kantor
            WHERE t.created = :harian_date
              AND t.tgl_realisasi >= :awal_bulan
              AND t.tgl_realisasi <= :harian_date2
            {$filter['sql']}
            GROUP BY t.kode_group2, ao.nama_ao, t.kode_cabang, k.nama_kantor
            HAVING SUM(t.plafond) > 0
            ORDER BY total_realisasi DESC
            LIMIT 5
        ";

        try {
            // --- Eksekusi Cabang ---
            $stmtCabang = $this->pdo->prepare($sqlCabang);
            $stmtCabang->bindValue(':harian_date', $harian_date);
            $stmtCabang->bindValue(':harian_date2', $harian_date);
            $stmtCabang->bindValue(':awal_bulan', $awal_bulan);
            foreach ($filter['params'] as $key => $val) {
                $stmtCabang->bindValue($key, $val);
            }
            $stmtCabang->execute();
            $rowsCabang = $stmtCabang->fetchAll(PDO::FETCH_ASSOC);

            // Kita sort di PHP untuk Top dan Bottom Cabang
            $cabangData = array_map(function($r) {
                return [
                    'kode_cabang'     => $r['kode_cabang'],
                    'nama_cabang'     => $r['nama_cabang'],
                    'total_realisasi' => (float) $r['total_realisasi'],
                    'noa_realisasi'   => (int) $r['noa_realisasi']
                ];
            }, $rowsCabang);

            // Sort Descending (Top 5 Tertinggi)
            usort($cabangData, function($a, $b) {
                return $b['total_realisasi'] <=> $a['total_realisasi'];
            });
            $topCabang = array_slice($cabangData, 0, 5);

            // Sort Ascending (Bottom 5 Terendah)
            usort($cabangData, function($a, $b) {
                return $a['total_realisasi'] <=> $b['total_realisasi'];
            });
            $bottomCabang = array_slice($cabangData, 0, 5);

            // --- Eksekusi AO ---
            $stmtAO = $this->pdo->prepare($sqlAO);
            $stmtAO->bindValue(':harian_date', $harian_date);
            $stmtAO->bindValue(':harian_date2', $harian_date);
            $stmtAO->bindValue(':awal_bulan', $awal_bulan);
            foreach ($filter['params'] as $key => $val) {
                $stmtAO->bindValue($key, $val);
            }
            $stmtAO->execute();
            $rowsAO = $stmtAO->fetchAll(PDO::FETCH_ASSOC);

            $topAO = array_map(function($r) {
                return [
                    'kode_ao'         => $r['kode_group2'],
                    'nama_ao'         => $r['nama_ao'],
                    'kode_cabang'     => $r['kode_cabang'],
                    'nama_cabang'     => $r['nama_cabang'],
                    'total_realisasi' => (float) $r['total_realisasi'],
                    'noa_realisasi'   => (int) $r['noa_realisasi']
                ];
            }, $rowsAO);

            return [
                'top_cabang'    => $topCabang,
                'bottom_cabang' => $bottomCabang,
                'top_ao'        => $topAO
            ];

        } catch (PDOException $e) {
            error_log("Error getTopBottomRealisasi: " . $e->getMessage());
            return ['top_cabang' => [], 'bottom_cabang' => [], 'top_ao' => []];
        }
    }

    public function getFlowPAR($input) {
        // Logika query untuk migrasi ke L, DP menjadi KL, D, M (Flow PAR)
        // Kamu bisa comot bagian query CTE gabung di getMigrasiKolek sebelumnya
        return [];
    }

    public function getRepaymentRateCabang($input) {
        $harian_date  = $input['harian_date'] ?? date('Y-m-d');
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        
        $filter = $this->buildFilterQuery($input, 't');

        $sql = "
            SELECT 
                t.kode_cabang,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', t.kode_cabang)) AS nama_cabang,
                
                -- Data Current (Harian)
                SUM(CASE WHEN t.created = :harian_date_1 AND t.hari_menunggak = 0 THEN t.baki_debet ELSE 0 END) AS baki_lancar_curr,
                SUM(CASE WHEN t.created = :harian_date_2 THEN t.baki_debet ELSE 0 END) AS baki_total_curr,
                
                -- Data Previous (Closing Bulan Lalu)
                SUM(CASE WHEN t.created = :closing_date_1 AND t.hari_menunggak = 0 THEN t.baki_debet ELSE 0 END) AS baki_lancar_prev,
                SUM(CASE WHEN t.created = :closing_date_2 THEN t.baki_debet ELSE 0 END) AS baki_total_prev
                
            FROM nominatif t
            LEFT JOIN kode_kantor k ON t.kode_cabang = k.kode_kantor
            WHERE t.created IN (:harian_date_3, :closing_date_3)
            {$filter['sql']}
            GROUP BY t.kode_cabang, k.nama_kantor
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':harian_date_3', $harian_date);
            
            $stmt->bindValue(':closing_date_1', $closing_date);
            $stmt->bindValue(':closing_date_2', $closing_date);
            $stmt->bindValue(':closing_date_3', $closing_date);
            
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $semua_cabang = [];
            $kenaikan_rr  = [];
            $penurunan_rr = [];

            // Variabel untuk Grand Total (OS ALL)
            $grand_baki_total_curr  = 0;
            $grand_baki_lancar_curr = 0;
            $grand_baki_total_prev  = 0;
            $grand_baki_lancar_prev = 0;

            foreach ($rows as $r) {
                $baki_total_curr  = (float) $r['baki_total_curr'];
                $baki_lancar_curr = (float) $r['baki_lancar_curr'];
                $baki_total_prev  = (float) $r['baki_total_prev'];
                $baki_lancar_prev = (float) $r['baki_lancar_prev'];

                if ($baki_total_curr <= 0) continue;

                // Akumulasi ke Grand Total
                $grand_baki_total_curr  += $baki_total_curr;
                $grand_baki_lancar_curr += $baki_lancar_curr;
                $grand_baki_total_prev  += $baki_total_prev;
                $grand_baki_lancar_prev += $baki_lancar_prev;

                $rr_curr = ($baki_lancar_curr / $baki_total_curr) * 100;
                $rr_prev = $baki_total_prev > 0 ? ($baki_lancar_prev / $baki_total_prev) * 100 : 0;
                $delta = $rr_curr - $rr_prev;

                $dataCabang = [
                    'kode_cabang'    => $r['kode_cabang'],
                    'nama_cabang'    => $r['nama_cabang'],
                    'os_total'       => $baki_total_curr,
                    'os_lancar'      => $baki_lancar_curr,
                    'rr_persen_prev' => round($rr_prev, 2),
                    'rr_persen_curr' => round($rr_curr, 2),
                    'delta_rr'       => round($delta, 2)
                ];

                $semua_cabang[] = $dataCabang;

                if ($delta > 0) {
                    $kenaikan_rr[] = $dataCabang;
                } elseif ($delta < 0) {
                    $penurunan_rr[] = $dataCabang;
                }
            }

            // Hitung RR untuk Grand Total Nasional/Konsolidasi
            $grand_rr_curr = $grand_baki_total_curr > 0 ? ($grand_baki_lancar_curr / $grand_baki_total_curr) * 100 : 0;
            $grand_rr_prev = $grand_baki_total_prev > 0 ? ($grand_baki_lancar_prev / $grand_baki_total_prev) * 100 : 0;
            $grand_delta   = $grand_rr_curr - $grand_rr_prev;

            $grand_total = [
                'nama_cabang'    => 'TOTAL KONSOLIDASI',
                'os_total'       => $grand_baki_total_curr,    // <-- Ini dia OS ALL nya!
                'os_lancar'      => $grand_baki_lancar_curr,
                'rr_persen_prev' => round($grand_rr_prev, 2),
                'rr_persen_curr' => round($grand_rr_curr, 2),
                'delta_rr'       => round($grand_delta, 2)
            ];

            usort($semua_cabang, function($a, $b) { return $b['rr_persen_curr'] <=> $a['rr_persen_curr']; });
            $top_rr = array_slice($semua_cabang, 0, 5);

            usort($semua_cabang, function($a, $b) { return $a['rr_persen_curr'] <=> $b['rr_persen_curr']; });
            $bottom_rr = array_slice($semua_cabang, 0, 5);

            usort($kenaikan_rr, function($a, $b) { return $b['delta_rr'] <=> $a['delta_rr']; });
            $top_kenaikan = array_slice($kenaikan_rr, 0, 5);

            usort($penurunan_rr, function($a, $b) { return $a['delta_rr'] <=> $b['delta_rr']; });
            $top_penurunan = array_slice($penurunan_rr, 0, 5);

            // Sort berdasarkan OS Total Terbesar
            usort($semua_cabang, function($a, $b) { return $b['os_total'] <=> $a['os_total']; });
            $top_os_terbesar = array_slice($semua_cabang, 0, 5);

            return [
                'grand_total'     => $grand_total,    // OS All dan RR All Nasional
                'top_os_terbesar' => $top_os_terbesar, // Top 5 OS dan RR-nya
                'top_rr'          => $top_rr,
                'bottom_rr'       => $bottom_rr,
                'top_kenaikan'    => $top_kenaikan,
                'top_penurunan'   => $top_penurunan
            ];

        } catch (PDOException $e) {
            error_log("Error getRepaymentRateCabang: " . $e->getMessage());
            return [];
        }
    }

    public function getPerkembanganDeposito($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');

        // 1. Panggil helper filter (beri alias 'nd' untuk nominatif_deposito)
        $filter = $this->buildFilterQuery($input, 'nd');
        
        // 2. Trik sakti: Ganti 'kode_cabang' jadi 'kode_kantor' khusus untuk tabel ini
        $filter['sql'] = str_replace('nd.kode_cabang', 'nd.kode_kantor', $filter['sql']);

        // Query CTE Ultimate: Menghitung mutasi, saldo baru, saldo cair, beserta filter
        $sql = "
            WITH rekap_rek AS (
                SELECT 
                    no_rekening,
                    MAX(kode_kantor) AS kode_kantor, 
                    SUM(CASE WHEN created = :closing_date_1 THEN 1 ELSE 0 END) AS is_prev,
                    SUM(CASE WHEN created = :harian_date_1 THEN 1 ELSE 0 END) AS is_curr,
                    SUM(CASE WHEN created = :closing_date_2 THEN saldo_akhir ELSE 0 END) AS saldo_prev,
                    SUM(CASE WHEN created = :harian_date_2 THEN saldo_akhir ELSE 0 END) AS saldo_curr
                FROM nominatif_deposito nd
                WHERE created IN (:closing_date_3, :harian_date_3)
                {$filter['sql']}
                GROUP BY no_rekening
            )
            SELECT 
                r.kode_kantor,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', r.kode_kantor)) AS nama_cabang,
                
                SUM(CASE WHEN r.is_curr > 0 THEN 1 ELSE 0 END) AS noa_curr, 
                SUM(CASE WHEN r.is_prev = 0 AND r.is_curr > 0 THEN 1 ELSE 0 END) AS noa_tambah,
                SUM(CASE WHEN r.is_prev > 0 AND r.is_curr = 0 THEN 1 ELSE 0 END) AS noa_kurang,
                
                SUM(r.saldo_prev) AS saldo_prev,
                SUM(r.saldo_curr) AS saldo_curr,
                
                -- LOGIKA BARU: Hitung Saldo Uang Segar dan Saldo Kabur
                SUM(CASE WHEN r.is_prev = 0 AND r.is_curr > 0 THEN r.saldo_curr ELSE 0 END) AS saldo_baru,
                SUM(CASE WHEN r.is_prev > 0 AND r.is_curr = 0 THEN r.saldo_prev ELSE 0 END) AS saldo_cair
                
            FROM rekap_rek r
            LEFT JOIN kode_kantor k ON r.kode_kantor = k.kode_kantor
            GROUP BY r.kode_kantor, k.nama_kantor
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':closing_date_1', $closing_date);
            $stmt->bindValue(':closing_date_2', $closing_date);
            $stmt->bindValue(':closing_date_3', $closing_date);
            
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':harian_date_3', $harian_date);
            
            // 3. Bind parameter filternya (jika ada)
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Siapkan Wadah untuk 4 Korwil Saja
            $korwil_data = [];
            $korwil_list = ['SEMARANG', 'SOLO', 'BANYUMAS', 'PEKALONGAN'];
            foreach ($korwil_list as $kw) {
                $korwil_data[$kw] = [
                    'nama_korwil' => $kw, 'noa_curr' => 0, 'noa_tambah' => 0, 'noa_kurang' => 0, 
                    'saldo_prev' => 0, 'saldo_curr' => 0, 'delta_saldo' => 0, 'saldo_baru' => 0, 'saldo_cair' => 0
                ];
            }

            $grand_total = [
                'nama_korwil' => 'TOTAL KONSOLIDASI', 'noa_curr' => 0, 'noa_tambah' => 0, 'noa_kurang' => 0, 
                'saldo_prev' => 0, 'saldo_curr' => 0, 'delta_saldo' => 0, 'saldo_baru' => 0, 'saldo_cair' => 0
            ];
            $cabang_array = [];

            // 5. Olah Data dari Database
            foreach ($rows as $r) {
                $kd = str_pad($r['kode_kantor'], 3, '0', STR_PAD_LEFT);
                $saldo_prev = (float) $r['saldo_prev'];
                $saldo_curr = (float) $r['saldo_curr'];
                $delta      = $saldo_curr - $saldo_prev;
                
                $saldo_baru = (float) $r['saldo_baru'];
                $saldo_cair = (float) $r['saldo_cair'];

                $noa_curr   = (int) $r['noa_curr'];
                $noa_tambah = (int) $r['noa_tambah'];
                $noa_kurang = (int) $r['noa_kurang'];

                // Mapping Korwil
                $korwil = '';
                if ($kd >= '001' && $kd <= '007') $korwil = 'SEMARANG';
                elseif ($kd >= '008' && $kd <= '014') $korwil = 'SOLO';
                elseif ($kd >= '015' && $kd <= '021') $korwil = 'BANYUMAS';
                elseif ($kd >= '022' && $kd <= '028') $korwil = 'PEKALONGAN';

                // Tambah ke Korwil (Hanya kalau masuk 4 korwil utama)
                if ($korwil !== '') {
                    $korwil_data[$korwil]['noa_curr']    += $noa_curr;
                    $korwil_data[$korwil]['noa_tambah']  += $noa_tambah;
                    $korwil_data[$korwil]['noa_kurang']  += $noa_kurang;
                    $korwil_data[$korwil]['saldo_prev']  += $saldo_prev;
                    $korwil_data[$korwil]['saldo_curr']  += $saldo_curr;
                    $korwil_data[$korwil]['delta_saldo'] += $delta;
                    $korwil_data[$korwil]['saldo_baru']  += $saldo_baru;
                    $korwil_data[$korwil]['saldo_cair']  += $saldo_cair;
                }

                // Tambah ke Grand Total
                $grand_total['noa_curr']    += $noa_curr;
                $grand_total['noa_tambah']  += $noa_tambah;
                $grand_total['noa_kurang']  += $noa_kurang;
                $grand_total['saldo_prev']  += $saldo_prev;
                $grand_total['saldo_curr']  += $saldo_curr;
                $grand_total['delta_saldo'] += $delta;
                $grand_total['saldo_baru']  += $saldo_baru;
                $grand_total['saldo_cair']  += $saldo_cair;

                // Simpan Data Cabang untuk di-Sortir nanti
                $cabang_array[] = [
                    'kode_cabang' => $kd,
                    'nama_cabang' => $r['nama_cabang'],
                    'noa_tambah'  => $noa_tambah,
                    'noa_kurang'  => $noa_kurang,
                    'saldo_prev'  => $saldo_prev,
                    'saldo_curr'  => $saldo_curr,
                    'delta_saldo' => $delta,
                    'saldo_baru'  => $saldo_baru,
                    'saldo_cair'  => $saldo_cair
                ];
            }

            // 6. Eksekusi Kategori Sortir

            // A. Top Kenaikan (Selisih Net Paling Positif)
            $kenaikan = array_filter($cabang_array, function($c) { return $c['delta_saldo'] > 0; });
            usort($kenaikan, function($a, $b) { return $b['delta_saldo'] <=> $a['delta_saldo']; });
            $top_kenaikan = array_slice($kenaikan, 0, 5);

            // B. Top Penurunan (Selisih Net Paling Negatif)
            $penurunan = array_filter($cabang_array, function($c) { return $c['delta_saldo'] < 0; });
            usort($penurunan, function($a, $b) { return $a['delta_saldo'] <=> $b['delta_saldo']; }); 
            $top_penurunan = array_slice($penurunan, 0, 5);

            // C. Top Uang Baru Masuk (Pencetak Deposito Baru Terbesar)
            $baru = array_filter($cabang_array, function($c) { return $c['saldo_baru'] > 0; });
            usort($baru, function($a, $b) { return $b['saldo_baru'] <=> $a['saldo_baru']; });
            $top_baru = array_slice($baru, 0, 5);

            // D. Top Uang Keluar / Pencairan (Paling banyak kehilangan Deposito)
            $cair = array_filter($cabang_array, function($c) { return $c['saldo_cair'] > 0; });
            usort($cair, function($a, $b) { return $b['saldo_cair'] <=> $a['saldo_cair']; });
            $top_cair = array_slice($cair, 0, 5);

            // E. Top & Bottom Saldo Terbesar
            $saldo_aktif = array_filter($cabang_array, function($c) { return $c['saldo_curr'] > 0; });
            
            usort($saldo_aktif, function($a, $b) { return $b['saldo_curr'] <=> $a['saldo_curr']; });
            $top_saldo = array_slice($saldo_aktif, 0, 5);

            usort($saldo_aktif, function($a, $b) { return $a['saldo_curr'] <=> $b['saldo_curr']; });
            $bottom_saldo = array_slice($saldo_aktif, 0, 5);

            return [
                'per_korwil'    => array_values($korwil_data),
                'grand_total'   => $grand_total,
                'top_saldo'     => $top_saldo,
                'bottom_saldo'  => $bottom_saldo,
                'top_kenaikan'  => $top_kenaikan,
                'top_penurunan' => $top_penurunan,
                'top_baru'      => $top_baru,
                'top_pencairan' => $top_cair
            ];

        } catch (PDOException $e) {
            error_log("Error getPerkembanganDeposito: " . $e->getMessage());
            return [];
        }
    }

    public function getPerkembanganTabungan($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');

        // 1. Panggil helper filter (beri alias 'nt' untuk nominatif_tabungan)
        $filter = $this->buildFilterQuery($input, 'nt');
        
        // 2. Trik sakti: Ganti 'kode_cabang' jadi 'kode_kantor' khusus untuk tabel ini
        $filter['sql'] = str_replace('nt.kode_cabang', 'nt.kode_kantor', $filter['sql']);

        // Query CTE Ultimate: Menghitung mutasi, saldo baru, saldo cair untuk TABUNGAN
        $sql = "
            WITH rekap_rek AS (
                SELECT 
                    no_rekening,
                    MAX(kode_kantor) AS kode_kantor, 
                    SUM(CASE WHEN created = :closing_date_1 THEN 1 ELSE 0 END) AS is_prev,
                    SUM(CASE WHEN created = :harian_date_1 THEN 1 ELSE 0 END) AS is_curr,
                    -- Perhatikan: Pakai kolom 'saldo' sesuai screenshot, bukan 'saldo_akhir'
                    SUM(CASE WHEN created = :closing_date_2 THEN saldo ELSE 0 END) AS saldo_prev,
                    SUM(CASE WHEN created = :harian_date_2 THEN saldo ELSE 0 END) AS saldo_curr
                FROM nominatif_tabungan nt
                WHERE created IN (:closing_date_3, :harian_date_3)
                {$filter['sql']}
                GROUP BY no_rekening
            )
            SELECT 
                r.kode_kantor,
                COALESCE(k.nama_kantor, CONCAT('CABANG ', r.kode_kantor)) AS nama_cabang,
                
                SUM(CASE WHEN r.is_curr > 0 THEN 1 ELSE 0 END) AS noa_curr, 
                SUM(CASE WHEN r.is_prev = 0 AND r.is_curr > 0 THEN 1 ELSE 0 END) AS noa_tambah,
                SUM(CASE WHEN r.is_prev > 0 AND r.is_curr = 0 THEN 1 ELSE 0 END) AS noa_kurang,
                
                SUM(r.saldo_prev) AS saldo_prev,
                SUM(r.saldo_curr) AS saldo_curr,
                
                -- Hitung Saldo Uang Segar (Rekening Baru) dan Saldo Kabur (Tutup Rekening)
                SUM(CASE WHEN r.is_prev = 0 AND r.is_curr > 0 THEN r.saldo_curr ELSE 0 END) AS saldo_baru,
                SUM(CASE WHEN r.is_prev > 0 AND r.is_curr = 0 THEN r.saldo_prev ELSE 0 END) AS saldo_cair
                
            FROM rekap_rek r
            LEFT JOIN kode_kantor k ON r.kode_kantor = k.kode_kantor
            GROUP BY r.kode_kantor, k.nama_kantor
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':closing_date_1', $closing_date);
            $stmt->bindValue(':closing_date_2', $closing_date);
            $stmt->bindValue(':closing_date_3', $closing_date);
            
            $stmt->bindValue(':harian_date_1', $harian_date);
            $stmt->bindValue(':harian_date_2', $harian_date);
            $stmt->bindValue(':harian_date_3', $harian_date);
            
            // 3. Bind parameter filternya (jika ada)
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Siapkan Wadah untuk 4 Korwil Saja
            $korwil_data = [];
            $korwil_list = ['SEMARANG', 'SOLO', 'BANYUMAS', 'PEKALONGAN'];
            foreach ($korwil_list as $kw) {
                $korwil_data[$kw] = [
                    'nama_korwil' => $kw, 'noa_curr' => 0, 'noa_tambah' => 0, 'noa_kurang' => 0, 
                    'saldo_prev' => 0, 'saldo_curr' => 0, 'delta_saldo' => 0, 'saldo_baru' => 0, 'saldo_cair' => 0
                ];
            }

            $grand_total = [
                'nama_korwil' => 'TOTAL KONSOLIDASI', 'noa_curr' => 0, 'noa_tambah' => 0, 'noa_kurang' => 0, 
                'saldo_prev' => 0, 'saldo_curr' => 0, 'delta_saldo' => 0, 'saldo_baru' => 0, 'saldo_cair' => 0
            ];
            $cabang_array = [];

            // 5. Olah Data dari Database
            foreach ($rows as $r) {
                $kd = str_pad($r['kode_kantor'], 3, '0', STR_PAD_LEFT);
                $saldo_prev = (float) $r['saldo_prev'];
                $saldo_curr = (float) $r['saldo_curr'];
                $delta      = $saldo_curr - $saldo_prev;
                
                $saldo_baru = (float) $r['saldo_baru'];
                $saldo_cair = (float) $r['saldo_cair'];

                $noa_curr   = (int) $r['noa_curr'];
                $noa_tambah = (int) $r['noa_tambah'];
                $noa_kurang = (int) $r['noa_kurang'];

                // Mapping Korwil
                $korwil = '';
                if ($kd >= '001' && $kd <= '007') $korwil = 'SEMARANG';
                elseif ($kd >= '008' && $kd <= '014') $korwil = 'SOLO';
                elseif ($kd >= '015' && $kd <= '021') $korwil = 'BANYUMAS';
                elseif ($kd >= '022' && $kd <= '028') $korwil = 'PEKALONGAN';

                // Tambah ke Korwil (Hanya kalau masuk 4 korwil utama)
                if ($korwil !== '') {
                    $korwil_data[$korwil]['noa_curr']    += $noa_curr;
                    $korwil_data[$korwil]['noa_tambah']  += $noa_tambah;
                    $korwil_data[$korwil]['noa_kurang']  += $noa_kurang;
                    $korwil_data[$korwil]['saldo_prev']  += $saldo_prev;
                    $korwil_data[$korwil]['saldo_curr']  += $saldo_curr;
                    $korwil_data[$korwil]['delta_saldo'] += $delta;
                    $korwil_data[$korwil]['saldo_baru']  += $saldo_baru;
                    $korwil_data[$korwil]['saldo_cair']  += $saldo_cair;
                }

                // Tambah ke Grand Total
                $grand_total['noa_curr']    += $noa_curr;
                $grand_total['noa_tambah']  += $noa_tambah;
                $grand_total['noa_kurang']  += $noa_kurang;
                $grand_total['saldo_prev']  += $saldo_prev;
                $grand_total['saldo_curr']  += $saldo_curr;
                $grand_total['delta_saldo'] += $delta;
                $grand_total['saldo_baru']  += $saldo_baru;
                $grand_total['saldo_cair']  += $saldo_cair;

                // Simpan Data Cabang untuk di-Sortir nanti
                $cabang_array[] = [
                    'kode_cabang' => $kd,
                    'nama_cabang' => $r['nama_cabang'],
                    'noa_tambah'  => $noa_tambah,
                    'noa_kurang'  => $noa_kurang,
                    'saldo_prev'  => $saldo_prev,
                    'saldo_curr'  => $saldo_curr,
                    'delta_saldo' => $delta,
                    'saldo_baru'  => $saldo_baru,
                    'saldo_cair'  => $saldo_cair
                ];
            }

            // 6. Eksekusi Kategori Sortir

            $kenaikan = array_filter($cabang_array, function($c) { return $c['delta_saldo'] > 0; });
            usort($kenaikan, function($a, $b) { return $b['delta_saldo'] <=> $a['delta_saldo']; });
            $top_kenaikan = array_slice($kenaikan, 0, 5);

            $penurunan = array_filter($cabang_array, function($c) { return $c['delta_saldo'] < 0; });
            usort($penurunan, function($a, $b) { return $a['delta_saldo'] <=> $b['delta_saldo']; }); 
            $top_penurunan = array_slice($penurunan, 0, 5);

            $baru = array_filter($cabang_array, function($c) { return $c['saldo_baru'] > 0; });
            usort($baru, function($a, $b) { return $b['saldo_baru'] <=> $a['saldo_baru']; });
            $top_baru = array_slice($baru, 0, 5);

            $cair = array_filter($cabang_array, function($c) { return $c['saldo_cair'] > 0; });
            usort($cair, function($a, $b) { return $b['saldo_cair'] <=> $a['saldo_cair']; });
            $top_cair = array_slice($cair, 0, 5);

            $saldo_aktif = array_filter($cabang_array, function($c) { return $c['saldo_curr'] > 0; });
            
            usort($saldo_aktif, function($a, $b) { return $b['saldo_curr'] <=> $a['saldo_curr']; });
            $top_saldo = array_slice($saldo_aktif, 0, 5);

            usort($saldo_aktif, function($a, $b) { return $a['saldo_curr'] <=> $b['saldo_curr']; });
            $bottom_saldo = array_slice($saldo_aktif, 0, 5);

            return [
                'per_korwil'    => array_values($korwil_data),
                'grand_total'   => $grand_total,
                'top_saldo'     => $top_saldo,
                'bottom_saldo'  => $bottom_saldo,
                'top_kenaikan'  => $top_kenaikan,
                'top_penurunan' => $top_penurunan,
                'top_baru'      => $top_baru,
                'top_pencairan' => $top_cair
            ];

        } catch (PDOException $e) {
            error_log("Error getPerkembanganTabungan: " . $e->getMessage());
            return [];
        }
    }

}