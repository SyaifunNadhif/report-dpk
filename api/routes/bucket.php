<?php
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/guard.php';        // requireAuth()
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/BucketController.php';

$pdo = function_exists('getPDO') ? getPDO() : ($pdo ?? null);
if (!$pdo) sendResponse(500, "DB not initialized");

$controller = new BucketController($pdo);

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

    if ($type === 'maping_account') {
      $user = requireAuth(); // harus berisi full_name untuk PIC
      // Ambil data mapping berdasarkan account (PIC = full_name pada token)
      $controller->getMappingAccountMyList($user, $input);

    } elseif($type === 'maping_account2') {
      $user = requireAuth(); // harus berisi full_name untuk PIC
      // Ambil data mapping berdasarkan account (PIC = full_name pada token)
      $controller->getMappingAccountMyList2($user, $input);

    } elseif ($type === 'bucket' || $type === 'backet') {
      $controller->getBucket($input);
    
    } elseif($type === 'rekap ckpn'){
      $controller->getRekapCkpnNominatif($input);

    } elseif($type === 'rekap ckpn cabang'){
      $controller->getRekapCkpnNominatifPerCabang($input);

    } elseif ($type === 'detail_bucket') {
      $controller->getBucketDetail($input);

    } else {
      sendResponse(400, "Type tidak dikenali. Gunakan: maping_account | bucket | detail_bucket");
    }
    break;

  default:
    sendResponse(405, "Metode tidak diizinkan");
}
