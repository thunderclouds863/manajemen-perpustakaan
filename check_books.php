<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if (isset($_POST['bookTitle'])) {
    $bookTitle = '%' . trim($_POST['bookTitle']) . '%';

    $stmt = $conn->prepare("SELECT bookID, title, COUNT(*) AS count FROM Book WHERE title LIKE ? GROUP BY title LIMIT 1");
    $stmt->bind_param('s', $bookTitle);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($book && (int) $book['count'] > 1) {
        echo json_encode(['success' => false, 'message' => 'Multiple books found with the same title. Please use the Book ID to specify.']);
    } else {
        echo json_encode(['success' => true]);
    }
}
