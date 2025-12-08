<?php

require_once __DIR__ . '/../helpers/response.php';

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































}
