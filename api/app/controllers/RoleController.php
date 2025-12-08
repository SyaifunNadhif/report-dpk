
<?php
// app/controllers/RoleController.php
require_once __DIR__ . '/../helpers/response.php';

class RoleController {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  public function index() {
    $rows = $this->pdo->query("SELECT id, name, created_at FROM roles ORDER BY id")->fetchAll();
    sendResponse(200, 'OK', $rows);
  }

  public function create(array $input) {
    $name = trim($input['name'] ?? '');
    if ($name==='') sendResponse(400,'name required');
    $stmt = $this->pdo->prepare("INSERT INTO roles(name) VALUES(:n)");
    $stmt->execute([':n'=>$name]);
    sendResponse(201,'Created',['id'=>$this->pdo->lastInsertId(),'name'=>$name]);
  }

  public function update(array $input, $id) {
    $name = trim($input['name'] ?? '');
    if ($name==='') sendResponse(400,'name required');
    $stmt = $this->pdo->prepare("UPDATE roles SET name=:n WHERE id=:id");
    $stmt->execute([':n'=>$name, ':id'=>$id]);
    sendResponse(200,'Updated');
  }

  public function destroy(array $input, $id) {
    $stmt = $this->pdo->prepare("DELETE FROM roles WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    sendResponse(200,'Deleted');
  }
}
