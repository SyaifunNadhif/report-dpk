SELECT
a.nasabah_id AS "CIF",		
a.no_rekening AS "NO REKENING",
c.nama_nasabah AS "NAMA NASABAH",
a.kode_produk AS "PRODUK",
a.jml_pinjaman AS "PLAFOND",
a.tgl_realisasi AS "TGL REALISASI",
a.tgl_jatuh_tempo AS "TGL JT",
d.tgl_trans AS "TGL PELUNASAN",
d.pokok AS "POKOK",
d.bunga AS "BUNGA",
b.kolektibilitas AS "KOLEKTIBILITAS SAAT PELUNASAN",
d.keterangan AS "KETERANGAN"

FROM 
kredit a
JOIN 
nasabah c ON a.nasabah_id = c.nasabah_id
JOIN 
kretrans d ON a.no_rekening = d.no_rekening
LEFT JOIN 
kredit_history b ON a.no_rekening = b.no_rekening
AND b.tanggal = (SELECT MAX(h.tanggal)
		FROM kredit_history h
		WHERE h.no_rekening = a.no_rekening
		AND h.tanggal <= d.tgl_trans)

WHERE 
	d.keterangan LIKE '%Pelunasan%'
    AND d.keterangan NOT LIKE '%Reversal%'
	AND d.tgl_trans BETWEEN '2025-08-01' AND '2025-09-30'
	AND d.tgl_trans = (
		SELECT MAX(x.tgl_trans)
		FROM kretrans x
		WHERE x.no_rekening = a.no_rekening
		AND x.keterangan LIKE '%Pelunasan%'
        AND x.keterangan NOT LIKE '%Reversal%'
		AND x.tgl_trans BETWEEN '2025-08-01' AND '2025-09-30')
	
	AND a.no_rekening NOT IN (
		SELECT DISTINCT r.no_rekening
		FROM kretrans_restruk r
		WHERE r.kode_trans IN (60, 62))

order by d.tgl_trans,a.no_rekening


SELECT
  a.nasabah_id        AS CIF,
  a.no_rekening       AS NO_REKENING,
  c.nama_nasabah      AS NAMA_NASABAH,
  a.kode_produk       AS PRODUK,
  a.jml_pinjaman      AS PLAFOND,
  a.tgl_realisasi     AS TGL_REALISASI,
  a.tgl_jatuh_tempo   AS TGL_JT,
  d.tgl_trans         AS TGL_PELUNASAN,
  d.pokok             AS POKOK,
  d.bunga             AS BUNGA,
  b.kolektibilitas    AS KOLEKTIBILITAS_SAAT_PELUNASAN,
  d.keterangan        AS KETERANGAN
FROM kredit a
JOIN nasabah c
  ON a.nasabah_id = c.nasabah_id

/* Ambil tgl pelunasan TERAKHIR per rekening dalam rentang tanggal, buang yang ada “Reversal” */
JOIN (
  SELECT
    kt.no_rekening,
    MAX(kt.tgl_trans) AS tgl_pelunasan_terakhir
  FROM kretrans kt
  WHERE
    LOWER(kt.keterangan) LIKE '%pelunasan%'
    AND LOWER(kt.keterangan) NOT LIKE '%reversal%'
    AND kt.tgl_trans BETWEEN '2025-08-01' AND '2025-09-30'
  GROUP BY kt.no_rekening
) p ON p.no_rekening = a.no_rekening


JOIN kretrans d
  ON d.no_rekening = a.no_rekening
  AND d.tgl_trans  = p.tgl_pelunasan_terakhir
  AND LOWER(d.keterangan) LIKE '%pelunasan%'
  AND LOWER(d.keterangan) NOT LIKE '%reversal%'


LEFT JOIN kredit_history b
  ON b.no_rekening = a.no_rekening
  AND b.tanggal = (
    SELECT MAX(h.tanggal)
    FROM kredit_history h
    WHERE h.no_rekening = a.no_rekening
      AND h.tanggal <= d.tgl_trans
  )


