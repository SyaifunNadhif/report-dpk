
<?php
// app/config/env.php
$__ENV = [
  'DB_HOST' => getenv('DB_HOST') ?: '127.0.0.1',
  'DB_PORT' => getenv('DB_PORT') ?: '3306',
  'DB_NAME' => getenv('DB_NAME') ?: 'hrms_db',
  'DB_USER' => getenv('DB_USER') ?: 'root',
  'DB_PASS' => getenv('DB_PASS') ?: '',
  'JWT_SECRET' => getenv('JWT_SECRET') ?: 'super-secret-key-change-me',
  'JWT_TTL' => getenv('JWT_TTL') ?: '7200',
];

function env(string $key, $default=null) {
  global $__ENV;
  return $__ENV[$key] ?? $default;
}
