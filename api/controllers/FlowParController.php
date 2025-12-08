<?php

require_once __DIR__ . '/../helpers/response.php';

class FlowParController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



    // ✅ READ Recovery Hapus Buku
    public function getFlowPar($input = []) {
        $closing_date = isset($input['closing_date']) ? $input['closing_date'] : date('Y-m-d', strtotime('last day of previous month'));
        $harian_date  = isset($input['harian_date'])  ? $input['harian_date']  : date('Y-m-d');

        $sql = "
            WITH closing AS (
                SELECT 
                    no_rekening,
                    kode_cabang,
                    kolektibilitas
                FROM nominatif
                WHERE created = :closing_date
                AND kolektibilitas IN ('L', 'DP')
            ),

            harian AS (
                SELECT 
                    no_rekening,
                    kode_cabang,
                    kolektibilitas,
                    baki_debet
                FROM nominatif
                WHERE created = :harian_date
                AND kolektibilitas IN ('KL', 'D', 'M')
            ),

            flow_par AS (
                SELECT 
                    h.kode_cabang,
                    h.no_rekening,
                    h.baki_debet
                FROM harian h
                JOIN closing c ON h.no_rekening = c.no_rekening
            ),

            rekap_cabang AS (
                SELECT 
                    k.kode_kantor AS kode_cabang,
                    k.nama_kantor,
                    COUNT(f.no_rekening) AS noa_flow,
                    COALESCE(SUM(f.baki_debet), 0) AS baki_debet_flow
                FROM kode_kantor k
                LEFT JOIN flow_par f ON f.kode_cabang = k.kode_kantor
                WHERE k.kode_kantor <> '000'
                GROUP BY k.kode_kantor, k.nama_kantor
            )

            SELECT 
                kode_cabang,
                nama_kantor,
                noa_flow,
                baki_debet_flow
            FROM rekap_cabang

            UNION ALL

            SELECT
                NULL,
                'TOTAL KONSOLIDASI',
                SUM(noa_flow),
                SUM(baki_debet_flow)
            FROM rekap_cabang

            ORDER BY
                CASE WHEN nama_kantor = 'TOTAL KONSOLIDASI' THEN 1 ELSE 0 END,
                kode_cabang
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':closing_date', $closing_date);
        $stmt->bindValue(':harian_date', $harian_date);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Berhasil ambil data Flow PAR", $data);
    }

