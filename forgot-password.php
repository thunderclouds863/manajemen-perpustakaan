<?php
require_once 'auth.php';
require_once 'db.php';

$submitted = false;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if ($identifier === '') {
        $errorMsg = 'Masukkan NIM atau alamat email yang terdaftar.';
    } else {
        try {
            rbti_ensure_password_resets_table($pdo);

            $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE (nim = :id1 OR email = :id2) AND status = 'approved' LIMIT 1");
            $stmt->execute([':id1' => $identifier, ':id2' => $identifier]);
            $user = $stmt->fetch();

            if ($user) {
                // Invalidate previous unused tokens for this user
                $inv = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = :uid AND used_at IS NULL");
                $inv->execute([':uid' => $user['id']]);

                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

                $ins = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :th, :exp)");
                $ins->execute([
                    ':uid' => $user['id'],
                    ':th' => $tokenHash,
                    ':exp' => $expiresAt,
                ]);

                $baseUrl = rtrim(getenv('APP_URL') ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:10000')), '/');
                $resetLink = $baseUrl . '/reset-password.php?token=' . urlencode($rawToken);

                // Local file mail outbox (safe dev/demo transport without exposing user existence in HTTP response)
                $logDir = __DIR__ . '/logs';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0750, true);
                }
                $entry = sprintf(
                    "[%s] TO: %s | SUBJECT: Reset Password RBTI UNDIP | LINK: %s | EXPIRES: %s\n",
                    date('Y-m-d H:i:s'),
                    $user['email'],
                    $resetLink,
                    $expiresAt
                );
                @file_put_contents($logDir . '/mail_outbox.log', $entry, FILE_APPEND | LOCK_EX);

                if (getenv('MAIL_TRANSPORT') === 'sendmail') {
                    @mail(
                        $user['email'],
                        'Reset Password — Ruang Baca Teknik Industri UNDIP',
                        "Halo {$user['name']},\n\nGunakan tautan berikut untuk mengatur ulang password Anda (berlaku 30 menit):\n{$resetLink}\n\nAbaikan email ini jika Anda tidak meminta reset password."
                    );
                }
            }

            // Always show a uniform non-enumerating confirmation message
            $submitted = true;
        } catch (Exception $e) {
            $errorMsg = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password — Ruang Baca Teknik Industri UNDIP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/rbti.css">
</head>
<body class="rbti-auth-page">
  <main class="rbti-auth-shell">
    <div class="rbti-auth-card">
      <div class="rbti-auth-header">
        <img src="undip.png" alt="Logo Universitas Diponegoro" class="rbti-auth-emblem">
        <h1 class="rbti-auth-title">Reset Password Anggota</h1>
        <p class="rbti-auth-subtitle">Masukkan NIM atau email terdaftar untuk menerima tautan reset</p>
      </div>

      <?php if ($errorMsg): ?>
        <div class="rbti-auth-alert rbti-auth-alert--error" role="alert">
          <i class="fas fa-circle-exclamation mt-1"></i>
          <span><?= htmlspecialchars($errorMsg) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($submitted): ?>
        <div class="rbti-auth-alert rbti-auth-alert--success" role="status">
          <i class="fas fa-circle-check mt-1"></i>
          <span>Jika NIM atau email tersebut terdaftar pada akun anggota aktif, tautan reset password (berlaku 30 menit) telah dikirimkan.</span>
        </div>
      <?php else: ?>
        <form method="POST" action="forgot-password.php">
          <div class="rbti-field">
            <label for="identifier">NIM atau Email</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-envelope"></i>
              <input type="text" id="identifier" name="identifier" placeholder="Masukkan NIM atau email terdaftar" required autofocus>
            </div>
          </div>
          <button type="submit" class="rbti-auth-submit">
            <i class="fas fa-paper-plane"></i> Kirim Tautan Reset
          </button>
        </form>
      <?php endif; ?>

      <div class="rbti-auth-switch">
        <a href="register.php#member-section" class="rbti-auth-link-btn">
          <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Login
        </a>
      </div>
    </div>

    <p class="rbti-auth-footer">&copy; 2024 Kelompok 02 APSI · RBTI Universitas Diponegoro</p>
  </main>
</body>
</html>
