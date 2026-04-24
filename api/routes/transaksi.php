<?php

require_once __DIR__ . '/../controllers/TransaksiController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new TransaksiController($pdo);

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

        if ($input['type'] === 'tren_nominal_va') {
            $controller->getTrenNominalVa($input);

        } elseif ($input['type'] === 'rekap_transaksi_channel') {
            $controller->getRekapTransaksiChannel($input);

        } elseif ($input['type'] === 'distribusi_va') {
            $controller->getDistribusiVa($input);
        
        } elseif ($input['type'] === 'summary_cards_transaksi') {
            $controller->getSummaryCardsTransaksi($input);
        
        } elseif ($input['type'] === 'detail_breakdown_transaksi') {
            $controller->getDetailBreakdownTransaksi($input);

        } elseif ($input['type'] === 'rekap_device_branchless') {
            $controller->getRekapDeviceBranchless($input);
        
        } elseif ($input['type'] === 'detail_device_branchless') {
            $controller->getDetailDeviceBranchless($input);

        


        } else {
            sendResponse(400, "Type tidak dikenali. Gunakan 'Flow Par', 'KL Baru', atau 'Last Created Nominatif'");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}
