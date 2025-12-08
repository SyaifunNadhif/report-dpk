<?php

require_once __DIR__ . '/../helpers/response.php';

class BucketController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
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

                /* ===== Sub-bucket detail ===== */
                SUM(CASE WHEN b.hm = 0                 THEN 1     ELSE 0 END) AS noa_0,
                SUM(CASE WHEN b.hm = 0                 THEN b.baki ELSE 0 END) AS baki_0,

                SUM(CASE WHEN b.hm BETWEEN 1   AND 30  THEN 1     ELSE 0 END) AS noa_1_30,
                SUM(CASE WHEN b.hm BETWEEN 1   AND 30  THEN b.baki ELSE 0 END) AS baki_1_30,

                SUM(CASE WHEN b.hm BETWEEN 31  AND 60  THEN 1     ELSE 0 END) AS noa_31_60,
                SUM(CASE WHEN b.hm BETWEEN 31  AND 60  THEN b.baki ELSE 0 END) AS baki_31_60,

                SUM(CASE WHEN b.hm BETWEEN 61  AND 90  THEN 1     ELSE 0 END) AS noa_61_90,
                SUM(CASE WHEN b.hm BETWEEN 61  AND 90  THEN b.baki ELSE 0 END) AS baki_61_90,

                SUM(CASE WHEN b.hm BETWEEN 91  AND 120 THEN 1     ELSE 0 END) AS noa_91_120,
                SUM(CASE WHEN b.hm BETWEEN 91  AND 120 THEN b.baki ELSE 0 END) AS baki_91_120,

                SUM(CASE WHEN b.hm BETWEEN 121 AND 150 THEN 1     ELSE 0 END) AS noa_121_150,
                SUM(CASE WHEN b.hm BETWEEN 121 AND 150 THEN b.baki ELSE 0 END) AS baki_121_150,

                SUM(CASE WHEN b.hm BETWEEN 151 AND 180 THEN 1     ELSE 0 END) AS noa_151_180,
                SUM(CASE WHEN b.hm BETWEEN 151 AND 180 THEN b.baki ELSE 0 END) AS baki_151_180,

                SUM(CASE WHEN b.hm BETWEEN 181 AND 210 THEN 1     ELSE 0 END) AS noa_181_210,
                SUM(CASE WHEN b.hm BETWEEN 181 AND 210 THEN b.baki ELSE 0 END) AS baki_181_210,

                SUM(CASE WHEN b.hm BETWEEN 211 AND 240 THEN 1     ELSE 0 END) AS noa_211_240,
                SUM(CASE WHEN b.hm BETWEEN 211 AND 240 THEN b.baki ELSE 0 END) AS baki_211_240,

                SUM(CASE WHEN b.hm BETWEEN 241 AND 270 THEN 1     ELSE 0 END) AS noa_241_270,
                SUM(CASE WHEN b.hm BETWEEN 241 AND 270 THEN b.baki ELSE 0 END) AS baki_241_270,

                SUM(CASE WHEN b.hm BETWEEN 271 AND 300 THEN 1     ELSE 0 END) AS noa_271_300,
                SUM(CASE WHEN b.hm BETWEEN 271 AND 300 THEN b.baki ELSE 0 END) AS baki_271_300,

                SUM(CASE WHEN b.hm BETWEEN 301 AND 330 THEN 1     ELSE 0 END) AS noa_301_330,
                SUM(CASE WHEN b.hm BETWEEN 301 AND 330 THEN b.baki ELSE 0 END) AS baki_301_330,

                SUM(CASE WHEN b.hm BETWEEN 331 AND 360 THEN 1     ELSE 0 END) AS noa_331_360,
                SUM(CASE WHEN b.hm BETWEEN 331 AND 360 THEN b.baki ELSE 0 END) AS baki_331_360,

                SUM(CASE WHEN b.hm > 360                 THEN 1   ELSE 0 END) AS noa_gt_360,
                SUM(CASE WHEN b.hm > 360                 THEN b.baki ELSE 0 END) AS baki_gt_360

            FROM base b
            JOIN kode_kantor k ON k.kode_kantor = b.kode_cabang
            WHERE k.kode_kantor <> '000' $filterCabang
            GROUP BY k.kode_kantor, k.nama_kantor
        ),
        final AS (
            SELECT
                a.*,

                /* ===== Agregat utama (kompatibel tampilan lama) ===== */
                (noa_0 + noa_1_30)                                      AS noa_0_30,
                (baki_0 + baki_1_30)                                    AS baki_0_30,

                (noa_31_60 + noa_61_90)                                 AS noa_31_90,
                (baki_31_60 + baki_61_90)                               AS baki_31_90,

                (noa_91_120 + noa_121_150 + noa_151_180)                AS noa_91_180,
                (baki_91_120 + baki_121_150 + baki_151_180)             AS baki_91_180,

                (noa_181_210 + noa_211_240 + noa_241_270
                 + noa_271_300 + noa_301_330 + noa_331_360)             AS noa_181_360,
                (baki_181_210 + baki_211_240 + baki_241_270
                 + baki_271_300 + baki_301_330 + baki_331_360)          AS baki_181_360,

                /* TOTAL */
                (noa_0 + noa_1_30 + noa_31_60 + noa_61_90 + noa_91_120 + noa_121_150 + noa_151_180
                 + noa_181_210 + noa_211_240 + noa_241_270 + noa_271_300 + noa_301_330 + noa_331_360
                 + noa_gt_360)                                          AS noa_total,

                (baki_0 + baki_1_30 + baki_31_60 + baki_61_90 + baki_91_120 + baki_121_150 + baki_151_180
                 + baki_181_210 + baki_211_240 + baki_241_270 + baki_271_300 + baki_301_330 + baki_331_360
                 + baki_gt_360)                                         AS baki_total
            FROM agg a
        )

        -- per cabang
        SELECT * FROM final

        UNION ALL

        -- total konsolidasi
        SELECT
            NULL AS kode_cabang,
            'TOTAL' AS nama_cabang,

            /* sub-bucket */
            SUM(noa_0), SUM(baki_0),
            SUM(noa_1_30), SUM(baki_1_30),
            SUM(noa_31_60), SUM(baki_31_60),
            SUM(noa_61_90), SUM(baki_61_90),
            SUM(noa_91_120), SUM(baki_91_120),
            SUM(noa_121_150), SUM(baki_121_150),
            SUM(noa_151_180), SUM(baki_151_180),
            SUM(noa_181_210), SUM(baki_181_210),
            SUM(noa_211_240), SUM(baki_211_240),
            SUM(noa_241_270), SUM(baki_241_270),
            SUM(noa_271_300), SUM(baki_271_300),
            SUM(noa_301_330), SUM(baki_301_330),
            SUM(noa_331_360), SUM(baki_331_360),
            SUM(noa_gt_360),  SUM(baki_gt_360),

            /* agregat & total */
            SUM(noa_0_30),   SUM(baki_0_30),
            SUM(noa_31_90),  SUM(baki_31_90),
            SUM(noa_91_180), SUM(baki_91_180),
            SUM(noa_181_360),SUM(baki_181_360),
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
        sendResponse(200, 'OK - Rekap bucket hari_menunggak (mekar)', $rows);
    } catch (Throwable $e) {
        sendResponse(500, 'Gagal ambil rekap: '.$e->getMessage(), null);
    }
}

