<?php

require_once __DIR__ . '/../helpers/response.php';
// require_once __DIR__ . '/../helpers/MobHelper.php';

class LaporanKeuanganController 
{
    private PDO $pdo;

    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }



    /**
     * =================================================================
     * 2. CORE ENGINE PIVOT REPORT (Matrix 000 - 028 + Konsolidasi)
     * =================================================================
     */
    private function generatePivotReport(array $input, array $kodePrefixes): array 
    {
        // 1. Ambil Tanggal H-1 atau dari request
        $tanggal = $input['harian_date'] ?? date('Y-m-d', strtotime('-1 day'));
        
        // 2. Susun Kondisi LIKE ('1%', '2%', dll)
        $likeConditions = [];
        foreach ($kodePrefixes as $prefix) {
            $likeConditions[] = "TRIM(CAST(kode_perk AS CHAR)) LIKE :prefix_{$prefix}";
        }
        $sqlLike = implode(' OR ', $likeConditions);

        // 3. Query Database: Tarik semua cabang 000 - 028 secara vertical
        // Kita grouping berdasarkan KODE PERK dan KODE KANTOR
        $sql = "
            SELECT 
                TRIM(CAST(kode_perk AS CHAR)) AS kode_perk,
                LPAD(CAST(kode_kantor AS CHAR), 3, '0') AS kode_kantor,
                SUM(saldo_akhir) AS total_saldo
            FROM acc_history
            WHERE tanggal = :tanggal
              AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') BETWEEN '000' AND '028'
              AND ({$sqlLike})
            GROUP BY 
                TRIM(CAST(kode_perk AS CHAR)),
                LPAD(CAST(kode_kantor AS CHAR), 3, '0')
            HAVING SUM(saldo_akhir) <> 0
            ORDER BY TRIM(CAST(kode_perk AS CHAR)) ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':tanggal', $tanggal);
            foreach ($kodePrefixes as $prefix) {
                $stmt->bindValue(":prefix_{$prefix}", "{$prefix}%");
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Proses PIVOTING Menggunakan PHP Array (Super Cepat!)
            $coaDict = $this->getCoaDictionary();
            $pivotData = [];

            foreach ($results as $row) {
                $perk   = $row['kode_perk'];
                $kantor = $row['kode_kantor'];
                $saldo  = (float) $row['total_saldo'];

                // Jika kode_perk ini belum ada di array pivot, kita inisialisasi dulu
                if (!isset($pivotData[$perk])) {
                    $pivotData[$perk] = [
                        'kode_perk'      => $perk,
                        'nama_perkiraan' => $coaDict[$perk] ?? 'Nama Perkiraan Tidak Ditemukan',
                        'konsolidasi'    => 0 // Untuk Total Keseluruhan
                    ];
                    
                    // Bikin kolom untuk 000 sampai 028 (Default 0)
                    for ($i = 0; $i <= 28; $i++) {
                        $kodeCabang = str_pad($i, 3, '0', STR_PAD_LEFT);
                        $pivotData[$perk]['cabang_' . $kodeCabang] = 0;
                    }
                }

                // Masukkan saldo ke kolom cabang yang tepat
                // Contoh: cabang_000, cabang_015, dll
                if (isset($pivotData[$perk]['cabang_' . $kantor])) {
                    $pivotData[$perk]['cabang_' . $kantor] += $saldo;
                }

                // Tambahkan langsung ke total konsolidasi
                $pivotData[$perk]['konsolidasi'] += $saldo;
            }

            // Kembalikan array associative menjadi array numerik biasa (index 0, 1, 2...)
            return array_values($pivotData);

        } catch (Exception $e) {
            error_log("Error generatePivotReport: " . $e->getMessage());
            return []; 
        }
    }

    /**
     * =================================================================
     * 3. ENDPOINT: API NERACA PIVOT (Kode 1, 2, 3)
     * =================================================================
     */
    public function apiGetNeraca(array $input) 
    {
        try {
            $data = $this->generatePivotReport($input, ['1', '2', '3']);
            sendResponse(200, "Berhasil memuat Laporan Neraca (Pivot)", $data);
        } catch (Exception $e) {
            sendResponse(500, "Gagal memuat Laporan Neraca: " . $e->getMessage(), null);
        }
    }

    /**
     * =================================================================
     * 4. ENDPOINT: API LABA RUGI PIVOT (Kode 4, 5)
     * =================================================================
     */
    public function apiGetLabaRugi(array $input) 
    {
        try {
            $data = $this->generatePivotReport($input, ['4', '5']);
            sendResponse(200, "Berhasil memuat Laporan Laba Rugi (Pivot)", $data);
        } catch (Exception $e) {
            sendResponse(500, "Gagal memuat Laporan Laba Rugi: " . $e->getMessage(), null);
        }
    }

    /**
     * FUNGSI DETAIL: Untuk cek per Kantor, per Kanwil, atau Total Konsolidasi saja
     */
    public function getReportDetail(array $input) 
    {
        try {
            $tanggal = $input['harian_date'] ?? date('Y-m-d', strtotime('-1 day'));
            $typeReport = $input['type'] ?? ''; 
            $kodeKantor = $input['kode_kantor'] ?? 'konsolidasi';

            $prefixes = (strpos($typeReport, 'neraca') !== false) ? ['1', '2', '3'] : ['4', '5'];
            $sqlLike = "";
            foreach ($prefixes as $p) { $sqlLike .= "TRIM(CAST(kode_perk AS CHAR)) LIKE '{$p}%' OR "; }
            $sqlLike = rtrim($sqlLike, " OR ");

            $sqlFilter = "";
            $params = [':tanggal' => $tanggal];

            if ($kodeKantor === 'konsolidasi') {
                $sqlFilter = " AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') BETWEEN '000' AND '028' ";
            } else {
                $params[':kode_kantor'] = str_pad($kodeKantor, 3, '0', STR_PAD_LEFT);
                $sqlFilter = " AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') = :kode_kantor ";
            }

            // Note: HAVING SUM <> 0 kita hapus agar data 0 ketarik dulu ke PHP
            $sql = "
                SELECT 
                    TRIM(CAST(kode_perk AS CHAR)) AS kode_perk,
                    SUM(saldo_akhir) AS total_saldo
                FROM acc_history
                WHERE tanggal = :tanggal
                {$sqlFilter}
                AND ({$sqlLike})
                GROUP BY kode_perk
                ORDER BY kode_perk ASC
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $coaDict = $this->getCoaDictionary();
            $mappedData = [];

            foreach ($results as $row) {
                $kode = $row['kode_perk'];
                $saldo = (float)$row['total_saldo'];
                $panjangKode = strlen($kode);

                // 🔥 LOGIKA FILTER SAKTI:
                // 1. Jika panjang kode <= 3 digit, MASUKKAN (meskipun saldo 0)
                // 2. Jika panjang kode > 3 digit DAN saldo != 0, MASUKKAN
                // 3. Selain itu (kode > 3 digit dan saldo 0), ABAIKAN
                if ($panjangKode <= 3 || ($panjangKode > 3 && $saldo != 0)) {
                    $mappedData[] = [
                        'kode_perk'      => $kode,
                        'nama_perkiraan' => $coaDict[$kode] ?? 'Detail Lainnya',
                        'total_saldo'    => $saldo,
                        'kantor_cek'     => strtoupper($kodeKantor)
                    ];
                }
            }

            sendResponse(200, "Berhasil memuat Detail " . strtoupper($typeReport), $mappedData);

        } catch (Exception $e) {
            sendResponse(500, "Error Detail: " . $e->getMessage());
        }
    }

    /**
     * =================================================================
     * ENDPOINT: API TREN PERKIRAAN SPESIFIK (MtM & YtY)
     * =================================================================
     */
    public function apiGetTrenPerkiraan(array $input) 
    {
        try {
            $kodePerk = $input['kode_perk'] ?? '';
            $baseDate = $input['harian_date'] ?? date('Y-m-d');
            $kodeKantorReq = $input['kode_kantor'] ?? 'konsolidasi'; 

            if (empty($kodePerk)) {
                sendResponse(400, "Pilih Kode Perkiraan dulu broku!", null);
                return;
            }

            // 1. Generate Tanggal Target Secara Dinamis
            $baseDateObj = new DateTime($baseDate);
            $targetDates = [];
            $labelsMtM = [];
            $labelsYtY = [];
            
            // A. Generate 4 Bulan Terakhir (MtM)
            for ($i = 3; $i >= 0; $i--) {
                $d = clone $baseDateObj;
                $d->modify("first day of -$i month");
                $d->modify('last day of this month');
                if ($i == 0) $d = clone $baseDateObj; 
                
                $dtStr = $d->format('Y-m-d');
                $targetDates[$dtStr] = true;
                $labelsMtM[] = ['date' => $dtStr, 'label' => $d->format('M')];
            }

            // B. Generate 5 Tahun Terakhir (YtY) -> 31 Des
            $currentYear = (int) $baseDateObj->format('Y');
            for ($i = 4; $i >= 0; $i--) {
                $targetYear = $currentYear - $i;
                if ($i == 0) {
                    $dtStr = $baseDateObj->format('Y-m-d');
                } else {
                    $dtStr = $targetYear . '-12-31';
                }
                $targetDates[$dtStr] = true;
                $labelsYtY[] = ['date' => $dtStr, 'label' => (string)$targetYear];
            }

            $dateList = array_keys($targetDates);
            $inQuery = implode(',', array_fill(0, count($dateList), '?'));

            // 2. 🔥 PERBAIKAN LOGIKA FILTER KANTOR & PARAMETER PDO 🔥
            $sqlKantor = "";
            $params = $dateList; // Masukkan list tanggal ke parameter duluan

            if (strtolower($kodeKantorReq) === 'konsolidasi') {
                $sqlKantor = "AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') BETWEEN '000' AND '028'";
            } else {
                $sqlKantor = "AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') = ?";
                $params[] = str_pad($kodeKantorReq, 3, '0', STR_PAD_LEFT); // Tambah param kantor
            }

            // 3. Tarik Data Historikal
            if ($kodePerk === 'LABA_RUGI') {
                $sql = "
                    SELECT 
                        tanggal,
                        (
                            SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '4' THEN saldo_akhir ELSE 0 END) -
                            SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '5' THEN saldo_akhir ELSE 0 END)
                        ) AS total_saldo
                    FROM acc_history
                    WHERE tanggal IN ($inQuery)
                      $sqlKantor
                    GROUP BY tanggal
                ";
                $namaAkun = "LABA RUGI BERJALAN (Pendapatan - Biaya)";
                
            } else {
                $sql = "
                    SELECT 
                        tanggal,
                        SUM(saldo_akhir) AS total_saldo
                    FROM acc_history
                    WHERE tanggal IN ($inQuery)
                      $sqlKantor
                      AND TRIM(CAST(kode_perk AS CHAR)) = ?
                    GROUP BY tanggal
                ";
                
                $params[] = $kodePerk; // Tambah param kode_perk terakhir
                $coaDict = $this->getCoaDictionary();
                $namaAkun = $coaDict[$kodePerk] ?? 'Nama Akun';
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Mapping Hasil Query
            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[$row['tanggal']] = (float) $row['total_saldo'];
            }

            $dataMtM = [];
            foreach ($labelsMtM as $item) {
                $dataMtM[] = ['label' => $item['label'], 'saldo' => $dataMap[$item['date']] ?? 0];
            }

            $dataYtY = [];
            foreach ($labelsYtY as $item) {
                $dataYtY[] = ['label' => $item['label'], 'saldo' => $dataMap[$item['date']] ?? 0];
            }

            $saldoSekarang = $dataMtM[3]['saldo'] ?? 0;
            $saldoBulanLalu = $dataMtM[2]['saldo'] ?? 0;
            $delta = $saldoSekarang - $saldoBulanLalu;
            $persen = ($saldoBulanLalu != 0) ? ($delta / abs($saldoBulanLalu)) * 100 : ($saldoSekarang != 0 ? 100 : 0);
            
            $responseData = [
                'summary' => [
                    'kode_perk' => $kodePerk,
                    'nama_perkiraan' => $namaAkun,
                    'saldo_sekarang' => $saldoSekarang,
                    'delta_nominal' => $delta,
                    'pertumbuhan_persen' => round($persen, 2)
                ],
                'mtm' => $dataMtM,
                'yty' => $dataYtY
            ];

            sendResponse(200, "Berhasil memuat tren data COA", $responseData);

        } catch (Exception $e) {
            sendResponse(500, "Gagal memuat tren: " . $e->getMessage(), null);
        }
    }

    /**
     * =================================================================
     * ENDPOINT: API FINANCIAL KPI DASHBOARD
     * (LDR, BOPO, CASA, ROA, ROE, Cash Ratio, Coverage Ratio, Top Biaya)
     * =================================================================
     */
    public function apiGetFinancialKPI(array $input) 
    {
        try {
            $baseDate = $input['harian_date'] ?? date('Y-m-d');
            $kodeKantorReq = $input['kode_kantor'] ?? 'konsolidasi';

            // 1. 🔥 FILTER KANTOR (Bisa dipakai u/ acc_history & nominatif) 🔥
            $sqlKantorAcc = "";
            $sqlKantorNom = "";
            $params = [':tanggal' => $baseDate];

            if (strtolower($kodeKantorReq) === 'konsolidasi') {
                $sqlKantorAcc = "AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') BETWEEN '000' AND '028'";
                // Nominatif konsolidasi tidak perlu filter cabang
            } else {
                $sqlKantorAcc = "AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') = :kode_kantor";
                $sqlKantorNom = "AND LPAD(CAST(kode_cabang AS CHAR), 3, '0') = :kode_kantor";
                $params[':kode_kantor'] = str_pad($kodeKantorReq, 3, '0', STR_PAD_LEFT);
            }

            // =========================================================
            // QUERY 1: Hitung Semua Variabel dari ACC_HISTORY (Buku Besar)
            // =========================================================
            // Tambahan: '107' (CKPN Kredit) untuk hitung Coverage Ratio
            $sqlRasio = "
                SELECT 
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '1' THEN saldo_akhir ELSE 0 END) AS total_aset,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '3' THEN saldo_akhir ELSE 0 END) AS total_ekuitas,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '101' THEN saldo_akhir ELSE 0 END) AS kas,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '104' THEN saldo_akhir ELSE 0 END) AS penempatan_bank,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '201' THEN saldo_akhir ELSE 0 END) AS kewajiban_segera,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '106' THEN saldo_akhir ELSE 0 END) AS kredit,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '107' THEN saldo_akhir ELSE 0 END) AS ckpn_kredit,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '20401' THEN saldo_akhir ELSE 0 END) AS tabungan,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '20402' THEN saldo_akhir ELSE 0 END) AS deposito,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '4' THEN saldo_akhir ELSE 0 END) AS total_pendapatan,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '5' THEN saldo_akhir ELSE 0 END) AS total_biaya,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '401' THEN saldo_akhir ELSE 0 END) AS pend_ops,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '501' THEN saldo_akhir ELSE 0 END) AS biaya_ops
                FROM acc_history
                WHERE tanggal = :tanggal $sqlKantorAcc
            ";
            
            $stmt1 = $this->pdo->prepare($sqlRasio);
            $stmt1->execute($params);
            $rasioData = $stmt1->fetch(PDO::FETCH_ASSOC);

            // Ekstrak Nominal Acc History
            $totalAset    = (float) ($rasioData['total_aset'] ?? 0);
            $totalEkuitas = (float) ($rasioData['total_ekuitas'] ?? 0);
            $kas          = (float) ($rasioData['kas'] ?? 0);
            $penempatanBank = (float) ($rasioData['penempatan_bank'] ?? 0);
            $kwjbnSegera  = (float) ($rasioData['kewajiban_segera'] ?? 0);
            
            $kredit       = (float) ($rasioData['kredit'] ?? 0);
            // Saldo CKPN biasanya bernilai minus (kredit), jadi kita mutlakkan pakai abs()
            $ckpnKredit   = abs((float) ($rasioData['ckpn_kredit'] ?? 0)); 
            $tabungan     = (float) ($rasioData['tabungan'] ?? 0);
            $deposito     = (float) ($rasioData['deposito'] ?? 0);
            
            $pendOps      = (float) ($rasioData['pend_ops'] ?? 0);
            $biayaOps     = (float) ($rasioData['biaya_ops'] ?? 0);
            
            $labaBerjalan = ((float) ($rasioData['total_pendapatan'] ?? 0)) - ((float) ($rasioData['total_biaya'] ?? 0));
            $dpk          = $tabungan + $deposito;
            $alatLikuid   = $kas + $penempatanBank;

            // =========================================================
            // QUERY 2: Tarik Total NPL dari tabel NOMINATIF (Bocoran Broku)
            // =========================================================
            $sqlNPL = "
                SELECT SUM(baki_debet) as total_npl
                FROM nominatif
                WHERE created = :tanggal 
                  AND kolektibilitas IN ('KL', 'D', 'M')
                  $sqlKantorNom
            ";
            $stmtNpl = $this->pdo->prepare($sqlNPL);
            $stmtNpl->execute($params);
            $nplData = $stmtNpl->fetch(PDO::FETCH_ASSOC);
            $totalNpl = (float) ($nplData['total_npl'] ?? 0);


            // =========================================================
            // PERHITUNGAN RASIO KESEHATAN BANK
            // =========================================================
            $bopo = ($pendOps > 0) ? ($biayaOps / $pendOps) * 100 : 0;
            $ldr  = ($dpk > 0) ? ($kredit / $dpk) * 100 : 0;
            $casa = ($dpk > 0) ? ($tabungan / $dpk) * 100 : 0; 

            // Disetahunkan (Annualized) untuk ROA & ROE
            $currentMonth = (int) date('m', strtotime($baseDate));
            $labaDisetahunkan = ($currentMonth > 0) ? ($labaBerjalan / $currentMonth) * 12 : $labaBerjalan;

            $roa = ($totalAset > 0) ? ($labaDisetahunkan / $totalAset) * 100 : 0;
            $roe = ($totalEkuitas > 0) ? ($labaDisetahunkan / $totalEkuitas) * 100 : 0;
            $cashRatio = ($kwjbnSegera > 0) ? ($alatLikuid / $kwjbnSegera) * 100 : 0;
            
            // COVERAGE RATIO: CKPN vs NPL
            $coverageRatio = ($totalNpl > 0) ? ($ckpnKredit / $totalNpl) * 100 : ($ckpnKredit > 0 ? 100 : 0);

            // =========================================================
            // QUERY 3: Cari Top 5 Biaya Terbesar (Leaf Node Filter)
            // =========================================================
            $sqlTopBiaya = "
                SELECT 
                    TRIM(CAST(kode_perk AS CHAR)) as kode, 
                    SUM(saldo_akhir) as total_biaya
                FROM acc_history
                WHERE tanggal = :tanggal $sqlKantorAcc
                  AND TRIM(CAST(kode_perk AS CHAR)) LIKE '5%'
                GROUP BY TRIM(CAST(kode_perk AS CHAR))
                HAVING SUM(saldo_akhir) > 0
            ";
            
            $stmt2 = $this->pdo->prepare($sqlTopBiaya);
            $stmt2->execute($params);
            $allBiayaRaw = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $allCodes = array_column($allBiayaRaw, 'kode');
            $leafBiaya = [];
            
            foreach ($allBiayaRaw as $row) {
                $kode = $row['kode'];
                $isParent = false;
                
                foreach ($allCodes as $otherKode) {
                    if ($kode !== $otherKode && strpos($otherKode, $kode) === 0) {
                        $isParent = true;
                        break;
                    }
                }
                
                if (!$isParent) {
                    $leafBiaya[] = $row;
                }
            }

            usort($leafBiaya, function($a, $b) {
                return $b['total_biaya'] <=> $a['total_biaya']; 
            });

            $top5BiayaRaw = array_slice($leafBiaya, 0, 5);

            $coaDict = $this->getCoaDictionary();
            $topBiaya = [];
            foreach($top5BiayaRaw as $row) {
                $topBiaya[] = [
                    'kode'    => $row['kode'],
                    'nama'    => $coaDict[$row['kode']] ?? 'Biaya / Beban Lainnya',
                    'nominal' => (float) $row['total_biaya']
                ];
            }

            // =========================================================
            // BUNGKUS KE DALAM RESPONSE JSON
            // =========================================================
            $responseData = [
                'rasio' => [
                    'bopo_persen'           => round($bopo, 2),
                    'ldr_persen'            => round($ldr, 2),
                    'casa_persen'           => round($casa, 2),
                    'roa_persen'            => round($roa, 2),
                    'roe_persen'            => round($roe, 2),
                    'cash_ratio_persen'     => round($cashRatio, 2),
                    'coverage_ratio_persen' => round($coverageRatio, 2), // ✨ INI DIA JAGOAN BARUNYA ✨
                    
                    'detail_nominal' => [
                        'total_kredit'      => $kredit,
                        'total_dpk'         => $dpk,
                        'pend_operasional'  => $pendOps,
                        'biaya_operasional' => $biayaOps,
                        'laba_disetahunkan' => $labaDisetahunkan,
                        'total_aset'        => $totalAset,
                        'total_ekuitas'     => $totalEkuitas,
                        'alat_likuid'       => $alatLikuid,
                        'kewajiban_segera'  => $kwjbnSegera,
                        'ckpn_kredit'       => $ckpnKredit,
                        'total_npl'         => $totalNpl
                    ]
                ],
                'top_5_biaya' => $topBiaya
            ];

            sendResponse(200, "Berhasil memuat KPI Kesehatan Bank (" . strtoupper($kodeKantorReq) . ")", $responseData);

        } catch (Exception $e) {
            sendResponse(500, "Gagal memuat KPI: " . $e->getMessage(), null);
        }
    }

    /**
     * =================================================================
     * ENDPOINT: API SUMMARY PERBANDINGAN (Aktual vs M-1 vs Y-1)
     * =================================================================
     */
    public function GetSummaryPerbandingan(array $input) 
    {
        try {
            $baseDate = $input['harian_date'] ?? date('Y-m-d');
            $kodeKantorReq = $input['kode_kantor'] ?? 'konsolidasi';

            // 1. Tentukan 3 Titik Waktu
            $baseDateObj = new DateTime($baseDate);
            $dateCurrent = $baseDateObj->format('Y-m-d');

            $dLastMonth = clone $baseDateObj;
            $dLastMonth->modify('last day of previous month');
            $dateLastMonth = $dLastMonth->format('Y-m-d');

            $dLastYear = clone $baseDateObj;
            $dLastYear->modify('-1 year');
            $dateLastYear = $dLastYear->format('Y') . '-12-31';

            // 2. Siapkan Filter Kantor
            $sqlKantor = "";
            $params = [$dateCurrent, $dateLastMonth, $dateLastYear];

            if (strtolower($kodeKantorReq) === 'konsolidasi') {
                $sqlKantor = "AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') BETWEEN '000' AND '028'";
            } else {
                $sqlKantor = "AND LPAD(CAST(kode_kantor AS CHAR), 3, '0') = ?";
                $params[] = str_pad($kodeKantorReq, 3, '0', STR_PAD_LEFT);
            }

            // 3. Query Super Cepat: Tarik Akun Makro (1-5) & Akun Mikro Pembentuk Rasio
            $sql = "
                SELECT 
                    tanggal,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '1' THEN saldo_akhir ELSE 0 END) AS aset,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '2' THEN saldo_akhir ELSE 0 END) AS kewajiban,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '3' THEN saldo_akhir ELSE 0 END) AS ekuitas,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '4' THEN saldo_akhir ELSE 0 END) AS pendapatan,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '5' THEN saldo_akhir ELSE 0 END) AS biaya,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '106' THEN saldo_akhir ELSE 0 END) AS kredit,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '20401' THEN saldo_akhir ELSE 0 END) AS tabungan,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '20402' THEN saldo_akhir ELSE 0 END) AS deposito,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '401' THEN saldo_akhir ELSE 0 END) AS pend_ops,
                    SUM(CASE WHEN TRIM(CAST(kode_perk AS CHAR)) = '501' THEN saldo_akhir ELSE 0 END) AS biaya_ops
                FROM acc_history
                WHERE tanggal IN (?, ?, ?) $sqlKantor
                GROUP BY tanggal
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Mapping Data berdasarkan Tanggal (Siapkan default 0)
            $defaultData = [
                'aset' => 0, 'kewajiban' => 0, 'ekuitas' => 0, 'pendapatan' => 0, 'biaya' => 0,
                'kredit' => 0, 'tabungan' => 0, 'deposito' => 0, 'pend_ops' => 0, 'biaya_ops' => 0
            ];

            $dataMap = [
                $dateCurrent   => $defaultData,
                $dateLastMonth => $defaultData,
                $dateLastYear  => $defaultData,
            ];

            foreach ($results as $row) {
                $tgl = $row['tanggal'];
                foreach ($defaultData as $key => $val) {
                    $dataMap[$tgl][$key] = (float) $row[$key];
                }
            }

            // 5. Fungsi Helper Buat Ngitung Pertumbuhan Nominal (%)
            $calculateGrowth = function($current, $past) {
                if ($past == 0) return $current > 0 ? 100 : 0;
                return round((($current - $past) / abs($past)) * 100, 2);
            };

            // Fungsi Helper Buat Ngitung Rasio (Persentase Kesehatan)
            $calculateRasio = function($data) {
                $dpk = $data['tabungan'] + $data['deposito'];
                return [
                    'bopo' => ($data['pend_ops'] > 0) ? ($data['biaya_ops'] / $data['pend_ops']) * 100 : 0,
                    'ldr'  => ($dpk > 0) ? ($data['kredit'] / $dpk) * 100 : 0,
                    'casa' => ($dpk > 0) ? ($data['tabungan'] / $dpk) * 100 : 0,
                ];
            };

            // 6. Format Response Makro (Nominal Aset, Laba Rugi, dll)
            $kategori = ['aset', 'kewajiban', 'ekuitas', 'pendapatan', 'biaya'];
            $summaryData = [];

            foreach ($kategori as $kat) {
                $valCurr  = $dataMap[$dateCurrent][$kat];
                $valPrevM = $dataMap[$dateLastMonth][$kat];
                $valPrevY = $dataMap[$dateLastYear][$kat];

                $summaryData[$kat] = [
                    'nominal_aktual'     => $valCurr,
                    'nominal_bulan_lalu' => $valPrevM,
                    'nominal_tahun_lalu' => $valPrevY,
                    'growth_mom'         => $calculateGrowth($valCurr, $valPrevM),
                    'growth_yoy'         => $calculateGrowth($valCurr, $valPrevY) 
                ];
            }

            // Laba Rugi (Pendapatan - Biaya)
            $lrCurr  = $dataMap[$dateCurrent]['pendapatan'] - $dataMap[$dateCurrent]['biaya'];
            $lrPrevM = $dataMap[$dateLastMonth]['pendapatan'] - $dataMap[$dateLastMonth]['biaya'];
            $lrPrevY = $dataMap[$dateLastYear]['pendapatan'] - $dataMap[$dateLastYear]['biaya'];

            $summaryData['laba_rugi'] = [
                'nominal_aktual'     => $lrCurr,
                'nominal_bulan_lalu' => $lrPrevM,
                'nominal_tahun_lalu' => $lrPrevY,
                'growth_mom'         => $calculateGrowth($lrCurr, $lrPrevM),
                'growth_yoy'         => $calculateGrowth($lrCurr, $lrPrevY)
            ];

            // 7. Format Response Rasio Kesehatan (Khusus Persentase)
            $rasioCurr  = $calculateRasio($dataMap[$dateCurrent]);
            $rasioPrevM = $calculateRasio($dataMap[$dateLastMonth]);
            $rasioPrevY = $calculateRasio($dataMap[$dateLastYear]);

            $rasioData = [];
            foreach (['bopo', 'ldr', 'casa'] as $r) {
                $rasioData[$r] = [
                    'persen_aktual'     => round($rasioCurr[$r], 2),
                    'persen_bulan_lalu' => round($rasioPrevM[$r], 2),
                    'persen_tahun_lalu' => round($rasioPrevY[$r], 2),
                    'delta_mom'         => round($rasioCurr[$r] - $rasioPrevM[$r], 2), 
                    'delta_yoy'         => round($rasioCurr[$r] - $rasioPrevY[$r], 2)
                ];
            }

            // 8. Rangkum Hasil
            $responseData = [
                'info_kantor'  => strtoupper($kodeKantorReq),
                'info_tanggal' => [
                    'aktual'     => $dateCurrent,
                    'bulan_lalu' => $dateLastMonth,
                    'tahun_lalu' => $dateLastYear
                ],
                'makro' => $summaryData,
                'kesehatan_rasio' => $rasioData
            ];

            sendResponse(200, "Berhasil memuat Summary Perbandingan (" . strtoupper($kodeKantorReq) . ")", $responseData);

        } catch (Exception $e) {
            sendResponse(500, "Gagal memuat Summary: " . $e->getMessage(), null);
        }
    }

    public function apiGetCoaList() 
    {
        try {
            $coaDict = $this->getCoaDictionary();
            $data = [];
            
            // Ubah format dari ['101' => 'Kas'] menjadi [['kode' => '101', 'nama' => 'Kas'], ...]
            foreach ($coaDict as $kode => $nama) {
                $data[] = [
                    'kode' => (string)$kode,
                    'nama' => $nama
                ];
            }

            sendResponse(200, "Berhasil memuat daftar COA", $data);

        } catch (Exception $e) {
            sendResponse(500, "Gagal memuat daftar COA: " . $e->getMessage(), null);
        }
    }

            /**
     * =================================================================
     * 1. KAMUS COA (Breakdown Nama Perkiraan)
     * =================================================================
     */
    private function getCoaDictionary(): array 
    {
        return [
            // ==========================================
            // KODE 1: ASET
            // ==========================================
            '1' => 'Aset',
            '101' => 'Kas',
            '10101' => 'Kas Besar',
            '10102' => 'Kas Kecil',
            '10103' => 'Kas Dalam Atm',
            '10104' => 'Kas Dalam Perjalanan (Cash In Transit)',
            '10105' => 'Kas Branchless',
            '10106' => 'Kas Echannel',
            '10199' => 'TDP',
            '102' => 'Kas Dalam Valuta Asing',
            '10201' => 'Kas Vallas - Dollar',
            '10202' => 'Kas Vallas - Euro',
            '10203' => 'Kas Vallas - Yen',
            '10299' => 'Travel Cheque',
            '103' => 'Surat Berharga',
            '10301' => 'Sertifikat Bank Indonesia',
            '104' => 'Penempatan Pada Bank Lain',
            '10401' => 'Giro',
            '10402' => 'Tabungan',
            '10403' => 'Deposito Berjangka',
            '10404' => 'Sertifikat Deposito',
            '105' => 'Cadangan Kerugian Penurunan Nilai (ABA)',
            '10501' => 'CKPN (ABA)',
            '106' => 'Kredit Yang Diberikan',
            '10601' => 'Kredit Yang Diberikan Baki Debet',
            '1060101' => 'Kredit Modal Kerja',
            '106010101' => 'Pertanian',
            '106010102' => 'Multi Manfaat',
            '106010103' => 'Sinden',
            '106010104' => 'Bumdes',
            '106010105' => 'Korporasi',
            '106010106' => 'KMB',
            '1060102' => 'Kredit Investasi',
            '1060103' => 'Kredit Konsumsi',
            '106010301' => 'Joglo',
            '106010302' => 'Pegawai',
            '106010303' => 'Karyawan',
            '106010304' => 'Pensiunan',
            '106010305' => 'Perangkat Desa',
            '106010306' => 'Lainnya',
            '1060104' => 'Kredit Mikro Bkk',
            '1060105' => 'Kredit BKK Joglo',
            '1060106' => 'Kredit BKK Sinden',
            '1060107' => 'Kredit BKK Korporasi',
            '1060108' => 'Kredit BKK Bumdes',
            '1060109' => 'Kredit BKK Musiman',
            '1060110' => 'Kredit Kolektif Karyawan (K3)',
            '1060111' => 'Kredit KPP',
            '1060112' => 'Kredit UMKM BKK (KUB)',
            '1060113' => 'Kredit UMKM BKK (KUB) 6',
            '1060114' => 'Kredit Koperasi',
            '1060115' => 'Kredit Agro',
            '1060116' => 'Kredit Bahari',
            '1060117' => 'Kredit BKK Joglo Mitra',
            '1060118' => 'Kredit BKK Joglo Khusus Pegawai',
            '10602' => 'KYD Provisi / Administrasi',
            '1060201' => 'Pendapatan Ditangguhkan - Provisi',
            '106020101' => 'KYD Provisi Kredit Modal Kerja',
            '106020102' => 'KYD Provisi Kredit Investasi',
            '106020103' => 'KYD Provisi Kredit Konsumsi',
            '106020104' => 'KYD Provisi Kredit Mikro Bkk',
            '106020105' => 'KYD Provisi Kredit BKK Joglo',
            '106020106' => 'KYD Provisi Kredit BKK Sinden',
            '106020107' => 'KYD Provisi Kredit BKK Korporasi',
            '106020108' => 'KYD Provisi Kredit BKK Bumdes',
            '106020109' => 'KYD Provisi Kredit BKK Musiman',
            '106020110' => 'KYD Provisi Kredit Kolektif Karyawan (K3)',
            '106020111' => 'KYD Provisi Kredit KPP',
            '106020112' => 'KYD Provisi Kredit KUB',
            '106020113' => 'KYD Provisi Kredit KUB 6',
            '106020114' => 'KYD Provisi Kredit Koperasi',
            '106020115' => 'KYD Provisi Kredit Agro',
            '106020116' => 'KYD Provisi Kredit Bahari',
            '106020117' => 'KYD Provisi Kredit BKK Joglo Mitra',
            '106020118' => 'KYD Provisi Kredit BKK Joglo Khusus Pegawai',
            '1060202' => 'Pendapatan Ditangguhkan - Administrasi',
            '106020201' => 'KYD Adm Kredit Modal Kerja',
            '106020202' => 'KYD Adm Kredit Investasi',
            '106020203' => 'KYD Adm Kredit Konsumsi',
            '106020204' => 'KYD Adm Kredit Mikro BKK',
            '106020205' => 'KYD Adm Kredit BKK Joglo',
            '106020206' => 'KYD Adm Kredit BKK Sinden',
            '106020207' => 'KYD Adm Kredit BKK Korporasi',
            '106020208' => 'KYD Adm Kredit BKK Bumdes',
            '106020209' => 'KYD Adm Kredit BKK Musiman',
            '106020210' => 'KYD Adm Kredit Kolektif Karyawan (K3)',
            '106020211' => 'KYD Adm Kredit KPP',
            '106020212' => 'KYD Adm Kredit KUB',
            '106020213' => 'KYD Adm Kredit KUB (Promo)',
            '106020214' => 'KYD Adm Kredit Agro',
            '106020215' => 'KYD Adm Kredit Bahari',
            '106020216' => 'KYD Adm Kredit BKK Joglo Mitra',
            '106020217' => 'KYD Adm Kredit BKK Joglo Khusus Pegawai',
            '10603' => 'KYD Biaya Transaksi',
            '1060301' => 'Biaya Di Tangguhkan - Biaya Transaksi',
            '106030101' => 'KYD By Trans Kredit Modal Kerja',
            '106030102' => 'KYD By Trans Kredit Investasi',
            '106030103' => 'KYD By Trans Kredit Konsumsi',
            '106030104' => 'KYD By Trans Kredit Mikro Bkk',
            '106030105' => 'KYD By Trans Kredit BKK Joglo',
            '106030106' => 'KYD By Trans Kredit BKK Sinden',
            '106030107' => 'KYD By Trans Kredit BKK Korporasi',
            '106030108' => 'KYD By Trans Kredit BKK Bumdes',
            '106030109' => 'KYD By Trans Kredit BKK Musiman',
            '106030110' => 'KYD By Trans Kredit Kolektif Karyawan (K3)',
            '106030111' => 'KYD By Trans Kredit KPP',
            '106030112' => 'KYD By Trans Kredit KUB',
            '106030113' => 'KYD By Trans Kredit Koperasi',
            '106030114' => 'KYD By Trans Kredit Agro',
            '106030115' => 'KYD By Trans Kredit Bahari',
            '106030116' => 'KYD By Trans Kredit BKK Joglo Mitra',
            '106030117' => 'KYD By Trans Kredit BKK Joglo Khusus Pegawai',
            '10604' => '-/- Pendapatan Yang Ditangguhkan Dalam Rangka',
            '10605' => '-/- Cadangan Kerugian Restrukturisasi',
            '10606' => 'Selisih Flat dan EIR',
            '107' => 'Cadangan Kerugian Penurunan Nilai (Kredit)',
            '10701' => 'CKPN Individual',
            '10702' => 'CKPN Kolektif',
            '108' => 'Agunan Yang Diambil Alih (AYDA)',
            '109' => 'Aktiva Tetap & Inventaris',
            '10901' => 'Tanah',
            '10902' => 'Gedung',
            '10903' => 'Peralatan Dan Perlengkapan',
            '10904' => 'Kendaraan',
            '10905' => 'Lainnya',
            '110' => '(-/-) Akumulasi Penyusutan Dan Penurunan Nilai',
            '11001' => 'Akumulasi Peny. Gedung',
            '1100101' => 'Akum. Gedung -/-',
            '11002' => 'Akumulasi Peny. Inventaris',
            '1100201' => 'Akum. Kendaraan',
            '1100202' => 'Akum. Peralatan Dan Perlengkapan',
            '1100203' => 'Akum. Lainnya',
            '111' => 'Aset Tidak Berwujud',
            '11101' => 'Program Aplikasi Core Banking (Software)',
            '11102' => 'Lainnya',
            '11103' => '(-/-) Akumulasi Amortisasi Dan Penurunan Nilai',
            '1110301' => 'Akum. Amortisasi Peny. Nilai -/-',
            '111030101' => 'Program Aplikasi Core Banking -/-',
            '111030102' => 'Lainnya -/-',
            '112' => 'Aset Antar Kantor',
            '11200' => 'AKA Kantor Pusat',
            '11201' => 'AKA KCU',
            '11202' => 'AKA KC Rembang',
            '11203' => 'AKA KC Pati',
            '11204' => 'AKA KC Demak',
            '11205' => 'AKA KC Kendal',
            '11206' => 'AKA KC Salatiga',
            '11207' => 'AKA KC Semarang',
            '11208' => 'AKA KC Wonogiri',
            '11209' => 'AKA KC Kota Surakarta',
            '11210' => 'AKA KC Karanganyar',
            '11211' => 'AKA KC Sukoharjo',
            '11212' => 'AKA KC Sragen',
            '11213' => 'AKA KC Boyolali',
            '11214' => 'AKA KC Magelang',
            '11215' => 'AKA KC Wonosobo',
            '11216' => 'AKA KC Purworejo',
            '11217' => 'AKA KC Kebumen',
            '11218' => 'AKA KC Banjarnegara',
            '11219' => 'AKA KC Purbalingga',
            '11220' => 'AKA KC Banyumas',
            '11221' => 'AKA KC Cilacap',
            '11222' => 'AKA KC Tegal',
            '11223' => 'AKA KC Brebes',
            '11224' => 'AKA KC Kota Tegal',
            '11225' => 'AKA KC Pemalang',
            '11226' => 'AKA KC Kota Pekalongan',
            '11227' => 'AKA KC Pekalongan',
            '11228' => 'AKA KC Batang',
            '113' => 'Aset Lain Lain',
            '11301' => 'Pendapatan Bunga Yang Akan Diterima (Pyad)',
            '1130101' => 'PYAD - Penempatan Pada Bank Lain',
            '1130102' => 'PYAD - Kredit Yang Diberikan',
            '11302' => 'Premi Penjamin Lps Dibayar Dimuka',
            '11303' => 'Pajak Di Bayar Dimuka',
            '1130301' => 'Pajak Dibayar Dimuka PPH',
            '1130302' => 'Pajak Dibayar Dimuka PPN',
            '11304' => 'Aset Pajak Tangguhan',
            '11305' => 'Biaya Di Bayar Di Muka',
            '1130501' => 'Biaya Dibayar Dimuka- Sewa',
            '1130502' => 'Biaya Dibayar Dimuka- Sewa Gedung',
            '1130503' => 'Biaya Dibayar Dimuka- Sewa Kendaraan',
            '1130504' => 'Biaya Dibayar Dimuka- Sewa Inventaris',
            '1130505' => 'Biaya Dibayar Dimuka- Sewa Lainnya',
            '1130506' => 'Biaya Dibayar Dimuka - Asuransi Purjab',
            '1130507' => 'Biaya Dibayar Dimuka - Bunga Deposito',
            '1130508' => 'Biaya Dibayar Dimuka - BPJS TK',
            '1130509' => 'Biaya Dibayar Dimuka - BPJS Kesehatan',
            '1130510' => 'Biaya Dibayar Dimuka - Asuransi',
            '1130599' => 'Biaya Dibayar Dimuka - Lainnya',
            '11306' => 'Tagihan Kepada Perusahaan Asuransi',
            '11307' => 'Uang Muka Kegiatan Operasional',
            '1130701' => 'Uang Muka Akomodasi dan Rapat',
            '1130702' => 'Uang Muka Pembelian',
            '1130703' => 'Uang Muka Operasional Kantor Wilayah',
            '1130704' => 'Uang Muka Jasa Pihak Ketiga',
            '1130705' => 'Uang Muka Pendidikan dan Pelatihan',
            '1130706' => 'Uang Muka Penanganan Kredit Bermasalah',
            '1130799' => 'Uang Muka Kegiatan Operasional Lainnya',
            '11399' => 'Aset lain-lain Lainnya',
            '1139901' => 'Persediaan Barang Cetakan',
            '1139902' => 'Persediaan Materai',
            '1139903' => 'Mata Uang Yg Ditarik Peredaran',
            '1139904' => 'Deposit PPOB',
            '1139905' => 'Deposit Mobile Banking',
            '1139906' => 'Kredit Dalam Penyelesaian',
            '1139907' => 'Titipan QRIS',
            '1139999' => 'Lain-Lain',
            

            // ==========================================
            // KODE 2: KEWAJIBAN
            // ==========================================
            '2' => 'Kewajiban',
            '201' => 'Kewajiban-Kewajiban Yang Segera',
            '20101' => 'Deposito Jatuh Tempo Yg Blm Ditarik',
            '20102' => 'Tabungan Berjangka Yg Jth Tmpo Yg Blm Ditarik',
            '20103' => 'Kewajiban Kpd Pemerintah Yg Hrs Dibayar',
            '2010301' => 'Pph Tabungan Final (Pasal 4 Ayat 2)',
            '2010302' => 'Pph Deposito (Pasal 4 Ayat 2)',
            '2010303' => 'PPh Pengurus Dan Pegawai (Ps 21 &/ 26)',
            '2010304' => 'Pph Juru Bayar (Pasal 21 &/ 26)',
            '2010305' => 'Ppn',
            '201030501' => 'Ppn Barang (Pasal 22)',
            '201030502' => 'Ppn Jasa (Pasal 23)',
            '2010306' => 'Hutang Pajak Badan (Pasal 29)',
            '2010307' => 'Pajak Lainnya',
            '20104' => 'Sanksi Kewajiban Membayar Kepada Bi Yg Blm Dib',
            '20105' => 'Titipan Nasabah',
            '2010501' => 'Kiriman Uang',
            '2010502' => 'Titipan Pln',
            '2010503' => 'Titipan Pdam',
            '2010504' => 'Kreditur / Simpanan',
            '2010505' => 'Debitur / Angsuran Kredit',
            '2010506' => 'Notaris',
            '2010507' => 'Premi Asuransi',
            '201050701' => 'Bumi Putra',
            '201050702' => 'Askrida',
            '201050703' => 'Jamkrida',
            '201050704' => 'Jiwasraya',
            '201050705' => 'BPJS TK KREDIT',
            '201050799' => 'Lainnya',
            '20106' => 'KYD Bersaldo Kredit',
            '20107' => 'Deviden Yang Belum Di Bayarkan',
            '2010701' => 'Pemerintah Provinsi',
            '2010702' => 'Pemerintah Kabupaten',
            '20108' => 'Selisih Hasil Penjualan Ayda',
            '20109' => 'Imbalan Kerja',
            '2010901' => 'Dana Kesejahteraan Yang Harus Dibayar',
            '2010902' => 'Jasa Produksi Yang Harus Dibayar',
            '20110' => 'Premi BPJS Kesehatan',
            '20111' => 'Premi BPJS Ketenagakerjaan',
            '20199' => 'Kewajiban Segera Lainnya',
            '2019901' => 'Dana Kesejahteraan',
            '2019902' => 'PPOB / EDC',
            '2019903' => 'Dana Bergulir',
            '2019904' => 'Subsidi Bunga',
            '2019905' => 'Pembayaran Pbb',
            '2019906' => 'Pembayaran Pdam',
            '2019907' => 'Pembayaran Pajak Kendaraan',
            '2019908' => 'ABA Dalam Penyelesaian',
            '2019909' => 'Kewajiban Gaji',
            '2019999' => 'KWS Lainnya Lain-lain',
            '202' => 'Utang Bunga',
            '20201' => 'Tabungan Berjangka',
            '20202' => 'Deposito',
            '2020201' => 'A. Sudah Jatuh Tempo',
            '2020202' => 'B. Belum Jatuh Tempo',
            '20203' => 'Simpanan Dari Bank Lain',
            '2020301' => 'A. Sudah Jatuh Tempo',
            '2020302' => 'B. Belum Jatuh Tempo',
            '20204' => 'Pinjaman Yang Diterima',
            '2020401' => 'A. Pinjaman Yang Diterima Sudah Jatuh Tempo',
            '2020402' => 'B. Pinjaman Yang Diterima Belum Jatuh Tempo',
            '20299' => 'Bunga Lainnya',
            '203' => 'Utang Pajak',
            '20301' => 'Taksiran Pajak Penghasilan Pph Badan',
            '204' => 'Simpanan',
            '20401' => 'Tabungan',
            '2040101' => 'Tabungan Wajib',
            '2040102' => 'Tabungan Tamades',
            '2040103' => 'Tabungan Tamades 1 (Bunga Harian)',
            '2040104' => 'Tabungan Tamades 2 (Tabungan Program)',
            '2040105' => 'Tabungan Tamades 3',
            '2040106' => 'Tabungan Tamades 4',
            '2040107' => 'Tabungan Tamades 5',
            '2040108' => 'Tabungan Pelajar',
            '2040109' => 'TAMADES',
            '2040110' => 'TAWA',
            '2040111' => 'TAWA PLUS',
            '2040112' => 'Tabungan Kredit BKK',
            '2040113' => 'Tabungan Mitra BKK',
            '2040114' => 'Tabungan BKK Prioritas',
            '20402' => 'Deposito',
            '2040201' => 'Deposito 1 Bulan',
            '2040202' => 'Deposito 3 Bulan',
            '2040203' => 'Deposito 6 Bulan',
            '2040204' => 'Deposito 9 Bulan',
            '2040205' => 'Deposito 12 Bulan',
            '205' => 'Simpanan Dari Bank Lain',
            '20501' => 'Bank Indonesia',
            '20502' => 'Bank Lain',
            '2050201' => 'Deposito',
            '2050202' => 'Tabungan',
            '206' => 'Pinjaman Diterima',
            '20601' => 'Bank Indonesia',
            '20602' => 'Bank Lain',
            '2060201' => 'Bank Umum',
            '2060202' => 'Bpr',
            '2060203' => 'Terkait Apex',
            '2060204' => 'Dalam Rangka Linkage',
            '20603' => 'Dari Bukan Bank',
            '2060301' => 'Kewajiban Sewa Pembiayaan',
            '2060399' => 'Lainnya',
            '20699' => 'Lainnya',
            '207' => 'Dana Setoran Modal Kewajiban',
            '20701' => 'Pemerintah Provinsi Jawa Tengah',
            '20702' => 'Pemerintah Kabupaten',
            '2070201' => 'Pemkab Semarang',
            '2070202' => 'Pemkot Salatiga',
            '2070203' => 'Pemkab Pati',
            '2070204' => 'Pemkab Rembang',
            '2070205' => 'Pemkab Kendal',
            '2070206' => 'Pemkab Demak',
            '2070207' => 'Pemkab Banjarnegara',
            '2070208' => 'Pemkab Wonosobo',
            '2070209' => 'Pemkab Purworejo',
            '2070210' => 'Pemkab Magelang',
            '2070211' => 'Pemkab Cilacap',
            '2070212' => 'Pemkab Purbalingga',
            '2070213' => 'Pemkab Banyumas',
            '2070214' => 'Pemkab Temanggung',
            '2070215' => 'Pemkab Boyolali',
            '2070216' => 'Pemkab Karanganyar',
            '2070217' => 'Pemkab Wonogiri',
            '2070218' => 'Pemkab Klaten',
            '2070219' => 'Pemkab Sukoharjo',
            '2070220' => 'Pemkot Surakarta',
            '2070221' => 'Pemkab Sragen',
            '2070222' => 'Pemkot Pekalongan',
            '2070223' => 'Pemkab Tegal',
            '2070224' => 'Pemkab Batang',
            '2070225' => 'Pemkab Pemalang',
            '2070226' => 'Pemkab Pekalongan',
            '2070227' => 'Pemkot Tegal',
            '2070228' => 'Pemkab Brebes',
            '2070229' => 'Pemkab Kebumen',
            '208' => 'Kewajiban Imbalan Kerja',
            '20801' => 'Jangka Pendek',
            '2080101' => 'Thr',
            '2080102' => 'Tunj. Bantuan Pendidikan',
            '2080103' => 'Kinerja',
            '208010301' => 'Kinerja 1',
            '208010302' => 'Kinerja 2',
            '20802' => 'Jangka Panjang',
            '2080201' => 'Jasa Pengabdian Pengurus',
            '2080202' => 'Jasa Pengabdian Pegawai',
            '2080203' => 'Imbalan Pesangon Phk',
            '20899' => 'Kewajiban Imbalan Kerja Lainnya',
            '209' => 'Pinjaman Subordinasi',
            '20901' => 'Modal Pinjaman',
            '210' => 'Kewajiban Antar Kantor',
            '21000' => 'AKP Kantor Pusat',
            '21001' => 'AKP KCU',
            '21002' => 'AKP KC Rembang',
            '21003' => 'AKP KC Pati',
            '21004' => 'AKP KC Demak',
            '21005' => 'AKP KC Kendal',
            '21006' => 'AKP KC Salatiga',
            '21007' => 'AKP KC Semarang',
            '21008' => 'AKP KC Wonogiri',
            '21009' => 'AKP KC Kota Surakarta',
            '21010' => 'AKP KC Karanganyar',
            '21011' => 'AKP KC Sukoharjo',
            '21012' => 'AKP KC Sragen',
            '21013' => 'AKP KC Boyolali',
            '21014' => 'AKP KC Magelang',
            '21015' => 'AKP KC Wonosobo',
            '21016' => 'AKP KC Purworejo',
            '21017' => 'AKP KC Kebumen',
            '21018' => 'AKP KC Banjarnegara',
            '21019' => 'AKP KC Purbalingga',
            '21020' => 'AKP KC Banyumas',
            '21021' => 'AKP KC Cilacap',
            '21022' => 'AKP KC Tegal',
            '21023' => 'AKP KC Brebes',
            '21024' => 'AKP KC Kota Tegal',
            '21025' => 'AKP KC Pemalang',
            '21026' => 'AKP KC Kota Pekalongan',
            '21027' => 'AKP KC Pekalongan',
            '21028' => 'AKP KC Batang',
            '211' => 'Kewajiban Lain Lain',
            '21101' => 'Taksiran Pajak Penghasilan',
            '21102' => 'Pendapatan Yang Ditangguhkan',
            '21103' => 'Lainnya',
            '2110301' => 'Pakaian Dinas',
            '2110302' => 'Rekreasi',
            '2110303' => 'Undian',
            '2110304' => 'Olah Raga',
            '2110305' => 'Dana Kesejahteraan',
            '2110306' => 'Jasa Produksi',
            '2110307' => 'Akomodasi KAP',
            '2110308' => 'Titipan Angs. BKK Pingsurat',
            '2110310' => 'CSR',
            '2110311' => 'Tantiem',
            '2110399' => 'Kewajiban Lain-lain Lainnya',
            
            // ==========================================
            // KODE 3: EKUITAS
            // ==========================================
            '3' => 'Ekuitas',
            '301' => 'Modal Disetor',
            '30101' => 'Modal Dasar',
            '3010101' => 'Pemerintah Provinsi Jawa Tengah 51 %',
            '3010102' => 'Pemerintah Kabupaten 49 %',
            '301010201' => 'Pemkab Semarang',
            '301010202' => 'Pemkot Salatiga',
            '301010203' => 'Pemkab Pati',
            '301010204' => 'Pemkab Rembang',
            '301010205' => 'Pemkab Kendal',
            '301010206' => 'Pemkab Demak',
            '301010207' => 'Pemkab Banjarnegara',
            '301010208' => 'Pemkab Wonosobo',
            '301010209' => 'Pemkab Purworejo',
            '301010210' => 'Pemkab Magelang',
            '301010211' => 'Pemkab Cilacap',
            '301010212' => 'Pemkab Purbalingga',
            '301010213' => 'Pemkab Banyumas',
            '301010214' => 'Pemkab Temanggung',
            '301010215' => 'Pemkab Boyolali',
            '301010216' => 'Pemkab Karanganyar',
            '301010217' => 'Pemkab Wonogiri',
            '301010218' => 'Pemkab Klaten',
            '301010219' => 'Pemkab Sukoharjo',
            '301010220' => 'Pemkot Surakarta',
            '301010221' => 'Pemkab Sragen',
            '301010222' => 'Pemkot Pekalongan',
            '301010223' => 'Pemkab Tegal',
            '301010224' => 'Pemkab Batang',
            '301010225' => 'Pemkab Pemalang',
            '301010226' => 'Pemkab Pekalongan',
            '301010227' => 'Pemkot Tegal',
            '301010228' => 'Pemkab Brebes',
            '301010229' => 'Pemkab Kebumen',
            '30102' => 'Modal Yang Belum Disetor -/-',
            '3010201' => 'Pemerintah Provinsi Jawa Tengah',
            '3010202' => 'Pemerintah Kabupaten / Kota',
            '301020201' => 'Pemkab Semarang',
            '301020202' => 'Pemkot Salatiga',
            '301020203' => 'Pemkab Pati',
            '301020204' => 'Pemkab Rembang',
            '301020205' => 'Pemkab Kendal',
            '301020206' => 'Pemkab Demak',
            '301020207' => 'Pemkab Banjarnegara',
            '301020208' => 'Pemkab Wonosobo',
            '301020209' => 'Pemkab Purworejo',
            '301020210' => 'Pemkab Magelang',
            '301020211' => 'Pemkab Cilacap',
            '301020212' => 'Pemkab Purbalingga',
            '301020213' => 'Pemkab Banyumas',
            '301020214' => 'Pemkab Temanggung',
            '301020215' => 'Pemkab Boyolali',
            '301020216' => 'Pemkab Karanganyar',
            '301020217' => 'Pemkab Wonogiri',
            '301020218' => 'Pemkab Klaten',
            '301020219' => 'Pemkab Sukoharjo',
            '301020220' => 'Pemkot Surakarta',
            '301020221' => 'Pemkab Sragen',
            '301020222' => 'Pemkot Pekalongan',
            '301020223' => 'Pemkab Tegal',
            '301020224' => 'Pemkab Batang',
            '301020225' => 'Pemkab Pemalang',
            '301020226' => 'Pemkab Pekalongan',
            '301020227' => 'Pemkot Tegal',
            '301020228' => 'Pemkab Brebes',
            '301020229' => 'Pemkab Kebumen',
            '30103' => 'Agio',
            '3010301' => 'Agio Saham',
            '30104' => 'Disagio -/-',
            '3010401' => 'Disagio Saham -/-',
            '30105' => 'Modal Sumbangan',
            '30106' => 'Modal Pinjaman',
            '30107' => 'Dana Setoran Modal - Ekuitas',
            '3010701' => 'Pemerintah Provinsi Jawa Tengah',
            '3010702' => 'Pemerintah Kabupaten',
            '301070201' => 'Pemkab Semarang',
            '301070202' => 'Pemkot Salatiga',
            '301070203' => 'Pemkab Pati',
            '301070204' => 'Pemkab Rembang',
            '301070205' => 'Pemkab Kendal',
            '301070206' => 'Pemkab Demak',
            '301070207' => 'Pemkab Banjarnegara',
            '301070208' => 'Pemkab Wonosobo',
            '301070209' => 'Pemkab Purworejo',
            '301070210' => 'Pemkab Magelang',
            '301070211' => 'Pemkab Cilacap',
            '301070212' => 'Pemkab Purbalingga',
            '301070213' => 'Pemkab Banyumas',
            '301070214' => 'Pemkab Temanggung',
            '301070215' => 'Pemkab Boyolali',
            '301070216' => 'Pemkab Karanganyar',
            '301070217' => 'Pemkab Wonogiri',
            '301070218' => 'Pemkab Klaten',
            '301070219' => 'Pemkab Sukoharjo',
            '301070220' => 'Pemkot Surakarta',
            '301070221' => 'Pemkab Sragen',
            '301070222' => 'Pemkot Pekalongan',
            '301070223' => 'Pemkab Tegal',
            '301070224' => 'Pemkab Batang',
            '301070225' => 'Pemkab Pemalang',
            '301070226' => 'Pemkab Pekalongan',
            '301070227' => 'Pemkot Tegal',
            '301070228' => 'Pemkab Brebes',
            '301070229' => 'Pemkab Kebumen',
            '302' => 'Laba / Rugi Yang Blm Direalisasi',
            '30201' => 'Surplus Revaluasi Aset Tetap',
            '303' => 'Saldo Laba',
            '30301' => 'Cadangan Umum',
            '30302' => 'Cadangan Tujuan',
            '30303' => 'Laba Rugi',
            '3030301' => 'Laba / Rugi Tahun Lalu',
            '303030101' => 'Laba / Rugi Tahun Lalu',
            '3030302' => 'Laba / Rugi Tahun Berjalan',
            
            // ==========================================
            // KODE 4: PENDAPATAN
            // ==========================================
            '4' => 'Pendapatan',
            '401' => 'Pendapatan Operasional',
            '40101' => '1. Pendapatan Bunga',
            '4010101' => 'A. Bunga Kontraktual',
            '401010101' => 'Surat Berharga',
            '40101010101' => 'Sertifikat Bank Indonesia',
            '401010102' => 'Bunga Penempatan Dari Bank Lain',
            '40101010201' => 'I. Giro',
            '40101010202' => 'Ii. Tabungan',
            '40101010203' => 'Iii. Deposito',
            '40101010204' => 'Iv. Sertifikat Deposito',
            '401010103' => 'Kredit Yang Diberikan',
            '40101010301' => 'Kepada Bank Lain',
            '40101010302' => 'Kepada Pihak Ketiga Bukan Bank',
            '4010101030201' => 'Pend. Bg Kredit Modal Kerja',
            '4010101030202' => 'Pend. Bg Kredit Investasi',
            '4010101030203' => 'Pend. Bg Kredit Konsumtif',
            '4010101030204' => 'Pend. Bg Kredit Mikro Bkk',
            '4010101030205' => 'Pend. Bg Kredit BKK Joglo',
            '4010101030206' => 'Pend. Bg Kredit BKK Sinden',
            '4010101030207' => 'Pend. Bg Kredit BKK Korporasi',
            '4010101030208' => 'Pend. Bg Kredit BKK Bumdes',
            '4010101030209' => 'Pend. Bg Kredit BKK Musiman',
            '4010101030210' => 'Pend. Bg Kredit Kolektif Karyawan (K3)',
            '4010101030211' => 'Pend. Bg Kredit KPP',
            '4010101030212' => 'Pend. Bg Krd KUB',
            '4010101030213' => 'Pend. Bg Krd KUB 6',
            '4010101030214' => 'Pend. Bg Krd Koperasi',
            '4010101030215' => 'Pend. Bg Krd Agro',
            '4010101030216' => 'Pend. Bg Krd Bahari',
            '4010101030217' => 'Pend. Bg Krd BKK Joglo Bahari',
            '4010101030218' => 'Pend. Bg Krd BKK Joglo Khusus Pegawai',
            '4010102' => 'B. Provisi Dan Administrasi',
            '401010201' => '1. Provisi Kredit',
            '40101020101' => 'A. Kepada Bank Lain',
            '40101020102' => 'B. Kepada Pihak Ketiga Bukan Bank',
            '4010102010201' => 'Pend. Provisi Kredit Modal Kerja',
            '4010102010202' => 'Pend. Provisi Kredit Investasi',
            '4010102010203' => 'Pend. Provisi Kredit Konsumtif',
            '4010102010204' => 'Pend. Provisi Kredit Mikro Bkk',
            '4010102010205' => 'Pend. Provisi Kredit BKK Joglo',
            '4010102010206' => 'Pend. Provisi Kredit BKK Sinden',
            '4010102010207' => 'Pend. Provisi Kredit BKK Korporasi',
            '4010102010208' => 'Pend. Provisi Kredit BKK Bumdes',
            '4010102010209' => 'Pend. Provisi Kredit BKK Musiman',
            '4010102010210' => 'Pend. Provisi Kredit Kolektif Karyawan (K3)',
            '4010102010211' => 'Pend. Provisi Kredit KPP',
            '4010102010212' => 'Pend. Provisi Kredit KUB',
            '4010102010213' => 'Pend. Provisi Kredit KUB 6',
            '4010102010214' => 'Pend. Provisi Kredit Koperasi',
            '4010102010215' => 'Pend. Provisi Kredit Agro',
            '4010102010216' => 'Pend. Provisi Kredit Bahari',
            '4010102010217' => 'Pend. Provisi Kredit BKK Joglo Mitra',
            '4010102010218' => 'Pend. Provisi Kredit BKK Joglo Khusus Pegawai',
            '401010202' => '2. Administrasi Kredit',
            '40101020201' => 'A. Kepada Bank Lain',
            '40101020202' => 'B. Kepada Pihak Ketiga Bukan Bank',
            '4010102020201' => 'Pend. Adm Kredit Modal Kerja',
            '4010102020202' => 'Pend. Adm Kredit Investasi',
            '4010102020203' => 'Pend. Adm Kredit Konsumtif',
            '4010102020204' => 'Pend. Adm Kredit Mikro Bkk',
            '4010102020205' => 'Pend. Adm Kredit BKK Joglo',
            '4010102020206' => 'Pend. Adm Kredit BKK Sinden',
            '4010102020207' => 'Pend. Adm Kredit BKK Korporasi',
            '4010102020208' => 'Pend. Adm Kredit BKK Bumdes',
            '4010102020209' => 'Pend. Adm Kredit BKK Musiman',
            '4010102020210' => 'Pend. Adm Kredit Kolektif Karyawan (K3)',
            '4010102020211' => 'Pend. Adm Kredit KKP',
            '4010102020212' => 'Pend. Adm Kredit KUB',
            '4010102020213' => 'Pend. Adm Kredit KUB (Promo)',
            '4010102020214' => 'Pend. Adm Kredit Koperasi',
            '4010102020215' => 'Pend. Adm Kredit Agro',
            '4010102020216' => 'Pend. Adm Kredit Bahari',
            '4010102020217' => 'Pend. Adm Kredit Joglo Mitra',
            '4010102020218' => 'Pend. Adm Kredit BKK Joglo Khusus Pegawai',
            '4010103' => 'C. Biaya Transaksi',
            '401010301' => 'Surat Berharga',
            '401010302' => 'Kredit Yang Diberikan',
            '40101030201' => 'Kepada Bank Lain',
            '40101030202' => 'Kepada Pihak Ketiga Bukan Bank',
            '4010104' => 'D. Pendapatan Bunga EIR',
            '40102' => '2. Lainnya',
            '4010201' => 'A. Pendapatan Jasa Transaksi',
            '401020101' => '1. Pend. Fee PPOB (EDC) PLN,Jastel, Dll',
            '401020102' => '2. Pend. Fee Biller Mobile Banking',
            '401020103' => '3. Pend. Fee PBB',
            '401020104' => '4. Pend. Fee PDAM',
            '401020105' => '6. Pend. Fee Pajak Kendaraan',
            '401020106' => '7. Pend. Fee Lainnya',
            '4010202' => 'B. Keuntungan Penjualan Valas',
            '4010203' => 'C. Keuntungan Penjualan Surat Berharga',
            '4010204' => 'D. Pendapatan Dari Kredit Yang Dihapus Buku',
            '401020401' => '1. Pend. Angsuran PH - Pokok',
            '401020402' => '2. Pend. Angsuran PH - Bunga',
            '401020403' => '3. Pend. Denda Angsuran PH',
            '4010205' => 'E. Pendapatan Dari Pemulihan CKPN',
            '401020501' => '1. Pend. Pemulihan CKPN ABA',
            '401020502' => '2. Pend. Pemulihan CKPN Kredit',
            '4010206' => 'F. Lainnya',
            '401020601' => '1. Pendapatan Administrasi',
            '40102060101' => 'A. Pend. Adm. Pengelolaan Rekening',
            '40102060102' => 'B. Pend. Adm. Penutupan Rekening',
            '40102060103' => 'C. Pend. Adm. Ganti Buku',
            '40102060104' => 'D. Pend. Adm. Tabungan Pasif',
            '40102060105' => 'E. Pend. Pinalty Dari Deposito',
            '40102060106' => 'F. Pend. Pinalty Kredit Pelunasan Belum Jatuh Tem',
            '40102060107' => 'G. Pend. Denda Dari Kredit',
            '40102060108' => 'H. Pend. Denda Dari Kredit Yg Melebihi Jangka Wak',
            '40102060109' => 'I. Pend. Amortisasi Restrukturisasi',
            '401020602' => '2. Pendapatan Koreksi Penyusutan Inventaris',
            '401020603' => '3. Pendapatan Fee',
            '40102060301' => 'A. Pend. Fee Asuransi',
            '40102060302' => 'B. Pend. Fee Notaris',
            '40102060303' => 'C. Pend. Fee Lainnya',
            '401020604' => '4. Pendapatan Pembulatan Kas',
            '401020605' => '5. Pendapatan Lainnya',
            '402' => 'Pendapatan Non Operasional',
            '40201' => '1. Keuntungan Penjualan',
            '4020101' => 'A. Aset Tetap & Inventaris',
            '402010101' => '1. Tanah',
            '402010102' => '2. Bangunan',
            '402010103' => '3. Inventaris',
            '4020102' => 'B. AYDA',
            '402010201' => '1. Tanah',
            '402010202' => '2. Bangunan',
            '402010203' => '3. Kendaraan',
            '40202' => '2. Pemulihan Penurunan Nilai',
            '4020201' => 'A. Aset Tetap & Inventaris',
            '402020101' => '1. Tanah',
            '402020102' => '2. Bangunan',
            '402020103' => '3. Inventaris',
            '4020202' => 'B. AYDA',
            '402020201' => '1. Tanah',
            '402020202' => '2. Bangunan',
            '402020203' => '3. Kendaraan',
            '40203' => '3. Pendapatan Ganti Rugi Asuransi',
            '40204' => '4. Pend. Bunga Antar Kantor',
            '40299' => '5. Lainnya',
            '4029999' => 'Lainnya',
            

            // KODE 5: BIAYA/BEBAN
            // ==========================================
            '5' => 'Biaya',
            '501' => 'Beban Operasional',
            '50101' => '1. Beban Bunga',
            '5010101' => 'A. Beban Bunga Kontraktual',
            '501010101' => 'I. Tabungan',
            '50101010101' => 'Beban Bg Tabungan Wajib',
            '50101010102' => 'Beban Bg Tabungan Tamades',
            '50101010103' => 'Beban Bg Tabungan Tamades 1',
            '50101010104' => 'Beban Bg Tabungan Tamades 2',
            '50101010105' => 'Beban Bg Tabungan Tamades 3',
            '50101010106' => 'Beban Bg Tabungan Tamades 4',
            '50101010107' => 'Beban Bg Tabungan Tamades 5',
            '50101010108' => 'Beban Bg Tabungan Pelajar',
            '50101010109' => 'Beban Bg TAMADES',
            '50101010110' => 'Beban Bg TAWA',
            '50101010111' => 'Beban Bg TAWA PLUS',
            '50101010112' => 'Beban Bg Tabungan Kredit BKK',
            '50101010113' => 'Beban Bg Tabungan Mitra BKK',
            '50101010114' => 'Beban Bg Tabungan BKK Prioritas',
            '501010102' => 'II. Deposito Berjangka',
            '50101010201' => 'Beban Bg Deposito 1 Bulan',
            '50101010202' => 'Beban Bg Deposito 3 Bulan',
            '50101010203' => 'Beban Bg Deposito 6 Bulan',
            '50101010204' => 'Beban Bg Deposito 9 Bulan',
            '50101010205' => 'Beban Bg Deposito 12 Bulan',
            '501010103' => 'III. Simpanan Dari Bank Lain',
            '50101010301' => 'Beban Bg Tabungan ABP',
            '50101010302' => 'Beban Bg Deposito ABP',
            '501010104' => 'IV. Pinjaman Yang Diterima',
            '50101010401' => 'Dari Bank Indonesia',
            '50101010402' => 'Dari Bank Lain',
            '501010403' => 'Dari Pihak Ketiga Bukan Bank',
            '501010105' => 'V. Pinjaman Subordinasi',
            '50101010501' => 'A. Dari Bank Lain',
            '50101010502' => 'B. Dari Pihak Ketiga Bukan Bank',
            '501010106' => 'VI. Premi Penjaminan Simpanan (LPS)',
            '5010102' => 'B. Biaya Transaksi',
            '501010201' => 'Kepada Bank Lain',
            '501010202' => 'Kepada Pihak Ketiga Bukan Bank',
            '50101020201' => 'A. Cash Back',
            '50101020202' => 'B. Asuransi',
            '50101020203' => 'C. Lainnya',
            '5010103' => 'C. Koreksi Atas Pendapatan Bunga',
            '501010301' => '1. Tabungan',
            '501010302' => '2. Deposito',
            '501010303' => '3. Kredit Yang Diberikan',
            '501010399' => '4. Lainnya',
            '50102' => '2. Beban Kerugian Restrukturisasi Kredit',
            '50103' => '3. Beban CKPN',
            '5010301' => 'A. Surat Berharga',
            '5010302' => 'B. Penempatan Pada Bank Lain',
            '5010303' => 'C. Kredit Yang Diberikan',
            '501030301' => 'i. Kepada Bank Lain',
            '501030302' => 'ii. Kepada Pihak Ketiga Bukan Bank',
            '50104' => '4. Beban Pemasaran',
            '5010401' => 'A. Beban Inklusi dan Literasi Keuangan',
            '5010402' => 'B. Beban Pemberian Hadiah',
            '5010403' => 'C. Beban Iklan/Promosi',
            '5010404' => 'D. Beban Edukasi & Sosialisasi Produk',
            '5010499' => 'E. Sponsorship',
            '50105' => '5. Beban Penelitian Dan Pengembangan',
            '5010501' => 'A. Tekhnologi Informasi',
            '5010502' => 'B. Pengembangan Produk Baru',
            '5010503' => 'C. Pembukaan Kantor Kas / Cabang',
            '5010599' => 'D. Lainnya',
            '50106' => '6. Beban Administrasi Dan Umum',
            '5010601' => 'A. Beban Tenaga Kerja',
            '501060101' => 'I. Gaji Dan Upah',
            '50106010101' => 'A. Gaji Direksi',
            '50106010102' => 'B. Gaji Pokok',
            '50106010103' => 'C. Tunjangan Suami / Istri',
            '50106010104' => 'D. Tunjangan Anak',
            '50106010105' => 'E. Tunjangan Pangan',
            '50106010106' => 'F. Tunjangan Jabatan',
            '50106010107' => 'G. Tunjangan Operasional',
            '50106010108' => 'H. Tunjangan Kinerja',
            '50106010110' => 'J. Tunjangan Fungsional',
            '50106010111' => 'K. Tunjangan Masa Kerja',
            '50106010112' => 'L. Honor Tenaga Kontrak',
            '50106010113' => 'M. Honor Tenaga Outsourcing',
            '501060102' => 'II. Honorarium',
            '50106010201' => 'A. Honor Dewan Komisaris',
            '50106010202' => 'B. Honor Kontrak',
            '501060103' => 'III. Lainnya',
            '50106010301' => 'A. Uang Makan',
            '50106010302' => 'B. Uang Lembur',
            '50106010303' => 'C. Uang Transport',
            '50106010304' => 'D. Jasa Pengabdian Pengurus',
            '50106010305' => 'E. Jasa Pengabdian Pegawai',
            '50106010306' => 'F. Premi Jht',
            '50106010307' => 'G. Dplk',
            '50106010308' => 'H. Tunj. Bantuan Pendidikan',
            '50106010309' => 'I. THR',
            '50106010310' => 'J. Tunjangan Kinerja',
            '50106010311' => 'K. Tunjangan PPh 21',
            '50106010312' => 'L. Uang Pesangon',
            '50106010313' => 'M. Uang Penghargaan Masa Kerja',
            '50106010314' => 'N. Tenaga Harian Lepas',
            '5010602' => 'B. Beban Pendidikan Dan Pelatihan',
            '501060201' => '1. In House Training',
            '501060202' => '2. Eksternal Training',
            '501060203' => '3. Study Banding',
            '501060299' => '4. Lainnya',
            '5010603' => 'C. Beban Sewa',
            '501060301' => '1. Sewa Tanah Dan Gedung',
            '50106030101' => 'A. Kantor Pusat',
            '50106030102' => 'B. Kantor Cabang',
            '50106030103' => 'C. Kantor Kas',
            '501060302' => '2. Lainnya',
            '50106030201' => 'Sewa Aplikasi Core Banking',
            '50106030202' => 'Sewa Koneksi Jaringan',
            '50106030203' => 'Sewa Kendaraan',
            '50106030204' => 'Sewa Peralatan Kantor',
            '50106030205' => 'Sewa Pengganti Rumah Dinas',
            '50106030206' => 'Sewa Layanan Teknologi Informasi',
            '50106030299' => 'Sewa Lainnya',
            '5010604' => 'D. Beban Penyusutan / Penghapusan Atas Ati',
            '501060401' => '1. Penyusutan Gedung',
            '501060402' => '2. Penyusutan Inventaris',
            '50106040201' => 'A. Kendaraan',
            '50106040202' => 'B. Inventaris',
            '5010605' => 'E. Beban Amortisasi Aset Tidak Berwujud',
            '501060501' => '1. Core Banking',
            '501060502' => '2. Instalasi Listrik',
            '501060599' => '3. Lainnya',
            '5010606' => 'F. Beban Premi Asuransi',
            '501060601' => 'Asuransi Aset Tetap Dan Inventaris',
            '50106060101' => 'A. Asuransi Gedung',
            '50106060102' => 'B. Asuransi Kendaraan',
            '5010606010201' => 'Asuransi Kend. Roda 4',
            '5010606010202' => 'Asuransi Kend. Roda 2',
            '50106060103' => 'C. Asuransi Inventaris Lainya',
            '501060602' => 'Asuransi Tenaga Kerja',
            '50106060201' => 'A. Bpjs Ketenagakerjaan',
            '50106060202' => 'B. Bpjs Kesehatan',
            '501060603' => 'Asuransi Uang Kas',
            '50106060301' => 'A. Cash In Save',
            '50106060302' => 'B. Cash In Transit',
            '501060699' => 'Lainnya',
            '50106069903' => 'Asuransi Mesin Fotocopy',
            '50106069904' => 'Asuransi Purna Jabatan Pengurus',
            '50106069999' => 'Lainnya',
            '5010607' => 'G. Beban Pemeliharaan Dan Perbaikan',
            '501060701' => '1. Pemeliharaan Ti',
            '501060702' => '2. Pemeliharaan Gedung Kantor',
            '501060703' => '3. Pemeliharaan Perabot Kantor',
            '501060704' => '4. Pemeliharaan Kendaraan',
            '50106070401' => 'By Pemeliharaan Kend. Roda 4',
            '50106070402' => 'By Pemeliharaan Kend. Roda 2',
            '501060799' => '5. Lainnya',
            '5010608' => 'H. Beban Barang Dan Jasa',
            '501060801' => '1. Listrik',
            '501060802' => '2. Air',
            '501060803' => '3. Telepon',
            '501060804' => '4. Materai',
            '501060805' => '5. Alat Tulis Kantor',
            '501060806' => '6. Percetakan',
            '501060807' => '7. Koran & Majalah',
            '501060808' => '8. Gas',
            '501060809' => '9. Akomodasi Tamu',
            '501060810' => '10. Perjalanan Dinas',
            '50106081001' => 'Perj Dinas Komisaris',
            '50106081002' => 'Perj Dinas Direksi',
            '50106081003' => 'Perj Dinas Pegawai',
            '50106081004' => 'Biaya Akomodasi dan Penginapan',
            '501060811' => '11. Jasa Pihak Lain',
            '50106081101' => 'A. Kantor Akuntan Publik (KAP)',
            '50106081102' => 'B. Lawyer',
            '50106081103' => 'C. Konsultan',
            '50106081104' => 'D. Notaris',
            '50106081105' => 'E. Keamanan',
            '50106081106' => 'F. Pungutan OJK',
            '50106081107' => 'G. Security (Outsourcing)',
            '50106081199' => 'H. Lainnya',
            '501060812' => '12. Pakaian Dinas',
            '501060813' => '13. Bahan Bakar Minyak',
            '501060814' => '14. Rapat Rapat',
            '501060815' => '15. Rumah Tangga Kantor',
            '501060816' => '16. Voucher Handphone',
            '501060817' => '17. Catering / Makan',
            '501060818' => '18. Perlengkapan IT',
            '501060819' => '19. Perabot Kantor',
            '501060820' => '20. Ekspedisi/Kurir',
            '501060899' => '21. Lainnya',
            '5010609' => 'I. Beban Pajak',
            '501060901' => '1. Beban Pajak Kendaraan',
            '501060902' => '2. Beban Pajak Bumi Dan Bangunan',
            '501060903' => '3. Beban Ppn Barang (Pasal 22)',
            '501060904' => '4. Beban Ppn Jasa (Pasal 23)',
            '501060999' => '5. Beban Pajak Lainnya',
            '50107' => '7. Beban Lainnya',
            '5010701' => 'Kerugian Penjualan Valas',
            '5010702' => 'Kerugian Penjualan Surat Berharga',
            '5010703' => 'Kerugian Piutang Asuransi',
            '5010799' => 'Lainnya',
            '501079901' => '1. Representatif',
            '501079902' => '2. Biaya Penagihan Kredit',
            '501079903' => '3. Konsolidasi',
            '501079904' => '4. Bingkisan/ Cinderamata',
            '501079905' => '5. Fee Juru Bayar',
            '501079906' => '6. By Adm PPBL',
            '501079907' => '7. Pajak Atas Bunga PPBL',
            '501079908' => '8. By. Pengadilan dan Gugatan Sederhana',
            '501079909' => '9. Iuran OJK',
            '502' => 'Beban Non Operasional',
            '50201' => 'Kerugian Penjualan / Kehilangan',
            '5020101' => 'Aset Tetap Dan Inventaris',
            '502010101' => '1. Kendaraan',
            '502010102' => '2. Inventaris',
            '5020102' => 'Ayda',
            '50202' => 'Kerugian Penurunan Nilai',
            '5020201' => 'Aset Tetap Dan Inventaris',
            '502020101' => '1. Kendaraan',
            '502020102' => '2. Inventaris',
            '5020202' => 'Ayda',
            '50203' => 'Beban Bunga Antar Kantor',
            '50204' => 'Selisih Kurs',
            '50299' => 'Lainnya',
            '5029901' => 'A. Rekreasi',
            '5029902' => 'B. Olah Raga',
            '5029903' => 'C. Iuran Asosiasi',
            '5029904' => 'E. Sumbangan',
            '5029905' => 'F. Denda',
            '5029907' => 'H. Bingkisan-Bingkisan',
            '5029908' => 'I. Lainnya'
            
            // 🔥 PASTE FULL DICTIONARY DARI KODE 1 - 5 DI SINI YAA BROOKUUU!
        ];
    }
}