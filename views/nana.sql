select kredit_history.no_rekening AS nor,
(select nama_nasabah from nasabah inner join kredit on nasabah.nasabah_id = kredit.nasabah_id where kredit.no_rekening = nor) as nama_orang,
(select kode_group1 from kredit where no_rekening = nor) as kodekas,
kredit_history.kode_kantor,
kredit.tgl_realisasi,
kredit.jml_pinjaman,
kredit_history.baki_debet,
kredit_history.tunggakan_pokok,
kredit_history.tunggakan_bunga,
kredit_history.hari_menunggak_pokok,
	kredit_history.hari_menunggak_bunga,
	kredit_history.hari_menunggak,
kredit_history.tanggal AS tanggalawal,
(select pokok from kretrans where tgl_trans = '$tgl' and kode_kantor = '003' and my_kode_trans= '200' AND no_rekening = nor) AS angs_pokok,
(select bunga from kretrans where tgl_trans = '$tgl' and kode_kantor = '003' and my_kode_trans= '200' AND no_rekening = nor) AS angs_bunga,
(select tgl_trans from kretrans where tgl_trans = '$tgl' and kode_kantor = '003' and my_kode_trans= '200' AND no_rekening = nor) AS tgl_jatuh_tempo
  from kredit_history inner join kredit on kredit_history.no_rekening=kredit.no_rekening 
  
  where kredit_history.tanggal = '$tga' and kredit_history.kode_kantor = '003' and kredit_history.baki_debet > 0 AND kredit_history.my_kolek_number = '1' 
  HAVING angs_bunga > 0