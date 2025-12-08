Get Data Run OFF dan Realisasi (BY Cabang dalam 1 Bulan)

SELECT

  a.kode_kantor,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(106,120), b.pokok, 0))) AS kmb,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(106,120), b.pokok, 0))) AS angs_kmb,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(109,121), b.pokok, 0))) AS k_joglo,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(109,121), b.pokok, 0))) AS angs_joglo,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(103,122), b.pokok, 0))) AS k_sinden,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(103,122), b.pokok, 0))) AS angs_sinden,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(105,107,108,123), b.pokok, 0))) AS k_korporasi,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(105,107,108,123), b.pokok, 0))) AS angs_korporasi,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(104,124), b.pokok, 0))) AS k_bumdes,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(104,124), b.pokok, 0))) AS angs_bumdes,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(101,125), b.pokok, 0))) AS real_musiman,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(101,125), b.pokok, 0))) AS angs_musiman,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(110,112,113,126), b.pokok, 0))) AS k3,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(110,112,113,126), b.pokok, 0))) AS angs_k3,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(111,127), b.pokok, 0))) AS k_kpp,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(111,127), b.pokok, 0))) AS angs_kpp,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(102,119,128,129), b.pokok, 0))) AS kub,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(102,119,128,129), b.pokok, 0))) AS angs_kub,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(130), b.pokok, 0))) AS k_kop,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(130), b.pokok, 0))) AS angs_kop,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(131), b.pokok, 0))) AS k_agro,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(131), b.pokok, 0))) AS angs_agro,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(132), b.pokok, 0))) AS k_bahari,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(132), b.pokok, 0))) AS angs_bahari,



  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=1 AND a.kode_produk IN(133), b.pokok, 0))) AS k_jogmit,

  ROUND(SUM(IF(FLOOR(b.my_kode_trans/100)=3 AND a.kode_produk IN(133), b.pokok, 0))) AS angs_jogmit



FROM kredit a

JOIN kretrans b ON a.no_rekening = b.no_rekening

WHERE b.tgl_trans BETWEEN 20251101 AND 20251130

GROUP BY a.kode_kantor

ORDER BY a.kode_kantor;




nominatif kredit

SELECT
  k.kode_kantor AS kode_cabang,
  k.no_rekening,
  k.no_alternatif,
  k.nasabah_id,
  n.hp,
  n.tgllahir,
  n.phone_number,
  n.telpon,
  n.nama_nasabah,
  n.alamat,
  n.nama_suami_atau_istri,
  k.nama_pasangan,
  n.nama_ibu_kandung,
  n.tempat_bekerja,
  n.no_id,
  kk.deskripsi_kode_kelurahan,
  kc.deskripsi_kode_kecamatan,
  n.kodepos,
  n.gelar_h,
  n.gelar_pend,
  k.tgl_realisasi,
  k.jml_angsuran,
  k.tgl_jatuh_tempo,
  k.jml_pinjaman,
  k.jml_mark_up,
  k.type_kredit,
  k.kode_promo,
  k.suku_bunga_per_tahun,
  k.auto_debet,
  k.saldo_bunga_yad,
  k.norek_tabungan,
  k.no_spk,
  k.kode_group1,
  k.kode_group2,
  k.kode_group3,
  k.kode_group4,
  kh.saldo_provisi AS provisi_saldo,
  kh.baki_debet,
  kh.saldo_bank,
  kh.nilai_ckpn,
  kh.plafond,
  kh.tunggakan_pokok,
  kh.saldo_pyad,
  CASE
    WHEN kh.baki_debet > 0 THEN NULL
    ELSE (
      SELECT MAX(t.tgl_trans)
      FROM kretrans t
      WHERE t.no_rekening = k.no_rekening
        AND t.tgl_trans <= '20250531'
        AND FLOOR(t.my_kode_trans/100) = 3
        AND t.pokok > 0
    )
  END AS tgl_pelunasan,
  CASE
    WHEN kh.baki_debet > 0 THEN kh.tunggakan_bunga
    ELSE (
      SELECT
        COALESCE(SUM(CASE WHEN FLOOR(t.my_kode_trans/100)=2 THEN t.bunga ELSE 0 END),0)
      - COALESCE(SUM(CASE WHEN FLOOR(t.my_kode_trans/100)=3 THEN t.bunga ELSE 0 END),0)
      FROM kretrans t
      WHERE t.no_rekening = k.no_rekening
        AND t.tgl_trans <= (
          SELECT MAX(t2.tgl_trans)
          FROM kretrans t2
          WHERE t2.no_rekening = k.no_rekening
            AND t2.tgl_trans <= '20250531'
            AND FLOOR(t2.my_kode_trans/100)=3
            AND t2.pokok > 0
        )
    )
  END AS tunggakan_bunga,
  kh.tgl_mulai_nunggak,
  kh.hari_menunggak_pokok,
  kh.hari_menunggak_bunga,
  kh.hari_menunggak,
  kh.hari_menunggak_jt,
  kh.kolektibilitas,
  kh.my_kolek_number,
  kh.saldo_debius,
  kh.nilai_ppapwd,
  (
    SELECT COUNT(a.no_rekening) + 1
    FROM kredit a
    WHERE a.nasabah_id = k.nasabah_id
      AND a.no_rekening <> k.no_rekening
      AND a.tgl_realisasi < k.tgl_realisasi
  ) AS pinj_ke,
  k.kode_produk,
  kh.jenis_agunan,
  kh.ikatan_agunan,
  kh.nilai_taksasi,
  k.slik_kode_gol_penjamin,
  k.slik_kode_jenis_penggunaan,
  k.bi_sifat,
  k.kode_sumber_pelunasan,
  k.bi_gol_debitur,
  k.kode_keterkaitan,
  k.slik_kode_sektor_ekonomi,
  CASE kh.kolektibilitas
    WHEN 'L' THEN 1
    WHEN 'DP' THEN 2
    WHEN 'KL' THEN 3
    WHEN 'D' THEN 4
    WHEN 'M' THEN 5
    ELSE NULL
  END AS bobot,
  '2025-05-31' AS created
