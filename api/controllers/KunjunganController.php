<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/ckpn.php';
require_once __DIR__ . '/../helpers/upload_helpers.php';

class KunjunganController
{
    private PDO $pdo;
    private CkpnUtils $ckpn;

    public function __construct(PDO $pdo)
    {
        $this->pdo  = $pdo;
        $this->ckpn = new CkpnUtils($pdo, [$this, 'dayRange']);
    }

    /* ===================== PIPELANE (tetap) ===================== */
    public function getPipelaneMapping($input = null)
    {
        $b   = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        $kc  = isset($b['kode_kantor']) ? str_pad((string)$b['kode_kantor'], 3, '0', STR_PAD_LEFT) : null;
        $add = $this->asDate($b['add_data']     ?? null);
        $m1  = $this->asDate($b['closing_date'] ?? null);
        $har = $this->asDate($b['harian_date']  ?? null);

        if (!$kc || !$add || !$m1) return sendResponse(400, "kode_kantor, add_data, closing_date wajib");

        [$defs, $nameMap, $tagMap] = $this->loadBuckets();
        if (empty($defs)) return sendResponse(200, "Master bucket kosong", ['rows'=>[]]);

        $newCols = function () use ($defs) {
            $cols = [];
            foreach ($defs as $bk) $cols[$bk['code']] = ['noa'=>0,'os'=>0];
            $cols['_SC']=['noa'=>0,'os'=>0]; $cols['_FE']=['noa'=>0,'os'=>0]; $cols['_BE']=['noa'=>0,'os'=>0];
            $cols['_TOTAL']=['noa'=>0,'os'=>0]; return $cols;
        };

        [$dsM1,$deM1] = $this->dayRange($m1);
        $stM1 = $this->pdo->prepare("
            SELECT n.no_rekening, n.baki_debet, n.hari_menunggak
            FROM nominatif n
            WHERE n.created >= ? AND n.created < ? AND LPAD(CAST(n.kode_cabang AS CHAR),3,'0') = ?
        ");
        $stM1->execute([$dsM1,$deM1,$kc]);
        $rowsM1 = $stM1->fetchAll(PDO::FETCH_ASSOC);

        [$dsA,$deA] = $this->dayRange($add);
        $stMap = $this->pdo->prepare("
            SELECT ah.no_rekening, COALESCE(NULLIF(TRIM(ah.ao_remedial),''),'vacant') AS ao
            FROM account_handle ah
            WHERE ah.created >= ? AND ah.created < ? AND ah.kode_kantor = ?
        ");
        $stMap->execute([$dsA,$deA,$kc]);
        $mapAO=[]; foreach ($stMap->fetchAll(PDO::FETCH_ASSOC) as $r) $mapAO[$r['no_rekening']] = $r['ao'];

        $agg=[]; $sumAll=$newCols();
        foreach ($rowsM1 as $r){
            $norek=$r['no_rekening'];
            $os=(int)round((float)($r['baki_debet']??0));
            $dpd=(int)($r['hari_menunggak']??0);
            $ao=$mapAO[$norek] ?? 'vacant';
            $code=$this->dpdToCode($dpd,$defs) ?? $defs[0]['code'];
            $tag=$tagMap[$code] ?? null;

            if (!isset($agg[$ao])) $agg[$ao]=$newCols();
            $agg[$ao][$code]['noa']++; $agg[$ao][$code]['os']+=$os;
            if ($tag==='SC') { $agg[$ao]['_SC']['noa']++; $agg[$ao]['_SC']['os']+=$os; }
            elseif ($tag==='FE') { $agg[$ao]['_FE']['noa']++; $agg[$ao]['_FE']['os']+=$os; }
            elseif ($tag==='BE') { $agg[$ao]['_BE']['noa']++; $agg[$ao]['_BE']['os']+=$os; }
            $agg[$ao]['_TOTAL']['noa']++; $agg[$ao]['_TOTAL']['os']+=$os;

            $sumAll[$code]['noa']++; $sumAll[$code]['os']+=$os;
            if ($tag==='SC') { $sumAll['_SC']['noa']++; $sumAll['_SC']['os']+=$os; }
            elseif ($tag==='FE') { $sumAll['_FE']['noa']++; $sumAll['_FE']['os']+=$os; }
            elseif ($tag==='BE') { $sumAll['_BE']['noa']++; $sumAll['_BE']['os']+=$os; }
            $sumAll['_TOTAL']['noa']++; $sumAll['_TOTAL']['os']+=$os;
        }
        ksort($agg,SORT_NATURAL);

        $rows=[];
        foreach ($agg as $ao=>$cols){
            $row=['ao_remedial'=>$ao,'buckets'=>[]];
            foreach ($defs as $bk){
                $code=$bk['code'];
                $row['buckets'][]=['code'=>$code,'name'=>$bk['name'],'noa'=>(int)$cols[$code]['noa'],'os'=>(int)$cols[$code]['os']];
            }
            $row['bucket_sc']=['noa'=>(int)$cols['_SC']['noa'],'os'=>(int)$cols['_SC']['os']];
            $row['bucket_fe']=['noa'=>(int)$cols['_FE']['noa'],'os'=>(int)$cols['_FE']['os']];
            $row['bucket_be']=['noa'=>(int)$cols['_BE']['noa'],'os'=>(int)$cols['_BE']['os']];
            $row['total']    =['noa'=>(int)$cols['_TOTAL']['noa'],'os'=>(int)$cols['_TOTAL']['os']];
            $rows[]=$row;
        }
        $totalRow=['ao_remedial'=>'TOTAL','buckets'=>[]];
        foreach ($defs as $bk){
            $code=$bk['code'];
            $totalRow['buckets'][]=['code'=>$code,'name'=>$bk['name'],'noa'=>(int)$sumAll[$code]['noa'],'os'=>(int)$sumAll[$code]['os']];
        }
        $totalRow['bucket_sc']=['noa'=>(int)$sumAll['_SC']['noa'],'os'=>(int)$sumAll['_SC']['os']];
        $totalRow['bucket_fe']=['noa'=>(int)$sumAll['_FE']['noa'],'os'=>(int)$sumAll['_FE']['os']];
        $totalRow['bucket_be']=['noa'=>(int)$sumAll['_BE']['noa'],'os'=>(int)$sumAll['_BE']['os']];
        $totalRow['total']    =['noa'=>(int)$sumAll['_TOTAL']['noa'],'os'=>(int)$sumAll['_TOTAL']['os']];
        $rows[]=$totalRow;

        return sendResponse(200,"OK",[
            'params'=>['kode_kantor'=>$kc,'add_data'=>$add,'closing_date'=>$m1,'harian_date'=>$har],
            'bucket_columns'=>array_map(fn($bk)=>['code'=>$bk['code'],'name'=>$bk['name'],'min'=>$bk['min'],'max'=>$bk['max'],'tag'=>$bk['tag']??null],$defs),
            'cluster_columns'=>['bucket_sc','bucket_fe','bucket_be','total'],
            'rows'=>$rows
        ]);
    }

    /* ===================== DETAIL (basis: account_handle closing) ===================== */
    public function getDetailNasabahFromHandle($input = null)
    {
        $b   = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        $kc  = isset($b['kode_kantor']) ? str_pad((string)$b['kode_kantor'],3,'0',STR_PAD_LEFT) : null;
        $clo = $this->asDate($b['closing_date'] ?? null);
        $har = $this->asDate($b['harian_date']  ?? null);
        if (!$kc || !$clo || !$har) return sendResponse(400,"kode_kantor, closing_date, harian_date wajib");

        // 1) account_handle (closing_date)
        [$dsC,$deC] = $this->dayRange($clo);
        $stAH = $this->pdo->prepare("
          SELECT
            ah.no_rekening,
            COALESCE(NULLIF(TRIM(ah.ao_remedial),''),'vacant') AS ao_remedial,
            ah.key_name,
            ah.plan_bucket,
            ah.nilai_ckpn        AS ckpn_m1,
            ah.nama_nasabah      AS nama_m1,
            ah.nama_produk,
            ah.kode_produk       AS kode_produk_m1,
            ah.bucket            AS bucket_m1,      -- ambil bucket dari account_handle
            ah.baki_debet        AS baki_debet_m1   -- OS M-1 dari account_handle
          FROM account_handle ah
          WHERE ah.created >= ? AND ah.created < ? AND ah.kode_kantor = ?
        ");
        $stAH->execute([$dsC,$deC,$kc]);
        $handles = $stAH->fetchAll(PDO::FETCH_ASSOC);
        if (!$handles) return sendResponse(200,"OK (tidak ada data account_handle pada closing_date)", ['rows'=>[]]);

        $ahByAcc=[]; $accList=[];
        foreach ($handles as $r){ $ahByAcc[$r['no_rekening']]=$r; $accList[]=$r['no_rekening']; }

        // 2) nominatif (harian_date) untuk norek tsb
        [$dsH,$deH] = $this->dayRange($har);
        $rowsHar=[];
        foreach (array_chunk($accList, 800) as $chunk){
            $ph = implode(',', array_fill(0,count($chunk),'?'));
            $sqlH = "
              SELECT n.no_rekening, n.nama_nasabah, n.baki_debet, n.saldo_bank,
                     n.hari_menunggak, n.tunggakan_pokok, n.tunggakan_bunga,
                     n.kolektibilitas AS kolek_update, n.kode_produk
              FROM nominatif n
              WHERE n.created >= ? AND n.created < ?
                AND LPAD(CAST(n.kode_cabang AS CHAR),3,'0') = ?
                AND n.no_rekening IN ($ph)
            ";
            $params = array_merge([$dsH,$deH,$kc], $chunk);
            $stH = $this->pdo->prepare($sqlH); $stH->execute($params);
            while ($r=$stH->fetch(PDO::FETCH_ASSOC)) $rowsHar[$r['no_rekening']]=$r;
        }

        // 3) master bucket + bahan CKPN/PD
        [$bucketDefs, $nameMap] = $this->loadBuckets();
        $LGD   = $this->ckpn->loadGlobalLGD($har);
        $pdMap = $this->ckpn->loadPdMap($har);
        $accHar  = array_keys($rowsHar);
        $snapMap = $this->ckpn->fetchSnapCkpnMap($har, $kc, $accHar);
        $indiv   = $this->ckpn->fetchIndivCkpnMap($har, $accHar);
        $restruk = $this->ckpn->fetchRestrukSet($har, $accHar);

        // 3b) AGREGASI ANGSURAN dari TRANSAKSI_KREDIT (window: tgl_trans > closing AND tgl_trans <= harian)
        $angsuranAgg = $this->aggregateKreditWindow($accList, $clo, $har);

        // 4) compose response
        $out=[];
        foreach ($ahByAcc as $acc=>$h){
            $harRow = $rowsHar[$acc] ?? null;

            // data angsuran agregat
            $angs = $angsuranAgg[$acc] ?? ['angsuran_pokok'=>0,'angsuran_bunga'=>0,'tgl_last_angsuran'=>null];

            if ($harRow){
                $code      = $this->dpdToCode((int)$harRow['hari_menunggak'], $bucketDefs) ?? 'A';
                $dpd_name  = $nameMap[$code] ?? $code;

                // pilih product_code (harian prioritas, fallback M-1 dari handle)
                $productCode = $harRow['kode_produk'] !== null && $harRow['kode_produk'] !== ''
                    ? (int)$harRow['kode_produk']
                    : (isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '' ? (int)$h['kode_produk_m1'] : null);

                $pdPercent = ($productCode !== null && isset($pdMap[$productCode][$code]))
                    ? (float)$pdMap[$productCode][$code]
                    : null;

                $ckpnAct = $this->ckpn->computeCkpnForRow(
                    [
                        'no_rekening'=>$acc,
                        'saldo_bank'=>$harRow['saldo_bank'],
                        'kode_produk'=>$productCode,
                        'hari_menunggak'=>$harRow['hari_menunggak'],
                        'to_bucket'=>$code
                    ],
                    $har, $pdMap, $LGD, $indiv, $restruk, ($snapMap[$acc] ?? null), $bucketDefs
                );

                $row = [
                    'no_rekening'        => $acc,
                    'ao_remedial'        => $h['ao_remedial'],
                    'key_name'           => $h['key_name'] ?? null,
                    'plan_bucket'        => $h['plan_bucket'] ?? null,

                    'nama_nasabah'       => $harRow['nama_nasabah'] ?? ($h['nama_m1'] ?? null),

                    // ==== ACTUAL (harian_date)
                    'baki_debet_update'  => (int)round((float)$harRow['baki_debet']),
                    'bucket_update'      => $dpd_name,

                    'saldo_bank'         => (int)round((float)$harRow['saldo_bank']),
                    'hari_menunggak'     => (int)$harRow['hari_menunggak'],
                    'tunggakan_pokok'    => (int)round((float)$harRow['tunggakan_pokok']),
                    'tunggakan_bunga'    => (int)round((float)$harRow['tunggakan_bunga']),
                    'kolek_update'       => $harRow['kolek_update'],

                    // ==== FROM account_handle (closing_date)
                    'bucket'             => $h['bucket_m1'] ?? null,
                    'baki_debet'         => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                    'nama_produk'        => $h['nama_produk'] ?? null,

                    // product & PD
                    'product_code'       => $productCode,
                    'pd_percent'         => $pdPercent,

                    // CKPN
                    'ckpn_m1'            => (int)round((float)($h['ckpn_m1'] ?? 0)),
                    'ckpn_actual'        => (int)round((float)$ckpnAct),
                    'inc_ckpn'           => (int)round(((float)$ckpnAct) - (float)($h['ckpn_m1'] ?? 0)),

                    // ====== ANGSURAN (sum transaksi_kredit window)
                    'angsuran_pokok'     => (int)round((float)$angs['angsuran_pokok']),
                    'angsuran_bunga'     => (int)round((float)$angs['angsuran_bunga']),
                    'tgl_trans_last_angsuran' => $angs['tgl_last_angsuran']
                ];
            } else {
                // LUNAS (tidak ada di harian)
                $row = [
                    'no_rekening'        => $acc,
                    'ao_remedial'        => $h['ao_remedial'],
                    'key_name'           => $h['key_name'] ?? null,
                    'plan_bucket'        => $h['plan_bucket'] ?? null,

                    'nama_nasabah'       => $h['nama_m1'] ?? null,

                    // ACTUAL (harian) tidak ada
                    'baki_debet_update'  => 0,
                    'bucket_update'      => 'O_Lunas',
                    'saldo_bank'         => 0,
                    'hari_menunggak'     => null,
                    'tunggakan_pokok'    => null,
                    'tunggakan_bunga'    => null,
                    'kolek_update'       => 'Lunas',

                    // FROM account_handle (closing)
                    'bucket'             => $h['bucket_m1'] ?? null,
                    'baki_debet'         => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                    'nama_produk'        => $h['nama_produk'] ?? null,

                    'product_code'       => isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '' ? (int)$h['kode_produk_m1'] : null,
                    'pd_percent'         => null,

                    'ckpn_m1'            => (int)round((float)($h['ckpn_m1'] ?? 0)),
                    'ckpn_actual'        => 0,
                    'inc_ckpn'           => (int)round(0 - (float)($h['ckpn_m1'] ?? 0)),

                    // ====== ANGSURAN (sum transaksi_kredit window) — tetap tampil
                    'angsuran_pokok'     => (int)round((float)$angs['angsuran_pokok']),
                    'angsuran_bunga'     => (int)round((float)$angs['angsuran_bunga']),
                    'tgl_trans_last_angsuran' => $angs['tgl_last_angsuran']
                ];
            }

            $out[]=$row;
        }

        usort($out, fn($a,$b)=>($b['baki_debet_update'] <=> $a['baki_debet_update']));

        return sendResponse(200,"OK (detail by account_handle closing)", [
            'params'=>[
                'kode_kantor'=>$kc,
                'closing_date'=>$clo,
                'harian_date'=>$har
            ],
            'rows'=>$out
        ]);
    }

    /* ===================== UTILITIES ===================== */
    private function asDate($s): ?string { if (!$s) return null; $t=strtotime($s); return $t?date('Y-m-d',$t):null; }

    public function dayRange(string $d): array {
        return [$d." 00:00:00", date('Y-m-d', strtotime("$d +1 day"))." 00:00:00"];
    }

    private function loadBuckets(): array {
        $rows = $this->pdo->query("
            SELECT dpd_code, dpd_name, min_day, max_day, status_tag
            FROM ref_dpd_bucket ORDER BY min_day
        ")->fetchAll(PDO::FETCH_ASSOC);
        $def=[]; $name=[]; $tag=[];
        foreach ($rows as $r){
            $def[]=['code'=>$r['dpd_code'],'name'=>$r['dpd_name'],'min'=>(int)$r['min_day'],'max'=>is_null($r['max_day'])?null:(int)$r['max_day'],'tag'=>$r['status_tag']??null];
            $name[$r['dpd_code']]=$r['dpd_name']; $tag[$r['dpd_code']]=$r['status_tag']??null;
        }
        return [$def,$name,$tag];
    }

    private function dpdToCode(int $dpd, array $defs): ?string {
        foreach ($defs as $b){ if ($dpd >= $b['min'] && ($b['max']===null || $dpd <= $b['max'])) return $b['code']; }
        return null;
    }

    /**
     * Agregasi ANGSURAN dari tabel transaksi_kredit untuk window:
     *   tgl_trans > $closing AND tgl_trans <= $harian
     * Return:
     *   [ no_rekening => ['angsuran_pokok'=>sum, 'angsuran_bunga'=>sum, 'tgl_last_angsuran'=>Y-m-d] ]
     */
    private function aggregateKreditWindow(array $accs, string $closing, string $harian): array
    {
        if (empty($accs)) return [];
        $out = [];

        foreach (array_chunk($accs, 800) as $chunk) {
            $ph = implode(',', array_fill(0, count($chunk), '?'));

            $sql = "
                SELECT
                    no_rekening,
                    COALESCE(SUM(angsuran_pokok), 0) AS angsuran_pokok,
                    COALESCE(SUM(angsuran_bunga), 0) AS angsuran_bunga,
                    MAX(tgl_trans) AS tgl_last_angsuran
                FROM transaksi_kredit
                WHERE tgl_trans > ? AND tgl_trans <= ?
                  AND no_rekening IN ($ph)
                GROUP BY no_rekening
            ";

            $params = array_merge([$closing, $harian], $chunk);
            $st = $this->pdo->prepare($sql);
            $st->execute($params);

            while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                $nr = $r['no_rekening'];
                $out[$nr] = [
                    'angsuran_pokok'      => (float)$r['angsuran_pokok'],
                    'angsuran_bunga'      => (float)$r['angsuran_bunga'],
                    'tgl_last_angsuran'   => $r['tgl_last_angsuran']
                ];
            }
        }
        return $out;
    }

    public function getAccountHandle($input = null, array $user)
    {
        // ===== 0) Params & auth (PIC dari token)
        $b   = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        $kc  = isset($b['kode_kantor']) ? str_pad((string)$b['kode_kantor'],3,'0',STR_PAD_LEFT) : null;
        $clo = $this->asDate($b['closing_date'] ?? null);
        $har = $this->asDate($b['harian_date']  ?? null);
        $pic = $user['full_name'] ?? null;
            if (!$pic && !empty($user['id'])) {
                $q = $this->pdo->prepare("SELECT full_name FROM users WHERE id=? LIMIT 1");
                $q->execute([$user['id']]);
                $pic = $q->fetchColumn();
            }
            if (!$pic) return sendResponse(400, "PIC login tidak ditemukan", null);

        if (!$kc || !$clo || !$har)       return sendResponse(400, "kode_kantor, closing_date, harian_date wajib");
        if ($pic === '')                  return sendResponse(401, "Unauthorized: full_name pada token kosong");

        // ===== 1) Ambil account_handle snapshot pada closing_date untuk PIC
        [$dsC,$deC] = $this->dayRange($clo);
        $sqlAH = "
        SELECT
            ah.no_rekening,
            COALESCE(NULLIF(TRIM(ah.ao_remedial),''),'vacant') AS ao_remedial,
            ah.key_name,
            ah.plan_bucket,
            ah.nilai_ckpn        AS ckpn_m1,
            ah.nama_nasabah      AS nama_m1,
            ah.nama_produk,
            ah.kode_produk       AS kode_produk_m1,
            ah.bucket            AS bucket_m1,
            ah.baki_debet        AS baki_debet_m1
        FROM account_handle ah
        WHERE ah.created >= ? AND ah.created < ?
            AND ah.kode_kantor = ?
            AND ah.key_name = ?
        ";
        $stAH = $this->pdo->prepare($sqlAH);
        $stAH->execute([$dsC, $deC, $kc, $pic]);
        $handles = $stAH->fetchAll(PDO::FETCH_ASSOC);

        if (!$handles) {
            return sendResponse(200, "OK (tidak ada mapping untuk PIC pada closing_date)", [
                'params'=>['kode_kantor'=>$kc,'closing_date'=>$clo,'harian_date'=>$har,'pic'=>$pic],
                'rows'=>[]
            ]);
        }

        // index
        $ahByAcc = []; $accList = [];
        foreach ($handles as $r){ $ahByAcc[$r['no_rekening']] = $r; $accList[] = $r['no_rekening']; }

        // ===== 2) Ambil nominatif (harian_date) utk norek tsb (sekali jalan IN (...))
        [$dsH,$deH] = $this->dayRange($har);
        $rowsHar = [];
        foreach (array_chunk($accList, 800) as $chunk){
            $ph = implode(',', array_fill(0,count($chunk),'?'));
            $sqlH = "
            SELECT n.no_rekening, n.nama_nasabah, n.baki_debet, n.saldo_bank,
                    n.hari_menunggak, n.tunggakan_pokok, n.tunggakan_bunga,
                    n.kolektibilitas AS kolek_update, n.kode_produk
            FROM nominatif n
            WHERE n.created >= ? AND n.created < ?
                AND LPAD(CAST(n.kode_cabang AS CHAR),3,'0') = ?
                AND n.no_rekening IN ($ph)
            ";
            $params = array_merge([$dsH,$deH,$kc], $chunk);
            $stH = $this->pdo->prepare($sqlH); $stH->execute($params);
            while ($r = $stH->fetch(PDO::FETCH_ASSOC)) $rowsHar[$r['no_rekening']] = $r;
        }

        // ===== 3) Master bucket & bahan CKPN/PD
        [$bucketDefs, $nameMap] = $this->loadBuckets();
        $LGD   = $this->ckpn->loadGlobalLGD($har);
        $pdMap = $this->ckpn->loadPdMap($har);

        $accHar  = array_keys($rowsHar);
        $snapMap = $this->ckpn->fetchSnapCkpnMap($har, $kc, $accHar);
        $indiv   = $this->ckpn->fetchIndivCkpnMap($har, $accHar);
        $restruk = $this->ckpn->fetchRestrukSet($har, $accHar);

        // ===== 4) Agregasi angsuran dari transaksi_kredit pada window (closing, harian]
        $angsuranAgg = $this->aggregateKreditWindow($accList, $clo, $har);

        // ===== 5) Compose response per rekening
        $out = [];
        foreach ($ahByAcc as $acc => $h) {
            $harRow = $rowsHar[$acc] ?? null;
            $angs   = $angsuranAgg[$acc] ?? ['angsuran_pokok'=>0,'angsuran_bunga'=>0,'tgl_last_angsuran'=>null];

            if ($harRow) {
                $code     = $this->dpdToCode((int)$harRow['hari_menunggak'], $bucketDefs) ?? 'A';
                $dpd_name = $nameMap[$code] ?? $code;

                // pilih product code: harian > handle
                $productCode = ($harRow['kode_produk'] !== null && $harRow['kode_produk'] !== '')
                    ? (int)$harRow['kode_produk']
                    : ((isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '') ? (int)$h['kode_produk_m1'] : null);

                $pdPercent = ($productCode !== null && isset($pdMap[$productCode][$code])) ? (float)$pdMap[$productCode][$code] : null;

                $ckpnAct = $this->ckpn->computeCkpnForRow(
                    [
                        'no_rekening'=>$acc,
                        'saldo_bank'=>$harRow['saldo_bank'],
                        'kode_produk'=>$productCode,
                        'hari_menunggak'=>$harRow['hari_menunggak'],
                        'to_bucket'=>$code
                    ],
                    $har, $pdMap, $LGD, $indiv, $restruk, ($snapMap[$acc] ?? null), $bucketDefs
                );

                $row = [
                    'no_rekening'              => $acc,
                    'ao_remedial'              => $h['ao_remedial'],
                    'key_name'                 => $h['key_name'] ?? null,
                    'plan_bucket'              => $h['plan_bucket'] ?? null,
                    'pic'                      => $pic,

                    'nama_nasabah'             => $harRow['nama_nasabah'] ?? ($h['nama_m1'] ?? null),

                    // Aktual (harian)
                    'baki_debet_update'        => (int)round((float)$harRow['baki_debet']),
                    'bucket_update'            => $dpd_name,
                    'saldo_bank'               => (int)round((float)$harRow['saldo_bank']),
                    'hari_menunggak'           => (int)$harRow['hari_menunggak'],
                    'tunggakan_pokok'          => (int)round((float)$harRow['tunggakan_pokok']),
                    'tunggakan_bunga'          => (int)round((float)$harRow['tunggakan_bunga']),
                    'kolek_update'             => $harRow['kolek_update'],

                    // Snapshot handle (M-1)
                    'bucket'                   => $h['bucket_m1'] ?? null,
                    'baki_debet'               => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                    'nama_produk'              => $h['nama_produk'] ?? null,

                    // Product & PD
                    'product_code'             => $productCode,
                    'pd_percent'               => $pdPercent,

                    // CKPN
                    'ckpn_m1'                  => (int)round((float)($h['ckpn_m1'] ?? 0)),
                    'ckpn_actual'              => (int)round((float)$ckpnAct),
                    'inc_ckpn'                 => (int)round(((float)$ckpnAct) - (float)($h['ckpn_m1'] ?? 0)),

                    // Angsuran window
                    'angsuran_pokok'           => (int)round((float)$angs['angsuran_pokok']),
                    'angsuran_bunga'           => (int)round((float)$angs['angsuran_bunga']),
                    'tgl_trans_last_angsuran'  => $angs['tgl_last_angsuran']
                ];
            } else {
                // Tidak ada di harian → LUNAS
                $row = [
                    'no_rekening'              => $acc,
                    'ao_remedial'              => $h['ao_remedial'],
                    'key_name'                 => $h['key_name'] ?? null,
                    'plan_bucket'              => $h['plan_bucket'] ?? null,
                    'pic'                      => $pic,

                    'nama_nasabah'             => $h['nama_m1'] ?? null,

                    'baki_debet_update'        => 0,
                    'bucket_update'            => 'O_Lunas',
                    'saldo_bank'               => 0,
                    'hari_menunggak'           => null,
                    'tunggakan_pokok'          => null,
                    'tunggakan_bunga'          => null,
                    'kolek_update'             => 'Lunas',

                    'bucket'                   => $h['bucket_m1'] ?? null,
                    'baki_debet'               => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                    'nama_produk'              => $h['nama_produk'] ?? null,

                    'product_code'             => isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '' ? (int)$h['kode_produk_m1'] : null,
                    'pd_percent'               => null,

                    'ckpn_m1'                  => (int)round((float)($h['ckpn_m1'] ?? 0)),
                    'ckpn_actual'              => 0,
                    'inc_ckpn'                 => (int)round(0 - (float)($h['ckpn_m1'] ?? 0)),

                    'angsuran_pokok'           => (int)round((float)$angs['angsuran_pokok']),
                    'angsuran_bunga'           => (int)round((float)$angs['angsuran_bunga']),
                    'tgl_trans_last_angsuran'  => $angs['tgl_last_angsuran']
                ];
            }

            $out[] = $row;
        }

        // urutkan by baki_debet_update desc
        usort($out, fn($a,$b)=>($b['baki_debet_update'] <=> $a['baki_debet_update']));

        return sendResponse(200, "OK (detail by PIC/key_name on account_handle)", [
            'params'=>[
                'kode_kantor'=>$kc,
                'closing_date'=>$clo,
                'harian_date'=>$har,
                'pic'=>$pic
            ],
            'rows'=>$out
        ]);
    }


    /* ===================== DETAIL: 1 REKENING (untuk form kunjungan) ===================== */
    public function getDetailByNoRekening($input = null)
    {
        $b   = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        $kc  = isset($b['kode_kantor']) ? str_pad((string)$b['kode_kantor'],3,'0',STR_PAD_LEFT) : null;
        $clo = $this->asDate($b['closing_date'] ?? null);
        $har = $this->asDate($b['harian_date']  ?? null);
        $acc = trim($b['no_rekening'] ?? '');

        if (!$kc || !$clo || !$har || $acc==='') {
            return sendResponse(400, "kode_kantor, no_rekening, closing_date, harian_date wajib");
        }

        // snapshot account_handle pada closing_date untuk 1 norek
        [$dsC,$deC] = $this->dayRange($clo);
        $sql = "
        SELECT
            ah.no_rekening,
            COALESCE(NULLIF(TRIM(ah.ao_remedial),''),'vacant') AS ao_remedial,
            ah.key_name,
            ah.plan_bucket,
            ah.nilai_ckpn        AS ckpn_m1,
            ah.nama_nasabah      AS nama_m1,
            ah.nama_produk,
            ah.kode_produk       AS kode_produk_m1,
            ah.bucket            AS bucket_m1,
            ah.baki_debet        AS baki_debet_m1
        FROM account_handle ah
        WHERE ah.created >= ? AND ah.created < ?
            AND ah.kode_kantor = ?
            AND ah.no_rekening = ?
        LIMIT 1
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([$dsC,$deC,$kc,$acc]);
        $handle = $st->fetch(PDO::FETCH_ASSOC);

        if (!$handle) {
            return sendResponse(404, "Rekening tidak ditemukan pada snapshot account_handle (closing_date).", [
                'params'=>compact('kc','clo','har','acc'),
                'rows'=>[]
            ]);
        }

        // gunakan composer untuk 1 item
        $rows = $this->composeDetailRowsForHandles($kc, $clo, $har, [$handle]);
        return sendResponse(200, "OK (detail by no_rekening)", [
            'params'=>compact('kc','clo','har','acc'),
            'rows'=>$rows
        ]);
    }

    /* ===================== SEARCH DETAIL (percabang) dengan LIKE ===================== */
    public function searchDetailHandle($input = null)
    {
        $b   = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
        $kc  = isset($b['kode_kantor']) ? str_pad((string)$b['kode_kantor'],3,'0',STR_PAD_LEFT) : null;
        $clo = $this->asDate($b['closing_date'] ?? null);
        $har = $this->asDate($b['harian_date']  ?? null);
        $q   = trim($b['q'] ?? '');

        // --- baru: totung filter (opsional) ---
        $totung = null;
        if (isset($b['totung']) && $b['totung'] !== '') {
            // pastikan numeric, ambil integer (jika input string seperti "1.000" bisa disesuaikan lagi)
            $totung = (int) filter_var($b['totung'], FILTER_SANITIZE_NUMBER_INT);
        }

        if (!$kc || !$clo || !$har) return sendResponse(400, "kode_kantor, closing_date, harian_date wajib");

        [$dsC,$deC] = $this->dayRange($clo);
        $params = [$dsC,$deC,$kc];
        $whereQ = '';
        if ($q !== '') {
            $whereQ = " AND (ah.no_rekening LIKE ? OR ah.nama_nasabah LIKE ?) ";
            $like   = "%{$q}%";
            $params[] = $like; $params[] = $like;
        }

        $sql = "
        SELECT
            ah.no_rekening,
            COALESCE(NULLIF(TRIM(ah.ao_remedial),''),'vacant') AS ao_remedial,
            ah.key_name,
            ah.plan_bucket,
            ah.nilai_ckpn        AS ckpn_m1,
            ah.nama_nasabah      AS nama_m1,
            ah.nama_produk,
            ah.kode_produk       AS kode_produk_m1,
            ah.bucket            AS bucket_m1,
            ah.baki_debet        AS baki_debet_m1
        FROM account_handle ah
        WHERE ah.created >= ? AND ah.created < ?
            AND ah.kode_kantor = ?
            $whereQ
        ORDER BY ah.baki_debet DESC

        ";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $handles = $st->fetchAll(PDO::FETCH_ASSOC);

        if (!$handles) {
            return sendResponse(200, "OK (tidak ada hasil untuk query)", [
                'params'=>['kode_kantor'=>$kc,'closing_date'=>$clo,'harian_date'=>$har,'q'=>$q,'totung'=>$totung],
                'rows'=>[]
            ]);
        }

        $rows = $this->composeDetailRowsForHandles($kc, $clo, $har, $handles);

        // --- baru: apply totung filter di level array (opsional) ---
        // --- apply filter totung sesuai input ---
        if ($totung !== null) {
            // user mengisi totung → filter
            $rows = array_values(array_filter($rows, function($r) use ($totung) {
                $val = isset($r['totung']) && $r['totung'] !== null ? (int)$r['totung'] : 0;

                // skip totung = 0
                if ($val <= 0) return false;

                // hanya tampil yang <= totung_input
                return $val <= $totung;
            }));
        }
        // kalau totung kosong → TIDAK dilakukan filtering apapun


        return sendResponse(200, "OK (search detail handle)", [
            'params'=>['kode_kantor'=>$kc,'closing_date'=>$clo,'harian_date'=>$har,'q'=>$q,'totung'=>$totung],
            'rows'=>$rows
        ]);
    }


    /* ===================== COMPOSER: olah handle[] → detail rows ===================== */
    private function composeDetailRowsForHandles(string $kc, string $clo, string $har, array $handles): array
    {
        // index & list norek
        $ahByAcc = []; $accList = [];
        foreach ($handles as $r){ $ahByAcc[$r['no_rekening']] = $r; $accList[] = $r['no_rekening']; }

        // nominatif (harian) untuk list norek
        [$dsH,$deH] = $this->dayRange($har);
        $rowsHar = [];
        foreach (array_chunk($accList, 800) as $chunk){
            $ph = implode(',', array_fill(0,count($chunk),'?'));
            $sqlH = "
            SELECT 
                n.no_rekening, n.nama_nasabah, n.baki_debet, n.saldo_bank,
                n.hari_menunggak, n.hari_menunggak_pokok, n.hari_menunggak_bunga, 
                n.tunggakan_pokok, n.tunggakan_bunga,
                n.kolektibilitas AS kolek_update, n.kode_produk, n.tgl_jatuh_tempo, 
                n.norek_tabungan,
                tab.saldo_akhir AS saldo_tab_akhir
            FROM nominatif n
            LEFT JOIN tabungan tab
            ON tab.no_rekening = n.norek_tabungan
            WHERE n.created >= ? AND n.created < ?
            AND LPAD(CAST(n.kode_cabang AS CHAR),3,'0') = ?
            AND n.no_rekening IN ($ph)
            ";
            $params = array_merge([$dsH,$deH,$kc], $chunk);
            $stH = $this->pdo->prepare($sqlH); 
            $stH->execute($params);
            while ($r=$stH->fetch(PDO::FETCH_ASSOC)) $rowsHar[$r['no_rekening']]=$r;
        }

        // master bucket & bahan CKPN
        [$bucketDefs,$nameMap] = $this->loadBuckets();
        $LGD   = $this->ckpn->loadGlobalLGD($har);
        $pdMap = $this->ckpn->loadPdMap($har);

        $accHar  = array_keys($rowsHar);
        $snapMap = $this->ckpn->fetchSnapCkpnMap($har, $kc, $accHar);
        $indiv   = $this->ckpn->fetchIndivCkpnMap($har, $accHar);
        $restruk = $this->ckpn->fetchRestrukSet($har, $accHar);

        // agregasi angsuran window (closing, harian]
        $angsuranAgg = $this->aggregateKreditWindow($accList, $clo, $har);

        // compose
        $out=[];
        foreach ($ahByAcc as $acc=>$h){
            $harRow = $rowsHar[$acc] ?? null;
            $angs   = $angsuranAgg[$acc] ?? ['angsuran_pokok'=>0,'angsuran_bunga'=>0,'tgl_last_angsuran'=>null];

            if ($harRow){
                $code     = $this->dpdToCode((int)$harRow['hari_menunggak'], $bucketDefs) ?? 'A';
                $dpd_name = $nameMap[$code] ?? $code;

                $productCode = ($harRow['kode_produk'] !== null && $harRow['kode_produk'] !== '')
                    ? (int)$harRow['kode_produk']
                    : ((isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '') ? (int)$h['kode_produk_m1'] : null);

                $pdPercent = ($productCode !== null && isset($pdMap[$productCode][$code])) ? (float)$pdMap[$productCode][$code] : null;

                $ckpnAct = $this->ckpn->computeCkpnForRow(
                    [
                        'no_rekening'=>$acc,
                        'saldo_bank'=>$harRow['saldo_bank'],
                        'kode_produk'=>$productCode,
                        'hari_menunggak'=>$harRow['hari_menunggak'],
                        'to_bucket'=>$code
                    ],
                    $har, $pdMap, $LGD, $indiv, $restruk, ($snapMap[$acc] ?? null), $bucketDefs
                );

                $row = [
                    'no_rekening'            => $acc,
                    // 'norek_tabungan'         => $harRow['norek_tabungan'],
                    'ao_remedial'            => $h['ao_remedial'],
                    'key_name'               => $h['key_name'] ?? null,
                    'plan_bucket'            => $h['plan_bucket'] ?? null,

                    'nama_nasabah'           => $harRow['nama_nasabah'] ?? ($h['nama_m1'] ?? null),

                    // aktual
                    'baki_debet_update'      => (int)round((float)$harRow['baki_debet']),
                    'bucket_update'          => $dpd_name,
                    'saldo_bank'             => (int)round((float)$harRow['saldo_bank']),
                    'hari_menunggak'         => (int)$harRow['hari_menunggak'],
                    'hari_menunggak_pokok'   => (int)$harRow['hari_menunggak_pokok'],
                    'hari_menunggak_bunga'   => (int)$harRow['hari_menunggak_bunga'],
                    'tgl_jatuh_tempo'        => $harRow['tgl_jatuh_tempo'],
                    'tgl_jt'                 => date('d', strtotime($harRow['tgl_jatuh_tempo'])),
                    'tunggakan_pokok'        => (int)round((float)$harRow['tunggakan_pokok']),
                    'tunggakan_bunga'        => (int)round((float)$harRow['tunggakan_bunga']),
                    'totung'                 => (int)round((float)$harRow['tunggakan_pokok']) + (int)round((float)$harRow['tunggakan_bunga']),
                    'kolek_update'           => $harRow['kolek_update'],

                    // saldo tabungan hasil join
                    'saldo_tabungan'         => (int)round((float)($harRow['saldo_tab_akhir'] ?? 0)),

                    // snapshot handle
                    'bucket'                 => $h['bucket_m1'] ?? null,
                    'baki_debet'             => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                    'nama_produk'            => $h['nama_produk'] ?? null,

                    'product_code'           => $productCode,
                    'pd_percent'             => $pdPercent,

                    'ckpn_m1'                => (int)round((float)($h['ckpn_m1'] ?? 0)),
                    'ckpn_actual'            => (int)round((float)$ckpnAct),
                    'inc_ckpn'               => (int)round(((float)$ckpnAct) - (float)($h['ckpn_m1'] ?? 0)),

                    // angsuran window
                    'angsuran_pokok'         => (int)round((float)$angs['angsuran_pokok']),
                    'angsuran_bunga'         => (int)round((float)$angs['angsuran_bunga']),
                    'tgl_trans_last_angsuran'=> $angs['tgl_last_angsuran']
                ];
            } else {
                // LUNAS
                $row = [
                    'no_rekening'            => $acc,
                    'ao_remedial'            => $h['ao_remedial'],
                    'key_name'               => $h['key_name'] ?? null,
                    'plan_bucket'            => $h['plan_bucket'] ?? null,
                    'nama_nasabah'           => $h['nama_m1'] ?? null,

                    'baki_debet_update'      => 0,
                    'bucket_update'          => 'O_Lunas',
                    'saldo_bank'             => 0,
                    'hari_menunggak'         => null,
                    'hari_menunggak_pokok'   => null,
                    'hari_menunggak_bunga'   => null,
                    'tunggakan_pokok'        => null,
                    'tunggakan_bunga'        => null,
                    'tgl_jatuh_tempo'        => null,
                    'tgl_jt'                 => null,
                    'totung'                 => 0,
                    'kolek_update'           => 'Lunas',

                    // saldo tabungan default 0 bila tidak ada row harian
                    'saldo_tabungan'         => 0,

                    'bucket'                 => $h['bucket_m1'] ?? null,
                    'baki_debet'             => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                    'nama_produk'            => $h['nama_produk'] ?? null,

                    'product_code'           => isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '' ? (int)$h['kode_produk_m1'] : null,
                    'pd_percent'             => null,

                    'ckpn_m1'                => (int)round((float)($h['ckpn_m1'] ?? 0)),
                    'ckpn_actual'            => 0,
                    'inc_ckpn'               => (int)round(0 - (float)($h['ckpn_m1'] ?? 0)),

                    'angsuran_pokok'         => (int)round((float)$angs['angsuran_pokok']),
                    'angsuran_bunga'         => (int)round((float)$angs['angsuran_bunga']),
                    'tgl_trans_last_angsuran'=> $angs['tgl_last_angsuran']
                ];
            }

            $out[] = $row;
        }

        // sort
        usort($out, fn($a,$b)=>($b['baki_debet_update'] <=> $a['baki_debet_update']));
        return $out;
    }


/* ===================== SEARCH DETAIL GLOBAL (tanpa KC, TANPA CKPN) ===================== */
public function searchDetailHandleGlobal($input = null)
{
    $b   = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);
    $clo = $this->asDate($b['closing_date'] ?? null);
    $har = $this->asDate($b['harian_date']  ?? null);
    $q   = trim($b['q'] ?? '');

    // totung opsional (sanitasi angka)
    $totung = null;
    if (isset($b['totung']) && $b['totung'] !== '') {
        $totung = (int) filter_var($b['totung'], FILTER_SANITIZE_NUMBER_INT);
    }

    if (!$clo || !$har) return sendResponse(400, "closing_date & harian_date wajib");

    [$dsC,$deC] = $this->dayRange($clo);
    $params = [$dsC,$deC];
    $whereQ = '';
    if ($q !== '') {
        $whereQ = " AND (ah.no_rekening LIKE ? OR ah.nama_nasabah LIKE ?) ";
        $like   = "%{$q}%";
        $params[] = $like; $params[] = $like;
    }

    $sql = "
    SELECT
        ah.no_rekening,
        COALESCE(NULLIF(TRIM(ah.ao_remedial),''),'vacant') AS ao_remedial,
        ah.key_name,
        ah.plan_bucket,
        ah.nilai_ckpn        AS ckpn_m1,
        ah.nama_nasabah      AS nama_m1,
        ah.nama_produk,
        ah.kode_produk       AS kode_produk_m1,
        ah.bucket            AS bucket_m1,
        ah.baki_debet        AS baki_debet_m1
    FROM account_handle ah
    WHERE ah.created >= ? AND ah.created < ?
        $whereQ
    ORDER BY ah.baki_debet DESC
    ";
    $st = $this->pdo->prepare($sql);
    $st->execute($params);
    $handles = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$handles) {
        return sendResponse(200, "OK (tidak ada hasil untuk query)", [
            'params'=>['closing_date'=>$clo,'harian_date'=>$har,'q'=>$q,'totung'=>$totung],
            'rows'=>[]
        ]);
    }

    // composer versi tanpa CKPN dan tanpa filter kode_kantor
    $rows = $this->composeDetailRowsForHandlesNoCkpn($clo, $har, $handles);

    // apply totung rules:
    // - kalau totung diisi: hanya yang totung > 0 dan <= totung_input
    // - kalau totung tidak diisi: tampilkan semua (termasuk totung = 0)
    if ($totung !== null) {
        $rows = array_values(array_filter($rows, function($r) use ($totung) {
            $val = isset($r['totung']) && $r['totung'] !== null ? (int)$r['totung'] : 0;
            if ($val === 0) return false;          // skip totung = 0
            return $val <= $totung;               // hanya yg <= totung_input
        }));
    }

    return sendResponse(200, "OK (search detail handle global no-ckpn)", [
        'params'=>['closing_date'=>$clo,'harian_date'=>$har,'q'=>$q,'totung'=>$totung],
        'rows'=>$rows
    ]);
}

/* ===================== COMPOSER: olah handle[] → detail rows (TANPA CKPN) ===================== */
private function composeDetailRowsForHandlesNoCkpn(string $clo, string $har, array $handles): array
{
    // index & list norek
    $ahByAcc = []; $accList = [];
    foreach ($handles as $r){ $ahByAcc[$r['no_rekening']] = $r; $accList[] = $r['no_rekening']; }

    // nominatif (harian) untuk list norek — TANPA filter kode_cabang
    [$dsH,$deH] = $this->dayRange($har);
    $rowsHar = [];
    foreach (array_chunk($accList, 800) as $chunk){
        $ph = implode(',', array_fill(0,count($chunk),'?'));
        $sqlH = "
        SELECT 
            n.no_rekening, n.nama_nasabah, n.baki_debet, n.saldo_bank,
            n.hari_menunggak, n.hari_menunggak_pokok, n.hari_menunggak_bunga, 
            n.tunggakan_pokok, n.tunggakan_bunga,
            n.kolektibilitas AS kolek_update, n.kode_produk, n.tgl_jatuh_tempo, 
            n.norek_tabungan,
            tab.saldo_akhir AS saldo_tab_akhir
        FROM nominatif n
        LEFT JOIN tabungan tab
          ON tab.no_rekening = n.norek_tabungan
        WHERE n.created >= ? AND n.created < ?
          AND n.no_rekening IN ($ph)
        ";
        $params = array_merge([$dsH,$deH], $chunk);
        $stH = $this->pdo->prepare($sqlH); 
        $stH->execute($params);
        while ($r=$stH->fetch(PDO::FETCH_ASSOC)) $rowsHar[$r['no_rekening']]=$r;
    }

    // (SKIP CKPN related loads: LGD, pdMap, snapMap, indiv, restruk)

    // agregasi angsuran window (closing, harian] — pakai fungsi yang sudah ada
    $angsuranAgg = $this->aggregateKreditWindow($accList, $clo, $har);

    // compose
    $out=[];
    foreach ($ahByAcc as $acc=>$h){
        $harRow = $rowsHar[$acc] ?? null;
        $angs   = $angsuranAgg[$acc] ?? ['angsuran_pokok'=>0,'angsuran_bunga'=>0,'tgl_last_angsuran'=>null];

        if ($harRow){
            // tanpa ckpn kita masih bisa peta bucket via dpdToCode jika tersedia
            $code     = $this->dpdToCode((int)$harRow['hari_menunggak'], $this->loadBuckets()[0]) ?? 'A';
            $nameMap  = $this->loadBuckets()[1] ?? [];
            $dpd_name = $nameMap[$code] ?? $code;

            $productCode = ($harRow['kode_produk'] !== null && $harRow['kode_produk'] !== '')
                ? (int)$harRow['kode_produk']
                : ((isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '') ? (int)$h['kode_produk_m1'] : null);

            $row = [
                'no_rekening'            => $acc,
                'ao_remedial'            => $h['ao_remedial'],
                'key_name'               => $h['key_name'] ?? null,
                'plan_bucket'            => $h['plan_bucket'] ?? null,

                'nama_nasabah'           => $harRow['nama_nasabah'] ?? ($h['nama_m1'] ?? null),

                // aktual
                'baki_debet_update'      => (int)round((float)$harRow['baki_debet']),
                'bucket_update'          => $dpd_name,
                'saldo_bank'             => (int)round((float)$harRow['saldo_bank']),
                'hari_menunggak'         => is_numeric($harRow['hari_menunggak']) ? (int)$harRow['hari_menunggak'] : null,
                'hari_menunggak_pokok'   => (int)$harRow['hari_menunggak_pokok'],
                'hari_menunggak_bunga'   => (int)$harRow['hari_menunggak_bunga'],
                'tgl_jatuh_tempo'        => $harRow['tgl_jatuh_tempo'],
                'tgl_jt'                 => $harRow['tgl_jt'] ?? ($harRow['tgl_jatuh_tempo'] ? date('d', strtotime($harRow['tgl_jatuh_tempo'])) : null),
                'tunggakan_pokok'        => (int)round((float)$harRow['tunggakan_pokok']),
                'tunggakan_bunga'        => (int)round((float)$harRow['tunggakan_bunga']),
                'totung'                 => (int)round((float)$harRow['tunggakan_pokok']) + (int)round((float)$harRow['tunggakan_bunga']),
                'kolek_update'           => $harRow['kolek_update'],

                // saldo tabungan hasil join
                'saldo_tabungan'         => (int)round((float)($harRow['saldo_tab_akhir'] ?? 0)),

                // snapshot handle
                'bucket'                 => $h['bucket_m1'] ?? null,
                'baki_debet'             => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                'nama_produk'            => $h['nama_produk'] ?? null,

                'product_code'           => $productCode,

                // CKPN fields disabled/neutral supaya front-end aman
                'ckpn_m1'                => (int)round((float)($h['ckpn_m1'] ?? 0)),
                'ckpn_actual'            => 0,
                'inc_ckpn'               => (int)round(0 - (float)($h['ckpn_m1'] ?? 0)),
                'pd_percent'             => null,

                // angsuran window
                'angsuran_pokok'         => (int)round((float)$angs['angsuran_pokok']),
                'angsuran_bunga'         => (int)round((float)$angs['angsuran_bunga']),
                'tgl_trans_last_angsuran'=> $angs['tgl_last_angsuran']
            ];
        } else {
            // LUNAS
            $row = [
                'no_rekening'            => $acc,
                'ao_remedial'            => $h['ao_remedial'],
                'key_name'               => $h['key_name'] ?? null,
                'plan_bucket'            => $h['plan_bucket'] ?? null,
                'nama_nasabah'           => $h['nama_m1'] ?? null,

                'baki_debet_update'      => 0,
                'bucket_update'          => 'O_Lunas',
                'saldo_bank'             => 0,
                'hari_menunggak'         => null,
                'hari_menunggak_pokok'   => null,
                'hari_menunggak_bunga'   => null,
                'tunggakan_pokok'        => null,
                'tunggakan_bunga'        => null,
                'tgl_jatuh_tempo'        => null,
                'tgl_jt'                 => null,
                'totung'                 => 0,
                'kolek_update'           => 'Lunas',

                'saldo_tabungan'         => 0,

                'bucket'                 => $h['bucket_m1'] ?? null,
                'baki_debet'             => (int)round((float)($h['baki_debet_m1'] ?? 0)),
                'nama_produk'            => $h['nama_produk'] ?? null,

                'product_code'           => isset($h['kode_produk_m1']) && $h['kode_produk_m1'] !== '' ? (int)$h['kode_produk_m1'] : null,

                // CKPN disabled
                'ckpn_m1'                => (int)round((float)($h['ckpn_m1'] ?? 0)),
                'ckpn_actual'            => 0,
                'inc_ckpn'               => (int)round(0 - (float)($h['ckpn_m1'] ?? 0)),
                'pd_percent'             => null,

                'angsuran_pokok'         => (int)round((float)$angs['angsuran_pokok']),
                'angsuran_bunga'         => (int)round((float)$angs['angsuran_bunga']),
                'tgl_trans_last_angsuran'=> $angs['tgl_last_angsuran']
            ];
        }

        $out[] = $row;
    }

    // sort
    usort($out, fn($a,$b)=>($b['baki_debet_update'] <=> $a['baki_debet_update']));
    return $out;
}




    /* ===================== CREATE DATA KUNJUNGAN (petugas dari token) ===================== */
    public function createDataKunjungan($input = null, array $user = [])
    {
        // --- 1) Auth (petugas dari token, fallback by id)
        $petugas = $user['full_name'] ?? null;
        if (!$petugas && !empty($user['id'])) {
            $q = $this->pdo->prepare("SELECT full_name FROM users WHERE id=? LIMIT 1");
            $q->execute([$user['id']]);
            $petugas = $q->fetchColumn() ?: null;
        }
        if (!$petugas) return sendResponse(400, "petugas login tidak ditemukan", null);
        if (trim($petugas) === '') return sendResponse(401, "Unauthorized: full_name pada token kosong");

        // --- 2) Ambil body (form-data -> json -> array)
        $body = [];
        if (!empty($_POST)) {
            $body = $_POST;
        } elseif (is_array($input)) {
            $body = $input;
        } else {
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
        }
        $g = static function($k, $def=null) use ($body){ return (isset($body[$k]) && $body[$k] !== '') ? $body[$k] : $def; };

        // --- 3) Wajib: no_rekening
        $no_rekening = trim((string)$g('no_rekening',''));
        if ($no_rekening === '') return sendResponse(400, 'no_rekening wajib diisi.');

        // derive kode_kantor (3 digit pertama dari no_rekening)
        $kode_kantor = substr($no_rekening, 0, 3);

        // --- 4) Ambil nama_nasabah langsung dari body
        $nama_nasabah = trim((string)$g('nama_nasabah',''));
        if ($nama_nasabah === '') return sendResponse(400, 'nama_nasabah wajib diisi.');

        // --- 5) Field lain (optional)
        $baki_debet       = (float)($g('baki_debet', 0));
        $tunggakan_pokok  = $g('tunggakan_pokok', null);
        $tunggakan_bunga  = $g('tunggakan_bunga', null);
        $hari_menunggak   = $g('hari_menunggak', null);
        $kolektabilitas   = $g('kolektabilitas', null);

        $kode_tindakan    = $g('kode_tindakan', null);
        $jenis_tindakan   = $g('jenis_tindakan', null);
        $lokasi_tindakan  = $g('lokasi_tindakan', null);
        $orang_ditemui    = $g('orang_ditemui', null);
        $status_kunjungan = $g('status_kunjungan', null);

        $nominal_janji_bayar = $g('nominal_janji_bayar', null);
        $tanggal_janji_bayar = $g('tanggal_janji_bayar', null);

        $keterangan  = $g('keterangan', null);
        $alamat_gps  = $g('alamat_gps', null);
        $koordinat   = $g('koordinat', null);

        $now            = date('Y-m-d H:i:s');
        $tgl_kunjungan  = $g('tgl_kunjungan', $now);

        // --- 6) Upload foto (opsional)
        // --- 6) Upload foto (opsional)
        $nama_foto = null;

        // terima beberapa kemungkinan nama field dari form-data
        $file = null;
        if (!empty($_FILES)) {
            $file = $_FILES['foto'] ?? $_FILES['nama_foto'] ?? $_FILES['file'] ?? null;
        }

        if ($file && is_array($file) && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $save = saveCompressedPhotoKunjungan($file, $no_rekening);
            if (!$save['success']) {
                return sendResponse(400, $save['message']);
            }
            $nama_foto = $save['file_name'];   // simpan ke DB
        }


        // --- 7) Insert data kunjungan
        $sql = "
            INSERT INTO kunjungan
            (petugas, kode_kantor, no_rekening, nama_nasabah, baki_debet,
            tunggakan_pokok, tunggakan_bunga, kolektabilitas, hari_menunggak,
            kode_tindakan, jenis_tindakan, lokasi_tindakan, orang_ditemui,
            nominal_janji_bayar, tanggal_janji_bayar, status_kunjungan, keterangan,
            nama_foto, alamat_gps, koordinat, tgl_kunjungan, created)
            VALUES
            (:petugas, :kode_kantor, :no_rekening, :nama_nasabah, :baki_debet,
            :tunggakan_pokok, :tunggakan_bunga, :kolektabilitas, :hari_menunggak,
            :kode_tindakan, :jenis_tindakan, :lokasi_tindakan, :orang_ditemui,
            :nominal_janji_bayar, :tanggal_janji_bayar, :status_kunjungan, :keterangan,
            :nama_foto, :alamat_gps, :koordinat, :tgl_kunjungan, :created)
        ";

        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':petugas'              => $petugas,
            ':kode_kantor'          => $kode_kantor,
            ':no_rekening'          => $no_rekening,
            ':nama_nasabah'         => $nama_nasabah,
            ':baki_debet'           => $baki_debet,
            ':tunggakan_pokok'      => is_null($tunggakan_pokok) ? null : (float)$tunggakan_pokok,
            ':tunggakan_bunga'      => is_null($tunggakan_bunga) ? null : (float)$tunggakan_bunga,
            ':kolektabilitas'       => $kolektabilitas,
            ':hari_menunggak'       => is_null($hari_menunggak) ? null : (int)$hari_menunggak,
            ':kode_tindakan'        => $kode_tindakan,
            ':jenis_tindakan'       => $jenis_tindakan,
            ':lokasi_tindakan'      => $lokasi_tindakan,
            ':orang_ditemui'        => $orang_ditemui,
            ':nominal_janji_bayar'  => is_null($nominal_janji_bayar) ? null : (float)$nominal_janji_bayar,
            ':tanggal_janji_bayar'  => $tanggal_janji_bayar,
            ':status_kunjungan'     => $status_kunjungan,
            ':keterangan'           => $keterangan,
            ':nama_foto'            => $nama_foto,
            ':alamat_gps'           => $alamat_gps,
            ':koordinat'            => $koordinat,
            ':tgl_kunjungan'        => $tgl_kunjungan,
            ':created'              => $now,
        ]);

