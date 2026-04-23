<?php

require_once __DIR__ . '/../helpers/response.php';


class TransaksiController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

 
    /**
     * 8. REKAP TRANSAKSI DIGITAL (VA, Branchless, QRIS)
     */
    public function getRekapTransaksiChannel($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $harian  = $b['harian_date'] ?? date('Y-m-d');
        $kode_kantor = !empty($b['kode_kantor']) ? str_pad($b['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        $korwil  = !empty($b['korwil']) ? strtoupper($b['korwil']) : null;
        $kankas  = !empty($b['kode_kankas']) ? $b['kode_kankas'] : null;
        
        $channel = !empty($b['channel']) ? strtoupper($b['channel']) : 'ALL';

        if (!$harian) return sendResponse(400, "Tanggal Actual (Harian) wajib diisi.", null);

        // 🔥 FIX: Bisa Custom Closing Date dari Input. Jika kosong, auto-hitung akhir bulan kemarin.
        if (!empty($b['closing_date'])) {
            $closing_date = $b['closing_date'];
        } else {
            $ts_harian = strtotime($harian);
            $closing_date = date('Y-m-t', strtotime(date('Y-m-01', $ts_harian) . ' -1 day'));
        }

        // --- 1. BUILD FILTER QUERY ---
        $sqlFilter = "";
        $params = [
            ':harian'  => $harian,
            ':closing' => $closing_date
        ];

        // Filter Cabang / Korwil
        if ($kode_kantor && $kode_kantor !== '000') {
            $sqlFilter .= " AND t.kantor = :kode_kantor ";
            $params[':kode_kantor'] = $kode_kantor;
        } elseif ($korwil) {
            $kw_start = null; $kw_end = null;
            switch ($korwil) {
                case 'SEMARANG':   $kw_start = '001'; $kw_end = '007'; break;
                case 'SOLO':       $kw_start = '008'; $kw_end = '014'; break;
                case 'BANYUMAS':   $kw_start = '015'; $kw_end = '021'; break;
                case 'PEKALONGAN': $kw_start = '022'; $kw_end = '028'; break;
            }
            if ($kw_start && $kw_end) {
                $sqlFilter .= " AND t.kantor BETWEEN :kw_start AND :kw_end ";
                $params[':kw_start'] = $kw_start;
                $params[':kw_end'] = $kw_end;
            }
        }

        // Filter Kankas
        if ($kankas) {
            $sqlFilter .= " AND TRIM(t.kankas) = :kode_kankas ";
            $params[':kode_kankas'] = $kankas;
        }

        // Filter Transaksi per Channel
        $chanFilter = "";
        if ($channel === 'VA') {
            $chanFilter = " AND TRIM(t.kode_transaksi) = '320' ";
        } elseif ($channel === 'BRANCHLESS') {
            $chanFilter = " AND TRIM(t.kode_transaksi) IN ('150', '152') ";
        } elseif ($channel === 'QRIS') {
            $chanFilter = " AND TRIM(t.kode_transaksi) IN ('140', '16', '162') ";
        } else {
            $chanFilter = " AND TRIM(t.kode_transaksi) IN ('320', '150', '152', '140', '16', '162') ";
        }

        // --- 2. MAIN QUERY ---
        $sql = "SELECT 
                    CASE 
                        WHEN TRIM(t.kode_transaksi) = '320' THEN 'Virtual Account (VA)'
                        WHEN TRIM(t.kode_transaksi) IN ('150', '152') THEN 'Branchless Banking'
                        WHEN TRIM(t.kode_transaksi) IN ('140', '16', '162') THEN 'QRIS'
                    END as kategori_trx,
                    CASE 
                        WHEN TRIM(t.kode_transaksi) = '320' THEN 1
                        WHEN TRIM(t.kode_transaksi) IN ('150', '152') THEN 2
                        WHEN TRIM(t.kode_transaksi) IN ('140', '16', '162') THEN 3
                    END as sort_order,
                    COUNT(1) as total_frekuensi,
                    SUM(t.jumlah) as total_nominal,
                    SUM(COALESCE(t.adm, 0)) as total_adm
                FROM va t 
                WHERE t.tgl_transaksi > :closing AND t.tgl_transaksi <= :harian
                $sqlFilter $chanFilter
                GROUP BY kategori_trx, sort_order
                ORDER BY sort_order ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) { $stmt->bindValue($key, $val); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Dinamis Filter Nama
            $nama_kantor_filter = "SEMUA CABANG (KONSOLIDASI)";
            if ($kankas) {
                $stmtK = $this->pdo->prepare("SELECT deskripsi_group1 FROM kankas WHERE kode_group1 = ?");
                $stmtK->execute([$kankas]);
                $nama_kantor_filter = $stmtK->fetchColumn() ?: "KANKAS " . $kankas;
            } elseif ($kode_kantor && $kode_kantor !== '000') {
                $stmtK = $this->pdo->prepare("SELECT nama_kantor FROM kode_kantor WHERE kode_kantor = ?");
                $stmtK->execute([$kode_kantor]);
                $nama_kantor_filter = $stmtK->fetchColumn() ?: "CABANG " . $kode_kantor;
            } elseif ($korwil) {
                $nama_kantor_filter = "KORWIL " . $korwil;
            }

            $grandTotal = ['total_frekuensi' => 0, 'total_nominal' => 0, 'total_adm' => 0];

            foreach ($rows as &$r) {
                $r['total_frekuensi'] = (int)$r['total_frekuensi'];
                $r['total_nominal']   = (float)$r['total_nominal'];
                $r['total_adm']       = (float)$r['total_adm'];

                $grandTotal['total_frekuensi'] += $r['total_frekuensi'];
                $grandTotal['total_nominal']   += $r['total_nominal'];
                $grandTotal['total_adm']       += $r['total_adm'];
            }

            return sendResponse(200, "Berhasil", [
                'meta' => [
                    'filter_aktif' => $nama_kantor_filter, 
                    'harian_date'  => $harian,
                    'closing_date' => $closing_date,
                    'channel_aktif'=> $channel
                ],
                'grand_total' => $grandTotal,
                'data' => $rows
            ]);
        } catch (PDOException $e) { 
            error_log("Error Rekap Transaksi: " . $e->getMessage());
            return sendResponse(500, "PDO Error: " . $e->getMessage(), null); 
        }
    }

    /**
     * 9. TREN NOMINAL & NOA VA (Chart Line)
     * Tabel: va
     * Syarat VA: kode_transaksi = '320'
     */
    public function getTrenNominalVa($input = null) {
        set_time_limit(300); ini_set('memory_limit', '1024M');

        $b = is_array($input) ? $input : [];
        $harian_date = $b['harian_date'] ?? date('Y-m-d');
        $periode     = $b['periode'] ?? 'bulanan'; // 7_hari, 30_hari, bulanan, tahunan
        $kode_kantor = !empty($b['kode_kantor']) ? str_pad($b['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        $korwil      = !empty($b['korwil']) ? strtoupper($b['korwil']) : null;
        $kankas      = !empty($b['kode_kankas']) ? $b['kode_kankas'] : null;
        $bank_filter = !empty($b['bank']) ? $b['bank'] : 'ALL'; // '1' = Mandiri, '4' = Permata

        if (!$harian_date) return sendResponse(400, "Tanggal Actual (Harian) wajib diisi.", null);

        // --- 1. GENERATE PERIODE & TANGGAL (X-AXIS CHART) ---
        $keys = [];
        $labels = [];
        $startDate = "";
        $endDate = "";
        $sqlGroup = "";

        if ($periode === '7_hari') {
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
                $keys[] = $d;
                $labels[] = date('d M', strtotime($d));
            }
            $startDate = $keys[0] . " 00:00:00";
            $endDate   = $harian_date . " 23:59:59";
            $sqlGroup  = "DATE(t.tgl_transaksi)";

        } elseif ($periode === '30_hari') {
            for ($i = 29; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i day", strtotime($harian_date)));
                $keys[] = $d;
                $labels[] = date('d M', strtotime($d));
            }
            $startDate = $keys[0] . " 00:00:00";
            $endDate   = $harian_date . " 23:59:59";
            $sqlGroup  = "DATE(t.tgl_transaksi)";

        } elseif ($periode === 'tahunan') {
            $startYear = 2020;
            $currentYear = (int)date('Y', strtotime($harian_date));
            for ($year = $startYear; $year <= $currentYear; $year++) {
                $keys[] = (string)$year;
                $labels[] = (string)$year;
            }
            $startDate = "2020-01-01 00:00:00";
            $endDate   = date('Y-12-31 23:59:59', strtotime($harian_date));
            $sqlGroup  = "YEAR(t.tgl_transaksi)";

        } else {
            // Default: bulanan (6 Bulan Terakhir)
            for ($i = 5; $i >= 0; $i--) {
                $d = date('Y-m', strtotime("-$i month", strtotime($harian_date)));
                $keys[] = $d;
                $labels[] = date('M Y', strtotime($d . '-01'));
            }
            $startDate = $keys[0] . "-01 00:00:00";
            $endDate   = date('Y-m-t 23:59:59', strtotime($harian_date));
            $sqlGroup  = "DATE_FORMAT(t.tgl_transaksi, '%Y-%m')";
        }

        // --- 2. BUILD FILTER QUERY ---
        $sqlFilter = "";
        $params = [
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ];

        // Filter Cabang / Korwil / Kankas
        if ($kode_kantor && $kode_kantor !== '000') {
            $sqlFilter .= " AND t.kantor = :kode_kantor ";
            $params[':kode_kantor'] = $kode_kantor;
        } elseif ($korwil) {
            $kw_start = null; $kw_end = null;
            switch ($korwil) {
                case 'SEMARANG':   $kw_start = '001'; $kw_end = '007'; break;
                case 'SOLO':       $kw_start = '008'; $kw_end = '014'; break;
                case 'BANYUMAS':   $kw_start = '015'; $kw_end = '021'; break;
                case 'PEKALONGAN': $kw_start = '022'; $kw_end = '028'; break;
            }
            if ($kw_start && $kw_end) {
                $sqlFilter .= " AND t.kantor BETWEEN :kw_start AND :kw_end ";
                $params[':kw_start'] = $kw_start;
                $params[':kw_end'] = $kw_end;
            }
        }

        if ($kankas) {
            $sqlFilter .= " AND COALESCE(NULLIF(TRIM(t.kankas), ''), CONCAT(t.kantor, '000')) = :kode_kankas ";
            $params[':kode_kankas'] = $kankas;
        }

        // Filter Bank (Akhiran norek_aba)
        if ($bank_filter === '1') {
            $sqlFilter .= " AND t.norek_aba LIKE '%0001000001' ";
        } elseif ($bank_filter === '4') {
            $sqlFilter .= " AND t.norek_aba LIKE '%0001000004' ";
        } else {
            $sqlFilter .= " AND (t.norek_aba LIKE '%0001000001' OR t.norek_aba LIKE '%0001000004') ";
        }

        // --- 3. MAIN QUERY ---
        $sql = "SELECT 
                    $sqlGroup as periode_key,
                    CASE 
                        WHEN t.norek_aba LIKE '%0001000001' THEN 'Mandiri'
                        WHEN t.norek_aba LIKE '%0001000004' THEN 'Permata'
                    END as nama_bank,
                    SUM(t.jumlah) as total_nominal,
                    COUNT(DISTINCT t.no_rekening) as total_noa
                FROM va t 
                WHERE t.tgl_transaksi >= :start_date AND t.tgl_transaksi <= :end_date
                AND TRIM(t.kode_transaksi) = '320'
                $sqlFilter
                GROUP BY periode_key, nama_bank
                ORDER BY periode_key ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $val) { $stmt->bindValue($key, $val); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // --- 4. FORMAT DATA UNTUK CHART (MAPPING) ---
            $dataMandiriNominal = array_fill(0, count($keys), 0);
            $dataPermataNominal = array_fill(0, count($keys), 0);
            $dataMandiriNoa     = array_fill(0, count($keys), 0);
            $dataPermataNoa     = array_fill(0, count($keys), 0);

            // Buat index pencarian cepat (Lookup)
            $keyIndex = array_flip($keys);

            $grandTotalNominal = 0;
            $grandTotalNoa = 0;

            foreach ($rows as $r) {
                $pkey = (string)$r['periode_key'];
                if (!isset($keyIndex[$pkey])) continue;
                
                $idx = $keyIndex[$pkey];
                $nominal = (float)$r['total_nominal'];
                $noa = (int)$r['total_noa'];

                $grandTotalNominal += $nominal;
                $grandTotalNoa += $noa;
                
                if ($r['nama_bank'] === 'Mandiri') {
                    $dataMandiriNominal[$idx] = $nominal;
                    $dataMandiriNoa[$idx]     = $noa;
                } elseif ($r['nama_bank'] === 'Permata') {
                    $dataPermataNominal[$idx] = $nominal;
                    $dataPermataNoa[$idx]     = $noa;
                }
            }

            // Siapkan Series Nominal
            $seriesNominal = [];
            if ($bank_filter === '1' || $bank_filter === 'ALL') {
                $seriesNominal[] = ['name' => 'Bank Mandiri', 'data' => $dataMandiriNominal];
            }
            if ($bank_filter === '4' || $bank_filter === 'ALL') {
                $seriesNominal[] = ['name' => 'Bank Permata Syariah', 'data' => $dataPermataNominal];
            }

            // Siapkan Series NOA
            $seriesNoa = [];
            if ($bank_filter === '1' || $bank_filter === 'ALL') {
                $seriesNoa[] = ['name' => 'Bank Mandiri', 'data' => $dataMandiriNoa];
            }
            if ($bank_filter === '4' || $bank_filter === 'ALL') {
                $seriesNoa[] = ['name' => 'Bank Permata Syariah', 'data' => $dataPermataNoa];
            }

            return sendResponse(200, "Berhasil ambil tren VA", [
                'meta' => [
                    'periode_aktif'   => $periode,
                    'bank_aktif'      => $bank_filter,
                    'total_akumulasi_nominal' => $grandTotalNominal,
                    'total_akumulasi_noa'     => $grandTotalNoa
                ],
                'chart_nominal' => [
                    'labels' => $labels,
                    'series' => $seriesNominal
                ],
                'chart_noa' => [
                    'labels' => $labels,
                    'series' => $seriesNoa
                ]
            ]);

        } catch (PDOException $e) { 
            error_log("Error Tren VA: " . $e->getMessage());
            return sendResponse(500, "PDO Error: " . $e->getMessage(), null); 
        }
    }





}