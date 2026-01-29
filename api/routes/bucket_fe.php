<?php

// 1. Load Controller & Database
require_once __DIR__ . '/../controllers/BucketFeController.php';
require_once __DIR__ . '/../config/database.php';

// Helper Response (Jika belum ada di global)
if (!function_exists('sendResponse')) {
    function sendResponse($status, $msg, $data = []) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $msg, 'data' => $data]);
        exit;
    }
}

// 2. Inisialisasi
$controller = new BucketFeController($pdo);

// 3. Ambil Method & Input Body
$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true);

// 4. Switch Logic
switch ($method) {
    case 'POST':
        // Pastikan ada parameter 'type' agar tidak error
        $type = $input['type'] ?? '';

        if ($type === 'rekap_migrasi_bucket') {
            // Untuk Matriks / Summary di Atas
            $controller->migrasiBucketOsc($input);

        } elseif ($type === 'detail_migrasi_bucket') {
            // Untuk Tabel Rincian Nasabah (Drilldown)
            $controller->getMigrasiDetail($input);

        } else {
            sendResponse(400, "Tipe request tidak dikenali: " . $type);
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}