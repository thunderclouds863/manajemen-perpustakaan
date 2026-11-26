<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengembalian Buku — Portal Anggota RBTI UNDIP</title>
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
              <a class="nav-link" href="katalog-pengguna.php"><i class="fas fa-book-open"></i> Catalog</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="pencarian-buku.php"><i class="fas fa-search"></i> Search</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="peminjaman_buku_pengguna.php"><i class="fas fa-bookmark"></i> Borrowings</a>
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
        <div class="rbti-eyebrow"><i class="fas fa-rotate-left"></i> Pengembalian &amp; Perpanjangan</div>
        <h1>Pengembalian Buku</h1>
        <p>Periksa daftar buku yang sedang dipinjam dan ajukan perpanjangan masa pinjam bila diperlukan.</p>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-panel">
          <div class="rbti-panel-header">
            <h2 class="rbti-panel-title"><i class="fas fa-book"></i> Daftar Buku yang Dipinjam</h2>
            <div style="width: 100%; max-width: 320px;">
              <div class="rbti-search-input-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="searchBook" class="rbti-search-input" style="height: 40px; font-size: 0.88rem;" placeholder="Cari berdasarkan judul buku...">
              </div>
            </div>
          </div>
          <div class="rbti-panel-body">
            <div class="rbti-table-wrap">
              <table class="table rbti-table" id="borrowTable">
                <thead>
                  <tr>
                    <th>ID Buku</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Perpanjang</th>
                  </tr>
                </thead>
                <tbody id="borrowList"></tbody>
              </table>
            </div>
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

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function () {
      function fetchBorrowedBooks() {
        $.ajax({
          url: 'backend_api.php',
          type: 'GET',
          dataType: 'json',
          success: function (data) {
            const borrowList = data.borrowList || [];
            const borrowTable = $('#borrowList');
            borrowTable.empty();

            if (borrowList.length === 0) {
              borrowTable.append('<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada buku yang sedang dipinjam.</td></tr>');
              return;
            }

            borrowList.forEach(function (borrow) {
              const returnDate = new Date(borrow.returnDate);
              const today = new Date();
              const overdue = today > returnDate;

              let statusBadge = borrow.status === 'pending'
                ? (overdue ? '<span class="rbti-badge rbti-badge--danger">Terlambat</span>' : '<span class="rbti-badge rbti-badge--info">Sedang Dipinjam</span>')
                : '<span class="rbti-badge rbti-badge--success">Kembali</span>';

              let extensionButton = '<span class="text-muted small">&mdash;</span>';
              if (borrow.status === 'pending' && borrow.extensionCount < 2) {
                extensionButton = `<button class="btn btn-outline-primary rbti-btn-sm" onclick="extendBorrow(${borrow.borrowID})"><i class="fas fa-clock"></i> Perpanjang</button>`;
              } else if (borrow.status === 'pending' && borrow.extensionCount >= 2) {
                extensionButton = `<button class="btn btn-outline-secondary rbti-btn-sm" disabled>Batas Maks.</button>`;
              }

              borrowTable.append(`
                <tr>
                  <td>${borrow.bookID}</td>
                  <td><strong style="color: var(--rbti-ink-900);">${borrow.bookTitle}</strong></td>
                  <td>${borrow.borrowDate}</td>
                  <td>${borrow.returnDate}</td>
                  <td>${statusBadge}</td>
                  <td>${extensionButton}</td>
                </tr>
              `);
            });
          }
        });
      }

      $('#searchBook').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('#borrowList tr').each(function () {
          const bookTitle = $(this).find('td').eq(1).text().toLowerCase();
          $(this).toggle(bookTitle.indexOf(searchTerm) > -1);
        });
      });

      window.extendBorrow = function (borrowID) {
        $.ajax({
          url: 'backend_api.php',
          type: 'POST',
          dataType: 'json',
          data: { action: 'extend', borrowID: borrowID },
          success: function (response) {
            alert(response.message);
            fetchBorrowedBooks();
          },
          error: function () {
            alert('Gagal memperpanjang peminjaman.');
          }
        });
      };

      fetchBorrowedBooks();
    });
  </script>
</body>
</html>
