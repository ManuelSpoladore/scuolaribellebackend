<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$key = $_ENV['JWT_SECRET'];

session_start();

// Token
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(['success' => false, 'message' => 'Token mancante']);
    exit;
}

list(, $jwt) = explode(" ", $headers['Authorization']);

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user_id = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Token non valido']);
    exit;
}

// Post Data
$data = json_decode(file_get_contents("php://input"));

$title = trim($data->title ?? '');
$content = trim($data->content ?? '');
$university_id = $data->university_id ?? null;
$faculty_id = $data->faculty_id ?? null;

if (empty($title) || empty($content) || !$university_id || !$faculty_id) {
    echo json_encode(['success' => false, 'message' => 'Tutti i campi sono obbligatori']);
    exit;
}

// Query 
$stmt = $conn->prepare("
  INSERT INTO posts (user_id, university_id, faculty_id, title, content)
  VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("iiiss", $user_id, $university_id, $faculty_id, $title, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post pubblicato']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Errore nel salvataggio',
        'error' => $stmt->error
    ]);
}
