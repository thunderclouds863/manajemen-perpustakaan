<?php
require_once 'auth.php';
require_once 'db_connect.php';

$searchQuery = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';

$sql = "SELECT * FROM Book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ? ORDER BY bookID DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $searchQuery, $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

header('Content-Type: application/json');
echo json_encode($books);

$stmt->close();
$conn->close();
