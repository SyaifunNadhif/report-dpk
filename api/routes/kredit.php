<?php

require_once __DIR__ . '/../controllers/KreditController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new KreditController($pdo);

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

        if ($input['type'] === 'Realisasi Kredit') {
            $controller->getRealisasiKredit($input);
        } elseif ($input['type'] === 'top realisasi') {
            $controller->getTopRealisasi($input);
        } elseif ($input['type'] === 'mob_vintage') {
            $controller->getRekapMob6Bulan($input);
        } elseif ($input['type'] === 'detail_mob_debitur') {
            $controller->getDetailMobDebitur($input);
        } elseif ($input['type'] === 'Migrasi Kolek') {
            $controller->getMigrasiKolek($input);
        } elseif ($input['type'] === 'kolektibilitas') {
            $controller->getKolek($input);

        } elseif ($input['type'] === 'Detail Realisasi Kredit') {
            if (!isset($input['kode_kantor'])) {
                sendResponse(400, "Parameter 'kode_kantor' wajib untuk type 'Detail Realisasi Kredit'");
                exit;
            }
            $controller->getDetailRealisasiKredit($input);



        } else {
            sendResponse(400, "Type tidak dikenali. Gunakan 'Flow Par', 'KL Baru', atau 'Last Created Nominatif'");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}
