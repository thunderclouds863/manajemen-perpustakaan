<?php
require_once 'db_connect.php';

if (isset($_POST['bookTitle'])) {
    $bookTitle = $_POST['bookTitle'];

    // Query to check if there are multiple books with the same title
    $result = $conn->query("SELECT bookID, title, COUNT(*) AS count FROM Book WHERE title LIKE '%$bookTitle%' GROUP BY title");

    $book = $result->fetch_assoc();

    if ($book['count'] > 1) {
        echo json_encode(['success' => false, 'message' => 'Multiple books found with the same title. Please use the Book ID to specify.']);
    } else {
        echo json_encode(['success' => true]);
    }
}
?>
