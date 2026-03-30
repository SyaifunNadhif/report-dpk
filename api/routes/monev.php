<?php
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/guard.php';        // requireAuth()
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/MonevController.php';

$pdo = function_exists('getPDO') ? getPDO() : ($pdo ?? null);
if (!$pdo) sendResponse(500, "DB not initialized");

$controller = new MonevController($pdo);
$method = $_SERVER['REQUEST_METHOD'];

// Body JSON (fallback ke $_POST)
$raw   = file_get_contents("php://input");
$input = json_decode($raw, true);
if (!is_array($input)) $input = $_POST ?: [];

switch ($method) {
  case 'POST':
    if (!isset($input['type'])) {
      sendResponse(400, "Parameter 'type' diperlukan.");
    }

    $type = strtolower(trim($input['type']));

    /* =====================================================
     * MONEV REALISASI MINGGUAN (GET & SAVE)
     * ===================================================== */
    
    // 1. Get Data (Bisa diakses siapapun untuk melihat)
    if ($type === 'get_monev') {
      // Opsional: $user = requireAuth(); kalau mau di-protect
      $controller->getMonevData($input);
    } 
    elseif ($type === 'get_real_kredit') {  
      $controller->getRealisasiKredit($input);
    } 
    elseif ($type === 'get_real_dpk') {  
      $controller->getRealisasiDPK($input);
    } 
    
    // 2. Save Data (Hanya Kacab, Terproteksi)
    elseif ($type === 'save_monev') {
        // Middleware Auth: Memastikan user login & mendapatkan data jabatannya
        $user = requireAuth(); 
        
        
        $controller->saveMonev($input, $user);
    } 
    
    // Fallback Type tidak ditemukan
    else {
        sendResponse(404, "Type request tidak dikenali.");
    }
    break;

  default:
    sendResponse(405, "Method not allowed.");
    break;
}