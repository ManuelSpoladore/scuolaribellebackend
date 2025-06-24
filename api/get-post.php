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


if (!$conn) {
    die(json_encode(["error" => "Errore di connessione al database"]));
}


$university_id = $_GET['university_id'] ?? null;
$faculty_id = $_GET['faculty_id'] ?? null;

$query = "
SELECT p.*, u.username, uni.name AS university_name, f.name AS faculty_name
FROM posts p
JOIN users u ON p.user_id = u.id
JOIN universities uni ON p.university_id = uni.id
JOIN faculties f ON p.faculty_id = f.id
WHERE 1  ";

$params = [];
$types = "";

if($university_id !== null) {
    $query .= "AND p.university_id = ? ";
    $params[] = $university_id;
    $types .= "i";
}

if($faculty_id !== null) {
    $query .= "AND p.faculty_id = ? ";
    $params[] = $faculty_id;
    $types .= "i";
}

$query .= "ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);


if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode($posts);