
<?php
// app/controllers/LeaveController.php
require_once __DIR__ . '/../helpers/response.php';

class LeaveController {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  public function getQuota(array $input, $employeeId) {
    $stmt = $this->pdo->prepare("SELECT year, quota_total, quota_used FROM leave_quota WHERE employee_id=:id ORDER BY year DESC LIMIT 1");
    $stmt->execute([':id'=>$employeeId]);
    $row = $stmt->fetch();
    if (!$row) sendResponse(404,'Quota not found');
    $row['quota_remaining'] = (int)$row['quota_total'] - (int)$row['quota_used'];
    sendResponse(200,'OK',$row);
  }

  public function request(array $input) {
    $required = ['employee_id','start_date','end_date','reason'];
    foreach($required as $r) if(empty($input[$r])) sendResponse(400,"$r required");
    $stmt = $this->pdo->prepare("INSERT INTO leave_requests(employee_id,start_date,end_date,reason,status) VALUES(:eid,:sd,:ed,:reason,'PENDING')");
    $stmt->execute([
      ':eid'=>$input['employee_id'],
      ':sd'=>$input['start_date'],
      ':ed'=>$input['end_date'],
      ':reason'=>$input['reason']
    ]);
    sendResponse(201,'Request created',['id'=>$this->pdo->lastInsertId()]);
  }

  public function approveReject(array $input, $id, $action) {
    $status = strtoupper($action)==='APPROVE' ? 'APPROVED' : 'REJECTED';
    $this->pdo->beginTransaction();
    try{
      $stmt = $this->pdo->prepare("UPDATE leave_requests SET status=:s, decided_at=NOW() WHERE id=:id");
      $stmt->execute([':s'=>$status, ':id'=>$id]);
      if ($status==='APPROVED') {
        // reduce quota
        $lr = $this->pdo->prepare("SELECT employee_id, DATEDIFF(end_date, start_date)+1 AS days FROM leave_requests WHERE id=:id");
        $lr->execute([':id'=>$id]);
        $row = $lr->fetch();
        if ($row) {
          $upd = $this->pdo->prepare("UPDATE leave_quota SET quota_used = quota_used + :d WHERE employee_id=:eid AND year=YEAR(CURDATE())");
          $upd->execute([':d'=>$row['days'], ':eid'=>$row['employee_id']]);
        }
      }
      $this->pdo->commit();
      sendResponse(200,$status==='APPROVED'?'Approved':'Rejected');
    } catch(Exception $e){
      $this->pdo->rollBack();
      sendResponse(500,'Failed',['error'=>$e->getMessage()]);
    }
  }
}
