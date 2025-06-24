<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Errore di connessione al database']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$name = $input['name'] ?? '';
$username = $input['username'] ?? '';
$mail = $input['mail'] ?? '';
$textMessage = $input['textMessage'] ?? '';

if (empty($name) || empty($username) || empty($mail) || empty($textMessage)) {
    echo json_encode(['success' => false, 'message' => 'Tutti i campi devono essere completati']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO contact_messages (name, username, mail, textMessage) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Errore nella query insert']);
    exit;
}
$stmt->bind_param("ssss", $name, $username, $mail, $textMessage);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Messaggio Inviato']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore durante l\'invio del messaggio']);
}

$stmt->close();
$conn->close();
