<?php

require_once __DIR__ . '/../controllers/DummyController.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/authMiddleware.php';

$catalogController = new CatalogController($pdo); // Ganti jika kamu pakai DummyController
$method = $_SERVER['REQUEST_METHOD'];
$segments = $_GET['segments'] ?? [];

$action = $segments[1] ?? ''; // ex: 'detail', 'home', etc.

switch ($method) {
    case 'GET':
        if ($action === 'detail') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $catalogController->getDetail($id);
            } else {
                sendResponse(400, "ID tidak disediakan");
            }

        } elseif ($action === 'home') {
            $catalogController->getHome(); // ✅ Tambahkan ini

        } else {
            $jenis = $_GET['jenis_jaminan'] ?? null;
            if ($jenis) {
                $catalogController->getByJenisAgunan($jenis);
            } else {
                $catalogController->getAll();
            }
        }
        break;

    default:
        sendResponse(405, "Method tidak diizinkan");
        break;
}
