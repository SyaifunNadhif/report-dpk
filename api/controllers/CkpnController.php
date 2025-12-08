<?php

require_once __DIR__ . '/../helpers/response.php';

class CkpnController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
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


public function getRekapCkpnPerCabang($input = null) {
  // ---------- 1) Parse input ----------
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) { if (!$s) return null; $t = strtotime($s); return $t ? date('Y-m-d',$t) : null; };
  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('last day of previous month'));
  $force = strtolower(trim($body['source'] ?? '')); // 'snapshot' | 'compute' | ''

  // ---------- Master kantor ----------
  $kantorMap = $this->loadKantorMap(); // ['001'=>'Kc. Utama', ...]

  // ---------- Helper total & akumulasi ----------
  $makeTotalRow = function () {
    return [
      'kode_cabang'           => null,
      'nama_kantor'           => 'TOTAL KONSOLIDASI',
      'noa_total'             => 0,
      'noa_individual'        => 0,
      'nilai_ckpn_individual' => 0,
      'noa_asset_baik'        => 0,
      'noa_kolektif'          => 0,
      'nilai_ckpn_kolektif'   => 0,
      'nilai_ckpn_total'      => 0,
    ];
  };
  $accumulate = function (&$total, $row) {
    $total['noa_total']             += (int)$row['noa_total'];
    $total['noa_individual']        += (int)$row['noa_individual'];
    $total['nilai_ckpn_individual'] += (int)$row['nilai_ckpn_individual'];
    $total['noa_asset_baik']        += (int)$row['noa_asset_baik'];
    $total['noa_kolektif']          += (int)$row['noa_kolektif'];
    $total['nilai_ckpn_kolektif']   += (int)$row['nilai_ckpn_kolektif'];
    $total['nilai_ckpn_total']      += (int)$row['nilai_ckpn_total'];
  };

  // =====================================================================
  // 2) Cek SNAPSHOT (nominatif_ckpn) kecuali dipaksa compute
  // =====================================================================
  $hasSnapshot = false;
  if ($force !== 'compute') {
    try {
      $st = $this->pdo->prepare("SELECT COUNT(1) FROM nominatif_ckpn WHERE created = :d");
      $st->execute([':d' => $harian_date]);
      $hasSnapshot = ((int)$st->fetchColumn() > 0);
      if ($force === 'snapshot' && !$hasSnapshot) {
        return sendResponse(404, "Snapshot nominatif_ckpn untuk {$harian_date} tidak ditemukan.", null);
      }
    } catch (PDOException $e) {
      return sendResponse(500, "DB Error (cek snapshot): ".$e->getMessage(), null);
    }
  }

  if ($hasSnapshot) {
    // ---------------- JALUR SNAPSHOT ----------------
    try {
      $sql = "
        SELECT
          kode_cabang AS kc,
          COUNT(*) AS noa_total,

          -- robust: INDIVIDUAL/INDIVIDU (prefix INDIV)
          SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),5)='INDIV' THEN 1 ELSE 0 END) AS noa_individual,
          SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),5)='INDIV' THEN COALESCE(nilai_ckpn,0) ELSE 0 END) AS ckpn_individual,

          -- robust: COLLECTIVE/KOLEKTIF + ASET BAIK / ASSET BAIK
          SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),4) IN ('COLL','KOLE')
                    AND UPPER(TRIM(COALESCE(keterangan,''))) IN ('ASET BAIK','ASSET BAIK')
                   THEN 1 ELSE 0 END) AS noa_asset_baik,

          SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),4) IN ('COLL','KOLE')
                    AND UPPER(TRIM(COALESCE(keterangan,''))) NOT IN ('ASET BAIK','ASSET BAIK')
                   THEN 1 ELSE 0 END) AS noa_kolektif,

          SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),4) IN ('COLL','KOLE')
                   THEN COALESCE(nilai_ckpn,0) ELSE 0 END) AS ckpn_kolektif
        FROM nominatif_ckpn
        WHERE created = :d
          AND kode_cabang <> '000'
        GROUP BY kode_cabang
      ";
      $st = $this->pdo->prepare($sql);
      $st->execute([':d' => $harian_date]);

      $byCab = [];
      foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $kc = str_pad((string)$r['kc'], 3, '0', STR_PAD_LEFT);
        $row = [
          'kode_cabang'           => $kc,
          'nama_kantor'           => $kantorMap[$kc] ?? null,
          'noa_total'             => (int)$r['noa_total'],
          'noa_individual'        => (int)$r['noa_individual'],
          'nilai_ckpn_individual' => (int)round($r['ckpn_individual']),
          'noa_asset_baik'        => (int)$r['noa_asset_baik'],
          'noa_kolektif'          => (int)$r['noa_kolektif'],
          'nilai_ckpn_kolektif'   => (int)round($r['ckpn_kolektif']),
        ];
        $row['nilai_ckpn_total'] = $row['nilai_ckpn_individual'] + $row['nilai_ckpn_kolektif'];
        $byCab[$kc] = $row;
      }

      // Urut 001..028 + total di atas
      $ordered = [];
      $total = $makeTotalRow();
      for ($i=1; $i<=28; $i++) {
        $code = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
        if (!isset($byCab[$code])) continue;
        $row = $byCab[$code];
        $accumulate($total, $row);
        $ordered[] = $row;
      }
      array_unshift($ordered, $total);

      return sendResponse(200, "OK (snapshot)", $ordered);
    } catch (PDOException $e) {
      return sendResponse(500, "DB Error (snapshot): ".$e->getMessage(), null);
    }
  }

  // =====================================================================
  // 3) JALUR COMPUTE – hitung dari tabel nominatif + referensi
  // =====================================================================

  // --- Ambil nominatif snapshot ---
  try {
    $sqlNom = "
      SELECT
        no_rekening,
        kode_cabang,
        kode_produk,
        hari_menunggak,
        saldo_bank
      FROM nominatif
      WHERE created = :d
    ";
    $st = $this->pdo->prepare($sqlNom);
    $st->execute([':d' => $harian_date]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error (nominatif): ".$e->getMessage(), null);
  }

  // --- Bucket DPD ---
  $buckets = [];
  try {
    $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
      $buckets[] = [
        'code' => $b['dpd_code'],
        'name' => $b['dpd_name'],
        'min'  => (int)$b['min_day'],
        'max'  => is_null($b['max_day']) ? null : (int)$b['max_day'],
      ];
    }
  } catch (PDOException $e) {
    // biarkan kosong (pdMap jd 0 semua nanti aman)
  }

  // --- PD product × bucket (versi efektif bila kolom created ada) ---
  $pdMap = [];
  try {
    // coba query versi (punya kolom created)
    $stPd = $this->pdo->prepare("
      SELECT p.product_code, p.dpd_code, p.pd_percent
      FROM pd_current p
      JOIN (
        SELECT product_code, dpd_code, MAX(created) AS created
        FROM pd_current
        WHERE created <= :d
        GROUP BY product_code, dpd_code
      ) x ON x.product_code = p.product_code
         AND x.dpd_code     = p.dpd_code
         AND x.created      = p.created
    ");
    $stPd->execute([':d' => $harian_date]);
    foreach ($stPd->fetchAll(PDO::FETCH_ASSOC) as $p) {
      $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
    }
  } catch (PDOException $e) {
    // fallback: tabel PD lama (tanpa versi)
    try {
      $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
      foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)str_replace(',', '.', (string)$p['pd_percent']);
      }
    } catch (PDOException $e2) {}
  }

  // --- RESTRUK terbaru <= tanggal ---
  $restrukSet = [];
  try {
    $stRes = $this->pdo->prepare("
      SELECT nr.no_rekening
      FROM nom_restruk nr
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM nom_restruk
        WHERE created <= :d
        GROUP BY no_rekening
      ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
    ");
    $stRes->execute([':d' => $harian_date]);
    $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC), 'no_rekening'), true);
  } catch (PDOException $e) {}

  // --- CKPN individual terbaru <= tanggal ---
  $indivMap = [];
  try {
    $stInd = $this->pdo->prepare("
      SELECT ci.no_rekening, ci.nilai_ckpn
      FROM ckpn_individual ci
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM ckpn_individual
        WHERE created <= :d
        GROUP BY no_rekening
      ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
    ");
    $stInd->execute([':d' => $harian_date]);
    foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
    }
  } catch (PDOException $e) {}

  // --- LGD GLOBAL versi tanggal ---
  $LGD = $this->loadGlobalLGD($harian_date); // pakai lgd_current (tanpa product_code)

  // --- Rekap per cabang ---
  $rekap = [];

  foreach ($rows as $r) {
    $noRek = $r['no_rekening'];
    $kc    = str_pad((string)$r['kode_cabang'], 3, '0', STR_PAD_LEFT);
    if ($kc === '000') continue;

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

    // Individual?
    if (isset($indivMap[$noRek])) {
      $rekap[$kc]['noa_individual']++;
      $rekap[$kc]['nilai_ckpn_individual'] += (int)round((float)$indivMap[$noRek]);
      continue;
    }

    // Asset baik? (≤7 hari & bukan restruk)
    $isRestruk = isset($restrukSet[$noRek]);
    if ($dpd <= 7 && !$isRestruk) {
      $rekap[$kc]['noa_asset_baik']++;
      continue; // ckpn = 0
    }

    // Kolektif (PD×LGD×EAD)
    $dpdCode = null;
    foreach ($buckets as $b) {
      if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) { $dpdCode = $b['code']; break; }
    }
    $pd = 0.0;
    if ($prod !== null && $dpdCode !== null && isset($pdMap[$prod][$dpdCode])) {
      $pd = (float)$pdMap[$prod][$dpdCode];
    }
    $ck = (int)round($ead * ($pd/100.0) * ($LGD/100.0));
    $rekap[$kc]['noa_kolektif']++;
    $rekap[$kc]['nilai_ckpn_kolektif'] += $ck;
  }

  // --- Susun 001..028 + total ---
  $ordered = [];
  $total = $makeTotalRow();
  for ($i=1; $i<=28; $i++) {
    $code = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
    if (!isset($rekap[$code])) continue;

    $rekap[$code]['nilai_ckpn_total'] =
      (int)$rekap[$code]['nilai_ckpn_individual'] + (int)$rekap[$code]['nilai_ckpn_kolektif'];

    $accumulate($total, $rekap[$code]);
    $ordered[] = $rekap[$code];
  }
  array_unshift($ordered, $total);

  return sendResponse(200, "OK (compute)", $ordered);
}

// public function getRekapCkpnPerCabang($input = null) {
//   // ---------- Parse input ----------
//   $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
//   $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };

//   $harian_date  = $parseDate($body['harian_date']  ?? null) ?: date('Y-m-d', strtotime('last day of previous month'));
//   // default closing = last day of month sebelum HARlAN date
//   $closing_date = $parseDate($body['closing_date'] ?? null) ?: date('Y-m-t', strtotime($harian_date.' -1 month'));

//   $force = strtolower(trim($body['source'] ?? '')); // '', 'snapshot', 'compute'

//   // ---------- Master kantor ----------
//   $kantorMap = $this->loadKantorMap(); // ['001'=>'KC A', ...]

//   // ---------- Helpers ----------
//   $makeEmptyRow = function (string $kc) use ($kantorMap) {
//     return [
//       'kode_cabang'           => $kc,
//       'nama_kantor'           => $kantorMap[$kc] ?? null,
//       'noa_total'             => 0,
//       'noa_individual'        => 0,
//       'nilai_ckpn_individual' => 0,
//       'noa_asset_baik'        => 0,
//       'noa_kolektif'          => 0,
//       'nilai_ckpn_kolektif'   => 0,
//       'nilai_ckpn_total'      => 0,
//     ];
//   };
//   $sumInto = function (&$dst, $src) {
//     $dst['noa_total']             += (int)$src['noa_total'];
//     $dst['noa_individual']        += (int)$src['noa_individual'];
//     $dst['nilai_ckpn_individual'] += (int)$src['nilai_ckpn_individual'];
//     $dst['noa_asset_baik']        += (int)$src['noa_asset_baik'];
//     $dst['noa_kolektif']          += (int)$src['noa_kolektif'];
//     $dst['nilai_ckpn_kolektif']   += (int)$src['nilai_ckpn_kolektif'];
//     $dst['nilai_ckpn_total']      += (int)$src['nilai_ckpn_total'];
//   };
//   $dayRange = function (string $d): array {
//     $ds = $d . " 00:00:00";
//     $de = date('Y-m-d', strtotime($d.' +1 day')) . " 00:00:00";
//     return [$ds, $de];
//   };
//   $hasSnapshot = function (string $d) use ($dayRange) {
//     try {
//       [$ds,$de] = $dayRange($d);
//       $st = $this->pdo->prepare("SELECT COUNT(1) FROM nominatif_ckpn WHERE created >= :ds AND created < :de");
//       $st->execute([':ds'=>$ds, ':de'=>$de]);
//       return ((int)$st->fetchColumn() > 0);
//     } catch (PDOException $e) { return false; }
//   };

//   // ---------- Core: ambil rekap per cabang untuk 1 tanggal (snapshot-first) ----------
//   $rekapForDate = function (string $d) use ($force, $dayRange, $hasSnapshot, $kantorMap, $makeEmptyRow) {
//     $byCab = []; // kc => row

