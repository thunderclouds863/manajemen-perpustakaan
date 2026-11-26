<?php
require_once 'auth.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($nim === '' || $password === '') {
        $_SESSION['error'] = 'NIM dan password wajib diisi.';
        header('Location: register.php#member-section');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nim = :nim LIMIT 1");
        $stmt->execute(['nim' => $nim]);
        $user = $stmt->fetch();

        if ($user && rbti_verify_password($password, (string) $user['password'])) {
            if ($user['status'] !== 'approved') {
                $_SESSION['error'] = 'Akun Anda sedang menunggu persetujuan admin.';
                header('Location: register.php#member-section');
                exit();
            }

            // Upgrade legacy plaintext password to bcrypt/argon2 hash
            if (rbti_needs_rehash((string) $user['password'])) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = :pw WHERE id = :id");
                $upd->execute(['pw' => $newHash, 'id' => $user['id']]);
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_nim'] = $user['nim'];
            unset($_SESSION['admin_logged_in'], $_SESSION['visitor_logged_in']);

            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'NIM atau password tidak valid.';
            header('Location: register.php#member-section');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        header('Location: register.php#member-section');
        exit();
    }
} else {
    header('Location: register.php#member-section');
    exit();
}
