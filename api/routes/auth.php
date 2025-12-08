<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../config/database.php';

$authController = new AuthController($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$uri = trim($_GET['request'] ?? '', '/');
$endpoint = explode('/', $uri)[1] ?? '';

switch ($endpoint) {
    case 'login':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $authController->login($data);
        } else {
            sendResponse(405, "Metode tidak diizinkan");
        }
        break;

    case 'whoami':
        if ($method === 'GET') {
            $headers = getallheaders();
            $token = $headers['Authorization'] ?? '';
            $authController->whoami($token);
        } else {
            sendResponse(405, "Metode tidak diizinkan");
        }
        break;

    default:
        sendResponse(404, "Auth endpoint tidak ditemukan");
}
