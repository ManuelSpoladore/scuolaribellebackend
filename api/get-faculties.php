<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/../config/database.php';




$university_id =$_GET['university_id'] ?? null;

if (!$university_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name FROM faculties WHERE university_id = ?");
$stmt->bind_param("i", $university_id);
$stmt->execute();
$result = $stmt->get_result();

$faculties = [];

while($row = $result->fetch_assoc()) {
    $faculties[] = $row;
}

echo json_encode($faculties);