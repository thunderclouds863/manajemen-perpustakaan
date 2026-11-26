<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Masukkan nama lengkap dan alamat email yang valid.';
        header('Location: register.php');
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO visitor (name, email) VALUES (:name, :email)");
        $stmt->execute(['name' => $name, 'email' => $email]);

        session_regenerate_id(true);
        $_SESSION['visitor_logged_in'] = true;
        $_SESSION['visitor_name'] = $name;
        $_SESSION['message'] = 'Selamat datang, ' . htmlspecialchars($name) . '! Kunjungan Anda telah dicatat.';

        header('Location: index-visitor.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan saat mencatat kunjungan. Silakan coba lagi.';
        header('Location: register.php');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
