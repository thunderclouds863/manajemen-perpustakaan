<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS');
if ($password === false) {
    $password = '';
}
$dbname = getenv('DB_NAME') ?: 'rbti';

$conn = new mysqli($servername, $username, $password, $dbname, $dbPort);

if ($conn->connect_error) {
    http_response_code(500);
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');
