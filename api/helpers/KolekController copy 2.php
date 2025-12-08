<?php

require_once __DIR__ . '/../helpers/response.php';

class KolekController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

  /* ==============================
     REKAP KOLEKTIBILITAS (STABLE)
     ============================== */
  public function getRekapKolektabilitas($input = []) {
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

    // Subqueries (gunakan 'created' = satu hari-window agar kena index)
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

    $m1Agg = "
      SELECT c.kolektibilitas AS kol, COUNT(*) AS noa, SUM(c.baki_debet) AS os
      FROM $subClosing c
      GROUP BY c.kolektibilitas
    ";
    $actAgg = "
      SELECT h.kolektibilitas AS kol, COUNT(*) AS noa, SUM(h.baki_debet) AS os
      FROM $subHarian h
      INNER JOIN $subClosing c2 ON c2.no_rekening = h.no_rekening
      GROUP BY h.kolektibilitas
    ";
    $realisasiAgg = "
      SELECT COUNT(*) AS noa, SUM(h.baki_debet) AS os
      FROM $subHarian h
      LEFT JOIN $subClosing c3 ON c3.no_rekening = h.no_rekening
      WHERE c3.no_rekening IS NULL
    ";
    $lunasByKolek = "
      SELECT c.kolektibilitas AS kol, COUNT(*) AS noa, SUM(c.baki_debet) AS os
      FROM $subClosing c
      LEFT JOIN $subHarian h2 ON h2.no_rekening = c.no_rekening
      WHERE h2.no_rekening IS NULL
      GROUP BY c.kolektibilitas
    ";
    $lunasTotalAgg = "SELECT COALESCE(SUM(noa),0) AS noa, COALESCE(SUM(os),0) AS os FROM ($lunasByKolek) x";

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

    $params = [];
    $params[] = $closing; if ($kantor) $params[] = $kantor; // m1Agg
    $params[] = $harian;  if ($kantor) $params[] = $kantor; // actAgg h
    $params[] = $closing; if ($kantor) $params[] = $kantor; // actAgg c2
    $params[] = $harian;  if ($kantor) $params[] = $kantor; // realisasiAgg h
    $params[] = $closing; if ($kantor) $params[] = $kantor; // realisasiAgg c3
    $params[] = $closing; if ($kantor) $params[] = $kantor; // lunasByKolek c
    $params[] = $harian;  if ($kantor) $params[] = $kantor; // lunasByKolek h2

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $LM = ['L','DP','KL','D','M'];
    $tot_m1_noa=0;$tot_m1_os=0.0;
    $tot_h_noa_lm=0;$tot_h_os_lm=0.0;
    $realisasi_noa=0;$realisasi_os=0.0;
    $byKol=[];

    foreach ($rows as &$r) {
      $kol = strtoupper(trim((string)($r['kol'] ?? '')));
      $m1noa=(int)($r['m1_noa']??0);
      $m1os =(float)($r['m1_os']??0);
      $actnoa=(int)($r['act_noa']??0);
      $actos =(float)($r['act_os']??0);

      if (in_array($kol,$LM,true)){
        $tot_m1_noa+=$m1noa; $tot_m1_os+=$m1os;
        $tot_h_noa_lm+=$actnoa; $tot_h_os_lm+=$actos;
        $r['inc_os']=$actos-$m1os;
      } elseif ($kol==='REALISASI'){
        $realisasi_noa=$actnoa; $realisasi_os=$actos; $r['inc_os']=null;
      } else { $r['inc_os']=null; }

      $byKol[$kol]=['m1_os'=>$m1os,'act_os'=>$actos];
    }
    unset($r);

    $total_actual_noa=$tot_h_noa_lm+$realisasi_noa;
    $total_actual_os =$tot_h_os_lm+$realisasi_os;

    $npl_m1_os=(float)($byKol['KL']['m1_os']??0)+(float)($byKol['D']['m1_os']??0)+(float)($byKol['M']['m1_os']??0);
    $npl_h_os =(float)($byKol['KL']['act_os']??0)+(float)($byKol['D']['act_os']??0)+(float)($byKol['M']['act_os']??0);

    $den_m1_os=(float)$tot_m1_os;
    $den_h_os =(float)$tot_h_os_lm+(float)$realisasi_os;

    $npl_m1_pct=$den_m1_os>0?round($npl_m1_os/$den_m1_os*100,2):0.0;
    $npl_h_pct =$den_h_os>0?round($npl_h_os/$den_h_os*100,2):0.0;

    $payload = [
      "rows"=>$rows,
      "total_osc"=>[
        "m1_noa"=>$tot_m1_noa,"m1_os"=>$tot_m1_os,
        "act_noa"=>$total_actual_noa,"act_os"=>$total_actual_os,
        "inc_os"=>$total_actual_os-$tot_m1_os
      ],
      "npl"=>[
        "m1_pct"=>$npl_m1_pct,"actual_pct"=>$npl_h_pct,
        "inc_pct"=>round($npl_h_pct-$npl_m1_pct,2)
      ],
      "debug"=>[
        "act_os_lm_only"=>$tot_h_os_lm,
        "realisasi_os"=>$realisasi_os,
        "act_os_total"=>$total_actual_os
      ]
    ];
    sendResponse(200,"OK",[
      "params"=>["closing_date"=>$closing,"harian_date"=>$harian,"kode_kantor"=>$kantor],
      "data"=>$payload
    ]);
  }

  /* ==========================================
     MIGRASI KOLEKTIBILITAS (RINGKASAN) – OK
     ========================================== */
  public function getMigrasiKolektabilitas($input = []) {
    $closing = $this->asDate($input['closing_date'] ?? null);
    $harian  = $this->asDate($input['harian_date']  ?? null);
    $kantor  = trim($input['kode_kantor'] ?? '');
    $kantor  = ($kantor === '' || $kantor === '000') ? null : $kantor;

    if (!$closing || !$harian) { sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)"); return; }
    if (!$this->snapshotExists('nominatif',$closing) || !$this->snapshotExists('nominatif',$harian)) {
        sendResponse(200,"Snapshot tidak tersedia",["params"=>compact('closing','harian','kantor'),"data"=>null]); return;
    }

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

    $sqlPairs = "
        SELECT
        c.kolektibilitas AS src_kol,
        COALESCE(h.kolektibilitas, 'LUNAS') AS dst_kol,
        COUNT(*) AS noa,
        SUM(c.baki_debet)             AS os_c,
        SUM(COALESCE(h.baki_debet,0)) AS os_h
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

    $SRC = ['L','DP','KL','D','M']; $DST = ['L','DP','KL','D','M','LUNAS'];
    $pair = [];
    foreach ($pairs as $r) { $pair[$r['src_kol']][$r['dst_kol']] = ['noa'=>(int)$r['noa'], 'os_c'=>(float)$r['os_c'], 'os_h'=>(float)$r['os_h']]; }

    $rows = [];
    $totals = [
        'm1_noa'=>0,'m1_os'=>0.0,
        'actual'=>array_fill_keys($DST,['noa'=>0,'os'=>0.0]),
        'angsuran_os_total'=>0.0, 'pelunasan'=>['noa'=>0,'os'=>0.0],
        'run_off_total'=>0.0, 'share_vs_angsuran_total'=>['L'=>0,'DP'=>0,'KL'=>0,'D'=>0,'M'=>0]
    ];

    foreach ($SRC as $src) {
      $m1_noa=0;$m1_os=0.0;
      $actual=[]; $sumActualAll=0.0; $sumActualOS_LM=0.0;
      $angsuranParts=['L'=>0.0,'DP'=>0.0,'KL'=>0.0,'D'=>0.0,'M'=>0.0];

      foreach ($DST as $dst) {
        $p = $pair[$src][$dst] ?? ['noa'=>0,'os_c'=>0.0,'os_h'=>0.0];
        $m1_noa += $p['noa']; $m1_os += $p['os_c'];
        $act_os  = ($dst==='LUNAS') ? $p['os_c'] : $p['os_h'];
        $actual[$dst] = ['noa'=>$p['noa'],'os'=>$act_os,'share'=>null];
        $totals['actual'][$dst]['noa'] += $p['noa']; $totals['actual'][$dst]['os'] += $act_os;

        if ($dst!=='LUNAS'){ $angsuranParts[$dst]+=max(0.0,$p['os_c']-$p['os_h']); $sumActualAll+=$p['os_h']; $sumActualOS_LM+=$p['os_h']; }
        else { $sumActualAll+=$p['os_c']; $totals['pelunasan']['noa']+=$p['noa']; $totals['pelunasan']['os']+=$p['os_c']; }
      }

      $run_off = $sumActualOS_LM - $m1_os;
      $run_off_ansuran = max(0.0,$m1_os - $sumActualAll);

      $den = $run_off_ansuran;
      foreach (['L','DP','KL','D','M'] as $dst){
        $num=$angsuranParts[$dst];
        $actual[$dst]['share'] = $den>0?round($num/$den*100,2):0.0;
        $totals['share_vs_angsuran_total'][$dst] += $num;
      }

      $rows[]=[
        'kol'=>$src,
        'm1'=>['noa'=>$m1_noa,'os'=>$m1_os],
        'actual'=>[
          'L'=>$actual['L'],'DP'=>$actual['DP'],'KL'=>$actual['KL'],'D'=>$actual['D'],'M'=>$actual['M'],'Lunas'=>$actual['LUNAS']
        ],
        'run_off'=>$run_off,'run_off_ansuran'=>$run_off_ansuran
      ];

      $totals['m1_noa']+=$m1_noa; $totals['m1_os']+=$m1_os;
      $totals['angsuran_os_total']+=$run_off_ansuran; $totals['run_off_total']+=$run_off;
    }

    $denTot = $totals['angsuran_os_total']>0?$totals['angsuran_os_total']:0.0;
    foreach (['L','DP','KL','D','M'] as $dst){
      $num=(float)$totals['share_vs_angsuran_total'][$dst];
      $totals['share_vs_angsuran_total'][$dst]=$denTot>0?round($num/$denTot*100,2):0.0;
    }

    sendResponse(200,"OK",[
      'params'=>['kode_kantor'=>$kantor,'closing_date'=>$closing,'harian_date'=>$harian],
      'data'=>[
        'rows'=>$rows,
        'totals'=>[
          'm1_noa'=>$totals['m1_noa'],'m1_os'=>$totals['m1_os'],
          'actual'=>$totals['actual'],
          'angsuran_os_total'=>$totals['angsuran_os_total'],
          'pelunasan'=>$totals['pelunasan'],
          'run_off_total'=>$totals['run_off_total'],
          'share_vs_angsuran_total'=>$totals['share_vs_angsuran_total']
        ]
      ]
    ]);
  }

  /* ===========================================================
     CKPN PER BUCKET – OK
     =========================================================== */
  public function getBucketCkpn($input=null){
    $b = is_array($input)?$input:(json_decode(file_get_contents('php://input'),true) ?: []);
    $closing = $this->asDate($b['closing_date'] ?? null);
    $harian  = $this->asDate($b['harian_date']  ?? null);
    $kc_raw  = $b['kode_kantor'] ?? null;
    $kc      = ($kc_raw===null || $kc_raw==='') ? null : str_pad((string)$kc_raw,3,'0',STR_PAD_LEFT);
    if (!$closing || !$harian) return sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)");

    [$defs,$nameMap,$tagMap] = $this->loadBuckets();
    $order = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];

    $M1  = $this->computeCKPNForDate($closing,$kc,$defs);
    $CUR = $this->computeCKPNForDate($harian ,$kc,$defs);

    $o_ckpn=0;
    foreach ($M1['accSet'] as $acc=>$_){ if (!isset($CUR['accSet'][$acc])) $o_ckpn += (int)round($M1['ckpnByAcc'][$acc] ?? 0); }

    $rows=[]; $totSC=['m1'=>0,'cur'=>0]; $totFE=['m1'=>0,'cur'=>0]; $totBE=['m1'=>0,'cur'=>0];

    foreach ($order as $code){
      $m1  = (int)round(($M1['perBucket'][$code]['ckpn'] ?? 0));
      $cur = (int)round(($CUR['perBucket'][$code]['ckpn'] ?? 0));
      $rows[]=['dpd_code'=>$code,'dpd_name'=>$nameMap[$code]??$code,'status_tag'=>$tagMap[$code]??null,'ckpn_m1'=>$m1,'ckpn_curr'=>$cur,'ckpn_inc'=>($cur-$m1)];
      $tag=$tagMap[$code]??null;
      if ($tag==='SC'){ $totSC['m1']+=$m1; $totSC['cur']+=$cur; }
      elseif($tag==='FE'){ $totFE['m1']+=$m1; $totFE['cur']+=$cur; }
      elseif($tag==='BE'){ $totBE['m1']+=$m1; $totBE['cur']+=$cur; }
    }

    $row_tot_sc=['dpd_code'=>'TOTAL_SC','dpd_name'=>'TOTAL SC','status_tag'=>null,'ckpn_m1'=>$totSC['m1'],'ckpn_curr'=>$totSC['cur'],'ckpn_inc'=>($totSC['cur']-$totSC['m1'])];
    $row_tot_fe=['dpd_code'=>'TOTAL_FE','dpd_name'=>'TOTAL FE','status_tag'=>null,'ckpn_m1'=>$totFE['m1'],'ckpn_curr'=>$totFE['cur'],'ckpn_inc'=>($totFE['cur']-$totFE['m1'])];
    $row_tot_be=['dpd_code'=>'TOTAL_BE','dpd_name'=>'TOTAL BE','status_tag'=>null,'ckpn_m1'=>$totBE['m1'],'ckpn_curr'=>$totBE['cur'],'ckpn_inc'=>($totBE['cur']-$totBE['m1'])];

    $gt_m1=(int)$totSC['m1']+(int)$totFE['m1']+(int)$totBE['m1'];
    $gt_cur=(int)$totSC['cur']+(int)$totFE['cur']+(int)$totBE['cur'];
    $row_grand=['dpd_code'=>'GRAND_TOTAL','dpd_name'=>'GRAND TOTAL','status_tag'=>null,'ckpn_m1'=>$gt_m1,'ckpn_curr'=>$gt_cur,'ckpn_inc'=>($gt_cur-$gt_m1)];

    $rows[]=$row_tot_sc; $rows[]=$row_tot_fe; $rows[]=$row_tot_be; $rows[]=$row_grand;
    $rows[]=['dpd_code'=>'O','dpd_name'=>'O_Lunas','status_tag'=>null,'ckpn_m1'=>(int)$o_ckpn,'ckpn_curr'=>0,'ckpn_inc'=>(0-(int)$o_ckpn)];

    return sendResponse(200,"OK",[
      'closing_date'=>$closing,'harian_date'=>$harian,'kode_kantor'=>$kc,
      'rows'=>$rows,
      'total_sc'=>$row_tot_sc,'total_fe'=>$row_tot_fe,'total_be'=>$row_tot_be,
      'grand_total'=>$row_grand,
      'source'=>['closing'=>$M1['source'],'current'=>$CUR['source']]
    ]);
  }

  /* ===========================================================
     FLOW MIGRASI OS PER BUCKET – OK
     =========================================================== */
  public function migrasiBucketOsc($input=null){
    $b = is_array($input)?$input:(json_decode(file_get_contents('php://input'),true) ?: []);
    $closing = $this->asDate($b['closing_date'] ?? null);
    $harian  = $this->asDate($b['harian_date']  ?? null);
    $kc_raw  = $b['kode_kantor'] ?? null;
    $kc      = ($kc_raw===null || $kc_raw==='') ? null : str_pad((string)$kc_raw,3,'0',STR_PAD_LEFT);
    if (!$closing || !$harian) return sendResponse(400,"closing_date & harian_date wajib (YYYY-MM-DD)");

    [$defs,$nameMap,$tagMap] = $this->loadBuckets();
    $order   = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
    $orderTo = array_merge($order, ['O']);

    $M1  = $this->computeOSForDate($closing,$kc,$defs);
    $CUR = $this->computeOSForDate($harian ,$kc,$defs);

    $matrix = [];
    foreach ($order as $f){ foreach ($orderTo as $t){ $matrix[$f][$t] = ['noa'=>0,'os_curr'=>0,'os_m1'=>0,'os_used'=>0]; } }

    $fromTotals = [];
    foreach ($order as $f){ $fromTotals[$f] = ['noa_m1'=>0,'os_m1'=>0,'noa_curr_from'=>0,'os_curr_from'=>0,'inc_noa'=>0,'inc_os'=>0]; }

    $grand_m1_noa=0; $grand_m1_os=0; $grand_cur_noa_fromPop=0; $grand_cur_os_fromPop=0;

    foreach ($M1['accSet'] as $acc => $_){
      $from  = $M1['bucketByAcc'][$acc] ?? 'A'; if (!in_array($from,$order,true)) $from='A';
      $os_m1 = (int)round($M1['osByAcc'][$acc] ?? 0);

      $to    = $CUR['bucketByAcc'][$acc] ?? 'O'; if (!in_array($to,$orderTo,true)) $to='O';
      $os_cur = (int)round($CUR['osByAcc'][$acc] ?? 0);
      $os_used = ($to==='O') ? $os_m1 : $os_cur;

      $fromTotals[$from]['noa_m1']++; $fromTotals[$from]['os_m1'] += $os_m1;
      $fromTotals[$from]['noa_curr_from']++; $fromTotals[$from]['os_curr_from'] += $os_cur;

      $grand_m1_noa++; $grand_m1_os += $os_m1;
      $grand_cur_noa_fromPop++; $grand_cur_os_fromPop += $os_cur;

      $matrix[$from][$to]['noa']++;
      $matrix[$from][$to]['os_curr'] += $os_cur;
      $matrix[$from][$to]['os_m1']   += $os_m1;
      $matrix[$from][$to]['os_used'] += $os_used;
    }

    $realisasi_total=['noa'=>0,'os'=>0]; $realisasi_by_bucket=[];
    foreach ($order as $t){ $realisasi_by_bucket[$t]=['noa'=>0,'os'=>0]; }
    foreach ($CUR['accSet'] as $acc => $_){
      if (!isset($M1['accSet'][$acc])){
        $to = $CUR['bucketByAcc'][$acc] ?? 'A'; if (!in_array($to,$order,true)) $to='A';
        $os_cur = (int)round($CUR['osByAcc'][$acc] ?? 0);
        $realisasi_total['noa']++; $realisasi_total['os'] += $os_cur;
        $realisasi_by_bucket[$to]['noa']++; $realisasi_by_bucket[$to]['os'] += $os_cur;
      }
    }

    foreach ($fromTotals as $f=>&$t){ $t['inc_noa']=(int)($t['noa_curr_from']-$t['noa_m1']); $t['inc_os']=(int)($t['os_curr_from']-$t['os_m1']); } unset($t);

    $out=[];
    foreach ($order as $from){
      $den=(int)$fromTotals[$from]['os_m1'];
      foreach ($orderTo as $to){
        $cell=$matrix[$from][$to]; $num=(int)$cell['os_used'];
        $pct=($den>0)?round($num/$den*100,2):null;
        $out[]=[
          'from_bucket'=>$from,'to_bucket'=>$to,'noa'=>(int)$cell['noa'],
          'os'=>$num,'os_curr'=>(int)$cell['os_curr'],'os_m1'=>(int)$cell['os_m1'],
          'denominator_os_m1'=>$den,'actual_pct'=>$pct
        ];
      }
    }

    return sendResponse(200,"OK",[
      'closing_date'=>$closing,'harian_date'=>$harian,'kode_kantor'=>$kc,
      'order_to'=>$orderTo,'from_totals'=>$fromTotals,'matrix'=>$out,
      'realisasi'=>['total'=>['noa'=>(int)$realisasi_total['noa'],'os'=>(int)$realisasi_total['os']],'by_bucket'=>$realisasi_by_bucket],
      'grand'=>['m1_noa'=>(int)$grand_m1_noa,'m1_os'=>(int)$grand_m1_os,'curr_noa_from_pop'=>(int)$grand_cur_noa_fromPop,'curr_os_from_pop'=>(int)$grand_cur_os_fromPop],
      'note'=>'TO=O (LUNAS) memakai OS M-1 untuk pembilang %.'
    ]);
  }

  /* ===========================================================
     DETAIL MIGRASI (REALISASI / ACTUAL / O_LUNAS)
     =========================================================== */

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

    if ($fb === 'REALISASI') {
      if ($kc===null || $kc==='000') return sendResponse(400,"Realisasi: kode_kantor wajib (tidak boleh konsolidasi).");
      $tbFilter = ($tb!=='' && in_array($tb,$VALID,true)) ? $tb : null;
      return $this->getMigrasiBucketDetailRealisasi($closing,$harian,$kc,$tbFilter);
    }

    if ($tb === 'O' || $tb === 'O_LUNAS') {
      if (!in_array($fb,$VALID,true)) return sendResponse(400,"O_LUNAS: from_bucket wajib A..N.");
      return $this->getMigrasiBucketDetailOLunas($closing,$harian,$kc,$fb);
    }

    if (!in_array($fb,$VALID,true) || !in_array($tb,$VALID,true))
      return sendResponse(400,"Actual: from_bucket & to_bucket wajib A..N.");

    return $this->getMigrasiBucketDetailActual($closing,$harian,$kc,$fb,$tb);
  }

  /* ---------- REALISASI (FAST) ---------- */
  public function getMigrasiBucketDetailRealisasi(string $closing, string $harian, string $kc, ?string $tbFilter=null)
  {
    [$dsH,$deH] = $this->dayRange($harian);

    $sql = "
      SELECT
        LPAD(CAST(nh.kode_cabang AS CHAR),3,'0') AS kode_cabang,
        nh.no_rekening, nh.nama_nasabah, NULL AS alamat,
        'REALISASI' AS from_bucket,
        rb2.dpd_code AS to_bucket,
        nh.baki_debet AS baki_debet,
        nh.kolektibilitas, nh.kode_produk,
        nh.tunggakan_pokok, nh.tunggakan_bunga,
        nh.hari_menunggak, nh.hari_menunggak_pokok, nh.hari_menunggak_bunga,
        nh.saldo_bank, nh.tgl_jatuh_tempo, nh.tgl_realisasi,
        NULL AS angsuran_pokok, NULL AS angsuran_bunga, NULL AS tgl_trans_terakhir,
        NULL AS os_m1, nh.baki_debet AS os_curr,
        NULL AS ckpn_actual, NULL AS ckpn_m1
      FROM nominatif nh
      JOIN ref_dpd_bucket rb2
        ON nh.hari_menunggak >= rb2.min_day
       AND (rb2.max_day IS NULL OR nh.hari_menunggak <= rb2.max_day)
      WHERE nh.created >= :dsH1 AND nh.created < :deH1
        AND LPAD(CAST(nh.kode_cabang AS CHAR),3,'0') = :kcH
        AND nh.tgl_realisasi >  :cld
        AND nh.tgl_realisasi <= :hrd
        ".($tbFilter?" AND rb2.dpd_code = :tb ":"")."
      ORDER BY nh.baki_debet DESC
    ";

    $st = $this->pdo->prepare($sql);
    $st->bindValue(':dsH1',$dsH); $st->bindValue(':deH1',$deH);
    $st->bindValue(':kcH',$kc); $st->bindValue(':cld',$closing); $st->bindValue(':hrd',$harian);
    if ($tbFilter) $st->bindValue(':tb',$tbFilter);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $num=['baki_debet','tunggakan_pokok','tunggakan_bunga','hari_menunggak','hari_menunggak_pokok','hari_menunggak_bunga','saldo_bank','os_curr'];
    foreach($rows as &$r){ foreach($num as $f){ if(isset($r[$f])&&$r[$f]!==null&&$r[$f]!=='') $r[$f]+=0; } } unset($r);
    return sendResponse(200,"OK (realisasi detail)", $rows);
  }

  /* ---------- ACTUAL A..N→A..N (FAST) ---------- */
  public function getMigrasiBucketDetailActual(string $closing, string $harian, ?string $kc, string $fb, string $tb){
    if ($kc===null || $kc==='000') return sendResponse(400, "Pilih kode_kantor spesifik (mis. '004') untuk detail ACTUAL.");
    $kc = str_pad($kc, 3, '0', STR_PAD_LEFT);

    [$dsC,$deC] = $this->dayRange($closing);
    [$dsH,$deH] = $this->dayRange($harian);

    // STRAIGHT_JOIN memaksa urutan join sesuai select agar gunakan index pada nominatif (created, kode_cabang, no_rekening)
    $sql = "/*+ SET_VAR(sort_buffer_size=64M) */ SELECT
        h.kode_cabang, h.no_rekening, h.nama_nasabah, h.alamat,
        rbf.dpd_code AS from_bucket, rbf.dpd_name AS from_bucket_name, rbf.status_tag AS from_status_tag, rbf.min_day AS from_min_day, rbf.max_day AS from_max_day,
        rbt.dpd_code AS to_bucket,   rbt.dpd_name   AS to_bucket_name,   rbt.status_tag  AS to_status_tag,   rbt.min_day  AS to_min_day,  rbt.max_day  AS to_max_day,
        h.baki_debet AS baki_debet, h.kolektibilitas, h.kode_produk, h.tunggakan_pokok, h.tunggakan_bunga,
        h.hari_menunggak, h.hari_menunggak_pokok, h.hari_menunggak_bunga, h.saldo_bank, h.tgl_jatuh_tempo,
        c.baki_debet AS os_m1, h.baki_debet AS os_curr,
        NULL AS angsuran_pokok, NULL AS angsuran_bunga, NULL AS tgl_trans_terakhir
      FROM nominatif h
      JOIN nominatif c
        ON c.no_rekening = h.no_rekening
       AND c.created >= :dsC AND c.created < :deC
       AND c.kode_cabang = :kcC
      JOIN ref_dpd_bucket rbf
        ON c.hari_menunggak >= rbf.min_day
       AND (rbf.max_day IS NULL OR c.hari_menunggak <= rbf.max_day)
       AND rbf.dpd_code = :fb
      JOIN ref_dpd_bucket rbt
        ON h.hari_menunggak >= rbt.min_day
       AND (rbt.max_day IS NULL OR h.hari_menunggak <= rbt.max_day)
       AND rbt.dpd_code = :tb
      WHERE h.created >= :dsH AND h.created < :deH
        AND h.kode_cabang = :kcH
      ORDER BY h.baki_debet DESC";

    $st=$this->pdo->prepare($sql);
    $st->bindValue(':dsC',$dsC); $st->bindValue(':deC',$deC); $st->bindValue(':kcC',$kc);
    $st->bindValue(':dsH',$dsH); $st->bindValue(':deH',$deH); $st->bindValue(':kcH',$kc);
    $st->bindValue(':fb',$fb);   $st->bindValue(':tb',$tb);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    return $this->attachCkpnAndCast($rows, $harian, $closing, $kc, 'actual');
  }

  /* ---------- O_LUNAS (SUPER FAST) ---------- */
  public function getMigrasiBucketDetailOLunas(string $closing, string $harian, ?string $kc, string $fromBucket){
    if ($kc===null || $kc==='000') return sendResponse(400, "Pilih kode_kantor spesifik (mis. '004') untuk detail O_LUNAS.");
    $kc = str_pad($kc, 3, '0', STR_PAD_LEFT);

    [$dsC,$deC] = $this->dayRange($closing);
    [$dsH,$deH] = $this->dayRange($harian);

    // NOT EXISTS jauh lebih cepat daripada LEFT JOIN IS NULL pada dataset besar
    $sql = "
      SELECT
        c.kode_cabang, c.no_rekening, c.nama_nasabah, c.alamat,
        rbf.dpd_code AS from_bucket, rbf.dpd_name AS from_bucket_name, rbf.status_tag AS from_status_tag, rbf.min_day AS from_min_day, rbf.max_day AS from_max_day,
        'O' AS to_bucket, 'O_LUNAS' AS to_bucket_name, NULL AS to_status_tag, NULL AS to_min_day, NULL AS to_max_day,
        c.baki_debet AS baki_debet, c.kolektibilitas, c.kode_produk, c.tunggakan_pokok, c.tunggakan_bunga,
        c.hari_menunggak, c.hari_menunggak_pokok, c.hari_menunggak_bunga, c.saldo_bank, c.tgl_jatuh_tempo,
        c.baki_debet AS os_m1, NULL AS os_curr,
        NULL AS angsuran_pokok, NULL AS angsuran_bunga, NULL AS tgl_trans_terakhir
      FROM nominatif c
      JOIN ref_dpd_bucket rbf
        ON c.hari_menunggak >= rbf.min_day
       AND (rbf.max_day IS NULL OR c.hari_menunggak <= rbf.max_day)
       AND rbf.dpd_code = :fb
      WHERE c.created >= :dsC AND c.created < :deC
        AND c.kode_cabang = :kcC
        AND NOT EXISTS (
          SELECT 1
          FROM nominatif h
          WHERE h.no_rekening = c.no_rekening
            AND h.created >= :dsH AND h.created < :deH
            AND h.kode_cabang = :kcH
        )
      ORDER BY c.baki_debet DESC
    ";

    $st = $this->pdo->prepare($sql);
    $st->bindValue(':fb',$fromBucket);
    $st->bindValue(':dsC',$dsC); $st->bindValue(':deC',$deC); $st->bindValue(':kcC',$kc);
    $st->bindValue(':dsH',$dsH); $st->bindValue(':deH',$deH); $st->bindValue(':kcH',$kc);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    return $this->attachCkpnAndCast($rows, $harian, $closing, $kc, 'o_lunas');
  }

  /* ===========================================================
     UTILITIES
     =========================================================== */
  private function asDate($s) { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; }
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
    $def=[];$name=[];$tag=[];
    foreach ($rows as $r){
      $def[]=['code'=>$r['dpd_code'],'name'=>$r['dpd_name'],'min'=>(int)$r['min_day'],'max'=>is_null($r['max_day'])?null:(int)$r['max_day'],'tag'=>$r['status_tag']??null];
      $name[$r['dpd_code']]=$r['dpd_name']; $tag[$r['dpd_code']]=$r['status_tag']??null;
    }
    return [$def,$name,$tag];
  }
  private function dpdToCode(int $dpd, array $defs): ?string {
    foreach ($defs as $b) { if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) return $b['code']; }
    return null;
  }
  private function dayRange(string $d): array { return [$d." 00:00:00", date('Y-m-d', strtotime("$d +1 day"))." 00:00:00"]; }

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

    $sumPer=[]; $accSet=[]; $osByAcc=[]; $bucketByAcc=[];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $acc=$r['no_rekening']; $dpd=(int)($r['hari_menunggak']??0); $os=(float)($r['baki_debet']??0);
      $code=$this->dpdToCode($dpd,$defs) ?? 'A';
      if (!isset($sumPer[$code])) $sumPer[$code]=['noa'=>0,'os'=>0.0];
      $sumPer[$code]['noa']++; $sumPer[$code]['os'] += $os;
      $accSet[$acc]=true; $osByAcc[$acc]=$os; $bucketByAcc[$acc]=$code;
    }
    foreach ($sumPer as &$v){ $v['os']=(int)round($v['os']); } unset($v);

    return ['perBucket'=>$sumPer,'accSet'=>$accSet,'osByAcc'=>$osByAcc,'bucketByAcc'=>$bucketByAcc];
  }

  private function loadGlobalLGD(string $harian_date): float {
    try {
      $st = $this->pdo->prepare("SELECT lgd_percent FROM lgd_current WHERE created <= ? ORDER BY created DESC LIMIT 1");
      $st->execute([$harian_date]);
      $v=$st->fetchColumn();
      return ($v!==false)?(float)$v:59.48;
    } catch (PDOException $e) { return 59.48; }
  }
  private function loadPdMap(string $d): array {
    $pdMap=[];
    try {
      $st=$this->pdo->prepare("
        SELECT p.product_code, p.dpd_code, p.pd_percent
        FROM pd_current p
        JOIN (
          SELECT product_code, dpd_code, MAX(created) AS created
          FROM pd_current WHERE created <= ?
          GROUP BY product_code, dpd_code
        ) x ON x.product_code=p.product_code AND x.dpd_code=p.dpd_code AND x.created=p.created
      "); $st->execute([$d]);
      foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r){
        $pdMap[(int)$r['product_code']][$r['dpd_code']] = (float)$r['pd_percent'];
      }
      if (!empty($pdMap)) return $pdMap;
    } catch (PDOException $e) {}
    try {
      $q=$this->pdo->query("SELECT product_code, dpd_code, pd_percent FROM pd_current");
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

  /* ===================== CKPN HELPERS ===================== */
  private function fetchSnapCkpnMap(string $d, ?string $kc, array $accs): array {
    if (!$accs) return [];
    [$ds,$de] = $this->dayRange($d);
    $out=[];
    foreach (array_chunk($accs, 500) as $chunk) {
      $ph = implode(',', array_fill(0, count($chunk), '?'));
      $sql = "SELECT no_rekening, nilai_ckpn
              FROM nominatif_ckpn
              WHERE created >= ? AND created < ?
                AND no_rekening IN ($ph)";
      $params = array_merge([$ds,$de], $chunk);
      if ($kc !== null && $kc !== '000') { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
      else { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }
      $st=$this->pdo->prepare($sql); $st->execute($params);
      while($r=$st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (float)$r['nilai_ckpn'];
    }
    return $out;
  }
  private function fetchIndivCkpnMap(string $d, array $accs): array {
    if (!$accs) return [];
    $out=[];
    foreach (array_chunk($accs, 500) as $chunk){
      $ph=implode(',', array_fill(0, count($chunk), '?'));
      $sql="SELECT ci.no_rekening, ci.nilai_ckpn
            FROM ckpn_individual ci
            JOIN (SELECT no_rekening, MAX(created) AS created FROM ckpn_individual
                  WHERE created <= ? AND no_rekening IN ($ph) GROUP BY no_rekening) x
              ON x.no_rekening=ci.no_rekening AND x.created=ci.created";
      $params=array_merge([$d],$chunk);
      $st=$this->pdo->prepare($sql); $st->execute($params);
      while($r=$st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (float)$r['nilai_ckpn'];
    }
    return $out;
  }
  private function fetchRestrukSet(string $d, array $accs): array {
    if (!$accs) return [];
    $set=[];
    foreach (array_chunk($accs, 500) as $chunk){
      $ph=implode(',', array_fill(0, count($chunk), '?'));
      $sql="SELECT nr.no_rekening
            FROM nom_restruk nr
            JOIN (SELECT no_rekening, MAX(created) AS created FROM nom_restruk
                  WHERE created <= ? AND no_rekening IN ($ph) GROUP BY no_rekening) x
              ON x.no_rekening=nr.no_rekening AND x.created=nr.created";
      $params=array_merge([$d],$chunk);
      $st=$this->pdo->prepare($sql); $st->execute($params);
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
    if (!$bucket) { [$defs] = $this->loadBuckets(); $bucket = $this->dpdToCode($dpd,$defs) ?? 'A'; }

    $isRestruk = $acc ? isset($restrukSet[$acc]) : false;
    if ($dpd <= 7 && !$isRestruk) return 0;

    $pd = 0.0;
    if ($prod !== null && isset($pdMap[$prod][$bucket])) $pd = (float)$pdMap[$prod][$bucket];
    return (int)round($ead * ($pd/100.0) * ($LGD/100.0));
  }

  /* ===================== ANGSURAN & CKPN M-1 ===================== */
  private function fetchAngsuranMap(string $closing, string $harian, ?string $kc, array $accs): array {
    if (!$accs) return [];
    $out=[];
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
      $st=$this->pdo->prepare($sql); $st->execute($params);
      while($r=$st->fetch(PDO::FETCH_ASSOC)){
        $out[$r['no_rekening']]=[
          'pokok'=>(int)round($r['sum_pokok']??0),
          'bunga'=>(int)round($r['sum_bunga']??0),
          'last_tgl_trans'=>$r['last_tgl_trans']??null
        ];
      }
    }
    return $out;
  }
  private function fetchCkpnM1Map(string $closing, ?string $kc, array $accs): array {
    if (!$accs) return [];
    [$dsC,$deC] = $this->dayRange($closing);
    $out=[];
    foreach (array_chunk($accs, 500) as $chunk) {
      $ph = implode(',', array_fill(0, count($chunk), '?'));
      $sql="SELECT no_rekening, nilai_ckpn
            FROM nominatif_ckpn
            WHERE created >= ? AND created < ?
              AND no_rekening IN ($ph)";
      $params=array_merge([$dsC,$deC],$chunk);
      if ($kc !== null && $kc !== '000') { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') = ?"; $params[]=$kc; }
      else { $sql .= " AND LPAD(CAST(kode_cabang AS CHAR),3,'0') <> '000'"; }
      $st=$this->pdo->prepare($sql); $st->execute($params);
      while($r=$st->fetch(PDO::FETCH_ASSOC)) $out[$r['no_rekening']] = (int)round($r['nilai_ckpn']??0);
    }
    return $out;
  }

  /* ===================== ATTACH & CAST (PERFORMANCE-AWARE) ===================== */
  private function attachCkpnAndCast(array $rows, string $harian, string $closing, ?string $kc, string $mode){
    $accs = array_values(array_unique(array_filter(array_map(fn($r)=>$r['no_rekening'] ?? null, $rows))));

    // 1) Map Angsuran (dipakai semua mode)
    $angsMap = $this->fetchAngsuranMap($closing, $harian, $kc, $accs);

    // 2) CKPN ACTUAL hanya untuk mode 'actual'
    $LGD=$pdMap=$snapMapH=$indivMap=$restruk=[];
    if ($mode!=='o_lunas'){
      $LGD   = $this->loadGlobalLGD($harian);
      $pdMap = $this->loadPdMap($harian);
      $snapMapH = $this->fetchSnapCkpnMap($harian, $kc, $accs);
      $indivMap = $this->fetchIndivCkpnMap($harian, $accs);
      $restruk  = $this->fetchRestrukSet($harian, $accs);
    }

    // 3) CKPN M-1:
    $ckpnM1Map = ($mode==='o_lunas') ? $this->fetchCkpnM1Map($closing, $kc, $accs) : [];

    // 4) Fallback CKPN M-1 compute (hanya kalau O_LUNAS & snapshot kosong untuk akun tsb)
    if ($mode==='o_lunas' && count($ckpnM1Map) < count($accs)){
      $pdMapC = $this->loadPdMap($closing);
      $LGDC   = $this->loadGlobalLGD($closing);
      foreach ($rows as $r){
        $acc = $r['no_rekening'] ?? null; if (!$acc || isset($ckpnM1Map[$acc])) continue;
        $fake = [
          'no_rekening'=>$acc,
          'saldo_bank' => $r['saldo_bank'] ?? 0,
          'kode_produk'=> $r['kode_produk'] ?? null,
          'hari_menunggak'=> (int)($r['hari_menunggak'] ?? 0),
          'from_bucket'=> $r['from_bucket'] ?? null
        ];
        $ckpnM1Map[$acc] = $this->computeCkpnForRow($fake, $closing, $pdMapC, $LGDC, [], [], null);
      }
    }

    foreach ($rows as &$r){
      $acc = $r['no_rekening'] ?? null;

      $r['angsuran_pokok']     = isset($angsMap[$acc]) ? (int)$angsMap[$acc]['pokok'] : 0;
      $r['angsuran_bunga']     = isset($angsMap[$acc]) ? (int)$angsMap[$acc]['bunga'] : 0;
      $r['tgl_trans_terakhir'] = $angsMap[$acc]['last_tgl_trans'] ?? null;

      if ($mode==='o_lunas'){ $r['ckpn_actual']=0; $r['ckpn_m1']=(int)round($ckpnM1Map[$acc] ?? 0); }
      else {
        $snap = $snapMapH[$acc] ?? null;
        $r['ckpn_actual'] = $this->computeCkpnForRow($r, $harian, $pdMap, $LGD, $indivMap, $restruk, $snap);
        $r['ckpn_m1'] = null; // sesuai kebutuhan: actual tidak menampilkan ckpn_m1
      }
    } unset($r);

    $num = ['baki_debet','tunggakan_pokok','tunggakan_bunga','hari_menunggak','hari_menunggak_pokok','hari_menunggak_bunga','saldo_bank','angsuran_pokok','angsuran_bunga','os_m1','os_curr','ckpn_actual','ckpn_m1'];
    foreach ($rows as &$r){ foreach ($num as $f){ if (array_key_exists($f,$r) && $r[$f]!==null && $r[$f]!=='') $r[$f]+=0; } } unset($r);

    return sendResponse(200,"OK ($mode detail)", $rows);
  }

}