//     // decide path
//     $snapAvailable = $hasSnapshot($d);
//     if ($force === 'snapshot' && !$snapAvailable) {
//       return ['error' => "Snapshot nominatif_ckpn untuk {$d} tidak ditemukan."];
//     }
//     $useSnapshot = ($force !== 'compute') && $snapAvailable;

//     if ($useSnapshot) {
//       // ---------- JALUR SNAPSHOT ----------
//       try {
//         [$ds, $de] = $dayRange($d);
//         $sql = "
//           SELECT
//             LPAD(CAST(kode_cabang AS CHAR),3,'0') AS kc,
//             COUNT(*) AS noa_total,

//             SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),5)='INDIV' THEN 1 ELSE 0 END) AS noa_individual,
//             SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),5)='INDIV' THEN COALESCE(nilai_ckpn,0) ELSE 0 END) AS ckpn_individual,

//             SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),4) IN ('COLL','KOLE')
//                       AND UPPER(TRIM(COALESCE(keterangan,''))) IN ('ASET BAIK','ASSET BAIK')
//                      THEN 1 ELSE 0 END) AS noa_asset_baik,

//             SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),4) IN ('COLL','KOLE')
//                       AND UPPER(TRIM(COALESCE(keterangan,''))) NOT IN ('ASET BAIK','ASSET BAIK')
//                      THEN 1 ELSE 0 END) AS noa_kolektif,

//             SUM(CASE WHEN LEFT(UPPER(TRIM(metode_penghitungan)),4) IN ('COLL','KOLE')
//                      THEN COALESCE(nilai_ckpn,0) ELSE 0 END) AS ckpn_kolektif
//           FROM nominatif_ckpn
//           WHERE created >= :ds AND created < :de
//             AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'
//           GROUP BY LPAD(CAST(kode_cabang AS CHAR),3,'0')
//         ";
//         $st = $this->pdo->prepare($sql);
//         $st->execute([':ds'=>$ds, ':de'=>$de]);
//         foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
//           $kc = $r['kc'];
//           $row = [
//             'kode_cabang'           => $kc,
//             'nama_kantor'           => $kantorMap[$kc] ?? null,
//             'noa_total'             => (int)$r['noa_total'],
//             'noa_individual'        => (int)$r['noa_individual'],
//             'nilai_ckpn_individual' => (int)round($r['ckpn_individual']),
//             'noa_asset_baik'        => (int)$r['noa_asset_baik'],
//             'noa_kolektif'          => (int)$r['noa_kolektif'],
//             'nilai_ckpn_kolektif'   => (int)round($r['ckpn_kolektif']),
//             'nilai_ckpn_total'      => 0,
//           ];
//           $row['nilai_ckpn_total'] = $row['nilai_ckpn_individual'] + $row['nilai_ckpn_kolektif'];
//           $byCab[$kc] = $row;
//         }
//         return ['rows'=>$byCab, 'source'=>'snapshot'];
//       } catch (PDOException $e) {
//         return ['error' => "DB Error (snapshot): ".$e->getMessage()];
//       }
//     }

//     // ---------- JALUR COMPUTE ----------
//     try {
//       // ref bucket
//       $buckets = [];
//       $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
//       foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
//         $buckets[] = ['code'=>$b['dpd_code'],'min'=>(int)$b['min_day'],'max'=>is_null($b['max_day'])?null:(int)$b['max_day']];
//       }

//       // PD versi tanggal (punya kolom created → pick MAX(created)<=d, else fallback)
//       $pdMap = [];
//       try {
//         $stPd = $this->pdo->prepare("
//           SELECT p.product_code, p.dpd_code, p.pd_percent
//           FROM pd_current p
//           JOIN (
//             SELECT product_code, dpd_code, MAX(created) AS created
//             FROM pd_current
//             WHERE created <= :d
//             GROUP BY product_code, dpd_code
//           ) x ON x.product_code=p.product_code AND x.dpd_code=p.dpd_code AND x.created=p.created
//         ");
//         $stPd->execute([':d'=>$d]);
//         foreach ($stPd->fetchAll(PDO::FETCH_ASSOC) as $p) {
//           $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
//         }
//         if (empty($pdMap)) throw new Exception('pd empty');
//       } catch (\Throwable $e) {
//         $qpd = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
//         foreach ($qpd->fetchAll(PDO::FETCH_ASSOC) as $p) {
//           $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)str_replace(',', '.', (string)$p['pd_percent']);
//         }
//       }

//       // RESTRUK ≤ d
//       $restrukSet = [];
//       try {
//         $stRes = $this->pdo->prepare("
//           SELECT nr.no_rekening
//           FROM nom_restruk nr
//           JOIN (
//             SELECT no_rekening, MAX(created) AS created
//             FROM nom_restruk
//             WHERE created <= :d
//             GROUP BY no_rekening
//           ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
//         ");
//         $stRes->execute([':d' => $d]);
//         $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC), 'no_rekening'), true);
//       } catch (PDOException $e) { $restrukSet = []; }

//       // INDIV ≤ d
//       $indivMap = [];
//       try {
//         $stInd = $this->pdo->prepare("
//           SELECT ci.no_rekening, ci.nilai_ckpn
//           FROM ckpn_individual ci
//           JOIN (
//             SELECT no_rekening, MAX(created) AS created
//             FROM ckpn_individual
//             WHERE created <= :d
//             GROUP BY no_rekening
//           ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
//         ");
//         $stInd->execute([':d' => $d]);
//         foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
//           $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
//         }
//       } catch (PDOException $e) {}

//       // LGD global versi tanggal
//       $LGD = $this->loadGlobalLGD($d);

//       // nominatif snapshot (DATETIME-safe)
//       [$ds, $de] = $dayRange($d);
//       $stNom = $this->pdo->prepare("
//         SELECT no_rekening, LPAD(CAST(kode_cabang AS CHAR),3,'0') AS kc, kode_produk, hari_menunggak, saldo_bank
//         FROM nominatif
//         WHERE created >= :ds AND created < :de
//           AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'
//       ");
//       $stNom->execute([':ds'=>$ds, ':de'=>$de]);
//       $rows = $stNom->fetchAll(PDO::FETCH_ASSOC);

//       // hitung
//       foreach ($rows as $r) {
//         $kc = $r['kc'];
//         if (!isset($byCab[$kc])) $byCab[$kc] = $makeEmptyRow($kc);

//         $byCab[$kc]['noa_total']++;

//         $noRek = $r['no_rekening'];
//         if (isset($indivMap[$noRek])) {
//           $byCab[$kc]['noa_individual']++;
//           $byCab[$kc]['nilai_ckpn_individual'] += (int)round((float)$indivMap[$noRek]);
//           continue;
//         }

//         $dpd  = (int)($r['hari_menunggak'] ?? 0);
//         $prod = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
//         $ead  = (float)($r['saldo_bank'] ?? 0.0);

//         $isRestruk = isset($restrukSet[$noRek]);
//         if ($dpd <= 7 && !$isRestruk) {
//           $byCab[$kc]['noa_asset_baik']++;
//           continue;
//         }

//         // cari dpd_code
//         $dpdCode = null;
//         foreach ($buckets as $b) {
//           if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) { $dpdCode = $b['code']; break; }
//         }
//         $pd = ($prod !== null && $dpdCode !== null && isset($pdMap[$prod][$dpdCode])) ? (float)$pdMap[$prod][$dpdCode] : 0.0;

//         $ck = (int)round($ead * ($pd/100.0) * ($LGD/100.0));
//         $byCab[$kc]['noa_kolektif']++;
//         $byCab[$kc]['nilai_ckpn_kolektif'] += $ck;
//       }

//       // finalize total per kc
//       foreach ($byCab as &$row) {
//         $row['nilai_ckpn_total'] = (int)$row['nilai_ckpn_individual'] + (int)$row['nilai_ckpn_kolektif'];
//       } unset($row);

//       return ['rows'=>$byCab, 'source'=>'compute'];
//     } catch (PDOException $e) {
//       return ['error' => "DB Error (compute): ".$e->getMessage()];
//     }
//   };

//   // ---------- Ambil rekap M-1 & CUR ----------
//   $m1Res  = $rekapForDate($closing_date);
//   if (isset($m1Res['error']))  return sendResponse(500, $m1Res['error'], null);
//   $curRes = $rekapForDate($harian_date);
//   if (isset($curRes['error'])) return sendResponse(500, $curRes['error'], null);

//   $m1Rows  = $m1Res['rows']  ?? [];
//   $curRows = $curRes['rows'] ?? [];

//   // ---------- Susun output per cabang: 001..028 ----------
//   $rowsOut = [];
//   $grand_m1  = $makeEmptyRow(null); $grand_m1['nama_kantor'] = 'TOTAL M-1';
//   $grand_cur = $makeEmptyRow(null); $grand_cur['nama_kantor'] = 'TOTAL CUR';
//   $grand_inc = $makeEmptyRow(null); $grand_inc['nama_kantor'] = 'TOTAL INC';

//   for ($i=1; $i<=28; $i++) {
//     $kc = str_pad((string)$i, 3, '0', STR_PAD_LEFT);

//     $m1 = $m1Rows[$kc] ?? $makeEmptyRow($kc);
//     $cur= $curRows[$kc] ?? $makeEmptyRow($kc);

//     // pastikan total terisi
//     $m1['nilai_ckpn_total']  = (int)$m1['nilai_ckpn_individual'] + (int)$m1['nilai_ckpn_kolektif'];
//     $cur['nilai_ckpn_total'] = (int)$cur['nilai_ckpn_individual'] + (int)$cur['nilai_ckpn_kolektif'];

//     $inc = [
//       'noa_total'             => (int)$cur['noa_total']             - (int)$m1['noa_total'],
//       'noa_individual'        => (int)$cur['noa_individual']        - (int)$m1['noa_individual'],
//       'nilai_ckpn_individual' => (int)$cur['nilai_ckpn_individual'] - (int)$m1['nilai_ckpn_individual'],
//       'noa_asset_baik'        => (int)$cur['noa_asset_baik']        - (int)$m1['noa_asset_baik'],
//       'noa_kolektif'          => (int)$cur['noa_kolektif']          - (int)$m1['noa_kolektif'],
//       'nilai_ckpn_kolektif'   => (int)$cur['nilai_ckpn_kolektif']   - (int)$m1['nilai_ckpn_kolektif'],
//       'nilai_ckpn_total'      => (int)$cur['nilai_ckpn_total']      - (int)$m1['nilai_ckpn_total'],
//     ];

//     // akumulasi grands
//     $sumInto($grand_m1,  $m1);
//     $sumInto($grand_cur, $cur);
//     $sumInto($grand_inc, $inc + ['kode_cabang'=>$kc, 'nama_kantor'=>null]);

//     $rowsOut[] = [
//       'kode_cabang' => $kc,
//       'nama_kantor' => $kantorMap[$kc] ?? null,

//       // M-1
//       'm1'  => [
//         'noa_total'             => (int)$m1['noa_total'],
//         'noa_individual'        => (int)$m1['noa_individual'],
//         'nilai_ckpn_individual' => (int)$m1['nilai_ckpn_individual'],
//         'noa_asset_baik'        => (int)$m1['noa_asset_baik'],
//         'noa_kolektif'          => (int)$m1['noa_kolektif'],
//         'nilai_ckpn_kolektif'   => (int)$m1['nilai_ckpn_kolektif'],
//         'nilai_ckpn_total'      => (int)$m1['nilai_ckpn_total'],
//       ],
//       // CUR
//       'cur' => [
//         'noa_total'             => (int)$cur['noa_total'],
//         'noa_individual'        => (int)$cur['noa_individual'],
//         'nilai_ckpn_individual' => (int)$cur['nilai_ckpn_individual'],
//         'noa_asset_baik'        => (int)$cur['noa_asset_baik'],
//         'noa_kolektif'          => (int)$cur['noa_kolektif'],
//         'nilai_ckpn_kolektif'   => (int)$cur['nilai_ckpn_kolektif'],
//         'nilai_ckpn_total'      => (int)$cur['nilai_ckpn_total'],
//       ],
//       // INC
//       'inc' => [
//         'noa_total'             => (int)$inc['noa_total'],
//         'noa_individual'        => (int)$inc['noa_individual'],
//         'nilai_ckpn_individual' => (int)$inc['nilai_ckpn_individual'],
//         'noa_asset_baik'        => (int)$inc['noa_asset_baik'],
//         'noa_kolektif'          => (int)$inc['noa_kolektif'],
//         'nilai_ckpn_kolektif'   => (int)$inc['nilai_ckpn_kolektif'],
//         'nilai_ckpn_total'      => (int)$inc['nilai_ckpn_total'],
//       ],
//     ];
//   }

//   // grand totals (top row)
//   $grand_m1['kode_cabang'] = null; $grand_m1['nama_kantor'] = 'TOTAL M-1';
//   $grand_cur['kode_cabang']= null; $grand_cur['nama_kantor']= 'TOTAL CUR';
//   $grand_inc['kode_cabang']= null; $grand_inc['nama_kantor']= 'TOTAL INC';

