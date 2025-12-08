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
  END AS bobot
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



baki_debet per produk dan kolek

SELECT 
  k.kode_produk,
  kh.kolektibilitas,
  SUM(kh.baki_debet) AS baki_debet
FROM kredit k
JOIN kredit_history kh 
  ON kh.no_rekening = k.no_rekening
  AND kh.tanggal = '20241231'
WHERE kh.baki_debet > 0
GROUP BY k.kode_produk, kh.kolektibilitas
ORDER BY k.kode_produk, FIELD(kh.kolektibilitas,'L','DP','KL','D','M');


Data Setaun Realisasi dan angsuran
SELECT
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(106,120), pokok, 0 ))/1000) AS kmb,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(106,120), pokok, 0 ))/1000) AS angs_kmb,
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(109,121), pokok, 0 ))/1000) AS k_joglo,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(109,121), pokok, 0 ))/1000) AS angs_joglo,
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(103,122), pokok, 0 ))/1000) AS k_sinden,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(103,122), pokok, 0 ))/1000) AS angs_sinden,	
 
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(105,107,108,123), pokok, 0 ))/1000) AS k_korporasi,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(105,107,108,123), pokok, 0 ))/1000) AS angs_korporasi,		
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(104,124), pokok, 0 ))/1000) AS k_bumdes,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(104,124), pokok, 0 ))/1000) AS angs_bumdes,		
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND kode_produk in(101,125), pokok, 0 ))/1000) AS real_musiman,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND kode_produk in(101,125), pokok, 0 ))/1000) AS angs_musiman,
  round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(110,112,113,126), pokok, 0 ))/1000) AS k3,
  round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(110,112,113,126), pokok, 0 ))/1000) AS angs_k3,
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(111,127), pokok, 0 ))/1000) AS k_kpp,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(111,127), pokok, 0 ))/1000) AS angs_kpp,
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(102,119,128,129), pokok, 0 ))/1000) AS kub,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(102,119,128,129), pokok, 0 ))/1000) AS angs_kub,	
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(130), pokok, 0 ))/1000) AS k_kop,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(130), pokok, 0 ))/1000) AS angs_kop,
 
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(131), pokok, 0 ))/1000) AS k_agro,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(131), pokok, 0 ))/1000) AS angs_agro,
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(132), pokok, 0 ))/1000) AS k_bahari,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(132), pokok, 0 ))/1000) AS angs_bahari,
 
	round(sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20231001 AND  a.kode_produk in(133), pokok, 0 ))/1000) AS k_jogmit,
	round(sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20231001 AND  a.kode_produk in(133), pokok, 0 ))/1000) AS angs_jogmit
 
FROM
	kredit a,
	kretrans b
WHERE
	a.no_rekening = b.no_rekening
	AND tgl_trans <= 20231031