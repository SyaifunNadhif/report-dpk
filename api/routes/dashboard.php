<?php

require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../config/database.php';

$controller = new DashboardController($pdo);

// Ambil method
$method = $_SERVER['REQUEST_METHOD'];

// POST body
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'POST':
        $type = $input['type'] ?? '';

        // ==========================================
        // ENDPOINT UTAMA DASHBOARD
        // ==========================================
        if ($type === 'executive dashboard') {
            $controller->getExecutiveDashboard($input);
        } 
        
        // ==========================================
        // ENDPOINT TESTING PER FUNGSI (Biar bisa di-Postman)
        // ==========================================
        elseif ($type === 'test tren npl') {
            $data = $controller->getTrenNPL($input);
            sendResponse(200, "Testing Tren NPL", $data);
            
        } elseif ($type === 'test runoff vs realisasi') {
            $data = $controller->getRunOffVsRealisasi($input);
            sendResponse(200, "Testing Run Off vs Realisasi", $data);
            
        } elseif ($type === 'test top bottom npl') {
            $data = $controller->getTopBottomNPL($input);
            sendResponse(200, "Testing Top & Bottom NPL", $data);

        } elseif ($type === 'test delta npl') {
            $data = $controller->getTopKenaikanPenurunanNPL($input);
            sendResponse(200, "Testing Kenaikan & Penurunan NPL", $data);

        } elseif ($type === 'test runoff korwil') {
            $data = $controller->getRunOffVsRealisasiKorwil($input);
            sendResponse(200, "Testing Run Off vs Realisasi (Per Korwil)", $data);

        } elseif ($type === 'test flow recovery npl') {
            $data = $controller->getFlowVsRecoveryNPL($input);
            sendResponse(200, "Testing Flow NPL vs Recovery NPL", $data);

        } elseif ($type === 'test top realisasi') {
            $data = $controller->getTopBottomRealisasi($input);
            sendResponse(200, "Testing Top Bottom Realisasi", $data);
            
        } elseif ($type === 'test flow par') {
            $data = $controller->getFlowPAR($input);
            sendResponse(200, "Testing Flow PAR", $data);
            
        } elseif ($type === 'test rr cabang') {
            $data = $controller->getRepaymentRateCabang($input);
            sendResponse(200, "Testing Repayment Rate Cabang", $data);
            
        } elseif ($type === 'test perkembangan deposito') {
            $data = $controller->getPerkembanganDeposito($input);
            sendResponse(200, "Testing Perkembangan Deposito", $data);

        } elseif ($type === 'test perkembangan tabungan') {
            $data = $controller->getPerkembanganTabungan($input);
            sendResponse(200, "Testing Perkembangan Tabungan", $data);

        } elseif ($type === 'tren_runoff_realisasi') {
            $data = $controller->getTrenRunOffRealisasi($input);
            sendResponse(200, "Testing tren run off realisasi", $data);

        } elseif ($type === 'realisasi_by_produk') {
            $data = $controller->getRealisasiRealtimeByProduk($input);
            sendResponse(200, "test realisasi produk", $data);

        } elseif ($type === 'saldo_bank') {
            $data = $controller->getSaldoBank($input);
            sendResponse(200, "test saldo bank", $data);

        } elseif ($type === 'tren_portofolio_kredit') {
            $data = $controller->getTrenPortofolioKredit($input);
            sendResponse(200, "test tren portofolio kredit", $data);
   
        } else {
            sendResponse(400, "Endpoint atau type salah bro");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}