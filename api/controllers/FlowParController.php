<?php

require_once __DIR__ . '/../helpers/response.php';

class FlowParController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



    // ✅ READ Recovery Hapus Buku
    public function getFlowPar($input = []) {
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');
        $kc           = $input['kode_kantor']  ?? null;

        if ($kc === '000' || $kc === '') $kc = null;

        // LOGIC GROUPING OTOMATIS
        if ($kc) {
            $masterTable  = "kankas k";
            $colKey       = "kode_group1"; 
            $selectName   = "k.deskripsi_group1 AS nama_kantor";
            $filterMaster = "WHERE k.kode_kantor = :kc_master";
            $joinKey      = "k.kode_group1";
            $filterHarian = "AND kode_cabang = :kc1";
            $filterClosing = "AND kode_cabang = :kc2";
            $kc_val       = str_pad((string)$kc, 3, '0', STR_PAD_LEFT);
        } else {
            $masterTable  = "kode_kantor k";
            $colKey       = "kode_cabang";
            $selectName   = "k.nama_kantor AS nama_kantor";
            $filterMaster = "WHERE k.kode_kantor <> '000'";
            $joinKey      = "k.kode_kantor";
            $filterHarian = "";
            $filterClosing = "";
            $kc_val       = null;
        }

        $sql = "
            WITH closing AS (
                SELECT no_rekening
                FROM nominatif
                WHERE created = :closing_date $filterClosing
                AND kolektibilitas IN ('L', 'DP')
            ),
            harian AS (
                SELECT no_rekening, $colKey as kode_key, baki_debet
                FROM nominatif
                WHERE created = :harian_date $filterHarian
                AND kolektibilitas IN ('KL', 'D', 'M')
            ),
            flow_par AS (
                SELECT h.kode_key, h.no_rekening, h.baki_debet
                FROM harian h
                JOIN closing c ON h.no_rekening = c.no_rekening
            ),
            rekap_cabang AS (
                -- RAHASIANYA DI SINI: Base tabelnya adalah Master, lalu di LEFT JOIN
                SELECT 
                    $joinKey AS kode_cabang,
                    $selectName,
                    COUNT(f.no_rekening) AS noa_flow,
                    COALESCE(SUM(f.baki_debet), 0) AS baki_debet_flow
                FROM $masterTable
                LEFT JOIN flow_par f ON f.kode_key = $joinKey
                $filterMaster
                GROUP BY $joinKey, nama_kantor
            )
            SELECT kode_cabang, nama_kantor, noa_flow, baki_debet_flow
            FROM rekap_cabang
            UNION ALL
            SELECT '', 'TOTAL KONSOLIDASI', SUM(noa_flow), SUM(baki_debet_flow)
            FROM rekap_cabang
            ORDER BY CASE WHEN nama_kantor = 'TOTAL KONSOLIDASI' THEN 1 ELSE 0 END, kode_cabang ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            
            if ($kc_val) {
                // Bind untuk filter master
                $stmt->bindValue(':kc_master', $kc_val);
                // Bind untuk CTE
                $stmt->bindValue(':kc1', $kc_val);
                $stmt->bindValue(':kc2', $kc_val);
            }
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pisahkan Grand Total agar API rapi
            $grandTotal = array_pop($data);
            if (!$grandTotal) {
                $grandTotal = ['kode_cabang'=>'','nama_kantor'=>'TOTAL KONSOLIDASI','noa_flow'=>0,'baki_debet_flow'=>0];
            }

            sendResponse(200, "Berhasil ambil data Flow PAR", ['data' => $data, 'grand_total' => $grandTotal]);
        } catch (Exception $e) {
            sendResponse(500, "Error: " . $e->getMessage());
        }
    }

    public function getDebiturFlowPar($input) {
        $kode_kantor  = str_pad($input['kode_kantor'] ?? '', 3, '0', STR_PAD_LEFT);
        $kode_kankas  = $input['kode_kankas'] ?? '';
        $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = $input['harian_date']  ?? date('Y-m-d');

        $filterCabang = "";
        $filterKankas = "";
        
        if ($kode_kantor !== '000' && $kode_kantor !== '') {
            $filterCabang = " AND kode_cabang = :kc1 ";
        }
        if ($kode_kankas !== '') {
            $filterKankas = " AND kode_group1 = :kankas1 ";
        }

        $filterHarian = $filterCabang . $filterKankas;

        $sql = "
            WITH closing AS (
                SELECT no_rekening, kolektibilitas AS kolek_closing, tunggakan_pokok, tunggakan_bunga
                FROM nominatif
                WHERE created = :closing_date
                  AND kolektibilitas IN ('L','DP')
            ),
            harian AS (
                SELECT 
                    no_rekening, kode_cabang, kolektibilitas AS kolek_harian, baki_debet,
                    tunggakan_pokok, tunggakan_bunga, nama_nasabah, tgl_realisasi,
                    tgl_jatuh_tempo, hari_menunggak, hari_menunggak_pokok, hari_menunggak_bunga,
                    norek_tabungan, kode_group1
                FROM nominatif
                WHERE created = :harian_date
                  AND kolektibilitas IN ('KL','D','M')
                  $filterHarian
            ),
            flow_par AS (
                SELECT h.*, c.kolek_closing
                FROM harian h
                JOIN closing c ON h.no_rekening = c.no_rekening
            ),
            trx AS (
                SELECT no_rekening, MAX(tgl_trans) AS tgl_trans,
                       SUM(COALESCE(angsuran_pokok,0)) AS angsuran_pokok,
                       SUM(COALESCE(angsuran_bunga,0)) AS angsuran_bunga,
                       SUM(COALESCE(angsuran_denda,0)) AS angsuran_denda
                FROM transaksi_kredit
                WHERE tgl_trans > :closing_date_trx AND tgl_trans <= :harian_date_trx
                GROUP BY no_rekening
            ),
            km_last AS (
                /* TAMBAHAN: Masukkan k.nominal di sini */
                SELECT k.no_rekening, k.komitmen, k.tgl_pembayaran, k.nominal, k.alasan
                FROM komitmen_flowpar k
                JOIN (
                    SELECT no_rekening, MAX(COALESCE(updated, created)) AS last_ts
                    FROM komitmen_flowpar
                    WHERE DATE_FORMAT(COALESCE(updated, created), '%Y-%m') = DATE_FORMAT(:harian_date_km, '%Y-%m')
                    GROUP BY no_rekening
                ) s ON s.no_rekening = k.no_rekening AND COALESCE(k.updated, k.created) = s.last_ts
            )
            SELECT
                f.kode_cabang,
                kk.nama_kantor,
                kas.deskripsi_group1 AS nama_kankas,
                f.no_rekening,
                f.nama_nasabah,
                f.kolek_closing,
                f.kolek_harian,
                f.baki_debet,
                f.tunggakan_pokok,
                f.tunggakan_bunga,
                (COALESCE(f.tunggakan_pokok, 0) + COALESCE(f.tunggakan_bunga, 0)) AS total_tunggakan,
                f.hari_menunggak,
                f.hari_menunggak_pokok,
                f.hari_menunggak_bunga,
                tb.saldo_akhir,
                f.tgl_realisasi,
                f.tgl_jatuh_tempo,
                trx.angsuran_pokok,
                trx.angsuran_bunga,
                trx.tgl_trans,
                km_last.komitmen,
                /* TAMBAHAN: Keluarkan data tgl, nominal, dan alasan */
                km_last.tgl_pembayaran,
                km_last.nominal,
                km_last.alasan
            FROM flow_par f
            LEFT JOIN trx ON f.no_rekening = trx.no_rekening
            LEFT JOIN kode_kantor kk ON f.kode_cabang = kk.kode_kantor
            LEFT JOIN kankas kas ON f.kode_group1 = kas.kode_group1
            LEFT JOIN km_last ON km_last.no_rekening = f.no_rekening
            LEFT JOIN tabungan tb ON tb.no_rekening = f.norek_tabungan
            ORDER BY f.baki_debet DESC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':closing_date', $closing_date);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':closing_date_trx', $closing_date);
            $stmt->bindValue(':harian_date_trx', $harian_date);
            $stmt->bindValue(':harian_date_km', $harian_date);

            if ($kode_kantor !== '000' && $kode_kantor !== '') {
                $stmt->bindValue(':kc1', $kode_kantor);
            }
            if ($kode_kankas !== '') {
                $stmt->bindValue(':kankas1', $kode_kankas);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(200, "Detail debitur flow PAR", $data);
        } catch (Exception $e) {
            sendResponse(500, "Error: " . $e->getMessage());
        }
    }

