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
if (strpos($url, 'api/')) {
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
// ROUTING DEFAULT
// =========================
if ($url === '') {
    $url = !empty($_SESSION['user_id']) ? 'home' : 'login';
}

// page / param
[$page, $param] = array_pad(explode('/', $url, 2), 2, null);

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
