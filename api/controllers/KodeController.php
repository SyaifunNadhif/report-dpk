<?php

require_once __DIR__ . '/../helpers/response.php';

class KodeController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- KODE KANTOR (EXISTING) ---
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

    // --- KODE AO (BARU: DITAMBAHKAN) ---
    public function getKodeAOKredit($input = [])
    {
        $kode_kantor = $input['kode_kantor'] ?? null;

        // Base Query
        $sql = "SELECT kode_group2, nama_ao, kode_kantor FROM ao_kredit WHERE 1=1";

        // Logic Filter: Jika ada kode_kantor (dan bukan pusat/konsolidasi), filter where
        if (!empty($kode_kantor) && $kode_kantor !== '000' && $kode_kantor !== '099') {
            $sql .= " AND kode_kantor = :kc";
        }

        $sql .= " ORDER BY nama_ao ASC";

        try {
            $stmt = $this->pdo->prepare($sql);

            // Bind Param
            if (!empty($kode_kantor) && $kode_kantor !== '000' && $kode_kantor !== '099') {
                $stmt->bindValue(':kc', $kode_kantor);
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(200, "Berhasil ambil daftar AO", $data);

        } catch (PDOException $e) {
            sendResponse(500, "Database Error: " . $e->getMessage());
        }
    }

}