public function getBucketDetail($input = [])
{
    // --- Gabungkan sumber input agar min/max pasti terbaca ---
    $raw = @file_get_contents('php://input');
    $fromJson = json_decode($raw, true);
    if (is_array($fromJson)) {
        // Prioritaskan JSON body dibanding default $input
        $input = array_merge($input, $fromJson);
    }
    // Ikut cek form/url jika ada
    $input = array_merge($_GET ?? [], $_POST ?? [], $input ?? []);

    // Helper ambil numerik atau null
    $numOrNull = function($v) {
        if ($v === null) return null;
        if (is_string($v)) {
            $v = trim($v);
            if ($v === '') return null;
            // dukung format seperti ">=361" atau "361+"
            $v = preg_replace('/[^0-9\-]/', '', $v);
        }
        return is_numeric($v) ? (int)$v : null;
    };

    // ===== 1) Param dasar =====
    $closing_date = !empty($input['closing_date'])
        ? date('Y-m-d', strtotime($input['closing_date']))
        : date('Y-m-d', strtotime('last day of previous month'));

    $kode_kantor = !empty($input['kode_kantor'])
        ? str_pad($input['kode_kantor'], 3, '0', STR_PAD_LEFT)
        : null;

    // ===== 2) Ambil min/max saja (tanpa bucket) =====
    $min = $numOrNull($input['min'] ?? $input['dpd_min'] ?? null);
    $max = $numOrNull($input['max'] ?? $input['dpd_max'] ?? null);

    if ($min !== null && $max !== null && $min > $max) {
        // tukar kalau kebalik
        $tmp = $min; $min = $max; $max = $tmp;
    }

    if ($min === null && $max === null) {
        // beri pesan yang jelas agar gampang ngecek
        sendResponse(400, 'Range tidak valid. Kirim setidaknya "min" atau "max". Contoh: {"min":361} atau {"min":1,"max":30}', null);
        return;
    }

    // Label info
    if ($min !== null && $max !== null)      $label = "DPD {$min}–{$max}";
    else if ($min !== null)                  $label = "DPD ≥ {$min}";
    else                                     $label = "DPD ≤ {$max}";

    // ===== 3) SQL range =====
    if ($min !== null && $max !== null) {
        $rangeSql = "b.hm BETWEEN :min AND :max";
        $bindMin = true; $bindMax = true;
    } elseif ($min !== null) {
        $rangeSql = "b.hm >= :min";
        $bindMin = true; $bindMax = false;
    } else {
        $rangeSql = "b.hm <= :max";
        $bindMin = false; $bindMax = true;
    }

    $filterCabang = $kode_kantor ? " AND k.kode_kantor = :kode_kantor " : "";

    // ===== 4) DETAIL =====
    $sqlDetail = "
        WITH base AS (
            SELECT
                n.kode_cabang,
                n.no_rekening,
                n.nama_nasabah,
                COALESCE(n.hari_menunggak,0) AS hm,
                COALESCE(n.baki_debet,0)     AS baki,
                n.tgl_jatuh_tempo
            FROM nominatif n
            WHERE n.created = :closing_date
        )
        SELECT
            k.kode_kantor AS kode_cabang,
            k.nama_kantor AS nama_cabang,
            b.no_rekening,
            b.nama_nasabah,
            b.hm          AS hari_menunggak,
            b.baki        AS baki_debet,
            b.tgl_jatuh_tempo
        FROM base b
        JOIN kode_kantor k ON k.kode_kantor = b.kode_cabang
        WHERE k.kode_kantor <> '000'
          AND {$rangeSql}
          {$filterCabang}
        ORDER BY b.hm DESC, b.baki DESC, b.no_rekening
    ";

    // ===== 5) RINGKAS =====
    $sqlSum = "
        WITH base AS (
            SELECT n.kode_cabang, COALESCE(n.hari_menunggak,0) AS hm, COALESCE(n.baki_debet,0) AS baki
            FROM nominatif n
            WHERE n.created = :closing_date
        )
        SELECT COUNT(*) AS noa, COALESCE(SUM(b.baki),0) AS baki
        FROM base b
        JOIN kode_kantor k ON k.kode_kantor = b.kode_cabang
        WHERE k.kode_kantor <> '000'
          AND {$rangeSql}
          {$filterCabang}
    ";

    try {
        // DETAIL
        $st = $this->pdo->prepare($sqlDetail);
        $st->bindValue(':closing_date', $closing_date);
        if ($bindMin) $st->bindValue(':min', (int)$min, PDO::PARAM_INT);
        if ($bindMax) $st->bindValue(':max', (int)$max, PDO::PARAM_INT);
        if ($kode_kantor) $st->bindValue(':kode_kantor', $kode_kantor);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // RINGKAS
        $sx = $this->pdo->prepare($sqlSum);
        $sx->bindValue(':closing_date', $closing_date);
        if ($bindMin) $sx->bindValue(':min', (int)$min, PDO::PARAM_INT);
        if ($bindMax) $sx->bindValue(':max', (int)$max, PDO::PARAM_INT);
        if ($kode_kantor) $sx->bindValue(':kode_kantor', $kode_kantor);
        $sx->execute();
        $sum = $sx->fetch(PDO::FETCH_ASSOC) ?: ['noa'=>0, 'baki'=>0];

        $meta = [
            'closing_date' => $closing_date,
            'kode_kantor'  => $kode_kantor,
            'range'        => ['min' => $min, 'max' => $max],
            'label'        => $label,
            'noa'          => (int)($sum['noa'] ?? 0),
            'baki_total'   => (string)($sum['baki'] ?? 0),
        ];

        sendResponse(200, "OK - Detail debitur {$label}", $rows, $meta);
    } catch (Throwable $e) {
        sendResponse(500, 'Gagal ambil detail debitur: '.$e->getMessage());
    }
}


