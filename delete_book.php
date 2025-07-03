<?php
include 'db.php';

$id = $_POST['id'];

// Query untuk menghapus data berdasarkan bookID
$query = "DELETE FROM Book WHERE bookID = :id";

// Menyiapkan statement menggunakan PDO
$stmt = $pdo->prepare($query);

// Mengikat parameter menggunakan bindParam untuk PDO
$stmt->bindParam(':id', $id, PDO::PARAM_INT);

// Menjalankan query
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>
