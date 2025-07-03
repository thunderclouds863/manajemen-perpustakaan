<?php
header('Content-Type: application/json');

try {
  $conn = new mysqli('localhost', 'root', '', 'rbti');

  if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
  }

  // Handle GET requests (fetch data)
  if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

    $sql = "SELECT b.*, u.name AS userName, bk.title AS bookTitle
            FROM borrow b
            LEFT JOIN users u ON b.userID = u.id
            LEFT JOIN book bk ON b.bookID = bk.bookID
            WHERE b.userID LIKE '%$search%'
               OR b.bookID LIKE '%$search%'
               OR u.name LIKE '%$search%'
               OR bk.title LIKE '%$search%'
            ORDER BY b.borrowDate DESC";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
    }

    echo json_encode($data);
    exit;
  }

  // Handle POST requests (actions)
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = strtolower(trim($_POST['action'] ?? ''));
    $borrowID = $conn->real_escape_string($_POST['borrowID'] ?? '');

    if (empty($borrowID)) {
      echo json_encode(['success' => false, 'message' => 'Borrow ID is missing']);
      exit;
    }

    switch ($action) {
      case 'borrowing':
        $conn->begin_transaction();
        try {
          // Update borrow status to 'borrowed'
          $sql = "UPDATE borrow SET status = 'borrowed' WHERE borrowID = '$borrowID'";
          if (!$conn->query($sql)) {
            throw new Exception("Error confirming borrowing");
          }

          // Get bookID and availableCopies
          $borrowQuery = $conn->query("SELECT bookID FROM borrow WHERE borrowID = '$borrowID'");
          $borrow = $borrowQuery->fetch_assoc();
          if (!$borrow) {
            throw new Exception("Borrow record not found");
          }

          $bookID = $borrow['bookID'];

          // Update book availability (decrease availableCopies)
          $updateBook = "UPDATE book
                         SET availableCopies = availableCopies - 1,
                             status = CASE WHEN availableCopies - 1 < 1 THEN 'unavailable' ELSE status END
                         WHERE bookID = '$bookID' AND availableCopies > 0";
          if (!$conn->query($updateBook)) {
            throw new Exception("Error updating book availability");
          }

          $conn->commit();
          echo json_encode(['success' => true, 'message' => 'Borrowing confirmed successfully']);
        } catch (Exception $e) {
          $conn->rollback();
          echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

      case 'extending':
        $sql = "UPDATE borrow
                SET returnDate = DATE_ADD(returnDate, INTERVAL 14 DAY),
                    status = 'extended'
                WHERE borrowID = '$borrowID'";
        if ($conn->query($sql)) {
          echo json_encode(['success' => true, 'message' => 'Extension confirmed successfully']);
        } else {
          echo json_encode(['success' => false, 'message' => 'Error confirming extension']);
        }
        break;

      case 'returning':
        $conn->begin_transaction();
        try {
          // Get bookID
          $borrowQuery = $conn->query("SELECT bookID FROM borrow WHERE borrowID = '$borrowID'");
          $borrow = $borrowQuery->fetch_assoc();

          if (!$borrow) {
            throw new Exception("Borrow record not found");
          }

          $bookID = $borrow['bookID'];

          // Update borrow status to 'returned'
          $updateBorrow = "UPDATE borrow SET status = 'returned' WHERE borrowID = '$borrowID'";
          if (!$conn->query($updateBorrow)) {
            throw new Exception("Error updating borrow status");
          }

          // Update book availability (increase availableCopies)
          $updateBook = "UPDATE book
                         SET availableCopies = availableCopies + 1,
                             status = CASE WHEN availableCopies + 1 > 0 THEN 'available' ELSE status END
                         WHERE bookID = '$bookID'";
          if (!$conn->query($updateBook)) {
            throw new Exception("Error updating book availability");
          }

          $conn->commit();
          echo json_encode(['success' => true, 'message' => 'Returning confirmed successfully']);
        } catch (Exception $e) {
          $conn->rollback();
          echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

      default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
    }
  }
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
  if (isset($conn)) {
    $conn->close();
  }
}
?>
