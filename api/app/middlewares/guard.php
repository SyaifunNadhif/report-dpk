
<?php
// app/middlewares/guard.php
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/jwt.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

function getBearerToken(): ?string {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
  if (preg_match('/Bearer\s+(.*)$/i', $hdr, $m)) return trim($m[1]);
  $t = $_GET['token'] ?? ($_POST['token'] ?? null);
  return $t ? trim($t) : null;
}

function requireAuth(string $roleFilter=null): array {
  $token = getBearerToken();
  if (!$token) sendResponse(401, "Missing token");
  $payload = jwt_verify($token, env('JWT_SECRET','dev-secret'));
  if (!$payload) sendResponse(401, "Invalid/expired token");
  // Optional role check: 'auth:Admin' or 'auth:Admin,HR'
  if ($roleFilter) {
    $roleFilter = preg_replace('/^auth:/','',$roleFilter);
    $allowed = array_map('trim', explode(',', $roleFilter));
    $userRoles = $payload['roles'] ?? [];
    $ok = count(array_intersect($allowed, $userRoles))>0;
    if (!$ok) sendResponse(403, "Forbidden (role)");
  }
  // attach to global
  $GLOBALS['AUTH_USER'] = $payload;
  return $payload;
}
