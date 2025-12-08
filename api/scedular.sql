# update dan insert tabel : kre_agunan_relasi

INSERT INTO bu_core_replica.kre_agunan_relasi (
    id_relasi,
    agunan_id,
    no_rekening,
    transfer,
    kode_kantor,
    primer,
    status
)
SELECT
    id_relasi,
    agunan_id,
    no_rekening,
    transfer,
    kode_kantor,
    primer,
    status
FROM bu_core.kre_agunan_relasi
WHERE id_relasi > 578548
ON DUPLICATE KEY UPDATE
    agunan_id   = VALUES(agunan_id),
    no_rekening = VALUES(no_rekening),
    transfer    = VALUES(transfer),
    kode_kantor = VALUES(kode_kantor),
    primer      = VALUES(primer),
    status      = VALUES(status);
    

Untuk mereplace data kre_agunan

REPLACE INTO bu_core_replica.kre_agunan
SELECT *
FROM bu_core.kre_agunan
WHERE TGL_REGISTER >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH);

