<?php

require_once __DIR__ . '/../controllers/CatalogController.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middlewares/authMiddleware.php';

$catalogController = new CatalogController($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$segments = $_GET['segments'] ?? [];

$action = $segments[1] ?? ''; // ex: 'detail', '1'

switch ($method) {
    case 'GET':
        if ($action === 'detail') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $catalogController->getDetail($id);
            } else {
                sendResponse(400, "ID tidak disediakan");
            }
        } else {
            $jenis = $_GET['jenis_jaminan'] ?? null;
            if ($jenis) {
                $catalogController->getByJenis($jenis);
            } else {
                $catalogController->getAll();
            }
        }
        break;

    case 'POST':
        if ($action === '') {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) return sendResponse(400, "Data JSON tidak valid");
            $user = requireAuth();
            $catalogController->create($data, $user);
        } else {
            sendResponse(404, "Endpoint POST tidak ditemukan");
        }
        break;

    case 'PUT':
        $id = $_GET['id'] ?? null;
    
        if ($id && is_numeric($id)) {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) return sendResponse(400, "Data JSON tidak valid");
    
            $user = requireAuth(); // ✅ middleware JWT
            $catalogController->update((int)$id, $data, $user);
        } else {
            sendResponse(400, "ID catalog tidak valid atau tidak ditemukan");
        }
        break;
        

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if ($id && is_numeric($id)) {
            $user = requireAuth(); // ✅ middleware JWT
            $catalogController->delete((int)$id, $user);
        } else {
            sendResponse(400, "ID catalog tidak valid atau tidak ditemukan");
        }
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
}