//   return sendResponse(200, "OK", [
//     'closing_date' => $closing_date,
//     'harian_date'  => $harian_date,
//     'source'       => ['closing' => $m1Res['source'] ?? null, 'current' => $curRes['source'] ?? null],
//     'rows'         => $rowsOut,
//     'grand_total'  => [
//       'm1'  => $grand_m1,
//       'cur' => $grand_cur,
//       'inc' => [
//         'noa_total'             => (int)$grand_cur['noa_total']             - (int)$grand_m1['noa_total'],
//         'noa_individual'        => (int)$grand_cur['noa_individual']        - (int)$grand_m1['noa_individual'],
//         'nilai_ckpn_individual' => (int)$grand_cur['nilai_ckpn_individual'] - (int)$grand_m1['nilai_ckpn_individual'],
//         'noa_asset_baik'        => (int)$grand_cur['noa_asset_baik']        - (int)$grand_m1['noa_asset_baik'],
//         'noa_kolektif'          => (int)$grand_cur['noa_kolektif']          - (int)$grand_m1['noa_kolektif'],
//         'nilai_ckpn_kolektif'   => (int)$grand_cur['nilai_ckpn_kolektif']   - (int)$grand_m1['nilai_ckpn_kolektif'],
//         'nilai_ckpn_total'      => (int)$grand_cur['nilai_ckpn_total']      - (int)$grand_m1['nilai_ckpn_total'],
//       ]
//     ]
//   ]);
// }




public function getRekapCkpnPerProduk($input = null) {
  // --- Body: harian_date (wajib) + kode_cabang (opsional) + source (opsional)
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };
  $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('last day of previous month'));
  $kode_filter_raw = $body['kode_cabang'] ?? null;
  $kode_filter = ($kode_filter_raw === null || $kode_filter_raw === '')
                  ? null
                  : str_pad((string)$kode_filter_raw, 3, '0', STR_PAD_LEFT);
  $force = strtolower(trim($body['source'] ?? '')); // 'snapshot' | 'compute' | ''

  // LGD global efektif (tanpa product_code)
  $LGD_GLOBAL = $this->loadGlobalLGD($harian_date);

  // --- Master nama produk
  $namaProduk = [];
  try {
    $q = $this->pdo->query("SELECT kode_produk, nama_produk FROM produk_kredit");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $m) {
      $namaProduk[(int)$m['kode_produk']] = $m['nama_produk'];
    }
  } catch (PDOException $e) {}

  // --- Helper band
  $bandOf = function(int $dpd){
    if($dpd<=30) return '0-30';
    if($dpd<=90) return '31-90';
    if($dpd<=180) return '91-180';
    if($dpd<=360) return '181-360';
    return '>360';
  };

  // ============================ SNAPSHOT? ============================
  $hasSnapshot = false;
  if ($force !== 'compute') {
    try {
      $st = $this->pdo->prepare("SELECT COUNT(1) FROM nominatif_ckpn WHERE created = :d");
      $st->execute([':d' => $harian_date]);
      $hasSnapshot = ((int)$st->fetchColumn() > 0);
      if ($force === 'snapshot' && !$hasSnapshot) {
        return sendResponse(404, "Snapshot nominatif_ckpn untuk {$harian_date} tidak ditemukan.", null);
      }
    } catch (PDOException $e) {
      return sendResponse(500, "DB Error (cek snapshot): ".$e->getMessage(), null);
    }
  }

  if ($hasSnapshot) {
    try {
      $sql = "
        SELECT
          no_rekening, kode_cabang, kode_produk, produk_kredit,
          hari_menunggak, ead, pd, lgd, nilai_ckpn,
          metode_penghitungan, keterangan
        FROM nominatif_ckpn
        WHERE created = :d
      ";
      $params = [':d' => $harian_date];
      if ($kode_filter !== null) {
        $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
        $params[':kc'] = $kode_filter;
      } else {
        $sql .= " AND kode_cabang <> '000'";
      }
      $st = $this->pdo->prepare($sql);
      $st->execute($params);
      $rows = $st->fetchAll(PDO::FETCH_ASSOC);

      $out = [];
      foreach ($rows as $r) {
        $prod = ($r['kode_produk'] === null || $r['kode_produk']==='') ? null : (int)$r['kode_produk'];
        if ($prod === null) continue;

        if (!isset($out[$prod])) {
          $out[$prod] = [
            'product_code'          => (string)$prod,
            'nama_produk'           => $namaProduk[$prod] ?? ($r['produk_kredit'] ?? null),
            'noa'                   => 0,
            'ead'                   => 0.0,
            'pd_wavg_percent'       => 0.0,        // wavg dari collective
            'lgd_percent'           => $LGD_GLOBAL,
            'ckpn_individual'       => 0,
            'ckpn_collective'       => 0,
            '_ead_sum_collective'   => 0.0,
            '_ead_x_pd_collective'  => 0.0,
            'detail'=>[
              'ASSET BAIK (0-7 non-restruk)'=>['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0],
              '0-30'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
              '31-90'  => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
              '91-180' => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
              '181-360'=> ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
              '>360'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
            ]
          ];
        }

        $dpd = (int)($r['hari_menunggak'] ?? 0);
        $ead = (float)($r['ead'] ?? 0);
        $pd  = (float)($r['pd'] ?? 0);
        $met = strtoupper(trim((string)$r['metode_penghitungan']));
        $ket = strtoupper(trim((string)($r['keterangan'] ?? '')));

        $out[$prod]['noa']++;
        $out[$prod]['ead'] += $ead;

        // INDIV
        if (strpos($met, 'INDIV') === 0) {
          $out[$prod]['ckpn_individual'] += (float)$r['nilai_ckpn'];
          continue;
        }

        // COLLECTIVE/KOLEKTIF
        if (strpos($met, 'COLL') === 0 || strpos($met, 'KOLE') === 0) {
          if (in_array($ket, ['ASET BAIK','ASSET BAIK'], true)) {
            $g =& $out[$prod]['detail']['ASSET BAIK (0-7 non-restruk)'];
            $g['noa']++; $g['ead_sum'] += $ead;
          } else {
            $band = $bandOf($dpd);
            $g =& $out[$prod]['detail'][$band];
            $g['noa']++;
            $g['ead_sum'] += $ead;
            $g['ckpn_collective'] += (float)$r['nilai_ckpn'];

            // akumulasi ke header produk
            $out[$prod]['ckpn_collective'] += (float)$r['nilai_ckpn'];

            // PD wavg per band & total collective
            $g['_ead_pd'] += $ead * $pd;
            $out[$prod]['_ead_sum_collective']  += $ead;
            $out[$prod]['_ead_x_pd_collective'] += $ead * $pd;
          }
        }
      }

      // Finalisasi & rounding
      foreach ($out as &$p) {
        $p['pd_wavg_percent'] = $p['_ead_sum_collective'] > 0
          ? round($p['_ead_x_pd_collective'] / $p['_ead_sum_collective'], 2) : 0.00;
        unset($p['_ead_sum_collective'], $p['_ead_x_pd_collective']);

        foreach ($p['detail'] as $label => &$g) {
          if (isset($g['_ead_pd'])) {
            $g['pd_percent'] = $g['ead_sum'] > 0 ? round($g['_ead_pd'] / $g['ead_sum'], 2) : 0.00;
            unset($g['_ead_pd']);
          }
          $g['ead_sum']         = (int)round($g['ead_sum']);
          $g['ckpn_collective'] = (int)round($g['ckpn_collective']);
        } unset($g);

        // safety net: header ckpn_collective = sum detail (kecuali asset baik)
        if ((int)$p['ckpn_collective'] === 0) {
          $sum = 0;
          foreach ($p['detail'] as $lbl => $g) {
            if ($lbl !== 'ASSET BAIK (0-7 non-restruk)') $sum += (int)$g['ckpn_collective'];
          }
          $p['ckpn_collective'] = (int)$sum;
        }

        $p['ead']              = (int)round($p['ead']);
        $p['ckpn_individual']  = (int)round($p['ckpn_individual']);
        $p['ckpn_collective']  = (int)round($p['ckpn_collective']);
      } unset($p);

      ksort($out, SORT_NUMERIC);
      return sendResponse(200,'OK (snapshot)', [
        'harian_date'  => $harian_date,
        'kode_cabang'  => $kode_filter,
        'produk'       => array_values($out)
      ]);
    } catch (PDOException $e) {
      return sendResponse(500, "DB Error (snapshot): ".$e->getMessage(), null);
    }
  }

  // ============================ FALLBACK COMPUTE ============================
  // nominatif (filter cabang)
  $baseSql = "SELECT no_rekening, kode_cabang, kode_produk, hari_menunggak, saldo_bank
              FROM nominatif
              WHERE created = :d";
  if ($kode_filter !== null) {
    $baseSql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
  } else {
    $baseSql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'";
  }
  try {
    $st = $this->pdo->prepare($baseSql);
    $params = [':d' => $harian_date];
    if ($kode_filter !== null) $params[':kc'] = $kode_filter;
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, 'DB Error (nominatif): '.$e->getMessage(), null);
  }

  // master bucket
  $buckets = [];
  try {
    foreach ($this->pdo->query("SELECT dpd_code,dpd_name,min_day,max_day FROM ref_dpd_bucket ORDER BY min_day")->fetchAll(PDO::FETCH_ASSOC) as $b) {
      $buckets[] = ['code'=>$b['dpd_code'],'name'=>$b['dpd_name'],'min'=>(int)$b['min_day'],'max'=>is_null($b['max_day'])?null:(int)$b['max_day']];
    }
  } catch (PDOException $e) {}

  // PD product x bucket (pakai versi efektif kalau ada kolom created)
  $pdMap = [];
  try {
    $stPd = $this->pdo->prepare("
      SELECT p.product_code, p.dpd_code, p.pd_percent
      FROM pd_current p
      JOIN (
        SELECT product_code, dpd_code, MAX(created) AS created
        FROM pd_current
        WHERE created <= :d
        GROUP BY product_code, dpd_code
      ) x ON x.product_code = p.product_code
         AND x.dpd_code     = p.dpd_code
         AND x.created      = p.created
    ");
    $stPd->execute([':d' => $harian_date]);
    foreach ($stPd->fetchAll(PDO::FETCH_ASSOC) as $p) {
      $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
    }
  } catch (PDOException $e) {
    // fallback: tabel lama tanpa kolom created
    try {
      foreach ($this->pdo->query("SELECT product_code,dpd_code,pd_percent FROM pd_current")->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)str_replace(',', '.', (string)$p['pd_percent']);
      }
    } catch (PDOException $e2) {}
  }

  // RESTRUK terbaru <= tanggal
  $restrukSet = [];
  try {
    $stRes = $this->pdo->prepare("
      SELECT nr.no_rekening
      FROM nom_restruk nr
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM nom_restruk
        WHERE created <= :d
        GROUP BY no_rekening
      ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
    ");
    $stRes->execute([':d'=>$harian_date]);
    $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC), 'no_rekening'), true);
  } catch (PDOException $e) {}

  // INDIVIDUAL terbaru <= tanggal
  $indivMap = [];
  try {
    $stInd = $this->pdo->prepare("
      SELECT ci.no_rekening, ci.nilai_ckpn
      FROM ckpn_individual ci
      JOIN (
        SELECT no_rekening, MAX(created) AS created
        FROM ckpn_individual
        WHERE created <= :d
        GROUP BY no_rekening
      ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
    ");
    $stInd->execute([':d'=>$harian_date]);
    foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $r) { $indivMap[$r['no_rekening']] = (float)$r['nilai_ckpn']; }
  } catch (PDOException $e) {}

  // peta PD untuk header band (ambil yang pertama tersedia)
  $bandBuckets = [
    '0-30'   => ['A','B'],
    '31-90'  => ['C','D'],
    '91-180' => ['E','F','G'],
    '181-360'=> ['H','I','J','K','L','M'],
    '>360'   => ['N'],
  ];
  $getBandPd = function(int $prod, string $band) use ($bandBuckets, $pdMap): float {
    if (!isset($pdMap[$prod])) return 0.0;
    foreach ($bandBuckets[$band] as $code) {
      if (isset($pdMap[$prod][$code])) return (float)$pdMap[$prod][$code];
    }
    return 0.0;
  };

  $out = [];
  foreach ($rows as $r) {
    $noRek = $r['no_rekening'];
    $prod  = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
    if ($prod === null) continue;
    $dpd = (int)($r['hari_menunggak'] ?? 0);
    $ead = (float)($r['saldo_bank'] ?? 0);

    if (!isset($out[$prod])) {
      $out[$prod] = [
        'product_code'          => (string)$prod,
        'nama_produk'           => $namaProduk[$prod] ?? null,
        'noa'                   => 0,
        'ead'                   => 0.0,
        'pd_wavg_percent'       => 0.0,
        'lgd_percent'           => $LGD_GLOBAL,
        'ckpn_individual'       => 0,
        'ckpn_collective'       => 0,
        '_ead_sum_collective'   => 0.0,
        '_ead_x_pd_collective'  => 0.0,
        'detail'=>[
          'ASSET BAIK (0-7 non-restruk)'=>['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0],
          '0-30'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'0-30'),   'ckpn_collective'=>0],
          '31-90'  => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'31-90'),  'ckpn_collective'=>0],
          '91-180' => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'91-180'), 'ckpn_collective'=>0],
          '181-360'=> ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'181-360'),'ckpn_collective'=>0],
          '>360'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'>360'),  'ckpn_collective'=>0],
        ]
      ];
    }

    $out[$prod]['noa']++;
    $out[$prod]['ead'] += $ead;

    // INDIVIDUAL
    if (isset($indivMap[$noRek])) {
      $out[$prod]['ckpn_individual'] += $indivMap[$noRek];
      continue;
    }

    // ASSET BAIK
    $isRestruk = isset($restrukSet[$noRek]);
    if ($dpd <= 7 && !$isRestruk) {
      $g =& $out[$prod]['detail']['ASSET BAIK (0-7 non-restruk)'];
      $g['noa']++; $g['ead_sum'] += $ead;
      continue;
    }

    // KOLEKTIF (PD×LGD×EAD)
    $dpdCode = null;
    foreach ($buckets as $b){
      if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) { $dpdCode=$b['code']; break; }
    }
    $pd = ($dpdCode && isset($pdMap[$prod][$dpdCode])) ? (float)$pdMap[$prod][$dpdCode] : 0.0;

    $band = $bandOf($dpd);
    $g =& $out[$prod]['detail'][$band];
    $g['noa']++; $g['ead_sum'] += $ead;

    $ck = round($ead * ($pd/100.0) * ($LGD_GLOBAL/100.0));
    $g['ckpn_collective'] += $ck;
    $out[$prod]['ckpn_collective'] += $ck;

    $out[$prod]['_ead_sum_collective']  += $ead;
    $out[$prod]['_ead_x_pd_collective'] += $ead * $pd;
  }

  // finalize compute
  foreach ($out as &$p) {
    $p['pd_wavg_percent'] = $p['_ead_sum_collective'] > 0
      ? round($p['_ead_x_pd_collective'] / $p['_ead_sum_collective'], 2) : 0.00;
    unset($p['_ead_sum_collective'], $p['_ead_x_pd_collective']);

    foreach ($p['detail'] as &$g) {
      $g['ead_sum']         = (int)round($g['ead_sum']);
      $g['ckpn_collective'] = (int)round($g['ckpn_collective']);
    } unset($g);

    $p['ead']              = (int)round($p['ead']);
    $p['ckpn_individual']  = (int)round($p['ckpn_individual']);
    $p['ckpn_collective']  = (int)round($p['ckpn_collective']);
  } unset($p);

  ksort($out, SORT_NUMERIC);
  return sendResponse(200,'OK (compute)', [
    'harian_date'  => $harian_date,
    'kode_cabang'  => $kode_filter,
    'produk'       => array_values($out)
  ]);
}


