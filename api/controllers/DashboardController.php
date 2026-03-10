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
    public function getExecutiveDashboard($input = []) {
        try {
            $data = [
                'tren_npl'             => $this->getTrenNPL($input),
                'runoff_vs_realisasi'  => $this->getRunOffVsRealisasi($input),
                'top_bottom_npl'       => $this->getTopBottomNPL($input),
                'flow_par_terbesar'    => $this->getFlowPAR($input),
                'repayment_rate'       => $this->getRepaymentRate($input),
                'recovery_vs_flow_par' => $this->getRecoveryVsFlowPAR($input),
                'realisasi_per_korwil' => $this->getRealisasiPerKorwil($input),
                
                // Disembunyikan dulu sesuai request
                // 'capaian_rbb'        => null, 
                // 'tabungan_deposito'  => null  
            ];

            sendResponse(200, "Berhasil memuat Executive Dashboard", $data);

        } catch (Exception $e) {
            sendResponse(500, "Error Dashboard: " . $e->getMessage());
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
        // Default ke 'bulanan' kalau Front-End tidak mengirim parameter
        $periode = $input['periode'] ?? 'bulanan'; 
        
        $dates = [$harian_date]; // Selalu masukkan tanggal hari ini/harian
        
        // 1. Generate Tanggal Secara Dinamis
        if ($periode === 'mingguan') {
            // Mundur 6 minggu ke belakang (interval 7 hari)
            for ($i = 1; $i <= 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i week", strtotime($harian_date)));
            }
        } elseif ($periode === '7_hari') {
            // Mundur 6 hari ke belakang (total 7 hari termasuk hari ini)
            for ($i = 1; $i <= 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
            }
        } elseif ($periode === '14_hari') {
            // Mundur 13 hari ke belakang (total 14 hari termasuk hari ini)
            for ($i = 1; $i <= 13; $i++) {
                $dates[] = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
            }
        } else {
            // Mundur 6 bulan ke belakang (ambil data closing akhir bulan)
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

        // 3. Ambil Filter Cabang/Korwil dari Helper
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
            
            // Bind parameter tanggal
            foreach ($inParams as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            // Bind parameter filter cabang/korwil
            foreach ($filter['params'] as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format data untuk output
            $formattedData = [];
            foreach ($rows as $r) {
                // Formatting label biar cantik di chart Front-End
                if ($periode === 'bulanan') {
                    $label = date('M Y', strtotime($r['tanggal'])); // cth: Mar 2026
                } elseif ($periode === 'mingguan') {
                    $label = date('d M Y', strtotime($r['tanggal'])); // cth: 10 Mar 2026
                } else {
                    // Untuk 7_hari dan 14_hari dibikin lebih ringkas biar sumbu X di chart gak sempit
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

    public function getFlowPAR($input) {
        // Logika query untuk migrasi ke L, DP menjadi KL, D, M (Flow PAR)
        // Kamu bisa comot bagian query CTE gabung di getMigrasiKolek sebelumnya
        return [];
    }

    public function getRepaymentRate($input) {
        // Logika untuk repayment rate yang mengalami penurunan
        return [];
    }

    public function getRecoveryVsFlowPAR($input) {
        // Logika perbandingan recovery dan flow par
        return [];
    }

    public function getRealisasiPerKorwil($input) {
        // Query group by Korwil untuk realisasi
        return [];
    }
}