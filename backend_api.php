<?php
include('db.php');

// Handle AJAX requests
$action = $_POST['action'] ?? $_GET['action'] ?? null;

switch ($action) {
    case 'borrow':
        borrowBook($pdo);
        break;
    case 'extend':
        extendBorrow($pdo);
        break;
    case 'return':
        returnBook($pdo);
        break;
    case 'fetch':
        fetchBorrowData($pdo);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function borrowBook($pdo) {
    $userID = $_POST['userID'];
    $bookID = $_POST['bookID'];
    $borrowDate = $_POST['borrowDate'];

    // Insert into Borrow table
    $stmt = $pdo->prepare("INSERT INTO Borrow (userID, bookID, borrowDate) VALUES (:userID, :bookID, :borrowDate)");
    $stmt->execute(['userID' => $userID, 'bookID' => $bookID, 'borrowDate' => $borrowDate]);

    // Update book availableCopies
    $stmt = $pdo->prepare("UPDATE Book SET availableCopies = availableCopies - 1 WHERE bookID = :bookID");
    $stmt->execute(['bookID' => $bookID]);

    echo json_encode(['message' => 'Book borrowed successfully']);
}

function extendBorrow($pdo) {
    $borrowID = $_POST['borrowID'];
    $stmt = $pdo->prepare("UPDATE ExtendBorrow SET status = 'approved' WHERE borrowID = :borrowID");
    $stmt->execute(['borrowID' => $borrowID]);

    echo json_encode(['message' => 'Borrow period extended successfully']);
}

function returnBook($pdo) {
    $borrowID = $_POST['borrowID'];
    $stmt = $pdo->prepare("UPDATE Borrow SET status = 'returned' WHERE borrowID = :borrowID");
    $stmt->execute(['borrowID' => $borrowID]);

    // Update book availableCopies
    $stmt = $pdo->prepare("UPDATE Book SET availableCopies = availableCopies + 1 WHERE bookID IN (SELECT bookID FROM Borrow WHERE borrowID = :borrowID)");
    $stmt->execute(['borrowID' => $borrowID]);

    // Insert into Return table
    $stmt = $pdo->prepare("INSERT INTO Return (borrowID, status) VALUES (:borrowID, 'returned')");
    $stmt->execute(['borrowID' => $borrowID]);

    echo json_encode(['message' => 'Book returned successfully']);
}

function fetchBorrowData($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM Borrow INNER JOIN Book ON Borrow.bookID = Book.bookID WHERE Borrow.status = 'pending'");
    $stmt->execute();
    $borrowList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['borrowList' => $borrowList]);
}
?>
