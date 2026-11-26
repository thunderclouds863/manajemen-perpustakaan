<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchQuery = '%' . trim($_POST['query'] ?? '') . '%';

    $result_count = $conn->query("SELECT COUNT(*) AS total FROM Book");
    $totalRecords = (int) $result_count->fetch_assoc()['total'];

    $stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM Book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ?");
    $stmt_count->bind_param('sss', $searchQuery, $searchQuery, $searchQuery);
    $stmt_count->execute();
    $totalFiltered = (int) $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();

    $stmt = $conn->prepare("SELECT * FROM Book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ? ORDER BY bookID DESC");
    $stmt->bind_param('sss', $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalFiltered,
        'data' => $books,
    ]);
    exit;
}
