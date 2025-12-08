<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class RegisterController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Ambil semua register
    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM register ORDER BY created_at DESC");
            $registers = $stmt->fetchAll();
            sendResponse(200, "Data register ditemukan", $registers);
        } catch (PDOException $e) {
            sendResponse(500, "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    // Tambah register baru
    public function create($data) {
        try {
            $sql = "INSERT INTO register (user_id, kepada, nomor_surat, type_nomor_surat, perihal, arsip_file) 
                    VALUES (:user_id, :kepada, :nomor_surat, :type_nomor_surat, :perihal, :arsip_file)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':kepada' => $data['kepada'],
                ':nomor_surat' => $data['nomor_surat'],
                ':type_nomor_surat' => $data['type_nomor_surat'] ?? null,
                ':perihal' => $data['perihal'],
                ':arsip_file' => $data['arsip_file'] ?? null,
            ]);
            sendResponse(201, "Register berhasil ditambahkan");
        } catch (PDOException $e) {
            sendResponse(500, "Gagal menambahkan register: " . $e->getMessage());
        }
    }

    // Ambil satu register berdasarkan ID
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM register WHERE id = ?");
            $stmt->execute([$id]);
            $register = $stmt->fetch();
            if ($register) {
                sendResponse(200, "Data register ditemukan", $register);
            } else {
                sendResponse(404, "Register tidak ditemukan");
            }
        } catch (PDOException $e) {
            sendResponse(500, "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    // Update register berdasarkan ID
    public function update($id, $data) {
        try {
            // Cek apakah data dengan ID tersebut ada
            $checkStmt = $this->pdo->prepare("SELECT id FROM register WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if (!$checkStmt->fetch()) {
                sendResponse(404, "Data register dengan ID $id tidak ditemukan");
                return;
            }

            // Update data
            $sql = "UPDATE register SET 
                        user_id = :user_id, 
                        kepada = :kepada, 
                        nomor_surat = :nomor_surat, 
                        type_nomor_surat = :type_nomor_surat, 
                        perihal = :perihal, 
                        arsip_file = :arsip_file
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $data['user_id'],
                ':kepada' => $data['kepada'],
                ':nomor_surat' => $data['nomor_surat'],
                ':type_nomor_surat' => $data['type_nomor_surat'] ?? null,
                ':perihal' => $data['perihal'],
                ':arsip_file' => $data['arsip_file'] ?? null,
            ]);

            sendResponse(200, "Register berhasil diperbarui");
        } catch (PDOException $e) {
            sendResponse(500, "Gagal memperbarui register: " . $e->getMessage());
        }
    }

    // Hapus register berdasarkan ID
    public function delete($id) {
        try {
            // Cek apakah data dengan ID tersebut ada
            $checkStmt = $this->pdo->prepare("SELECT id FROM register WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if (!$checkStmt->fetch()) {
                sendResponse(404, "Data register dengan ID $id tidak ditemukan");
                return;
            }

            // Hapus data
            $stmt = $this->pdo->prepare("DELETE FROM register WHERE id = :id");
            $stmt->execute([':id' => $id]);
            sendResponse(200, "Register berhasil dihapus");
        } catch (PDOException $e) {
            sendResponse(500, "Gagal menghapus register: " . $e->getMessage());
        }
    }
}
?>
