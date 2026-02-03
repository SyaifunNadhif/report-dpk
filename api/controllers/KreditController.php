<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/MobHelper.php';

class KreditController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getRealisasiKredit($input = []) {
        $closing_date = isset($input['closing_date']) ? $input['closing_date'] : date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = isset($input['harian_date'])  ? $input['harian_date']  : date('Y-m-d');
        $awal_date    = date('Y-m-01', strtotime($harian_date)); // awal bulan dari harian_date

        $sql = "
            WITH closing AS (
                SELECT no_rekening, kode_cabang, baki_debet
                FROM nominatif
                WHERE created = :closing
            ),
            harian AS (
                SELECT no_rekening, kode_cabang, baki_debet
                FROM nominatif
                WHERE created = :harian
            ),
            runoff AS (
                SELECT 
                    c.no_rekening,
                    c.kode_cabang,
                    c.baki_debet - COALESCE(h.baki_debet, 0) AS run_off
                FROM closing c
                LEFT JOIN harian h ON c.no_rekening = h.no_rekening
            ),
            runoff_agg AS (
                SELECT kode_cabang, SUM(run_off) AS total_run_off
                FROM runoff
                GROUP BY kode_cabang
            ),
            realisasi AS (
                SELECT kode_cabang, no_rekening, MAX(plafond) AS plafond
                FROM nominatif
                WHERE created = :harian_real_1
                AND tgl_realisasi BETWEEN :awal_realisasi AND :harian_real_2
                GROUP BY kode_cabang, no_rekening
            ),
            realisasi_agg AS (
                SELECT 
                    kode_cabang,
                    COUNT(DISTINCT no_rekening) AS noa_realisasi,
                    SUM(plafond) AS total_realisasi
                FROM realisasi
                GROUP BY kode_cabang
            ),
            rekap AS (
                SELECT 
                    k.kode_kantor AS kode_cabang,
                    k.nama_kantor,
                    COALESCE(r.noa_realisasi, 0) AS noa_realisasi,
                    COALESCE(r.total_realisasi, 0) AS total_realisasi,
                    COALESCE(ro.total_run_off, 0) AS total_run_off,
                    COALESCE(r.total_realisasi, 0) - COALESCE(ro.total_run_off, 0) AS growth
                FROM kode_kantor k
                LEFT JOIN realisasi_agg r ON r.kode_cabang = k.kode_kantor
                LEFT JOIN runoff_agg ro ON ro.kode_cabang = k.kode_kantor
                WHERE k.kode_kantor <> '000'
            )

            SELECT 
                kode_cabang,
                nama_kantor,
                noa_realisasi,
                total_realisasi,
                total_run_off,
                growth
            FROM rekap

            UNION ALL

            SELECT 
                NULL,
                'TOTAL KONSOLIDASI',
                SUM(noa_realisasi),
                SUM(total_realisasi),
                SUM(total_run_off),
                SUM(growth)
            FROM rekap

            ORDER BY 
                CASE WHEN nama_kantor = 'TOTAL KONSOLIDASI' THEN 1 ELSE 0 END,
                kode_cabang
        ";


        $stmt = $this->pdo->prepare($sql);