public function searchDebiturKredit($input) {
    // --- 1. AMBIL PARAMETER ---
    $user_login_kode = $input['user_kode'] ?? '000'; 
    $kc_input = $input['kode_kantor'] ?? '';

    if ($user_login_kode !== '000' && $user_login_kode !== '') {
        $kode_kantor = str_pad($user_login_kode, 3, '0', STR_PAD_LEFT);
    } else {
        $kode_kantor = ($kc_input !== '') ? str_pad($kc_input, 3, '0', STR_PAD_LEFT) : '';
    }

    $kolek  = $input['kolek'] ?? 'Semua';
    $search = trim($input['search'] ?? '');
    
    // Ambil totung dan pastikan tipenya float/angka
    $totung = (isset($input['totung']) && $input['totung'] !== '') ? (float)$input['totung'] : null;
    
    // Ambil tanggal harian terakhir dari input atau default H-1
    $tanggal_h1 = $input['tanggal'] ?? date('Y-m-d', strtotime('-1 days'));

    $page   = isset($input['page']) ? (int)$input['page'] : 1;
    $limit  = isset($input['limit']) ? (int)$input['limit'] : 50;
    $offset = ($page - 1) * $limit;

    // WHERE AWAL: Batasi berdasarkan tanggal created
    $where = " WHERE n.created = :tanggal_h1 ";
    $params = [':tanggal_h1' => $tanggal_h1];

    // --- 2. LOGIC FILTERING ---
    if ($kode_kantor !== '' && $kode_kantor !== '000') {
        $where .= " AND n.kode_cabang = :kode_kantor ";
        $params[':kode_kantor'] = $kode_kantor;
    }

    if ($kolek !== 'Semua' && $kolek !== '') {
        $where .= " AND n.kolektibilitas = :kolek ";
        $params[':kolek'] = $kolek;
    }

    if ($search !== '') {
        $where .= " AND (n.no_rekening LIKE :search OR n.nama_nasabah LIKE :search_nama) ";
        $params[':search'] = "%$search%";
        $params[':search_nama'] = "%$search%";
    }

    // --- FIX LOGIC TOTUNG SESUAI REQUEST ---
    // totung > 0 DAN totung <= nilai_input
    if ($totung !== null) {
        $where .= " AND (COALESCE(n.tunggakan_pokok, 0) + COALESCE(n.tunggakan_bunga, 0)) > 0 ";
        $where .= " AND (COALESCE(n.tunggakan_pokok, 0) + COALESCE(n.tunggakan_bunga, 0)) <= :totung ";
        $params[':totung'] = $totung;
    }

    try {
        // --- 3. COUNT & SUMMARY ---
        $sqlSum = "SELECT COUNT(n.no_rekening) as total_data, SUM(n.baki_debet) AS sum_bd FROM nominatif n $where";
        $stmtSum = $this->pdo->prepare($sqlSum);
        foreach ($params as $key => $val) { $stmtSum->bindValue($key, $val); }
        $stmtSum->execute();
        $summary = $stmtSum->fetch(PDO::FETCH_ASSOC);
        
        $totalData  = (int)($summary['total_data'] ?? 0);
        $totalPages = ceil($totalData / $limit);

        // --- 4. QUERY DATA UTAMA ---
        $sqlData = "
            SELECT 
                n.kode_cabang, n.nama_nasabah, n.no_rekening, n.norek_tabungan, n.kode_produk,
                n.kolektibilitas AS kolek, n.hari_menunggak AS dpd,
                n.hari_menunggak_pokok AS hmp, n.hari_menunggak_bunga AS hmb,
                n.tgl_jatuh_tempo, n.baki_debet,
                (COALESCE(n.tunggakan_pokok, 0) + COALESCE(n.tunggakan_bunga, 0)) AS totung,
                COALESCE(tb.saldo_akhir, 0) AS saldo_tabungan
            FROM nominatif n
            LEFT JOIN tabungan tb ON n.norek_tabungan = tb.no_rekening
            $where
            ORDER BY n.baki_debet DESC
            LIMIT $limit OFFSET $offset
        ";

        $stmtData = $this->pdo->prepare($sqlData);
        foreach ($params as $key => $val) { $stmtData->bindValue($key, $val); }
        $stmtData->execute();
        $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, "Sukses", [
            'summary' => ['noa' => $totalData, 'bd_act' => $summary['sum_bd'] ?? 0],
            'pagination' => [
                'total_data'   => $totalData,
                'total_page'   => $totalPages,
                'current_page' => $page,
                'limit'        => $limit
            ],
            'data' => $data
        ]);
    } catch (Exception $e) {
        sendResponse(500, "Error BE: " . $e->getMessage());
    }
}

    public function getPotensiNplRekap($input = [])
    {
        $harian_date = $input['harian_date'] ?? date('Y-m-d');
        // Gunakan closing_date dari frontend jika ada, jika tidak otomatis hitung
        if (!empty($input['closing_date'])) {
            $closing = date('Y-m-d', strtotime($input['closing_date']));
        } else {
            $base = new DateTime($harian_date);
            $closing = (clone $base)->modify('first day of this month')->modify('-1 day')->format('Y-m-d');
        }
        
        $awalBulan  = date('Y-m-01', strtotime($harian_date));
        $akhirBulan = date('Y-m-t', strtotime($harian_date));
        
        // LOGIC KALKULASI SISA HARI BULAN BERJALAN
        $jml_hari_bulan = (int) date('t', strtotime($harian_date));
        $tgl_harian     = (int) date('d', strtotime($harian_date));
        $sisa_hari      = $jml_hari_bulan - $tgl_harian;
        if ($sisa_hari < 0) $sisa_hari = 0;

        $kc = $input['kode_kantor'] ?? null;
        if ($kc === '000' || $kc === '') $kc = null;

        // LOGIC MASTER TABLE (Agar Kantor / Kankas dengan nilai 0 tetap tampil)
        if ($kc) {
            $masterTable  = "kankas k";
            $colKey       = "kode_group1"; 
            $selectName   = "k.deskripsi_group1 AS nama_cabang";
            $filterMaster = "WHERE k.kode_kantor = :kc_master";
            $joinKey      = "k.kode_group1";
            $filterHarian = "AND kode_cabang = :kc1";
            $filterClosing= "AND kode_cabang = :kc2";
            $kc_val       = str_pad((string)$kc, 3, '0', STR_PAD_LEFT);
        } else {
            $masterTable  = "kode_kantor k";
            $colKey       = "kode_cabang";
            $selectName   = "k.nama_kantor AS nama_cabang";
            $filterMaster = "WHERE k.kode_kantor <> '000'";
            $joinKey      = "k.kode_kantor";
            $filterHarian = "";
            $filterClosing= "";
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
                AND kolektibilitas IN ('L','DP')
                $filterClosing
                AND (
                        (COALESCE(hari_menunggak,0) + :jml_hari) >= 90
                     OR (COALESCE(hari_menunggak_pokok,0) + :jml_hari) >= 90
                     OR (COALESCE(hari_menunggak_bunga,0) + :jml_hari) >= 90
                     OR (tgl_jatuh_tempo BETWEEN :awal_bulan AND :akhir_bulan)
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
                    
                    -- AMAN (Sisa hari tidak tembus 90 & bukan JT)
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :awal_bulan OR f.tgl_jatuh_tempo > :akhir_bulan OR f.tgl_jatuh_tempo IS NULL) AND (f.hari_menunggak + :sisa_hari) < 90 AND (f.hari_menunggak_pokok + :sisa_hari) < 90 AND (f.hari_menunggak_bunga + :sisa_hari) < 90 THEN 1 ELSE 0 END) AS noa_aman,
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :awal_bulan OR f.tgl_jatuh_tempo > :akhir_bulan OR f.tgl_jatuh_tempo IS NULL) AND (f.hari_menunggak + :sisa_hari) < 90 AND (f.hari_menunggak_pokok + :sisa_hari) < 90 AND (f.hari_menunggak_bunga + :sisa_hari) < 90 THEN f.baki_debet ELSE 0 END) AS baki_aman,

                    -- JATUH TEMPO
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND f.tgl_jatuh_tempo BETWEEN :awal_bulan AND :akhir_bulan THEN 1 ELSE 0 END) AS noa_jt,
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND f.tgl_jatuh_tempo BETWEEN :awal_bulan AND :akhir_bulan THEN f.baki_debet ELSE 0 END) AS baki_jt,
                    
                    -- FLOW KOLEK (KL/D/M)
                    SUM(CASE WHEN f.kolektibilitas IN ('KL','D','M') THEN 1 ELSE 0 END) AS noa_flow,
                    SUM(CASE WHEN f.kolektibilitas IN ('KL','D','M') THEN f.baki_debet ELSE 0 END) AS baki_flow,

                    -- MASIH POTENSI
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :awal_bulan OR f.tgl_jatuh_tempo > :akhir_bulan OR f.tgl_jatuh_tempo IS NULL) AND ((f.hari_menunggak + :sisa_hari) >= 90 OR (f.hari_menunggak_pokok + :sisa_hari) >= 90 OR (f.hari_menunggak_bunga + :sisa_hari) >= 90) THEN 1 ELSE 0 END) AS noa_potensi,
                    SUM(CASE WHEN f.kolektibilitas NOT IN ('KL','D','M') AND (f.tgl_jatuh_tempo < :awal_bulan OR f.tgl_jatuh_tempo > :akhir_bulan OR f.tgl_jatuh_tempo IS NULL) AND ((f.hari_menunggak + :sisa_hari) >= 90 OR (f.hari_menunggak_pokok + :sisa_hari) >= 90 OR (f.hari_menunggak_bunga + :sisa_hari) >= 90) THEN f.baki_debet ELSE 0 END) AS baki_potensi

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
            $stmt->bindValue(':closing',     $closing);
            $stmt->bindValue(':harian_date', $harian_date);
            $stmt->bindValue(':awal_bulan',  $awalBulan);
            $stmt->bindValue(':akhir_bulan', $akhirBulan);
            $stmt->bindValue(':jml_hari',    $jml_hari_bulan);
            $stmt->bindValue(':sisa_hari',   $sisa_hari);
            
            if ($kc_val) {
                $stmt->bindValue(':kc_master', $kc_val);
                $stmt->bindValue(':kc1', $kc_val);
                $stmt->bindValue(':kc2', $kc_val);
            }
            
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $filterKantorClosing = $kode_kantor && $kode_kantor !== '000' ? " AND n.kode_cabang = :kode_kantor " : "";
        $filterKankasClosing = $kode_kankas !== '' ? " AND n.kode_group1 = :kode_kankas " : "";
        $filterKantorTrx     = $kode_kantor && $kode_kantor !== '000' ? " AND t.kode_kantor = :kode_kantor_trx " : "";

        $sql = "
            WITH kandidat AS (
                SELECT
                    n.no_rekening,
                    n.kode_cabang,
                    n.kode_group1,
                    n.nama_nasabah,
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
                        (COALESCE(n.hari_menunggak,0)       + :jml_hari) >= 90
                    OR (COALESCE(n.hari_menunggak_pokok,0) + :jml_hari) >= 90
                    OR (COALESCE(n.hari_menunggak_bunga,0) + :jml_hari) >= 90
                    OR (n.tgl_jatuh_tempo BETWEEN :bulan_awal AND :bulan_akhir)
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
                    h.tgl_jatuh_tempo AS jt_harian
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
                kd.kolek_closing,
                kd.baki_debet_closing,
                h.kolek_harian,
                h.baki_debet_harian,
                h.tunggakan_pokok,
                h.tunggakan_bunga,
                (COALESCE(h.tunggakan_pokok, 0) + COALESCE(h.tunggakan_bunga, 0)) AS total_tunggakan,
                h.hm_harian,
                h.hmp_harian,
                h.hmb_harian,
                h.jt_harian,
                CASE 
                    WHEN h.no_rekening IS NULL OR h.baki_debet_harian = 0 THEN 'LUNAS / AMAN'
                    WHEN h.kolek_harian IN ('KL','D','M') THEN 'FLOW KOLEK'
                    WHEN h.jt_harian BETWEEN :bulan_awal AND :bulan_akhir THEN 'JATUH TEMPO'
                    WHEN (h.hm_harian + :sisa_hari) < 90 AND (h.hmp_harian + :sisa_hari) < 90 AND (h.hmb_harian + :sisa_hari) < 90 THEN 'AMAN'
                    ELSE 'MASIH POTENSI'
                END AS status_potensi,
                tr.tgl_trans_terakhir,
                tr.angsuran_pokok,
                tr.angsuran_bunga
            FROM kandidat kd
            LEFT JOIN harian h ON kd.no_rekening = h.no_rekening
            LEFT JOIN trx    tr ON kd.no_rekening = tr.no_rekening
            LEFT JOIN kode_kantor kk ON kd.kode_cabang = kk.kode_kantor
            LEFT JOIN kankas kas ON kd.kode_group1 = kas.kode_group1
            WHERE kk.kode_kantor <> '000'
            ORDER BY kd.baki_debet_closing DESC, kd.no_rekening
        ";

        try {
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':closing_date',    $closing_date);
            $st->bindValue(':harian_date',     $harian_date);
            $st->bindValue(':awal_date',       $awal_date);
            $st->bindValue(':harian_date_trx', $harian_date);
            
            $st->bindValue(':jml_hari',        $jml_hari_bulan);
            $st->bindValue(':sisa_hari',       $sisa_hari);
            $st->bindValue(':bulan_awal',      $bulan_awal);
            $st->bindValue(':bulan_akhir',     $bulan_akhir);

            if ($kode_kantor && $kode_kantor !== '000') {
                $st->bindValue(':kode_kantor',     $kode_kantor);
                $st->bindValue(':kode_kantor_trx', $kode_kantor);
            }
            if ($kode_kankas !== '') {
                $st->bindValue(':kode_kankas', $kode_kankas);
            }

            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            
            // Format Output numeric
            $numericFields = [
                'baki_debet_closing', 'baki_debet_harian', 'tunggakan_pokok', 'tunggakan_bunga', 
                'total_tunggakan', 'hm_harian', 'hmp_harian', 'hmb_harian', 'angsuran_pokok', 'angsuran_bunga'
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






    
    public function getLastCreatedDate() {
        $sql = "SELECT MAX(created) AS last_created FROM nominatif";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastCreated = $result['last_created'];
        $closingDate = null;
        $awalBulan   = null;

        if ($lastCreated) {
            $closingDateObj = new DateTime($lastCreated);

            // Hitung closing date: akhir bulan sebelum tanggal lastCreated
            $closingDateObj->modify('last day of previous month');
            $closingDate = $closingDateObj->format('Y-m-d');

            // Hitung awal bulan dari lastCreated
            $awalBulanObj = new DateTime($lastCreated);
            $awalBulanObj->modify('first day of this month');
            $awalBulan = $awalBulanObj->format('Y-m-d');
        }

        sendResponse(200, "Tanggal terakhir data nominatif", [
            'awal_bulan'   => $awalBulan,
            'last_created' => $lastCreated,
            'last_closing' => $closingDate
        ]);
    }

    public function getTop50FlowPar($input) {
        $closing_date  = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
        $awal_date     = $input['awal_date'] ?? date('Y-m-01');
        $harian_date   = $input['harian_date'] ?? date('Y-m-d');

        $sql = "
            WITH closing AS (
                SELECT 
                    no_rekening,
                    kolektibilitas AS kolek_closing
                FROM nominatif
                WHERE created = :closing_date
                AND kolektibilitas IN ('L', 'DP')
            ),

            harian AS (
                SELECT 
                    no_rekening,
                    kode_cabang,
                    kolektibilitas AS kolek_harian,
                    baki_debet,
                    alamat,
                    tunggakan_pokok,
                    tunggakan_bunga,
                    nama_nasabah,
                    tgl_realisasi
                FROM nominatif
                WHERE created = :harian_date
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
                GROUP BY no_rekening
            )

            SELECT
                h.kode_cabang,
                k.nama_kantor,
                h.no_rekening,
                h.nama_nasabah,
                c.kolek_closing,
                h.alamat,
                h.kolek_harian,
                h.baki_debet,
                h.tunggakan_pokok,
                h.tunggakan_bunga,
                h.tgl_realisasi,
                trx.tgl_trans,
                trx.angsuran_pokok,
                trx.angsuran_bunga,
                trx.angsuran_denda
            FROM harian h
            JOIN closing c ON h.no_rekening = c.no_rekening
            LEFT JOIN trx ON h.no_rekening = trx.no_rekening
            LEFT JOIN kode_kantor k ON h.kode_cabang = k.kode_kantor
            ORDER BY h.baki_debet DESC
            LIMIT 50
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':closing_date', $closing_date);
        $stmt->bindValue(':harian_date', $harian_date);
        $stmt->bindValue(':harian_date_trx', $harian_date);
        $stmt->bindValue(':awal_date', $awal_date);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, "Top 50 debitur flow PAR konsolidasi", $data);
    }

    public function updateKomitmenKlBaru($input) {
        $rekening        = $input['rekening'] ?? null;
        $komitmen        = $input['komitmen'] ?? null;
        $alasan          = $input['alasan'] ?? null;
        $tgl_pembayaran  = $input['tgl_pembayaran'] ?? date('Y-m-d');
        // Tangkap nilai nominal, pastikan hanya angka yang masuk (hapus karakter selain digit jika perlu, atau cast to int)
        $nominal         = isset($input['nominal']) ? (int) $input['nominal'] : 0; 
        
        $tanggal = date('Y-m-d');

        if (!$rekening || !$komitmen) {
            sendResponse(400, "Request tidak valid");
            return;
        }

        // Cek apakah data dengan rekening dan bulan yang sama sudah ada
        $sql_check = "
            SELECT id, created FROM komitmen_flowpar 
            WHERE no_rekening = :rekening 
            AND DATE_FORMAT(created, '%Y-%m') = DATE_FORMAT(:created, '%Y-%m')
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql_check);
        $stmt->execute([
            ':rekening' => $rekening,
            ':created' => $tanggal
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Data sudah ada → lakukan UPDATE, pertahankan created, UPDATE nominal
            $sql_update = "
                UPDATE komitmen_flowpar 
                SET 
                    tgl_pembayaran = :tgl_pembayaran,
                    komitmen = :komitmen,
                    alasan = :alasan,
                    nominal = :nominal,
                    updated = NOW()
                WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql_update);
            $stmt->execute([
                ':tgl_pembayaran' => $tgl_pembayaran,
                ':komitmen' => $komitmen,
                ':alasan' => $alasan,
                ':nominal' => $nominal, // Bind parameter nominal
                ':id' => $existing['id']
            ]);

            sendResponse(200, "Data komitmen berhasil diupdate");
        } else {
            // Data belum ada → lakukan INSERT baru termasuk nominal
            $sql_insert = "
                INSERT INTO komitmen_flowpar 
                    (no_rekening, komitmen, alasan, tgl_pembayaran, nominal, created, updated)
                VALUES 
                    (:rekening, :komitmen, :alasan, :tgl_pembayaran, :nominal, NOW(), NOW())
            ";
            $stmt = $this->pdo->prepare($sql_insert);
            $stmt->execute([
                ':rekening' => $rekening,
                ':komitmen' => $komitmen,
                ':alasan' => $alasan,
                ':tgl_pembayaran' => $tgl_pembayaran,
                ':nominal' => $nominal // Bind parameter nominal
            ]);
            
            sendResponse(200, "Data komitmen berhasil disimpan");
        }
    }




    // ======================= Helper Buckets & Utilities =======================
