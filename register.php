<?php
require_once 'auth.php';
$flashError = $_SESSION['error'] ?? null;
$flashSuccess = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — Ruang Baca Teknik Industri UNDIP</title>
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
        <h1 class="rbti-auth-title">Ruang Baca Teknik Industri</h1>
        <p class="rbti-auth-subtitle">Fakultas Teknik · Universitas Diponegoro</p>
      </div>

      <?php if ($flashError): ?>
        <div class="rbti-auth-alert rbti-auth-alert--error" role="alert">
          <i class="fas fa-circle-exclamation mt-1"></i>
          <span><?= htmlspecialchars($flashError) ?></span>
        </div>
      <?php endif; ?>

      <?php if ($flashSuccess): ?>
        <div class="rbti-auth-alert rbti-auth-alert--success" role="status">
          <i class="fas fa-circle-check mt-1"></i>
          <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
      <?php endif; ?>

      <div class="rbti-role-tabs" role="tablist" aria-label="Pilih peran masuk">
        <button type="button" class="rbti-role-tab active" data-target="visitor" role="tab" aria-selected="true" id="tab-visitor">
          <i class="fas fa-user"></i> Pengunjung
        </button>
        <button type="button" class="rbti-role-tab" data-target="member" role="tab" aria-selected="false" id="tab-member">
          <i class="fas fa-user-graduate"></i> Anggota
        </button>
        <button type="button" class="rbti-role-tab" data-target="admin" role="tab" aria-selected="false" id="tab-admin">
          <i class="fas fa-user-shield"></i> Admin
        </button>
      </div>

      <!-- Visitor Section -->
      <section class="rbti-auth-section active" id="visitor-section" aria-labelledby="tab-visitor">
        <form id="visitor-form" action="login-visitor.php" method="POST">
          <div class="rbti-field">
            <label for="visitor-name">Nama Lengkap</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-user"></i>
              <input type="text" id="visitor-name" name="name" placeholder="Masukkan nama lengkap" required autocomplete="name">
            </div>
          </div>
          <div class="rbti-field">
            <label for="visitor-email">Email</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" id="visitor-email" name="email" placeholder="nama@email.com" required autocomplete="email">
            </div>
          </div>
          <button type="submit" class="rbti-auth-submit">
            <i class="fas fa-arrow-right-to-bracket"></i> Masuk sebagai Pengunjung
          </button>
        </form>
      </section>

      <!-- Member Login Section -->
      <section class="rbti-auth-section" id="member-section" aria-labelledby="tab-member">
        <form id="member-form" action="login.php" method="POST">
          <div class="rbti-field">
            <label for="member-nim">ID Anggota / NIM</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-id-card"></i>
              <input type="text" id="member-nim" name="nim" placeholder="Masukkan NIM" required autocomplete="username">
            </div>
          </div>
          <div class="rbti-field">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
              <label for="member-password" style="margin-bottom:0;">Password</label>
              <a href="forgot-password.php" style="font-size:0.78rem; font-weight:600; text-decoration:none;">Lupa password?</a>
            </div>
            <div class="rbti-input-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" id="member-password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
              <button type="button" class="rbti-pw-toggle" data-target="member-password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <button type="submit" class="rbti-auth-submit">
            <i class="fas fa-arrow-right-to-bracket"></i> Masuk sebagai Anggota
          </button>
        </form>
        <div class="rbti-auth-switch">
          Belum terdaftar sebagai anggota?
          <button type="button" class="rbti-auth-link-btn" id="go-to-register">Daftar Baru</button>
        </div>
      </section>

      <!-- Member Registration Section -->
      <section class="rbti-auth-section" id="register-section" aria-label="Pendaftaran Anggota Baru">
        <form id="register-form" action="register-process.php" method="POST">
          <div class="rbti-field">
            <label for="reg-name">Nama Lengkap</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-user"></i>
              <input type="text" id="reg-name" name="name" placeholder="Nama lengkap mahasiswa" required autocomplete="name">
            </div>
          </div>
          <div class="rbti-field">
            <label for="reg-nim">NIM</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-id-badge"></i>
              <input type="text" id="reg-nim" name="nim" placeholder="Nomor Induk Mahasiswa" required>
            </div>
          </div>
          <div class="rbti-field">
            <label for="reg-email">Email</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" id="reg-email" name="email" placeholder="nama@students.undip.ac.id" required autocomplete="email">
            </div>
          </div>
          <div class="rbti-field">
            <label for="reg-password">Password</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" id="reg-password" name="password" placeholder="Minimal 6 karakter" minlength="6" required autocomplete="new-password">
              <button type="button" class="rbti-pw-toggle" data-target="reg-password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <button type="submit" class="rbti-auth-submit">
            <i class="fas fa-user-plus"></i> Daftar Anggota Baru
          </button>
        </form>
        <div class="rbti-auth-switch">
          Sudah memiliki akun?
          <button type="button" class="rbti-auth-link-btn" id="back-to-member">Kembali ke Login Anggota</button>
        </div>
      </section>

      <!-- Admin Login Section -->
      <section class="rbti-auth-section" id="admin-section" aria-labelledby="tab-admin">
        <form id="admin-form" action="admin-login.php" method="POST">
          <div class="rbti-field">
            <label for="admin-username">Username Admin</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-user-shield"></i>
              <input type="text" id="admin-username" name="username" placeholder="Masukkan username admin" required autocomplete="username">
            </div>
          </div>
          <div class="rbti-field">
            <label for="admin-password">Password</label>
            <div class="rbti-input-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" id="admin-password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
              <button type="button" class="rbti-pw-toggle" data-target="admin-password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <button type="submit" class="rbti-auth-submit">
            <i class="fas fa-shield-halved"></i> Masuk sebagai Admin
          </button>
        </form>
      </section>
    </div>

    <p class="rbti-auth-footer">&copy; 2024 Kelompok 02 APSI · RBTI Universitas Diponegoro</p>
  </main>

  <script>
    (function () {
      const sections = {
        visitor: document.getElementById('visitor-section'),
        member: document.getElementById('member-section'),
        register: document.getElementById('register-section'),
        admin: document.getElementById('admin-section')
      };

      const tabs = document.querySelectorAll('.rbti-role-tab');

      function showSection(key) {
        if (!sections[key]) key = 'visitor';
        Object.keys(sections).forEach(function (k) {
          sections[k].classList.toggle('active', k === key);
        });
        const activeTabKey = key === 'register' ? 'member' : key;
        tabs.forEach(function (tab) {
          const isMatch = tab.dataset.target === activeTabKey;
          tab.classList.toggle('active', isMatch);
          tab.setAttribute('aria-selected', isMatch ? 'true' : 'false');
        });
      }

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          showSection(tab.dataset.target);
        });
      });

      document.getElementById('go-to-register').addEventListener('click', function () {
        showSection('register');
      });

      document.getElementById('back-to-member').addEventListener('click', function () {
        showSection('member');
      });

      // Password visibility toggles
      document.querySelectorAll('.rbti-pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const input = document.getElementById(btn.dataset.target);
          if (!input) return;
          const isPw = input.type === 'password';
          input.type = isPw ? 'text' : 'password';
          btn.innerHTML = isPw ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
        });
      });

      function syncFromHash() {
        const hash = window.location.hash;
        if (hash === '#member-section') showSection('member');
        else if (hash === '#register-section') showSection('register');
        else if (hash === '#admin-section') showSection('admin');
      }
      syncFromHash();
      window.addEventListener('hashchange', syncFromHash);

      document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
          const btn = form.querySelector('button[type="submit"]');
          if (btn) {
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memproses...';
            btn.disabled = true;
          }
        });
      });
    })();
  </script>
</body>
</html>
