<?php

header('Content-Type: application/json');


$host = 'sql309.infinityfree.com';               
$user = 'if0_39269908';                 
$password = 'patturetto123';    
$dbname = 'if0_39269908_scuolaribelle'; 

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore di connessione al database.']);
    exit;
}
