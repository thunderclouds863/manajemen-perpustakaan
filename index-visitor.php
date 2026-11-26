<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda Pengunjung — Ruang Baca Teknik Industri UNDIP</title>
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
        <a class="rbti-brand" href="index-visitor.php">
          <img src="undip.png" alt="Logo UNDIP" class="rbti-brand-logo">
          <span class="rbti-brand-text">
            <span class="rbti-brand-title">RBTI UNDIP</span>
            <span class="rbti-brand-sub">Portal Pengunjung</span>
          </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link active" href="index-visitor.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="katalog.php"><i class="fas fa-book-open"></i> Catalog</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="pencarian-buku-visitor.php"><i class="fas fa-search"></i> Search</a>
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
              <i class="fas fa-university"></i> Fakultas Teknik · Universitas Diponegoro
            </div>
            <h1 class="rbti-hero-title">Ruang Baca<br>Teknik Industri</h1>
            <p class="rbti-hero-lead">
              Jelajahi katalog buku, referensi ilmiah, laporan Kerja Praktik, Tugas Akhir, dan Tesis yang tersedia di Ruang Baca Teknik Industri.
            </p>
            <div class="rbti-hero-actions">
              <a href="pencarian-buku-visitor.php" class="rbti-btn-gold">
                <i class="fas fa-search"></i> Cari Koleksi Buku
              </a>
              <a href="katalog.php" class="rbti-btn-ghost-light">
                <i class="fas fa-book-open"></i> Lihat Katalog
              </a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="rbti-hero-visual">
              <div class="rbti-hero-visual-header">
                <span class="rbti-hero-visual-label">Akses Pengunjung</span>
                <i class="fas fa-compass" style="color: var(--rbti-gold-400);"></i>
              </div>
              <div class="rbti-hero-metrics">
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">Katalog</div>
                  <div class="rbti-hero-metric-key">5 koleksi terindeks</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">Pencarian</div>
                  <div class="rbti-hero-metric-key">Judul, penulis &amp; genre</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">Baca di Tempat</div>
                  <div class="rbti-hero-metric-key">Terbuka untuk pengunjung</div>
                </div>
                <div class="rbti-hero-metric">
                  <div class="rbti-hero-metric-val">Keanggotaan</div>
                  <div class="rbti-hero-metric-key">Pinjam hingga 2 minggu</div>
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
            <div class="rbti-section-kicker">Layanan Tersedia</div>
            <h2 class="rbti-section-title">Mulai Menjelajah</h2>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4">
            <a href="katalog.php" class="rbti-service-card">
              <div class="rbti-service-icon">
                <i class="fas fa-layer-group"></i>
              </div>
              <h3>Katalog</h3>
              <p>Buku sirkulasi, buku referensi, KP, Tugas Akhir, dan Tesis.</p>
              <span class="rbti-service-cta">Lihat Katalog <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>

          <div class="col-md-4">
            <a href="pencarian-buku-visitor.php" class="rbti-service-card">
              <div class="rbti-service-icon rbti-service-icon--gold">
                <i class="fas fa-magnifying-glass"></i>
              </div>
              <h3>Pencarian Buku</h3>
              <p>Periksa ketersediaan salinan buku berdasarkan judul, penulis, atau genre.</p>
              <span class="rbti-service-cta">Cari Sekarang <i class="fas fa-arrow-right"></i></span>
            </a>
          </div>

          <div class="col-md-4">
            <a href="register.php#member-section" class="rbti-service-card">
              <div class="rbti-service-icon rbti-service-icon--emerald">
                <i class="fas fa-id-card"></i>
              </div>
              <h3>Peminjaman &amp; Pengembalian</h3>
              <p>Khusus anggota terdaftar (maks. 2 minggu, dapat diperpanjang sesuai ketentuan).</p>
              <span class="rbti-service-cta">Masuk sebagai Anggota <i class="fas fa-arrow-right"></i></span>
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