        if (!$ok) return sendResponse(500, 'Gagal menyimpan kunjungan.');

        $id = (int)$this->pdo->lastInsertId();

        return sendResponse(201, 'Kunjungan tersimpan.', [
            'id'           => $id,
            'petugas'      => $petugas,
            'kode_kantor'  => $kode_kantor,
            'no_rekening'  => $no_rekening,
            'nama_nasabah' => $nama_nasabah,
            'nama_foto'    => $nama_foto,
            'file_url'     => $nama_foto ? ("img/kunjungan/" . $nama_foto) : null,
        ]);
    }


    /* ---------- Helpers kecil ---------- */
    // Normalisasi angka: terima "1.234.567,89" atau "1,234,567.89" jadi float
    private function num($v): float {
        if ($v === null || $v === '') return 0.0;
        $s = trim((string)$v);
        // buang spasi
        $s = str_replace(' ', '', $s);
        // jika format ID: ada koma sebagai desimal
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // format EN: 1,234,567.89
            $s = str_replace(',', '', $s);
        }
        return (float)$s;
    }

    // Parse 'YYYY-MM-DD' -> string atau null
    // private function asDate($s): ?string {
    //     if (!$s) return null;
    //     $t = strtotime($s);
    //     return $t ? date('Y-m-d', $t) : null;
    // }

    // Parse datetime bebas -> 'Y-m-d H:i:s' atau null
    private function asDateTime($s): ?string {
        if (!$s) return null;
        $t = strtotime($s);
        return $t ? date('Y-m-d H:i:s', $t) : null;
    }






        /* =====================================================
     * HISTORY KUNJUNGAN
     * ===================================================== */
