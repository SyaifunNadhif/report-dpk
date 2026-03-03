<?php

require_once __DIR__ . '/../helpers/response.php';

class NplController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }


    public function getNpl($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');
        $kc           = $input['kode_kantor']  ?? null;

        if ($kc === '000') $kc = null;

        // Logic Parameter Binding (FIX ERROR 500)
        if ($kc) {
            // Mode KANKAS
            $colKey       = "kode_group1";
            $selectName   = "COALESCE(k.deskripsi_group1, CONCAT('KAS ', h.kode_key))";
            $joinTable    = "LEFT JOIN kankas k ON h.kode_key = k.kode_group1";
            // Kita pakai nama parameter beda (:kc1 dan :kc2) biar PDO tidak bingung
            $filterClauseHarian = "AND kode_cabang = :kc1";
            $filterClauseClosing = "AND kode_cabang = :kc2";
            $kc_val       = str_pad((string)$kc, 3, '0', STR_PAD_LEFT);
        } else {
            // Mode KONSOLIDASI
            $colKey       = "kode_cabang";
            $selectName   = "k.nama_kantor";
            $joinTable    = "LEFT JOIN kode_kantor k ON h.kode_key = k.kode_kantor";
            $filterClauseHarian = "";
            $filterClauseClosing = "";
            $kc_val       = null;
        }

        $sql = "
            WITH 
            harian AS (
                SELECT $colKey as kode_key, kolektibilitas, baki_debet
                FROM nominatif WHERE created = :harian_date $filterClauseHarian
            ),
            closing AS (
                SELECT $colKey as kode_key, kolektibilitas, baki_debet
                FROM nominatif WHERE created = :closing_date $filterClauseClosing
            ),
            rekap_harian AS (
                SELECT h.kode_key, $selectName as nama_unit,
                    SUM(CASE WHEN h.kolektibilitas IN ('KL', 'D', 'M') THEN h.baki_debet ELSE 0 END) AS npl_harian,
                    SUM(h.baki_debet) AS total_harian
                FROM harian h $joinTable GROUP BY h.kode_key, $selectName
            ),
            rekap_closing AS (
                SELECT c.kode_key,
                    SUM(CASE WHEN c.kolektibilitas IN ('KL', 'D', 'M') THEN c.baki_debet ELSE 0 END) AS npl_closing,
                    SUM(c.baki_debet) AS total_closing
                FROM closing c GROUP BY c.kode_key
            ),
            gabung AS (
                SELECT rh.kode_key, rh.nama_unit, COALESCE(rc.npl_closing, 0) AS npl_closing, rh.npl_harian,
                    (rh.npl_harian - COALESCE(rc.npl_closing, 0)) AS selisih_npl,
                    COALESCE(rc.total_closing, 0) AS total_closing, rh.total_harian
                FROM rekap_harian rh LEFT JOIN rekap_closing rc ON rh.kode_key = rc.kode_key
            )
            SELECT kode_key as kode_unit, nama_unit, npl_closing, npl_harian, selisih_npl,
                ROUND(CASE WHEN total_closing = 0 THEN 0 ELSE (npl_closing * 100.0) / total_closing END, 2) AS npl_closing_persen,
                ROUND(CASE WHEN total_harian = 0 THEN 0 ELSE (npl_harian * 100.0) / total_harian END, 2) AS npl_harian_persen,
                ROUND((CASE WHEN total_harian = 0 THEN 0 ELSE (npl_harian * 100.0) / total_harian END) - 
                    (CASE WHEN total_closing = 0 THEN 0 ELSE (npl_closing * 100.0) / total_closing END), 2) AS selisih_npl_persen
            FROM gabung
            UNION ALL
            SELECT '', 'TOTAL KONSOLIDASI', SUM(npl_closing), SUM(npl_harian), SUM(selisih_npl),
                ROUND(CASE WHEN SUM(total_closing) = 0 THEN 0 ELSE (SUM(npl_closing) * 100.0) / SUM(total_closing) END, 2),
                ROUND(CASE WHEN SUM(total_harian) = 0 THEN 0 ELSE (SUM(npl_harian) * 100.0) / SUM(total_harian) END, 2),
                ROUND((CASE WHEN SUM(total_harian) = 0 THEN 0 ELSE (SUM(npl_harian) * 100.0) / SUM(total_harian) END) - 
                    (CASE WHEN SUM(total_closing) = 0 THEN 0 ELSE (SUM(npl_closing) * 100.0) / SUM(total_closing) END), 2)
            FROM gabung
            ORDER BY CASE WHEN nama_unit = 'TOTAL KONSOLIDASI' THEN 1 ELSE 0 END, kode_unit ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            if ($kc_val) {
                // Bind 2 kali untuk variabel :kc1 dan :kc2
                $stmt->bindValue(':kc1', $kc_val);
                $stmt->bindValue(':kc2', $kc_val);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Pisahkan Grand Total
            $grandTotal = array_pop($data);
            if(!$grandTotal) { // Fallback kosong
                $grandTotal = ['kode_unit'=>'','nama_unit'=>'TOTAL KONSOLIDASI','npl_closing'=>0,'npl_harian'=>0,'selisih_npl'=>0,'npl_closing_persen'=>0,'npl_harian_persen'=>0,'selisih_npl_persen'=>0];
            }

            sendResponse(200, "Sukses", ['data' => $data, 'grand_total' => $grandTotal]);
        } catch (Exception $e) {
            sendResponse(500, "Error: " . $e->getMessage());
        }
    }

    public function getRecoveryNPL($input = []) {
        $closing_date = isset($input['closing_date']) ? $input['closing_date'] : date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = isset($input['harian_date'])  ? $input['harian_date']  : date('Y-m-d');

        $sql = "
            -- STEP 1: Ambil NPL dari 30 Juni
            WITH closing AS (
                SELECT 
                    no_rekening,
                    kode_cabang,
                    baki_debet
                FROM nominatif
                WHERE created = :closing1
                AND kolektibilitas IN ('KL', 'D', 'M')
            ),

            -- STEP 2: BACKFLOW = masuk 7 Juli, kolektibilitas L/DP
            backflow AS (
                SELECT 
                    h.no_rekening,
                    h.kode_cabang,
                    h.baki_debet
                FROM nominatif h
                INNER JOIN closing c ON h.no_rekening = c.no_rekening
                WHERE h.created = :harian1
                AND h.kolektibilitas IN ('L', 'DP')
            ),

            -- STEP 3: LUNAS = tidak muncul sama sekali di 7 Juli
            lunas AS (
                SELECT 
                    c.no_rekening,
                    c.kode_cabang,
                    c.baki_debet
                FROM closing c
                LEFT JOIN nominatif h
                ON h.no_rekening = c.no_rekening
                AND h.created = :harian2
                WHERE h.no_rekening IS NULL
            ),

            -- STEP 4: Rekap per cabang
            rekap_lunas AS (
                SELECT 
                    kode_cabang,
                    COUNT(*) AS noa_lunas,
                    SUM(baki_debet) AS baki_debet_lunas
                FROM lunas
                GROUP BY kode_cabang
            ),

            rekap_backflow AS (
                SELECT 
                    kode_cabang,
                    COUNT(*) AS noa_backflow,
                    SUM(baki_debet) AS baki_debet_backflow
                FROM backflow
                GROUP BY kode_cabang
            ),

            -- STEP 5: Gabungkan dan join ke nama kantor
            rekap_cabang AS (
                SELECT 
                    k.kode_kantor AS kode_cabang,
                    k.nama_kantor,
                    COALESCE(l.noa_lunas, 0) AS noa_lunas,
                    COALESCE(l.baki_debet_lunas, 0) AS baki_debet_lunas,
                    COALESCE(b.noa_backflow, 0) AS noa_backflow,
                    COALESCE(b.baki_debet_backflow, 0) AS baki_debet_backflow
                FROM kode_kantor k
                LEFT JOIN rekap_lunas l ON k.kode_kantor = l.kode_cabang
                LEFT JOIN rekap_backflow b ON k.kode_kantor = b.kode_cabang
                WHERE k.kode_kantor <> '000'
            )

            -- STEP 6: Tampilkan + total konsolidasi
            SELECT 
                kode_cabang,
                nama_kantor,
                noa_lunas,
                baki_debet_lunas,
                noa_backflow,
                baki_debet_backflow
            FROM rekap_cabang

            UNION ALL

            SELECT 
                'TOTAL',
                'TOTAL KONSOLIDASI',
                SUM(noa_lunas),
                SUM(baki_debet_lunas),
                SUM(noa_backflow),
                SUM(baki_debet_backflow)
            FROM rekap_cabang

            ORDER BY 
                CASE WHEN kode_cabang = 'TOTAL' THEN 1 ELSE 0 END,
                kode_cabang;
        ";

        $stmt = $this->pdo->prepare($sql);

        // Bind parameter yang dipakai lebih dari satu kali
        $stmt->bindValue(':closing1', $closing_date);
        $stmt->bindValue(':harian1', $harian_date);
        $stmt->bindValue(':harian2', $harian_date);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, "Berhasil ambil data Recovery NPL", $data);
    }


    // public function getTop25NplPerCabang($input) {
    //     $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
    //     $kode_cabang  = $input['kode_cabang'] ?? null;

    //     // SQL dasar
    //     $sql = "
    //         SELECT 
    //             n.no_rekening,
    //             n.nama_nasabah,
    //             n.kode_cabang,
    //             k.nama_kantor,
    //             n.kolektibilitas,
    //             n.baki_debet,
    //             n.tunggakan_pokok,
    //             n.tunggakan_bunga,
    //             n.tgl_realisasi
    //         FROM nominatif n
    //         LEFT JOIN kode_kantor k ON n.kode_cabang = k.kode_kantor
    //         WHERE n.created = :closing_date
    //         AND n.kolektibilitas IN ('KL', 'D', 'M')
    //     ";

    //     // Filter cabang jika ada
    //     if (!empty($kode_cabang)) {
    //         $sql .= " AND n.kode_cabang = :kode_cabang";
    //     }

    //     $sql .= " ORDER BY n.baki_debet DESC LIMIT 25";

    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->bindValue(':closing_date', $closing_date);
    //     if (!empty($kode_cabang)) {
    //         $stmt->bindValue(':kode_cabang', $kode_cabang);
    //     }
    //     $stmt->execute();

    //     $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     $msg = !empty($kode_cabang)
    //         ? "Top 25 NPL cabang $kode_cabang"
    //         : "Top 25 NPL konsolidasi";

    //     sendResponse(200, $msg, $data);
    // }

    public function getTop25NplPerCabang($input) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date'] ?? date('Y-m-d');
        $kode_cabang  = $input['kode_cabang'] ?? null;
        $start_month  = date('Y-m-01', strtotime($harian_date));

        // Ambil 25 rekening NPL tertinggi duluan (ringan)
        $sql = "
            WITH top25 AS (
                SELECT 
                    n.no_rekening,
                    n.nama_nasabah,
                    n.kode_cabang,
                    k.nama_kantor,
                    n.jml_pinjaman,
                    n.kolektibilitas AS kolek_closing,
                    n.baki_debet
                FROM nominatif n
                LEFT JOIN kode_kantor k ON n.kode_cabang = k.kode_kantor
                WHERE n.created = :closing_date
                AND n.kolektibilitas IN ('KL', 'D', 'M')
        ";

        if (!empty($kode_cabang)) {
            $sql .= " AND n.kode_cabang = :kode_cabang";
        }

        $sql .= "
                ORDER BY n.baki_debet DESC
                LIMIT 25
            )

            SELECT 
                t25.*,

                -- Dari nominatif harian
                nh.kolektibilitas AS kolek_harian,
                nh.baki_debet AS baki_debet_harian,
                nh.tunggakan_pokok,
                nh.tunggakan_bunga,

                -- Transaksi
                COALESCE(SUM(tk.angsuran_pokok), 0) AS total_pokok,
                COALESCE(SUM(tk.angsuran_bunga), 0) AS total_bunga,
                MAX(tk.tgl_trans) AS tgl_trans

            FROM top25 t25

            -- Join ke nominatif harian
            LEFT JOIN nominatif nh 
                ON t25.no_rekening = nh.no_rekening 
                AND nh.created = :harian_date

            -- Join ke transaksi kredit
            LEFT JOIN transaksi_kredit tk 
                ON t25.no_rekening = tk.no_rekening 
                AND tk.tgl_trans BETWEEN :start_month AND :end_date

            GROUP BY 
                t25.no_rekening, t25.nama_nasabah, t25.kode_cabang, t25.nama_kantor, 
                t25.kolek_closing, t25.baki_debet,
                nh.kolektibilitas, nh.baki_debet, nh.tunggakan_pokok, nh.tunggakan_bunga

            ORDER BY t25.baki_debet DESC
        ";


        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':closing_date', $closing_date);
        $stmt->bindValue(':harian_date', $harian_date);
        $stmt->bindValue(':start_month', $start_month);
        $stmt->bindValue(':end_date', $harian_date); // sama seperti harian_date
        if (!empty($kode_cabang)) {
            $stmt->bindValue(':kode_cabang', $kode_cabang);
        }

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $msg = !empty($kode_cabang)
            ? "Top 25 NPL cabang $kode_cabang"
            : "Top 25 NPL konsolidasi";

        sendResponse(200, $msg, $data);
    }

    
    public function getDetailRecoveryNpl($input = []) {
        $kode_kantor       = isset($input['kode_kantor']) ? $input['kode_kantor'] : null;
        $closing_date      = isset($input['closing_date']) ? $input['closing_date'] : date('Y-m-d', strtotime('last day of previous month'));
        $harian_date       = isset($input['harian_date']) ? $input['harian_date'] : date('Y-m-d');
        $type              = isset($input['type']) ? strtolower($input['type']) : null; // lunas / backflow

        if (!$kode_kantor || !$type) {
            sendResponse(400, "Parameter 'kode_kantor' dan 'type' harus diisi (lunas/backflow).");
            return;
        }

        if ($type === 'backflow') {
            $sql = "
                WITH closing AS (
                    SELECT no_rekening, kolektibilitas
                    FROM nominatif
                    WHERE created = :closing_date
                    AND kolektibilitas IN ('KL', 'D', 'M')
                ),
                trx AS (
                    SELECT 
                        no_rekening,
                        MAX(tgl_trans) AS tgl_trans,
                        SUM(angsuran_pokok) AS angsuran_pokok,
                        SUM(angsuran_bunga) AS angsuran_bunga,
                        SUM(angsuran_denda) AS angsuran_denda
                    FROM transaksi_kredit
                    WHERE tgl_trans BETWEEN :awal_date AND :harian_date_trx
                    AND kode_kantor = :kode_kantor_trx
                    GROUP BY no_rekening
                )
                SELECT 
                    h.no_rekening,
                    h.nama_nasabah,
                    h.baki_debet,
                    c.kolektibilitas AS kolek,
                    h.kolektibilitas AS kolek_update,
                    h.kode_cabang,
                    trx.tgl_trans,
                    trx.angsuran_pokok,
                    trx.angsuran_bunga,
                    trx.angsuran_denda
                FROM nominatif h
                JOIN closing c ON h.no_rekening = c.no_rekening
                LEFT JOIN trx ON h.no_rekening = trx.no_rekening
                WHERE h.created = :harian_date
                AND h.kolektibilitas IN ('L', 'DP')
                AND h.kode_cabang = :kode_kantor_harian
                ORDER BY trx.angsuran_pokok DESC
            ";
        } elseif ($type === 'lunas') {
            $sql = "
                WITH closing AS (
                    SELECT no_rekening, nama_nasabah, baki_debet, kolektibilitas, kode_cabang
                    FROM nominatif
                    WHERE created = :closing_date
                    AND kolektibilitas IN ('KL', 'D', 'M')
                    AND kode_cabang = :kode_kantor_closing
                ),
                trx AS (
                    SELECT 
                        no_rekening,
                        MAX(tgl_trans) AS tgl_trans,
                        SUM(angsuran_pokok) AS angsuran_pokok,
                        SUM(angsuran_bunga) AS angsuran_bunga,
                        SUM(angsuran_denda) AS angsuran_denda
                    FROM transaksi_kredit
                    WHERE tgl_trans BETWEEN :awal_date AND :harian_date_trx
                    AND kode_kantor = :kode_kantor_trx
                    GROUP BY no_rekening
                )
                SELECT 
                    c.no_rekening,
                    c.nama_nasabah,
                    c.baki_debet,
                    c.kolektibilitas AS kolek,
                    'Lunas' AS kolek_update,
                    c.kode_cabang,
                    trx.tgl_trans,
                    trx.angsuran_pokok,
                    trx.angsuran_bunga,
                    trx.angsuran_denda
                FROM closing c
                LEFT JOIN nominatif h ON h.no_rekening = c.no_rekening AND h.created = :harian_date
                LEFT JOIN trx ON c.no_rekening = trx.no_rekening
                WHERE h.no_rekening IS NULL
                ORDER BY trx.angsuran_pokok DESC
            ";
        } else {
            sendResponse(400, "Tipe harus 'lunas' atau 'backflow'.");
            return;
        }

        // Parameter date awal untuk transaksi (ambil dari closing_date)
        $awal_date = $closing_date;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':closing_date', $closing_date);              // untuk tabel closing
        $stmt->bindValue(':harian_date', $harian_date);                // untuk tabel harian
        $stmt->bindValue(':awal_date', $awal_date);                    // awal trx
        $stmt->bindValue(':harian_date_trx', $harian_date);            // akhir trx
        $stmt->bindValue(':kode_kantor_trx', $kode_kantor);            // kantor untuk trx

        // Bind kode_kantor sesuai kebutuhan per type
        if ($type === 'backflow') {
            $stmt->bindValue(':kode_kantor_harian', $kode_kantor);
        } elseif ($type === 'lunas') {
            $stmt->bindValue(':kode_kantor_closing', $kode_kantor);
        }

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Berhasil ambil detail debitur $type", $data);
    }

    public function getPotensiNplRekap($input = [])
    {
        $harian_date = !empty($input['harian_date']) 
            ? date('Y-m-d', strtotime($input['harian_date'])) 
            : date('Y-m-d');

        $closing_date = !empty($input['closing_date']) 
            ? date('Y-m-d', strtotime($input['closing_date'])) 
            : date('Y-m-t', strtotime($harian_date . ' -1 month'));

        $awalBulan  = date('Y-m-01', strtotime($harian_date));
        $akhirBulan = date('Y-m-t', strtotime($harian_date));

        // --- LOGIC HARI DINAMIS ---
        $hari_bulan = (int) date('t', strtotime($harian_date)); // Total hari di bulan ini (misal 30 atau 31)
        $tgl_harian = (int) date('d', strtotime($harian_date)); // Tanggal hari ini
        $sisa_hari  = $hari_bulan - $tgl_harian;                // Sisa hari menuju akhir bulan
        if ($sisa_hari < 0) $sisa_hari = 0;

        $jt_start = date('Y-m-15', strtotime($harian_date . ' -1 month'));
        $jt_end   = $akhirBulan;

        $kc = $input['kode_kantor'] ?? null;
        if ($kc === '000' || $kc === '') $kc = null;

        // --- LOGIC MASTER TABLE (Agar Kantor 0 tetap tampil) ---
        if ($kc) {
            $masterTable  = "kankas k";
            $colKey       = "kode_group1"; 
            $selectName   = "k.deskripsi_group1 AS nama_cabang";
            $filterMaster = "WHERE k.kode_kantor = :kc_master";
            $joinKey      = "k.kode_group1";
            $filterHarian = "AND kode_cabang = :kc1";
            $filterClosing = "AND kode_cabang = :kc2";
            $kc_val       = str_pad((string)$kc, 3, '0', STR_PAD_LEFT);
        } else {
            $masterTable  = "kode_kantor k";
            $colKey       = "kode_cabang";
            $selectName   = "k.nama_kantor AS nama_cabang";
            $filterMaster = "WHERE k.kode_kantor <> '000'";
            $joinKey      = "k.kode_kantor";
            $filterHarian = "";
            $filterClosing = "";
            $kc_val       = null;
        }

        $sql = "
            WITH master_data AS (
                SELECT $joinKey AS kode_unit, $selectName 
                FROM $masterTable 
                $filterMaster
            ),
            kandidat AS (
                SELECT no_rekening
                FROM nominatif
                WHERE created = :closing
                AND kolektibilitas IN ('L', 'DP')
                $filterClosing
                AND (
                        (COALESCE(hari_menunggak,0) + :hb1) >= 90
                     OR (COALESCE(hari_menunggak_pokok,0) + :hb2) >= 90
                     OR (COALESCE(hari_menunggak_bunga,0) + :hb3) >= 90
                     OR (
                            tgl_jatuh_tempo >= :jt_start
                        AND tgl_jatuh_tempo <= :jt_end
                     )
                )
            ),
            harian AS (
                SELECT 
                    no_rekening, 
                    $colKey AS kode_join, 
                    baki_debet, 
                    kolektibilitas, 
                    COALESCE(hari_menunggak,0) AS hari_menunggak, 
                    COALESCE(hari_menunggak_pokok,0) AS hari_menunggak_pokok, 
                    COALESCE(hari_menunggak_bunga,0) AS hari_menunggak_bunga, 
                    tgl_jatuh_tempo
                FROM nominatif
                WHERE created = :harian_date
                $filterHarian
            ),
            flow_par AS (
                SELECT h.* FROM harian h
                JOIN kandidat c ON h.no_rekening = c.no_rekening
            ),
            rekap AS (
                SELECT 
                    m.kode_unit AS kode_cabang,
                    m.nama_cabang,
                    
                    -- TOTAL POTENSI
                    COUNT(f.no_rekening) AS total_noa,
                    COALESCE(SUM(f.baki_debet),0) AS total_baki,
                    
                    -- AMAN (Sisa hari tidak tembus 90 & bukan JT bulan ini)
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :ab1 OR f.tgl_jatuh_tempo > :ak1 OR f.tgl_jatuh_tempo IS NULL) AND (f.hari_menunggak + :sh1) < 90 AND (f.hari_menunggak_pokok + :sh2) < 90 AND (f.hari_menunggak_bunga + :sh3) < 90 THEN 1 ELSE 0 END) AS noa_aman,
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :ab2 OR f.tgl_jatuh_tempo > :ak2 OR f.tgl_jatuh_tempo IS NULL) AND (f.hari_menunggak + :sh4) < 90 AND (f.hari_menunggak_pokok + :sh5) < 90 AND (f.hari_menunggak_bunga + :sh6) < 90 THEN f.baki_debet ELSE 0 END) AS baki_aman,

                    -- JATUH TEMPO BULAN INI
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND f.tgl_jatuh_tempo BETWEEN :ab3 AND :ak3 THEN 1 ELSE 0 END) AS noa_jt,
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND f.tgl_jatuh_tempo BETWEEN :ab4 AND :ak4 THEN f.baki_debet ELSE 0 END) AS baki_jt,
                    
                    -- FLOW KOLEK (KL/D/M)
                    SUM(CASE WHEN f.kolektibilitas IN ('KL','D','M') THEN 1 ELSE 0 END) AS noa_flow,
                    SUM(CASE WHEN f.kolektibilitas IN ('KL','D','M') THEN f.baki_debet ELSE 0 END) AS baki_flow,

                    -- MASIH POTENSI (Ditambah sisa hari tembus 90)
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :ab5 OR f.tgl_jatuh_tempo > :ak5 OR f.tgl_jatuh_tempo IS NULL) AND ((f.hari_menunggak + :sh7) >= 90 OR (f.hari_menunggak_pokok + :sh8) >= 90 OR (f.hari_menunggak_bunga + :sh9) >= 90) THEN 1 ELSE 0 END) AS noa_potensi,
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :ab6 OR f.tgl_jatuh_tempo > :ak6 OR f.tgl_jatuh_tempo IS NULL) AND ((f.hari_menunggak + :sh10) >= 90 OR (f.hari_menunggak_pokok + :sh11) >= 90 OR (f.hari_menunggak_bunga + :sh12) >= 90) THEN f.baki_debet ELSE 0 END) AS baki_potensi

                FROM master_data m
                LEFT JOIN flow_par f ON f.kode_join = m.kode_unit
                GROUP BY m.kode_unit, m.nama_cabang
            )
            SELECT * FROM rekap
            UNION ALL
            SELECT NULL, 'TOTAL KONSOLIDASI', SUM(total_noa), SUM(total_baki), SUM(noa_aman), SUM(baki_aman), SUM(noa_jt), SUM(baki_jt), SUM(noa_flow), SUM(baki_flow), SUM(noa_potensi), SUM(baki_potensi)
            FROM rekap
            ORDER BY CASE WHEN nama_cabang = 'TOTAL KONSOLIDASI' THEN 1 ELSE 0 END, kode_cabang ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing',     $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':hb1', $hari_bulan, PDO::PARAM_INT);
            $stmt->bindValue(':hb2', $hari_bulan, PDO::PARAM_INT);
            $stmt->bindValue(':hb3', $hari_bulan, PDO::PARAM_INT);
            $stmt->bindValue(':jt_start', $jt_start);
            $stmt->bindValue(':jt_end',   $jt_end);

            // Looping Parameter Binding untuk Case (Mencegah error HY093)
            for ($i=1; $i<=6; $i++) {
                $stmt->bindValue(":ab$i", $awalBulan);
                $stmt->bindValue(":ak$i", $akhirBulan);
            }
            for ($i=1; $i<=12; $i++) {
                $stmt->bindValue(":sh$i", $sisa_hari, PDO::PARAM_INT);
            }
            
            if ($kc_val) {
                $stmt->bindValue(':kc_master', $kc_val);
                $stmt->bindValue(':kc1', $kc_val);
                $stmt->bindValue(':kc2', $kc_val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pisahkan Grand Total
            $grandTotal = array_pop($rows);
            if (!$grandTotal) {
                $grandTotal = ['kode_cabang'=>'','nama_cabang'=>'TOTAL KONSOLIDASI','total_noa'=>0,'total_baki'=>0];
            }

            sendResponse(200, "Berhasil ambil Rekap Potensi NPL", [
                'data' => $rows, 
                'grand_total' => $grandTotal
            ]);
        } catch (Exception $e) {
            sendResponse(500, "Error: " . $e->getMessage());
        }
    }


