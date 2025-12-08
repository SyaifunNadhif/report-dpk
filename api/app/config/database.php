
<?php
// app/config/database.php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../helpers/response.php';

function getPDO(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    env('DB_HOST'), env('DB_PORT'), env('DB_NAME'));
  try {
    $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  } catch (PDOException $e) {
    sendResponse(500, 'DB connection failed', ['error'=>$e->getMessage()]);
  }
  return $pdo;
}
