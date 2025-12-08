<?php

require_once __DIR__ . '/../helpers/response.php';

class HapusBukuController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



    // ✅ READ Recovery Hapus Buku
    public function getRecoveryMount($input = []) {
        $start_date = isset($input['start_date']) ? $input['start_date'] : date('Y-m-01');
        $end_date   = isset($input['end_date'])   ? $input['end_date']   : date('Y-m-d');

        $sql = "
            (
                SELECT 
                    kk.kode_kantor,
                    kk.nama_kantor,
                    COALESCE(SUM(t.pokok), 0) AS total_pokok,
                    COALESCE(SUM(t.bunga), 0) AS total_bunga,
                    COALESCE(SUM(t.total), 0) AS total_ph,
                    COUNT(t.no_rekening) AS noa
                FROM 
                    kode_kantor kk
                LEFT JOIN (
                    SELECT 
                        b.kode_kantor,
                        b.no_rekening,
                        SUM(b.pokok) AS pokok,
                        SUM(b.bunga) AS bunga,
                        SUM(b.total) AS total
                    FROM 
                        transaksi_ph b
                    WHERE 
                        b.tanggal_transaksi BETWEEN :start_date AND :end_date
                    GROUP BY 
                        b.kode_kantor, b.no_rekening
                    HAVING 
                        SUM(b.total) <> 0
                ) t ON kk.kode_kantor = t.kode_kantor
                WHERE 
                    kk.kode_kantor NOT IN ('000')
                GROUP BY 
                    kk.kode_kantor, kk.nama_kantor
            )
            UNION ALL
            (
                SELECT 
                    'TOTAL' AS kode_kantor,
                    'KONSOLIDASI' AS nama_kantor,
                    COALESCE(SUM(t.pokok), 0) AS total_pokok,
                    COALESCE(SUM(t.bunga), 0) AS total_bunga,
                    COALESCE(SUM(t.total), 0) AS total_ph,
                    COUNT(t.no_rekening) AS noa
                FROM (
                    SELECT 
                        b.kode_kantor,
                        b.no_rekening,
                        SUM(b.pokok) AS pokok,
                        SUM(b.bunga) AS bunga,
                        SUM(b.total) AS total
                    FROM 
                        transaksi_ph b
                    WHERE 
                        b.tanggal_transaksi BETWEEN :start_date_2 AND :end_date_2
                    GROUP BY 
                        b.kode_kantor, b.no_rekening
                    HAVING 
                        SUM(b.total) <> 0
                ) t
            )
            ORDER BY 
                CASE 
                    WHEN kode_kantor = 'TOTAL' THEN 1 ELSE 0
                END,
                kode_kantor
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);
        $stmt->bindValue(':start_date_2', $start_date);
        $stmt->bindValue(':end_date_2', $end_date);

        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Berhasil ambil data Recovery PH", $data);
    }


    public function getDetailDebitur($input) {
        $kode_kantor = str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT); // pastikan 3 digit
        $start_date = $input['start_date'];
        $end_date = $input['end_date'];

        $sql = "
            SELECT 
                no_rekening,
                nama_nasabah,
                MAX(tanggal_transaksi) AS tanggal_transaksi,
                SUM(pokok) AS pokok,
                SUM(bunga) AS bunga,
                SUM(total) AS total
            FROM 
                transaksi_ph
            WHERE 
                kode_kantor = :kode_kantor
                AND tanggal_transaksi BETWEEN :start_date AND :end_date
            GROUP BY 
                no_rekening, nama_nasabah
            HAVING 
                SUM(total) <> 0
            ORDER BY 
                nama_nasabah ASC
        ";


        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':kode_kantor', $kode_kantor);
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Daftar debitur berdasarkan kode kantor $kode_kantor", $data);
    }

    public function getRekapSaldoPH($input = []) {
        // Ambil parameter created (opsional)
        $created = $input['created'] ?? null;

        if ($created) {
            // Cek apakah snapshot 'created' ini ada datanya
            $chk = $this->pdo->prepare("SELECT 1 FROM nominatif_hapus_buku WHERE created = :c LIMIT 1");
            $chk->execute([':c' => $created]);
            if (!$chk->fetchColumn()) {
                // Param salah / tidak ada data → kembalikan null
                sendResponse(200, "Data kosong (created = {$created} tidak ditemukan)", null);
                return;
            }
        } else {
            // Jika tidak kirim created, pakai snapshot terbaru
            $latestStmt = $this->pdo->query("SELECT MAX(created) AS latest_created FROM nominatif_hapus_buku");
            $created = $latestStmt->fetchColumn();
            if (!$created) {
                sendResponse(200, "Data kosong (tidak ada snapshot)", null);
                return;
            }
        }

        // Urutan bucket: < 2019, 2019, 2023, 2024, 2025
        $sql = "
            (
                SELECT
                    kk.kode_kantor,
                    kk.nama_kantor,
                    t.bucket_tahun,
                    COALESCE(t.noa, 0)       AS noa,
                    COALESCE(t.saldo_ph, 0)  AS saldo_ph
                FROM kode_kantor kk
                LEFT JOIN (
                    SELECT
                        n.kode_kantor,
                        CASE
                            WHEN YEAR(n.tgl_hapus_buku) < 2019 THEN '< 2019'
                            WHEN YEAR(n.tgl_hapus_buku) = 2019 THEN '2019'
                            WHEN YEAR(n.tgl_hapus_buku) = 2023 THEN '2023'
                            WHEN YEAR(n.tgl_hapus_buku) = 2024 THEN '2024'
                            WHEN YEAR(n.tgl_hapus_buku) = 2025 THEN '2025'
                            ELSE NULL
                        END AS bucket_tahun,
                        COUNT(n.no_rekening)       AS noa,
                        SUM(n.saldo_hapus_buku)    AS saldo_ph
                    FROM nominatif_hapus_buku n
                    WHERE n.created = :created_date
                    AND (
                            YEAR(n.tgl_hapus_buku) < 2019
                            OR YEAR(n.tgl_hapus_buku) IN (2019, 2023, 2024, 2025)
                    )
                    GROUP BY n.kode_kantor, bucket_tahun
                    HAVING bucket_tahun IS NOT NULL
                ) t ON kk.kode_kantor = t.kode_kantor
                WHERE kk.kode_kantor <> '000'
                ORDER BY 
                    kk.kode_kantor,
                    FIELD(t.bucket_tahun, '< 2019','2019','2023','2024','2025')
            )
            UNION ALL
            (
                SELECT
                    'TOTAL' AS kode_kantor,
                    'KONSOLIDASI' AS nama_kantor,
                    sub.bucket_tahun,
                    SUM(sub.noa)      AS noa,
                    SUM(sub.saldo_ph) AS saldo_ph
                FROM (
                    SELECT
                        CASE
                            WHEN YEAR(n.tgl_hapus_buku) < 2019 THEN '< 2019'
                            WHEN YEAR(n.tgl_hapus_buku) = 2019 THEN '2019'
                            WHEN YEAR(n.tgl_hapus_buku) = 2023 THEN '2023'
                            WHEN YEAR(n.tgl_hapus_buku) = 2024 THEN '2024'
                            WHEN YEAR(n.tgl_hapus_buku) = 2025 THEN '2025'
                            ELSE NULL
                        END AS bucket_tahun,
                        COUNT(n.no_rekening)       AS noa,
                        SUM(n.saldo_hapus_buku)    AS saldo_ph
                    FROM nominatif_hapus_buku n
                    WHERE n.created = :created_date_2
                    AND (
                            YEAR(n.tgl_hapus_buku) < 2019
                            OR YEAR(n.tgl_hapus_buku) IN (2019, 2023, 2024, 2025)
                    )
                    GROUP BY bucket_tahun
                    HAVING bucket_tahun IS NOT NULL
                ) sub
                GROUP BY sub.bucket_tahun
                ORDER BY FIELD(sub.bucket_tahun, '< 2019','2019','2023','2024','2025')
            )
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':created_date',   $created);
        $stmt->bindValue(':created_date_2', $created);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Berhasil ambil rekap Saldo PH by bucket tahun (created = {$created})", $rows);
    }


    public function getDetailPHByBucket($input = []) {
        $kode_kantor = trim($input['kode_kantor'] ?? '');
        $bucket      = trim($input['bucket'] ?? ''); // contoh: '< 2019' | '2019' | '2023' | '2024' | '2025'
        $created     = trim($input['created'] ?? '');

        if ($kode_kantor === '' || $bucket === '' || $created === '') {
            sendResponse(422, "kode_kantor, bucket, dan created wajib diisi", []);
            return;
        }

        // Hitung range transaksi: > created s/d (hari-1)
        $start_date = date('Y-m-d', strtotime($created . ' +1 day'));
        $end_date   = date('Y-m-d', strtotime('yesterday')); // h-1

        // Safety: kalau end_date < start_date (misal created = kemarin), pakai end_date = start_date
        if (strtotime($end_date) < strtotime($start_date)) {
            $end_date = $start_date;
        }

        // Validasi bucket ke dalam ekspresi CASE yang sama seperti rekap
        // Bucket lain (di luar daftar) tidak ditampilkan.
        $bucketCase = "
            CASE
                WHEN YEAR(n.tgl_hapus_buku) < 2019 THEN '< 2019'
                WHEN YEAR(n.tgl_hapus_buku) = 2019 THEN '2019'
                WHEN YEAR(n.tgl_hapus_buku) = 2023 THEN '2023'
                WHEN YEAR(n.tgl_hapus_buku) = 2024 THEN '2024'
                WHEN YEAR(n.tgl_hapus_buku) = 2025 THEN '2025'
                ELSE NULL
            END
        ";

        $sql = "
            SELECT
                n.kode_kantor,
                n.nama_kantor,
                n.no_rekening,
                n.cif,
                n.no_rekening_lama,
                n.nama_nasabah,
                n.alamat,
                n.desa,
                n.kecamatan,
                n.kabupaten_kota,
                n.tgl_hapus_buku,
                n.plafond,
                n.jml_hapus_buku,
                n.akumulasi_angsuran_sd_bulan_lalu,
                n.jml_angsuran_bulan_ini,
                n.saldo_hapus_buku,
                n.created,

                -- ringkasan transaksi setelah created s/d h-1
                COALESCE(tp.total_pokok, 0) AS bayar_pokok,
                COALESCE(tp.total_bunga, 0) AS bayar_bunga,
                COALESCE(tp.total_total, 0) AS bayar_total,
                tp.last_payment_date,

                -- flags untuk ordering
                CASE WHEN COALESCE(tp.total_total,0) > 0 THEN 1 ELSE 0 END AS has_payment,
                CASE WHEN COALESCE(n.akumulasi_angsuran_sd_bulan_lalu,0) > 0 THEN 1 ELSE 0 END AS has_prev_month_amount
            FROM nominatif_hapus_buku n
            LEFT JOIN (
                SELECT 
                    b.no_rekening,
                    SUM(b.pokok) AS total_pokok,
                    SUM(b.bunga) AS total_bunga,
                    SUM(b.total) AS total_total,
                    MAX(b.tanggal_transaksi) AS last_payment_date
                FROM transaksi_ph b
                WHERE b.tanggal_transaksi > :start_date
                AND b.tanggal_transaksi <= :end_date
                GROUP BY b.no_rekening
            ) tp ON tp.no_rekening = n.no_rekening
            WHERE n.created = :created
            AND n.kode_kantor = :kode_kantor
            AND {$bucketCase} = :bucket
            AND (
                    YEAR(n.tgl_hapus_buku) < 2019
                    OR YEAR(n.tgl_hapus_buku) IN (2019, 2023, 2024, 2025)
            )
            ORDER BY
                -- 1) yang ada pembayaran di atas
                CASE WHEN COALESCE(tp.total_total,0) > 0 THEN 0 ELSE 1 END,
                -- 2) yang bulan_sebelumnya ada angkanya (akumulasi_angsuran_sd_bulan_lalu > 0)
                CASE WHEN COALESCE(n.akumulasi_angsuran_sd_bulan_lalu,0) > 0 THEN 0 ELSE 1 END,
                -- 3) saldo hapus buku terbesar
                n.saldo_hapus_buku DESC,
                -- 4) tie-breaker opsional
                n.no_rekening
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':created',      $created);
        $stmt->bindValue(':kode_kantor',  $kode_kantor);
        $stmt->bindValue(':bucket',       $bucket);
        $stmt->bindValue(':start_date',   $start_date);
        $stmt->bindValue(':end_date',     $end_date);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, "Berhasil ambil detail PH by bucket (created {$created}, transaksi {$start_date} s/d {$end_date})", $rows);
    }


    public function getListPHLGD($input = [])
    {
        $kode_kantor = trim($input['kode_kantor'] ?? '');
        $created     = trim($input['created'] ?? '');
        $bucket      = trim($input['bucket'] ?? ''); // opsional: '' atau 'YYYY'

        if ($created === '') {
            sendResponse(422, "Parameter 'created' wajib diisi (format YYYY-MM-DD).", []);
            return;
        }

        // --- tanggal acuan ---
        $created_date = date('Y-m-d', strtotime($created));
        $createdYear  = (int)date('Y', strtotime($created_date));

        // range TANGGAL untuk tgl_hapus_buku: (YEAR(created)-5)-01-01 s/d created
        $range_start = sprintf('%04d-01-01', $createdYear - 5);
        $range_end   = $created_date;

        // window transaksi PH: > created s/d H-1 (dikunci bila perlu)
        $tx_start_date = date('Y-m-d', strtotime($created_date . ' +1 day'));
        $tx_end_date   = date('Y-m-d', strtotime('yesterday'));
        if (strtotime($tx_end_date) < strtotime($tx_start_date)) {
            $tx_end_date = $tx_start_date;
        }

        // bucket opsional (filter ke TAHUN tgl_hapus_buku)
        $useBucketFilter = false;
        $bucketYear = null;
        if ($bucket !== '') {
            if (!ctype_digit($bucket)) {
                sendResponse(422, "Bucket harus berupa tahun dalam rentang ".($createdYear-5)."–{$createdYear} atau kosong.", []);
                return;
            }
            $bucketYear = (int)$bucket;
            if ($bucketYear < ($createdYear - 5) || $bucketYear > $createdYear) {
                sendResponse(422, "Bucket di luar rentang ".($createdYear - 5)."–{$createdYear}.", []);
                return;
            }
            $useBucketFilter = true;
        }

        // konsolidasi vs per-kantor
        $isKonsolidasi = ($kode_kantor === '' || $kode_kantor === '000');
        $filterKantorN = $isKonsolidasi ? "" : " AND n.kode_kantor = :kode_kantor_n ";
        $filterKantorT = $isKonsolidasi ? "" : " AND t.kode_kantor = :kode_kantor_t ";
        $filterBucket  = $useBucketFilter ? " AND YEAR(n.tgl_hapus_buku) = :bucket_year " : "";

        $sql = "
            SELECT
                n.kode_kantor,
                kk.nama_kantor,
                n.no_rekening,
                n.cif,
                n.no_rekening_lama,
                n.nama_nasabah,
                n.alamat,
                n.desa,
                n.kecamatan,
                n.kabupaten_kota,
                n.tgl_hapus_buku,
                n.plafond,
                n.jml_hapus_buku,
                n.akumulasi_angsuran_sd_bulan_lalu,
                n.jml_angsuran_bulan_ini,
                n.saldo_hapus_buku,
                n.created,

                -- ringkasan transaksi setelah created s/d H-1
                COALESCE(tp.total_pokok, 0)  AS bayar_pokok,
                COALESCE(tp.total_bunga, 0)  AS bayar_bunga,
                COALESCE(tp.total_total, 0)  AS bayar_total,
                tp.last_payment_date,

                -- kolom baru
                (n.saldo_hapus_buku - COALESCE(tp.total_pokok,0))                                   AS sisa_saldo,
                (n.jml_hapus_buku   - (n.saldo_hapus_buku - COALESCE(tp.total_pokok,0)))            AS recovery,
                ( (n.jml_hapus_buku - (n.saldo_hapus_buku - COALESCE(tp.total_pokok,0)))
                    / NULLIF(n.jml_hapus_buku,0) )                                                  AS rr,

                -- flags bantu (buat sorting kalau perlu)
                CASE WHEN COALESCE(tp.total_total,0) > 0 THEN 1 ELSE 0 END AS has_payment,
                YEAR(n.tgl_hapus_buku) AS tahun_ph
            FROM nominatif_hapus_buku n
            JOIN kode_kantor kk ON kk.kode_kantor = n.kode_kantor
            LEFT JOIN (
                SELECT 
                    t.no_rekening,
                    SUM(t.pokok) AS total_pokok,
                    SUM(t.bunga) AS total_bunga,
                    SUM(t.total) AS total_total,
                    MAX(t.tanggal_transaksi) AS last_payment_date
                FROM transaksi_ph t
                WHERE t.tanggal_transaksi >  :tx_start_date
                AND t.tanggal_transaksi <= :tx_end_date
                {$filterKantorT}
                GROUP BY t.no_rekening
            ) tp ON tp.no_rekening = n.no_rekening
            WHERE n.created = :created
            AND n.tgl_hapus_buku >= :range_start
            AND n.tgl_hapus_buku <= :range_end
            {$filterBucket}
            {$filterKantorN}
            ORDER BY
                has_payment DESC,           -- yang ada pembayaran di atas
                n.tgl_hapus_buku DESC,      -- terbaru dulu
                n.saldo_hapus_buku DESC,
                n.no_rekening
        ";

        try {
            $st = $this->pdo->prepare($sql);

            // bind core
            $st->bindValue(':created',       $created_date);
            $st->bindValue(':range_start',   $range_start);
            $st->bindValue(':range_end',     $range_end);
            $st->bindValue(':tx_start_date', $tx_start_date);
            $st->bindValue(':tx_end_date',   $tx_end_date);

            // bucket (jika dipakai)
            if ($useBucketFilter) {
                $st->bindValue(':bucket_year', $bucketYear, PDO::PARAM_INT);
            }

            // kantor (jika per-kantor)
            if (!$isKonsolidasi) {
                $st->bindValue(':kode_kantor_n', $kode_kantor);
                $st->bindValue(':kode_kantor_t', $kode_kantor);
            }

            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            $scope = $isKonsolidasi ? 'KONSOLIDASI' : "KANTOR {$kode_kantor}";
            $buck  = $useBucketFilter ? " | bucket {$bucketYear}" : "";
            sendResponse(200,
                "List PH (detail) tgl_hapus_buku {$range_start}–{$range_end} - {$scope}{$buck}; pembayaran > {$tx_start_date} s/d {$tx_end_date}",
                $rows
            );
        } catch (Throwable $e) {
            sendResponse(500, "Gagal ambil list PH (detail): ".$e->getMessage());
        }
    }

    public function getListPHLGDWoke($input = [])
    {
        $kode_kantor = trim($input['kode_kantor'] ?? '');
        $created     = trim($input['created'] ?? '');
        $bucket      = trim($input['bucket'] ?? '');           // opsional: '' atau 'YYYY'
        $rateInput   = isset($input['discount_rate']) ? floatval($input['discount_rate']) : 0.12; // default 12%

        if ($created === '') {
            sendResponse(422, "Parameter 'created' wajib diisi (format YYYY-MM-DD).", []);
            return;
        }

        // Normalisasi rate: 12 -> 0.12
        $r = ($rateInput > 1) ? $rateInput / 100.0 : $rateInput;
        if ($r < 0) $r = 0.0;

        // --- tanggal acuan ---
        $created_date = date('Y-m-d', strtotime($created));
        $createdYear  = (int)date('Y', strtotime($created_date));

        // range TANGGAL untuk tgl_hapus_buku: (YEAR(created)-5)-01-01 s/d created
        $range_start = sprintf('%04d-01-01', $createdYear - 5);
        $range_end   = $created_date;

        // window transaksi PH: > created s/d H-1 (dikunci bila perlu)
        $tx_start_date = date('Y-m-d', strtotime($created_date . ' +1 day'));
        $tx_end_date   = date('Y-m-d', strtotime('yesterday'));
        if (strtotime($tx_end_date) < strtotime($tx_start_date)) {
            $tx_end_date = $tx_start_date;
        }

        // bucket opsional (filter ke TAHUN tgl_hapus_buku)
        $useBucketFilter = false;
        $bucketYear = null;
        if ($bucket !== '') {
            if (!ctype_digit($bucket)) {
                sendResponse(422, "Bucket harus berupa tahun dalam rentang ".($createdYear-5)."–{$createdYear} atau kosong.", []);
                return;
            }
            $bucketYear = (int)$bucket;
            if ($bucketYear < ($createdYear - 5) || $bucketYear > $createdYear) {
                sendResponse(422, "Bucket di luar rentang ".($createdYear - 5)."–{$createdYear}.", []);
                return;
            }
            $useBucketFilter = true;
        }

        // konsolidasi vs per-kantor
        $isKonsolidasi = ($kode_kantor === '' || $kode_kantor === '000');
        $filterKantorN = $isKonsolidasi ? "" : " AND n.kode_kantor = :kode_kantor_n ";
        $filterKantorT = $isKonsolidasi ? "" : " AND t.kode_kantor = :kode_kantor_t ";
        $filterBucket  = $useBucketFilter ? " AND YEAR(n.tgl_hapus_buku) = :bucket_year " : "";

        $sql = "
            SELECT
                n.kode_kantor,
                kk.nama_kantor,
                n.no_rekening,
                n.cif,
                n.no_rekening_lama,
                n.nama_nasabah,
                n.alamat,
                n.desa,
                n.kecamatan,
                n.kabupaten_kota,
                n.tgl_hapus_buku,
                n.plafond,
                n.jml_hapus_buku,
                n.akumulasi_angsuran_sd_bulan_lalu,
                n.jml_angsuran_bulan_ini,
                n.saldo_hapus_buku,
                n.created,

                -- ringkasan pembayaran setelah created s/d H-1 (total)
                COALESCE(tp.total_pokok, 0)  AS bayar_pokok,
                COALESCE(tp.total_bunga, 0)  AS bayar_bunga,
                COALESCE(tp.total_total, 0)  AS bayar_total,
                tp.last_payment_date,

                -- recovery per tahun (berdasarkan POKOK)
                COALESCE(tp.rec_y1,0) AS rec_y1,
                COALESCE(tp.rec_y2,0) AS rec_y2,
                COALESCE(tp.rec_y3,0) AS rec_y3,
                COALESCE(tp.rec_y4,0) AS rec_y4,
                COALESCE(tp.rec_y5,0) AS rec_y5,

                -- NPV tail (tahun 2-5 didiskonto terhadap 'created'), tahun-1 = 100%
                (
                    COALESCE(tp.rec_y2,0) / (1 + :r1)
                + COALESCE(tp.rec_y3,0) / POW(1 + :r2, 2)
                + COALESCE(tp.rec_y4,0) / POW(1 + :r3, 3)
                + COALESCE(tp.rec_y5,0) / POW(1 + :r4, 4)
                ) AS npv_tail,

                -- recovery NPV = tahun-1 (full) + npv_tail
                (COALESCE(tp.rec_y1,0)
                + COALESCE(tp.rec_y2,0) / (1 + :r5)
                + COALESCE(tp.rec_y3,0) / POW(1 + :r6, 2)
                + COALESCE(tp.rec_y4,0) / POW(1 + :r7, 3)
                + COALESCE(tp.rec_y5,0) / POW(1 + :r8, 4)
                ) AS recovery_npv,

                -- RR NPV = recovery_npv / jml_hapus_buku
                (
                (COALESCE(tp.rec_y1,0)
                + COALESCE(tp.rec_y2,0) / (1 + :r9)
                + COALESCE(tp.rec_y3,0) / POW(1 + :r10, 2)
                + COALESCE(tp.rec_y4,0) / POW(1 + :r11, 3)
                + COALESCE(tp.rec_y5,0) / POW(1 + :r12, 4)
                ) / NULLIF(n.jml_hapus_buku,0)
                ) AS rr_npv,

                -- metrik non-diskonto (seperti sebelumnya)
                (n.saldo_hapus_buku - COALESCE(tp.total_pokok,0))                        AS sisa_saldo,
                (n.jml_hapus_buku   - (n.saldo_hapus_buku - COALESCE(tp.total_pokok,0))) AS recovery,
                ((n.jml_hapus_buku - (n.saldo_hapus_buku - COALESCE(tp.total_pokok,0))) / NULLIF(n.jml_hapus_buku,0)) AS rr,

                -- flags bantu
                CASE WHEN COALESCE(tp.total_total,0) > 0 THEN 1 ELSE 0 END AS has_payment,
                YEAR(n.tgl_hapus_buku) AS tahun_ph
            FROM nominatif_hapus_buku n
            JOIN kode_kantor kk ON kk.kode_kantor = n.kode_kantor
            LEFT JOIN (
                SELECT 
                    t.no_rekening,
                    SUM(t.pokok) AS total_pokok,
                    SUM(t.bunga) AS total_bunga,
                    SUM(t.total) AS total_total,
                    MAX(t.tanggal_transaksi) AS last_payment_date,

                    -- bucket hari relatif terhadap created
                    SUM(CASE WHEN DATEDIFF(t.tanggal_transaksi, :base_d) BETWEEN 1 AND 365  THEN t.pokok ELSE 0 END) AS rec_y1,
                    SUM(CASE WHEN DATEDIFF(t.tanggal_transaksi, :base_d) BETWEEN 366 AND 730 THEN t.pokok ELSE 0 END) AS rec_y2,
                    SUM(CASE WHEN DATEDIFF(t.tanggal_transaksi, :base_d) BETWEEN 731 AND 1095 THEN t.pokok ELSE 0 END) AS rec_y3,
                    SUM(CASE WHEN DATEDIFF(t.tanggal_transaksi, :base_d) BETWEEN 1096 AND 1460 THEN t.pokok ELSE 0 END) AS rec_y4,
                    SUM(CASE WHEN DATEDIFF(t.tanggal_transaksi, :base_d) BETWEEN 1461 AND 1825 THEN t.pokok ELSE 0 END) AS rec_y5

                FROM transaksi_ph t
                WHERE t.tanggal_transaksi >  :tx_start_date
                AND t.tanggal_transaksi <= :tx_end_date
                {$filterKantorT}
                GROUP BY t.no_rekening
            ) tp ON tp.no_rekening = n.no_rekening
            WHERE n.created = :created
            AND n.tgl_hapus_buku >= :range_start
            AND n.tgl_hapus_buku <= :range_end
            {$filterBucket}
            {$filterKantorN}
            ORDER BY
                has_payment DESC,
                n.tgl_hapus_buku DESC,
                n.saldo_hapus_buku DESC,
                n.no_rekening
        ";

        try {
            $st = $this->pdo->prepare($sql);

            // bind core
            $st->bindValue(':created',       $created_date);
            $st->bindValue(':range_start',   $range_start);
            $st->bindValue(':range_end',     $range_end);
            $st->bindValue(':tx_start_date', $tx_start_date);
            $st->bindValue(':tx_end_date',   $tx_end_date);

            // bind dasar untuk cut-off DATEDIFF
            $st->bindValue(':base_d', $created_date);

            // bind rate (pakai placeholder unik biar aman HY093)
            $st->bindValue(':r1',  $r);
            $st->bindValue(':r2',  $r);
            $st->bindValue(':r3',  $r);
            $st->bindValue(':r4',  $r);
            $st->bindValue(':r5',  $r);
            $st->bindValue(':r6',  $r);
            $st->bindValue(':r7',  $r);
            $st->bindValue(':r8',  $r);
            $st->bindValue(':r9',  $r);
            $st->bindValue(':r10', $r);
            $st->bindValue(':r11', $r);
            $st->bindValue(':r12', $r);

            // bucket (jika dipakai)
            if ($useBucketFilter) {
                $st->bindValue(':bucket_year', $bucketYear, PDO::PARAM_INT);
            }

            // kantor (jika per-kantor)
            if (!$isKonsolidasi) {
                $st->bindValue(':kode_kantor_n', $kode_kantor);
                $st->bindValue(':kode_kantor_t', $kode_kantor);
            }

            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            $scope = $isKonsolidasi ? 'KONSOLIDASI' : "KANTOR {$kode_kantor}";
            $buck  = $useBucketFilter ? " | bucket {$bucketYear}" : "";
            $ratePct = round($r * 100, 2) . '%';

            sendResponse(200,
                "List PH (detail) + NPV (tahun-1 full, tahun 2–5 diskonto @{$ratePct}); tgl_hapus_buku {$range_start}–{$range_end} - {$scope}{$buck}; pembayaran > {$tx_start_date} s/d {$tx_end_date}",
                $rows
            );
        } catch (Throwable $e) {
            sendResponse(500, "Gagal ambil list PH (detail + NPV): ".$e->getMessage());
        }
    }


















}
