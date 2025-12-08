<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middlewares/auth.php';
// require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($data) {
        $employee_id = $data['employee_id'] ?? '';
        $password = $data['password'] ?? '';

        // Cek user di database
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE employee_id = :employee_id");
        $stmt->execute([':employee_id' => $employee_id]);
        $user = $stmt->fetch();

        if (!$user || $password !== 'bkkjtg123') {
            sendResponse(401, "employee_id atau password salah");
        }

        $payload = [
            "id" => $user['id'],
            "employee_id" => $user['employee_id'],
            "iat" => time(),
            "exp" => time() + (60 * 60 * 5) // 1 jam
        ];

        $token = generateJWT($payload);
        sendResponse(200, "Login berhasil", ["token" => $token]);
    }

    public function whoami($token) {
        $decoded = verifyJWT($token);
    
        if (!$decoded) {
            sendResponse(401, "Token tidak valid atau kadaluarsa");
        }
    
        // Ambil employee_id dari payload token
        $employee_id = $decoded['employee_id'] ?? null;
    
        if (!$employee_id) {
            sendResponse(400, "ID Karyawan tidak ditemukan dalam token");
        }
    
        // Query data user dari database
        $stmt = $this->pdo->prepare("SELECT id, kode, employee_id, full_name, job_position, branch_name, mobile_phone FROM users WHERE employee_id = :employee_id");
        $stmt->execute([':employee_id' => $employee_id]);
        $user = $stmt->fetch();
    
        if (!$user) {
            sendResponse(404, "User tidak ditemukan");
        }
    
        sendResponse(200, "Data user berhasil diambil", $user);
    }
}
