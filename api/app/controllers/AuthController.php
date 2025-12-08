
<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/jwt.php';
require_once __DIR__ . '/../config/env.php';

class AuthController {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  public function login(array $input) {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    if ($username === '' || $password === '') sendResponse(400, 'username & password required');

    $stmt = $this->pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :u AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([':u'=>$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
      sendResponse(401, 'Invalid credentials');
    }

    // roles
    $rs = $this->pdo->prepare("
      SELECT r.name FROM user_roles ur
      JOIN roles r ON r.id = ur.role_id
      WHERE ur.user_id = :uid
    ");
    $rs->execute([':uid'=>$user['id']]);
    $roles = array_map(fn($r)=>$r['name'], $rs->fetchAll());

    $payload = [
      'sub' => (string)$user['id'],
      'username' => $username,
      'roles' => $roles,
    ];
    $ttl = (int)env('JWT_TTL', '7200');
    $token = jwt_sign($payload, env('JWT_SECRET','dev'), $ttl);
    sendResponse(200, 'OK', ['token'=>$token, 'user'=>['id'=>$user['id'],'username'=>$username,'roles'=>$roles]]);
  }

  public function whoami(array $input) {
    global $AUTH_USER;
    if (!$AUTH_USER) sendResponse(401,'Not authenticated');
    sendResponse(200, 'OK', $AUTH_USER);
  }
}