WHERE NOT EXISTS (
  SELECT 1
  FROM kretrans_restruk r
  WHERE r.no_rekening = a.no_rekening
    AND r.kode_trans IN (60, 62)
)
ORDER BY d.tgl_trans, a.no_rekening;



SELECT
	kredit.kode_produk,
	( SELECT deskripsi_produk FROM kre_produk WHERE kode_produk = kredit.kode_produk ) deskripsi,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250101 AND tgl_trans <= 20250131, pokok, 0 ))/1000 AS realisasi_01,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250101 AND tgl_trans <= 20250131, pokok, 0 ))/1000 AS angsuran_01,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250201 AND tgl_trans <= 20250228, pokok, 0 ))/1000 AS realisasi_02,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250201 AND tgl_trans <= 20250228, pokok, 0 ))/1000 AS angsuran_02,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250301 AND tgl_trans <= 20250331, pokok, 0 ))/1000 AS realisasi_03,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250301 AND tgl_trans <= 20250331, pokok, 0 ))/1000 AS angsuran_03,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250401 AND tgl_trans <= 20250430, pokok, 0 ))/1000 AS realisasi_04,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250401 AND tgl_trans <= 20250430, pokok, 0 ))/1000 AS angsuran_04,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250501 AND tgl_trans <= 20250531, pokok, 0 ))/1000 AS realisasi_05,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250501 AND tgl_trans <= 20250531, pokok, 0 ))/1000 AS angsuran_05,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250601 AND tgl_trans <= 20250630, pokok, 0 ))/1000 AS realisasi_06,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250601 AND tgl_trans <= 20250630, pokok, 0 ))/1000 AS angsuran_06,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250701 AND tgl_trans <= 20250731, pokok, 0 ))/1000 AS realisasi_07,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250701 AND tgl_trans <= 20250731, pokok, 0 ))/1000 AS angsuran_07,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250801 AND tgl_trans <= 20250831, pokok, 0 ))/1000 AS realisasi_08,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250801 AND tgl_trans <= 20250831, pokok, 0 ))/1000 AS angsuran_08,	
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250901 AND tgl_trans <= 20250930, pokok, 0 ))/1000 AS realisasi_09,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250901 AND tgl_trans <= 20250930, pokok, 0 ))/1000 AS angsuran_09,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20251001 AND tgl_trans <= 20251031, pokok, 0 ))/1000 AS realisasi_10,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20251001 AND tgl_trans <= 20251031, pokok, 0 ))/1000 AS angsuran_10,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20251101 AND tgl_trans <= 20251130, pokok, 0 ))/1000 AS realisasi_11,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20251101 AND tgl_trans <= 20251130, pokok, 0 ))/1000 AS angsuran_11,
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20251201 AND tgl_trans <= 20251231, pokok, 0 ))/1000 AS realisasi_12,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20251201 AND tgl_trans <= 20251231, pokok, 0 ))/1000 AS angsuran_12
FROM
	kredit,
	kretrans
WHERE
	kredit.no_rekening = kretrans.no_rekening
  #AND kredit.kode_kantor='018'
GROUP BY
	kredit.kode_produk




SELECT
	kredit.kode_produk,
	( SELECT deskripsi_produk FROM kre_produk WHERE kode_produk = kredit.kode_produk ) deskripsi,
	 
	 sum(	IF( floor( my_kode_trans / 100 )= 1 AND tgl_trans >= 20250701 AND tgl_trans <= 20250731, pokok, 0 ))/1000 AS realisasi_07,
	 sum(	IF( floor( my_kode_trans / 100 )= 3 AND tgl_trans >= 20250701 AND tgl_trans <= 20250731, pokok, 0 ))/1000 AS angsuran_07

FROM
	kredit,
	kretrans
WHERE
	kredit.no_rekening = kretrans.no_rekening
  
GROUP BY
	kredit.kode_produk