public function getMappingAccountMyList($user, $input = null) {
  // ---- Ambil PIC dari token (full_name), fallback by id
  $pic = $user['full_name'] ?? null;
  if (!$pic && !empty($user['id'])) {
    $q = $this->pdo->prepare("SELECT full_name FROM users WHERE id=? LIMIT 1");
    $q->execute([$user['id']]);
    $pic = $q->fetchColumn();
  }
  if (!$pic) return sendResponse(400, "PIC login tidak ditemukan", null);

  // ---- Ambil body JSON (closing_date & harian_date optional)
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) {
    if (!$s) return null;
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
  };

  // Default: harian = kemarin; closing = EoM bulan sebelum harian
  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('-1 day'));
  $closing_date = $parseDate($body['closing_date'] ?? null) ?: date('Y-m-t', strtotime("$harian_date -1 month"));
  $start_month  = date('Y-m-01', strtotime($harian_date));   // transaksi 1..harian_date

  // ---- Query (gunakan placeholder berbeda)
  $sql = "
    SELECT
      a.no_rekening,
      a.nama_nasabah AS nama_debitur,
      a.alamat,
      a.baki_debet,
      DATE_FORMAT(a.tgl_realisasi, '%d') AS tgl_realisasi,

      a.tunggakan_pokok,
      a.tunggakan_bunga,
      a.ckpn,
      a.bucket,
      a.pipelane,
      a.plan_ckpn,
      a.pemulihan_pembentukan,

      nh.baki_debet     AS baki_debet_harian,
      nh.hari_menunggak AS hari_menunggak_harian,
      nh.kode_produk,
      nh.saldo_bank,

      COALESCE(tk.angsuran_pokok,0) AS angsuran_pokok,
      COALESCE(tk.angsuran_bunga,0) AS angsuran_bunga,
      DATE_FORMAT(tk.tgl_trans, '%d/%m/%y') AS tgl_trans
    FROM maping_account a
    LEFT JOIN nominatif nh
      ON nh.no_rekening = a.no_rekening
     AND nh.created     = :harian_date_nh
    LEFT JOIN (
      SELECT
        no_rekening,
        SUM(angsuran_pokok) AS angsuran_pokok,
        SUM(angsuran_bunga) AS angsuran_bunga,
        MAX(tgl_trans)      AS tgl_trans
      FROM transaksi_kredit
      WHERE tgl_trans BETWEEN :start_month AND :harian_date_trx
      GROUP BY no_rekening
    ) tk ON tk.no_rekening = a.no_rekening
    WHERE a.created = :closing_date
      AND a.pic     = :pic
    ORDER BY a.baki_debet DESC
  ";

  try {
    $st = $this->pdo->prepare($sql);
    $st->execute([
      ':harian_date_nh'  => $harian_date,  // untuk join nominatif
      ':harian_date_trx' => $harian_date,  // untuk subquery transaksi
      ':start_month'     => $start_month,
      ':closing_date'    => $closing_date,
      ':pic'             => trim($pic),
    ]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error: ".$e->getMessage(), null);
  }

  // ---- Pastikan numeric terkirim sebagai number
  foreach ($rows as &$r) {
    foreach ([
      'baki_debet','tunggakan_pokok','tunggakan_bunga',
      'baki_debet_harian','hari_menunggak_harian',
      'angsuran_pokok','angsuran_bunga','ckpn','plan_ckpn','pemulihan_pembentukan'
    ] as $k) {
      if (!array_key_exists($k,$r) || $r[$k] === null || $r[$k] === '') { $r[$k] = 0; continue; }
      $r[$k] = (strpos((string)$r[$k], '.') !== false) ? (float)$r[$k] : (int)$r[$k];
    }
  }
  unset($r);

  return sendResponse(200, "OK", $rows);
}