// public function getRekapCkpnPerBucket($input = null) {
//   // ===== Params =====
//   $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
//   $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };
//   $closing_date = $parseDate($body['closing_date'] ?? null) ?: date('Y-m-t', strtotime('-1 month'));
//   $harian_date  = $parseDate($body['harian_date']  ?? null) ?: date('Y-m-d', strtotime('-1 day'));
//   $kc_raw       = $body['kode_kantor'] ?? null;
//   $kode_filter  = ($kc_raw === null || $kc_raw === '') ? null : str_pad((string)$kc_raw, 3, '0', STR_PAD_LEFT);
//   $LGD = 59.48;

//   // ===== Master bucket & PD =====
//   $bucketRows = $this->pdo->query("
//       SELECT dpd_code, dpd_name, min_day, max_day, status_tag
//       FROM ref_dpd_bucket ORDER BY min_day
//   ")->fetchAll(PDO::FETCH_ASSOC);

//   $buckets = [];
//   $nameMap = [];
//   $tagMap  = [];
//   foreach ($bucketRows as $b) {
//     $buckets[] = [
//       'code'=>$b['dpd_code'], 'name'=>$b['dpd_name'],
//       'min'=>(int)$b['min_day'], 'max'=>is_null($b['max_day'])?null:(int)$b['max_day'],
//       'tag'=>$b['status_tag'] ?? null
//     ];
//     $nameMap[$b['dpd_code']] = $b['dpd_name'];
//     $tagMap[$b['dpd_code']]  = $b['status_tag'] ?? null;
//   }

//   $pdMap = [];
//   foreach ($this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current")->fetchAll(PDO::FETCH_ASSOC) as $p) {
//     $pdMap[(int)$p['product_code']][$p['dpd_code']] =
//       (float)str_replace(',', '.', (string)$p['pd_percent']);
//   }

//   // helpers
//   $dpdToCode = function (int $dpd) use ($buckets): ?string {
//     foreach ($buckets as $b) {
//       if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) return $b['code'];
//     }
//     return null;
//   };

//   // ===== Core: hitung per tanggal =====
//   $computeForDate = function (string $snap_date, ?string $kcFilter) use ($LGD, $dpdToCode, $pdMap) {
//     // nominatif snapshot: ambil OS (baki_debet) dan EAD (saldo_bank)
//     $sql = "SELECT no_rekening, kode_cabang, kode_produk, hari_menunggak, saldo_bank, baki_debet
//             FROM nominatif WHERE created = :d";
//     if ($kcFilter !== null) $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
//     $st = $this->pdo->prepare($sql);
//     $params = [':d'=>$snap_date];
//     if ($kcFilter !== null) $params[':kc'] = $kcFilter;
//     $st->execute($params);
//     $rows = $st->fetchAll(PDO::FETCH_ASSOC);

//     // restruk terbaru ≤ snapshot
//     $stRes = $this->pdo->prepare("
//       SELECT nr.no_rekening
//       FROM nom_restruk nr
//       JOIN (
//         SELECT no_rekening, MAX(created) AS created
//         FROM nom_restruk
//         WHERE created <= :d
//         GROUP BY no_rekening
//       ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
//     ");
//     $restrukSet = [];
//     try {
//       $stRes->execute([':d'=>$snap_date]);
//       $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
//     } catch (PDOException $e) { $restrukSet = []; }

//     // individual terbaru ≤ snapshot
//     $stInd = $this->pdo->prepare("
//       SELECT ci.no_rekening, ci.nilai_ckpn
//       FROM ckpn_individual ci
//       JOIN (
//         SELECT no_rekening, MAX(created) AS created
//         FROM ckpn_individual
//         WHERE created <= :d
//         GROUP BY no_rekening
//       ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
//     ");
//     $stInd->execute([':d'=>$snap_date]);
//     $indivMap = [];
//     foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $r) $indivMap[$r['no_rekening']] = (float)$r['nilai_ckpn'];

//     $sumPerBucket = [];          // [code] => ['noa'=>..,'os'=>..,'ckpn'=>..]
//     $ckpnByAcc    = [];          // [no_rekening] => ckpn
//     $accSet       = [];          // presence
//     $osByAcc      = [];          // [no_rekening] => OS (baki_debet) untuk hitung realisasi set

//     foreach ($rows as $r) {
//       $norek = $r['no_rekening'];
//       $accSet[$norek] = true;

//       $prod = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
//       $dpd  = (int)($r['hari_menunggak'] ?? 0);
//       $os   = (float)($r['baki_debet'] ?? 0.0);    // OS = baki_debet
//       $ead  = (float)($r['saldo_bank'] ?? 0.0);    // EAD = saldo_bank (untuk CKPN)

//       $osByAcc[$norek] = $os;

//       $code = $dpdToCode($dpd) ?? 'A';
//       if (!isset($sumPerBucket[$code])) $sumPerBucket[$code] = ['noa'=>0,'os'=>0.0,'ckpn'=>0.0];
//       $sumPerBucket[$code]['noa']++;
//       $sumPerBucket[$code]['os']  += $os;

//       if (isset($indivMap[$norek])) {
//         $ck = (float)$indivMap[$norek];
//       } else {
//         $isRestruk = isset($restrukSet[$norek]);
//         if ($dpd <= 7 && !$isRestruk) {
//           $ck = 0.0;
//         } else {
//           $pd = ($prod !== null && $code !== null && isset($pdMap[$prod][$code])) ? (float)$pdMap[$prod][$code] : 0.0;
//           $ck = round($ead * ($pd/100.0) * ($LGD/100.0));
//         }
//       }
//       $sumPerBucket[$code]['ckpn'] += $ck;
//       $ckpnByAcc[$norek] = $ck;
//     }

//     // pembulatan angka besar
//     foreach ($sumPerBucket as &$v) { $v['os'] = (int)round($v['os']); $v['ckpn'] = (int)round($v['ckpn']); } unset($v);

//     return ['perBucket'=>$sumPerBucket, 'ckpnByAcc'=>$ckpnByAcc, 'accSet'=>$accSet, 'osByAcc'=>$osByAcc];
//   };

//   // ===== Jalankan untuk closing_date & harian_date =====
//   $M1  = $computeForDate($closing_date, $kode_filter);
//   $CUR = $computeForDate($harian_date,  $kode_filter);

//   // ===== O_LUNAS (CKPN M-1 utk rekening yg hilang di current) =====
//   $oLunas_m1 = 0;
//   foreach ($M1['ckpnByAcc'] as $acc => $ck) {
//     if (!isset($CUR['accSet'][$acc])) $oLunas_m1 += (int)$ck;
//   }

//   // ===== REALISASI (row) -> awal bulan s/d harian_date (berdasarkan tgl_realisasi) =====
//   $realisasiRow = ['noa'=>0, 'os'=>0];
//   $start_month = date('Y-m-01', strtotime($harian_date));
//   try {
//     $sqlReal = "SELECT COUNT(*) AS noa, COALESCE(SUM(baki_debet),0) AS os
//                 FROM nominatif
//                 WHERE created = :d AND tgl_realisasi BETWEEN :s AND :e";
//     if ($kode_filter !== null) $sqlReal .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
//     $stR = $this->pdo->prepare($sqlReal);
//     $paramsR = [':d'=>$harian_date, ':s'=>$start_month, ':e'=>$harian_date];
//     if ($kode_filter !== null) $paramsR[':kc'] = $kode_filter;
//     $stR->execute($paramsR);
//     $tmp = $stR->fetch(PDO::FETCH_ASSOC);
//     if ($tmp) $realisasiRow = ['noa'=>(int)$tmp['noa'], 'os'=>(int)$tmp['os']];
//   } catch (PDOException $e) {
//     $realisasiRow = ['noa'=>0,'os'=>0];
//   }

//   // ===== REALISASI untuk rumus DPD0 (set-based): account ada di CUR tapi tidak di M1 =====
//   $real_set_noa = 0; $real_set_os = 0;
//   foreach ($CUR['accSet'] as $acc => $_) {
//     if (!isset($M1['accSet'][$acc])) {
//       $real_set_noa++;
//       $real_set_os += (int)round($CUR['osByAcc'][$acc] ?? 0);
//     }
//   }

//   // ===== Susun output baris A..N + subtotal + O_LUNAS =====
//   $order = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
//   $rowsOut = [];

//   // baris Realisasi di paling atas (NOA/OS current saja)
//   $rowsOut[] = [
//     'dpd_code'   => 'REALISASI',
//     'dpd_name'   => 'Realisasi (awal bulan s/d tanggal laporan)',
//     'status_tag' => null,
//     'noa_m1'     => null,
//     'os_m1'      => null,
//     'noa_curr'   => $realisasiRow['noa'],
//     'os_curr'    => $realisasiRow['os'],
//     'inc_noa'    => $realisasiRow['noa'],
//     'inc_os'     => $realisasiRow['os'],
//     'inc_pct'    => null,
//     'ckpn_m1'    => null,
//     'ckpn_curr'  => null,
//     'ckpn_inc'   => null
//   ];

//   $totSC = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];
//   $totFE = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];
//   $totBE = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];

//   $pushRow = function($code,$name,$m1,$cur,$isA=false,$realNoa=0,$realOs=0) use (&$rowsOut,$tagMap) {
//     $noa_m1 = $m1['noa'] ?? 0; $os_m1 = $m1['os'] ?? 0; $ck_m1 = $m1['ckpn'] ?? 0;
//     $noa_cu = $cur['noa'] ?? 0; $os_cu = $cur['os'] ?? 0; $ck_cu = $cur['ckpn'] ?? 0;
//     $inc_noa = $noa_cu - $noa_m1;
//     $inc_os  = $os_cu  - $os_m1;
//     if ($isA) { // aturan khusus DPD 0
//       $inc_noa += $realNoa;
//       $inc_os  += $realOs;
//     }
//     $inc_pct = ($os_m1 > 0) ? round(($inc_os / $os_m1) * 100, 2) : null;

//     $rowsOut[] = [
//       'dpd_code'   => $code,
//       'dpd_name'   => $name,
//       'status_tag' => (strlen($code)===1 ? ($tagMap[$code] ?? null) : null),
//       'noa_m1'     => (int)$noa_m1,
//       'os_m1'      => (int)$os_m1,
//       'noa_curr'   => (int)$noa_cu,
//       'os_curr'    => (int)$os_cu,
//       'inc_noa'    => (int)$inc_noa,
//       'inc_os'     => (int)$inc_os,
//       'inc_pct'    => $inc_pct,
//       'ckpn_m1'    => (int)$ck_m1,
//       'ckpn_curr'  => (int)$ck_cu,
//       'ckpn_inc'   => (int)($ck_cu - $ck_m1),
//     ];
//   };

//   foreach ($order as $code) {
//     $m1  = $M1['perBucket'][$code]  ?? ['noa'=>0,'os'=>0,'ckpn'=>0];
//     $cur = $CUR['perBucket'][$code] ?? ['noa'=>0,'os'=>0,'ckpn'=>0];

//     // pasang baris
//     $isA = ($code === 'A');
//     $pushRow($code, $nameMap[$code] ?? $code, $m1, $cur, $isA, $real_set_noa, $real_set_os);

