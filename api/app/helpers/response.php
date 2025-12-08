
<?php
// app/helpers/response.php
function sendResponse(int $code, string $message, $data=null): void {
  http_response_code($code);
  echo json_encode([
    'status'=>$code,
    'message'=>$message,
    'data'=>$data
  ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  exit;
}