public function getDebiturFlowPar($input) {
    $kode_kantor  = str_pad($input['kode_kantor'] ?? '', 3, '0', STR_PAD_LEFT);
    if ($kode_kantor === '' || $kode_kantor === '00 ') {
        return sendResponse(400, "kode_kantor wajib diisi", []);
    }

    $closing_date = $input['closing_date'] ?? date('Y-m-d', strtotime('last day of previous month'));
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
            WHERE created = :closing_date_closing
              AND kolektibilitas IN ('L','DP')
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
                hari_menunggak_pokok,
                hari_menunggak_bunga,
                norek_tabungan
            FROM nominatif
            WHERE created = :harian_date_harian
              AND kolektibilitas IN ('KL','D','M')
              AND kode_cabang = :kode_kantor_harian
        ),
        trx AS (
            SELECT 
                no_rekening,
                MAX(tgl_trans)                  AS tgl_trans,
                SUM(COALESCE(angsuran_pokok,0)) AS angsuran_pokok,
                SUM(COALESCE(angsuran_bunga,0)) AS angsuran_bunga,
                SUM(COALESCE(angsuran_denda,0)) AS angsuran_denda
            FROM transaksi_kredit
            WHERE tgl_trans >  :closing_date_trx   /* > closing_date */
              AND tgl_trans <= :harian_date_trx    /* <= harian_date */
              AND kode_kantor = :kode_kantor_trx
            GROUP BY no_rekening
        ),
        /* Komitmen terbaru per rekening berdasarkan BULAN-TAHUN harian_date */
        km_last AS (
            SELECT k.no_rekening,
                   k.komitmen,
                   k.tgl_pembayaran,
                   k.alasan
            FROM komitmen_flowpar k
            JOIN (
                SELECT no_rekening,
                       MAX(COALESCE(updated, created)) AS last_ts
                FROM komitmen_flowpar
                WHERE DATE_FORMAT(COALESCE(updated, created), '%Y-%m')
                      = DATE_FORMAT(:harian_date_km, '%Y-%m')
                GROUP BY no_rekening
            ) s ON s.no_rekening = k.no_rekening
               AND COALESCE(k.updated, k.created) = s.last_ts
        )
        SELECT
            h.kode_cabang,
            kk.nama_kantor,
            h.no_rekening,
            h.nama_nasabah,
            c.kolek_closing,
            h.kolek_harian,
            h.baki_debet,
            h.tunggakan_pokok,
            h.tunggakan_bunga,
            h.hari_menunggak,
            h.hari_menunggak_pokok,
            h.hari_menunggak_bunga,
            tb.saldo_akhir,
            tb.saldo_blokir,
            h.tgl_realisasi,
            h.tgl_jatuh_tempo,
            h.norek_tabungan,
            trx.angsuran_pokok,
            trx.angsuran_bunga,
            trx.angsuran_denda,
            trx.tgl_trans,
            km_last.komitmen,
            km_last.tgl_pembayaran,
            km_last.alasan
        FROM harian h
        JOIN closing c 
          ON h.no_rekening = c.no_rekening
        LEFT JOIN trx 
          ON h.no_rekening = trx.no_rekening
        LEFT JOIN kode_kantor kk
          ON h.kode_cabang = kk.kode_kantor
        LEFT JOIN km_last
          ON km_last.no_rekening = h.no_rekening
        LEFT JOIN tabungan tb
          ON tb.no_rekening = h.norek_tabungan
        ORDER BY h.baki_debet DESC
    ";

    $stmt = $this->pdo->prepare($sql);

    // Bind: CTE closing
    $stmt->bindValue(':closing_date_closing', $closing_date);
    $stmt->bindValue(':kode_kantor_closing',  $kode_kantor);

    // Bind: CTE harian
    $stmt->bindValue(':harian_date_harian',   $harian_date);
    $stmt->bindValue(':kode_kantor_harian',   $kode_kantor);

    // Bind: CTE trx (range > closing && <= harian)
    $stmt->bindValue(':closing_date_trx',     $closing_date);
    $stmt->bindValue(':harian_date_trx',      $harian_date);
    $stmt->bindValue(':kode_kantor_trx',      $kode_kantor);

    // Bind: komitmen bulanan (berdasarkan bulan & tahun dari harian_date)
    $stmt->bindValue(':harian_date_km',       $harian_date);

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalisasi angka ke number di JSON
    $numericFields = [
        'baki_debet','tunggakan_pokok','tunggakan_bunga',
        'hari_menunggak','hari_menunggak_pokok','hari_menunggak_bunga',
        'saldo_akhir','saldo_blokir',
        'angsuran_pokok','angsuran_bunga','angsuran_denda'
    ];
    foreach ($data as &$row) {
        foreach ($numericFields as $f) {
            if (array_key_exists($f, $row) && $row[$f] !== null && $row[$f] !== '') {
                $row[$f] = 0 + $row[$f];
            }
        }
    }
    unset($row);

    sendResponse(200, "Detail debitur flow PAR untuk cabang $kode_kantor", $data);
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
            // Data sudah ada → lakukan UPDATE, pertahankan created
            $sql_update = "
                UPDATE komitmen_flowpar 
                SET 
                    tgl_pembayaran = :tgl_pembayaran,
                    komitmen = :komitmen,
                    alasan = :alasan,
                    updated = NOW()
                WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql_update);
            $stmt->execute([
                ':tgl_pembayaran' => $tgl_pembayaran,
                ':komitmen' => $komitmen,
                ':alasan' => $alasan,
                ':id' => $existing['id']
            ]);

            sendResponse(200, "Data komitmen berhasil diupdate");
        } else {
            // Data belum ada → lakukan INSERT baru
            $sql_insert = "
                INSERT INTO komitmen_flowpar 
                    (no_rekening, komitmen, alasan, tgl_pembayaran, created, updated)
                VALUES 
                    (:rekening, :komitmen, :alasan, :tgl_pembayaran, NOW(), NOW())
            ";
            $stmt = $this->pdo->prepare($sql_insert);
            $stmt->execute([
                ':rekening' => $rekening,
                ':komitmen' => $komitmen,
                ':alasan' => $alasan,
                ':tgl_pembayaran' => $tgl_pembayaran
            ]);
            sendResponse(200, "Data komitmen berhasil disimpan");
        }
    }

    public function getPotensiNplRekap($input = [])
    {
        // Periode target (tanggal apa pun di bulan yg diinginkan). Default: hari ini.
        $periode = $input['periode'] ?? $input['bulan'] ?? $input['harian_date'] ?? date('Y-m-d');

        $base       = new DateTime($periode);
        $awalBulan  = $base->format('Y-m-01');
        $akhirBulan = $base->format('Y-m-t');
        // created = closing bulan kemarin
        $closing    = (clone $base)->modify('first day of this month')->modify('-1 day')->format('Y-m-d');

        $sql = "
            WITH data_harian AS (
                SELECT 
                    kode_cabang,
                    baki_debet,
                    hari_menunggak,
                    hari_menunggak_pokok,
                    hari_menunggak_bunga,
                    tgl_jatuh_tempo
                FROM nominatif
                WHERE created = :closing
                AND kolektibilitas IN ('L','DP')
                AND (
                        (hari_menunggak + 30)       >= 90
                    OR (hari_menunggak_pokok + 30) >= 90
                    OR (hari_menunggak_bunga + 30) >= 90
                    OR (
                        tgl_jatuh_tempo >= :awal_bulan
                    AND tgl_jatuh_tempo <= :akhir_bulan
                    AND DATE_ADD(tgl_jatuh_tempo, INTERVAL 15 DAY) <= :akhir_bulan
                    )
                )
            ),
            rekap AS (
                SELECT
                    d.kode_cabang,
                    k.nama_kantor AS nama_cabang,
                    COUNT(*) AS noa,
                    COALESCE(SUM(d.baki_debet),0) AS baki_debet
                FROM data_harian d
                JOIN kode_kantor k ON d.kode_cabang = k.kode_kantor
                WHERE k.kode_kantor <> '000'
                GROUP BY d.kode_cabang, k.nama_kantor
            )
            SELECT 
                kode_cabang,
                nama_cabang,
                noa,
                baki_debet
            FROM rekap

            UNION ALL

            SELECT
                NULL                 AS kode_cabang,
                'TOTAL KONSOLIDASI'  AS nama_cabang,
                SUM(noa)             AS noa,
                SUM(baki_debet)      AS baki_debet
            FROM rekap

            ORDER BY
                CASE WHEN nama_cabang = 'TOTAL KONSOLIDASI' THEN 1 ELSE 0 END,
                kode_cabang
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':closing',     $closing);
        $stmt->bindValue(':awal_bulan',  $awalBulan);
        $stmt->bindValue(':akhir_bulan', $akhirBulan);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, "Berhasil ambil Rekap Potensi NPL", $rows, [
            'meta' => [
                'periode_bulan' => $base->format('Y-m'),
                'closing'       => $closing,
                'awal_bulan'    => $awalBulan,
                'akhir_bulan'   => $akhirBulan
            ]
        ]);
    }

    public function getDetailPotensiNpl($input = [])
    {
        $kode_kantor = isset($input['kode_kantor']) && $input['kode_kantor'] !== ''
            ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT)
            : null;

        $closing_date = !empty($input['closing_date'])
            ? date('Y-m-d', strtotime($input['closing_date']))
            : date('Y-m-d', strtotime('last day of previous month'));

        $harian_date = !empty($input['harian_date'])
            ? date('Y-m-d', strtotime($input['harian_date']))
            : date('Y-m-d');

        $awal_date = !empty($input['awal_date'])
            ? date('Y-m-d', strtotime($input['awal_date']))
            : date('Y-m-01', strtotime($harian_date));

        $bulan_awal      = date('Y-m-01', strtotime($harian_date));
        $bulan_akhir     = date('Y-m-t',  strtotime($harian_date));
        $bulan_awal_next = date('Y-m-01', strtotime($bulan_awal.' +1 month'));

        $filterKantorClosing = $kode_kantor && $kode_kantor !== '000' ? " AND n.kode_cabang = :kode_kantor " : "";
        $filterKantorTrx     = $kode_kantor && $kode_kantor !== '000' ? " AND t.kode_kantor = :kode_kantor_trx " : "";

        $sql = "
            WITH kandidat AS (
                SELECT
                    n.no_rekening,
                    n.kode_cabang,
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
                AND (
                        COALESCE(n.hari_menunggak,0)       + 30 >= 90
                    OR COALESCE(n.hari_menunggak_pokok,0) + 30 >= 90
                    OR COALESCE(n.hari_menunggak_bunga,0) + 30 >= 90
                    OR (
                            n.tgl_jatuh_tempo >= :bulan_awal
                        AND n.tgl_jatuh_tempo <  :bulan_awal_next
                        AND DATE_ADD(n.tgl_jatuh_tempo, INTERVAL 15 DAY) <= :bulan_akhir
                    )
                )
            ),
            harian AS (
                SELECT
                    h.no_rekening,
                    h.kolektibilitas AS kolek_harian,
                    h.baki_debet     AS baki_debet_harian,
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
                kd.no_rekening,
                kd.nama_nasabah,
                kd.kolek_closing,
                kd.baki_debet_closing,
                kd.hm_closing,
                kd.hmp_closing,
                kd.hmb_closing,
                kd.jt_closing,
                kd.tgl_realisasi,
                h.kolek_harian,
                h.baki_debet_harian,
                h.hm_harian,
                h.hmp_harian,
                h.hmb_harian,
                h.jt_harian,
                tr.tgl_trans_terakhir,
                tr.angsuran_pokok,
                tr.angsuran_bunga,
                tr.angsuran_denda
            FROM kandidat kd
            LEFT JOIN harian h ON kd.no_rekening = h.no_rekening
            LEFT JOIN trx    tr ON kd.no_rekening = tr.no_rekening
            LEFT JOIN kode_kantor kk ON kd.kode_cabang = kk.kode_kantor
            WHERE kk.kode_kantor <> '000'
            ORDER BY kd.baki_debet_closing DESC, kd.no_rekening
        ";

        try {
            $st = $this->pdo->prepare($sql);
            $st->bindValue(':closing_date',    $closing_date);
            $st->bindValue(':harian_date',     $harian_date);
            $st->bindValue(':awal_date',       $awal_date);
            $st->bindValue(':harian_date_trx', $harian_date);

            $st->bindValue(':bulan_awal',       $bulan_awal);
            $st->bindValue(':bulan_akhir',      $bulan_akhir);
            $st->bindValue(':bulan_awal_next',  $bulan_awal_next);

            if ($kode_kantor && $kode_kantor !== '000') {
                $st->bindValue(':kode_kantor',     $kode_kantor);
                $st->bindValue(':kode_kantor_trx', $kode_kantor);
            }

            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, 'Detail potensi NPL', $rows);
        } catch (Throwable $e) {
            sendResponse(500, 'Gagal ambil detail potensi NPL: '.$e->getMessage());
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
