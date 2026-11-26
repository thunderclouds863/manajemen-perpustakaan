<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $_SESSION['error'] = 'Username dan password admin wajib diisi.';
        header('Location: register.php#admin-section');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && rbti_verify_password($password, (string) $admin['password'])) {
            if (rbti_needs_rehash((string) $admin['password'])) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE admins SET password = :pw WHERE id = :id");
                $upd->execute(['pw' => $newHash, 'id' => $admin['id']]);
            }

            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['username'] = $admin['username'];
            unset($_SESSION['user_id'], $_SESSION['visitor_logged_in']);

            header('Location: admin_dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Username atau password admin tidak valid.';
            header('Location: register.php#admin-section');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        header('Location: register.php#admin-section');
        exit();
    }
} else {
    header('Location: register.php#admin-section');
    exit();
}
