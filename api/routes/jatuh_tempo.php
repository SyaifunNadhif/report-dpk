<?php

// 1. Load Controller & Database
require_once __DIR__ . '/../controllers/JatuhTempoController.php';
require_once __DIR__ . '/../config/database.php';

// 2. Inisialisasi
$controller = new JatuhTempoController($pdo);

// 3. Ambil Method & Input Body
$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true);

// 4. Switch Logic
switch ($method) {
    case 'POST':
        // Pastikan ada parameter 'type' agar tidak error
        $type = $input['type'] ?? '';

        if ($type === 'rekap prospek jatuh tempo') {
            // Untuk Grafik / Ringkasan di Atas
            $controller->getRekapProspek($input);

        } elseif ($type === 'detail prospek jatuh tempo') {
            // Untuk Tabel Rincian Nasabah
            $controller->getDetailProspek($input);

        } else {
            sendResponse(400, "Tipe request tidak dikenali: " . $type);
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}