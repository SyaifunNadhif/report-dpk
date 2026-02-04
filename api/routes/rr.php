<?php

// =============================================================
// ROUTER KHUSUS: REPAYMENT RATE (RR) - FIXED
// =============================================================

// 1. Load Dependencies (Gunakan require_once ke helper pusat)
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/RepaymentRateController.php';

// 2. Init Database (Cara Aman agar $pdo tidak null)
// Cek apakah function getPDO ada (dari config), atau ambil variabel $pdo langsung
$pdo = function_exists('getPDO') ? getPDO() : ($pdo ?? null);

if (!$pdo) {
    sendResponse(500, "Database Connection Failed (PDO is null)");
}

// 3. Init Controller
try {
    $controller = new RepaymentRateController($pdo);
} catch (Exception $e) {
    sendResponse(500, "Controller Init Failed: " . $e->getMessage());
}

// 4. Ambil Method & Input Body
$method = $_SERVER['REQUEST_METHOD'];
$raw    = file_get_contents("php://input");
$input  = json_decode($raw, true);

// Fallback: Jika input bukan JSON (misal form-data), ambil dari $_POST
if (!is_array($input)) {
    $input = $_POST ?: [];
}

// 5. Switch Logic
switch ($method) {
    case 'POST':
        // Validasi Parameter Type
        if (empty($input['type'])) {
            sendResponse(400, "Parameter 'type' diperlukan.");
        }

        // Normalisasi input (kecilkan huruf & hapus spasi)
        $type = strtolower(trim($input['type']));

        // --- A. REKAP UTAMA (RR) ---
        if ($type === 'rekap_rr') {
            $controller->getRepaymentRate($input);

        // --- B. DETAIL DRILL DOWN ---
        } elseif ($type === 'detail_rr') {
            $controller->getDetailRepaymentRate($input);

        // --- C. MONITORING (EARLY WARNING) ---
        } elseif ($type === 'monitoring_rr') {
            $controller->getMonitoringLatePayers($input);

        // --- D. DETAIL PELUNASAN ---
        } elseif ($type === 'detail_lunas_rr') {
            $controller->getDetailLunasRR($input);

        // --- ERROR: TYPE TIDAK DIKENAL ---
        } else {
            sendResponse(400, "Type request tidak dikenali: " . $type);
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan (Gunakan POST)");
        break;
}