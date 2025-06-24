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

use Firebase\JWT\JWT;
use Firebase\JWT\Key;




if (!isset($_ENV['JWT_SECRET'])) {
    echo json_encode(['success' => false, 'message' => 'Chiave segreta JWT non configurata']);
    exit;
}

$authHeader = '';

if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
}

if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
}

$token = str_replace('Bearer ', '', $authHeader);

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token mancante']);
    exit;
}

try {
    $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
    $user_id = $decoded->user_id;

    $stmt = $conn->prepare("
    SELECT u.id, u.username, u.mail, 
           u.university_id, u.faculty_id,   
           un.name AS university, 
           f.name AS faculty
    FROM users u
    JOIN universities un ON u.university_id = un.id
    JOIN faculties f ON u.faculty_id = f.id
    WHERE u.id = ?
");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'mail' => $user['mail'],
            'university' => $user['university'],
            'faculty' => $user['faculty'],
            'university_id' => $user['university_id'], 
            'faculty_id' => $user['faculty_id']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token non valido; ' . $e->getMessage()]);
    exit;
}
