<?php
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/guard.php';        // requireAuth()
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/CkpnController.php';

$pdo = function_exists('getPDO') ? getPDO() : ($pdo ?? null);
if (!$pdo) sendResponse(500, "DB not initialized");

$controller = new CkpnController($pdo);

// Ambil method
$method = $_SERVER['REQUEST_METHOD'];

// Body JSON (fallback ke $_POST)
$raw   = file_get_contents("php://input");
$input = json_decode($raw, true);
if (!is_array($input)) $input = $_POST ?: [];

switch ($method) {
  case 'POST':
    // Wajib login → ambil payload user dari token
    

    if (!isset($input['type'])) {
      sendResponse(400, "Parameter 'type' diperlukan: maping_account | bucket | detail_bucket");
    }

    $type = strtolower(trim($input['type']));

    
    if($type === 'rekap ckpn'){
      $controller->getRekapCkpnNominatif($input);

    } elseif($type === 'rekap ckpn cabang'){
      $controller->getRekapCkpnNominatifPerCabang($input);
    } elseif($type === 'rekap ckpn produk'){
      $controller->getRekapCkpnPerProduk($input);
    } elseif($type === 'rekap ckpn bucket'){
      $controller->getRekapCkpnPerBucket($input);
    } elseif($type === 'ckpn cabang'){
      $controller->getRekapCkpnPerCabang($input);
    
    } else {
      sendResponse(400, "Type tidak dikenali. Gunakan: maping_account | bucket | detail_bucket");
    }
    break;

  default:
    sendResponse(405, "Metode tidak diizinkan");
}
