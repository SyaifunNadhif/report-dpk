<?php

require_once __DIR__ . '/../controllers/HapusBukuController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new HapusBukuController($pdo);

// Ambil method
$method = $_SERVER['REQUEST_METHOD'];

// POST body
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'POST':
        if (!isset($input['type'])) {
            sendResponse(400, "Parameter 'type' diperlukan ('recovery' atau 'debitur')");
            exit;
        }

        if ($input['type'] === 'recovery') {
            $controller->getRecoveryMount($input);
        } elseif($input['type'] === 'saldo ph'){
            $controller->getRekapSaldoPH($input);
        } elseif($input['type'] === 'detail debitur bucket'){
            $controller->getDetailPHByBucket($input);
        } elseif($input['type'] === 'detail debitur ph lgd'){
            $controller->getListPHLGD($input);
        } elseif ($input['type'] === 'debitur') {
            if (!isset($input['kode_kantor'])) {
                sendResponse(400, "Parameter 'kode_kantor' wajib untuk type 'debitur'");
                exit;
            }
            $controller->getDetailDebitur( $input);
        } else {
            sendResponse(400, "Type tidak dikenali. Gunakan 'recovery' atau 'debitur'");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}
