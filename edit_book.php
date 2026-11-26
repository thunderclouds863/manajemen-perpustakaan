<?php
require_once 'auth.php';
rbti_require_admin(true);
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$totalCopies = (int) ($_POST['totalCopies'] ?? 0);
$availableCopies = (int) ($_POST['availableCopies'] ?? 0);

if ($id <= 0 || $title === '' || $author === '' || $genre === '' || $totalCopies < 0 || $availableCopies < 0 || $availableCopies > $totalCopies) {
    http_response_code(400);
    echo 'Data buku tidak valid. Pastikan salinan tersedia tidak melebihi total salinan.';
    exit;
}

$status = ($availableCopies === 0) ? 'unavailable' : 'available';

$query = "UPDATE Book
          SET title = :title, author = :author, genre = :genre, totalCopies = :totalCopies, availableCopies = :availableCopies, status = :status
          WHERE bookID = :id";
$stmt = $pdo->prepare($query);

if ($stmt->execute([
    ':title' => $title,
    ':author' => $author,
    ':genre' => $genre,
    ':totalCopies' => $totalCopies,
    ':availableCopies' => $availableCopies,
    ':status' => $status,
    ':id' => $id,
])) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Gagal memperbarui buku.';
}
