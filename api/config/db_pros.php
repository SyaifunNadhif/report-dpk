<?php
declare(strict_types=1);

// response helper
require_once __DIR__ . '/../helpers/response.php';

/**
 * Cari project root dengan mendeteksi file .env ke atas max 6 level
 */
function findProjectRoot(string $startDir): string {
  $dir = $startDir;

  for ($i = 0; $i < 6; $i++) {
    if (is_file($dir . '/.env')) return $dir;

    $parent = dirname($dir);
    if ($parent === $dir) break;

    $dir = $parent;
  }

  return dirname($startDir, 2);
}

$PROJECT_ROOT = findProjectRoot(__DIR__);
$envFile      = $PROJECT_ROOT . '/.env';

/**
 * Load .env
 */
function loadEnv(string $file): array {
  if (!is_file($file)) {
    sendResponse(500, "File .env tidak ditemukan di: {$file}");
    exit;
  }

  $data = parse_ini_file($file, false, INI_SCANNER_RAW);

  if ($data === false) {
    sendResponse(500, "Gagal membaca .env di: {$file}");
    exit;
  }

  return $data;
}

$env = loadEnv($envFile);

/**
 * Helper ambil ENV
 */
$ENV = function (string $key, $default = null) use ($env) {
  $v = getenv($key);
  if ($v !== false) return $v;

  return $env[$key] ?? $default;
};

// ================== CONFIG DB ==================
$DB_HOST = (string)$ENV('DB_HOST1', '127.0.0.1');
$DB_USER = (string)$ENV('DB_USER1', 'root');
$DB_PASS = (string)$ENV('DB_PASS1', '');
$DB_NAME = (string)$ENV('DB_NAME1', '');
$DB_PORT = (int)$ENV('DB_ROOT1', 3306);

// ================== CONNECT ==================
try {
  $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";

  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ]);

  // TEST (boleh dihapus nanti)
  // echo "✅ Connected ke DB: " . $DB_NAME;

} catch (PDOException $e) {
  sendResponse(500, "Koneksi database gagal: " . $e->getMessage());
  exit;
}