public function getMappingAccountMyList2($user, $input = null) {
  // ---- Ambil PIC dari token (full_name), fallback by id
  $pic = $user['full_name'] ?? null;
  if (!$pic && !empty($user['id'])) {
    $q = $this->pdo->prepare("SELECT full_name FROM users WHERE id=? LIMIT 1");
    $q->execute([$user['id']]);
    $pic = $q->fetchColumn();
  }
  if (!$pic) return sendResponse(400, "PIC login tidak ditemukan", null);

  // ---- Ambil body JSON (closing_date & harian_date optional)
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) {
    if (!$s) return null;
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
  };

  // Default: harian = kemarin; closing = EoM bulan sebelum harian
  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('-1 day'));
  $closing_date = $parseDate($body['closing_date'] ?? null) ?: date('Y-m-t', strtotime("$harian_date -1 month"));
  $start_month  = date('Y-m-01', strtotime($harian_date));   // transaksi 1..harian_date

  // ---- Query utama: mapping + nominatif H-1 + ringkasan transaksi bulan berjalan
  $sql = "
    SELECT
      a.no_rekening,
      a.nama_nasabah AS nama_debitur,
      a.alamat,
      a.baki_debet,
      DATE_FORMAT(a.tgl_realisasi, '%d') AS tgl_realisasi,

      a.tunggakan_pokok,
      a.tunggakan_bunga,
      a.ckpn,
      a.bucket,
      a.pipelane,
      a.plan_ckpn,
      a.pemulihan_pembentukan,

      nh.baki_debet     AS baki_debet_harian,
      nh.hari_menunggak AS hari_menunggak_harian,
      nh.kode_produk,
      nh.saldo_bank,
      nh.jml_pinjaman   AS plafon_harian,

      COALESCE(tk.angsuran_pokok,0) AS angsuran_pokok,
      COALESCE(tk.angsuran_bunga,0) AS angsuran_bunga,
      DATE_FORMAT(tk.tgl_trans, '%d/%m/%y') AS tgl_trans
    FROM maping_account a
    LEFT JOIN nominatif nh
      ON nh.no_rekening = a.no_rekening
     AND nh.created     = :harian_date_nh
    LEFT JOIN (
      SELECT
        no_rekening,
        SUM(angsuran_pokok) AS angsuran_pokok,
        SUM(angsuran_bunga) AS angsuran_bunga,
        MAX(tgl_trans)      AS tgl_trans
      FROM transaksi_kredit
      WHERE tgl_trans BETWEEN :start_month AND :harian_date_trx
      GROUP BY no_rekening
    ) tk ON tk.no_rekening = a.no_rekening
    WHERE a.created = :closing_date
      AND a.pic     = :pic
    ORDER BY a.baki_debet DESC
  ";

  try {
    $st = $this->pdo->prepare($sql);
    $st->execute([
      ':harian_date_nh'  => $harian_date,  // join nominatif
      ':harian_date_trx' => $harian_date,  // subquery transaksi
      ':start_month'     => $start_month,
      ':closing_date'    => $closing_date,
      ':pic'             => trim($pic),
    ]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error: ".$e->getMessage(), null);
  }

  // ---------- Prefetch referensi (bucket, PD, nom_restruk snapshot 2025-07-31, CKPN individual) ----------

  // 1) Bucket DPD (A..N)
  $buckets = [];
  $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
    $buckets[] = [
      'code' => $b['dpd_code'],
      'name' => $b['dpd_name'],
      'min'  => (int)$b['min_day'],
      'max'  => is_null($b['max_day']) ? null : (int)$b['max_day'],
    ];
  }

  // 2) PD per (produk × bucket)
  $pdMap = []; // $pdMap[product_code][dpd_code] = pd_percent
  $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
  }

  // 3) Snapshot restruk (asset baik) — hanya created = '2025-07-31'
  $restruk_snapshot_date = $closing_date; 
  $restrukSet = [];
  try {
    $stRes = $this->pdo->prepare("
        SELECT DISTINCT no_rekening
        FROM nom_restruk
        WHERE created = :snap_date
    ");
    $stRes->execute([':snap_date' => $restruk_snapshot_date]);
    $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
  } catch (PDOException $e) {
    // kalau tabel belum ada, anggap kosong (tidak error-kan response)
    $restrukSet = [];
  }

  // 4) CKPN individual terbaru (<= H-1). Jika tabel pakai created = H-1, tetap OK.
  $stInd = $this->pdo->prepare("
      SELECT ci.no_rekening, ci.nilai_ckpn
      FROM ckpn_individual ci
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM ckpn_individual
        WHERE created <= :harian_date
        GROUP BY no_rekening
      ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
  ");
  $stInd->execute([':harian_date' => $harian_date]);
  $indivMap = [];
  foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
  }

  // ---------- Hitung CKPN per baris ----------
  foreach ($rows as &$r) {
    // normalisasi angka (kolom yang ada di SELECT)
    foreach ([
      'baki_debet','tunggakan_pokok','tunggakan_bunga',
      'baki_debet_harian','hari_menunggak_harian',
      'angsuran_pokok','angsuran_bunga','ckpn','plan_ckpn','pemulihan_pembentukan',
      'saldo_bank','plafon_harian'
    ] as $k) {
      if (!array_key_exists($k,$r) || $r[$k] === null || $r[$k] === '') { $r[$k] = 0; continue; }
      $r[$k] = (strpos((string)$r[$k], '.') !== false) ? (float)$r[$k] : (int)$r[$k];
    }

    $noRek   = $r['no_rekening'];
    $prodStr = $r['kode_produk'];                 // bisa "120" string
    $prod    = ($prodStr === null || $prodStr === '') ? null : (int)$prodStr;
    $dpd     = (int)$r['hari_menunggak_harian'];
    $ead     = ($r['saldo_bank'] ?? 0) > 0 ? (float)$r['saldo_bank'] : (float)$r['baki_debet_harian'];

    // LGD KONSTAN (sementara): 59.48%
    $lgd = 59.48;

    // --- Map DPD -> bucket SELALU dilakukan (kecuali Lunas nanti di-override 'O') ---
    $dpdCode = null; $dpdName = null;
    foreach ($buckets as $b) {
      if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) {
        $dpdCode = $b['code'];
        $dpdName = $b['name'];
        break;
      }
    }

    // siapkan kolom output tambahan (default)
    $r['dpd_code']    = $dpdCode;
    $r['dpd_name']    = $dpdName;
    $r['pd_percent']  = 0.0;              // default PD=0; akan diisi kalau Kolektif
    $r['ead']         = (float)$ead;
    $r['lgd_percent'] = (float)$lgd;
    $r['ckpn_now']    = 0;
    $r['ckpn_rule']   = '';

    // 0) LUNAS (tidak ada baris nominatif H-1 → kode_produk null)
    if ($prod === null) {
      $r['dpd_code']   = 'O';
      $r['dpd_name']   = 'O_Lunas';
      $r['pd_percent'] = 0.0;
      $r['ckpn_now']   = 0;
      $r['ckpn_rule']  = 'LUNAS (tidak ada di nominatif H-1)';
      continue;
    }

    // 1) INDIVIDUAL: jika ada di ckpn_individual → PD=0, dpd_xx tetap terisi, CKPN dari tabel
    if (isset($indivMap[$noRek])) {
      $r['pd_percent'] = 0.0;
      $r['ckpn_now']   = (int)round($indivMap[$noRek]);
      $r['ckpn_rule']  = 'INDIVIDUAL (dari ckpn_individual)';
      continue;
    }

    // 2) ASSET BAIK: DPD ≤ 7 dan TIDAK ada di nom_restruk snapshot 2025-07-31 → PD=0, CKPN=0
    $isRestrukOnSnapshot = isset($restrukSet[$noRek]);
    if ($dpd <= 7 && !$isRestrukOnSnapshot) {
      $r['pd_percent'] = 0.0;
      $r['ckpn_now']   = 0;
      $r['ckpn_rule']  = 'ASSET BAIK ≤7D (tidak ada di nom_restruk 2025-07-31)';
      continue;
    }

    // 3) KOLEKTIF → PD dari pd_current
    $pd = 0.0;
    if ($prod !== null && $dpdCode !== null && isset($pdMap[$prod][$dpdCode])) {
      $pd = (float)$pdMap[$prod][$dpdCode];
    }
    $ckpn = round($ead * ($pd/100.0) * ($lgd/100.0));

    // set hasil kolektif
    $r['pd_percent'] = $pd;
    $r['ckpn_now']   = (int)$ckpn;
    $r['ckpn_rule']  = 'KOLEKTIF';
  }
  unset($r);

  return sendResponse(200, "OK", $rows);
}

