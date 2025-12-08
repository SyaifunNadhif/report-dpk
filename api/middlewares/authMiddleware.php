<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middlewares/auth.php'; // pakai verifyJWT()

function requireAuth() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';

    if (str_starts_with($token, 'Bearer ')) {
        $token = str_replace('Bearer ', '', $token);
    } else {
        sendResponse(401, "Token tidak ditemukan");
        exit;
    }

    $user = verifyJWT($token);
    if (!$user) {
        sendResponse(401, "Token tidak valid atau kadaluarsa");
        exit;
    }

    return $user; // return data user yang ada di payload token
}
