<?php
require_once 'auth.php';
require_once 'db.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$errorMsg = null;
$tokenValid = false;
$resetRow = null;

if ($token !== '') {
    try {
        rbti_ensure_password_resets_table($pdo);
        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare(
            "SELECT pr.id, pr.user_id, u.name
             FROM password_resets pr
             JOIN users u ON pr.user_id = u.id
             WHERE pr.token_hash = :th AND pr.used_at IS NULL AND pr.expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([':th' => $tokenHash]);
        $resetRow = $stmt->fetch();
        $tokenValid = (bool) $resetRow;
    } catch (Exception $e) {
        $errorMsg = 'Terjadi kesalahan saat memvalidasi token.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid && $resetRow) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password_confirm'] ?? '';

    if (strlen($newPassword) < 6) {
        $errorMsg = 'Password baru minimal terdiri dari 6 karakter.';
    } elseif (!hash_equals($newPassword, $confirmPassword)) {
        $errorMsg = 'Konfirmasi password tidak cocok.';
    } else {
        try {
            $pdo->beginTransaction();

            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $u1 = $pdo->prepare("UPDATE users SET password = :pw WHERE id = :uid");
            $u1->execute([':pw' => $hash, ':uid' => $resetRow['user_id']]);

            $u2 = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
            $u2->execute([':id' => $resetRow['id']]);

            $pdo->commit();

            $_SESSION['success'] = 'Password berhasil diperbarui. Silakan login dengan password baru Anda.';
            header('Location: register.php#member-section');
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errorMsg = 'Gagal memperbarui password. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atur Password Baru — Ruang Baca Teknik Industri UNDIP</title>
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
        <h1 class="rbti-auth-title">Atur Password Baru</h1>
        <p class="rbti-auth-subtitle">Ruang Baca Teknik Industri · UNDIP</p>
      </div>

      <?php if ($errorMsg): ?>
        <div class="rbti-auth-alert rbti-auth-alert--error" role="alert">
          <i class="fas fa-circle-exclamation mt-1"></i>
          <span><?= htmlspecialchars($errorMsg) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!$tokenValid): ?>
        <div class="rbti-auth-alert rbti-auth-alert--error" role="alert">
          <i class="fas fa-triangle-exclamation mt-1"></i>
          <span>Tautan reset password tidak valid atau sudah kedaluwarsa. Silakan ajukan permintaan reset baru.</span>
        </div>
        <div class="rbti-auth-switch">
          <a href="forgot-password.php" class="rbti-auth-link-btn">Minta Tautan Reset Baru</a>
        </div>
      <?php else: ?>
        <form method="POST" action="reset-password.php">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <div class="rbti-field">
            <label for="new-password">Password Baru</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" id="new-password" name="password" placeholder="Minimal 6 karakter" minlength="6" required autocomplete="new-password">
              <button type="button" class="rbti-pw-toggle" data-target="new-password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="rbti-field">
            <label for="confirm-password">Konfirmasi Password Baru</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" id="confirm-password" name="password_confirm" placeholder="Ulangi password baru" minlength="6" required autocomplete="new-password">
              <button type="button" class="rbti-pw-toggle" data-target="confirm-password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <button type="submit" class="rbti-auth-submit">
            <i class="fas fa-check"></i> Simpan Password Baru
          </button>
        </form>
      <?php endif; ?>
    </div>

    <p class="rbti-auth-footer">&copy; 2024 Kelompok 02 APSI · RBTI Universitas Diponegoro</p>
  </main>

  <script>
    document.querySelectorAll('.rbti-pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        const isPw = input.type === 'password';
        input.type = isPw ? 'text' : 'password';
        btn.innerHTML = isPw ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
      });
    });
  </script>
</body>
</html>