public function getMappingAccountRekapPerCabang($user, $input = null) {
  // ---- PIC dari token
  $pic = $user['full_name'] ?? null;
  if (!$pic && !empty($user['id'])) {
    $q = $this->pdo->prepare("SELECT full_name FROM users WHERE id=? LIMIT 1");
    $q->execute([$user['id']]);
    $pic = $q->fetchColumn();
  }
  if (!$pic) return sendResponse(400, "PIC login tidak ditemukan", null);

  // ---- Param tanggal
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };

  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('-1 day'));
  $closing_date = $parseDate($body['closing_date'] ?? null) ?: date('Y-m-t', strtotime("$harian_date -1 month"));
  $start_month  = date('Y-m-01', strtotime($harian_date));

  // ---- Ambil mapping + nominatif H-1
  $sql = "
    SELECT
      a.no_rekening,
      a.kode_cabang,                -- pastikan kolom ini ada di maping_account
      a.nama_kantor,                -- ganti jika nama kolom berbeda
      a.nama_nasabah AS nama_debitur,
      a.alamat,
      a.baki_debet,
      DATE_FORMAT(a.tgl_realisasi, '%d') AS tgl_realisasi,

      a.tunggakan_pokok,
      a.tunggakan_bunga,
      a.ckpn,
      a.bucket,
      a.pipelane,
      a.plan_ckpn,
      a.pemulihan_pembentukan,

      nh.baki_debet     AS baki_debet_harian,
      nh.hari_menunggak AS hari_menunggak_harian,
      nh.kode_produk,
      nh.saldo_bank,
      nh.jml_pinjaman   AS plafon_harian

    FROM maping_account a
    LEFT JOIN nominatif nh
      ON nh.no_rekening = a.no_rekening
     AND nh.created     = :harian_date_nh
    WHERE a.created = :closing_date
      AND a.pic     = :pic
    ORDER BY a.baki_debet DESC
  ";

  try {
    $st = $this->pdo->prepare($sql);
    $st->execute([
      ':harian_date_nh'  => $harian_date,
      ':closing_date'    => $closing_date,
      ':pic'             => trim($pic),
    ]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error: ".$e->getMessage(), null);
  }

  // ---------- Prefetch referensi ----------
  // Bucket DPD
  $buckets = [];
  $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
    $buckets[] = ['code'=>$b['dpd_code'],'name'=>$b['dpd_name'],'min'=>(int)$b['min_day'],'max'=>is_null($b['max_day'])?null:(int)$b['max_day']];
  }
  // PD product x bucket
  $pdMap = [];
  $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
  }
  // Snapshot restruk (asset baik) — fixed 2025-07-31
  $restruk_snapshot_date = '2025-07-31';
  $restrukSet = [];
  try {
    $stRes = $this->pdo->prepare("SELECT DISTINCT no_rekening FROM nom_restruk WHERE created = :d");
    $stRes->execute([':d'=>$restruk_snapshot_date]);
    $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
  } catch (PDOException $e) { $restrukSet = []; }
  // CKPN individual (pakai record terbaru <= H-1)
  $stInd = $this->pdo->prepare("
      SELECT ci.no_rekening, ci.nilai_ckpn
      FROM ckpn_individual ci
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM ckpn_individual
        WHERE created <= :harian_date
        GROUP BY no_rekening
      ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
  ");
  $stInd->execute([':harian_date'=>$harian_date]);
  $indivMap = [];
  foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
  }

  // ---------- Hitung CKPN per baris (LGD konstan 59.48%) ----------
  foreach ($rows as &$r) {
    foreach (['baki_debet','tunggakan_pokok','tunggakan_bunga','baki_debet_harian','hari_menunggak_harian','ckpn','plan_ckpn','pemulihan_pembentukan','saldo_bank','plafon_harian'] as $k) {
      if (!array_key_exists($k,$r) || $r[$k] === null || $r[$k] === '') { $r[$k] = 0; continue; }
      $r[$k] = (strpos((string)$r[$k], '.') !== false) ? (float)$r[$k] : (int)$r[$k];
    }
    $noRek = $r['no_rekening'];
    $prod  = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
    $dpd   = (int)$r['hari_menunggak_harian'];
    $ead   = ($r['saldo_bank'] ?? 0) > 0 ? (float)$r['saldo_bank'] : (float)$r['baki_debet_harian'];
    $lgd   = 59.48;

    // Map DPD -> bucket (selalu), kecuali LUNAS nanti di-override O_Lunas
    $dpdCode = null; $dpdName = null;
    foreach ($buckets as $b) {
      if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) { $dpdCode=$b['code']; $dpdName=$b['name']; break; }
    }

    // Init output tambahan
    $r['dpd_code']   = $dpdCode;
    $r['dpd_name']   = $dpdName;
    $r['pd_percent'] = 0.0;
    $r['ead']        = (float)$ead;
    $r['lgd_percent']= (float)$lgd;
    $r['ckpn_now']   = 0;
    $r['ckpn_rule']  = '';

    // LUNAS: tidak ada nominatif H-1
    if ($prod === null) {
      $r['dpd_code']   = 'O';
      $r['dpd_name']   = 'O_Lunas';
      $r['pd_percent'] = 0.0;
      $r['ckpn_now']   = 0;
      $r['ckpn_rule']  = 'LUNAS (tidak ada di nominatif H-1)';
      continue;
    }

    // INDIVIDUAL: ada di ckpn_individual -> PD 0, CKPN dari tabel
    if (isset($indivMap[$noRek])) {
      $r['pd_percent'] = 0.0;
      $r['ckpn_now']   = (int)round($indivMap[$noRek]);
      $r['ckpn_rule']  = 'INDIVIDUAL (dari ckpn_individual)';
      continue;
    }

    // ASSET BAIK: DPD <= 7 dan tidak ada di nom_restruk snapshot -> PD 0, CKPN 0
    $isRestruk = isset($restrukSet[$noRek]);
    if ($dpd <= 7 && !$isRestruk) {
      $r['pd_percent'] = 0.0;
      $r['ckpn_now']   = 0;
      $r['ckpn_rule']  = 'ASSET BAIK ≤7D (tidak ada di nom_restruk 2025-07-31)';
      continue;
    }

    // KOLEKTIF: PD dari pd_current
    $pd = 0.0;
    if ($r['kode_produk'] !== '' && $dpdCode !== null) {
      $pc = (int)$r['kode_produk'];
      if (isset($pdMap[$pc][$dpdCode])) $pd = (float)$pdMap[$pc][$dpdCode];
    }
    $r['pd_percent'] = $pd;
    $r['ckpn_now']   = (int)round($ead * ($pd/100.0) * ($lgd/100.0));
    $r['ckpn_rule']  = 'KOLEKTIF';
  }
  unset($r);

  // ---------- REKAP PER CABANG ----------
  $rekap = [];  // by kode_cabang
  foreach ($rows as $r) {
    $kc = $r['kode_cabang'] ?? 'UNK';
    if (!isset($rekap[$kc])) {
      $rekap[$kc] = [
        'kode_cabang'     => $kc,
        'nama_kantor'     => $r['nama_kantor'] ?? $kc,
        'noa_map'         => 0,   // jumlah rekening yang dimapping (closing)
        'noa_harian'      => 0,   // ada di nominatif H-1
        'noa_lunas'       => 0,
        'noa_individual'  => 0,
        'noa_asset_baik'  => 0,
        'noa_kolektif'    => 0,
        'ead_sum'         => 0.0,
        'ckpn_sum'        => 0.0,
        'pd_wavg_percent' => 0.0, // (Σ EAD*PD) / Σ EAD  (dalam %)
        '_ead_x_pd'       => 0.0  // internal untuk hitung rata2 tertimbang
      ];
    }
    $rekap[$kc]['noa_map']++;

    $harianAda = ($r['kode_produk'] !== null && $r['kode_produk'] !== '');
    if ($harianAda) $rekap[$kc]['noa_harian']++;

    // hitung label rule
    $rule = $r['ckpn_rule'] ?? '';
    if (strpos($rule,'LUNAS') === 0)        $rekap[$kc]['noa_lunas']++;
    elseif (strpos($rule,'INDIVIDUAL')===0) $rekap[$kc]['noa_individual']++;
    elseif (strpos($rule,'ASSET BAIK')===0) $rekap[$kc]['noa_asset_baik']++;
    elseif ($rule === 'KOLEKTIF')           $rekap[$kc]['noa_kolektif']++;

    // agregat nilai
    $ead = (float)($r['ead'] ?? 0);
    $pd  = (float)($r['pd_percent'] ?? 0);
    $ck  = (float)($r['ckpn_now'] ?? 0);

    $rekap[$kc]['ead_sum']  += $ead;
    $rekap[$kc]['ckpn_sum'] += $ck;
    $rekap[$kc]['_ead_x_pd']+= $ead * $pd;  // PD masih dalam %, sesuai formula rata2 tertimbang
  }

  // finalize PD rata-rata tertimbang
  foreach ($rekap as &$v) {
    $v['pd_wavg_percent'] = $v['ead_sum'] > 0 ? round($v['_ead_x_pd'] / $v['ead_sum'], 2) : 0.00;
    unset($v['_ead_x_pd']);
    // pembulatan rupiah
    $v['ead_sum']  = (int)round($v['ead_sum']);
    $v['ckpn_sum'] = (int)round($v['ckpn_sum']);
  }
  unset($v);

  // Urutkan cabang by ckpn_sum DESC
  usort($rekap, function($a,$b){ return $b['ckpn_sum'] <=> $a['ckpn_sum']; });

  // return
  return sendResponse(200, "OK", [
    'harian_date' => $harian_date,
    'closing_date'=> $closing_date,
    'rekap'       => $rekap
  ]);
}