FROM kredit k
JOIN nasabah n ON n.nasabah_id = k.nasabah_id
JOIN kredit_history kh
  ON kh.no_rekening = k.no_rekening
 AND kh.tanggal = '20250531'
LEFT JOIN css_kode_dati kd
  ON kd.kode_provinsi = n.propinsi
 AND kd.kode_dati     = n.kota_kab
LEFT JOIN css_kode_kecamatan kc
  ON kc.kode_provinsi = n.propinsi
 AND kc.kode_dati     = n.kota_kab
 AND kc.kode_kecamatan= n.kecamatan
LEFT JOIN css_kode_kelurahan kk
  ON kk.kode_provinsi = n.propinsi
 AND kk.kode_dati     = n.kota_kab
 AND kk.kode_kecamatan= n.kecamatan
 AND kk.kode_kelurahan= n.desa
WHERE kh.baki_debet > 0
ORDER BY k.no_rekening;

PH

SELECT
  k.kode_kantor                                    AS kode_kantor,
  k.no_rekening                                    AS no_rekening,
  n.nama_nasabah                                   AS nama_nasabah,
  t.tgl_trans                                      AS tanggal_transaksi,
  IF(t.my_kode_trans = 900, t.pokok, 0)            AS pokok,
  IF(t.my_kode_trans = 900, t.bunga, 0)            AS bunga,
  (IF(t.my_kode_trans = 900, t.pokok, 0)
   + IF(t.my_kode_trans = 900, t.bunga, 0))        AS total
FROM kredit k
JOIN kretrans t ON k.no_rekening = t.no_rekening
JOIN nasabah n ON k.nasabah_id = n.nasabah_id
WHERE
  t.tgl_trans BETWEEN '2025-11-01' AND '2025-11-05'
  AND t.my_kode_trans = 900
ORDER BY t.tgl_trans, k.no_rekening;


