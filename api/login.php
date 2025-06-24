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

session_start();

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_ENV['JWT_SECRET'])) {
    echo json_encode(['success' => false, 'message' => 'Chiave segreta JWT non configurata']);
    exit;
}


$username = $input['username'] ?? '';
$password = $input['password'] ?? '';



if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Inserisci username e password']);
    exit;
}

$stmt = $conn->prepare("
  SELECT u.id, u.username, u.password,
         u.university_id, u.faculty_id,
         un.name AS university,
         f.name AS faculty
  FROM users u
  JOIN universities un ON u.university_id = un.id
  JOIN faculties f ON u.faculty_id = f.id
  WHERE u.username = ?
");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Errore SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Utente non trovato']);
    exit;
}

$user = $result->fetch_assoc();



if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;

    $key = $_ENV['JWT_SECRET'];
    $payload = [
        "user_id" => $user['id'],
        "username" => $username,
        "university" => $user['university'],
        "faculty" => $user['faculty'],
        "university_id" => $user['university_id'],
        "faculty_id" => $user['faculty_id'],
        "exp" => time() + 3600
    ];
    $jwt = JWT::encode($payload, $key, 'HS256');

    echo json_encode([
        'success' => true,
        'token' => $jwt,
        'message' => 'Login riuscito'
    ]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Password errata']);
    exit;
}

$stmt->close();
$conn->close();
