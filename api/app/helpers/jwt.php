
<?php
// app/helpers/jwt.php
function base64url_encode($data){return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');}
function base64url_decode($data){return base64_decode(strtr($data, '-_', '+/'));}

function jwt_sign(array $payload, string $secret, int $ttlSeconds=3600): string {
  $header = ['alg'=>'HS256','typ'=>'JWT'];
  $now = time();
  $payload['iat'] = $payload['iat'] ?? $now;
  $payload['exp'] = $payload['exp'] ?? ($now + $ttlSeconds);
  $h = base64url_encode(json_encode($header));
  $p = base64url_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
  $sig = hash_hmac('sha256', "$h.$p", $secret, true);
  $s = base64url_encode($sig);
  return "$h.$p.$s";
}

function jwt_verify(string $token, string $secret) {
  $parts = explode('.', $token);
  if (count($parts) !== 3) return false;
  [$h,$p,$s] = $parts;
  $calc = base64url_encode(hash_hmac('sha256', "$h.$p", $secret, true));
  if (!hash_equals($calc, $s)) return false;
  $payload = json_decode(base64url_decode($p), true);
  if (!$payload) return false;
  if (isset($payload['exp']) && time() > $payload['exp']) return false;
  return $payload;
}
