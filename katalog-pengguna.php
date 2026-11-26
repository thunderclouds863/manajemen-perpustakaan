<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog Koleksi — Portal Anggota RBTI UNDIP</title>
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
              <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="katalog-pengguna.php"><i class="fas fa-book-open"></i> Catalog</a>
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
    <section class="rbti-masthead">
      <div class="container rbti-masthead-inner">
        <div class="rbti-eyebrow"><i class="fas fa-layer-group"></i> Arsip &amp; Daftar Koleksi</div>
        <h1>Katalog Koleksi RBTI</h1>
        <p>Pilih kategori koleksi di bawah ini untuk membuka daftar lengkap arsip Ruang Baca Teknik Industri.</p>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-catalog-grid">
          <a href="https://docs.google.com/spreadsheets/d/1kPDNEL63OlwpQxpTRaIZDZvRtkBD-2aLA3pd0pPljmY/edit?usp=sharing" class="rbti-catalog-card" target="_blank" rel="noopener noreferrer">
            <div class="rbti-catalog-top">
              <div class="rbti-service-icon mb-0"><i class="fas fa-book"></i></div>
              <span class="rbti-catalog-badge">Pinjam</span>
            </div>
            <h2 class="rbti-catalog-title">Buku Sirkulasi</h2>
            <p class="rbti-catalog-meta">Koleksi buku teks Teknik Industri yang dapat dipinjam oleh anggota aktif.</p>
            <span class="rbti-catalog-link">Buka Daftar <i class="fas fa-arrow-up-right-from-square"></i></span>
          </a>

          <a href="https://docs.google.com/spreadsheets/d/138QbRq1M_Vg9DfUEkglhX5lBCHJtS1wlj2M2O78AxSA/edit?usp=sharing" class="rbti-catalog-card" target="_blank" rel="noopener noreferrer">
            <div class="rbti-catalog-top">
              <div class="rbti-service-icon rbti-service-icon--gold mb-0"><i class="fas fa-bookmark"></i></div>
              <span class="rbti-catalog-badge">Baca di Tempat</span>
            </div>
            <h2 class="rbti-catalog-title">Buku Referensi</h2>
            <p class="rbti-catalog-meta">Handbook, kamus teknik, standar industri, dan pustaka rujukan utama.</p>
            <span class="rbti-catalog-link">Buka Daftar <i class="fas fa-arrow-up-right-from-square"></i></span>
          </a>

          <a href="https://docs.google.com/spreadsheets/d/1JkA9dIa4KtsvSoDlC1AaQ8vMgEtoXbviCaU3b1pnpXk/edit?gid=1246111956#gid=1246111956" class="rbti-catalog-card" target="_blank" rel="noopener noreferrer">
            <div class="rbti-catalog-top">
              <div class="rbti-service-icon rbti-service-icon--emerald mb-0"><i class="fas fa-briefcase"></i></div>
              <span class="rbti-catalog-badge">Laporan</span>
            </div>
            <h2 class="rbti-catalog-title">Kerja Praktik (KP)</h2>
            <p class="rbti-catalog-meta">Arsip laporan Kerja Praktik mahasiswa di berbagai sektor industri.</p>
            <span class="rbti-catalog-link">Buka Daftar <i class="fas fa-arrow-up-right-from-square"></i></span>
          </a>

          <a href="https://docs.google.com/spreadsheets/d/1NJa4gIzub_4ENb4SLLLXfFUu7HjHqz0Aaw8mUp6BKWg/edit?usp=sharing" class="rbti-catalog-card" target="_blank" rel="noopener noreferrer">
            <div class="rbti-catalog-top">
              <div class="rbti-service-icon mb-0"><i class="fas fa-graduation-cap"></i></div>
              <span class="rbti-catalog-badge">Sarjana</span>
            </div>
            <h2 class="rbti-catalog-title">Tugas Akhir</h2>
            <p class="rbti-catalog-meta">Koleksi skripsi dan laporan Tugas Akhir program sarjana Teknik Industri.</p>
            <span class="rbti-catalog-link">Buka Daftar <i class="fas fa-arrow-up-right-from-square"></i></span>
          </a>

          <a href="https://docs.google.com/spreadsheets/d/1-OQw7NAnoGnwq_ImnC8S-AHD-5xk0aNaQjkz_RzWnyY/edit?usp=sharing" class="rbti-catalog-card" target="_blank" rel="noopener noreferrer">
            <div class="rbti-catalog-top">
              <div class="rbti-service-icon rbti-service-icon--gold mb-0"><i class="fas fa-scroll"></i></div>
              <span class="rbti-catalog-badge">Magister</span>
            </div>
            <h2 class="rbti-catalog-title">Tesis</h2>
            <p class="rbti-catalog-meta">Karya ilmiah dan penelitian lanjutan program magister Teknik Industri.</p>
            <span class="rbti-catalog-link">Buka Daftar <i class="fas fa-arrow-up-right-from-square"></i></span>
          </a>
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
