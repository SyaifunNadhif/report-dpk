SELECT
  k.kode_kantor AS kode_cabang,
  k.no_rekening,
  n.nama_nasabah,
  n.alamat,
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
        AND t.tgl_trans <= '20260131'
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
            AND t2.tgl_trans <= '20260131'
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
  CASE kh.kolektibilitas
    WHEN 'L' THEN 1
    WHEN 'DP' THEN 2
    WHEN 'KL' THEN 3
    WHEN 'D' THEN 4
    WHEN 'M' THEN 5
    ELSE NULL
  END AS bobot,
  tanggal AS created
FROM kredit k
JOIN nasabah n ON n.nasabah_id = k.nasabah_id
JOIN kredit_history kh
  ON kh.no_rekening = k.no_rekening
 AND kh.tanggal = '20260131'
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
  t.tgl_trans BETWEEN '2026-01-01' AND '2026-01-31'
  AND t.my_kode_trans = 900
ORDER BY t.tgl_trans, k.no_rekening;

Transaksi kredit

SELECT
  k.kode_kantor,
  k.no_rekening,
  k.no_alternatif,
  k.status,
  n.nama_nasabah,
  t.tgl_trans,
  t.kode_trans,
  

 
  IF(t.my_kode_trans = 100, t.pokok,        0) AS realisasi_pokok,
  IF(t.my_kode_trans = 100, t.provisi,      0) AS realisasi_provisi,
  IF(t.my_kode_trans = 100, t.materai,      0) AS realisasi_materai,
  IF(t.my_kode_trans = 100, t.premi,        0) AS realisasi_premi,
  IF(t.my_kode_trans = 100, t.notariel,     0) AS realisasi_notariel,
  IF(t.my_kode_trans = 100, t.adm_lainnya,  0) AS realisasi_adm_lainnya,
  IF(t.my_kode_trans = 100, t.tabungan,     0) AS realisasi_tabungan,

  IF(t.my_kode_trans = 300, t.pokok,        0) AS angsuran_pokok,
  IF(t.my_kode_trans = 300, t.bunga,        0) AS angsuran_bunga,
  IF(t.my_kode_trans = 300, t.bunga_yad,    0) AS angsuran_bunga_yad,
  IF(t.my_kode_trans = 300, t.denda,        0) AS angsuran_denda,
  IF(t.my_kode_trans = 300, t.disc_bunga,   0) AS diskon_bunga,
  IF(t.my_kode_trans = 300, t.adm_lainnya,  0) AS angsuran_adm_lainnya,

  t.keterangan
FROM kredit   k
JOIN kretrans t ON k.no_rekening = t.no_rekening
JOIN nasabah  n ON k.nasabah_id  = n.nasabah_id
WHERE
  t.tgl_trans BETWEEN '2026-01-01' AND '2026-01-31'
  AND FIND_IN_SET(t.kode_trans,
      '100,110,120,130,300,310,320,303,304,315,316,340,343,350') > 0
ORDER BY t.kretrans_id;