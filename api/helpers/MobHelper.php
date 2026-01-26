<?php

class MobHelper {

    // --- Helper Label Bucket (Tidak Berubah) ---
    public static function getBucketLabel($dpd) {
        $dpd = (int)$dpd;
        if ($dpd === 0) return '0';
        if ($dpd >= 1 && $dpd <= 7)   return '1 - 7';
        if ($dpd >= 8 && $dpd <= 14)  return '8 - 14';
        if ($dpd >= 15 && $dpd <= 21) return '15 - 21';
        if ($dpd >= 22 && $dpd <= 30) return '22 - 30';
        if ($dpd >= 31 && $dpd <= 60) return '31 - 60';
        if ($dpd >= 61 && $dpd <= 90) return '61 - 90';
        return '> 90';
    }

    // --- List Urutan Bucket (Tidak Berubah) ---
    public static function getBucketOrder() {
        return ['0', '1 - 7', '8 - 14', '15 - 21', '22 - 30', '31 - 60', '61 - 90', '> 90'];
    }

    // --- Hitung MOB (Tidak Berubah) ---
    public static function calculateMob($tgl_realisasi, $tgl_posisi_data) {
        $start = new DateTime($tgl_realisasi);
        $end   = new DateTime($tgl_posisi_data);
        $months = (($end->format('Y') - $start->format('Y')) * 12) + ($end->format('m') - $start->format('m'));
        return $months <= 0 ? 1 : $months + 1; // MOB Minimal 1
    }

    /**
     * Proses Raw Data Menjadi Matriks MOB per Cabang
     * Output Structure:
     * [
     * '001' => [ ...data per bulan... ],
     * '002' => [ ...data per bulan... ]
     * ]
     */
    public static function processMobMatrix($rows, $harian_date) {
        $buckets = self::getBucketOrder();
        $grouped = [];

        foreach ($rows as $row) {
            $cabang = $row['kode_cabang'] ?? 'UNKNOWN';
            $bln_realisasi = date('Y-m', strtotime($row['tgl_realisasi']));
            
            $mob_ke = self::calculateMob($row['tgl_realisasi'], $harian_date);
            $bucket = self::getBucketLabel($row['hari_menunggak']);

            if (!isset($grouped[$cabang][$bln_realisasi])) {
                $grouped[$cabang][$bln_realisasi] = [
                    'kode_cabang'     => $cabang,
                    'bulan_realisasi' => $bln_realisasi,
                    'mob'             => $mob_ke,
                    'total_plafond'   => 0,
                    'buckets'         => []
                ];
                // Siapkan slot bucket
                foreach ($buckets as $b) {
                    $grouped[$cabang][$bln_realisasi]['buckets'][$b] = [
                        'os'  => 0, 
                        'noa' => 0,  // <-- Tambahan: Counter NOA
                        'pct' => 0
                    ];
                }
            }

            $item = &$grouped[$cabang][$bln_realisasi];
            $item['total_plafond'] += (float)$row['plafond'];

            // Masukkan ke bucket spesifik
            $item['buckets'][$bucket]['os'] += (float)$row['os'];
            $item['buckets'][$bucket]['noa']++; // <-- Tambah 1 NOA
        }

        // Hitung Persentase & Flattening
        $finalResult = [];
        ksort($grouped);

        foreach ($grouped as $kd_cabang => $dataBulan) {
            ksort($dataBulan);
            foreach ($dataBulan as &$data) {
                $pembagi = $data['total_plafond'] > 0 ? $data['total_plafond'] : 1;
                
                foreach ($data['buckets'] as $key => &$val) {
                    $val['pct'] = round(($val['os'] / $pembagi) * 100, 2);
                }
                $finalResult[] = $data;
            }
        }

        return $finalResult;
    }
}