//     $tag = $tagMap[$code] ?? null;
//     if ($tag === 'SC') {
//       $totSC['noa_m1'] += $m1['noa']; $totSC['os_m1'] += $m1['os'];
//       $totSC['noa_cur']+= $cur['noa'];$totSC['os_cur']+= $cur['os'];
//       $totSC['ck_m1']  += $m1['ckpn'];$totSC['ck_cur'] += $cur['ckpn'];
//     } elseif ($tag === 'FE') {
//       $totFE['noa_m1'] += $m1['noa']; $totFE['os_m1'] += $m1['os'];
//       $totFE['noa_cur']+= $cur['noa'];$totFE['os_cur']+= $cur['os'];
//       $totFE['ck_m1']  += $m1['ckpn'];$totFE['ck_cur'] += $cur['ckpn'];
//     } elseif ($tag === 'BE') {
//       $totBE['noa_m1'] += $m1['noa']; $totBE['os_m1'] += $m1['os'];
//       $totBE['noa_cur']+= $cur['noa'];$totBE['os_cur']+= $cur['os'];
//       $totBE['ck_m1']  += $m1['ckpn'];$totBE['ck_cur'] += $cur['ckpn'];
//     }

//     // subtotal
//     if ($code === 'B') {
//       $rowsOut[] = [
//         'dpd_code'  => 'TOTAL_SC',
//         'dpd_name'  => 'TOTAL SC',
//         'status_tag'=> null,
//         'noa_m1'    => (int)$totSC['noa_m1'],
//         'os_m1'     => (int)$totSC['os_m1'],
//         'noa_curr'  => (int)$totSC['noa_cur'],
//         'os_curr'   => (int)$totSC['os_cur'],
//         'inc_noa'   => (int)($totSC['noa_cur'] - $totSC['noa_m1']),
//         'inc_os'    => (int)($totSC['os_cur']  - $totSC['os_m1']),
//         'inc_pct'   => ($totSC['os_m1']>0) ? round((($totSC['os_cur']-$totSC['os_m1'])/$totSC['os_m1'])*100,2) : null,
//         'ckpn_m1'   => (int)$totSC['ck_m1'],
//         'ckpn_curr' => (int)$totSC['ck_cur'],
//         'ckpn_inc'  => (int)($totSC['ck_cur'] - $totSC['ck_m1'])
//       ];
//     }
//     if ($code === 'G') {
//       $rowsOut[] = [
//         'dpd_code'  => 'TOTAL_FE',
//         'dpd_name'  => 'TOTAL FE',
//         'status_tag'=> null,
//         'noa_m1'    => (int)$totFE['noa_m1'],
//         'os_m1'     => (int)$totFE['os_m1'],
//         'noa_curr'  => (int)$totFE['noa_cur'],
//         'os_curr'   => (int)$totFE['os_cur'],
//         'inc_noa'   => (int)($totFE['noa_cur'] - $totFE['noa_m1']),
//         'inc_os'    => (int)($totFE['os_cur']  - $totFE['os_m1']),
//         'inc_pct'   => ($totFE['os_m1']>0) ? round((($totFE['os_cur']-$totFE['os_m1'])/$totFE['os_m1'])*100,2) : null,
//         'ckpn_m1'   => (int)$totFE['ck_m1'],
//         'ckpn_curr' => (int)$totFE['ck_cur'],
//         'ckpn_inc'  => (int)($totFE['ck_cur'] - $totFE['ck_m1'])
//       ];
//     }
//     if ($code === 'N') {
//       $rowsOut[] = [
//         'dpd_code'  => 'TOTAL_BE',
//         'dpd_name'  => 'TOTAL BE',
//         'status_tag'=> null,
//         'noa_m1'    => (int)$totBE['noa_m1'],
//         'os_m1'     => (int)$totBE['os_m1'],
//         'noa_curr'  => (int)$totBE['noa_cur'],
//         'os_curr'   => (int)$totBE['os_cur'],
//         'inc_noa'   => (int)($totBE['noa_cur'] - $totBE['noa_m1']),
//         'inc_os'    => (int)($totBE['os_cur']  - $totBE['os_m1']),
//         'inc_pct'   => ($totBE['os_m1']>0) ? round((($totBE['os_cur']-$totBE['os_m1'])/$totBE['os_m1'])*100,2) : null,
//         'ckpn_m1'   => (int)$totBE['ck_m1'],
//         'ckpn_curr' => (int)$totBE['ck_cur'],
//         'ckpn_inc'  => (int)($totBE['ck_cur'] - $totBE['ck_m1'])
//       ];
//     }
//   }

//   // O_LUNAS (hanya CKPN)
//   $rowsOut[] = [
//     'dpd_code'  => 'O',
//     'dpd_name'  => 'O_Lunas',
//     'status_tag'=> null,
//     'noa_m1'    => null, 'os_m1'=>null,
//     'noa_curr'  => null, 'os_curr'=>null,
//     'inc_noa'   => null, 'inc_os'=>null, 'inc_pct'=>null,
//     'ckpn_m1'   => (int)$oLunas_m1,
//     'ckpn_curr' => 0,
//     'ckpn_inc'  => (int)(0 - $oLunas_m1)
//   ];

//   // GRAND TOTAL (A..N saja)
//   $grand = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];
//   foreach (['A','B','C','D','E','F','G','H','I','J','K','L','M','N'] as $code) {
//     $m1  = $M1['perBucket'][$code]  ?? ['noa'=>0,'os'=>0,'ckpn'=>0];
//     $cur = $CUR['perBucket'][$code] ?? ['noa'=>0,'os'=>0,'ckpn'=>0];
//     $grand['noa_m1'] += $m1['noa'];  $grand['os_m1'] += $m1['os'];  $grand['ck_m1'] += $m1['ckpn'];
//     $grand['noa_cur']+= $cur['noa']; $grand['os_cur']+= $cur['os']; $grand['ck_cur']+= $cur['ckpn'];
//   }

//   return sendResponse(200,'OK', [
//     'closing_date' => $closing_date,
//     'harian_date'  => $harian_date,
//     'kode_kantor'  => $kode_filter, // null = konsolidasi
//     'realisasi_row'=> $realisasiRow, // info tambahan bila mau dipakai di UI
//     'rows' => $rowsOut,
//     'grand_total' => [
//       'noa_m1'    => (int)$grand['noa_m1'],
//       'os_m1'     => (int)$grand['os_m1'],
//       'noa_curr'  => (int)$grand['noa_cur'],
//       'os_curr'   => (int)$grand['os_cur'],
//       'inc_noa'   => (int)($grand['noa_cur'] - $grand['noa_m1']),
//       'inc_os'    => (int)($grand['os_cur']  - $grand['os_m1']),
//       'inc_pct'   => ($grand['os_m1']>0) ? round((($grand['os_cur']-$grand['os_m1'])/$grand['os_m1'])*100,2) : null,
//       'ckpn_m1'   => (int)$grand['ck_m1'],
//       'ckpn_curr' => (int)$grand['ck_cur'],
//       'ckpn_inc'  => (int)($grand['ck_cur'] - $grand['ck_m1'])
//     ]
//   ]);
// }

private function loadGlobalLGD(string $harian_date): float {
  try {
    $st = $this->pdo->prepare("
      SELECT lgd_percent
      FROM lgd_current
      WHERE created <= :d
      ORDER BY created DESC
      LIMIT 1
    ");
    $st->execute([':d' => $harian_date]);
    $v = $st->fetchColumn();
    return ($v !== false) ? (float)$v : 59.48;
  } catch (PDOException $e) {
    return 59.48;
  }
}


// ================== Helpers ==================
private function loadKantorMap(): array {
    $map = [];
    try {
      $q = $this->pdo->query("SELECT kode_kantor, nama_kantor FROM kode_kantor");
      foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $m) {
        $kode = str_pad((string)$m['kode_kantor'], 3, '0', STR_PAD_LEFT);
        $map[$kode] = $m['nama_kantor'];
      }
    } catch (PDOException $e) {
      // biarkan kosong
    }
    return $map;
}

