<?php

// =============================================================
// ROUTER KHUSUS: REPAYMENT RATE (RR)
// =============================================================

// 1. Load Controller & Database
require_once __DIR__ . '/../controllers/RepaymentRateController.php';
require_once __DIR__ . '/../config/database.php';

// Fungsi Helper Response
if (!function_exists('sendResponse')) {
    function sendResponse($code, $msg, $data = []) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode(['status' => $code, 'message' => $msg, 'data' => $data]);
        exit;
    }
}

// 2. Inisialisasi Controller
try {
    $controller = new RepaymentRateController($pdo);
} catch (Exception $e) {
    sendResponse(500, "Database Connection Failed: " . $e->getMessage());
}

// 3. Ambil Method & Input Body
$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true);

// 4. Switch Logic
switch ($method) {
    case 'POST':
        // Pastikan ada parameter 'type' agar tidak error
        $type = $input['type'] ?? '';

        // --- A. REKAP UTAMA ---
        if ($type === 'rekap_rr') {
            // Target 1-31 vs Actual (Lancar/Macet) vs Recovery
            $controller->getRepaymentRate($input);

        // --- B. DETAIL DRILL DOWN ---
        } elseif ($type === 'detail_rr') {
            // Detail nasabah per tanggal tagih
            $controller->getDetailRepaymentRate($input);

        // --- C. MONITORING (YANG HILANG TADI) ---
        } elseif ($type === 'monitoring_rr') {
            // Early Warning: M-1 Lancar -> Current Menunggak
            $controller->getMonitoringLatePayers($input);
        } elseif ($type === 'detail_lunas_rr') {
            // Early Warning: M-1 Lancar -> Current Menunggak
            $controller->getDetailLunasRR($input);

        // --- ERROR ---
        } else {
            sendResponse(400, "Tipe request tidak dikenali: " . $type);
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan (Gunakan POST)");
        break;
}