public function getRekapCkpnNominatif($input = null) {
  // ---- Ambil body JSON: hanya harian_date
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) {
    if (!$s) return null;
    $ts = strtotime($s);
    return $ts ? date('Y-m-d', $ts) : null;
  };

  // Default: harian_date = kemarin
  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('-1 day'));

  // ---- Ambil data nominatif (hanya yang di tanggal harian_date)
  $sql = "
    SELECT
      no_rekening,
      kode_produk,
      hari_menunggak,
      saldo_bank
    FROM nominatif
    WHERE created = :harian_date
  ";

  try {
    $st = $this->pdo->prepare($sql);
    $st->execute([':harian_date' => $harian_date]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error: ".$e->getMessage(), null);
  }

  // ---------- Prefetch referensi ----------
  // 1) Bucket DPD
  $buckets = [];
  $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
    $buckets[] = [
      'code' => $b['dpd_code'],
      'name' => $b['dpd_name'],
      'min'  => (int)$b['min_day'],
      'max'  => is_null($b['max_day']) ? null : (int)$b['max_day'],
    ];
  }

  // 2) PD per (produk × bucket)
  $pdMap = []; // $pdMap[product_code][dpd_code] = pd_percent
  $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
  }

  // 3) Snapshot restruk untuk aturan Asset Baik (fixed: 2025-07-31)
  $restruk_snapshot_date = '2025-07-31';
  $restrukSet = [];
  try {
    $stRes = $this->pdo->prepare("SELECT DISTINCT no_rekening FROM nom_restruk WHERE created = :d");
    $stRes->execute([':d' => $restruk_snapshot_date]);
    $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
  } catch (PDOException $e) {
    // bila tabel belum ada, anggap tidak ada rekening restruk pada snapshot tsb
    $restrukSet = [];
  }

  // 4) CKPN individual terbaru (<= harian_date)
  $stInd = $this->pdo->prepare("
      SELECT ci.no_rekening, ci.nilai_ckpn
      FROM ckpn_individual ci
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM ckpn_individual
        WHERE created <= :harian_date
        GROUP BY no_rekening
      ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
  ");
  $stInd->execute([':harian_date' => $harian_date]);
  $indivMap = [];
  foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
  }

  // ---------- Rekap inisialisasi ----------
  $rekap = [
    'harian_date'            => $harian_date,
    'noa_total'              => 0,
    'noa_individual'         => 0,
    'nilai_ckpn_individual'  => 0,
    'noa_asset_baik'         => 0,
    'noa_kolektif'           => 0,
    'nilai_ckpn_kolektif'    => 0,
    'nilai_ckpn_total'       => 0
  ];

  // LGD konstan
  $LGD = 59.48;

  // ---------- Proses baris nominatif ----------
  foreach ($rows as $r) {
    // normalisasi
    $noRek = $r['no_rekening'];
    $prod  = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
    $dpd   = isset($r['hari_menunggak']) ? (int)$r['hari_menunggak'] : 0;
    $ead   = isset($r['saldo_bank']) ? (float)$r['saldo_bank'] : 0.0;

    $rekap['noa_total']++;

    // 1) INDIVIDUAL → pakai nilai_ckpn dari tabel, PD dianggap 0 (tidak dihitung di rekap ini)
    if (isset($indivMap[$noRek])) {
      $ck = (float)$indivMap[$noRek];
      $rekap['noa_individual']++;
      $rekap['nilai_ckpn_individual'] += $ck;
      continue;
    }

    // 2) ASSET BAIK: DPD ≤ 7 dan TIDAK ada di nom_restruk snapshot 2025-07-31 → CKPN 0
    $isRestruk = isset($restrukSet[$noRek]);
    if ($dpd <= 7 && !$isRestruk) {
      $rekap['noa_asset_baik']++;
      // nilai CKPN 0; lanjut ke baris berikutnya
      continue;
    }

    // 3) KOLEKTIF
    // Map DPD -> bucket
    $dpdCode = null;
    foreach ($buckets as $b) {
      if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) {
        $dpdCode = $b['code'];
        break;
      }
    }
    // Ambil PD %
    $pd = 0.0;
    if ($prod !== null && $dpdCode !== null && isset($pdMap[$prod][$dpdCode])) {
      $pd = (float)$pdMap[$prod][$dpdCode];
    }
    // Hitung CKPN kolektif
    $ck = round($ead * ($pd/100.0) * ($LGD/100.0));

    $rekap['noa_kolektif']++;
    $rekap['nilai_ckpn_kolektif'] += $ck;
  }

  // Total CKPN
  $rekap['nilai_ckpn_individual'] = (int)round($rekap['nilai_ckpn_individual']);
  $rekap['nilai_ckpn_kolektif']   = (int)round($rekap['nilai_ckpn_kolektif']);
  $rekap['nilai_ckpn_total']      = (int)($rekap['nilai_ckpn_individual'] + $rekap['nilai_ckpn_kolektif']);

  return sendResponse(200, "OK", $rekap);
}

