<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $nim === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Mohon lengkapi nama, NIM, dan alamat email yang valid.';
        header('Location: register.php#register-section');
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password minimal terdiri dari 6 karakter.';
        header('Location: register.php#register-section');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nim = :nim OR email = :email LIMIT 1");
        $stmt->execute(['nim' => $nim, 'email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'NIM atau email sudah terdaftar. Silakan login atau gunakan reset password.';
            header('Location: register.php#register-section');
            exit();
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, nim, email, password, status) VALUES (:name, :nim, :email, :password, 'pending')");
        $stmt->execute([
            'name' => $name,
            'nim' => $nim,
            'email' => $email,
            'password' => $passwordHash,
        ]);

        $_SESSION['success'] = 'Pendaftaran berhasil! Silakan menunggu persetujuan admin sebelum login.';
        header('Location: register.php#member-section');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
        header('Location: register.php#register-section');
        exit();
    }
} else {
    header('Location: register.php#register-section');
    exit();
}