private function loadBuckets(): array {
    $rows = $this->pdo->query("
      SELECT dpd_code, dpd_name, min_day, max_day, status_tag
      FROM ref_dpd_bucket ORDER BY min_day
    ")->fetchAll(PDO::FETCH_ASSOC);

    $def = []; $name=[]; $tag=[];
    foreach ($rows as $r){
      $def[] = [
        'code'=>$r['dpd_code'],
        'name'=>$r['dpd_name'],
        'min'=>(int)$r['min_day'],
        'max'=>is_null($r['max_day'])?null:(int)$r['max_day'],
        'tag'=>$r['status_tag'] ?? null
      ];
      $name[$r['dpd_code']] = $r['dpd_name'];
      $tag[$r['dpd_code']]  = $r['status_tag'] ?? null;
    }
    return [$def,$name,$tag];
}

private function dpdToCode(int $dpd, array $defs): ?string {
    foreach ($defs as $b) {
      if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) return $b['code'];
    }
    return null;
}

private function dayRange(string $d): array {
    return [$d." 00:00:00", date('Y-m-d', strtotime("$d +1 day"))." 00:00:00"];
}

// ======================= Endpoint: Detail Debitur Flow PAR =======================
public function getDebiturFlowParXX($input) {
    // --- Parse & normalisasi input
    $kode_kantor  = isset($input['kode_kantor']) ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
    if (!$kode_kantor) {
        sendResponse(400, "Parameter 'kode_kantor' wajib diisi", []);
        return;
    }
    $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
    $awal_date    = $input['awal_date']    ?? date('Y-m-01');
    $harian_date  = $input['harian_date']  ?? date('Y-m-d');

    $sql = "
        WITH closing AS (
            SELECT 
                no_rekening,
                kode_cabang,
                kolektibilitas AS kolek_closing,
                tunggakan_pokok,
                tunggakan_bunga
            FROM nominatif
            WHERE created = :closing_date
              AND kolektibilitas IN ('L', 'DP')
              AND kode_cabang = :kode_kantor_closing
        ),
        harian AS (
            SELECT 
                no_rekening,
                kode_cabang,
                kolektibilitas AS kolek_harian,
                baki_debet,
                tunggakan_pokok,
                tunggakan_bunga,
                nama_nasabah,
                tgl_realisasi,
                tgl_jatuh_tempo,
                hari_menunggak,
                norek_tabungan
            FROM nominatif
            WHERE created = :harian_date
              AND kolektibilitas IN ('KL', 'D', 'M')
              AND kode_cabang = :kode_kantor_harian
        ),
        trx AS (
            SELECT 
                no_rekening,
                MAX(tgl_trans)      AS tgl_trans,
                SUM(angsuran_pokok) AS angsuran_pokok,
                SUM(angsuran_bunga) AS angsuran_bunga,
                SUM(angsuran_denda) AS angsuran_denda
            FROM transaksi_kredit
            WHERE tgl_trans BETWEEN :awal_date AND :harian_date_trx
              AND kode_kantor = :kode_kantor_trx
            GROUP BY no_rekening
        )
        SELECT
            h.kode_cabang,
            k.nama_kantor,
            h.no_rekening,
            h.nama_nasabah,
            c.kolek_closing,
            h.kolek_harian,
            h.baki_debet,
            h.tunggakan_pokok,
            h.tunggakan_bunga,
            h.hari_menunggak,
            tb.saldo_akhir,
            tb.saldo_blokir,
            h.tgl_realisasi,
            h.tgl_jatuh_tempo,
            h.norek_tabungan,
            trx.angsuran_pokok,
            trx.angsuran_bunga,
            trx.angsuran_denda,
            trx.tgl_trans,
            km.komitmen,
            km.tgl_pembayaran,
            km.alasan
        FROM harian h
        JOIN closing c 
          ON h.no_rekening = c.no_rekening
        LEFT JOIN trx 
          ON h.no_rekening = trx.no_rekening
        LEFT JOIN kode_kantor k 
          ON h.kode_cabang = k.kode_kantor
        LEFT JOIN komitmen_flowpar km 
          ON h.no_rekening = km.no_rekening
         AND DATE_FORMAT(COALESCE(km.updated, km.created), '%Y-%m') = DATE_FORMAT(:harian_date_km, '%Y-%m')
        LEFT JOIN tabungan tb
          ON tb.no_rekening = h.norek_tabungan
        ORDER BY h.baki_debet DESC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':closing_date',        $closing_date);
    $stmt->bindValue(':harian_date',         $harian_date);
    $stmt->bindValue(':harian_date_trx',     $harian_date);
    $stmt->bindValue(':awal_date',           $awal_date);
    $stmt->bindValue(':kode_kantor_closing', $kode_kantor);
    $stmt->bindValue(':kode_kantor_harian',  $kode_kantor);
    $stmt->bindValue(':kode_kantor_trx',     $kode_kantor);
    $stmt->bindValue(':harian_date_km',      $harian_date);

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---------- Tambahkan kolom DPD berdasar hari_menunggak ----------
    list($defs, $nameMap, $tagMap) = $this->loadBuckets(); // defs: [code,min,max,....], nameMap: code=>name, tagMap: code=>tag
    foreach ($data as &$row) {
        $hm = isset($row['hari_menunggak']) && $row['hari_menunggak'] !== '' ? (int)$row['hari_menunggak'] : null;
        if ($hm !== null) {
            $code = $this->dpdToCode($hm, $defs); // contoh hasil: "E_DPD"
            $row['dpd_code']   = $code;                             // opsional
            $row['dpd_name']   = $code ? ($nameMap[$code] ?? null) : null; // contoh: "91-120"
            $row['status_tag'] = $code ? ($tagMap[$code] ?? null) : null;   // opsional
            $row['DPD']        = ($code && isset($nameMap[$code]))
                                  ? ($code . ' ' . $nameMap[$code])          // "E_DPD 91-120"
                                  : null;
        } else {
            $row['dpd_code'] = $row['dpd_name'] = $row['status_tag'] = $row['DPD'] = null;
        }
    }
    unset($row);

    sendResponse(200, "Detail debitur flow PAR untuk cabang $kode_kantor", $data);
}


