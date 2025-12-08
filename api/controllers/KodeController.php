<?php

require_once __DIR__ . '/../helpers/response.php';

class KodeController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }


    public function getKodeKantor($input = [])
    {
        $sql = "
            SELECT 
                kode_kantor, 
                nama_kantor
            FROM kode_kantor
            WHERE kode_kantor <> '000'
            ORDER BY kode_kantor
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(200, "Berhasil ambil daftar kode kantor", $data);
    }



}
