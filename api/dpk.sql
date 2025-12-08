transaksi kredit

SELECT
  k.kode_kantor,
  k.no_rekening,
  k.no_alternatif,
  k.status,
  n.nama_nasabah,
  n.alamat,
  t.tgl_trans,
  t.kode_trans,
  t.kuitansi,
  k.kode_produk,
  n.gelar_h,
  n.gelar_pend,
  t.userid,
  k.kode_integrasi,
  k.kode_group1,
  k.kode_produk AS kode_produk_1,
  k.slik_kode_jenis_penggunaan,
  t.kolek,
  t.no_rekening_tabungan,
  k.kode_group2,

 
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
  t.tgl_trans BETWEEN '2025-11-01' AND '2025-11-03'
  AND FIND_IN_SET(t.kode_trans,
      '100,110,120,130,300,310,320,303,304,315,316,340,343,350') > 0
ORDER BY t.kretrans_id;


transaksi ph

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
  t.tgl_trans BETWEEN '2025-11-01' AND '2025-11-03'
  AND t.my_kode_trans = 900
ORDER BY t.tgl_trans, k.no_rekening;


nom Restruk

SELECT
  b.kode_kantor,
  a.no_rekening,
  b.no_alternatif,
  c.nama_nasabah,
  c.alamat,
  a.tgl_trans,
  a.kode_trans,
  a.jml_restruk,
  a.baki_debet,
  a.cad_restruk,
  a.tunggakan_bunga,
  a.tgl_jt_lama,
  a.tgl_jt_baru,
  a.periode_angsuran_lama,
  a.periode_angsuran_baru,
  a.suku_bunga_lama,
  a.suku_bunga_baru,
  a.kolektibilitas,
  a.jkw_lama,
  a.jkw_baru,
  a.grace_period_pokok,
  a.grace_period_bunga,
  IF(a.kapitalisasi = 1, 'YA', 'TIDAK') AS kapitalisasi,
  a.keterangan,
  a.type_kredit_lama,
  a.type_kredit_baru
FROM kretrans_restruk a
JOIN kredit  b ON a.no_rekening = b.no_rekening
JOIN nasabah c ON b.nasabah_id   = c.nasabah_id
WHERE a.tgl_trans BETWEEN '2025-10-01' AND '2025-10-24'
ORDER BY a.tgl_trans, a.no_rekening;









ambil data ph dengan tabungannya
SELECT
  k.kode_kantor AS "KODE KANTOR",
  (SELECT nama_kantor FROM app_kode_kantor WHERE kode_kantor = k.kode_kantor) AS "NAMA KANTOR",
  k.no_rekening AS "NO REKENING KREDIT",
  k.nasabah_id AS "CIF",
  k.no_alternatif AS "NO ALTERNATIF",
  n.nama_nasabah AS "NAMA NASABAH",

  -- tanggal hapus buku (transaksi debius)
  (SELECT MAX(tgl_trans)
   FROM kretrans
   WHERE FLOOR(my_kode_trans / 100) = 8
     AND no_rekening = k.no_rekening) AS "TGL DEBIUS",

  k.jml_pinjaman AS "PLAFOND",

  -- nilai debius
  COALESCE((
    SELECT SUM(pokok)
    FROM kretrans
    WHERE FLOOR(my_kode_trans / 100) = 8
      AND no_rekening = k.no_rekening
  ), 0) AS "JUMLAH DEBIUS",

  -- nilai ditagih setelah hapus buku
  COALESCE((
    SELECT SUM(pokok)
    FROM kretrans
    WHERE FLOOR(my_kode_trans / 100) = 9
      AND no_rekening = k.no_rekening
      AND tgl_trans <= 20251031
  ), 0) AS "JUMLAH DITAGIH",

  -- saldo debius snapshot 2025-09-30
  COALESCE((
    SELECT saldo_debius
    FROM kredit_history
    WHERE no_rekening = k.no_rekening
      AND tanggal = 20251031
  ), 0) AS "SALDO DEBIUS",

  -- data tabungan per nasabah (produk 212, snapshot 2025-10-27)
  t.no_rekening AS "NO REK TABUNGAN",
  t.kode_produk AS "KODE PRODUK TABUNGAN",
  th.saldo_akhir AS "SALDO AKHIR TAB (27-10-2025)",
  

FROM kredit k
JOIN nasabah n
  ON n.nasabah_id = k.nasabah_id

-- 🔹 ambil tabungan produk 212 milik debitur yang sama (per nasabah_id)
LEFT JOIN tabung t
  ON t.nasabah_id = n.nasabah_id
  AND t.kode_produk = 212
LEFT JOIN tabung_history th
  ON th.no_rekening = t.no_rekening
  AND th.tanggal = 20251031

WHERE k.no_rekening IN (
  SELECT DISTINCT no_rekening
  FROM kretrans
  WHERE FLOOR(my_kode_trans / 100) = 8
    AND tgl_trans <= 20251031
)
AND k.kode_kantor = 023
ORDER BY k.no_rekening
LIMIT 100;


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
        AND t.tgl_trans <= '20251105'
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
            AND t2.tgl_trans <= '20251105'
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
  '2025-11-5' AS created
FROM kredit k
JOIN nasabah n ON n.nasabah_id = k.nasabah_id
JOIN kredit_history kh
  ON kh.no_rekening = k.no_rekening
 AND kh.tanggal = '20251105'
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