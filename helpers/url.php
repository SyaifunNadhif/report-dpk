<?php
// app/helpers/url.php
function base_path(): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // jika base = '/' -> return ''
    return $base === '/' ? '' : $base;
}

function redirect(string $path = ''): void {
    // $path diasumsikan tanpa leading base; contoh 'login' atau '/login' juga akan normal
    $path = ltrim($path, '/');
    $base = base_path();
    header('Location: ' . $base . '/' . $path);
    exit;
}
