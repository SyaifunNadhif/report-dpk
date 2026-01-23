<?php

class MobHelper {

    /**
     * Menentukan label bucket berdasarkan hari menunggak
     */
    public static function getBucketLabel($dpd) {
        $dpd = (int)$dpd;

        if ($dpd === 0) return '0';
        if ($dpd >= 1 && $dpd <= 7)   return '1 - 7';
        if ($dpd >= 8 && $dpd <= 14)  return '8 - 14';
        if ($dpd >= 15 && $dpd <= 21) return '15 - 21';
        if ($dpd >= 22 && $dpd <= 30) return '22 - 30';
        if ($dpd >= 31 && $dpd <= 60) return '31 - 60';
        if ($dpd >= 61 && $dpd <= 90) return '61 - 90';
        
        return '> 90'; // Default jika ada yang lebih dari 90
    }

    /**
     * List urutan bucket agar rapi saat diloop
     */
    public static function getBucketOrder() {
        return ['0', '1 - 7', '8 - 14', '15 - 21', '22 - 30', '31 - 60', '61 - 90', '> 90'];
    }

    /**
     * Hitung MOB (Umur bulan sejak realisasi sampai data ditarik)
     */
    public static function calculateMob($tgl_realisasi, $tgl_posisi_data) {
        $start = new DateTime($tgl_realisasi);
        $end   = new DateTime($tgl_posisi_data);
        
        // Logika: (Tahun * 12 + Bulan) - (Tahun * 12 + Bulan)
        // Ditambah 1 jika ingin hitungan inklusif (Des ke Des = MOB 1)
        $months = (($end->format('Y') - $start->format('Y')) * 12) + ($end->format('m') - $start->format('m'));
        return $months <= 0 ? 1 : $months + 1; // Minimal MOB 1
    }

    /**
     * Proses Raw Data dari Database menjadi Matriks MOB
     * @param array $rows Data hasil query (wajib ada field: tgl_realisasi, plafond, os, hari_menunggak)
     * @param string $harian_date Tanggal posisi data (Jan 2026)
     */
    public static function processMobMatrix($rows, $harian_date) {
        $buckets = self::getBucketOrder();
        $result  = [];

        foreach ($rows as $row) {
            // 1. Tentukan Group Bulan Realisasi (YYYY-MM)
            $bln_realisasi = date('Y-m', strtotime($row['tgl_realisasi']));
            
            // 2. Hitung MOB ke berapa saat ini
            $mob_ke = self::calculateMob($row['tgl_realisasi'], $harian_date);

            // 3. Tentukan Bucket saat ini
            $bucket = self::getBucketLabel($row['hari_menunggak']);

            // 4. Inisialisasi Array jika belum ada
            if (!isset($result[$bln_realisasi])) {
                $result[$bln_realisasi] = [
                    'bulan_realisasi' => $bln_realisasi,
                    'mob'             => $mob_ke,
                    'total_noa'       => 0,
                    'total_plafond'   => 0,
                    'total_os'        => 0,
                    'buckets'         => []
                ];
                // Siapkan slot bucket biar rapi (default 0)
                foreach ($buckets as $b) {
                    $result[$bln_realisasi]['buckets'][$b] = ['os' => 0, 'pct' => 0];
                }
            }

            // 5. Agregasi Data
            $result[$bln_realisasi]['total_noa']++;
            $result[$bln_realisasi]['total_plafond'] += (float)$row['plafond'];
            $result[$bln_realisasi]['total_os']      += (float)$row['os']; // Baki debet saat ini

            // Masukkan ke bucket spesifik
            $result[$bln_realisasi]['buckets'][$bucket]['os'] += (float)$row['os'];
        }

        // 6. Hitung Persentase per Bucket (OS Bucket / Total Plafond atau Total OS Realisasi)
        // Biasanya MOB diukur: OS Bucket / Total Plafond Awal (Booking Amount)
        foreach ($result as &$data) {
            $pembagi = $data['total_plafond'] > 0 ? $data['total_plafond'] : 1;
            
            foreach ($data['buckets'] as $key => &$val) {
                $val['pct'] = round(($val['os'] / $pembagi) * 100, 2);
            }
        }

        // Urutkan berdasarkan bulan realisasi (Ascending)
        ksort($result);

        return array_values($result);
    }
}