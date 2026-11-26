<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: register.php');
    exit();
}

$memberName = $_SESSION['user_name'] ?? 'Anggota RBTI';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda Anggota — Ruang Baca Teknik Industri UNDIP</title>
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
        <a class="rbti-brand" href="index.php">
          <img src="undip.png" alt="Logo UNDIP" class="rbti-brand-logo">
          <span class="rbti-brand-text">
            <span class="rbti-brand-title">RBTI UNDIP</span>
            <span class="rbti-brand-sub">Portal Anggota</span>
          </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link active" href="index.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="katalog-pengguna.php"><i class="fas fa-book-open"></i> Catalog</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="pencarian-buku.php"><i class="fas fa-search"></i> Search</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="peminjaman_buku_pengguna.php"><i class="fas fa-bookmark"></i> Borrowings</a>
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
              <i class="fas fa-graduation-cap"></i> Selamat datang, <?= htmlspecialchars($memberName) ?>
            </div>
            <h1 class="rbti-hero-title">Ruang Baca<br>Teknik Industri</h1>
            <p class="rbti-hero-lead">
              Akses katalog koleksi akademik, cari ketersediaan buku secara langsung, dan kelola peminjaman Anda di Fakultas Teknik Universitas Diponegoro.
            </p>
            <div class="rbti-hero-actions">
              <a href="pencarian-buku.php" class="rbti-btn-gold">
                <i class="fas fa-search"></i> Cari Koleksi Buku
              </a>
              <a href="peminjaman_buku_pengguna.php" class="rbti-btn-ghost-light">
                <i class="fas fa-bookmark"></i> Kelola Peminjaman
              </a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="rbti-hero-visual">
              <div class="rbti-hero-visual-header">
                <span class="rbti-hero-visual-label">Ringkasan Layanan Anggota</span>
                <i class="fas fa-book-reader" style="color: var(--rbti-gold-400);"></i>
              </div>
              <div class="rbti-hero-metrics">
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">5 Kategori</div>
                  <div class="rbti-hero-metric-key">Buku, KP, TA &amp; Tesis</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">14 Hari</div>
                  <div class="rbti-hero-metric-key">Masa pinjam standar</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">Perpanjangan</div>
                  <div class="rbti-hero-metric-key">Ajukan langsung online</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">Sirkulasi</div>
                  <div class="rbti-hero-metric-key">Status stok real-time</div>
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
            <div class="rbti-section-kicker">Akses Cepat</div>
            <h2 class="rbti-section-title">Layanan Ruang Baca</h2>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4">
            <a href="katalog-pengguna.php" class="rbti-service-card">
              <div class="rbti-service-icon">
                <i class="fas fa-layer-group"></i>
              </div>
              <h3>Katalog Koleksi</h3>
              <p>Buku sirkulasi, referensi, laporan Kerja Praktik, Tugas Akhir, dan Tesis.</p>
              <span class="rbti-service-cta">Buka Katalog <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>

          <div class="col-md-4">
            <a href="peminjaman_buku_pengguna.php" class="rbti-service-card">
              <div class="rbti-service-icon rbti-service-icon--gold">
                <i class="fas fa-book-bookmark"></i>
              </div>
              <h3>Peminjaman Buku</h3>
              <p>Khusus anggota aktif dengan durasi peminjaman maksimal 2 minggu.</p>
              <span class="rbti-service-cta">Ajukan Peminjaman <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>

          <div class="col-md-4">
            <a href="peminjaman_buku_pengguna.php" class="rbti-service-card">
              <div class="rbti-service-icon rbti-service-icon--emerald">
                <i class="fas fa-rotate-left"></i>
              </div>
              <h3>Pengembalian &amp; Perpanjangan</h3>
              <p>Pantau tenggat waktu, ajukan perpanjangan, atau konfirmasi pengembalian buku.</p>
              <span class="rbti-service-cta">Lihat Riwayat <i class="fas fa-arrow-right"></i></span>
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