public function getRekapCkpnNominatifPerCabang($input = null) {
  // ---- Body: hanya harian_date
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };
  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('-1 day'));

  // ---- Ambil nominatif (snapshot harian_date)
  // Wajib punya kolom: no_rekening, kode_cabang, kode_produk, hari_menunggak, saldo_bank
  $sql = "
    SELECT
      no_rekening,
      kode_cabang,
      kode_produk,
      hari_menunggak,
      saldo_bank
    FROM nominatif
    WHERE created = :harian_date
  ";
  try {
    $st = $this->pdo->prepare($sql);
    $st->execute([':harian_date' => $harian_date]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error: ".$e->getMessage(), null);
  }

  // ---- Prefetch master kantor (join nama_kantor)
  $kantorMap = []; // kode_kantor => nama_kantor
  try {
    $q = $this->pdo->query("SELECT kode_kantor, nama_kantor FROM kode_kantor");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $m) {
      $kode = str_pad((string)$m['kode_kantor'], 3, '0', STR_PAD_LEFT);
      $kantorMap[$kode] = $m['nama_kantor'];
    }
  } catch (PDOException $e) {
    // kalau tabel master tidak ada, lanjut tanpa nama_kantor
    $kantorMap = [];
  }

  // ---- Prefetch referensi lain
  // 1) Bucket DPD
  $buckets = [];
  $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
    $buckets[] = ['code'=>$b['dpd_code'],'name'=>$b['dpd_name'],'min'=>(int)$b['min_day'],'max'=>is_null($b['max_day'])?null:(int)$b['max_day']];
  }
  // 2) PD product × bucket
  $pdMap = [];
  $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
  foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
  }
  // 3) Snapshot restruk (asset baik) — fixed 2025-07-31
  $restruk_snapshot_date = '2025-07-31';
  $restrukSet = [];
  try {
    $stRes = $this->pdo->prepare("SELECT DISTINCT no_rekening FROM nom_restruk WHERE created = :d");
    $stRes->execute([':d' => $restruk_snapshot_date]);
    $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
  } catch (PDOException $e) { $restrukSet = []; }
  // 4) CKPN individual terbaru (<= harian_date)
  $stInd = $this->pdo->prepare("
      SELECT ci.no_rekening, ci.nilai_ckpn
      FROM ckpn_individual ci
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM ckpn_individual
        WHERE created <= :harian_date
        GROUP BY no_rekening
      ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
  ");
  $stInd->execute([':harian_date' => $harian_date]);
  $indivMap = [];
  foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
  }

  // ---- Rekap per cabang
  $LGD = 59.48; // persen, konstan
  $rekap = [];  // keyed by kode_cabang (001..028), exclude 000

  foreach ($rows as $r) {
    $noRek = $r['no_rekening'];
    $kcRaw = $r['kode_cabang'];
    $kc    = str_pad((string)$kcRaw, 3, '0', STR_PAD_LEFT);
    if ($kc === '000') continue; // exclude 000

    $prod  = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
    $dpd   = isset($r['hari_menunggak']) ? (int)$r['hari_menunggak'] : 0;
    $ead   = isset($r['saldo_bank']) ? (float)$r['saldo_bank'] : 0.0;

    if (!isset($rekap[$kc])) {
      $rekap[$kc] = [
        'kode_cabang'            => $kc,
        'nama_kantor'            => $kantorMap[$kc] ?? null,
        'noa_total'              => 0,
        'noa_individual'         => 0,
        'nilai_ckpn_individual'  => 0,
        'noa_asset_baik'         => 0,
        'noa_kolektif'           => 0,
        'nilai_ckpn_kolektif'    => 0,
        'nilai_ckpn_total'       => 0
      ];
    }
    $rekap[$kc]['noa_total']++;

    // 1) INDIVIDUAL
    if (isset($indivMap[$noRek])) {
      $ck = (float)$indivMap[$noRek];
      $rekap[$kc]['noa_individual']++;
      $rekap[$kc]['nilai_ckpn_individual'] += $ck;
      continue;
    }

    // 2) ASSET BAIK (≤7 hari & tidak ada di nom_restruk snapshot)
    $isRestruk = isset($restrukSet[$noRek]);
    if ($dpd <= 7 && !$isRestruk) {
      $rekap[$kc]['noa_asset_baik']++;
      continue; // CKPN = 0
    }

    // 3) KOLEKTIF
    // Map DPD → bucket
    $dpdCode = null;
    foreach ($buckets as $b) {
      if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) { $dpdCode = $b['code']; break; }
    }
    // Ambil PD %
    $pd = 0.0;
    if ($prod !== null && $dpdCode !== null && isset($pdMap[$prod][$dpdCode])) {
      $pd = (float)$pdMap[$prod][$dpdCode];
    }
    // Hitung CKPN
    $ck = round($ead * ($pd/100.0) * ($LGD/100.0));

    $rekap[$kc]['noa_kolektif']++;
    $rekap[$kc]['nilai_ckpn_kolektif'] += $ck;
  }

  // ---- Finalisasi & urutkan 001..028
  foreach ($rekap as &$v) {
    $v['nilai_ckpn_individual'] = (int)round($v['nilai_ckpn_individual']);
    $v['nilai_ckpn_kolektif']   = (int)round($v['nilai_ckpn_kolektif']);
    $v['nilai_ckpn_total']      = (int)($v['nilai_ckpn_individual'] + $v['nilai_ckpn_kolektif']);
  }
  unset($v);

  // Susun urutan 001..028
  $ordered = [];
  for ($i=1; $i<=28; $i++) {
    $code = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
    if (isset($rekap[$code])) $ordered[] = $rekap[$code];
  }

  return sendResponse(200, "OK", [
    'harian_date' => $harian_date,
    'rekap'       => $ordered
  ]);
}





































}