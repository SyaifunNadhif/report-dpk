<?php
// File Location: api/pipelane/index.php

// 1. Load Dependencies (Mundur 2 folder ke root api)
require_once __DIR__ . '/../controllers/PipelaneController.php';
require_once __DIR__ . '/../config/database.php';

// 2. Init Database
// Cek fungsi getPDO (jika pakai pattern singleton) atau ambil variabel $pdo langsung
$pdo = function_exists('getPDO') ? getPDO() : ($pdo ?? null);

if (!$pdo) {
    sendResponse(500, "Database Connection Failed (PDO Not Initialized)");
}

// 3. Init Controller
try {
    $controller = new PipelineController($pdo);
} catch (Exception $e) {
    sendResponse(500, "Controller Initialization Failed: " . $e->getMessage());
}

// 4. Ambil Method & Input Body
$method = $_SERVER['REQUEST_METHOD'];
$raw    = file_get_contents("php://input");
$input  = json_decode($raw, true);

// Fallback: Jika input bukan JSON (misal form-data), ambil dari $_POST
if (!is_array($input)) {
    $input = $_POST ?: [];
}

// 5. Routing Logic
switch ($method) {
    case 'POST':
        // Validasi keberadaan parameter type
        if (empty($input['type'])) {
            sendResponse(400, "Parameter 'type' diperlukan.");
        }

        // Normalisasi type (huruf kecil & hapus spasi)
        $type = strtolower(trim($input['type']));

        // --- A. REKAP UTAMA (Dashboard) ---
        if ($type === 'rekap_pipeline') {
            $controller->getRekapPipeline($input);

        // --- B. DETAIL NASABAH (Modal) ---
        } elseif ($type === 'detail_pipeline') {
            $controller->getDetailPipeline($input);

        // --- C. ERROR TYPE TIDAK DIKENALI ---
        } else {
            sendResponse(400, "Type tidak dikenali: " . $type);
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan (Gunakan POST)");
        break;
}
?>