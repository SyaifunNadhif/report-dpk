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
                id, 
                foto1, 
                harga_jual, 
                luas_bangunan, 
                luas_tanah, 
                jenis_surat, 
                nomor_surat, 
                proses_penjualan, 
                alamat_asset
            FROM dummy_asset
            ORDER BY tampil DESC
        ";
    
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();
        sendResponse(200, "Berhasil ambil semua data dummy asset", $data);
    }
    

    public function getHome() {
        $sql = "
            SELECT 
                id, 
                foto1, 
                harga_jual, 
                luas_bangunan, 
                luas_tanah, 
                jenis_surat, 
                nomor_surat, 
                proses_penjualan, 
                alamat_asset
            FROM dummy_asset 
            WHERE tampil = 1
            
        ";
    
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();
        sendResponse(200, "Berhasil ambil data home dummy asset", $data);
    }
    
    
    
    

    // ✅ READ BY JENIS JAMINAN - PUBLIC
    public function getByJenisAgunan($jenis) {
        $sql = "
            SELECT * 
            FROM dummy_asset 
            WHERE jenis_agunan = :jenis
        ";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':jenis' => $jenis]);
        $data = $stmt->fetchAll();
    
        sendResponse(200, "Berhasil ambil data berdasarkan jenis agunan", $data);
    }
    


    // ✅ READ DETAIL BY ID - PUBLIC
    public function getDetail($id) {
        $sql = "
            SELECT 
                da.*, 
                kk.nama_kantor
            FROM 
                dummy_asset da
            INNER JOIN 
                kode_kantor kk 
                ON da.kode_kantor = kk.kode_kantor
            WHERE 
                da.id = :id
        ";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
    
        if ($data) {
            sendResponse(200, "Detail dummy asset ditemukan", $data);
        } else {
            sendResponse(404, "Dummy asset tidak ditemukan");
        }
    }
    
    



}
