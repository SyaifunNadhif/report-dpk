<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil url dari rewrite (?url=...)
$url = $_GET['url'] ?? '';
$url = trim($url);
$url = trim($url, '/');

// =========================
// Routing default:
// - Kalau url kosong:
//      - kalau sudah login  -> home
//      - kalau belum login  -> login
// =========================
if ($url === '' || $url === null) {
    if (!empty($_SESSION['user_id'])) {   // GANTI sesuai nama session login-mu
        $url = 'home';
    } else {
        $url = 'login';
    }
}

// Pecah: page / param
[$page, $param] = array_pad(explode('/', $url, 2), 2, null);

$baseDir = __DIR__;

// Header
include $baseDir . "/views/header.php";

// login tidak pakai navbar
if ($page !== 'login') {
    include $baseDir . "/views/navbar.php";
}

// File halaman
$path = $baseDir . "/pages/{$page}.php";

if (is_file($path)) {
    if ($param !== null && $param !== '') {
        $_GET['id'] = $param;
    }
    include $path;
} else {
    http_response_code(404);
    echo "<h1>404 - Halaman tidak ditemukan</h1>";
}

// Script & footer
include $baseDir . "/views/script.php";
include $baseDir . "/views/footer.php";