public function getRekapCkpnPerBucket($input = null) {
  // ===== Params =====
  $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
  $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };
  $closing_date = $parseDate($body['closing_date'] ?? null) ?: date('Y-m-t', strtotime('-1 month'));
  $harian_date  = $parseDate($body['harian_date']  ?? null) ?: date('Y-m-d', strtotime('-1 day'));
  $kc_raw       = $body['kode_kantor'] ?? null;
  $kode_filter  = ($kc_raw === null || $kc_raw === '') ? null : str_pad((string)$kc_raw, 3, '0', STR_PAD_LEFT);

  // ===== Master bucket =====
  try {
    $bucketRows = $this->pdo->query("
      SELECT dpd_code, dpd_name, min_day, max_day, status_tag
      FROM ref_dpd_bucket ORDER BY min_day
    ")->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return sendResponse(500, "DB Error (ref_dpd_bucket): ".$e->getMessage(), null);
  }

  $buckets = [];
  $nameMap = [];
  $tagMap  = [];
  foreach ($bucketRows as $b) {
    $buckets[] = [
      'code'=>$b['dpd_code'], 'name'=>$b['dpd_name'],
      'min'=>(int)$b['min_day'], 'max'=>is_null($b['max_day'])?null:(int)$b['max_day'],
      'tag'=>$b['status_tag'] ?? null
    ];
    $nameMap[$b['dpd_code']] = $b['dpd_name'];
    $tagMap[$b['dpd_code']]  = $b['status_tag'] ?? null;
  }

  // helper: map DPD → dpd_code
  $dpdToCode = function (int $dpd) use ($buckets): ?string {
    foreach ($buckets as $b) {
      if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) return $b['code'];
    }
    return null;
  };

  // helper: buat range satu hari [start, end)
  $dayRange = function (string $d): array {
    $ds = $d . " 00:00:00";
    $de = date('Y-m-d', strtotime($d.' +1 day')) . " 00:00:00";
    return [$ds, $de];
  };

  // helper: cek snapshot utk tanggal + (opsional) cabang
  $hasSnapshot = function (string $d, ?string $kcFilter) use ($dayRange) : bool {
    try {
      [$ds, $de] = $dayRange($d);
      $sql = "SELECT COUNT(1) FROM nominatif_ckpn WHERE created >= :ds AND created < :de";
      if ($kcFilter !== null) $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
      $st = $this->pdo->prepare($sql);
      $params = [':ds'=>$ds, ':de'=>$de];
      if ($kcFilter !== null) $params[':kc'] = $kcFilter;
      $st->execute($params);
      return ((int)$st->fetchColumn() > 0);
    } catch (PDOException $e) { return false; }
  };

  // helper: PD map versi tanggal
  $loadPdMap = function (string $d): array {
    $pdMap = [];
    try {
      $stPd = $this->pdo->prepare("
        SELECT p.product_code, p.dpd_code, p.pd_percent
        FROM pd_current p
        JOIN (
          SELECT product_code, dpd_code, MAX(created) AS created
          FROM pd_current
          WHERE created <= :d
          GROUP BY product_code, dpd_code
        ) x ON x.product_code = p.product_code
           AND x.dpd_code     = p.dpd_code
           AND x.created      = p.created
      ");
      $stPd->execute([':d' => $d]);
      foreach ($stPd->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)$p['pd_percent'];
      }
      if (!empty($pdMap)) return $pdMap;
    } catch (PDOException $e) { /* fallback */ }

    try {
      $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
      foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $pdMap[(int)$p['product_code']][$p['dpd_code']] =
          (float)str_replace(',', '.', (string)$p['pd_percent']);
      }
    } catch (PDOException $e) {}
    return $pdMap;
  };

  // ===== Core: hitung per tanggal (snapshot-first) =====
  $computeForDate = function (string $snap_date, ?string $kcFilter) use ($dpdToCode, $dayRange, $hasSnapshot, $loadPdMap) {
    $sumPerBucket = [];          // [code] => ['noa'=>..,'os'=>..,'ckpn'=>..]
    $ckpnByAcc    = [];          // [no_rekening] => ckpn
    $accSet       = [];          // presence
    $osByAcc      = [];          // [no_rekening] => OS (baki_debet)

    // LGD efektif tanggal tsb
    $LGD_eff = $this->loadGlobalLGD($snap_date);

    // selalu load OS dari nominatif utk tanggal tsb (dipakai kedua jalur)
    try {
      [$ds, $de] = $dayRange($snap_date);
      $sqlOS = "SELECT no_rekening, baki_debet, kode_cabang
                FROM nominatif WHERE created >= :ds AND created < :de";
      if ($kcFilter !== null) $sqlOS .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
      else $sqlOS .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'";
      $stOS = $this->pdo->prepare($sqlOS);
      $paramsOS = [':ds'=>$ds, ':de'=>$de];
      if ($kcFilter !== null) $paramsOS[':kc'] = $kcFilter;
      $stOS->execute($paramsOS);
      foreach ($stOS->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $osByAcc[$r['no_rekening']] = (float)($r['baki_debet'] ?? 0);
      }
    } catch (PDOException $e) {}

    $useSnapshot = $hasSnapshot($snap_date, $kcFilter);

    if ($useSnapshot) {
      // ---------- SNAPSHOT ----------
      try {
        [$ds, $de] = $dayRange($snap_date);
        $sql = "SELECT no_rekening, kode_cabang, hari_menunggak, nilai_ckpn
                FROM nominatif_ckpn
                WHERE created >= :ds AND created < :de";
        if ($kcFilter !== null) $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
        else $sql .= " AND kode_cabang <> '000'";
        $st = $this->pdo->prepare($sql);
        $params = [':ds'=>$ds, ':de'=>$de];
        if ($kcFilter !== null) $params[':kc'] = $kcFilter;
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
          $norek = $r['no_rekening'];
          $accSet[$norek] = true;

          $dpd  = (int)($r['hari_menunggak'] ?? 0);
          $code = $dpdToCode($dpd) ?? 'A';
          if (!isset($sumPerBucket[$code])) $sumPerBucket[$code] = ['noa'=>0,'os'=>0.0,'ckpn'=>0.0];

          $sumPerBucket[$code]['noa']++;
          $sumPerBucket[$code]['os']  += (float)($osByAcc[$norek] ?? 0.0);
          $sumPerBucket[$code]['ckpn']+= (float)($r['nilai_ckpn'] ?? 0.0);

          $ckpnByAcc[$norek] = (float)($r['nilai_ckpn'] ?? 0.0);
        }
      } catch (PDOException $e) {
        return ['error' => "DB Error (snapshot load): ".$e->getMessage()];
      }

    } else {
      // ---------- COMPUTE ----------
      try {
        [$ds, $de] = $dayRange($snap_date);
        $sql = "SELECT no_rekening, kode_cabang, kode_produk, hari_menunggak, saldo_bank
                FROM nominatif
                WHERE created >= :ds AND created < :de";
        if ($kcFilter !== null) $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
        else $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'";
        $st = $this->pdo->prepare($sql);
        $params = [':ds'=>$ds, ':de'=>$de];
        if ($kcFilter !== null) $params[':kc'] = $kcFilter;
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
        return ['error' => "DB Error (nominatif load): ".$e->getMessage()];
      }

      // restruk
      $restrukSet = [];
      try {
        $stRes = $this->pdo->prepare("
          SELECT nr.no_rekening
          FROM nom_restruk nr
          JOIN (
            SELECT no_rekening, MAX(created) AS created
            FROM nom_restruk
            WHERE created <= :d
            GROUP BY no_rekening
          ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
        ");
        $stRes->execute([':d'=>$snap_date]);
        $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
      } catch (PDOException $e) { $restrukSet = []; }

      // individual
      $indivMap = [];
      try {
        $stInd = $this->pdo->prepare("
          SELECT ci.no_rekening, ci.nilai_ckpn
          FROM ckpn_individual ci
          JOIN (
            SELECT no_rekening, MAX(created) AS created
            FROM ckpn_individual
            WHERE created <= :d
            GROUP BY no_rekening
          ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
        ");
        $stInd->execute([':d'=>$snap_date]);
        foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $r) $indivMap[$r['no_rekening']] = (float)$r['nilai_ckpn'];
      } catch (PDOException $e) {}

      // PD
      $pdMap = $loadPdMap($snap_date);

      foreach ($rows as $r) {
        $norek = $r['no_rekening'];
        $accSet[$norek] = true;

        $prod = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
        $dpd  = (int)($r['hari_menunggak'] ?? 0);
        $ead  = (float)($r['saldo_bank'] ?? 0.0);
        $os   = (float)($osByAcc[$norek] ?? 0.0);

        $code = $dpdToCode($dpd) ?? 'A';
        if (!isset($sumPerBucket[$code])) $sumPerBucket[$code] = ['noa'=>0,'os'=>0.0,'ckpn'=>0.0];

        $sumPerBucket[$code]['noa']++;
        $sumPerBucket[$code]['os']  += $os;

        if (isset($indivMap[$norek])) {
          $ck = (float)$indivMap[$norek];
        } else {
          $isRestruk = isset($restrukSet[$norek]);
          if ($dpd <= 7 && !$isRestruk) {
            $ck = 0.0;
          } else {
            $pd = ($prod !== null && isset($pdMap[$prod][$code])) ? (float)$pdMap[$prod][$code] : 0.0;
            $ck = round($ead * ($pd/100.0) * ($LGD_eff/100.0));
          }
        }
        $sumPerBucket[$code]['ckpn'] += $ck;
        $ckpnByAcc[$norek] = $ck;
      }
    }

    foreach ($sumPerBucket as &$v) { $v['os'] = (int)round($v['os']); $v['ckpn'] = (int)round($v['ckpn']); } unset($v);

    return ['perBucket'=>$sumPerBucket, 'ckpnByAcc'=>$ckpnByAcc, 'accSet'=>$accSet, 'osByAcc'=>$osByAcc];
  };

  // ===== Jalankan untuk closing_date & harian_date =====
  $M1  = $computeForDate($closing_date, $kode_filter);
  if (isset($M1['error'])) return sendResponse(500, $M1['error'], null);

  $CUR = $computeForDate($harian_date,  $kode_filter);
  if (isset($CUR['error'])) return sendResponse(500, $CUR['error'], null);

  // ===== O_LUNAS: rekening ada di M-1 tapi hilang di current =====
  $oLunas_noa = 0;
  $oLunas_os  = 0;
  $oLunas_ckpn_m1 = 0;
  foreach ($M1['accSet'] as $acc => $_) {
    if (!isset($CUR['accSet'][$acc])) {
      $oLunas_noa++;
      $oLunas_os      += (int)round($M1['osByAcc'][$acc]   ?? 0);
      $oLunas_ckpn_m1 += (int)round($M1['ckpnByAcc'][$acc] ?? 0);
    }
  }

  // ===== REALISASI (awal bulan s/d harian_date) =====
  $realisasiRow = ['noa'=>0, 'os'=>0];
  $start_month = date('Y-m-01', strtotime($harian_date));
  try {
    [$ds, $de] = $dayRange($harian_date);
    $sqlReal = "SELECT COUNT(*) AS noa, COALESCE(SUM(baki_debet),0) AS os
                FROM nominatif
                WHERE created >= :ds AND created < :de
                  AND tgl_realisasi BETWEEN :s AND :e";
    if ($kode_filter !== null) $sqlReal .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
    else $sqlReal .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'";
    $stR = $this->pdo->prepare($sqlReal);
    $paramsR = [':ds'=>$ds, ':de'=>$de, ':s'=>$start_month, ':e'=>$harian_date];
    if ($kode_filter !== null) $paramsR[':kc'] = $kode_filter;
    $stR->execute($paramsR);
    $tmp = $stR->fetch(PDO::FETCH_ASSOC);
    if ($tmp) $realisasiRow = ['noa'=>(int)$tmp['noa'], 'os'=>(int)$tmp['os']];
  } catch (PDOException $e) {
    $realisasiRow = ['noa'=>0,'os'=>0];
  }

  // ===== REALISASI set-based (akun baru di CUR vs M1) → untuk koreksi INC DPD A
  $real_set_noa = 0; $real_set_os = 0;
  foreach ($CUR['accSet'] as $acc => $_) {
    if (!isset($M1['accSet'][$acc])) {
      $real_set_noa++;
      $real_set_os += (int)round($CUR['osByAcc'][$acc] ?? 0);
    }
  }

  // ===== Susun output baris A..N + subtotal + O_LUNAS =====
  $order = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
  $rowsOut = [];

  // baris Realisasi
  $rowsOut[] = [
    'dpd_code'   => 'REALISASI',
    'dpd_name'   => 'Realisasi (awal bulan s/d tanggal laporan)',
    'status_tag' => null,
    'noa_m1'     => null,
    'os_m1'      => null,
    'noa_curr'   => $realisasiRow['noa'],
    'os_curr'    => $realisasiRow['os'],
    'inc_noa'    => $realisasiRow['noa'],
    'inc_os'     => $realisasiRow['os'],
    'inc_pct'    => null,
    'ckpn_m1'    => null,
    'ckpn_curr'  => null,
    'ckpn_inc'   => null
  ];

  $totSC = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];
  $totFE = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];
  $totBE = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];

  $pushRow = function($code,$name,$m1,$cur,$isA=false,$realNoa=0,$realOs=0) use (&$rowsOut,$tagMap) {
    $noa_m1 = $m1['noa'] ?? 0; $os_m1 = $m1['os'] ?? 0; $ck_m1 = $m1['ckpn'] ?? 0;
    $noa_cu = $cur['noa'] ?? 0; $os_cu = $cur['os'] ?? 0; $ck_cu = $cur['ckpn'] ?? 0;

    $inc_noa = $noa_cu - $noa_m1;
    $inc_os  = $os_cu  - $os_m1;

    // aturan khusus DPD 0 (A): tambah realisasi (akun baru)
    if ($isA) {
      $inc_noa += $realNoa;
      $inc_os  += $realOs;
    }

    $inc_pct = ($os_m1 > 0) ? round(($inc_os / $os_m1) * 100, 2) : null;

    $rowsOut[] = [
      'dpd_code'   => $code,
      'dpd_name'   => $name,
      'status_tag' => (strlen($code)===1 ? ($tagMap[$code] ?? null) : null),
      'noa_m1'     => (int)$noa_m1,
      'os_m1'      => (int)$os_m1,
      'noa_curr'   => (int)$noa_cu,
      'os_curr'    => (int)$os_cu,
      'inc_noa'    => (int)$inc_noa,
      'inc_os'     => (int)$inc_os,
      'inc_pct'    => $inc_pct,
      'ckpn_m1'    => (int)$ck_m1,
      'ckpn_curr'  => (int)$ck_cu,
      'ckpn_inc'   => (int)($ck_cu - $ck_m1),
    ];
  };

  foreach ($order as $code) {
    $m1  = $M1['perBucket'][$code]  ?? ['noa'=>0,'os'=>0,'ckpn'=>0];
    $cur = $CUR['perBucket'][$code] ?? ['noa'=>0,'os'=>0,'ckpn'=>0];

    $isA = ($code === 'A');
    $pushRow($code, $nameMap[$code] ?? $code, $m1, $cur, $isA, $real_set_noa, $real_set_os);

    $tag = $tagMap[$code] ?? null;
    if ($tag === 'SC') {
      $totSC['noa_m1'] += $m1['noa']; $totSC['os_m1'] += $m1['os'];
      $totSC['noa_cur']+= $cur['noa'];$totSC['os_cur']+= $cur['os'];
      $totSC['ck_m1']  += $m1['ckpn'];$totSC['ck_cur'] += $cur['ckpn'];
    } elseif ($tag === 'FE') {
      $totFE['noa_m1'] += $m1['noa']; $totFE['os_m1'] += $m1['os'];
      $totFE['noa_cur']+= $cur['noa'];$totFE['os_cur']+= $cur['os'];
      $totFE['ck_m1']  += $m1['ckpn'];$totFE['ck_cur'] += $cur['ckpn'];
    } elseif ($tag === 'BE') {
      $totBE['noa_m1'] += $m1['noa']; $totBE['os_m1'] += $m1['os'];
      $totBE['noa_cur']+= $cur['noa'];$totBE['os_cur']+= $cur['os'];
      $totBE['ck_m1']  += $m1['ckpn'];$totBE['ck_cur'] += $cur['ckpn'];
    }

    // subtotal
    if ($code === 'B') {
      $rowsOut[] = [
        'dpd_code'  => 'TOTAL_SC',
        'dpd_name'  => 'TOTAL SC',
        'status_tag'=> null,
        'noa_m1'    => (int)$totSC['noa_m1'],
        'os_m1'     => (int)$totSC['os_m1'],
        'noa_curr'  => (int)$totSC['noa_cur'],
        'os_curr'   => (int)$totSC['os_cur'],
        'inc_noa'   => (int)($totSC['noa_cur'] - $totSC['noa_m1']),
        'inc_os'    => (int)($totSC['os_cur']  - $totSC['os_m1']),
        'inc_pct'   => ($totSC['os_m1']>0) ? round((($totSC['os_cur']-$totSC['os_m1'])/$totSC['os_m1'])*100,2) : null,
        'ckpn_m1'   => (int)$totSC['ck_m1'],
        'ckpn_curr' => (int)$totSC['ck_cur'],
        'ckpn_inc'  => (int)($totSC['ck_cur'] - $totSC['ck_m1'])
      ];
    }
    if ($code === 'G') {
      $rowsOut[] = [
        'dpd_code'  => 'TOTAL_FE',
        'dpd_name'  => 'TOTAL FE',
        'status_tag'=> null,
        'noa_m1'    => (int)$totFE['noa_m1'],
        'os_m1'     => (int)$totFE['os_m1'],
        'noa_curr'  => (int)$totFE['noa_cur'],
        'os_curr'   => (int)$totFE['os_cur'],
        'inc_noa'   => (int)($totFE['noa_cur'] - $totFE['noa_m1']),
        'inc_os'    => (int)($totFE['os_cur']  - $totFE['os_m1']),
        'inc_pct'   => ($totFE['os_m1']>0) ? round((($totFE['os_cur']-$totFE['os_m1'])/$totFE['os_m1'])*100,2) : null,
        'ckpn_m1'   => (int)$totFE['ck_m1'],
        'ckpn_curr' => (int)$totFE['ck_cur'],
        'ckpn_inc'  => (int)($totFE['ck_cur'] - $totFE['ck_m1'])
      ];
    }
    if ($code === 'N') {
      $rowsOut[] = [
        'dpd_code'  => 'TOTAL_BE',
        'dpd_name'  => 'TOTAL BE',
        'status_tag'=> null,
        'noa_m1'    => (int)$totBE['noa_m1'],
        'os_m1'     => (int)$totBE['os_m1'],
        'noa_curr'  => (int)$totBE['noa_cur'],
        'os_curr'   => (int)$totBE['os_cur'],
        'inc_noa'   => (int)($totBE['noa_cur'] - $totBE['noa_m1']),
        'inc_os'    => (int)($totBE['os_cur']  - $totBE['os_m1']),
        'inc_pct'   => ($totBE['os_m1']>0) ? round((($totBE['os_cur']-$totBE['os_m1'])/$totBE['os_m1'])*100,2) : null,
        'ckpn_m1'   => (int)$totBE['ck_m1'],
        'ckpn_curr' => (int)$totBE['ck_cur'],
        'ckpn_inc'  => (int)($totBE['ck_cur'] - $totBE['ck_m1'])
      ];
    }
  }

  // O_LUNAS: actual berisi NOA & OS dari rekening M-1 yang hilang (OS pakai M-1)
  $rowsOut[] = [
    'dpd_code'   => 'O',
    'dpd_name'   => 'O_Lunas',
    'status_tag' => null,
    'noa_m1'     => null, 'os_m1'=>null,
    'noa_curr'   => (int)$oLunas_noa,
    'os_curr'    => (int)$oLunas_os,
    'inc_noa'    => null, 'inc_os'=>null, 'inc_pct'=>null,
    'ckpn_m1'    => (int)$oLunas_ckpn_m1,
    'ckpn_curr'  => 0,
    'ckpn_inc'   => (int)(0 - $oLunas_ckpn_m1)
  ];

  // GRAND TOTAL (A..N saja)
  $grand = ['noa_m1'=>0,'os_m1'=>0,'noa_cur'=>0,'os_cur'=>0,'ck_m1'=>0,'ck_cur'=>0];
  foreach (['A','B','C','D','E','F','G','H','I','J','K','L','M','N'] as $code) {
    $m1  = $M1['perBucket'][$code]  ?? ['noa'=>0,'os'=>0,'ckpn'=>0];
    $cur = $CUR['perBucket'][$code] ?? ['noa'=>0,'os'=>0,'ckpn'=>0];
    $grand['noa_m1'] += $m1['noa'];  $grand['os_m1'] += $m1['os'];  $grand['ck_m1'] += $m1['ckpn'];
    $grand['noa_cur']+= $cur['noa']; $grand['os_cur']+= $cur['os']; $grand['ck_cur']+= $cur['ckpn'];
  }

  // info sumber
  $srcM1  = $hasSnapshot($closing_date, $kode_filter) ? 'snapshot' : 'compute';
  $srcCUR = $hasSnapshot($harian_date,  $kode_filter) ? 'snapshot' : 'compute';

  return sendResponse(200,'OK', [
    'closing_date' => $closing_date,
    'harian_date'  => $harian_date,
    'kode_kantor'  => $kode_filter,
    'realisasi_row'=> $realisasiRow,
    'rows' => $rowsOut,
    'grand_total' => [
      'noa_m1'    => (int)$grand['noa_m1'],
      'os_m1'     => (int)$grand['os_m1'],
      'noa_curr'  => (int)$grand['noa_cur'],
      'os_curr'   => (int)$grand['os_cur'],
      'inc_noa'   => (int)($grand['noa_cur'] - $grand['noa_m1']),
      'inc_os'    => (int)($grand['os_cur']  - $grand['os_m1']),
      'inc_pct'   => ($grand['os_m1']>0) ? round((($grand['os_cur']-$grand['os_m1'])/$grand['os_m1'])*100,2) : null,
      'ckpn_m1'   => (int)$grand['ck_m1'],
      'ckpn_curr' => (int)$grand['ck_cur'],
      'ckpn_inc'  => (int)($grand['ck_cur'] - $grand['ck_m1'])
    ],
    'source' => ['closing'=>$srcM1, 'current'=>$srcCUR]
  ]);
}










