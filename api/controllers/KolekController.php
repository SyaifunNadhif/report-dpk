<?php

require_once __DIR__ . '/../helpers/response.php';

class KolekController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



  public function getRekapKolektabilitas($input = []) {
    // -------- 1) Parse & validasi --------
    $closing = $this->asDate($input['closing_date'] ?? null);
    $harian  = $this->asDate($input['harian_date']  ?? null);
    $kantor  = trim($input['kode_kantor'] ?? '');
    $kantor  = ($kantor === '' || $kantor === '000') ? null : $kantor;

    if (!$closing || !$harian) {
      sendResponse(400, "closing_date & harian_date wajib (YYYY-MM-DD)");
      return;
    }
    if (!$this->snapshotExists('nominatif', $closing) || !$this->snapshotExists('nominatif', $harian)) {
      sendResponse(200, "Snapshot tidak tersedia", ["params"=>["closing_date"=>$closing,"harian_date"=>$harian,"kode_kantor"=>$kantor], "data"=>null]);
      return;
    }

    // -------- 2) Subquery templates (positional ?) --------
    $subClosing = "(
      SELECT no_rekening, kode_cabang, kolektibilitas, baki_debet
      FROM nominatif
      WHERE created = ?".($kantor ? " AND kode_cabang = ?" : "")."
    )";

    $subHarian = "(
      SELECT no_rekening, kode_cabang, kolektibilitas, baki_debet
      FROM nominatif
      WHERE created = ?".($kantor ? " AND kode_cabang = ?" : "")."
    )";

    // -------- 3) Derived tables tanpa CTE --------
    // M-1 per kolek
    $m1Agg = "
      SELECT c.kolektibilitas AS kol, COUNT(*) AS noa, SUM(c.baki_debet) AS os
      FROM $subClosing c
      GROUP BY c.kolektibilitas
    ";

    // Actual per kolek (EXCLUDE Realisasi) -> hanya norek yang juga ada di closing
    $actAgg = "
      SELECT h.kolektibilitas AS kol, COUNT(*) AS noa, SUM(h.baki_debet) AS os
      FROM $subHarian h
      INNER JOIN $subClosing c2 ON c2.no_rekening = h.no_rekening
      GROUP BY h.kolektibilitas
    ";

    // Realisasi total (ada di harian tapi tidak di closing)
    $realisasiAgg = "
      SELECT COUNT(*) AS noa, SUM(h.baki_debet) AS os
      FROM $subHarian h
      LEFT JOIN $subClosing c3 ON c3.no_rekening = h.no_rekening
      WHERE c3.no_rekening IS NULL
    ";

    // Lunas per kolek (ada di closing tapi tidak di harian)
    $lunasByKolek = "
      SELECT c.kolektibilitas AS kol, COUNT(*) AS noa, SUM(c.baki_debet) AS os
      FROM $subClosing c
      LEFT JOIN $subHarian h2 ON h2.no_rekening = c.no_rekening
      WHERE h2.no_rekening IS NULL
      GROUP BY c.kolektibilitas
    ";

    // Lunas total (scalar)
    $lunasTotalAgg = "SELECT COALESCE(SUM(noa),0) AS noa, COALESCE(SUM(os),0) AS os FROM ($lunasByKolek) x";

    // Kategori urut + join agregat
    $sql = "
      SELECT
        cat.kol,
        COALESCE(m1.noa,0) AS m1_noa,
        COALESCE(m1.os,0)  AS m1_os,
        CASE WHEN cat.kol='Realisasi' THEN COALESCE(r.noa,0)
             WHEN cat.kol='Lunas'     THEN COALESCE(lt.noa,0)
             ELSE COALESCE(a.noa,0) END AS act_noa,
        CASE WHEN cat.kol='Realisasi' THEN COALESCE(r.os,0)
             WHEN cat.kol='Lunas'     THEN COALESCE(lt.os,0)
             ELSE COALESCE(a.os,0) END AS act_os
      FROM (
        SELECT 'Realisasi' AS kol, 0 AS urut UNION ALL
        SELECT 'L', 1 UNION ALL
        SELECT 'DP', 2 UNION ALL
        SELECT 'KL', 3 UNION ALL
        SELECT 'D', 4 UNION ALL
        SELECT 'M', 5 UNION ALL
        SELECT 'Lunas', 6
      ) cat
      LEFT JOIN ($m1Agg) m1 ON m1.kol = cat.kol
      LEFT JOIN ($actAgg) a ON a.kol  = cat.kol
      LEFT JOIN ($realisasiAgg) r ON cat.kol = 'Realisasi'
      LEFT JOIN ($lunasTotalAgg) lt ON cat.kol = 'Lunas'
      ORDER BY cat.urut
    ";

    // -------- 4) Susun parameter sesuai URUTAN '?' --------
    $params = [];
    // $m1Agg -> $subClosing
    $params[] = $closing; if ($kantor) $params[] = $kantor;
    // $actAgg -> $subHarian, lalu $subClosing
    $params[] = $harian;  if ($kantor) $params[] = $kantor;
    $params[] = $closing; if ($kantor) $params[] = $kantor;
    // $realisasiAgg -> $subHarian, lalu $subClosing
    $params[] = $harian;  if ($kantor) $params[] = $kantor;
    $params[] = $closing; if ($kantor) $params[] = $kantor;
    // $lunasByKolek (di dalam $lunasTotalAgg) -> $subClosing, lalu $subHarian
    $params[] = $closing; if ($kantor) $params[] = $kantor;
    $params[] = $harian;  if ($kantor) $params[] = $kantor;

    // -------- 5) Eksekusi --------
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // -------- 6) Rekap & NPL (Actual denom = L..M + Realisasi) --------
    $LM       = ['L','DP','KL','D','M'];
    $NPL_SET  = ['KL','D','M'];

    $tot_m1_noa = 0;   $tot_m1_os = 0.0;            // hanya L..M (M-1)
    $tot_h_noa_lm = 0; $tot_h_os_lm = 0.0;          // hanya L..M (Actual, tanpa Realisasi)
    $realisasi_noa = 0; $realisasi_os = 0.0;        // baris Realisasi

    $byKol = [];

    foreach ($rows as &$r) {
      $kol    = strtoupper(trim((string)($r['kol'] ?? '')));
      $m1noa  = (int)   ($r['m1_noa'] ?? 0);
      $m1os   = (float) ($r['m1_os']  ?? 0);
      $actnoa = (int)   ($r['act_noa']?? 0);
      $actos  = (float) ($r['act_os'] ?? 0);

      if (in_array($kol, $LM, true)) {
        $tot_m1_noa   += $m1noa;
        $tot_m1_os    += $m1os;
        $tot_h_noa_lm += $actnoa;
        $tot_h_os_lm  += $actos;
        $r['inc_os']   = $actos - $m1os;          // inc OS per baris (L..M)
      } elseif ($kol === 'REALISASI') {
        $realisasi_noa = $actnoa;
        $realisasi_os  = $actos;
        $r['inc_os']   = null;
      } else { // LUNAS
        $r['inc_os']   = null;
      }

      $byKol[$kol] = ['m1_os'=>$m1os, 'act_os'=>$actos];
    }
    unset($r);

    // TOTAL Actual = L..M + Realisasi  (ini yang dipakai di Excel)
    $total_actual_noa = $tot_h_noa_lm + $realisasi_noa;
    $total_actual_os  = $tot_h_os_lm  + $realisasi_os;

    // NPL:
    //  - Numerator  : KL + D + M
    //  - Denominator:
    //      * M-1   = total OS L..M (closing)
    //      * Actual= total OS L..M + Realisasi (harian)  ✅
    $npl_m1_os = (float)($byKol['KL']['m1_os'] ?? 0) + (float)($byKol['D']['m1_os'] ?? 0) + (float)($byKol['M']['m1_os'] ?? 0);
    $npl_h_os  = (float)($byKol['KL']['act_os'] ?? 0) + (float)($byKol['D']['act_os'] ?? 0) + (float)($byKol['M']['act_os'] ?? 0);

    $den_m1_os = (float)$tot_m1_os;
    $den_h_os  = (float)$tot_h_os_lm + (float)$realisasi_os;

    $npl_m1_pct = $den_m1_os > 0 ? round($npl_m1_os / $den_m1_os * 100, 2) : 0.0;
    $npl_h_pct  = $den_h_os  > 0 ? round($npl_h_os  / $den_h_os  * 100, 2) : 0.0;

    $payload = [
      "rows" => $rows,  // Realisasi, L, DP, KL, D, M, Lunas
      "total_osc" => [
        "m1_noa"  => $tot_m1_noa,
        "m1_os"   => $tot_m1_os,
        "act_noa" => $total_actual_noa,  // termasuk Realisasi
        "act_os"  => $total_actual_os,   // termasuk Realisasi
        "inc_os"  => $total_actual_os - $tot_m1_os
      ],
      "npl" => [
        "m1_pct"     => $npl_m1_pct,
        "actual_pct" => $npl_h_pct,
        "inc_pct"    => round($npl_h_pct - $npl_m1_pct, 2)
      ],
      // bantu debug kalau perlu
      "debug" => [
        "act_os_lm_only" => $tot_h_os_lm,
        "realisasi_os"   => $realisasi_os,
        "act_os_total"   => $total_actual_os
      ]
    ];

    sendResponse(200, "OK", [
      "params" => [
        "closing_date" => $closing,
        "harian_date"  => $harian,
        "kode_kantor"  => $kantor
      ],
      "data" => $payload
    ]);
  }

  public function getMigrasiKolektabilitas($input = []) {
    // ---- 1) Params & validasi
    $closing = $this->asDate($input['closing_date'] ?? null);
    $harian  = $this->asDate($input['harian_date']  ?? null);
    $kantor  = trim($input['kode_kantor'] ?? '');
    $kantor  = ($kantor === '' || $kantor === '000') ? null : $kantor;

    if (!$closing || !$harian) { sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)"); return; }
    if (!$this->snapshotExists('nominatif',$closing) || !$this->snapshotExists('nominatif',$harian)) {
        sendResponse(200,"Snapshot tidak tersedia",["params"=>compact('closing','harian','kantor'),"data"=>null]); return;
    }

    // ---- 2) Subquery template (positional ? biar aman dari HY093)
    $subClosing = "(
        SELECT no_rekening, kode_cabang, kolektibilitas, baki_debet
        FROM nominatif
        WHERE created = ?".($kantor ? " AND kode_cabang = ?" : "")."
    )";
    $subHarian = "(
        SELECT no_rekening, kode_cabang, kolektibilitas, baki_debet
        FROM nominatif
        WHERE created = ?".($kantor ? " AND kode_cabang = ?" : "")."
    )";

    // ---- 3) Aggregasi pair (closing LEFT JOIN harian)
    $sqlPairs = "
        SELECT
        c.kolektibilitas AS src_kol,
        COALESCE(h.kolektibilitas, 'LUNAS') AS dst_kol,
        COUNT(*) AS noa,
        SUM(c.baki_debet)             AS os_c,  -- OS closing (M-1)
        SUM(COALESCE(h.baki_debet,0)) AS os_h   -- OS harian (0 bila Lunas)
        FROM $subClosing c
        LEFT JOIN $subHarian h ON h.no_rekening = c.no_rekening
        WHERE c.kolektibilitas IN ('L','DP','KL','D','M')
        GROUP BY c.kolektibilitas, COALESCE(h.kolektibilitas, 'LUNAS')
    ";
    $params = [$closing]; if ($kantor) $params[]=$kantor;
    $params[] = $harian;  if ($kantor) $params[]=$kantor;

    $st = $this->pdo->prepare($sqlPairs);
    $st->execute($params);
    $pairs = $st->fetchAll(PDO::FETCH_ASSOC);

    // ---- 4) Bangun matriks + run_off & run_off_ansuran + share
    $SRC = ['L','DP','KL','D','M'];
    $DST = ['L','DP','KL','D','M','LUNAS'];

    // Index pairs: $pair[src][dst] = ['noa'=>..,'os_c'=>..,'os_h'=>..]
    $pair = [];
    foreach ($pairs as $r) {
        $s = (string)$r['src_kol']; $d = (string)$r['dst_kol'];
        $pair[$s][$d] = ['noa'=>(int)$r['noa'], 'os_c'=>(float)$r['os_c'], 'os_h'=>(float)$r['os_h']];
    }

    $rows = [];
    $totals = [
        'm1_noa'=>0, 'm1_os'=>0.0,
        'actual'=>array_fill_keys($DST, ['noa'=>0,'os'=>0.0]),
        'angsuran_os_total'=>0.0,          // Σ run_off_ansuran
        'pelunasan'=>['noa'=>0,'os'=>0.0], // total Lunas
        'run_off_total'=>0.0,              // Σ run_off
        'share_vs_angsuran_total'=>['L'=>0,'DP'=>0,'KL'=>0,'D'=>0,'M'=>0] // temp numerator
    ];

    foreach ($SRC as $src) {
        $m1_noa = 0; $m1_os = 0.0;

        $actual = [];
        $sumActualAll   = 0.0; // Σ(OS_h tujuan L..M) + OS_c LUNAS
        $sumActualOS_LM = 0.0; // Σ(OS_h tujuan L..M) — utk run_off
        $angsuranParts  = ['L'=>0.0,'DP'=>0.0,'KL'=>0.0,'D'=>0.0,'M'=>0.0];

        foreach ($DST as $dst) {
        $p = $pair[$src][$dst] ?? ['noa'=>0,'os_c'=>0.0,'os_h'=>0.0];

        $m1_noa += $p['noa'];
        $m1_os  += $p['os_c'];

        // OS actual cell: L..M pakai os_h; LUNAS pakai os_c
        $act_os = ($dst === 'LUNAS') ? $p['os_c'] : $p['os_h'];
        $actual[$dst] = ['noa'=>$p['noa'], 'os'=>$act_os, 'share'=>null];

        // total kolom
        $totals['actual'][$dst]['noa'] += $p['noa'];
        $totals['actual'][$dst]['os']  += $act_os;

        if ($dst !== 'LUNAS') {
            $angsuranParts[$dst] += max(0.0, $p['os_c'] - $p['os_h']); // kontribusi angsuran
            $sumActualAll   += $p['os_h'];
            $sumActualOS_LM += $p['os_h'];
        } else {
            $sumActualAll += $p['os_c']; // lunas pakai OS closing
            $totals['pelunasan']['noa'] += $p['noa'];
            $totals['pelunasan']['os']  += $p['os_c'];
        }
        }

        // run_off (tanpa Lunas) & run_off_ansuran (murni)
        $run_off         = $sumActualOS_LM - $m1_os;
        $run_off_ansuran = $m1_os - $sumActualAll; if ($run_off_ansuran < 0) $run_off_ansuran = 0.0;

        // % share per tujuan (L..M) terhadap angsuran baris
        $den = $run_off_ansuran;
        foreach (['L','DP','KL','D','M'] as $dst) {
        $num = $angsuranParts[$dst];
        $actual[$dst]['share'] = $den > 0 ? round($num / $den * 100, 2) : 0.0;
        $totals['share_vs_angsuran_total'][$dst] += $num; // akan dibagi total angsuran pada akhir
        }

        $rows[] = [
        'kol' => $src,
        'm1'  => ['noa'=>$m1_noa, 'os'=>$m1_os],
        'actual' => [
            'L'=>$actual['L'], 'DP'=>$actual['DP'], 'KL'=>$actual['KL'],
            'D'=>$actual['D'], 'M'=>$actual['M'], 'Lunas'=>$actual['LUNAS']
        ],
        'run_off'         => $run_off,
        'run_off_ansuran' => $run_off_ansuran
        ];

        // totals
        $totals['m1_noa'] += $m1_noa;
        $totals['m1_os']  += $m1_os;
        $totals['angsuran_os_total'] += $run_off_ansuran;
        $totals['run_off_total']     += $run_off;
    }

    // % share total per kolom tujuan (dibanding total angsuran murni)
    $denTot = $totals['angsuran_os_total'] > 0 ? $totals['angsuran_os_total'] : 0.0;
    $shareTot = [];
    foreach (['L','DP','KL','D','M'] as $dst) {
        $num = (float)$totals['share_vs_angsuran_total'][$dst];
        $shareTot[$dst] = $denTot > 0 ? round($num / $denTot * 100, 2) : 0.0;
    }
    $totals['share_vs_angsuran_total'] = $shareTot;

    // ---- 5) Response
    $payload = [
        'rows' => $rows,
        'totals' => [
        'm1_noa' => $totals['m1_noa'],
        'm1_os'  => $totals['m1_os'],
        'actual' => $totals['actual'],
        'angsuran_os_total' => $totals['angsuran_os_total'], // Σ run_off_ansuran
        'pelunasan' => $totals['pelunasan'],
        'run_off_total' => $totals['run_off_total'],         // Σ run_off
        'share_vs_angsuran_total' => $totals['share_vs_angsuran_total']
        ]
    ];

    sendResponse(200,"OK",[
        'params' => [
        'kode_kantor'  => $kantor,
        'closing_date' => $closing,
        'harian_date'  => $harian
        ],
        'data' => $payload
    ]);
  }

  // private function asDate($s) {
  //   if (!$s) return null;
  //   $t = strtotime($s);
  //   return $t ? date('Y-m-d', $t) : null;
  // }

  // private function snapshotExists($table, $created) {
  //   $st = $this->pdo->prepare("SELECT 1 FROM {$table} WHERE created = ? LIMIT 1");
  //   $st->execute([$created]);
  //   return (bool)$st->fetchColumn();
  // }


  // private function loadBuckets(): array {
  //   $rows = $this->pdo->query("
  //     SELECT dpd_code, dpd_name, min_day, max_day, status_tag
  //     FROM ref_dpd_bucket ORDER BY min_day
  //   ")->fetchAll(PDO::FETCH_ASSOC);
  //   $def = []; $name=[]; $tag=[];
  //   foreach ($rows as $r){
  //     $def[] = [
  //       'code'=>$r['dpd_code'],'name'=>$r['dpd_name'],
  //       'min'=>(int)$r['min_day'],'max'=>is_null($r['max_day'])?null:(int)$r['max_day'],
  //       'tag'=>$r['status_tag'] ?? null
  //     ];
  //     $name[$r['dpd_code']] = $r['dpd_name'];
  //     $tag[$r['dpd_code']]  = $r['status_tag'] ?? null;
  //   }
  //   return [$def,$name,$tag];
  // }

  // private function dpdToCode(int $dpd, array $defs): ?string {
  //   foreach ($defs as $b) {
  //     if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) return $b['code'];
  //   }
  //   return null;
  // }

  // private function dayRange(string $d): array {
  //   return [$d." 00:00:00", date('Y-m-d', strtotime("$d +1 day"))." 00:00:00"];
  // }

  // private function computeOSForDate(string $d, ?string $kc, array $defs): array {
  //   [$ds,$de] = $this->dayRange($d);
  //   $sql = "SELECT no_rekening, hari_menunggak, baki_debet
  //           FROM nominatif
  //           WHERE created >= ? AND created < ?";
  //   $params = [$ds,$de];
  //   if ($kc !== null) { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
  //   else { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }

  //   $st = $this->pdo->prepare($sql);
  //   $st->execute($params);

  //   $sumPer = []; $accSet=[]; $osByAcc=[]; $bucketByAcc=[];
  //   while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
  //     $acc = $r['no_rekening'];
  //     $dpd = (int)($r['hari_menunggak'] ?? 0);
  //     $os  = (float)($r['baki_debet'] ?? 0);
  //     $code = $this->dpdToCode($dpd,$defs) ?? 'A';
  //     if (!isset($sumPer[$code])) $sumPer[$code] = ['noa'=>0,'os'=>0.0];
  //     $sumPer[$code]['noa']++;
  //     $sumPer[$code]['os'] += $os;

  //     $accSet[$acc] = true;
  //     $osByAcc[$acc] = $os;
  //     $bucketByAcc[$acc] = $code;
  //   }
  //   foreach ($sumPer as &$v){ $v['os'] = (int)round($v['os']); } unset($v);

  //   return [
  //     'perBucket'   => $sumPer,
  //     'accSet'      => $accSet,
  //     'osByAcc'     => $osByAcc,
  //     'bucketByAcc' => $bucketByAcc
  //   ];
  // }

  // private function loadGlobalLGD(string $harian_date): float {
  //   try {
  //     $st = $this->pdo->prepare("
  //       SELECT lgd_percent FROM lgd_current
  //       WHERE created <= ? ORDER BY created DESC LIMIT 1
  //     ");
  //     $st->execute([$harian_date]);
  //     $v = $st->fetchColumn();
  //     return ($v!==false) ? (float)$v : 59.48;
  //   } catch (PDOException $e) { return 59.48; }
  // }

  // private function loadPdMap(string $d): array {
  //   $pdMap=[];
  //   try {
  //     $st = $this->pdo->prepare("
  //       SELECT p.product_code, p.dpd_code, p.pd_percent
  //       FROM pd_current p
  //       JOIN (
  //         SELECT product_code, dpd_code, MAX(created) AS created
  //         FROM pd_current
  //         WHERE created <= ?
  //         GROUP BY product_code, dpd_code
  //       ) x ON x.product_code=p.product_code AND x.dpd_code=p.dpd_code AND x.created=p.created
  //     ");
  //     $st->execute([$d]);
  //     foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r){
  //       $pdMap[(int)$r['product_code']][$r['dpd_code']] = (float)$r['pd_percent'];
  //     }
  //     if (!empty($pdMap)) return $pdMap;
  //   } catch (PDOException $e) {}
  //   try {
  //     $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
  //     foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r){
  //       $pdMap[(int)$r['product_code']][$r['dpd_code']] = (float)str_replace(',','.',$r['pd_percent']);
  //     }
  //   } catch (PDOException $e) {}
  //   return $pdMap;
  // }

  // private function hasSnapshot(string $d, ?string $kc): bool {
  //   try {
  //     [$ds,$de] = $this->dayRange($d);
  //     $sql = "SELECT COUNT(1) FROM nominatif_ckpn WHERE created >= ? AND created < ?";
  //     $params = [$ds,$de];
  //     if ($kc!==null){ $sql.=" AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
  //     $st = $this->pdo->prepare($sql); $st->execute($params);
  //     return ((int)$st->fetchColumn() > 0);
  //   } catch (PDOException $e) { return false; }
  // }

  /** hitung CKPN per bucket untuk 1 tanggal (pakai snapshot kalau ada; kalau tidak compute) */
  private function computeCKPNForDate(string $d, ?string $kc, array $defs): array {
    $sumPer=[]; $ckByAcc=[]; $accSet=[];
    $LGD = $this->loadGlobalLGD($d);

    // OS/EAD diperlukan kalau compute
    [$ds,$de] = $this->dayRange($d);
    $osByAcc=[];
    try{
      $sqlOS="SELECT no_rekening, saldo_bank, baki_debet, kode_cabang, hari_menunggak, kode_produk
              FROM nominatif WHERE created >= ? AND created < ?";
      $paramsOS=[$ds,$de];
      if ($kc!==null){ $sqlOS.=" AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $paramsOS[]=$kc; }
      else { $sqlOS.=" AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }
      $stOS=$this->pdo->prepare($sqlOS); $stOS->execute($paramsOS);
      $nom = $stOS->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e){ $nom=[]; }

    $useSnap = $this->hasSnapshot($d,$kc);
    if ($useSnap){
      try{
        $sql="SELECT no_rekening, hari_menunggak, nilai_ckpn FROM nominatif_ckpn
              WHERE created >= ? AND created < ?";
        $params=[$ds,$de];
        if ($kc!==null){ $sql.=" AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
        else { $sql.=" AND kode_cabang <> '000'"; }
        $st=$this->pdo->prepare($sql); $st->execute($params);
        while($r=$st->fetch(PDO::FETCH_ASSOC)){
          $acc=$r['no_rekening']; $accSet[$acc]=true;
          $code = $this->dpdToCode((int)$r['hari_menunggak'], $defs) ?? 'A';
          if (!isset($sumPer[$code])) $sumPer[$code]=['ckpn'=>0.0];
          $sumPer[$code]['ckpn'] += (float)($r['nilai_ckpn'] ?? 0);
          $ckByAcc[$acc] = (float)($r['nilai_ckpn'] ?? 0);
        }
      }catch(PDOException $e){ /* fallback kosong */ }
    } else {
      // compute
      // auxiliary: restruk & ckpn_individual & PD map
      $restruk=[]; $indiv=[]; $pdMap=$this->loadPdMap($d);
      try{
        $st=$this->pdo->prepare("
          SELECT nr.no_rekening
          FROM nom_restruk nr
          JOIN (SELECT no_rekening, MAX(created) AS created
                FROM nom_restruk WHERE created <= ? GROUP BY no_rekening) x
          ON x.no_rekening=nr.no_rekening AND x.created=nr.created
        "); $st->execute([$d]);
        $restruk = array_fill_keys(array_column($st->fetchAll(PDO::FETCH_ASSOC),'no_rekening'), true);
      }catch(PDOException $e){}

      try{
        $st=$this->pdo->prepare("
          SELECT ci.no_rekening, ci.nilai_ckpn
          FROM ckpn_individual ci
          JOIN (SELECT no_rekening, MAX(created) AS created
                FROM ckpn_individual WHERE created <= ? GROUP BY no_rekening) x
          ON x.no_rekening=ci.no_rekening AND x.created=ci.created
        "); $st->execute([$d]);
        foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r) $indiv[$r['no_rekening']] = (float)$r['nilai_ckpn'];
      }catch(PDOException $e){}

      foreach ($nom as $r){
        $acc=$r['no_rekening']; $accSet[$acc]=true;
        $dpd=(int)($r['hari_menunggak'] ?? 0);
        $prod = ($r['kode_produk']===''||$r['kode_produk']===null)?null:(int)$r['kode_produk'];
        $ead=(float)($r['saldo_bank'] ?? 0);
        $code=$this->dpdToCode($dpd,$defs) ?? 'A';
        if (!isset($sumPer[$code])) $sumPer[$code]=['ckpn'=>0.0];

        if (isset($indiv[$acc])) { $ck=$indiv[$acc]; }
        else {
          $isRestruk = isset($restruk[$acc]);
          if ($dpd <= 7 && !$isRestruk) $ck = 0.0;
          else {
            $pd = ($prod!==null && isset($pdMap[$prod][$code])) ? (float)$pdMap[$prod][$code] : 0.0;
            $ck = round($ead * ($pd/100.0) * ($LGD/100.0));
          }
        }
        $sumPer[$code]['ckpn'] += $ck;
        $ckByAcc[$acc] = $ck;
      }
    }

    foreach ($sumPer as &$v){ $v['ckpn'] = (int)round($v['ckpn']); } unset($v);
    return ['perBucket'=>$sumPer,'ckpnByAcc'=>$ckByAcc,'accSet'=>$accSet,'source'=>$useSnap?'snapshot':'compute'];
  }


  public function getBucketOsc($input = null){
    // ---- Params
    $b = is_array($input)?$input:(json_decode(file_get_contents('php://input'),true) ?: []);
    $closing = $this->asDate($b['closing_date'] ?? null);
    $harian  = $this->asDate($b['harian_date'] ?? null);
    $kc_raw  = $b['kode_kantor'] ?? null;
    $kc      = ($kc_raw===null || $kc_raw==='') ? null : str_pad((string)$kc_raw,3,'0',STR_PAD_LEFT);
    if (!$closing || !$harian) return sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)");

    // ---- Master bucket
    [$defs,$nameMap,$tagMap] = $this->loadBuckets();
    $order = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];

    // ---- Data per tanggal
    $M1  = $this->computeOSForDate($closing,$kc,$defs);
    $CUR = $this->computeOSForDate($harian ,$kc,$defs);

    // ===== EXCLUDE realisasi baru dari ACTUAL SEMUA BUCKET =====
    foreach ($CUR['accSet'] as $acc => $_) {
      if (!isset($M1['accSet'][$acc])) {
        $bucket = $CUR['bucketByAcc'][$acc] ?? null;
        if ($bucket !== null) {
          if (!isset($CUR['perBucket'][$bucket])) $CUR['perBucket'][$bucket] = ['noa'=>0,'os'=>0];
          $CUR['perBucket'][$bucket]['noa'] = max(0, (int)$CUR['perBucket'][$bucket]['noa'] - 1);
          $CUR['perBucket'][$bucket]['os']  = (int)max(0, (int)$CUR['perBucket'][$bucket]['os'] - (int)round($CUR['osByAcc'][$acc] ?? 0));
        }
        unset($CUR['accSet'][$acc]); // buang dari set
      }
    }

    // ---- O_LUNAS: akun ada di M-1 tapi tidak ada di current → actual os = OS M-1
    $o_noa = 0; $o_os = 0;
    foreach ($M1['accSet'] as $acc=>$_){
      if (!isset($CUR['accSet'][$acc])) {
        $o_noa++; $o_os += (int)round($M1['osByAcc'][$acc] ?? 0);
      }
    }

    // ---- Realisasi (awal bulan s/d harian_date) — untuk baris Realisasi, INC DPD 0, dan TOTAL_SC (+realisasi)
    $realisasi = ['noa'=>0,'os'=>0];
    $start_month = date('Y-m-01', strtotime($harian));
    [$ds,$de] = $this->dayRange($harian);
    $sqlR = "SELECT COUNT(*) AS noa, COALESCE(SUM(baki_debet),0) AS os
            FROM nominatif
            WHERE created >= ? AND created < ?
              AND tgl_realisasi BETWEEN ? AND ?";
    $paramsR = [$ds,$de,$start_month,$harian];
    if ($kc!==null){ $sqlR .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $paramsR[]=$kc; }
    else { $sqlR .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }
    $stR = $this->pdo->prepare($sqlR); $stR->execute($paramsR);
    if ($tmp = $stR->fetch(PDO::FETCH_ASSOC)) $realisasi = ['noa'=>(int)$tmp['noa'],'os'=>(int)$tmp['os']];

    // ---- Susun output
    $rows=[];

    // Baris Realisasi (INC dibuat 0 sesuai permintaan)
    $rows[] = [
      'dpd_code'=>'REALISASI',
      'dpd_name'=>'Realisasi (awal bulan s/d tanggal laporan)',
      'status_tag'=>null,
      'noa_m1'=>null,'os_m1'=>null,
      'noa_curr'=>$realisasi['noa'],'os_curr'=>$realisasi['os'],
      'inc_noa'=>0,'inc_os'=>0,'inc_pct'=>null
    ];

    $totSC=['noa_m1'=>0,'os_m1'=>0,'noa_c'=>0,'os_c'=>0];
    $totFE=['noa_m1'=>0,'os_m1'=>0,'noa_c'=>0,'os_c'=>0];
    $totBE=['noa_m1'=>0,'os_m1'=>0,'noa_c'=>0,'os_c'=>0];

    // --- penampung subtotal untuk GRAND TOTAL
    $row_tot_sc = ['noa_m1'=>0,'os_m1'=>0,'noa_curr'=>0,'os_curr'=>0];
    $row_tot_fe = ['noa_m1'=>0,'os_m1'=>0,'noa_curr'=>0,'os_curr'=>0];
    $row_tot_be = ['noa_m1'=>0,'os_m1'=>0,'noa_curr'=>0,'os_curr'=>0];

    $push = function($code,$name,$m1,$cur,$isA=false,$realNoa=0,$realOs=0) use (&$rows,$tagMap){
      $n1 = (int)($m1['noa'] ?? 0); $o1 = (int)($m1['os'] ?? 0);
      $nc = (int)($cur['noa'] ?? 0); $oc = (int)($cur['os'] ?? 0);
      $inc_noa = $nc - $n1; $inc_os = $oc - $o1;
      if ($isA){ $inc_noa += (int)$realNoa; $inc_os += (int)$realOs; } // aturan khusus DPD 0 (INC tambahkan realisasi)
      $rows[] = [
        'dpd_code'=>$code,'dpd_name'=>$name,'status_tag'=>(strlen($code)===1?($tagMap[$code]??null):null),
        'noa_m1'=>$n1,'os_m1'=>$o1,'noa_curr'=>$nc,'os_curr'=>$oc,
        'inc_noa'=>$inc_noa,'inc_os'=>$inc_os,
        'inc_pct'=> $o1>0 ? round($inc_os/$o1*100,2) : null
      ];
    };

    foreach ($order as $code){
      $m1  = $M1['perBucket'][$code]  ?? ['noa'=>0,'os'=>0];
      $cur = $CUR['perBucket'][$code] ?? ['noa'=>0,'os'=>0];

      // baris per bucket; DPD 0 (A) inc += realisasi
      $push($code, $nameMap[$code] ?? $code, $m1, $cur, $code==='A', $realisasi['noa'], $realisasi['os']);

      // Totals per cluster
      $tag = $tagMap[$code] ?? null;
      if ($tag==='SC'){ $totSC['noa_m1']+=$m1['noa']; $totSC['os_m1']+=$m1['os']; $totSC['noa_c']+=$cur['noa']; $totSC['os_c']+=$cur['os']; }
      elseif($tag==='FE'){ $totFE['noa_m1']+=$m1['noa']; $totFE['os_m1']+=$m1['os']; $totFE['noa_c']+=$cur['noa']; $totFE['os_c']+=$cur['os']; }
      elseif($tag==='BE'){ $totBE['noa_m1']+=$m1['noa']; $totBE['os_m1']+=$m1['os']; $totBE['noa_c']+=$cur['noa']; $totBE['os_c']+=$cur['os']; }

      // Subtotal SC setelah B — NOTE: actual SC + realisasi
      if ($code==='B'){
        $row_tot_sc = [
          'noa_m1'=>(int)$totSC['noa_m1'],
          'os_m1' =>(int)$totSC['os_m1'],
          'noa_curr'=>(int)$totSC['noa_c'] + (int)$realisasi['noa'],
          'os_curr' =>(int)$totSC['os_c']  + (int)$realisasi['os'],
        ];
        $rows[] = [
          'dpd_code'=>'TOTAL_SC','dpd_name'=>'TOTAL SC','status_tag'=>null,
          'noa_m1'=>$row_tot_sc['noa_m1'],'os_m1'=>$row_tot_sc['os_m1'],
          'noa_curr'=>$row_tot_sc['noa_curr'],'os_curr'=>$row_tot_sc['os_curr'],
          'inc_noa'=> $row_tot_sc['noa_curr'] - $row_tot_sc['noa_m1'],
          'inc_os' => $row_tot_sc['os_curr']  - $row_tot_sc['os_m1'],
          'inc_pct'=> $row_tot_sc['os_m1']>0 ? round((($row_tot_sc['os_curr']-$row_tot_sc['os_m1'])/$row_tot_sc['os_m1'])*100,2) : null
        ];
      }
      // Subtotal FE setelah G — TANPA realisasi
      if ($code==='G'){
        $row_tot_fe = [
          'noa_m1'=>(int)$totFE['noa_m1'],
          'os_m1' =>(int)$totFE['os_m1'],
          'noa_curr'=>(int)$totFE['noa_c'],
          'os_curr' =>(int)$totFE['os_c']
        ];
        $rows[] = [
          'dpd_code'=>'TOTAL_FE','dpd_name'=>'TOTAL FE','status_tag'=>null,
          'noa_m1'=>$row_tot_fe['noa_m1'],'os_m1'=>$row_tot_fe['os_m1'],
          'noa_curr'=>$row_tot_fe['noa_curr'],'os_curr'=>$row_tot_fe['os_curr'],
          'inc_noa'=>$row_tot_fe['noa_curr'] - $row_tot_fe['noa_m1'],
          'inc_os' =>$row_tot_fe['os_curr']  - $row_tot_fe['os_m1'],
          'inc_pct'=> $row_tot_fe['os_m1']>0 ? round((($row_tot_fe['os_curr']-$row_tot_fe['os_m1'])/$row_tot_fe['os_m1'])*100,2) : null
        ];
      }
      // Subtotal BE setelah N
      if ($code==='N'){
        $row_tot_be = [
          'noa_m1'=>(int)$totBE['noa_m1'],
          'os_m1' =>(int)$totBE['os_m1'],
          'noa_curr'=>(int)$totBE['noa_c'],
          'os_curr' =>(int)$totBE['os_c']
        ];
        $rows[] = [
          'dpd_code'=>'TOTAL_BE','dpd_name'=>'TOTAL BE','status_tag'=>null,
          'noa_m1'=>$row_tot_be['noa_m1'],'os_m1'=>$row_tot_be['os_m1'],
          'noa_curr'=>$row_tot_be['noa_curr'],'os_curr'=>$row_tot_be['os_curr'],
          'inc_noa'=>$row_tot_be['noa_curr'] - $row_tot_be['noa_m1'],
          'inc_os' =>$row_tot_be['os_curr']  - $row_tot_be['os_m1'],
          'inc_pct'=> $row_tot_be['os_m1']>0 ? round((($row_tot_be['os_curr']-$row_tot_be['os_m1'])/$row_tot_be['os_m1'])*100,2) : null
        ];
      }
    }

    // ===== GRAND TOTAL = TOTAL_SC + TOTAL_FE + TOTAL_BE (dipush ke rows)
    $gt_noa_m1 = (int)$row_tot_sc['noa_m1'] + (int)$row_tot_fe['noa_m1'] + (int)$row_tot_be['noa_m1'];
    $gt_os_m1  = (int)$row_tot_sc['os_m1']  + (int)$row_tot_fe['os_m1']  + (int)$row_tot_be['os_m1'];
    $gt_noa_c  = (int)$row_tot_sc['noa_curr'] + (int)$row_tot_fe['noa_curr'] + (int)$row_tot_be['noa_curr'];
    $gt_os_c   = (int)$row_tot_sc['os_curr']  + (int)$row_tot_fe['os_curr']  + (int)$row_tot_be['os_curr'];
    $gt_inc_noa = $gt_noa_c - $gt_noa_m1;
    $gt_inc_os  = $gt_os_c  - $gt_os_m1;

    $rows[] = [
      'dpd_code'=>'GRAND_TOTAL','dpd_name'=>'GRAND TOTAL','status_tag'=>null,
      'noa_m1'=>$gt_noa_m1,'os_m1'=>$gt_os_m1,
      'noa_curr'=>$gt_noa_c,'os_curr'=>$gt_os_c,
      'inc_noa'=>$gt_inc_noa,'inc_os'=>$gt_inc_os,
      'inc_pct'=> $gt_os_m1>0 ? round(($gt_inc_os/$gt_os_m1)*100,2) : null
    ];

    // O_LUNAS row (tetap di-append setelah GRAND TOTAL)
    $rows[] = [
      'dpd_code'=>'O', 'dpd_name'=>'O_Lunas', 'status_tag'=>null,
      'noa_m1'=>null,'os_m1'=>null,
      'noa_curr'=>(int)$o_noa, 'os_curr'=>(int)$o_os,
      'inc_noa'=>null,'inc_os'=>null,'inc_pct'=>null
    ];

    // ===== FLOW RATE (Actual, Appetite, INC)
    // boleh override appetite via $b['fr_appetite'] = [0.05,0.50,0.80,0.20,0.75,0.80,0.85]
    $appDefaults = [0.05,0.50,0.80,0.20,0.75,0.80,0.85];
    $apps = (isset($b['fr_appetite']) && is_array($b['fr_appetite'])) ? $b['fr_appetite'] : $appDefaults;

    $pairs = [
      ['from'=>'A','to'=>'B','label'=>'FR 0→30'],   // index 0
      ['from'=>'B','to'=>'C','label'=>'FR 30→60'],  // index 1
      ['from'=>'C','to'=>'D','label'=>'FR 60→90'],
      ['from'=>'D','to'=>'E','label'=>'FR 90→120'],
      ['from'=>'E','to'=>'F','label'=>'FR 120→150'],
      ['from'=>'F','to'=>'G','label'=>'FR 150→180'],
      ['from'=>'G','to'=>'H','label'=>'FR 180→210'],
    ];
    $flow_rate = [];
    foreach ($pairs as $i=>$p){
      $den = (int)round(($M1['perBucket'][$p['from']]['os'] ?? 0));
      $num = (int)round(($CUR['perBucket'][$p['to']]['os']  ?? 0));
      $actual = ($den>0) ? round($num / $den * 100, 2) : null;   // %
      $app    = isset($apps[$i]) ? round($apps[$i]*100, 2) : null;
      $inc    = (!is_null($actual) && !is_null($app)) ? round($actual - $app, 2) : null;

      $flow_rate[] = [
        'code'=>"FR_{$p['from']}_{$p['to']}",
        'label'=>$p['label'],
        'from_bucket'=>$p['from'],
        'to_bucket'=>$p['to'],
        'numerator_os_curr'=>$num,
        'denominator_os_m1'=>$den,
        'actual_pct'=>$actual,     // %
        'appetite_pct'=>$app,      // %
        'inc_pct'=>$inc            // %
      ];
    }

    // ---- Tambahan: FR C→30 = FR 0→30 × FR 30→60 (produk)
    $fr_cx = $flow_rate[0]['actual_pct'] ?? null;
    $fr_x30= $flow_rate[1]['actual_pct'] ?? null;
    $fr_cx_app = $flow_rate[0]['appetite_pct'] ?? null;
    $fr_x30_app= $flow_rate[1]['appetite_pct'] ?? null;

    $fr_c30_actual = (!is_null($fr_cx) && !is_null($fr_x30))
                    ? round(($fr_cx/100) * ($fr_x30/100) * 100, 2) : null;
    $fr_c30_app = (!is_null($fr_cx_app) && !is_null($fr_x30_app))
                    ? round(($fr_cx_app/100) * ($fr_x30_app/100) * 100, 2) : null;
    $fr_c30_inc = (!is_null($fr_c30_actual) && !is_null($fr_c30_app))
                    ? round($fr_c30_actual - $fr_c30_app, 2) : null;

    $flow_rate[] = [
      'code'=>'FR_C_30','label'=>'FR C→30',
      'from_bucket'=>'A','to_bucket'=>'C','is_composite'=>true,
      'numerator_os_curr'=>null,'denominator_os_m1'=>null,
      'actual_pct'=>$fr_c30_actual,'appetite_pct'=>$fr_c30_app,'inc_pct'=>$fr_c30_inc
    ];

    // ===== Portfolio metrics (VERSI BARU)
    // DPD 30+ = semua OS bucket >30 (C..N) / GRAND TOTAL
    // DPD 90+ = semua OS bucket >90 (E..N) / GRAND TOTAL
    $codes_gt30 = ['C','D','E','F','G','H','I','J','K','L','M','N'];
    $codes_gt90 = ['E','F','G','H','I','J','K','L','M','N'];

    $sum_os = function(array $perBucket, array $codes){
      $s = 0;
      foreach ($codes as $c) { $s += (int)round($perBucket[$c]['os'] ?? 0); }
      return $s;
    };

    $dpd30_m1_num = $sum_os($M1['perBucket'] ?? [], $codes_gt30);
    $dpd30_act_num= $sum_os($CUR['perBucket'] ?? [], $codes_gt30);

    $dpd90_m1_num = $sum_os($M1['perBucket'] ?? [], $codes_gt90);
    $dpd90_act_num= $sum_os($CUR['perBucket'] ?? [], $codes_gt90);

    $dpd30_m1 = $gt_os_m1>0 ? round($dpd30_m1_num / $gt_os_m1 * 100, 2) : null;
    $dpd30_act= $gt_os_c>0  ? round($dpd30_act_num / $gt_os_c * 100, 2) : null;
    $dpd30_inc= (!is_null($dpd30_m1) && !is_null($dpd30_act)) ? round($dpd30_act - $dpd30_m1, 2) : null;

    $dpd90_m1 = $gt_os_m1>0 ? round($dpd90_m1_num / $gt_os_m1 * 100, 2) : null;
    $dpd90_act= $gt_os_c>0  ? round($dpd90_act_num / $gt_os_c * 100, 2) : null;
    $dpd90_inc= (!is_null($dpd90_m1) && !is_null($dpd90_act)) ? round($dpd90_act - $dpd90_m1, 2) : null;

    // Repayment Rate biarkan seperti sebelumnya: (A os / grand total), actual menggunakan A_actual + realisasi
    $osA_m1 = (int)round($M1['perBucket']['A']['os'] ?? 0);
    $osA_c  = (int)round($CUR['perBucket']['A']['os'] ?? 0);
    $rr_m1 = $gt_os_m1>0 ? round($osA_m1 / $gt_os_m1 * 100, 2) : null;
    $rr_act_num = $osA_c + (int)$realisasi['os'];
    $rr_act = $gt_os_c>0 ? round($rr_act_num / $gt_os_c * 100, 2) : null;
    $rr_inc = (!is_null($rr_m1) && !is_null($rr_act)) ? round($rr_act - $rr_m1, 2) : null;

    $portfolio_metrics = [
      'repayment_rate'=>[
        'm1_pct'=>$rr_m1, 'actual_pct'=>$rr_act, 'inc_pct'=>$rr_inc,
        'm1_num'=>$osA_m1, 'm1_den'=>$gt_os_m1,
        'actual_num'=>$rr_act_num, 'actual_den'=>$gt_os_c
      ],
      'dpd_30_plus'=>[
        'm1_pct'=>$dpd30_m1, 'actual_pct'=>$dpd30_act, 'inc_pct'=>$dpd30_inc,
        'm1_num'=>$dpd30_m1_num, 'm1_den'=>$gt_os_m1,
        'actual_num'=>$dpd30_act_num, 'actual_den'=>$gt_os_c
      ],
      'dpd_90_plus'=>[
        'm1_pct'=>$dpd90_m1, 'actual_pct'=>$dpd90_act, 'inc_pct'=>$dpd90_inc,
        'm1_num'=>$dpd90_m1_num, 'm1_den'=>$gt_os_m1,
        'actual_num'=>$dpd90_act_num, 'actual_den'=>$gt_os_c
      ]
    ];

    // ---- Response
    return sendResponse(200,"OK",[
      'closing_date'=>$closing,'harian_date'=>$harian,'kode_kantor'=>$kc,
      'realisasi_row'=>$realisasi,
      'rows'=>$rows,
      'grand_total'=>[
        'noa_m1'=>$gt_noa_m1, 'os_m1'=>$gt_os_m1,
        'noa_curr'=>$gt_noa_c, 'os_curr'=>$gt_os_c,
        'inc_noa'=>$gt_inc_noa, 'inc_os'=>$gt_inc_os,
        'inc_pct'=> $gt_os_m1>0 ? round(($gt_inc_os/$gt_os_m1)*100,2) : null
      ],
      'flow_rate'=>$flow_rate,               // FR 0→30 ... + FR C→30 (composite)
      'portfolio_metrics'=>$portfolio_metrics, // Repayment Rate, DPD 30+, DPD 90+
      'source'=>['closing'=>'nominatif','current'=>'nominatif (exclude realisasi baru)']
    ]);
  }


  public function getBucketCkpn($input=null){
    // ---- params
    $b = is_array($input)?$input:(json_decode(file_get_contents('php://input'),true) ?: []);
    $closing = $this->asDate($b['closing_date'] ?? null);
    $harian  = $this->asDate($b['harian_date'] ?? null);
    $kc_raw  = $b['kode_kantor'] ?? null;
    $kc      = ($kc_raw===null || $kc_raw==='') ? null : str_pad((string)$kc_raw,3,'0',STR_PAD_LEFT);
    if (!$closing || !$harian) return sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)");

    [$defs,$nameMap,$tagMap] = $this->loadBuckets();
    $order = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];

    $M1  = $this->computeCKPNForDate($closing,$kc,$defs);
    $CUR = $this->computeCKPNForDate($harian ,$kc,$defs);

    // ---- O_LUNAS: CKPN M-1 utk akun yang hilang (curr = 0)
    $o_ckpn = 0; $o_noa = 0;
    foreach ($M1['accSet'] as $acc=>$_){
      if (!isset($CUR['accSet'][$acc])){ $o_noa++; $o_ckpn += (int)round($M1['ckpnByAcc'][$acc] ?? 0); }
    }

    // ---- rows A..N + akumulasi subtotal SC/FE/BE
    $rows=[];
    $totSC = ['m1'=>0,'cur'=>0];
    $totFE = ['m1'=>0,'cur'=>0];
    $totBE = ['m1'=>0,'cur'=>0];

    foreach ($order as $code){
      $m1  = (int)round(($M1['perBucket'][$code]['ckpn'] ?? 0));
      $cur = (int)round(($CUR['perBucket'][$code]['ckpn'] ?? 0));

      $rows[] = [
        'dpd_code'=>$code,
        'dpd_name'=>$nameMap[$code] ?? $code,
        'status_tag'=>$tagMap[$code] ?? null,
        'ckpn_m1'=>$m1,
        'ckpn_curr'=>$cur,
        'ckpn_inc'=>($cur-$m1)
      ];

      $tag = $tagMap[$code] ?? null;
      if ($tag==='SC'){ $totSC['m1'] += $m1; $totSC['cur'] += $cur; }
      elseif($tag==='FE'){ $totFE['m1'] += $m1; $totFE['cur'] += $cur; }
      elseif($tag==='BE'){ $totBE['m1'] += $m1; $totBE['cur'] += $cur; }
    }

    // ---- Subtotal per cluster + GRAND TOTAL (A..N, tanpa O)
    $row_tot_sc = [
      'dpd_code'=>'TOTAL_SC','dpd_name'=>'TOTAL SC','status_tag'=>null,
      'ckpn_m1'=>(int)$totSC['m1'],
      'ckpn_curr'=>(int)$totSC['cur'],
      'ckpn_inc'=>(int)($totSC['cur'] - $totSC['m1'])
    ];
    $row_tot_fe = [
      'dpd_code'=>'TOTAL_FE','dpd_name'=>'TOTAL FE','status_tag'=>null,
      'ckpn_m1'=>(int)$totFE['m1'],
      'ckpn_curr'=>(int)$totFE['cur'],
      'ckpn_inc'=>(int)($totFE['cur'] - $totFE['m1'])
    ];
    $row_tot_be = [
      'dpd_code'=>'TOTAL_BE','dpd_name'=>'TOTAL BE','status_tag'=>null,
      'ckpn_m1'=>(int)$totBE['m1'],
      'ckpn_curr'=>(int)$totBE['cur'],
      'ckpn_inc'=>(int)($totBE['cur'] - $totBE['m1'])
    ];

    $gt_m1  = (int)$totSC['m1'] + (int)$totFE['m1'] + (int)$totBE['m1'];
    $gt_cur = (int)$totSC['cur'] + (int)$totFE['cur'] + (int)$totBE['cur'];

    $row_grand = [
      'dpd_code'=>'GRAND_TOTAL','dpd_name'=>'GRAND TOTAL','status_tag'=>null,
      'ckpn_m1'=>$gt_m1,
      'ckpn_curr'=>$gt_cur,
      'ckpn_inc'=> (int)($gt_cur - $gt_m1)
    ];

    // Tambahkan baris total ke rows (urutan: TOTAL_SC, TOTAL_FE, TOTAL_BE, GRAND_TOTAL)
    $rows[] = $row_tot_sc;
    $rows[] = $row_tot_fe;
    $rows[] = $row_tot_be;
    $rows[] = $row_grand;

    // ---- O_LUNAS row (tidak masuk total SC/FE/BE/GRAND)
    $rows[] = [
      'dpd_code'=>'O','dpd_name'=>'O_Lunas','status_tag'=>null,
      'ckpn_m1'=>(int)$o_ckpn,'ckpn_curr'=>0,'ckpn_inc'=>(int)(0 - $o_ckpn)
    ];

    // ---- Response (tambahkan ringkasan top-level agar FE mudah konsumsi)
    return sendResponse(200,"OK",[
      'closing_date'=>$closing,
      'harian_date'=>$harian,
      'kode_kantor'=>$kc,
      'rows'=>$rows,

      // Ringkasan per cluster (A..N)
      'total_sc'=>[
        'ckpn_m1'=>$row_tot_sc['ckpn_m1'],
        'ckpn_curr'=>$row_tot_sc['ckpn_curr'],
        'ckpn_inc' =>$row_tot_sc['ckpn_inc']
      ],
      'total_fe'=>[
        'ckpn_m1'=>$row_tot_fe['ckpn_m1'],
        'ckpn_curr'=>$row_tot_fe['ckpn_curr'],
        'ckpn_inc' =>$row_tot_fe['ckpn_inc']
      ],
      'total_be'=>[
        'ckpn_m1'=>$row_tot_be['ckpn_m1'],
        'ckpn_curr'=>$row_tot_be['ckpn_curr'],
        'ckpn_inc' =>$row_tot_be['ckpn_inc']
      ],

      // Grand total A..N (tidak termasuk O_LUNAS)
      'grand_total'=>[
        'ckpn_m1'=>$row_grand['ckpn_m1'],
        'ckpn_curr'=>$row_grand['ckpn_curr'],
        'ckpn_inc' =>$row_grand['ckpn_inc']
      ],

      'source'=>[
        'closing'=>$M1['source'],
        'current'=>$CUR['source']
      ]
    ]);
  }


  public function migrasiBucketOsc($input=null){
    // ---- Params
    $b = is_array($input)?$input:(json_decode(file_get_contents('php://input'),true) ?: []);
    $closing = $this->asDate($b['closing_date'] ?? null);
    $harian  = $this->asDate($b['harian_date'] ?? null);
    $kc_raw  = $b['kode_kantor'] ?? null;
    $kc      = ($kc_raw===null || $kc_raw==='') ? null : str_pad((string)$kc_raw,3,'0',STR_PAD_LEFT);
    if (!$closing || !$harian) return sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)");

    // ---- Master bucket
    [$defs,$nameMap,$tagMap] = $this->loadBuckets();
    $order   = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];      // FROM
    $orderTo = array_merge($order, ['O']);                                      // TO termasuk O (LUNAS)

    // ---- Data per tanggal
    $M1  = $this->computeOSForDate($closing,$kc,$defs);
    $CUR = $this->computeOSForDate($harian ,$kc,$defs);

    // ---- Penampung matriks (agregat)
    $matrix = [];
    foreach ($order as $f){
      foreach ($orderTo as $t){
        $matrix[$f][$t] = ['noa'=>0,'os_curr'=>0,'os_m1'=>0,'os_used'=>0];     // os_used: pembilang untuk % (TO=O -> pakai os_m1)
      }
    }

    // ---- Ringkasan per FROM (denominator & INC menggunakan OS CURRENT apa adanya)
    $fromTotals = [];
    foreach ($order as $f){
      $fromTotals[$f] = [
        'noa_m1'=>0,'os_m1'=>0,
        'noa_curr_from'=>0,'os_curr_from'=>0,
        'inc_noa'=>0,'inc_os'=>0
      ];
    }

    // ---- Grand ringkas populasi M-1
    $grand_m1_noa = 0; $grand_m1_os = 0;
    $grand_cur_noa_fromPop = 0; $grand_cur_os_fromPop = 0;

    // ===== Iterasi populasi M-1 (patokan FROM)
    foreach ($M1['accSet'] as $acc => $_){
      $from  = $M1['bucketByAcc'][$acc] ?? 'A';
      if (!in_array($from,$order,true)) $from = 'A';

      $os_m1 = (int)round($M1['osByAcc'][$acc] ?? 0);

      // Tujuan di CURRENT; jika tidak ada -> O (LUNAS)
      $to    = $CUR['bucketByAcc'][$acc] ?? 'O';
      if (!in_array($to,$orderTo,true)) $to = 'O';

      $os_cur = (int)round($CUR['osByAcc'][$acc] ?? 0);     // 0 bila LUNAS
      $os_used = ($to==='O') ? $os_m1 : $os_cur;            // <- aturan baru

      // Totals FROM (denominator & INC calculation pakai OS CURRENT asli)
      $fromTotals[$from]['noa_m1']++;
      $fromTotals[$from]['os_m1'] += $os_m1;
      $fromTotals[$from]['noa_curr_from']++;
      $fromTotals[$from]['os_curr_from'] += $os_cur;

      $grand_m1_noa++;                 $grand_m1_os += $os_m1;
      $grand_cur_noa_fromPop++;        $grand_cur_os_fromPop += $os_cur;

      // Agregasi pasangan FROM→TO
      $matrix[$from][$to]['noa']++;
      $matrix[$from][$to]['os_curr'] += $os_cur;
      $matrix[$from][$to]['os_m1']   += $os_m1;
      $matrix[$from][$to]['os_used'] += $os_used;           // inilah yang dipakai untuk % per FROM
    }

    // ===== REALISASI (akun baru di CURRENT yang tidak ada di M-1)
    $realisasi_total = ['noa'=>0,'os'=>0];
    $realisasi_by_bucket = [];
    foreach ($order as $t){ $realisasi_by_bucket[$t] = ['noa'=>0,'os'=>0]; }

    foreach ($CUR['accSet'] as $acc => $_){
      if (!isset($M1['accSet'][$acc])){                // NEW / realisasi
        $to = $CUR['bucketByAcc'][$acc] ?? 'A';
        if (!in_array($to,$order,true)) $to = 'A';

        $os_cur = (int)round($CUR['osByAcc'][$acc] ?? 0);
        $realisasi_total['noa']++;
        $realisasi_total['os']  += $os_cur;

        $realisasi_by_bucket[$to]['noa']++;
        $realisasi_by_bucket[$to]['os']  += $os_cur;
      }
    }

    // ===== Hitung INC per FROM (curr_from − m1) — pakai OS CURRENT asli
    foreach ($fromTotals as $f => &$t){
      $t['inc_noa'] = (int)($t['noa_curr_from'] - $t['noa_m1']);
      $t['inc_os']  = (int)($t['os_curr_from']  - $t['os_m1']);
    } unset($t);

    // ===== Flatten matriks (lengkap + % per FROM dengan os_used)
    $out = [];
    foreach ($order as $from){
      $den = (int)$fromTotals[$from]['os_m1'];         // denominator % = OS M-1 FROM
      foreach ($orderTo as $to){
        $cell = $matrix[$from][$to];
        $num  = (int)$cell['os_used'];                 // pembilang: TO!=O pakai os_curr, TO=O pakai os_m1
        $pct  = ($den>0) ? round($num / $den * 100, 2) : null;

        $out[] = [
          'from_bucket'       => $from,
          'to_bucket'         => $to,
          'noa'               => (int)$cell['noa'],
          'os'                => $num,                 // nilai yang dipakai untuk % (TO=O -> OS M-1)
          'os_curr'           => (int)$cell['os_curr'],// OS CURRENT agregat pada pasangan ini (TO=O biasanya 0)
          'os_m1'             => (int)$cell['os_m1'],  // OS M-1 agregat pada pasangan ini
          'denominator_os_m1' => $den,
          'actual_pct'        => $pct
        ];
      }
    }

    // ===== Response
    return sendResponse(200,"OK",[
      'closing_date' => $closing,
      'harian_date'  => $harian,
      'kode_kantor'  => $kc,
      'order_to'     => $orderTo,             // urutan kolom TO (A..N,O)
      'from_totals'  => $fromTotals,          // ringkasan per FROM (INC pakai OS CURRENT asli)
      'matrix'       => $out,                 // matriks FROM→TO (TO=O: os = OS M-1)
      'realisasi'    => [
        'total'     => ['noa'=>(int)$realisasi_total['noa'], 'os'=>(int)$realisasi_total['os']],
        'by_bucket' => $realisasi_by_bucket
      ],
      'grand'        => [
        'm1_noa'            => (int)$grand_m1_noa,
        'm1_os'             => (int)$grand_m1_os,
        'curr_noa_from_pop' => (int)$grand_cur_noa_fromPop,
        'curr_os_from_pop'  => (int)$grand_cur_os_fromPop
      ],
      'note'         => 'TO=O (LUNAS) memakai OS M-1 sebagai pembilang (kolom os) agar terlihat porsi pelunasan. Ringkasan INC per FROM tetap menggunakan OS CURRENT asli.'
    ]);
  }

  /* ====== Utilities yang sudah kamu punya ====== */
  private function asDate($s) {
    if (!$s) return null;
    $t = strtotime($s);
    return $t ? date('Y-m-d', $t) : null;
  }
  private function snapshotExists($table, $created) {
    $st = $this->pdo->prepare("SELECT 1 FROM {$table} WHERE created = ? LIMIT 1");
    $st->execute([$created]);
    return (bool)$st->fetchColumn();
  }
  private function loadBuckets(): array {
    $rows = $this->pdo->query("
      SELECT dpd_code, dpd_name, min_day, max_day, status_tag
      FROM ref_dpd_bucket ORDER BY min_day
    ")->fetchAll(PDO::FETCH_ASSOC);
    $def = []; $name=[]; $tag=[];
    foreach ($rows as $r){
      $def[] = [
        'code'=>$r['dpd_code'],'name'=>$r['dpd_name'],
        'min'=>(int)$r['min_day'],'max'=>is_null($r['max_day'])?null:(int)$r['max_day'],
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

  private function computeOSForDate(string $d, ?string $kc, array $defs): array {
    [$ds,$de] = $this->dayRange($d);
    $sql = "SELECT no_rekening, hari_menunggak, baki_debet
            FROM nominatif
            WHERE created >= ? AND created < ?";
    $params = [$ds,$de];
    if ($kc !== null) { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
    else { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }

    $st = $this->pdo->prepare($sql);
    $st->execute($params);

    $sumPer = []; $accSet=[]; $osByAcc=[]; $bucketByAcc=[];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $acc = $r['no_rekening'];
      $dpd = (int)($r['hari_menunggak'] ?? 0);
      $os  = (float)($r['baki_debet'] ?? 0);
      $code = $this->dpdToCode($dpd,$defs) ?? 'A';
      if (!isset($sumPer[$code])) $sumPer[$code] = ['noa'=>0,'os'=>0.0];
      $sumPer[$code]['noa']++;
      $sumPer[$code]['os'] += $os;

      $accSet[$acc] = true;
      $osByAcc[$acc] = $os;
      $bucketByAcc[$acc] = $code;
    }
    foreach ($sumPer as &$v){ $v['os'] = (int)round($v['os']); } unset($v);

    return [
      'perBucket'   => $sumPer,
      'accSet'      => $accSet,
      'osByAcc'     => $osByAcc,
      'bucketByAcc' => $bucketByAcc
    ];
  }

  private function loadGlobalLGD(string $harian_date): float {
    try {
      $st = $this->pdo->prepare("
        SELECT lgd_percent FROM lgd_current
        WHERE created <= ? ORDER BY created DESC LIMIT 1
      ");
      $st->execute([$harian_date]);
      $v = $st->fetchColumn();
      return ($v!==false) ? (float)$v : 59.48;
    } catch (PDOException $e) { return 59.48; }
  }
  private function loadPdMap(string $d): array {
    $pdMap=[];
    try {
      $st = $this->pdo->prepare("
        SELECT p.product_code, p.dpd_code, p.pd_percent
        FROM pd_current p
        JOIN (
          SELECT product_code, dpd_code, MAX(created) AS created
          FROM pd_current
          WHERE created <= ?
          GROUP BY product_code, dpd_code
        ) x ON x.product_code=p.product_code AND x.dpd_code=p.dpd_code AND x.created=p.created
      ");
      $st->execute([$d]);
      foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r){
        $pdMap[(int)$r['product_code']][$r['dpd_code']] = (float)$r['pd_percent'];
      }
      if (!empty($pdMap)) return $pdMap;
    } catch (PDOException $e) {}
    try {
      $q = $this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
      foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r){
        $pdMap[(int)$r['product_code']][$r['dpd_code']] = (float)str_replace(',','.',$r['pd_percent']);
      }
    } catch (PDOException $e) {}
    return $pdMap;
  }
  private function hasSnapshot(string $d, ?string $kc): bool {
    try {
      [$ds,$de] = $this->dayRange($d);
      $sql = "SELECT COUNT(1) FROM nominatif_ckpn WHERE created >= ? AND created < ?";
      $params = [$ds,$de];
      if ($kc!==null){ $sql.=" AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
      $st = $this->pdo->prepare($sql); $st->execute($params);
      return ((int)$st->fetchColumn() > 0);
    } catch (PDOException $e) { return false; }
  }

  /* ====== Tambahan helper untuk CKPN per akun ====== */

/* ===================== CKPN HELPERS ===================== */

/*********************** CKPN HELPERS ***********************/
private function fetchSnapCkpnMap(string $d, ?string $kc, array $accs): array {
  if (!$accs) return [];
  [$ds,$de] = $this->dayRange($d);
  $out = [];
  foreach (array_chunk($accs, 500) as $chunk) {
    $ph = implode(',', array_fill(0, count($chunk), '?'));
    $sql = "SELECT no_rekening, nilai_ckpn
            FROM nominatif_ckpn
            WHERE created >= ? AND created < ? AND no_rekening IN ($ph)";
    $params = array_merge([$ds,$de], $chunk);
    if ($kc !== null && $kc !== '000') { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
    else { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }
    $st = $this->pdo->prepare($sql); $st->execute($params);
    while($r = $st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (float)$r['nilai_ckpn'];
  }
  return $out;
}

private function fetchIndivCkpnMap(string $d, array $accs): array {
  if (!$accs) return [];
  $out = [];
  foreach (array_chunk($accs, 500) as $chunk){
    $ph = implode(',', array_fill(0, count($chunk), '?'));
    $sql = "SELECT ci.no_rekening, ci.nilai_ckpn
            FROM ckpn_individual ci
            JOIN (
              SELECT no_rekening, MAX(created) AS created
              FROM ckpn_individual
              WHERE created <= ? AND no_rekening IN ($ph)
              GROUP BY no_rekening
            ) x ON x.no_rekening=ci.no_rekening AND x.created=ci.created";
    $params = array_merge([$d], $chunk);
    $st = $this->pdo->prepare($sql); $st->execute($params);
    while($r=$st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (float)$r['nilai_ckpn'];
  }
  return $out;
}
private function fetchRestrukSet(string $d, array $accs): array {
  if (!$accs) return [];
  $set = [];
  foreach (array_chunk($accs, 500) as $chunk){
    $ph = implode(',', array_fill(0, count($chunk), '?'));
    $sql = "SELECT nr.no_rekening
            FROM nom_restruk nr
            JOIN (
              SELECT no_rekening, MAX(created) AS created
              FROM nom_restruk
              WHERE created <= ? AND no_rekening IN ($ph)
              GROUP BY no_rekening
            ) x ON x.no_rekening=nr.no_rekening AND x.created=nr.created";
    $params = array_merge([$d], $chunk);
    $st = $this->pdo->prepare($sql); $st->execute($params);
    while($r=$st->fetch(PDO::FETCH_ASSOC)) $set[$r['no_rekening']] = true;
  }
  return $set;
}

private function computeCkpnForRow(array $row, string $d, array $pdMap, float $LGD,
                                   array $indivMap, array $restrukSet, $snapVal) : int {
  if ($snapVal !== null) return (int)round((float)$snapVal);
  $acc  = $row['no_rekening'] ?? null;
  if ($acc && isset($indivMap[$acc])) return (int)round((float)$indivMap[$acc]);

  $ead  = (float)($row['saldo_bank'] ?? 0);
  $dpd  = (int)($row['hari_menunggak'] ?? 0);
  $prod = isset($row['kode_produk']) && $row['kode_produk']!=='' ? (int)$row['kode_produk'] : null;

  $bucket = $row['to_bucket'] ?? $row['from_bucket'] ?? null;
  if (!$bucket) { [$defs] = $this->loadBuckets(); $bucket = $this->dpdToCode($dpd, $defs) ?? 'A'; }

  $isRestruk = $acc ? isset($restrukSet[$acc]) : false;
  if ($dpd <= 7 && !$isRestruk) return 0;

  $pd = 0.0;
  if ($prod !== null && isset($pdMap[$prod][$bucket])) $pd = (float)$pdMap[$prod][$bucket];
  return (int)round($ead * ($pd/100.0) * ($LGD/100.0));
}

/* ====== NEW: angsuran & CKPN M-1 exact ====== */
private function fetchAngsuranMap(string $closing, string $harian, ?string $kc, array $accs): array {
  if (!$accs) return [];
  $out = [];
  foreach (array_chunk($accs, 500) as $chunk) {
    $ph = implode(',', array_fill(0, count($chunk), '?'));
    $sql = "SELECT no_rekening,
                   COALESCE(SUM(angsuran_pokok),0) AS sum_pokok,
                   COALESCE(SUM(angsuran_bunga),0) AS sum_bunga,
                   MAX(tgl_trans) AS last_tgl_trans
            FROM transaksi_kredit
            WHERE tgl_trans > ? AND tgl_trans <= ?
              AND no_rekening IN ($ph)";
    $params = array_merge([$closing,$harian], $chunk);
    if ($kc !== null && $kc !== '000') { $sql .= " AND LPAD(CAST(kode_kantor AS CHAR),3,'0') = ?"; $params[]=$kc; }
    $sql .= " GROUP BY no_rekening";
    $st = $this->pdo->prepare($sql);
    $st->execute($params);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $out[$r['no_rekening']] = [
        'pokok' => (int)round($r['sum_pokok'] ?? 0),
        'bunga' => (int)round($r['sum_bunga'] ?? 0),
        'last_tgl_trans' => $r['last_tgl_trans'] ?? null
      ];
    }
  }
  return $out;
}

private function fetchCkpnM1MapExact(string $closing, ?string $kc, array $accs): array {
  if (!$accs) return [];
  $out = [];
  foreach (array_chunk($accs, 500) as $chunk) {
    $ph = implode(',', array_fill(0, count($chunk), '?'));
    $sql = "SELECT no_rekening, nilai_ckpn
            FROM nominatif_ckpn
            WHERE created = ? AND no_rekening IN ($ph)";
    $params = array_merge([$closing], $chunk);
    if ($kc !== null && $kc !== '000') { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
    $st = $this->pdo->prepare($sql); $st->execute($params);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $out[$r['no_rekening']] = (int)round($r['nilai_ckpn'] ?? 0);
    }
  }
  return $out;
}

/*********************** DISPATCHER ***********************/
public function getMigrasiBucketDetail($input = null)
{
  $b = is_array($input)?$input:(json_decode(file_get_contents('php://input'),true) ?: []);

  $closing = $this->asDate($b['closing_date'] ?? null);
  $harian  = $this->asDate($b['harian_date']  ?? null);
  $fb      = strtoupper(trim($b['from_bucket'] ?? ''));
  $tb      = strtoupper(trim($b['to_bucket']   ?? ''));
  $kc_raw  = $b['kode_kantor'] ?? null;
  $kc      = ($kc_raw===null || $kc_raw==='') ? null : str_pad((string)$kc_raw,3,'0',STR_PAD_LEFT);

  if (!$closing || !$harian) return sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)");

  $VALID = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];

  // REALISASI — wajib cabang spesifik (bukan konsolidasi)
  if ($fb === 'REALISASI') {
    if ($kc===null || $kc==='000') return sendResponse(400,"Realisasi: kode_kantor wajib (tidak boleh konsolidasi).");
    $tbFilter = ($tb!=='' && in_array($tb,$VALID,true)) ? $tb : null;
    return $this->getMigrasiBucketDetailRealisasi($closing,$harian,$kc,$tbFilter);
  }

  // O_LUNAS — wajib from_bucket (A..N)
  if ($tb === 'O' || $tb === 'O_LUNAS') {
    if (!in_array($fb,$VALID,true)) return sendResponse(400,"O_LUNAS: from_bucket wajib A..N.");
    return $this->getMigrasiBucketDetailOLunas($closing,$harian,$kc,$fb);
  }

  // ACTUAL — A..N → A..N
  if (!in_array($fb,$VALID,true) || !in_array($tb,$VALID,true))
    return sendResponse(400,"Actual: from_bucket & to_bucket wajib A..N.");

  return $this->getMigrasiBucketDetailActual($closing,$harian,$kc,$fb,$tb);
}




/*********************** REALISASI (akun baru) ***********************/
public function getMigrasiBucketDetailRealisasi(string $closing, string $harian, string $kc, ?string $tbFilter=null)
{
  [$dsH,$deH] = $this->dayRange($harian);

  $sql = "
    SELECT
      LPAD(CAST(nh.kode_cabang AS CHAR),3,'0') AS kode_cabang,
      nh.no_rekening,
      nh.nama_nasabah,
      NULL                AS alamat,
      'REALISASI'         AS from_bucket,
      rb2.dpd_code        AS to_bucket,
      nh.baki_debet       AS baki_debet,
      nh.kolektibilitas,
      nh.kode_produk,
      nh.tunggakan_pokok,
      nh.tunggakan_bunga,
      nh.hari_menunggak,
      nh.hari_menunggak_pokok,
      nh.hari_menunggak_bunga,
      nh.saldo_bank,
      nh.tgl_jatuh_tempo,
      nh.tgl_realisasi,

      NULL AS angsuran_pokok,
      NULL AS angsuran_bunga,
      NULL AS os_m1,
      nh.baki_debet AS os_curr,
      NULL AS ckpn_actual,
      NULL AS ckpn_m1,
      NULL AS tgl_trans_terakhir
    FROM nominatif nh
    JOIN ref_dpd_bucket rb2
      ON nh.hari_menunggak >= rb2.min_day
     AND (rb2.max_day IS NULL OR nh.hari_menunggak <= rb2.max_day)
    WHERE nh.created >= :dsH1 AND nh.created < :deH1
      AND LPAD(CAST(nh.kode_cabang AS CHAR),3,'0') = :kcH
      AND nh.tgl_realisasi >  :cld
      AND nh.tgl_realisasi <= :hrd
      " . ($tbFilter ? " AND rb2.dpd_code = :tb " : "") . "
    ORDER BY nh.baki_debet DESC
  ";

  $st = $this->pdo->prepare($sql);
  $st->bindValue(':dsH1', $dsH);
  $st->bindValue(':deH1', $deH);
  $st->bindValue(':kcH',  $kc);
  $st->bindValue(':cld',  $closing);
  $st->bindValue(':hrd',  $harian);
  if ($tbFilter) $st->bindValue(':tb', $tbFilter);

  $st->execute();
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $num = [
    'baki_debet','tunggakan_pokok','tunggakan_bunga',
    'hari_menunggak','hari_menunggak_pokok','hari_menunggak_bunga',
    'saldo_bank','os_curr'
  ];
  foreach ($rows as &$r){ foreach ($num as $f){ if (array_key_exists($f,$r) && $r[$f]!==null && $r[$f]!=='') $r[$f]=0+$r[$f]; } }
  unset($r);

  return sendResponse(200,"OK (realisasi detail)", $rows);
}


/*********************** ACTUAL A..N → A..N (FAST + BUCKET INFO) ***********************/
public function getMigrasiBucketDetailActual(string $closing, string $harian, ?string $kc, string $fb, string $tb){
  if ($kc===null || $kc==='000') {
    return sendResponse(400, "Pilih kode_kantor spesifik (CHAR(3), mis. '004') untuk detail ACTUAL.");
  }
  $kc = str_pad($kc, 3, '0', STR_PAD_LEFT);

  [$dsC,$deC] = $this->dayRange($closing);
  [$dsH,$deH] = $this->dayRange($harian);

  $sql = "
    SELECT
      h.kode_cabang,
      h.no_rekening,
      h.nama_nasabah,
      h.alamat,

      rbf.dpd_code  AS from_bucket,
      rbf.dpd_name  AS from_bucket_name,
      rbf.status_tag AS from_status_tag,
      rbf.min_day   AS from_min_day,
      rbf.max_day   AS from_max_day,

      rbt.dpd_code  AS to_bucket,
      rbt.dpd_name  AS to_bucket_name,
      rbt.status_tag AS to_status_tag,
      rbt.min_day   AS to_min_day,
      rbt.max_day   AS to_max_day,

      h.baki_debet  AS baki_debet,
      h.kolektibilitas,
      h.kode_produk,
      h.tunggakan_pokok,
      h.tunggakan_bunga,
      h.hari_menunggak,
      h.hari_menunggak_pokok,
      h.hari_menunggak_bunga,
      h.saldo_bank,
      h.tgl_jatuh_tempo,

      c.baki_debet  AS os_m1,
      h.baki_debet  AS os_curr,

      NULL AS angsuran_pokok,
      NULL AS angsuran_bunga,
      NULL AS tgl_trans_terakhir
    FROM nominatif c
    JOIN nominatif h
      ON h.no_rekening = c.no_rekening
     AND h.created >= :dsH AND h.created < :deH
     AND h.kode_cabang = :kcH
    JOIN ref_dpd_bucket rbf
      ON c.hari_menunggak >= rbf.min_day
     AND (rbf.max_day IS NULL OR c.hari_menunggak <= rbf.max_day)
     AND rbf.dpd_code = :fb
    JOIN ref_dpd_bucket rbt
      ON h.hari_menunggak >= rbt.min_day
     AND (rbt.max_day IS NULL OR h.hari_menunggak <= rbt.max_day)
     AND rbt.dpd_code = :tb
    WHERE c.created >= :dsC AND c.created < :deC
      AND c.kode_cabang = :kcC
    ORDER BY h.baki_debet DESC
  ";

  $st = $this->pdo->prepare($sql);
  $st->bindValue(':kcH', $kc);
  $st->bindValue(':kcC', $kc);
  $st->bindValue(':dsH', $dsH); $st->bindValue(':deH', $deH);
  $st->bindValue(':dsC', $dsC); $st->bindValue(':deC', $deC);
  $st->bindValue(':fb',  $fb);
  $st->bindValue(':tb',  $tb);
  $st->execute();
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  return $this->attachCkpnAndCast($rows, $harian, $closing, $kc, 'actual');
}




/*********************** O_LUNAS (M-1 → O) — FAST + BUCKET INFO ***********************/
public function getMigrasiBucketDetailOLunas(string $closing, string $harian, ?string $kc, string $fromBucket){
  if ($kc===null || $kc==='000') {
    return sendResponse(400, "Pilih kode_kantor spesifik (CHAR(3), mis. '004') untuk detail O_LUNAS.");
  }
  $kc = str_pad($kc, 3, '0', STR_PAD_LEFT);

  [$dsC,$deC] = $this->dayRange($closing);
  [$dsH,$deH] = $this->dayRange($harian);

  $sql = "
    SELECT
      c.kode_cabang,
      c.no_rekening,
      c.nama_nasabah,
      c.alamat,

      rbf.dpd_code  AS from_bucket,
      rbf.dpd_name  AS from_bucket_name,
      rbf.status_tag AS from_status_tag,
      rbf.min_day   AS from_min_day,
      rbf.max_day   AS from_max_day,

      'O'           AS to_bucket,
      'O_LUNAS'     AS to_bucket_name,
      NULL          AS to_status_tag,
      NULL          AS to_min_day,
      NULL          AS to_max_day,

      c.baki_debet  AS baki_debet,
      c.kolektibilitas,
      c.kode_produk,
      c.tunggakan_pokok,
      c.tunggakan_bunga,
      c.hari_menunggak,
      c.hari_menunggak_pokok,
      c.hari_menunggak_bunga,
      c.saldo_bank,
      c.tgl_jatuh_tempo,

      c.baki_debet  AS os_m1,
      NULL          AS os_curr,

      NULL AS angsuran_pokok,
      NULL AS angsuran_bunga,
      NULL AS tgl_trans_terakhir
    FROM nominatif c
    LEFT JOIN nominatif h
      ON h.no_rekening = c.no_rekening
     AND h.created >= :dsH AND h.created < :deH
     AND h.kode_cabang = :kcH
    JOIN ref_dpd_bucket rbf
      ON c.hari_menunggak >= rbf.min_day
     AND (rbf.max_day IS NULL OR c.hari_menunggak <= rbf.max_day)
     AND rbf.dpd_code = :fb
    WHERE c.created >= :dsC AND c.created < :deC
      AND c.kode_cabang = :kcC
      AND h.no_rekening IS NULL
    ORDER BY c.baki_debet DESC
  ";

  $st = $this->pdo->prepare($sql);
  $st->bindValue(':kcH', $kc);
  $st->bindValue(':kcC', $kc);
  $st->bindValue(':dsH', $dsH); $st->bindValue(':deH', $deH);
  $st->bindValue(':dsC', $dsC); $st->bindValue(':deC', $deC);
  $st->bindValue(':fb',  $fromBucket);
  $st->execute();
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  return $this->attachCkpnAndCast($rows, $harian, $closing, $kc, 'o_lunas');
}


/*********************** ATTACH CKPN & CAST ***********************/
private function attachCkpnAndCast(array $rows, string $harian, string $closing, ?string $kc, string $mode){
  $accs = array_values(array_unique(array_filter(array_map(fn($r)=>$r['no_rekening'] ?? null, $rows))));

  // 1) Angsuran periode (closing, harian]
  $angsMap = $this->fetchAngsuranMap($closing, $harian, $kc, $accs);

  // 2) CKPN M-1 EXACT (created = closing_date) — untuk SEMUA mode
  $ckpnM1Map = $this->fetchCkpnM1MapExact($closing, $kc, $accs);

  // 3) CKPN ACTUAL (mode != o_lunas) via snapshot/compute
  $LGD   = $this->loadGlobalLGD($harian);
  $pdMap = $this->loadPdMap($harian);
  $snapMapH = ($mode!=='o_lunas') ? $this->fetchSnapCkpnMap($harian, $kc, $accs) : [];
  $indivMap = $this->fetchIndivCkpnMap($harian, $accs);
  $restruk  = $this->fetchRestrukSet($harian, $accs);

  foreach ($rows as &$r){
    $acc = $r['no_rekening'] ?? null;

    // Angsuran
    $r['angsuran_pokok']     = isset($angsMap[$acc]) ? (int)$angsMap[$acc]['pokok'] : 0;
    $r['angsuran_bunga']     = isset($angsMap[$acc]) ? (int)$angsMap[$acc]['bunga'] : 0;
    $r['tgl_trans_terakhir'] = $angsMap[$acc]['last_tgl_trans'] ?? null;

    // CKPN M-1 (selalu isi dari nominatif_ckpn created = closing_date)
    $r['ckpn_m1'] = (int)round($ckpnM1Map[$acc] ?? 0);

    // CKPN actual
    if ($mode==='o_lunas'){
      $r['ckpn_actual'] = 0;
    } else {
      $snap = $snapMapH[$acc] ?? null;
      $r['ckpn_actual'] = $this->computeCkpnForRow($r, $harian, $pdMap, $LGD, $indivMap, $restruk, $snap);
    }
  } unset($r);

  $num = [
    'baki_debet','tunggakan_pokok','tunggakan_bunga',
    'hari_menunggak','hari_menunggak_pokok','hari_menunggak_bunga',
    'saldo_bank','angsuran_pokok','angsuran_bunga','os_m1','os_curr',
    'ckpn_actual','ckpn_m1'
  ];
  foreach ($rows as &$r){ foreach ($num as $f){ if (array_key_exists($f,$r) && $r[$f]!==null && $r[$f]!=='') $r[$f]=0+$r[$f]; } }
  unset($r);

  return sendResponse(200,"OK ($mode detail)", $rows);
}

}


