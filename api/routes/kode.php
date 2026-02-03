<?php

require_once __DIR__ . '/../controllers/KodeController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new KodeController($pdo);

// Ambil method
$method = $_SERVER['REQUEST_METHOD'];

// POST body
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'POST':
        if (!isset($input['type'])) {
            sendResponse(400, "Parameter 'type' diperlukan");
            exit;
        }

        if ($input['type'] === 'kode_kantor') {
            $controller->getKodeKantor($input);
        }elseif ($input['type'] == 'kode_ao_kredit') { // Tambahkan kondisi ini
            $controller->getKodeAOKredit($input);

        } else {
            sendResponse(400, "Type tidak dikenali");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}