SELECT
  k.kode_kantor AS kode_cabang,
  k.no_rekening,
  k.no_alternatif,
  k.nasabah_id,
  n.hp,
  n.tgllahir,
  n.phone_number,
  n.telpon,
  n.nama_nasabah,
  n.alamat,
  n.nama_suami_atau_istri,
  k.nama_pasangan,
  n.nama_ibu_kandung,
  n.tempat_bekerja,
  n.no_id,
  kk.deskripsi_kode_kelurahan,
  kc.deskripsi_kode_kecamatan,
  n.kodepos,
  n.gelar_h,
  n.gelar_pend,
  k.tgl_realisasi,
  k.jml_angsuran,
  k.tgl_jatuh_tempo,
  k.jml_pinjaman,
  k.jml_mark_up,
  k.type_kredit,
  k.kode_promo,
  k.suku_bunga_per_tahun,
  k.auto_debet,
  k.saldo_bunga_yad,
  k.norek_tabungan,
  k.no_spk,
  k.kode_group1,
  k.kode_group2,
  k.kode_group3,
  k.kode_group4,
  kh.saldo_provisi AS provisi_saldo,
  kh.baki_debet,
  kh.saldo_bank,
  kh.nilai_ckpn,
  kh.plafond,
  kh.tunggakan_pokok,
  kh.saldo_pyad,
  CASE
    WHEN kh.baki_debet > 0 THEN NULL
    ELSE (
      SELECT MAX(t.tgl_trans)
      FROM kretrans t
      WHERE t.no_rekening = k.no_rekening
        AND t.tgl_trans <= '20241231'
        AND FLOOR(t.my_kode_trans/100) = 3
        AND t.pokok > 0
    )
  END AS tgl_pelunasan,
  CASE
    WHEN kh.baki_debet > 0 THEN kh.tunggakan_bunga
    ELSE (
      SELECT
        COALESCE(SUM(CASE WHEN FLOOR(t.my_kode_trans/100)=2 THEN t.bunga ELSE 0 END),0)
      - COALESCE(SUM(CASE WHEN FLOOR(t.my_kode_trans/100)=3 THEN t.bunga ELSE 0 END),0)
      FROM kretrans t
      WHERE t.no_rekening = k.no_rekening
        AND t.tgl_trans <= (
          SELECT MAX(t2.tgl_trans)
          FROM kretrans t2
          WHERE t2.no_rekening = k.no_rekening
            AND t2.tgl_trans <= '20241231'
            AND FLOOR(t2.my_kode_trans/100)=3
            AND t2.pokok > 0
        )
    )
  END AS tunggakan_bunga,
  kh.tgl_mulai_nunggak,
  kh.hari_menunggak_pokok,
  kh.hari_menunggak_bunga,
  kh.hari_menunggak,
  kh.hari_menunggak_jt,
  kh.kolektibilitas,
  kh.my_kolek_number,
  kh.saldo_debius,
  kh.nilai_ppapwd,
  (
    SELECT COUNT(a.no_rekening) + 1
    FROM kredit a
    WHERE a.nasabah_id = k.nasabah_id
      AND a.no_rekening <> k.no_rekening
      AND a.tgl_realisasi < k.tgl_realisasi
  ) AS pinj_ke,
  k.kode_produk,
  kh.jenis_agunan,
  kh.ikatan_agunan,
  kh.nilai_taksasi,
  k.slik_kode_gol_penjamin,
  k.slik_kode_jenis_penggunaan,
  k.bi_sifat,
  k.kode_sumber_pelunasan,
  k.bi_gol_debitur,
  k.kode_keterkaitan,
  k.slik_kode_sektor_ekonomi,
  CASE kh.kolektibilitas
    WHEN 'L' THEN 1
    WHEN 'DP' THEN 2
    WHEN 'KL' THEN 3
    WHEN 'D' THEN 4
    WHEN 'M' THEN 5
    ELSE NULL
  END AS bobot,
  DATE '2024-12-31' AS created
FROM kredit k
JOIN nasabah n ON n.nasabah_id = k.nasabah_id
JOIN kredit_history kh
  ON kh.no_rekening = k.no_rekening
 AND kh.tanggal = '20241231'
LEFT JOIN css_kode_dati kd
  ON kd.kode_provinsi = n.propinsi
 AND kd.kode_dati     = n.kota_kab
LEFT JOIN css_kode_kecamatan kc
  ON kc.kode_provinsi = n.propinsi
 AND kc.kode_dati     = n.kota_kab
 AND kc.kode_kecamatan= n.kecamatan
LEFT JOIN css_kode_kelurahan kk
  ON kk.kode_provinsi = n.propinsi
 AND kk.kode_dati     = n.kota_kab
 AND kk.kode_kecamatan= n.kecamatan
 AND kk.kode_kelurahan= n.desa
WHERE kh.baki_debet > 0
ORDER BY k.no_rekening;