public function getDetailPotensiNpl($input = [])
    {
        $kode_kantor = isset($input['kode_kantor']) && $input['kode_kantor'] !== ''
            ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT)
            : null;
            
        $kode_kankas = $input['kode_kankas'] ?? '';

        $harian_date = !empty($input['harian_date'])
            ? date('Y-m-d', strtotime($input['harian_date']))
            : date('Y-m-d');
            
        $closing_date = !empty($input['closing_date'])
            ? date('Y-m-d', strtotime($input['closing_date']))
            : (new DateTime($harian_date))->modify('first day of this month')->modify('-1 day')->format('Y-m-d');

        $awal_date = !empty($input['awal_date'])
            ? date('Y-m-d', strtotime($input['awal_date']))
            : date('Y-m-01', strtotime($harian_date));

        $bulan_awal      = date('Y-m-01', strtotime($harian_date));
        $bulan_akhir     = date('Y-m-t',  strtotime($harian_date));
        
        $jml_hari_bulan = (int) date('t', strtotime($harian_date));
        $tgl_harian     = (int) date('d', strtotime($harian_date));
        $sisa_hari      = $jml_hari_bulan - $tgl_harian;
        if ($sisa_hari < 0) $sisa_hari = 0;

        $filterKantorClosing = $kode_kantor && $kode_kantor !== '000' ? " AND n.kode_cabang = :kode_kantor_c " : "";
        $filterKankasClosing = $kode_kankas !== '' ? " AND n.kode_group1 = :kode_kankas " : "";
        $filterKantorTrx     = $kode_kantor && $kode_kantor !== '000' ? " AND t.kode_kantor = :kode_kantor_trx " : "";

        $sql = "
            WITH kandidat AS (
                SELECT
                    n.no_rekening,
                    n.kode_cabang,
                    n.kode_group1,
                    n.nama_nasabah,
                    n.alamat,
                    n.kolektibilitas AS kolek_closing,
                    n.baki_debet     AS baki_debet_closing,
                    COALESCE(n.hari_menunggak,0)        AS hm_closing,
                    COALESCE(n.hari_menunggak_pokok,0)  AS hmp_closing,
                    COALESCE(n.hari_menunggak_bunga,0)  AS hmb_closing,
                    n.tgl_jatuh_tempo AS jt_closing,
                    n.tgl_realisasi
                FROM nominatif n
                WHERE n.created = :closing_date
                AND n.kolektibilitas IN ('L','DP')
                {$filterKantorClosing}
                {$filterKankasClosing}
                AND (
                        (COALESCE(n.hari_menunggak,0)       + :jml_hari1) >= 90
                    OR (COALESCE(n.hari_menunggak_pokok,0) + :jml_hari2) >= 90
                    OR (COALESCE(n.hari_menunggak_bunga,0) + :jml_hari3) >= 90
                    OR (n.tgl_jatuh_tempo BETWEEN :bulan_awal1 AND :bulan_akhir1)
                )
            ),
            harian AS (
                SELECT
                    h.no_rekening,
                    h.kolektibilitas AS kolek_harian,
                    h.baki_debet     AS baki_debet_harian,
                    COALESCE(h.tunggakan_pokok,0)       AS tunggakan_pokok,
                    COALESCE(h.tunggakan_bunga,0)       AS tunggakan_bunga,
                    COALESCE(h.hari_menunggak,0)        AS hm_harian,
                    COALESCE(h.hari_menunggak_pokok,0)  AS hmp_harian,
                    COALESCE(h.hari_menunggak_bunga,0)  AS hmb_harian,
                    h.tgl_jatuh_tempo AS jt_harian,
                    h.norek_tabungan   -- <===== INI DITAMBAHKAN AGAR BISA DIJOIN
                FROM nominatif h
                WHERE h.created = :harian_date
            ),
            trx AS (
                SELECT
                    t.no_rekening,
                    MAX(t.tgl_trans)        AS tgl_trans_terakhir,
                    SUM(t.angsuran_pokok)   AS angsuran_pokok,
                    SUM(t.angsuran_bunga)   AS angsuran_bunga,
                    SUM(t.angsuran_denda)   AS angsuran_denda
                FROM transaksi_kredit t
                WHERE t.tgl_trans BETWEEN :awal_date AND :harian_date_trx
                {$filterKantorTrx}
                GROUP BY t.no_rekening
            )
            SELECT
                kd.kode_cabang,
                kk.nama_kantor,
                kas.deskripsi_group1 AS nama_kankas,
                kd.no_rekening,
                kd.nama_nasabah,
                kd.alamat,
                kd.kolek_closing,
                kd.baki_debet_closing,
                COALESCE(h.kolek_harian, 'Lunas') AS kolek_harian,
                COALESCE(h.baki_debet_harian, 0) AS baki_debet_harian,
                h.tunggakan_pokok,
                h.tunggakan_bunga,
                (COALESCE(h.tunggakan_pokok, 0) + COALESCE(h.tunggakan_bunga, 0)) AS total_tunggakan,
                h.hm_harian,
                h.hmp_harian,
                h.hmb_harian,
                h.jt_harian,
                tb.saldo_akhir,    -- <===== OUTPUT SALDO TABUNGAN
                CASE 
                    WHEN h.no_rekening IS NULL OR h.baki_debet_harian = 0 THEN 'LUNAS / AMAN'
                    WHEN h.kolek_harian IN ('KL','D','M') THEN 'FLOW KOLEK'
                    WHEN h.jt_harian BETWEEN :bulan_awal2 AND :bulan_akhir2 THEN 'JATUH TEMPO'
                    WHEN (h.hm_harian + :sisa_hari1) < 90 AND (h.hmp_harian + :sisa_hari2) < 90 AND (h.hmb_harian + :sisa_hari3) < 90 THEN 'AMAN'
                    ELSE 'MASIH POTENSI'
                END AS status_potensi,
                tr.tgl_trans_terakhir,
                tr.angsuran_pokok,
                tr.angsuran_bunga,
                tr.angsuran_denda
            FROM kandidat kd
            LEFT JOIN harian h ON kd.no_rekening = h.no_rekening
            LEFT JOIN trx    tr ON kd.no_rekening = tr.no_rekening
            LEFT JOIN kode_kantor kk ON kd.kode_cabang = kk.kode_kantor
            LEFT JOIN kankas kas ON kd.kode_group1 = kas.kode_group1
            LEFT JOIN tabungan tb ON tb.no_rekening = h.norek_tabungan  -- <===== JOIN TABUNGAN
            WHERE kk.kode_kantor <> '000'
            ORDER BY kd.baki_debet_closing DESC, kd.no_rekening
        ";

        try {
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':closing_date',    $closing_date);
            $st->bindValue(':harian_date',     $harian_date);
            $st->bindValue(':awal_date',       $awal_date);
            $st->bindValue(':harian_date_trx', $harian_date);
            
            $st->bindValue(':jml_hari1', $jml_hari_bulan, PDO::PARAM_INT);
            $st->bindValue(':jml_hari2', $jml_hari_bulan, PDO::PARAM_INT);
            $st->bindValue(':jml_hari3', $jml_hari_bulan, PDO::PARAM_INT);

            $st->bindValue(':sisa_hari1', $sisa_hari, PDO::PARAM_INT);
            $st->bindValue(':sisa_hari2', $sisa_hari, PDO::PARAM_INT);
            $st->bindValue(':sisa_hari3', $sisa_hari, PDO::PARAM_INT);

            $st->bindValue(':bulan_awal1',  $bulan_awal);
            $st->bindValue(':bulan_akhir1', $bulan_akhir);
            $st->bindValue(':bulan_awal2',  $bulan_awal);
            $st->bindValue(':bulan_akhir2', $bulan_akhir);

            if ($kode_kantor && $kode_kantor !== '000') {
                $st->bindValue(':kode_kantor_c',   $kode_kantor);
                $st->bindValue(':kode_kantor_trx', $kode_kantor);
            }
            if ($kode_kankas !== '') {
                $st->bindValue(':kode_kankas', $kode_kankas);
            }

            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            
            // Format Output agar angka masuk sebagai numeric (bukan string) untuk JS
            $numericFields = [
                'baki_debet_closing', 'baki_debet_harian', 'tunggakan_pokok', 'tunggakan_bunga', 
                'total_tunggakan', 'hm_harian', 'hmp_harian', 'hmb_harian', 'angsuran_pokok', 'angsuran_bunga', 'saldo_akhir' // Tambah saldo_akhir
            ];
            foreach ($rows as &$r) {
                foreach ($numericFields as $f) {
                    if (isset($r[$f])) $r[$f] = 0 + $r[$f];
                }
            }
            unset($r);

            sendResponse(200, 'Detail potensi NPL', $rows);
        } catch (Throwable $e) {
            sendResponse(500, 'Gagal ambil detail potensi NPL: '.$e->getMessage());
        }
    }


    public function getBucket($input = [])
    {
        // snapshot nominatif
        $closing_date = !empty($input['closing_date'])
            ? date('Y-m-d', strtotime($input['closing_date']))
            : date('Y-m-d', strtotime('last day of previous month'));

        // optional: filter 1 cabang (biarkan null untuk konsolidasi)
        $kode_kantor = !empty($input['kode_kantor'])
            ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT)
            : null;

        $filterCabang = $kode_kantor ? " AND k.kode_kantor = :kode_kantor " : "";

        $sql = "
            WITH base AS (
                SELECT
                    n.kode_cabang,
                    COALESCE(n.hari_menunggak,0) AS hm,
                    COALESCE(n.baki_debet,0)     AS baki
                FROM nominatif n
                WHERE n.created = :closing_date
                -- TIDAK filter kolektibilitas, ambil semua
            ),
            agg AS (
                SELECT
                    k.kode_kantor AS kode_cabang,
                    k.nama_kantor AS nama_cabang,

                    -- 0–30
                    SUM(CASE WHEN b.hm BETWEEN 0   AND 30  THEN 1     ELSE 0 END) AS noa_0_30,
                    SUM(CASE WHEN b.hm BETWEEN 0   AND 30  THEN b.baki ELSE 0 END) AS baki_0_30,

                    -- 31–90
                    SUM(CASE WHEN b.hm BETWEEN 31  AND 90  THEN 1     ELSE 0 END) AS noa_31_90,
                    SUM(CASE WHEN b.hm BETWEEN 31  AND 90  THEN b.baki ELSE 0 END) AS baki_31_90,

                    -- 91–180
                    SUM(CASE WHEN b.hm BETWEEN 91  AND 180 THEN 1     ELSE 0 END) AS noa_91_180,
                    SUM(CASE WHEN b.hm BETWEEN 91  AND 180 THEN b.baki ELSE 0 END) AS baki_91_180,

                    -- 181–360
                    SUM(CASE WHEN b.hm BETWEEN 181 AND 360 THEN 1     ELSE 0 END) AS noa_181_360,
                    SUM(CASE WHEN b.hm BETWEEN 181 AND 360 THEN b.baki ELSE 0 END) AS baki_181_360,

                    -- >360
                    SUM(CASE WHEN b.hm > 360                  THEN 1   ELSE 0 END) AS noa_gt_360,
                    SUM(CASE WHEN b.hm > 360                  THEN b.baki ELSE 0 END) AS baki_gt_360

                FROM base b
                JOIN kode_kantor k ON k.kode_kantor = b.kode_cabang
                WHERE k.kode_kantor <> '000' $filterCabang
                GROUP BY k.kode_kantor, k.nama_kantor
            ),
            final AS (
                SELECT
                    a.*,
                    -- total
                    (noa_0_30 + noa_31_90 + noa_91_180 + noa_181_360 + noa_gt_360)   AS noa_total,
                    (baki_0_30 + baki_31_90 + baki_91_180 + baki_181_360 + baki_gt_360) AS baki_total
                FROM agg a
            )

            -- per cabang
            SELECT * FROM final

            UNION ALL

            -- total konsolidasi
            SELECT
                NULL AS kode_cabang,
                'TOTAL' AS nama_cabang,
                SUM(noa_0_30),  SUM(baki_0_30),
                SUM(noa_31_90), SUM(baki_31_90),
                SUM(noa_91_180),SUM(baki_91_180),
                SUM(noa_181_360),SUM(baki_181_360),
                SUM(noa_gt_360), SUM(baki_gt_360),
                SUM(noa_total),  SUM(baki_total)
            FROM final

            ORDER BY
                CASE WHEN nama_cabang='TOTAL' THEN 1 ELSE 0 END,
                kode_cabang
        ";

        try {
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':closing_date', $closing_date);
            if ($kode_kantor) {
                $st->bindValue(':kode_kantor', $kode_kantor);
            }
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, 'OK - Rekap baket hari_menunggak', $rows);
        } catch (Throwable $e) {
            sendResponse(500, 'Gagal ambil rekap: '.$e->getMessage(), null);
        }
    }

    
















    













    












}