// public function getRekapCkpnPerProduk($input = null) {
//   // --- Body: harian_date (wajib) + kode_cabang (opsional) + source (opsional)
//   $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
//   $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };
//   $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('last day of previous month'));
//   $kode_filter_raw = $body['kode_cabang'] ?? null;
//   $kode_filter = ($kode_filter_raw === null || $kode_filter_raw === '')
//                   ? null
//                   : str_pad((string)$kode_filter_raw, 3, '0', STR_PAD_LEFT);
//   $force = strtolower(trim($body['source'] ?? '')); // 'snapshot' | 'compute' | ''

//   // --- Master nama produk
//   $namaProduk = [];
//   try {
//     $q = $this->pdo->query("SELECT kode_produk, nama_produk FROM produk_kredit");
//     foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $m) {
//       $namaProduk[(int)$m['kode_produk']] = $m['nama_produk'];
//     }
//   } catch (PDOException $e) {}

//   // --- Helper band
//   $bandOf = function(int $dpd){
//     if($dpd<=30) return '0-30';
//     if($dpd<=90) return '31-90';
//     if($dpd<=180) return '91-180';
//     if($dpd<=360) return '181-360';
//     return '>360';
//   };
//   $LGD_DEFAULT = 59.48;

//   // ============================ SNAPSHOT? ============================
//   $hasSnapshot = false;
//   if ($force !== 'compute') {
//     try {
//       $st = $this->pdo->prepare("SELECT COUNT(1) FROM nominatif_ckpn WHERE created = :d");
//       $st->execute([':d' => $harian_date]);
//       $hasSnapshot = ((int)$st->fetchColumn() > 0);
//       if ($force === 'snapshot' && !$hasSnapshot) {
//         return sendResponse(404, "Snapshot nominatif_ckpn untuk {$harian_date} tidak ditemukan.", null);
//       }
//     } catch (PDOException $e) {
//       return sendResponse(500, "DB Error (cek snapshot): ".$e->getMessage(), null);
//     }
//   }

//   if ($hasSnapshot) {
//     try {
//       $sql = "
//         SELECT
//           no_rekening, kode_cabang, kode_produk, produk_kredit,
//           hari_menunggak, ead, pd, lgd, nilai_ckpn,
//           metode_penghitungan, keterangan
//         FROM nominatif_ckpn
//         WHERE created = :d
//       ";
//       $params = [':d' => $harian_date];
//       if ($kode_filter !== null) {
//         $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
//         $params[':kc'] = $kode_filter;
//       } else {
//         $sql .= " AND kode_cabang <> '000'";
//       }
//       $st = $this->pdo->prepare($sql);
//       $st->execute($params);
//       $rows = $st->fetchAll(PDO::FETCH_ASSOC);

//       $out = [];
//       foreach ($rows as $r) {
//         $prod = ($r['kode_produk'] === null || $r['kode_produk']==='') ? null : (int)$r['kode_produk'];
//         if ($prod === null) continue;

//         if (!isset($out[$prod])) {
//           $out[$prod] = [
//             'product_code'          => (string)$prod,
//             'nama_produk'           => $namaProduk[$prod] ?? ($r['produk_kredit'] ?? null),
//             'noa'                   => 0,
//             'ead'                   => 0.0,
//             'pd_wavg_percent'       => 0.0,        // wavg dari collective
//             'lgd_percent'           => $LGD_DEFAULT,
//             'ckpn_individual'       => 0,
//             'ckpn_collective'       => 0,          // <-- header collective (AKAN diakumulasi)
//             '_ead_sum_collective'   => 0.0,
//             '_ead_x_pd_collective'  => 0.0,
//             'detail'=>[
//               'ASSET BAIK (0-7 non-restruk)'=>['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0],
//               '0-30'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
//               '31-90'  => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
//               '91-180' => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
//               '181-360'=> ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
//               '>360'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0,'_ead_pd'=>0.0],
//             ]
//           ];
//         }

//         $dpd = (int)($r['hari_menunggak'] ?? 0);
//         $ead = (float)($r['ead'] ?? 0);
//         $pd  = (float)($r['pd'] ?? 0);
//         $met = strtoupper(trim((string)$r['metode_penghitungan']));
//         $ket = strtoupper(trim((string)($r['keterangan'] ?? '')));

//         $out[$prod]['noa']++;
//         $out[$prod]['ead'] += $ead;

//         // INDIV
//         if (strpos($met, 'INDIV') === 0) {
//           $out[$prod]['ckpn_individual'] += (float)$r['nilai_ckpn'];
//           continue;
//         }

//         // COLLECTIVE/KOLEKTIF
//         if (strpos($met, 'COLL') === 0 || strpos($met, 'KOLE') === 0) {
//           if (in_array($ket, ['ASET BAIK','ASSET BAIK'], true)) {
//             $g =& $out[$prod]['detail']['ASSET BAIK (0-7 non-restruk)'];
//             $g['noa']++; $g['ead_sum'] += $ead;
//           } else {
//             $band = $bandOf($dpd);
//             $g =& $out[$prod]['detail'][$band];
//             $g['noa']++;
//             $g['ead_sum'] += $ead;
//             $g['ckpn_collective'] += (float)$r['nilai_ckpn'];

//             // >>> FIX PENTING: akumulasi ke header produk
//             $out[$prod]['ckpn_collective'] += (float)$r['nilai_ckpn'];

//             // PD wavg per band & total collective
//             $g['_ead_pd'] += $ead * $pd;
//             $out[$prod]['_ead_sum_collective']  += $ead;
//             $out[$prod]['_ead_x_pd_collective'] += $ead * $pd;
//           }
//         }
//       }

//       // Finalisasi & rounding
//       foreach ($out as &$p) {
//         $p['pd_wavg_percent'] = $p['_ead_sum_collective'] > 0
//           ? round($p['_ead_x_pd_collective'] / $p['_ead_sum_collective'], 2) : 0.00;
//         unset($p['_ead_sum_collective'], $p['_ead_x_pd_collective']);

//         // PD wavg per band + rounding nilai
//         foreach ($p['detail'] as $label => &$g) {
//           if (isset($g['_ead_pd'])) {
//             $g['pd_percent'] = $g['ead_sum'] > 0 ? round($g['_ead_pd'] / $g['ead_sum'], 2) : 0.00;
//             unset($g['_ead_pd']);
//           }
//           $g['ead_sum']         = (int)round($g['ead_sum']);
//           $g['ckpn_collective'] = (int)round($g['ckpn_collective']);
//         } unset($g);

//         // Safety net (kalau header belum sempat keakumulasi)
//         if ((int)$p['ckpn_collective'] === 0) {
//           $sum = 0;
//           foreach ($p['detail'] as $lbl => $g) {
//             if ($lbl !== 'ASSET BAIK (0-7 non-restruk)') $sum += (int)$g['ckpn_collective'];
//           }
//           $p['ckpn_collective'] = (int)$sum;
//         }

//         $p['ead']              = (int)round($p['ead']);
//         $p['ckpn_individual']  = (int)round($p['ckpn_individual']);
//         $p['ckpn_collective']  = (int)round($p['ckpn_collective']);
//       } unset($p);

//       ksort($out, SORT_NUMERIC);
//       return sendResponse(200,'OK (snapshot)', [
//         'harian_date'  => $harian_date,
//         'kode_cabang'  => $kode_filter,
//         'produk'       => array_values($out)
//       ]);
//     } catch (PDOException $e) {
//       return sendResponse(500, "DB Error (snapshot): ".$e->getMessage(), null);
//     }
//   }

//   // ============================ FALLBACK COMPUTE ============================
//   // nominatif (filter cabang)
//   $baseSql = "SELECT no_rekening, kode_cabang, kode_produk, hari_menunggak, saldo_bank
//               FROM nominatif
//               WHERE created = :d";
//   if ($kode_filter !== null) {
//     $baseSql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = :kc";
//   } else {
//     $baseSql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'";
//   }
//   try {
//     $st = $this->pdo->prepare($baseSql);
//     $params = [':d' => $harian_date];
//     if ($kode_filter !== null) $params[':kc'] = $kode_filter;
//     $st->execute($params);
//     $rows = $st->fetchAll(PDO::FETCH_ASSOC);
//   } catch (PDOException $e) {
//     return sendResponse(500, 'DB Error (nominatif): '.$e->getMessage(), null);
//   }

//   // master bucket
//   $buckets = [];
//   try {
//     foreach ($this->pdo->query("SELECT dpd_code,dpd_name,min_day,max_day FROM ref_dpd_bucket ORDER BY min_day")->fetchAll(PDO::FETCH_ASSOC) as $b) {
//       $buckets[] = ['code'=>$b['dpd_code'],'name'=>$b['dpd_name'],'min'=>(int)$b['min_day'],'max'=>is_null($b['max_day'])?null:(int)$b['max_day']];
//     }
//   } catch (PDOException $e) {}

//   // PD product x bucket
//   $pdMap = [];
//   try {
//     foreach ($this->pdo->query("SELECT product_code,dpd_code,pd_percent FROM pd_current")->fetchAll(PDO::FETCH_ASSOC) as $p) {
//       $pdMap[(int)$p['product_code']][$p['dpd_code']] = (float)str_replace(',', '.', (string)$p['pd_percent']);
//     }
//   } catch (PDOException $e) {}

//   // RESTRUK terbaru <= tanggal
//   $restrukSet = [];
//   try {
//     $stRes = $this->pdo->prepare("
//       SELECT nr.no_rekening
//       FROM nom_restruk nr
//       JOIN (
//         SELECT no_rekening, MAX(created) AS created
//         FROM nom_restruk
//         WHERE created <= :d
//         GROUP BY no_rekening
//       ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
//     ");
//     $stRes->execute([':d'=>$harian_date]);
//     $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC), 'no_rekening'), true);
//   } catch (PDOException $e) {}