        // Hati-hati, ini HARUS cocok semua dengan nama parameter di SQL
        $stmt->bindValue(':closing', $closing_date);
        $stmt->bindValue(':harian', $harian_date);
        $stmt->bindValue(':harian_real_1', $harian_date);
        $stmt->bindValue(':harian_real_2', $harian_date);
        $stmt->bindValue(':awal_realisasi', $awal_date);


        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, "Berhasil ambil data realisasi kredit", $data);
    }

    public function getDetailRealisasiKredit($input = []) {
        $kode_cabang  = str_pad($input['kode_kantor'] ?? '', 3, '0', STR_PAD_LEFT);
        $harian_date  = $input['harian_date'] ?? date('Y-m-d');
        $awal_date    = date('Y-m-01', strtotime($harian_date));

        if (!$kode_cabang) {
            sendResponse(400, "Kode kantor wajib diisi");
            return;
        }

        $sql = "
            SELECT 
                no_rekening,
                nama_nasabah,
                plafond,
                alamat,
                tgl_realisasi,
                tgl_jatuh_tempo
            FROM nominatif
            WHERE kode_cabang = :kode_cabang
            AND created = :harian_date
            AND tgl_realisasi BETWEEN :awal_date AND :harian_date_1
            ORDER BY no_rekening
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':kode_cabang', $kode_cabang);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':harian_date_1', $harian_date);

            $stmt->bindValue(':awal_date', $awal_date);
            

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, "Detail realisasi kredit cabang $kode_cabang", $data);
        } catch (PDOException $e) {
            sendResponse(500, "PDO Error: " . $e->getMessage());
        }
    }

    public function getMigrasiKolek1($input) {
        $closing_date = !empty($input['closing_date']) ? $input['closing_date'] : date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = !empty($input['harian_date'])  ? $input['harian_date']  : date('Y-m-d');
        $kode_kantor  = !empty($input['kode_kantor'])  ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;

        // filter cabang (pakai placeholder BERBEDA)
        $filter_cabang_closing = $kode_kantor ? " AND c.kode_cabang = :kode_kantor_c " : "";
        $filter_cabang_harian  = $kode_kantor ? " AND h.kode_cabang = :kode_kantor_h " : "";

        $sql = "
            WITH 
            closing AS (
                SELECT c.no_rekening, c.kode_cabang, c.kolektibilitas AS kolek_closed, c.baki_debet AS baki_closed
                FROM nominatif c
                WHERE c.created = :closing_date_m
                AND c.kolektibilitas IN ('L','DP','KL','D','M')
                $filter_cabang_closing
            ),
            harian AS (
                SELECT h.no_rekening, h.kode_cabang, h.kolektibilitas AS kolek_update, h.baki_debet AS baki_harian
                FROM nominatif h
                WHERE h.created = :harian_date_m
                AND h.kolektibilitas IN ('L','DP','KL','D','M')
                $filter_cabang_harian
            ),
            gabung AS (
                SELECT 
                    c.kolek_closed,
                    h.kolek_update,
                    c.baki_closed,
                    COALESCE(h.baki_harian, 0) AS baki_harian,
                    (c.baki_closed - COALESCE(h.baki_harian, 0)) AS pembayaran,
                    CASE WHEN h.no_rekening IS NULL THEN 1 ELSE 0 END AS is_lunas
                FROM closing c
                LEFT JOIN harian h ON h.no_rekening = c.no_rekening
            )
            SELECT 
                g.kolek_closed,
                SUM(g.baki_closed) AS saldo_closed,
                SUM(CASE WHEN g.kolek_update = 'L'  THEN g.baki_harian ELSE 0 END) AS migrasi_L,
                SUM(CASE WHEN g.kolek_update = 'DP' THEN g.baki_harian ELSE 0 END) AS migrasi_DP,
                SUM(CASE WHEN g.kolek_update = 'KL' THEN g.baki_harian ELSE 0 END) AS migrasi_KL,
                SUM(CASE WHEN g.kolek_update = 'D'  THEN g.baki_harian ELSE 0 END) AS migrasi_D,
                SUM(CASE WHEN g.kolek_update = 'M'  THEN g.baki_harian ELSE 0 END) AS migrasi_M,
                SUM(g.pembayaran) AS pembayaran,
                SUM(CASE WHEN g.is_lunas = 1 THEN g.baki_closed ELSE 0 END) AS lunas_osc
            FROM gabung g
            GROUP BY g.kolek_closed

            UNION ALL

            SELECT 
                'TOTAL' AS kolek_closed,
                SUM(g.baki_closed) AS saldo_closed,
                SUM(CASE WHEN g.kolek_update = 'L'  THEN g.baki_harian ELSE 0 END) AS migrasi_L,
                SUM(CASE WHEN g.kolek_update = 'DP' THEN g.baki_harian ELSE 0 END) AS migrasi_DP,
                SUM(CASE WHEN g.kolek_update = 'KL' THEN g.baki_harian ELSE 0 END) AS migrasi_KL,
                SUM(CASE WHEN g.kolek_update = 'D'  THEN g.baki_harian ELSE 0 END) AS migrasi_D,
                SUM(CASE WHEN g.kolek_update = 'M'  THEN g.baki_harian ELSE 0 END) AS migrasi_M,
                SUM(g.pembayaran) AS pembayaran,
                SUM(CASE WHEN g.is_lunas = 1 THEN g.baki_closed ELSE 0 END) AS lunas_osc
            FROM gabung g

            ORDER BY 
                CASE 
                    WHEN kolek_closed = 'L'     THEN 1
                    WHEN kolek_closed = 'DP'    THEN 2
                    WHEN kolek_closed = 'KL'    THEN 3
                    WHEN kolek_closed = 'D'     THEN 4
                    WHEN kolek_closed = 'M'     THEN 5
                    WHEN kolek_closed = 'TOTAL' THEN 99
                    ELSE 98
                END
        ";

        try {
            // 1) Eksekusi migrasi
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date_m', $closing_date);
            $stmt->bindValue(':harian_date_m',  $harian_date);
            if ($kode_kantor) {
                $stmt->bindValue(':kode_kantor_c', $kode_kantor);
                $stmt->bindValue(':kode_kantor_h', $kode_kantor);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ambil pembayaran TOTAL
            $pembayaran_total = 0.0; $totalIdx = null;
            foreach ($rows as $i => $r) {
                if (($r['kolek_closed'] ?? '') === 'TOTAL') {
                    $totalIdx = $i;
                    $pembayaran_total = (float)($r['pembayaran'] ?? 0);
                    break;
                }
            }

            // ================== META: %NPL, REALISASI, GROWTH ==================
            $awal_bulan = date('Y-m-01', strtotime($harian_date));
            $filter_np = $kode_kantor ? " AND kode_cabang = :kode_np " : "";
            $filter_nn = $kode_kantor ? " AND kode_cabang = :kode_nn " : "";
            $filter_tp = $kode_kantor ? " AND kode_cabang = :kode_tp " : "";
            $filter_tn = $kode_kantor ? " AND kode_cabang = :kode_tn " : "";
            $filter_rl = $kode_kantor ? " AND kode_cabang = :kode_rl " : "";

            $sqlMeta = "
                SELECT
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :closing_date_np
                    AND kolektibilitas IN ('KL','D','M') $filter_np) AS npl_prev,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :harian_date_nn
                    AND kolektibilitas IN ('KL','D','M') $filter_nn) AS npl_now,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :closing_date_tp
                    AND kolektibilitas IN ('L','DP','KL','D','M') $filter_tp) AS total_prev,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :harian_date_tn
                    AND kolektibilitas IN ('L','DP','KL','D','M') $filter_tn) AS total_now,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :harian_date_rl1
                    AND tgl_realisasi >= :awal_bulan
                    AND tgl_realisasi <= :harian_date_rl2 $filter_rl) AS realisasi_bulan_ini
            ";

            $st = $this->pdo->prepare($sqlMeta);
            // bind tanggal – semua UNIK
            $st->bindValue(':closing_date_np', $closing_date);
            $st->bindValue(':harian_date_nn',  $harian_date);
            $st->bindValue(':closing_date_tp', $closing_date);
            $st->bindValue(':harian_date_tn',  $harian_date);
            $st->bindValue(':harian_date_rl1', $harian_date);
            $st->bindValue(':harian_date_rl2', $harian_date);
            $st->bindValue(':awal_bulan',      $awal_bulan);
            // bind cabang opsional
            if ($kode_kantor) {
                $st->bindValue(':kode_np', $kode_kantor);
                $st->bindValue(':kode_nn', $kode_kantor);
                $st->bindValue(':kode_tp', $kode_kantor);
                $st->bindValue(':kode_tn', $kode_kantor);
                $st->bindValue(':kode_rl', $kode_kantor);
            }
            $st->execute();
            $meta = $st->fetch(PDO::FETCH_ASSOC) ?: [
                'npl_prev'=>0,'npl_now'=>0,'total_prev'=>0,'total_now'=>0,'realisasi_bulan_ini'=>0
            ];

            $npl_prev = (float)$meta['npl_prev'];
            $npl_now  = (float)$meta['npl_now'];
            $tot_prev = (float)$meta['total_prev'];
            $tot_now  = (float)$meta['total_now'];
            $realisasi= (float)$meta['realisasi_bulan_ini'];

            $npl_prev_pct = $tot_prev > 0 ? round($npl_prev * 100.0 / $tot_prev, 2) : 0.0;
            $npl_now_pct  = $tot_now  > 0 ? round($npl_now  * 100.0 / $tot_now , 2) : 0.0;
            $npl_delta_pct= round($npl_now_pct - $npl_prev_pct, 2);
            $growth       = $realisasi - $pembayaran_total;

            if ($totalIdx !== null) {
                $rows[$totalIdx]['realisasi_bulan_ini'] = $realisasi;
                $rows[$totalIdx]['npl_prev']            = $npl_prev;
                $rows[$totalIdx]['npl_now']             = $npl_now;
                $rows[$totalIdx]['total_prev']          = $tot_prev;
                $rows[$totalIdx]['total_now']           = $tot_now;
                $rows[$totalIdx]['npl_prev_pct']        = $npl_prev_pct;
                $rows[$totalIdx]['npl_now_pct']         = $npl_now_pct;
                $rows[$totalIdx]['npl_delta_pct']       = $npl_delta_pct;
                $rows[$totalIdx]['growth']              = $growth;
                $rows[$totalIdx]['pembayaran_total']    = $pembayaran_total;
            }

            sendResponse(200, "Berhasil ambil data migrasi kolektibilitas (dengan lunas_osc)", $rows);

        } catch (PDOException $e) {
            sendResponse(500, "PDO Error: " . $e->getMessage(), null);
        }
    }

    
    public function getMigrasiKolek($input) {
        $closing_date = !empty($input['closing_date']) ? $input['closing_date'] : date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = !empty($input['harian_date'])  ? $input['harian_date']  : date('Y-m-d');
        
        // 1. Ambil Input Kode Kantor (Prioritas Utama)
        $kode_kantor  = !empty($input['kode_kantor'])  ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        
        // 2. Ambil Input Korwil (Prioritas Kedua)
        $korwil_input = !empty($input['korwil']) ? strtoupper($input['korwil']) : null;
        $kw_start = null;
        $kw_end   = null;

        // Mapping Kode Korwil
        if (!$kode_kantor && $korwil_input) {
            switch ($korwil_input) {
                case 'SEMARANG':   $kw_start = '001'; $kw_end = '007'; break;
                case 'SOLO':       $kw_start = '008'; $kw_end = '014'; break;
                case 'BANYUMAS':   $kw_start = '015'; $kw_end = '021'; break;
                case 'PEKALONGAN': $kw_start = '022'; $kw_end = '028'; break;
            }
        }

        // 3. Siapkan String Filter SQL (Pilih salah satu: Cabang atau Korwil)
        $filter_cabang_closing = "";
        $filter_cabang_harian  = "";

        if ($kode_kantor) {
            // Filter Cabang Spesifik
            $filter_cabang_closing = " AND c.kode_cabang = :kode_kantor_c ";
            $filter_cabang_harian  = " AND h.kode_cabang = :kode_kantor_h ";
        } elseif ($kw_start && $kw_end) {
            // Filter Range Korwil
            $filter_cabang_closing = " AND c.kode_cabang BETWEEN :kw_start_c AND :kw_end_c ";
            $filter_cabang_harian  = " AND h.kode_cabang BETWEEN :kw_start_h AND :kw_end_h ";
        }

        $sql = "
            WITH 
            closing AS (
                SELECT c.no_rekening, c.kode_cabang, c.kolektibilitas AS kolek_closed, c.baki_debet AS baki_closed
                FROM nominatif c
                WHERE c.created = :closing_date_m
                AND c.kolektibilitas IN ('L','DP','KL','D','M')
                $filter_cabang_closing
            ),
            harian AS (
                SELECT h.no_rekening, h.kode_cabang, h.kolektibilitas AS kolek_update, h.baki_debet AS baki_harian
                FROM nominatif h
                WHERE h.created = :harian_date_m
                AND h.kolektibilitas IN ('L','DP','KL','D','M')
                $filter_cabang_harian
            ),
            gabung AS (
                SELECT 
                    c.no_rekening,
                    c.kolek_closed,
                    h.kolek_update,
                    c.baki_closed,
                    COALESCE(h.baki_harian, 0) AS baki_harian,
                    (c.baki_closed - COALESCE(h.baki_harian, 0)) AS pembayaran,
                    CASE WHEN h.no_rekening IS NULL THEN 1 ELSE 0 END AS is_lunas
                FROM closing c
                LEFT JOIN harian h ON h.no_rekening = c.no_rekening
            )
            /* ===== Per-kolek: + run_off_asli (lunas_osc - pembayaran) ===== */
            SELECT 
                g.kolek_closed,
                SUM(g.baki_closed) AS saldo_closed,
                SUM(CASE WHEN g.kolek_update = 'L'  THEN g.baki_harian ELSE 0 END) AS migrasi_L,
                SUM(CASE WHEN g.kolek_update = 'DP' THEN g.baki_harian ELSE 0 END) AS migrasi_DP,
                SUM(CASE WHEN g.kolek_update = 'KL' THEN g.baki_harian ELSE 0 END) AS migrasi_KL,
                SUM(CASE WHEN g.kolek_update = 'D'  THEN g.baki_harian ELSE 0 END) AS migrasi_D,
                SUM(CASE WHEN g.kolek_update = 'M'  THEN g.baki_harian ELSE 0 END) AS migrasi_M,
                SUM(g.pembayaran) AS pembayaran,
                SUM(CASE WHEN g.is_lunas = 1 THEN g.baki_closed ELSE 0 END) AS lunas_osc,
                (SUM(CASE WHEN g.is_lunas = 1 THEN g.baki_closed ELSE 0 END) - SUM(g.pembayaran)) AS run_off_asli
            FROM gabung g
            GROUP BY g.kolek_closed

            UNION ALL

            /* ===== Baris TOTAL ===== */
            SELECT 
                'TOTAL' AS kolek_closed,
                SUM(g.baki_closed) AS saldo_closed,
                SUM(CASE WHEN g.kolek_update = 'L'  THEN g.baki_harian ELSE 0 END) AS migrasi_L,
                SUM(CASE WHEN g.kolek_update = 'DP' THEN g.baki_harian ELSE 0 END) AS migrasi_DP,
                SUM(CASE WHEN g.kolek_update = 'KL' THEN g.baki_harian ELSE 0 END) AS migrasi_KL,
                SUM(CASE WHEN g.kolek_update = 'D'  THEN g.baki_harian ELSE 0 END) AS migrasi_D,
                SUM(CASE WHEN g.kolek_update = 'M'  THEN g.baki_harian ELSE 0 END) AS migrasi_M,
                SUM(g.pembayaran) AS pembayaran,
                SUM(CASE WHEN g.is_lunas = 1 THEN g.baki_closed ELSE 0 END) AS lunas_osc,
                (SUM(CASE WHEN g.is_lunas = 1 THEN g.baki_closed ELSE 0 END) - SUM(g.pembayaran)) AS run_off_asli
            FROM gabung g

            ORDER BY 
                CASE 
                    WHEN kolek_closed = 'L'     THEN 1
                    WHEN kolek_closed = 'DP'    THEN 2
                    WHEN kolek_closed = 'KL'    THEN 3
                    WHEN kolek_closed = 'D'     THEN 4
                    WHEN kolek_closed = 'M'     THEN 5
                    WHEN kolek_closed = 'TOTAL' THEN 99
                    ELSE 98
                END
        ";

        try {
            // 1) Eksekusi migrasi
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date_m', $closing_date);
            $stmt->bindValue(':harian_date_m',  $harian_date);
            
            if ($kode_kantor) {
                $stmt->bindValue(':kode_kantor_c', $kode_kantor);
                $stmt->bindValue(':kode_kantor_h', $kode_kantor);
            } elseif ($kw_start && $kw_end) {
                // Bind range korwil
                $stmt->bindValue(':kw_start_c', $kw_start);
                $stmt->bindValue(':kw_end_c',   $kw_end);
                $stmt->bindValue(':kw_start_h', $kw_start);
                $stmt->bindValue(':kw_end_h',   $kw_end);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // cari indeks TOTAL + pembayaran_total
            $pembayaran_total = 0.0; $totalIdx = null;
            foreach ($rows as $i => $r) {
                if (($r['kolek_closed'] ?? '') === 'TOTAL') {
                    $totalIdx = $i;
                    $pembayaran_total = (float)($r['pembayaran'] ?? 0);
                    break;
                }
            }

            // ================== HITUNG METRIK TOTAL SAJA ==================
            $backflow_total = 0.0; 
            $lunas_npl      = 0.0; 
            $angsuran_npl   = 0.0; 
            $flow_par       = 0.0; 

            foreach ($rows as $r) {
                $kc = $r['kolek_closed'] ?? '';
                if ($kc === 'TOTAL') continue;

                $migrasiL   = (float)($r['migrasi_L']  ?? 0);
                $migrasiDP  = (float)($r['migrasi_DP'] ?? 0);
                $migrasiKL  = (float)($r['migrasi_KL'] ?? 0);
                $migrasiD   = (float)($r['migrasi_D']  ?? 0);
                $migrasiM   = (float)($r['migrasi_M']  ?? 0);
                $bayar      = (float)($r['pembayaran'] ?? 0);
                $lunas      = (float)($r['lunas_osc']  ?? 0);

                if ($kc === 'KL' || $kc === 'D' || $kc === 'M') {
                    $backflow_total += ($migrasiL + $migrasiDP);
                    $lunas_npl     += $lunas;
                    $angsuran_npl  += max(0.0, $bayar - $lunas);
                }

                if ($kc === 'L' || $kc === 'DP') {
                    $flow_par += ($migrasiKL + $migrasiD + $migrasiM);
                }
            }

            // ================== META: %NPL, REALISASI, GROWTH (Filter Korwil Applied) ==================
            $awal_bulan = date('Y-m-01', strtotime($harian_date));
            
            // Siapkan filter untuk query META
            $filter_np = ""; $filter_nn = ""; $filter_tp = ""; $filter_tn = ""; $filter_rl = "";
            
            if ($kode_kantor) {
                $filter_np = " AND kode_cabang = :kode_np ";
                $filter_nn = " AND kode_cabang = :kode_nn ";
                $filter_tp = " AND kode_cabang = :kode_tp ";
                $filter_tn = " AND kode_cabang = :kode_tn ";
                $filter_rl = " AND kode_cabang = :kode_rl ";
            } elseif ($kw_start && $kw_end) {
                $filter_np = " AND kode_cabang BETWEEN :kw_start_np AND :kw_end_np ";
                $filter_nn = " AND kode_cabang BETWEEN :kw_start_nn AND :kw_end_nn ";
                $filter_tp = " AND kode_cabang BETWEEN :kw_start_tp AND :kw_end_tp ";
                $filter_tn = " AND kode_cabang BETWEEN :kw_start_tn AND :kw_end_tn ";
                $filter_rl = " AND kode_cabang BETWEEN :kw_start_rl AND :kw_end_rl ";
            }

            $sqlMeta = "
                SELECT
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :closing_date_np
                    AND kolektibilitas IN ('KL','D','M') $filter_np) AS npl_prev,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :harian_date_nn
                    AND kolektibilitas IN ('KL','D','M') $filter_nn) AS npl_now,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :closing_date_tp
                    AND kolektibilitas IN ('L','DP','KL','D','M') $filter_tp) AS total_prev,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :harian_date_tn
                    AND kolektibilitas IN ('L','DP','KL','D','M') $filter_tn) AS total_now,
                (SELECT COALESCE(SUM(baki_debet),0) FROM nominatif
                    WHERE created = :harian_date_rl1
                    AND tgl_realisasi >= :awal_bulan
                    AND tgl_realisasi <= :harian_date_rl2 $filter_rl) AS realisasi_bulan_ini
            ";

            $st = $this->pdo->prepare($sqlMeta);
            // Bind tanggal
            $st->bindValue(':closing_date_np', $closing_date);
            $st->bindValue(':harian_date_nn',  $harian_date);
            $st->bindValue(':closing_date_tp', $closing_date);
            $st->bindValue(':harian_date_tn',  $harian_date);
            $st->bindValue(':harian_date_rl1', $harian_date);
            $st->bindValue(':harian_date_rl2', $harian_date);
            $st->bindValue(':awal_bulan',      $awal_bulan);

            // Bind Cabang atau Korwil
            if ($kode_kantor) {
                $st->bindValue(':kode_np', $kode_kantor);
                $st->bindValue(':kode_nn', $kode_kantor);
                $st->bindValue(':kode_tp', $kode_kantor);
                $st->bindValue(':kode_tn', $kode_kantor);
                $st->bindValue(':kode_rl', $kode_kantor);
            } elseif ($kw_start && $kw_end) {
                // Kita bind berulang untuk tiap subquery agar aman
                $st->bindValue(':kw_start_np', $kw_start); $st->bindValue(':kw_end_np', $kw_end);
                $st->bindValue(':kw_start_nn', $kw_start); $st->bindValue(':kw_end_nn', $kw_end);
                $st->bindValue(':kw_start_tp', $kw_start); $st->bindValue(':kw_end_tp', $kw_end);
                $st->bindValue(':kw_start_tn', $kw_start); $st->bindValue(':kw_end_tn', $kw_end);
                $st->bindValue(':kw_start_rl', $kw_start); $st->bindValue(':kw_end_rl', $kw_end);
            }

            $st->execute();
            $meta = $st->fetch(PDO::FETCH_ASSOC) ?: [
                'npl_prev'=>0,'npl_now'=>0,'total_prev'=>0,'total_now'=>0,'realisasi_bulan_ini'=>0
            ];

            $npl_prev = (float)$meta['npl_prev'];
            $npl_now  = (float)$meta['npl_now'];
            $tot_prev = (float)$meta['total_prev'];
            $tot_now  = (float)$meta['total_now'];
            $realisasi= (float)$meta['realisasi_bulan_ini'];

            $npl_prev_pct = $tot_prev > 0 ? round($npl_prev * 100.0 / $tot_prev, 2) : 0.0;
            $npl_now_pct  = $tot_now  > 0 ? round($npl_now  * 100.0 / $tot_now , 2) : 0.0;
            $npl_delta_pct= round($npl_now_pct - $npl_prev_pct, 2);
            $growth       = $realisasi - $pembayaran_total;

            if ($totalIdx !== null) {
                $rows[$totalIdx]['realisasi_bulan_ini'] = $realisasi;
                $rows[$totalIdx]['npl_prev']            = $npl_prev;
                $rows[$totalIdx]['npl_now']             = $npl_now;
                $rows[$totalIdx]['total_prev']          = $tot_prev;
                $rows[$totalIdx]['total_now']           = $tot_now;
                $rows[$totalIdx]['npl_prev_pct']        = $npl_prev_pct;
                $rows[$totalIdx]['npl_now_pct']         = $npl_now_pct;
                $rows[$totalIdx]['npl_delta_pct']       = $npl_delta_pct;
                $rows[$totalIdx]['growth']              = $growth;
                $rows[$totalIdx]['pembayaran_total']    = $pembayaran_total;

                $rows[$totalIdx]['backflow_total']      = $backflow_total;
                $rows[$totalIdx]['lunas_npl']           = $lunas_npl;
                $rows[$totalIdx]['angsuran_npl']        = $angsuran_npl;
                $rows[$totalIdx]['flow_par']            = $flow_par;
            }

            sendResponse(200, "Berhasil ambil data migrasi kolektibilitas", $rows);

        } catch (PDOException $e) {
            sendResponse(500, "PDO Error: " . $e->getMessage(), null);
        }
    }



    public function getKolek($input) {
        $harian_date = isset($input['harian_date']) ? $input['harian_date'] : date('Y-m-d');

        $sql = "
            WITH data_harian AS (
                SELECT 
                    kode_cabang,
                    kolektibilitas,
                    baki_debet,
                    no_rekening
                FROM nominatif
                WHERE created = :harian_date
            ),
            
            agregat AS (
                SELECT
                    d.kode_cabang,
                    k.nama_kantor,

                    COUNT(CASE WHEN d.kolektibilitas = 'L' THEN 1 END) AS noa_L,
                    SUM(CASE WHEN d.kolektibilitas = 'L' THEN d.baki_debet ELSE 0 END) AS bd_L,

                    COUNT(CASE WHEN d.kolektibilitas = 'DP' THEN 1 END) AS noa_DP,
                    SUM(CASE WHEN d.kolektibilitas = 'DP' THEN d.baki_debet ELSE 0 END) AS bd_DP,

                    COUNT(CASE WHEN d.kolektibilitas = 'KL' THEN 1 END) AS noa_KL,
                    SUM(CASE WHEN d.kolektibilitas = 'KL' THEN d.baki_debet ELSE 0 END) AS bd_KL,

                    COUNT(CASE WHEN d.kolektibilitas = 'D' THEN 1 END) AS noa_D,
                    SUM(CASE WHEN d.kolektibilitas = 'D' THEN d.baki_debet ELSE 0 END) AS bd_D,

                    COUNT(CASE WHEN d.kolektibilitas = 'M' THEN 1 END) AS noa_M,
                    SUM(CASE WHEN d.kolektibilitas = 'M' THEN d.baki_debet ELSE 0 END) AS bd_M,

                    COUNT(*) AS total_noa,
                    SUM(d.baki_debet) AS total_bd,

                    SUM(CASE WHEN d.kolektibilitas IN ('KL', 'D', 'M') THEN 1 ELSE 0 END) AS noa_npl,
                    SUM(CASE WHEN d.kolektibilitas IN ('KL', 'D', 'M') THEN d.baki_debet ELSE 0 END) AS bd_npl

                FROM data_harian d
                JOIN kode_kantor k ON d.kode_cabang = k.kode_kantor
                WHERE k.kode_kantor <> '000'
                GROUP BY d.kode_cabang, k.nama_kantor
            )

            SELECT
                kode_cabang,
                nama_kantor,
                noa_L, bd_L,
                noa_DP, bd_DP,
                noa_KL, bd_KL,
                noa_D, bd_D,
                noa_M, bd_M,
                total_noa,
                total_bd,
                noa_npl,
                bd_npl,
                ROUND(CASE WHEN total_bd = 0 THEN 0 ELSE (bd_npl * 100.0) / total_bd END, 2) AS persentase_npl
            FROM agregat


        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':harian_date', $harian_date);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Berhasil ambil data Kolektibilitas Harian", $data);
    }

    public function getTopRealisasi($input = []) {
        // 1. Setup Variable
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date'] ?? date('Y-m-d');
        
        $kode_kantor = $input['kode_kantor'] ?? null;
        $kode_ao     = $input['kode_ao'] ?? null;

        // 2. Query SQL
        // Perbaikan: Nama parameter dibedakan (:created_date dan :max_realisasi)
        $sql = "
            SELECT 
                t1.kode_cabang,
                t1.no_rekening,
                t1.nama_nasabah,
                t1.jml_pinjaman as plafond,
                t1.alamat,
                t1.tgl_realisasi,
                t1.tgl_jatuh_tempo,
                t1.kode_group2,
                COALESCE(ao.nama_ao, t1.kode_group2) as nama_ao
            FROM nominatif t1
            LEFT JOIN ao_kredit ao ON t1.kode_group2 = ao.kode_group2
            
            WHERE t1.created = :created_date 
            AND t1.tgl_realisasi > :closing_date 
            AND t1.tgl_realisasi <= :max_realisasi
        ";

        // Logic Filter
        if (!empty($kode_kantor)) {
            $sql .= " AND t1.kode_cabang = :kode_kantor ";
        }
        if (!empty($kode_ao)) {
            $sql .= " AND t1.kode_group2 = :kode_ao ";
        }

        $sql .= " ORDER BY t1.jml_pinjaman DESC ";

        // Logic Limit
        if (empty($kode_kantor) && empty($kode_ao)) {
            $sql .= " LIMIT 50 ";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            
            // 3. Binding Value (FIXED)
            // Bind parameter yang namanya sudah dibedakan tadi
            $stmt->bindValue(':created_date', $harian_date);  // Untuk t1.created
            $stmt->bindValue(':closing_date', $closing_date); // Untuk > closing
            $stmt->bindValue(':max_realisasi', $harian_date); // Untuk <= harian
            
            if (!empty($kode_kantor)) {
                $stmt->bindValue(':kode_kantor', $kode_kantor);
            }
            if (!empty($kode_ao)) {
                $stmt->bindValue(':kode_ao', $kode_ao);
            }
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $ket = empty($kode_kantor) ? "Top 50 Realisasi (Konsolidasi)" : "Realisasi Cabang $kode_kantor";
            
            // Panggil helper global (tanpa $this->)
            sendResponse(200, "$ket (Sejak $closing_date s/d $harian_date)", $data);
            
        } catch (PDOException $e) {
            sendResponse(500, "Database Error: " . $e->getMessage());
        }
    }

