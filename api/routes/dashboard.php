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
            
        } elseif ($type === 'test flow par') {
            $data = $controller->getFlowPAR($input);
            sendResponse(200, "Testing Flow PAR", $data);
            
        } elseif ($type === 'test repayment rate') {
            $data = $controller->getRepaymentRate($input);
            sendResponse(200, "Testing Repayment Rate", $data);
            
        } elseif ($type === 'test recovery vs flow par') {
            $data = $controller->getRecoveryVsFlowPAR($input);
            sendResponse(200, "Testing Recovery vs Flow PAR", $data);
            
        } elseif ($type === 'test realisasi per korwil') {
            $data = $controller->getRealisasiPerKorwil($input);
            sendResponse(200, "Testing Realisasi per Korwil", $data);
            
        } else {
            sendResponse(400, "Endpoint atau type salah bro");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}