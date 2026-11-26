<?php
require_once 'auth.php';
rbti_require_admin(true);
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$totalCopies = (int) ($_POST['totalCopies'] ?? 0);
$availableCopies = (int) ($_POST['availableCopies'] ?? 0);

if ($title === '' || $author === '' || $genre === '' || $totalCopies < 0 || $availableCopies < 0 || $availableCopies > $totalCopies) {
    http_response_code(400);
    echo 'Data buku tidak valid. Pastikan salinan tersedia tidak melebihi total salinan.';
    exit;
}

$status = ($availableCopies === 0) ? 'unavailable' : 'available';

$query = "INSERT INTO Book (title, author, genre, totalCopies, availableCopies, status)
          VALUES (:title, :author, :genre, :totalCopies, :availableCopies, :status)";
$stmt = $pdo->prepare($query);

if ($stmt->execute([
    ':title' => $title,
    ':author' => $author,
    ':genre' => $genre,
    ':totalCopies' => $totalCopies,
    ':availableCopies' => $availableCopies,
    ':status' => $status,
])) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Gagal menambahkan buku.';
}
