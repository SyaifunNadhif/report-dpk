
<?php
// app/controllers/EmployeeController.php
require_once __DIR__ . '/../helpers/response.php';

class EmployeeController {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  public function index() {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page-1)*$limit;
    $stmt = $this->pdo->prepare("SELECT SQL_CALC_FOUND_ROWS id, nip, name, email, phone, department, position FROM employees WHERE deleted_at IS NULL ORDER BY id DESC LIMIT :o,:l");
    $stmt->bindValue(':o',$offset,PDO::PARAM_INT);
    $stmt->bindValue(':l',$limit,PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    $total = (int)$this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
    sendResponse(200,'OK',['data'=>$rows,'page'=>$page,'limit'=>$limit,'total'=>$total]);
  }

  public function show(array $input, $id) {
    $stmt = $this->pdo->prepare("SELECT * FROM employees WHERE id=:id AND deleted_at IS NULL");
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch();
    if (!$row) sendResponse(404,'Not found');
    sendResponse(200,'OK',$row);
  }

  public function create(array $input) {
    $required = ['nip','name','email'];
    foreach($required as $r){ if(empty($input[$r])) sendResponse(400,"$r required"); }
    $stmt = $this->pdo->prepare("INSERT INTO employees(nip,name,email,phone,department,position) VALUES(:nip,:name,:email,:phone,:department,:position)");
    $stmt->execute([
      ':nip'=>$input['nip'], ':name'=>$input['name'], ':email'=>$input['email'],
      ':phone'=>$input['phone'] ?? null, ':department'=>$input['department'] ?? null, ':position'=>$input['position'] ?? null
    ]);
    sendResponse(201,'Created',['id'=>$this->pdo->lastInsertId()]);
  }

  public function update(array $input, $id) {
    $stmt = $this->pdo->prepare("UPDATE employees SET nip=:nip, name=:name, email=:email, phone=:phone, department=:department, position=:position WHERE id=:id");
    $stmt->execute([
      ':nip'=>$input['nip'] ?? null, ':name'=>$input['name'] ?? null, ':email'=>$input['email'] ?? null,
      ':phone'=>$input['phone'] ?? null, ':department'=>$input['department'] ?? null, ':position'=>$input['position'] ?? null,
      ':id'=>$id
    ]);
    sendResponse(200,'Updated');
  }

  public function destroy(array $input, $id) {
    $stmt = $this->pdo->prepare("UPDATE employees SET deleted_at=NOW() WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    sendResponse(200,'Soft deleted');
  }
}
