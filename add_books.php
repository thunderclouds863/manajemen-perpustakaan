<?php
include 'db.php';

$title = $_POST['title'];
$author = $_POST['author'];
$genre = $_POST['genre'];
$totalCopies = $_POST['totalCopies'];
$availableCopies = $_POST['availableCopies'];

// Query untuk menambahkan data
$query = "INSERT INTO Book (title, author, genre, totalCopies, availableCopies)
          VALUES (:title, :author, :genre, :totalCopies, :availableCopies)";

// Menyiapkan statement menggunakan PDO
$stmt = $pdo->prepare($query);

// Mengikat parameter dengan menggunakan bindParam untuk PDO
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->bindParam(':author', $author, PDO::PARAM_STR);
$stmt->bindParam(':genre', $genre, PDO::PARAM_STR);
$stmt->bindParam(':totalCopies', $totalCopies, PDO::PARAM_INT);
$stmt->bindParam(':availableCopies', $availableCopies, PDO::PARAM_INT);

// Menjalankan query
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>
