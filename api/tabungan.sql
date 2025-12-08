SELECT
  t.kode_kantor AS "KODE KANTOR",
  ak.nama_kantor AS "NAMA KANTOR",
  g1.deskripsi_group1 AS "NAMA KANKAS",
  g2.deskripsi_group2 AS "NAMA AO",
  jd.deskripsi_jenis_debitur AS "DESKRIPSI JENIS DEBITUR",
  t.nasabah_id AS "CIF",
  t.no_rekening AS "NO REKENING",
  n.nama_nasabah AS "NAMA NASABAH",
  n.alamat AS "ALAMAT",
  p.deskripsi_produk AS "PRODUK",
  t.kode_jenis AS "JENIS TABUNGAN",
  t.kode_keterkaitan AS "HUBUNGAN DG BANK",
  pk.deskripsi_pemilik AS "DESKRIPSI BI",
  sg.deskripsi_gol_deb AS "DESKRIPSI SLIK",
  t.suku_bunga AS "SK BUNGA",
  th.saldo_akhir AS "SALDO",
  prov.nama_provinsi AS "PROVINSI",
  dati.deskripsi_kode_dati AS "DESK KAB",
  kec.deskripsi_kode_kecamatan AS "DESK KEC",
  kel.deskripsi_kode_kelurahan AS "DESK DESA",
  n.kodepos AS "KODE POS"
FROM tabung_history th
JOIN tabung t ON t.no_rekening = th.no_rekening
JOIN nasabah n ON t.nasabah_id = n.nasabah_id
LEFT JOIN app_kode_kantor ak ON ak.kode_kantor = th.kode_kantor
LEFT JOIN tab_kode_group1 g1 ON g1.kode_group1 = t.kode_group1
LEFT JOIN tab_kode_group2 g2 ON g2.kode_group2 = t.kode_group2
LEFT JOIN css_jenis_debitur jd ON jd.kode_jenis_debitur = n.jenis_debitur
LEFT JOIN tab_produk p ON p.kode_produk = t.kode_produk
LEFT JOIN tab_kode_pemilik pk ON pk.kode_pemilik = t.kode_bi_pemilik
LEFT JOIN slik_ref21_gol_deb sg ON sg.kode_gol_deb = n.slik_kode_gol_debitur
LEFT JOIN css_kode_propvinsi prov ON prov.kode_provinsi = n.PROPINSI
LEFT JOIN css_kode_dati dati ON dati.kode_provinsi = n.PROPINSI AND dati.kode_dati = n.KOTA_KAB
LEFT JOIN css_kode_kecamatan kec ON kec.kode_provinsi = n.PROPINSI AND kec.kode_dati = n.KOTA_KAB AND kec.kode_kecamatan = n.KECAMATAN
LEFT JOIN css_kode_kelurahan kel ON kel.kode_provinsi = n.PROPINSI AND kel.kode_dati = n.KOTA_KAB AND kel.kode_kecamatan = n.KECAMATAN AND kel.kode_kelurahan = n.DESA
WHERE th.tanggal = 20251125
  AND th.saldo_akhir > 0
  AND t.kode_kantor = '001'   -- kalau mau batasi ke kantor 001, aktifkan baris ini
ORDER BY t.kode_kantor;











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
        AND t.tgl_trans <= '20251130'
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
            AND t2.tgl_trans <= '20251130'
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
DATE_FORMAT(STR_TO_DATE('20251130','%Y%m%d'), '%d/%m/%Y') AS created
FROM kredit k
JOIN nasabah n ON n.nasabah_id = k.nasabah_id
JOIN kredit_history kh
  ON kh.no_rekening = k.no_rekening
 AND kh.tanggal = '20251130'
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

