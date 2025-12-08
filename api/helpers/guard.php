<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/JWT.php';  // pakai generateJWT/verifyJWT

function getAuthHeaderValue(): string {
  if (isset($_SERVER['HTTP_AUTHORIZATION'])) return $_SERVER['HTTP_AUTHORIZATION'];
  if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
  if (function_exists('getallheaders')) {
    $h = getallheaders();
    return $h['Authorization'] ?? $h['authorization'] ?? '';
  }
  return '';
}

function requireAuth(): array {
  $hdr = trim(getAuthHeaderValue());
  // dukung "Bearer xxx" atau token polos
  if (preg_match('/^Bearer\s+(.+)$/i', $hdr, $m)) $token = trim($m[1]); else $token = $hdr;
  if ($token === '') sendResponse(401, 'No token');

  // SAMAKAN secret dengan yang dipakai saat login!
  $secret  = $_ENV['JWT_SECRET'] ?? 'your-secret-key';

  $payload = verifyJWT($token, $secret);   // <- fungsi milikmu
  if (!$payload) sendResponse(401, 'Invalid/expired token');

  return $payload; // berisi full_name, dst.
}
