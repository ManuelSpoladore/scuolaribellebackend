<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);



header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/../config/database.php';


$sql = "SELECT id, name FROM universities";

$result = $conn->query($sql);


$universities = [];

while($row = $result->fetch_assoc()) {
    $universities[] = $row;
}

echo json_encode($universities);