report51!@#

Update (3)
1.tabel : kre_agunan
select * from kre_agunan where tgl_register > '20251001' 
2.tabel : kredit
select * from kredit where tgl_realisasi > '20251001' 
3.tabel : nasabah
select * from nasabah where tgl_register > '20251001' 
4.tabel : kre_agunan_relasi
select * from kre_agunan_relasi where id_relasi > '578548' 

1.select * from kretrans where  month(tgl_trans) = '02' and year(tgl_trans) = '2026' and kode_kantor between '001' and '018'
2.select * from kretrans where  month(tgl_trans) = '12' and year(tgl_trans) = '2026' and kode_kantor between '019' and '028'


-- tab
SELECT
	tabung.no_rekening "no_rekening",
	nama_nasabah,
	tabung_history.saldo_akhir AS "saldo_akhir"
FROM
	tabung_history,
	tabung,
	nasabah 
WHERE
	tabung.no_rekening = tabung_history.no_rekening 
	AND tabung.nasabah_id = nasabah.nasabah_id 
	AND tabung_history.tanggal = "20260203"
	AND tabung_history.saldo_akhir >0
	AND tabung.kode_produk=212


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
        AND t.tgl_trans <= '20260201'
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
            AND t2.tgl_trans <= '20260201'
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
  tanggal AS created
FROM kredit k
JOIN nasabah n ON n.nasabah_id = k.nasabah_id
JOIN kredit_history kh
  ON kh.no_rekening = k.no_rekening
 AND kh.tanggal = '20260201'
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

