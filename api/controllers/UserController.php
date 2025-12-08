<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "message" => "Daftar users",
    "data" => [
        ["id" => 1, "name" => "John Doe"],
        ["id" => 2, "name" => "Jane Doe"]
    ]
]);
?>
