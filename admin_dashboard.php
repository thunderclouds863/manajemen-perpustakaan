<?php
require_once 'auth.php';
rbti_require_admin();
require_once 'db_connect.php';

$totalBooks = 0;
$pendingUsers = 0;
$pendingBorrows = 0;
$activeBorrows = 0;

if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM Book")) {
    $totalBooks = (int) ($res->fetch_assoc()['cnt'] ?? 0);
}
if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE status = 'pending'")) {
    $pendingUsers = (int) ($res->fetch_assoc()['cnt'] ?? 0);
}
if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM Borrow WHERE status IN ('borrow pending', 'extend pending', 'return pending')")) {
    $pendingBorrows = (int) ($res->fetch_assoc()['cnt'] ?? 0);
}
if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM Borrow WHERE status IN ('borrowed', 'extended', 'overdue')")) {
    $activeBorrows = (int) ($res->fetch_assoc()['cnt'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin — Ruang Baca Teknik Industri UNDIP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="assets/css/rbti.css" rel="stylesheet">
</head>
<body class="rbti-has-nav">
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top rbti-navbar">
      <div class="container">
        <a class="rbti-brand" href="admin_dashboard.php">
          <img src="undip.png" alt="Logo UNDIP" class="rbti-brand-logo">
          <span class="rbti-brand-text">
            <span class="rbti-brand-title">RBTI UNDIP</span>
            <span class="rbti-brand-sub">Admin Panel</span>
          </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link active" href="admin_dashboard.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="manajemen-user.php"><i class="fas fa-users"></i> Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="manajemen_buku.php"><i class="fas fa-book"></i> Books</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="peminjaman_buku.php"><i class="fas fa-bookmark"></i> Borrowing</a>
            </li>
            <li class="nav-item">
              <a class="nav-link rbti-nav-logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main class="rbti-main">
    <section class="rbti-hero">
      <div class="container rbti-hero-inner">
        <div class="row align-items-center g-4 g-lg-5">
          <div class="col-lg-7">
            <div class="rbti-eyebrow">
              <i class="fas fa-shield-halved"></i> Panel Administrasi Perpustakaan
            </div>
            <h1 class="rbti-hero-title">Ruang Baca<br>Teknik Industri</h1>
            <p class="rbti-hero-lead">
              Kelola inventaris koleksi buku, verifikasi pendaftaran anggota baru, serta konfirmasi transaksi peminjaman, perpanjangan, dan pengembalian.
            </p>
            <div class="rbti-hero-actions">
              <a href="manajemen_buku.php" class="rbti-btn-gold">
                <i class="fas fa-book"></i> Kelola Buku
              </a>
              <a href="peminjaman_buku.php" class="rbti-btn-ghost-light">
                <i class="fas fa-clipboard-check"></i> Antrean Peminjaman (<?= $pendingBorrows ?>)
              </a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="rbti-hero-visual">
              <div class="rbti-hero-visual-header">
                <span class="rbti-hero-visual-label">Ringkasan Operasional</span>
                <i class="fas fa-chart-simple" style="color: var(--rbti-gold-400);"></i>
              </div>
              <div class="rbti-hero-metrics">
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val"><?= $totalBooks ?></div>
                  <div class="rbti-hero-metric-key">Total Judul Buku</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val"><?= $pendingUsers ?></div>
                  <div class="rbti-hero-metric-key">Anggota Pending</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val"><?= $pendingBorrows ?></div>
                  <div class="rbti-hero-metric-key">Permintaan Sirkulasi</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val"><?= $activeBorrows ?></div>
                  <div class="rbti-hero-metric-key">Peminjaman Aktif</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-section-header">
          <div>
            <div class="rbti-section-kicker">Manajemen Utama</div>
            <h2 class="rbti-section-title">Akses Modul Admin</h2>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4">
            <a href="manajemen_buku.php" class="rbti-service-card">
              <div class="rbti-service-icon">
                <i class="fas fa-book"></i>
              </div>
              <h3>Manajemen Buku</h3>
              <p>Kelola daftar buku, jumlah salinan tersedia, kategori, dan unggah data CSV.</p>
              <span class="rbti-service-cta">Kelola Koleksi <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>

          <div class="col-md-4">
            <a href="manajemen-user.php" class="rbti-service-card">
              <div class="rbti-service-icon rbti-service-icon--gold">
                <i class="fas fa-users"></i>
              </div>
              <h3>Manajemen Anggota</h3>
              <p>Tinjau dan setujui pendaftaran anggota mahasiswa agar dapat meminjam buku.</p>
              <span class="rbti-service-cta">Kelola Anggota <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>

          <div class="col-md-4">
            <a href="peminjaman_buku.php" class="rbti-service-card">
              <div class="rbti-service-icon rbti-service-icon--emerald">
                <i class="fas fa-bookmark"></i>
              </div>
              <h3>Peminjaman &amp; Pengembalian</h3>
              <p>Konfirmasi atau tolak permintaan peminjaman, perpanjangan, dan pengembalian.</p>
              <span class="rbti-service-cta">Kelola Sirkulasi <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="rbti-footer">
    <div class="container rbti-footer-inner">
      <span><strong>Ruang Baca Teknik Industri</strong> · Universitas Diponegoro</span>
      <span>&copy; Kelompok 02 APSI 2024 - RBTI UNDIP. All Rights Reserved.</span>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
