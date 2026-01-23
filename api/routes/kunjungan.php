<?php
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/guard.php';        // requireAuth()
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/KunjunganController.php';

$pdo = function_exists('getPDO') ? getPDO() : ($pdo ?? null);
if (!$pdo) sendResponse(500, "DB not initialized");

$controller = new KunjunganController($pdo);
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
     * PIPELANE MAPPING & DETAIL
     * ===================================================== */
    if ($type === 'pipelane_mapping') {
      $controller->getPipelaneMapping($input);
    } elseif ($type === 'detail_nasabah_maping') {
      $controller->getDetailNasabahFromHandle($input);
    } elseif ($type === 'detail_accout_maping') {
      $user = requireAuth();
      $controller->getAccountHandle($input, $user);
    } elseif ($type === 'detail_by_rekening') {
      $controller->getDetailByNoRekening($input);
    } elseif ($type === 'search_detail_handle') {
      $controller->searchDetailHandle($input);
    } elseif ($type === 'search_detail') {
      $controller->searchDetailHandleGlobal($input);
        } elseif ($type === 'rekap_kunjungan') {
      $controller->getRekapKunjungan($input);

    /* =====================================================
     * KUNJUNGAN CRUD
     * ===================================================== */
    } elseif ($type === 'create_kunjungan') {
      $user = requireAuth();
      $controller->createDataKunjungan(null, $user);
      exit;
  
    

    /* =====================================================
     * HISTORY KUNJUNGAN
     * ===================================================== */
    } elseif ($type === 'history_kunjungan_rekening') {
      // $user = requireAuth();
      $controller->getHistoryKunjunganByNoRekening($input);
    }elseif ($type === 'monitoring_ao'){
      $controller->getMonitoringKunjunganAO($input);
    } elseif ($type === 'kunjungan_by_user_login') {
      $user = requireAuth();
      $controller->getKunjunganByUserLogin($input, $user);

    } elseif ($type === 'history_kunjungan_atasan') {
      $user = requireAuth();
      $controller->getHistoryKunjunganByAtasan($input, $user);

    } elseif ($type === 'reminder_janji_bayar') {
      $user = requireAuth();
      $controller->getReminderJanjiBayar($input, $user);

    // } elseif ($type === 'rekap_kunjungan') {
    //   $user = requireAuth();
    //   $controller->getRekapKunjunganByKode($input, $user);

    } elseif ($type === 'frekuensi_kunjungan') {
      $user = requireAuth();
      $controller->getFrekuensiKunjunganDebitur($input, $user);

    } elseif ($type === 'verify_kunjungan') {
      $user = requireAuth();
      $controller->verifyKunjungan($input, $user);

    /* =====================================================
     * DEFAULT
     * ===================================================== */
    } else {
      sendResponse(400, "Type '$type' tidak dikenali. Gunakan salah satu:
      pipelane_mapping |
      detail_nasabah_maping |
      detail_accout_maping |
      detail_by_rekening |
      search_detail_handle |
      create_kunjungan |
      history_kunjungan_rekening |
      kunjungan_by_user_login |
      history_kunjungan_atasan |
      reminder_janji_bayar |
      rekap_kunjungan |
      frekuensi_kunjungan |
      verify_kunjungan");
    }
    break;

  default:
    sendResponse(405, "Metode tidak diizinkan");
}
