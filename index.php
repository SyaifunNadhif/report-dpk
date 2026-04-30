<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * =========================
 * BASE APP (AUTO)
 * =========================
 * Local   : http://localhost/report-dpk
 * Server  : https://domain.com
 */
define('BASE_APP',
    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' .
    $_SERVER['HTTP_HOST'] .
    (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? '/report-dpk' : '')
);

// =========================
// AMBIL URL DARI REWRITE
// =========================
$url = $_GET['url'] ?? '';
$url = trim($url, '/');

// =========================
// JANGAN LEWATKAN API KE ROUTER HALAMAN
// =========================
if (strpos($url, 'api/') === 0) {
    $apiPath = __DIR__ . '/' . $url . '.php';

    if (is_file($apiPath)) {
        require $apiPath;
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => false,
            'message' => 'API endpoint not found'
        ]);
    }
    exit;
}

// =========================
// CEK STATUS LOGIN (VIA COOKIE SSO)
// =========================
// Sekarang kita cek pakai Cookie sso_token, bukan $_SESSION lagi
$isLoggedIn = isset($_COOKIE['sso_token']) && !empty($_COOKIE['sso_token']);

// =========================
// ROUTING DEFAULT
// =========================
if ($url === '') {
    $url = $isLoggedIn ? 'dashboard' : 'login';
}

// page / param
[$page, $param] = array_pad(explode('/', $url, 2), 2, null);

// =========================
// PROTEKSI HALAMAN (AUTH GUARD)
// =========================
// 1. Kalau belum login tapi maksa buka halaman selain login, lempar ke login!
if (!$isLoggedIn && $page !== 'login') {
    header("Location: " . BASE_APP . "/login");
    exit;
}

// 2. Kalau SUDAH login tapi malah buka halaman login, paksa masuk ke dashboard!
if ($isLoggedIn && $page === 'login') {
    header("Location: " . BASE_APP . "/dashboard");
    exit;
}

$baseDir = __DIR__;

// =========================
// HEADER
// =========================
include $baseDir . "/views/header.php";

// login tidak pakai navbar
if ($page !== 'login') {
    include $baseDir . "/views/navbar.php";
}

// =========================
// LOAD PAGE
// =========================
$path = $baseDir . "/pages/{$page}.php";

if (is_file($path)) {
    if ($param !== null) {
        $_GET['id'] = $param;
    }
    include $path;
} else {
    http_response_code(404);
    echo "<h1>404 - Halaman tidak ditemukan</h1>";
}

// =========================
// FOOTER
// =========================
include $baseDir . "/views/script.php";
include $baseDir . "/views/footer.php";