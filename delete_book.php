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
if ($id <= 0) {
    http_response_code(400);
    echo 'ID buku tidak valid.';
    exit;
}

try {
    // Check if book has borrow records
    $chk = $pdo->prepare("SELECT COUNT(*) FROM Borrow WHERE bookID = :id");
    $chk->execute([':id' => $id]);
    if ((int) $chk->fetchColumn() > 0) {
        http_response_code(409);
        echo 'Buku tidak dapat dihapus karena masih memiliki riwayat peminjaman.';
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM Book WHERE bookID = :id");
    $stmt->execute([':id' => $id]);
    echo 'success';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Gagal menghapus buku.';
}