//   // INDIVIDUAL terbaru <= tanggal
//   $indivMap = [];
//   try {
//     $stInd = $this->pdo->prepare("
//       SELECT ci.no_rekening, ci.nilai_ckpn
//       FROM ckpn_individual ci
//       JOIN (
//         SELECT no_rekening, MAX(created) AS created
//         FROM ckpn_individual
//         WHERE created <= :d
//         GROUP BY no_rekening
//       ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
//     ");
//     $stInd->execute([':d'=>$harian_date]);
//     foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $r) { $indivMap[$r['no_rekening']] = (float)$r['nilai_ckpn']; }
//   } catch (PDOException $e) {}

//   // peta PD untuk header band (ambil yang pertama tersedia)
//   $bandBuckets = [
//     '0-30'   => ['A','B'],
//     '31-90'  => ['C','D'],
//     '91-180' => ['E','F','G'],
//     '181-360'=> ['H','I','J','K','L','M'],
//     '>360'   => ['N'],
//   ];
//   $getBandPd = function(int $prod, string $band) use ($bandBuckets, $pdMap): float {
//     if (!isset($pdMap[$prod])) return 0.0;
//     foreach ($bandBuckets[$band] as $code) {
//       if (isset($pdMap[$prod][$code])) return (float)$pdMap[$prod][$code];
//     }
//     return 0.0;
//   };

//   $out = [];
//   foreach ($rows as $r) {
//     $noRek = $r['no_rekening'];
//     $prod  = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
//     if ($prod === null) continue;
//     $dpd = (int)($r['hari_menunggak'] ?? 0);
//     $ead = (float)($r['saldo_bank'] ?? 0);

//     if (!isset($out[$prod])) {
//       $out[$prod] = [
//         'product_code'          => (string)$prod,
//         'nama_produk'           => $namaProduk[$prod] ?? null,
//         'noa'                   => 0,
//         'ead'                   => 0.0,
//         'pd_wavg_percent'       => 0.0,
//         'lgd_percent'           => $LGD_DEFAULT,
//         'ckpn_individual'       => 0,
//         'ckpn_collective'       => 0,
//         '_ead_sum_collective'   => 0.0,
//         '_ead_x_pd_collective'  => 0.0,
//         'detail'=>[
//           'ASSET BAIK (0-7 non-restruk)'=>['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>0.0,'ckpn_collective'=>0],
//           '0-30'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'0-30'),   'ckpn_collective'=>0],
//           '31-90'  => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'31-90'),  'ckpn_collective'=>0],
//           '91-180' => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'91-180'), 'ckpn_collective'=>0],
//           '181-360'=> ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'181-360'),'ckpn_collective'=>0],
//           '>360'   => ['noa'=>0,'ead_sum'=>0.0,'pd_percent'=>$getBandPd($prod,'>360'),  'ckpn_collective'=>0],
//         ]
//       ];
//     }

//     $out[$prod]['noa']++;
//     $out[$prod]['ead'] += $ead;

//     // INDIVIDUAL
//     if (isset($indivMap[$noRek])) {
//       $out[$prod]['ckpn_individual'] += $indivMap[$noRek];
//       continue;
//     }

//     // ASSET BAIK
//     $isRestruk = isset($restrukSet[$noRek]);
//     if ($dpd <= 7 && !$isRestruk) {
//       $g =& $out[$prod]['detail']['ASSET BAIK (0-7 non-restruk)'];
//       $g['noa']++; $g['ead_sum'] += $ead;
//       continue;
//     }

//     // KOLEKTIF (PD×LGD×EAD)
//     $dpdCode = null;
//     foreach ($buckets as $b){
//       if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) { $dpdCode=$b['code']; break; }
//     }
//     $pd = ($dpdCode && isset($pdMap[$prod][$dpdCode])) ? (float)$pdMap[$prod][$dpdCode] : 0.0;

//     $band = $bandOf($dpd);
//     $g =& $out[$prod]['detail'][$band];
//     $g['noa']++; $g['ead_sum'] += $ead;

//     $ck = round($ead * ($pd/100.0) * ($LGD_DEFAULT/100.0));
//     $g['ckpn_collective'] += $ck;
//     $out[$prod]['ckpn_collective'] += $ck;

//     $out[$prod]['_ead_sum_collective']  += $ead;
//     $out[$prod]['_ead_x_pd_collective'] += $ead * $pd;
//   }

//   // finalize compute
//   foreach ($out as &$p) {
//     $p['pd_wavg_percent'] = $p['_ead_sum_collective'] > 0
//       ? round($p['_ead_x_pd_collective'] / $p['_ead_sum_collective'], 2) : 0.00;
//     unset($p['_ead_sum_collective'], $p['_ead_x_pd_collective']);

//     foreach ($p['detail'] as &$g) {
//       $g['ead_sum']         = (int)round($g['ead_sum']);
//       $g['ckpn_collective'] = (int)round($g['ckpn_collective']);
//     } unset($g);

//     $p['ead']              = (int)round($p['ead']);
//     $p['ckpn_individual']  = (int)round($p['ckpn_individual']);
//     $p['ckpn_collective']  = (int)round($p['ckpn_collective']);
//   } unset($p);

//   ksort($out, SORT_NUMERIC);
//   return sendResponse(200,'OK (compute)', [
//     'harian_date'  => $harian_date,
//     'kode_cabang'  => $kode_filter,
//     'produk'       => array_values($out)
//   ]);
// }


// wokoko bug
// public function getRekapCkpnNominatifPerCabang($input = null) {
//   // ---- Body: hanya harian_date
//   $body = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
//   $parseDate = function ($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; };
//   $harian_date = $parseDate($body['harian_date'] ?? null) ?: date('Y-m-d', strtotime('-1 day'));

//   // ---- Ambil nominatif snapshot harian_date
//   $sql = "
//     SELECT
//       no_rekening,
//       kode_cabang,
//       kode_produk,
//       hari_menunggak,
//       saldo_bank
//     FROM nominatif
//     WHERE created = :harian_date
//   ";
//   try {
//     $st = $this->pdo->prepare($sql);
//     $st->execute([':harian_date' => $harian_date]);
//     $rows = $st->fetchAll(PDO::FETCH_ASSOC);
//   } catch (PDOException $e) {
//     return sendResponse(500, "DB Error: ".$e->getMessage(), null);
//   }

//   // ---- Master kantor
//   $kantorMap = [];
//   try {
//     $q = $this->pdo->query("SELECT kode_kantor, nama_kantor FROM kode_kantor");
//     foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $m) {
//       $kode = str_pad((string)$m['kode_kantor'], 3, '0', STR_PAD_LEFT);
//       $kantorMap[$kode] = $m['nama_kantor'];
//     }
//   } catch (PDOException $e) {
//     $kantorMap = [];
//   }

//   // ---- Referensi
//   // Bucket DPD
//   $buckets = [];
//   $q = $this->pdo->query("SELECT dpd_code, dpd_name, min_day, max_day FROM ref_dpd_bucket ORDER BY min_day");
//   foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $b) {
//     $buckets[] = ['code'=>$b['dpd_code'],'name'=>$b['dpd_name'],'min'=>(int)$b['min_day'],'max'=>is_null($b['max_day'])?null:(int)$b['max_day']];
//   }

//   // PD product × bucket (normalize koma → titik)
//   $pdMap = [];
//   $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
//   foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $p) {
//     $pd = (float)str_replace(',', '.', (string)$p['pd_percent']);
//     $pdMap[(int)$p['product_code']][$p['dpd_code']] = $pd;
//   }

//   // RESTRUK TERBARU ≤ harian_date  (ganti snapshot fixed)
//   $stRes = $this->pdo->prepare("
//     SELECT nr.no_rekening
//     FROM nom_restruk nr
//     JOIN (
//       SELECT no_rekening, MAX(created) AS created
//       FROM nom_restruk
//       WHERE created <= :d
//       GROUP BY no_rekening
//     ) x ON x.no_rekening = nr.no_rekening AND x.created = nr.created
//   ");
//   $restrukSet = [];
//   try {
//     $stRes->execute([':d' => $harian_date]);
//     $restrukSet = array_fill_keys(array_column($stRes->fetchAll(PDO::FETCH_ASSOC), 'no_rekening'), true);
//   } catch (PDOException $e) {
//     $restrukSet = [];
//   }

//   // CKPN individual TERBARU (<= harian_date) — sudah benar
//   $stInd = $this->pdo->prepare("
//       SELECT ci.no_rekening, ci.nilai_ckpn
//       FROM ckpn_individual ci
//       JOIN (
//         SELECT no_rekening, MAX(created) AS created
//         FROM ckpn_individual
//         WHERE created <= :harian_date
//         GROUP BY no_rekening
//       ) x ON x.no_rekening = ci.no_rekening AND x.created = ci.created
//   ");
//   $stInd->execute([':harian_date' => $harian_date]);
//   $indivMap = [];
//   foreach ($stInd->fetchAll(PDO::FETCH_ASSOC) as $row) {
//     $indivMap[$row['no_rekening']] = (float)$row['nilai_ckpn'];
//   }

//   // ---- Rekap per cabang
//   $LGD = 59.48; // %
//   $rekap = [];  // keyed by kode_cabang

//   foreach ($rows as $r) {
//     $noRek = $r['no_rekening'];
//     $kcRaw = $r['kode_cabang'];
//     $kc    = str_pad((string)$kcRaw, 3, '0', STR_PAD_LEFT);
//     if ($kc === '000') continue; // exclude 000

//     $prod  = ($r['kode_produk'] === null || $r['kode_produk'] === '') ? null : (int)$r['kode_produk'];
//     $dpd   = isset($r['hari_menunggak']) ? (int)$r['hari_menunggak'] : 0;
//     $ead   = isset($r['saldo_bank']) ? (float)$r['saldo_bank'] : 0.0;

//     if (!isset($rekap[$kc])) {
//       $rekap[$kc] = [
//         'kode_cabang'            => $kc,
//         'nama_kantor'            => $kantorMap[$kc] ?? null,
//         'noa_total'              => 0,
//         'noa_individual'         => 0,
//         'nilai_ckpn_individual'  => 0.0,
//         'noa_asset_baik'         => 0,
//         'noa_kolektif'           => 0,
//         'nilai_ckpn_kolektif'    => 0.0,
//         'nilai_ckpn_total'       => 0.0
//       ];
//     }
//     $rekap[$kc]['noa_total']++;

//     // INDIVIDUAL
//     if (isset($indivMap[$noRek])) {
//       $ck = (float)$indivMap[$noRek];
//       $rekap[$kc]['noa_individual']++;
//       $rekap[$kc]['nilai_ckpn_individual'] += $ck;
//       continue;
//     }

//     // ASSET BAIK (≤7 hari & BUKAN restruk terbaru)
//     $isRestruk = isset($restrukSet[$noRek]);
//     if ($dpd <= 7 && !$isRestruk) {
//       $rekap[$kc]['noa_asset_baik']++;
//       continue; // CKPN = 0
//     }

//     // KOLEKTIF
//     $dpdCode = null;
//     foreach ($buckets as $b) {
//       if ($dpd >= $b['min'] && ($b['max'] === null || $dpd <= $b['max'])) { $dpdCode = $b['code']; break; }
//     }
//     $pd = 0.0;
//     if ($prod !== null && $dpdCode !== null && isset($pdMap[$prod][$dpdCode])) {
//       $pd = (float)$pdMap[$prod][$dpdCode];
//     }
//     $ck = round($ead * ($pd/100.0) * ($LGD/100.0));

//     $rekap[$kc]['noa_kolektif']++;
//     $rekap[$kc]['nilai_ckpn_kolektif'] += $ck;
//   }

//   // ---- Pembulatan & total konsolidasi + urutan 001..028
//   $ordered = [];
//   $total = [
//     'kode_cabang'            => null,
//     'nama_kantor'            => 'TOTAL KONSOLIDASI',
//     'noa_total'              => 0,
//     'noa_individual'         => 0,
//     'nilai_ckpn_individual'  => 0.0,
//     'noa_asset_baik'         => 0,
//     'noa_kolektif'           => 0,
//     'nilai_ckpn_kolektif'    => 0.0,
//     'nilai_ckpn_total'       => 0.0
//   ];

//   for ($i=1; $i<=28; $i++) {
//     $code = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
//     if (!isset($rekap[$code])) continue;

//     $rekap[$code]['nilai_ckpn_individual'] = (int)round($rekap[$code]['nilai_ckpn_individual']);
//     $rekap[$code]['nilai_ckpn_kolektif']   = (int)round($rekap[$code]['nilai_ckpn_kolektif']);
//     $rekap[$code]['nilai_ckpn_total']      = (int)($rekap[$code]['nilai_ckpn_individual'] + $rekap[$code]['nilai_ckpn_kolektif']);

//     $total['noa_total']              += (int)$rekap[$code]['noa_total'];
//     $total['noa_individual']         += (int)$rekap[$code]['noa_individual'];
//     $total['nilai_ckpn_individual']  += (int)$rekap[$code]['nilai_ckpn_individual'];
//     $total['noa_asset_baik']         += (int)$rekap[$code]['noa_asset_baik'];
//     $total['noa_kolektif']           += (int)$rekap[$code]['noa_kolektif'];
//     $total['nilai_ckpn_kolektif']    += (int)$rekap[$code]['nilai_ckpn_kolektif'];

//     $ordered[] = $rekap[$code];
//   }

//   $total['nilai_ckpn_total'] = (int)($total['nilai_ckpn_individual'] + $total['nilai_ckpn_kolektif']);
//   $ordered[] = $total;

//   return sendResponse(200, "OK", $ordered);
// }














































}