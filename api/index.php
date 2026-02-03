<?php

require_once __DIR__ . '/helpers/response.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['request'] ?? '';
$segments = explode('/', trim($request, '/'));

$endpoint = $segments[0] ?? ''; // ex: 'catalog'
switch ($endpoint) {
    case '':
        sendResponse(200, "API is running");
        break;
    case 'users':
        require __DIR__ . '/routes/user.php';
        break;
    case 'catalog':
        $_GET['segments'] = $segments; // Pass segments ke route
        require __DIR__ . '/routes/catalog.php';
        break;
    case 'asset':
        $_GET['segments'] = $segments; // Pass segments ke route
        require __DIR__ . '/routes/asset.php';
        break;
    case 'auth':
        require __DIR__ . '/routes/auth.php';
        break;
    case 'hapus_buku':
        require __DIR__ . '/routes/hapus_buku.php';
        break;
    case 'date':
        require __DIR__ . '/routes/date.php';
        break;
    case 'flow_par':
        require __DIR__ . '/routes/flow_par.php';
        break;
    case 'npl':
        require __DIR__ . '/routes/npl.php';
        break;
    case 'kredit':
        require __DIR__ . '/routes/kredit.php';
        break;
    case 'kode':
        require __DIR__ . '/routes/kode.php';
        break;
    case 'bucket':
        require __DIR__ . '/routes/bucket.php';
        break;
    case 'ckpn':
        require __DIR__ . '/routes/ckpn.php';
        break;
    case 'kolek':
        require __DIR__ . '/routes/kolek.php';
        break;
    case 'kunjungan':
        require __DIR__ . '/routes/kunjungan.php';
        break;
    case 'jt':
        require __DIR__ . '/routes/jatuh_tempo.php';
        break;
    case 'bucket_fe':
        require __DIR__ . '/routes/bucket_fe.php';
        break;
    case 'rr':
        require __DIR__ . '/routes/rr.php';
        break;

    default:
        sendResponse(404, "Endpoint tidak ditemukan");
        break;
}