/**
     * API 1: REKAP MATRIKS MOB (View Utama)
     */
    public function getRekapMob6Bulan($input = null) {
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        
        $harian_date = $b['harian_date'] ?? date('Y-m-d'); 
        
        // Input 'kode_kantor' -> Filter 'kode_cabang'
        $kc_raw = $b['kode_kantor'] ?? null;
        $kc     = ($kc_raw === null || $kc_raw === '') ? null : str_pad((string)$kc_raw, 3, '0', STR_PAD_LEFT);

        // Range Realisasi (Mundur 6 bulan)
        $end_date_realisasi   = date('Y-m-t', strtotime($harian_date . ' -1 month')); 
        $start_date_realisasi = date('Y-m-01', strtotime($end_date_realisasi . ' -5 months')); 

        // Query Utama (Hanya butuh data agregat/summary, tidak perlu select * semua kolom berat)
        $sql = "
            SELECT 
                kode_cabang, 
                tgl_realisasi,
                jml_pinjaman as plafond, 
                baki_debet as os,            
                hari_menunggak
            FROM nominatif
            WHERE created = :harian_date
            AND tgl_realisasi BETWEEN :start_date AND :end_date
        ";

        if ($kc) {
            $sql .= " AND kode_cabang = :kc"; 
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':start_date', $start_date_realisasi);
            $stmt->bindValue(':end_date', $end_date_realisasi);
            if ($kc) $stmt->bindValue(':kc', $kc);

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process Matrix via Helper (Pastikan Helper sudah update support NOA)
            $matrix = MobHelper::processMobMatrix($rows, $harian_date);
            
            return sendResponse(200, "Rekap MOB 6 Bulan", [
                'posisi_data' => $harian_date,
                'filter_cabang' => $kc ?? 'ALL',
                'data' => $matrix
            ]);

        } catch (PDOException $e) {
            return sendResponse(500, "DB Error: " . $e->getMessage());
        }
    }

    /**
     * API 2: DETAIL DEBITUR PER BUCKET (Untuk Modal Klik + Pagination)
     * Request Body: harian_date, kode_kantor, bulan_realisasi (YYYY-MM), bucket_label (e.g., '1 - 7'), page
     */
    
    public function getDetailMobDebitur($input = null) {
        // 1. Ambil Input
        $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);

        // 2. Filter Wajib
        $harian_date   = $b['harian_date'] ?? date('Y-m-d');
        $bln_realisasi = $b['bulan_realisasi'] ?? null; 
        
        // [PERBAIKAN] Pastikan bucket_label diambil, meski nilainya "0"
        $bucket_label = isset($b['bucket_label']) ? (string)$b['bucket_label'] : null;
        
        // Filter Cabang (Input 'kode_kantor' -> DB 'kode_cabang')
        $kc_raw = $b['kode_kantor'] ?? null;
        $kc     = ($kc_raw === null || $kc_raw === '') ? null : str_pad((string)$kc_raw, 3, '0', STR_PAD_LEFT);

        // Pagination
        $page   = isset($b['page']) ? (int)$b['page'] : 1;
        $limit  = 10; 
        $offset = ($page - 1) * $limit;

        // [PERBAIKAN VALIDASI] Gunakan isset() atau strlen() karena "0" dianggap false
        if (!$bln_realisasi || $bucket_label === null || $bucket_label === '') {
            return sendResponse(400, "Parameter 'bulan_realisasi' dan 'bucket_label' wajib diisi.");
        }

        // 3. Mapping Bucket ke Range DPD
        $dpd_min = 0; $dpd_max = 99999;
        
        // Gunakan switch atau if dengan loose comparison (==) atau paksa string
        if ($bucket_label === '0')           { $dpd_min = 0;  $dpd_max = 0; }
        elseif ($bucket_label === '1 - 7')   { $dpd_min = 1;  $dpd_max = 7; }
        elseif ($bucket_label === '8 - 14')  { $dpd_min = 8;  $dpd_max = 14; }
        elseif ($bucket_label === '15 - 21') { $dpd_min = 15; $dpd_max = 21; }
        elseif ($bucket_label === '22 - 30') { $dpd_min = 22; $dpd_max = 30; }
        elseif ($bucket_label === '31 - 60') { $dpd_min = 31; $dpd_max = 60; }
        elseif ($bucket_label === '61 - 90') { $dpd_min = 61; $dpd_max = 90; }
        elseif ($bucket_label === '> 90')    { $dpd_min = 91; $dpd_max = 99999; }
        else {
            // Default Fallback jika label tidak dikenali (Opsional, bisa return error)
            return sendResponse(400, "Label Bucket tidak valid: " . $bucket_label);
        }

        // Tentukan Range Tanggal Realisasi (Awal s/d Akhir Bulan)
        $tgl_awal_bulan  = $bln_realisasi . '-01';
        $tgl_akhir_bulan = date('Y-m-t', strtotime($tgl_awal_bulan));

        try {
            // 4. Hitung Total Data (Untuk Pagination)
            $sqlCount = "
                SELECT COUNT(*) 
                FROM nominatif 
                WHERE DATE(created) = :harian_date
                AND tgl_realisasi BETWEEN :start AND :end
                AND hari_menunggak BETWEEN :dpd_min AND :dpd_max
            ";
            if ($kc) $sqlCount .= " AND kode_cabang = :kc";

            $stmt = $this->pdo->prepare($sqlCount);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':start', $tgl_awal_bulan);
            $stmt->bindValue(':end', $tgl_akhir_bulan);
            $stmt->bindValue(':dpd_min', $dpd_min);
            $stmt->bindValue(':dpd_max', $dpd_max);
            if ($kc) $stmt->bindValue(':kc', $kc);
            $stmt->execute();
            $total_records = $stmt->fetchColumn();

            // 5. Query Utama (Ambil Detail + Join Transaksi Kredit)
            $sql = "
                SELECT 
                    n.no_rekening, 
                    n.nama_nasabah, 
                    n.tgl_realisasi, 
                    n.jml_pinjaman as plafond, 
                    n.baki_debet as os, 
                    
                    -- Info Menunggak
                    n.hari_menunggak,
                    COALESCE(n.hari_menunggak_pokok, 0) as hari_menunggak_pokok,
                    COALESCE(n.hari_menunggak_bunga, 0) as hari_menunggak_bunga,
                    GREATEST((COALESCE(n.tunggakan_pokok, 0) + COALESCE(n.tunggakan_bunga, 0)), 0) as totung,

                    n.kolektibilitas,
                    n.kode_cabang,

                    -- Info Transaksi (Dari Subquery)
                    t.tgl_trans,
                    COALESCE(t.total_bayar, 0) as transaksi

                FROM nominatif n
                
                LEFT JOIN (
                    SELECT 
                        no_rekening,
                        MAX(tgl_trans) as tgl_trans,
                        SUM(COALESCE(angsuran_pokok, 0) + COALESCE(angsuran_bunga, 0)) as total_bayar
                    FROM transaksi_kredit 
                    WHERE MONTH(tgl_trans) = MONTH(:trans_date_1) 
                      AND YEAR(tgl_trans) = YEAR(:trans_date_2)
                    GROUP BY no_rekening
                ) t ON n.no_rekening = t.no_rekening

                WHERE DATE(n.created) = :harian_date
                AND n.tgl_realisasi BETWEEN :start AND :end
                AND n.hari_menunggak BETWEEN :dpd_min AND :dpd_max
            ";

            if ($kc) $sql .= " AND n.kode_cabang = :kc";
            
            $sql .= " ORDER BY n.baki_debet DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            
            // --- Binding Parameters ---
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':trans_date_1', $harian_date); 
            $stmt->bindValue(':trans_date_2', $harian_date); 
            $stmt->bindValue(':start', $tgl_awal_bulan);
            $stmt->bindValue(':end', $tgl_akhir_bulan);
            $stmt->bindValue(':dpd_min', $dpd_min);
            $stmt->bindValue(':dpd_max', $dpd_max);
            
            if ($kc) $stmt->bindValue(':kc', $kc);
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Data Type Casting
            foreach ($data as &$row) {
                $row['transaksi'] = (float)$row['transaksi']; 
                $row['plafond']   = (float)$row['plafond'];
                $row['os']        = (float)$row['os'];
                $row['totung']    = (float)$row['totung'];
                
                $row['hari_menunggak']       = (int)$row['hari_menunggak'];
                $row['hari_menunggak_pokok'] = (int)$row['hari_menunggak_pokok'];
                $row['hari_menunggak_bunga'] = (int)$row['hari_menunggak_bunga'];
            }
            unset($row); 

            // 7. Return Response
            return sendResponse(200, "Detail Debitur Sukses (Bucket: $bucket_label)", [
                'total_records' => $total_records,
                'total_pages' => ceil($total_records / $limit),
                'current_page' => $page,
                'data' => $data
            ]);

        } catch (PDOException $e) {
            return sendResponse(500, "Database Error: " . $e->getMessage());
        }
    }

}