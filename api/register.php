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

// input JSON
$input = json_decode(file_get_contents('php://input'), true);

$name = $input['name'] ?? '';
$surname = $input['surname'] ?? '';
$username = $input['username'] ?? '';
$mail = $input['mail'] ?? '';
$password = $input['password'] ?? '';
$university = $input['university_id'] ?? '';
$faculty = $input['faculty_id'] ?? '';

if (empty($name) || empty($surname) || empty($username) || empty($mail) || empty($password) || empty($university) || empty($faculty)) {
    echo json_encode(['success' => false, 'message' => 'Tutti i campi sono obbligatori']);
    exit;
}

if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Indirizzo mail non valido']);
    exit;
}

// Email verification
$stmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Errore nella query email']);
    exit;
}
$stmt->bind_param("s", $mail);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email già registrata']);
    $stmt->close();
    exit;
}
$stmt->close();

// username verification
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Errore nella query username']);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username già registrato']);
    $stmt->close();
    exit;
}
$stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);


$stmt = $conn->prepare("INSERT INTO users (name, surname, username, mail, password, university_id, faculty_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Errore nella query insert']);
    exit;
}
$stmt->bind_param("sssssss", $name, $surname, $username, $mail, $hashed_password, $university, $faculty);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registrazione completata']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore durante la registrazione']);
}

$stmt->close();
$conn->close();
