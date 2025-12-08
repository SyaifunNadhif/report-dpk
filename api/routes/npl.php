<?php

require_once __DIR__ . '/../controllers/NplController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new NplController($pdo);

// Ambil method
$method = $_SERVER['REQUEST_METHOD'];

// POST body
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'POST':
        if (!isset($input['type'])) {
            sendResponse(400, "Parameter 'type' diperlukan ('Flow Par', 'KL Baru', atau 'Last Created Nominatif')");
            exit;
        }

        if ($input['type'] === 'NPL') {
            $controller->getNpl($input);

        } elseif ($input['type'] === 'KL Baru') {
            if (!isset($input['kode_kantor'])) {
                sendResponse(400, "Parameter 'kode_kantor' wajib untuk type 'KL Baru'");
                exit;
            }
            $controller->getDebiturFlowPar($input);

        } elseif ($input['type'] === 'Recovery NPL') {
            $controller->getRecoveryNpl($input);
        } elseif ($input['type'] === '25 NPL Terbesar') {
            $controller->getTop25NplPerCabang($input);
        } elseif ($input['type'] === 'Potensi NPL') {
            $controller->getPotensiNpl($input);
        } elseif ($input['type'] === 'Debitur Potensi NPL') {
            $controller->getDetailPotensiNpl($input);
        } elseif ($input['type'] === 'Backet') {
            $controller->getBucket($input = []);
        
        } elseif (in_array($input['type'], ['lunas', 'backflow'])) {
            $controller->getDetailRecoveryNpl($input);

        } else {
            sendResponse(400, "Type tidak dikenali. Gunakan 'Flow Par', 'KL Baru', atau 'Last Created Nominatif'");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}
