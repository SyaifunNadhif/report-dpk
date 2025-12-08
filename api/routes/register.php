<?php

require_once __DIR__ . '/../controllers/RegisterController.php';
require_once __DIR__ . '/../config/database.php';


$registerController = new RegisterController($pdo);

// Ambil request method
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = explode('/', trim($_GET['request'], '/'));

// Cek apakah ada ID dalam request
$id = isset($_GET['id']) ? intval($_GET['id']) : null;


switch ($method) {
    case 'GET':
        if ($id) {
            $registerController->getById($id);
        } else {
            $registerController->getAll();
        }
        break;
    
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            sendResponse(400, "Data tidak valid");
        }
        $registerController->create($data);
        break;
    
    case 'PUT':
        if (!$id) {
            sendResponse(400, "ID register diperlukan untuk update");
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            sendResponse(400, "Data tidak valid");
        }
        $registerController->update($id, $data);
        break;

    case 'DELETE':
        if (!$id) {
            sendResponse(400, "ID register diperlukan untuk menghapus");
        }
        $registerController->delete($id);
        break;

    default:
        sendResponse(405, "Metode tidak diizinkan");
        break;
}

?>
