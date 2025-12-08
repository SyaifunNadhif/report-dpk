<?php

require_once __DIR__ . '/../controllers/DateController.php';
require_once __DIR__ . '/../config/database.php';

$dateController = new DateController($pdo);

// Ambil method dan body JSON
$method = $_SERVER['REQUEST_METHOD'];
$raw    = file_get_contents("php://input");
$input  = json_decode($raw, true);
if (!is_array($input)) $input = [];

// Ambil type dari body (kalau ada)
$type = $input['type'] ?? null;

switch ($method) {
    case 'GET':
        // GET tetap default
        $dateController->getDefaultDate();
        break;

    case 'POST':
        // Kalau type = account_handle → panggil fungsi baru
        if ($type === 'account_handle') {
            $dateController->getDefaultAccountHandle();
        } else {
            // Default POST lama (PH)
            $dateController->getDefaultDatePH();
        }
        break;

    default:
        sendResponse(405, "Metode $method tidak diizinkan");
        break;
}
