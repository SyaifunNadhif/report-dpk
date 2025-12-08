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

WHERE b.tgl_trans BETWEEN 20250101 AND 20250131

GROUP BY a.kode_kantor

ORDER BY a.kode_kantor;