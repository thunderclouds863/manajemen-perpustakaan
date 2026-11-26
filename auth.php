<?php
/**
 * Shared Authentication, RBAC, Password, CSRF, and Notification Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function rbti_is_admin(): bool {
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['username']);
}

function rbti_is_member(): bool {
    return !empty($_SESSION['user_id']);
}

function rbti_is_visitor(): bool {
    return !empty($_SESSION['visitor_logged_in']);
}

function rbti_require_admin(bool $jsonResponse = false): void {
    if (!rbti_is_admin()) {
        if ($jsonResponse) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak. Login admin diperlukan.']);
            exit;
        }
        $_SESSION['error'] = 'Silakan login sebagai admin untuk mengakses halaman ini.';
        header('Location: register.php#admin-section');
        exit;
    }
}

function rbti_require_member(bool $jsonResponse = false): void {
    if (!rbti_is_member()) {
        if ($jsonResponse) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesi berakhir. Silakan login kembali.']);
            exit;
        }
        $_SESSION['error'] = 'Silakan login sebagai anggota untuk mengakses halaman ini.';
        header('Location: register.php#member-section');
        exit;
    }
}

function rbti_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function rbti_verify_password(string $inputPassword, string $storedPassword): bool {
    if ($storedPassword === '') {
        return false;
    }
    // Support modern password_hash() values
    $info = password_get_info($storedPassword);
    if (!empty($info['algo'])) {
        return password_verify($inputPassword, $storedPassword);
    }
    // Backward compatibility for legacy/seeded plaintext accounts
    return hash_equals($storedPassword, $inputPassword);
}

function rbti_needs_rehash(string $storedPassword): bool {
    $info = password_get_info($storedPassword);
    return empty($info['algo']) || password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
}

function rbti_ensure_password_resets_table(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token_hash (token_hash),
            INDEX idx_user_id (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
}

function rbti_notify_user(mysqli $conn, int $userID, string $message): void {
    $stmt = $conn->prepare("INSERT INTO Notification (userID, message, status) VALUES (?, ?, 'unread')");
    if ($stmt) {
        $stmt->bind_param('is', $userID, $message);
        $stmt->execute();
        $stmt->close();
    }
}