public function getDetailDebitur($input) {
    // ===== Validasi input dasar =====
    $no_rekening  = trim($input['no_rekening']  ?? '');
    $kode_cabangI = trim($input['kode_cabang']  ?? '');
    if ($no_rekening === '' || $kode_cabangI === '') {
        return sendResponse(400, "no_rekening dan kode_cabang wajib diisi", []);
    }

    // Normalisasi kode cabang (3 digit)
    $kode_cabang  = str_pad($kode_cabangI, 3, '0', STR_PAD_LEFT);

    // Tanggal default
    $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
    $harian_date  = $input['harian_date']  ?? date('Y-m-d');

    // Awal periode transaksi = awal bulan dari harian_date (bisa diubah jika perlu)
    $awal_date    = date('Y-m-01', strtotime($harian_date));

    $sql = "
        WITH closing AS (
            SELECT 
                no_rekening,
                kode_cabang,
                kolektibilitas AS kolek_closing,
                tunggakan_pokok,
                tunggakan_bunga
            FROM nominatif
            WHERE created = :closing_date
              AND kolektibilitas IN ('L','DP')
              AND kode_cabang = :kode_cabang_closing
              AND no_rekening = :no_rekening_closing
        ),
        harian AS (
            SELECT 
                no_rekening,
                kode_cabang,
                kolektibilitas AS kolek_harian,
                baki_debet,
                tunggakan_pokok,
                tunggakan_bunga,
                nama_nasabah,
                tgl_realisasi,
                tgl_jatuh_tempo,
                hari_menunggak,
                norek_tabungan
            FROM nominatif
            WHERE created = :harian_date
              AND kolektibilitas IN ('KL','D','M')
              AND kode_cabang = :kode_cabang_harian
              AND no_rekening = :no_rekening_harian
        ),
        trx AS (
            SELECT 
                no_rekening,
                MAX(tgl_trans)      AS tgl_trans,
                SUM(angsuran_pokok) AS angsuran_pokok,
                SUM(angsuran_bunga) AS angsuran_bunga,
                SUM(angsuran_denda) AS angsuran_denda
            FROM transaksi_kredit
            WHERE tgl_trans BETWEEN :awal_date AND :harian_date_trx
              AND kode_kantor = :kode_kantor_trx
              AND no_rekening = :no_rekening_trx
            GROUP BY no_rekening
        )
        SELECT
            h.kode_cabang,
            k.nama_kantor,
            h.no_rekening,
            h.nama_nasabah,
            c.kolek_closing,
            h.kolek_harian,
            h.baki_debet,
            h.tunggakan_pokok,
            h.tunggakan_bunga,
            h.hari_menunggak,
            tb.saldo_akhir,
            tb.saldo_blokir,
            h.tgl_realisasi,
            h.tgl_jatuh_tempo,
            h.norek_tabungan,
            trx.angsuran_pokok,
            trx.angsuran_bunga,
            trx.angsuran_denda,
            trx.tgl_trans,
            km.komitmen,
            km.tgl_pembayaran,
            km.alasan
        FROM harian h
        JOIN closing c 
          ON h.no_rekening = c.no_rekening
        LEFT JOIN trx 
          ON h.no_rekening = trx.no_rekening
        LEFT JOIN kode_kantor k 
          ON h.kode_cabang = k.kode_kantor
        LEFT JOIN komitmen_flowpar km 
          ON h.no_rekening = km.no_rekening
         AND DATE_FORMAT(COALESCE(km.updated, km.created), '%Y-%m') = DATE_FORMAT(:harian_date_km, '%Y-%m')
        LEFT JOIN tabungan tb
          ON tb.no_rekening = h.norek_tabungan
        ORDER BY h.baki_debet DESC
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);

    // Bind tanggal
    $stmt->bindValue(':closing_date',      $closing_date);
    $stmt->bindValue(':harian_date',       $harian_date);     // untuk CTE harian
    $stmt->bindValue(':awal_date',         $awal_date);
    $stmt->bindValue(':harian_date_trx',   $harian_date);     // untuk trx
    $stmt->bindValue(':harian_date_km',    $harian_date);     // join komitmen

    // Bind kode cabang / kantor
    $stmt->bindValue(':kode_cabang_closing', $kode_cabang);
    $stmt->bindValue(':kode_cabang_harian',  $kode_cabang);
    $stmt->bindValue(':kode_kantor_trx',     $kode_cabang);

    // Bind no rekening (semua tempat)
    $stmt->bindValue(':no_rekening_closing', $no_rekening);
    $stmt->bindValue(':no_rekening_harian',  $no_rekening);
    $stmt->bindValue(':no_rekening_trx',     $no_rekening);

    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return sendResponse(404, "Detail debitur tidak ditemukan untuk $no_rekening di cabang $kode_cabang", []);
    }

    return sendResponse(200, "Detail debitur flow PAR ($no_rekening) – cabang $kode_cabang", $row);
}











    












}