public function getHistoryKunjunganByNoRekening($input = null)
{
    // 1. Ambil Input
    $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);

    // 2. Validasi No Rekening
    $no_rekening = trim((string)($b['no_rekening'] ?? ''));
    if ($no_rekening === '') {
        return sendResponse(400, "no_rekening wajib diisi");
    }

    // 3. DEFINE KODE USER (Ini yang sebelumnya error/hilang)
    // Kita ambil dari input 'kode_kantor'. Jika tidak ada, anggap '000' (Pusat) atau null.
    $kode_user = isset($b['kode_kantor']) ? (string)$b['kode_kantor'] : '000';

    // 4. Siapkan Query
    $sql = "SELECT * FROM kunjungan WHERE no_rekening = ?";
    $p   = [$no_rekening];

    // 5. Filter Cabang
    // Jika user BUKAN orang pusat ('000'), filter agar cuma bisa lihat kunjungan cabang dia sendiri
    if ($kode_user !== '000') {
        $sql .= " AND kode_kantor = ?";
        $p[]  = $kode_user;
    }

    $sql .= " ORDER BY created DESC";

    try {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return sendResponse(200, "OK (history kunjungan)", ['rows' => $rows]);
    } catch (PDOException $e) {
        return sendResponse(500, "Database Error: " . $e->getMessage());
    }
}

    public function getKunjunganByUserLogin($input=null, array $user=[])
    {
        $petugas=$user['full_name']??null;
        if(!$petugas) return sendResponse(401,"Unauthorized");
        [$kode_user]=$this->whoAmI($user);
        $b=is_array($input)?$input:(json_decode(file_get_contents('php://input'),true)?:[]);
        $from=$this->asDate($b['date_from']??date('Y-m-01')); $to=$this->asDate($b['date_to']??date('Y-m-d'));
        $sql="SELECT * FROM kunjungan WHERE petugas=? AND DATE(created) BETWEEN ? AND ?"; $p=[$petugas,$from,$to];
        if($kode_user!=='000'){ $sql.=" AND kode_kantor=?"; $p[]=$kode_user; }
        $sql.=" ORDER BY created DESC"; $st=$this->pdo->prepare($sql); $st->execute($p);
        return sendResponse(200,"OK (kunjungan by user)",['petugas'=>$petugas,'rows'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function getHistoryKunjunganByAtasan($input=null,array $user=[])
    {
        [$kode_user,$job]=$this->whoAmI($user);
        $b=is_array($input)?$input:(json_decode(file_get_contents('php://input'),true)?:[]);
        $ao=$b['petugas']??''; $from=$this->asDate($b['date_from']??date('Y-m-01')); $to=$this->asDate($b['date_to']??date('Y-m-d'));
        $sql="SELECT * FROM kunjungan WHERE DATE(created) BETWEEN ? AND ?"; $p=[$from,$to];
        if($kode_user!=='000'){ $sql.=" AND kode_kantor=?"; $p[]=$kode_user; }
        if($ao!==''){ $sql.=" AND petugas=?"; $p[]=$ao; }
        $sql.=" ORDER BY created DESC"; $st=$this->pdo->prepare($sql); $st->execute($p);
        return sendResponse(200,"OK (history by atasan)",['rows'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function getReminderJanjiBayar($input=null,array $user=[])
    {
        [$kode_user]=$this->whoAmI($user);
        $today=date('Y-m-d');
        $sql="SELECT * FROM kunjungan WHERE tanggal_janji_bayar IS NOT NULL AND DATE(tanggal_janji_bayar)>=?"; $p=[$today];
        if($kode_user!=='000'){ $sql.=" AND kode_kantor=?"; $p[]=$kode_user; }
        $st=$this->pdo->prepare($sql); $st->execute($p);
        return sendResponse(200,"OK (reminder janji bayar)",['rows'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function getRekapKunjunganByKode($input=null,array $user=[])
    {
        [$kode_user]=$this->whoAmI($user);
        $sql="SELECT kode_kantor,kode_tindakan,COUNT(*) AS total FROM kunjungan"; $cond=[];
        if($kode_user!=='000'){ $cond[]="kode_kantor='$kode_user'"; }
        $sql.=count($cond)?" WHERE ".implode(" AND ",$cond):"";
        $sql.=" GROUP BY kode_kantor,kode_tindakan ORDER BY kode_kantor";
        $rows=$this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return sendResponse(200,"OK (rekap kunjungan)",['rows'=>$rows]);
    }

    public function getFrekuensiKunjunganDebitur($input=null,array $user=[])
    {
        [$kode_user]=$this->whoAmI($user);
        $sql="SELECT a.kode_kantor,a.no_rekening,a.nama_nasabah,COUNT(k.id) AS freq
              FROM account_handle a LEFT JOIN kunjungan k ON a.no_rekening=k.no_rekening";
        $cond=[]; if($kode_user!=='000'){ $cond[]="a.kode_kantor='$kode_user'"; }
        $sql.=count($cond)?" WHERE ".implode(" AND ",$cond):"";
        $sql.=" GROUP BY a.kode_kantor,a.no_rekening,a.nama_nasabah ORDER BY freq DESC";
        $rows=$this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return sendResponse(200,"OK (frekuensi kunjungan)",['rows'=>$rows]);
    }

    public function verifyKunjungan($input=null,array $user=[])
    {
        [$kode_user,$job]=$this->whoAmI($user);
        if(!preg_match('/kepala cabang|kabid pemasaran|kasubid remedial/i',$job))
            return sendResponse(403,"Anda tidak berhak verifikasi.");
        $b=is_array($input)?$input:(json_decode(file_get_contents('php://input'),true)?:[]);
        $id=$b['id']??null; if(!$id) return sendResponse(400,"id wajib.");
        $full_name=$user['full_name']??'Atasan';
        $st=$this->pdo->prepare("UPDATE kunjungan SET verifikasi=1,verified_by=?,verified_at=NOW() WHERE id=? AND kode_kantor=?");
        $ok=$st->execute([$full_name,$id,$kode_user]);
        return $ok?sendResponse(200,"Verifikasi sukses oleh $full_name"):sendResponse(500,"Gagal verifikasi");
    }

    /* =====================================================
     * Helper
     * ===================================================== */
    private function whoAmI(array $user):array{
        $kode=$user['kode_kantor']??'000';
        $job=$user['job_position']??'';
        return [str_pad((string)$kode,3,'0',STR_PAD_LEFT),$job];
    }

public function getMonitoringKunjunganAO($input = null)
{
    // ... (Bagian ambil input sama) ...
    $b = is_array($input) ? $input : (json_decode(file_get_contents('php://input'), true) ?: []);

    $closing_date = !empty($b['account_handle']) ? $b['account_handle'] : date('Y-m-d', strtotime('last day of previous month'));
    $harian_date  = !empty($b['harian_date']) ? $b['harian_date'] : date('Y-m-d');
    $start_date   = date('Y-m-01', strtotime($harian_date));
    $end_date     = $harian_date;

    // Ambil input (bisa 000, 001, atau SEMARANG/SOLO dll)
    $pilihan_kantor = isset($b['kode_kantor']) ? (string)$b['kode_kantor'] : '000';

    // === LOGIKA FILTER BARU (Support Korwil) ===
    $filter_kantor = "";
    $params = [
        ':closing_date' => $closing_date,
        ':start_date'   => $start_date,
        ':end_date'     => $end_date
    ];

    // Cek apakah inputnya nama KORWIL?
    $korwil_map = [
        'SEMARANG'   => ['001','007'],
        'SOLO'       => ['008','014'],
        'BANYUMAS'   => ['015','021'],
        'PEKALONGAN' => ['022','028']
    ];

    if (isset($korwil_map[$pilihan_kantor])) {
        // Jika user pilih KORWIL (Range)
        $range = $korwil_map[$pilihan_kantor];
        $filter_kantor = " AND ah.kode_kantor BETWEEN :k_start AND :k_end ";
        $params[':k_start'] = $range[0];
        $params[':k_end']   = $range[1];
    } elseif ($pilihan_kantor !== '000') {
        // Jika user pilih CABANG SPESIFIK (001, 002, dll)
        $filter_kantor = " AND ah.kode_kantor = :kode_kantor ";
        $params[':kode_kantor'] = $pilihan_kantor;
    }
    // Jika '000', $filter_kantor tetap kosong (Konsolidasi)

    // ... (Sisa query sama persis ke bawah) ...
    $sql = "
        SELECT 
            ah.kode_kantor,
            ah.ao_remedial AS nama_ao,
            COUNT(ah.no_rekening) AS total_kelolaan,
            COUNT(DISTINCT k.no_rekening) AS acc_visited,
            COUNT(k.id) AS frekuensi_kunjungan,
            CASE 
                WHEN COUNT(ah.no_rekening) > 0 
                THEN ROUND((COUNT(DISTINCT k.no_rekening) * 100.0 / COUNT(ah.no_rekening)), 2)
                ELSE 0 
            END AS coverage_ratio
        FROM account_handle ah
        LEFT JOIN kunjungan k ON k.no_rekening = ah.no_rekening
            AND k.tgl_kunjungan >= :start_date 
            AND k.tgl_kunjungan <= :end_date
        WHERE ah.created = :closing_date
          $filter_kantor
          AND ah.ao_remedial IS NOT NULL 
          AND ah.ao_remedial != ''
        GROUP BY ah.kode_kantor, ah.ao_remedial
        ORDER BY ah.kode_kantor ASC, coverage_ratio DESC
    ";

    // ... (Eksekusi & Return sama) ...
    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Hitung Summary (Sama)
        $total_kelolaan = 0; $total_visited = 0; $total_freq = 0;
        foreach ($rows as $r) {
            $total_kelolaan += (int)$r['total_kelolaan'];
            $total_visited  += (int)$r['acc_visited'];
            $total_freq     += (int)$r['frekuensi_kunjungan'];
        }
        $grand_coverage = $total_kelolaan > 0 ? round(($total_visited / $total_kelolaan) * 100, 2) : 0;
        $summary = [
            'total_ao' => count($rows), 'total_kelolaan' => $total_kelolaan,
            'total_acc_visited' => $total_visited, 'total_frekuensi' => $total_freq,
            'avg_coverage' => $grand_coverage
        ];

        return sendResponse(200, "Berhasil", ['periode'=>"$start_date s/d $end_date", 'summary'=>$summary, 'rows'=>$rows]);
    } catch (PDOException $e) { return sendResponse(500, "DB Error: ".$e->getMessage()); }
}
   





}
