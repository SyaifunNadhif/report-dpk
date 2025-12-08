<?php

require_once __DIR__ . '/../helpers/response.php';

class CatalogController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ READ ALL - PUBLIC
    public function getAll() {
        $sql = "
            SELECT 
                c.id, c.rekening, c.nasabah, c.alamat_jaminan, c.jenis_jaminan, 
                c.jenis_Surat, c.no_surat, c.nilai_pasar, c.likuidasi, c.deskripsi, c.link_maps, 

                f.tampak_depan, f.tampak_belakang, f.tampak_kanan, f.tampak_belakang AS tampak_kiri,

                j.jadwal_lelang, j.harga_jual
            FROM catalog c
            LEFT JOIN foto_catalog f ON c.id = f.catalog_id
            LEFT JOIN (
                SELECT jl1.*
                FROM jadwal_lelang jl1
                INNER JOIN (
                    SELECT catalog_id, MAX(created_at) AS max_created
                    FROM jadwal_lelang
                    GROUP BY catalog_id
                ) jl2 ON jl1.catalog_id = jl2.catalog_id AND jl1.created_at = jl2.max_created
            ) j ON c.id = j.catalog_id
        ";
    
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();
        sendResponse(200, "Berhasil ambil semua data catalog", $data);
    }
    
    

    // ✅ READ BY JENIS JAMINAN - PUBLIC
    public function getByJenis($jenis) {
        $sql = "
            SELECT 
                c.id, c.rekening, c.nasabah, c.alamat_jaminan, c.jenis_jaminan, 
                c.jenis_Surat, c.no_surat, c.nilai_pasar, c.likuidasi, c.deskripsi, c.link_maps, 

                f.tampak_depan, f.tampak_belakang, f.tampak_kanan, f.tampak_belakang AS tampak_kiri,
                j.jadwal_lelang, j.harga_jual
            FROM catalog c
            LEFT JOIN foto_catalog f ON c.id = f.catalog_id
            LEFT JOIN (
                SELECT jl1.*
                FROM jadwal_lelang jl1
                INNER JOIN (
                    SELECT catalog_id, MAX(created_at) AS max_created
                    FROM jadwal_lelang
                    GROUP BY catalog_id
                ) jl2 ON jl1.catalog_id = jl2.catalog_id AND jl1.created_at = jl2.max_created
            ) j ON c.id = j.catalog_id
            WHERE c.jenis_jaminan = :jenis
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':jenis' => $jenis]);
        $data = $stmt->fetchAll();
        sendResponse(200, "Berhasil ambil data berdasarkan jenis jaminan", $data);
    }


    // ✅ READ DETAIL BY ID - PUBLIC
    public function getDetail($id) {
        $sql = "
            SELECT 
                c.id, c.rekening, c.nasabah, c.alamat_jaminan, c.jenis_jaminan, 
                c.jenis_Surat, c.no_surat, c.nilai_pasar, c.likuidasi, c.deskripsi, c.link_maps,
                 
                f.tampak_depan, f.tampak_belakang, f.tampak_kanan, f.tampak_belakang AS tampak_kiri,
                j.jadwal_lelang, j.harga_jual
            FROM catalog c
            LEFT JOIN foto_catalog f ON c.id = f.catalog_id
            LEFT JOIN (
                SELECT jl1.*
                FROM jadwal_lelang jl1
                INNER JOIN (
                    SELECT catalog_id, MAX(created_at) AS max_created
                    FROM jadwal_lelang
                    GROUP BY catalog_id
                ) jl2 ON jl1.catalog_id = jl2.catalog_id AND jl1.created_at = jl2.max_created
            ) j ON c.id = j.catalog_id
            WHERE c.id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        if ($data) {
            sendResponse(200, "Detail catalog ditemukan", $data);
        } else {
            sendResponse(404, "Catalog tidak ditemukan");
        }
    }


    // ✅ CREATE - PRIVATE
    public function create($data, $user) {
        // Bisa ditambah validasi role user jika perlu
        $sql = "INSERT INTO catalog (
                    rekening, nasabah, alamat_jaminan, jenis_jaminan, jenis_surat, 
                    no_surat, nilai_pasar, likuidasi, deskripsi, link_maps
                ) VALUES (
                    :rekening, :nasabah, :alamat_jaminan, :jenis_jaminan, :jenis_surat, 
                    :no_surat, :nilai_pasar, :likuidasi, :deskripsi, :link_maps
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        sendResponse(201, "Catalog berhasil ditambahkan");
    }

    // ✅ UPDATE - PRIVATE
    public function update($id, $data, $user) {
        $sql = "UPDATE catalog SET 
                    rekening = :rekening,
                    nasabah = :nasabah,
                    alamat_jaminan = :alamat_jaminan,
                    jenis_jaminan = :jenis_jaminan,
                    jenis_surat = :jenis_surat,
                    no_surat = :no_surat,
                    nilai_pasar = :nilai_pasar,
                    likuidasi = :likuidasi,
                    deskripsi = :deskripsi,
                    link_maps = :link_maps
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        sendResponse(200, "Catalog berhasil diupdate");
    }

    // ✅ DELETE - PRIVATE
    public function delete($id, $user) {
        $stmt = $this->pdo->prepare("DELETE FROM catalog WHERE id = :id");
        $stmt->execute([':id' => $id]);
        sendResponse(200, "Catalog berhasil dihapus");
    }
}
