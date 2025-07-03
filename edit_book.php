<?php
include 'db.php';

$id = $_POST['id'];
$title = $_POST['title'];
$author = $_POST['author'];
$genre = $_POST['genre'];
$totalCopies = $_POST['totalCopies'];
$availableCopies = $_POST['availableCopies'];

// Query untuk update data berdasarkan bookID
$query = "UPDATE Book
          SET title = :title, author = :author, genre = :genre, totalCopies = :totalCopies, availableCopies = :availableCopies
          WHERE bookID = :id";

// Menyiapkan statement menggunakan PDO
$stmt = $pdo->prepare($query);

// Mengikat parameter menggunakan bindParam untuk PDO
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->bindParam(':author', $author, PDO::PARAM_STR);
$stmt->bindParam(':genre', $genre, PDO::PARAM_STR);
$stmt->bindParam(':totalCopies', $totalCopies, PDO::PARAM_INT);
$stmt->bindParam(':availableCopies', $availableCopies, PDO::PARAM_INT);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

// Menjalankan query
if ($stmt->execute()) {
    // Query untuk memperbarui status buku berdasarkan jumlah availableCopies
    $statusQuery = "UPDATE Book
                    SET status = CASE
                        WHEN availableCopies = 0 THEN 'unavailable'
                        ELSE 'available'
                    END
                    WHERE bookID = :id";

    $statusStmt = $pdo->prepare($statusQuery);
    $statusStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $statusStmt->execute();

    echo "success";
} else {
    echo "error";
}

?>
