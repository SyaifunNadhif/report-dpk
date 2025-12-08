<?php

require_once __DIR__ . '/../controllers/FlowParController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new FlowParController($pdo);

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

        if ($input['type'] === 'Flow Par') {
            $controller->getFlowPar($input);

        } elseif ($input['type'] === 'KL Baru') {
            if (!isset($input['kode_kantor'])) {
                sendResponse(400, "Parameter 'kode_kantor' wajib untuk type 'KL Baru'");
                exit;
            }
            $controller->getDebiturFlowPar($input);

        } elseif ($input['type'] === 'Last Created Nominatif') {
            $controller->getLastCreatedDate();
        } elseif ($input['type'] === '50 Besar') {
            $controller->getTop50FlowPar($input);
        } elseif ($input['type'] === 'Update KL Baru') {
            $controller->updateKomitmenKlBaru($input);
        } elseif ($input['type'] === 'detail debitur') {
            $controller->getDetailDebitur($input);
        } else {
            sendResponse(400, "Type tidak dikenali. Gunakan 'Flow Par', 'KL Baru', atau 'Last Created Nominatif'");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}
