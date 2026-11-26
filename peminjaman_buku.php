<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Peminjaman — Admin RBTI UNDIP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
              <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home"></i> Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="manajemen-user.php"><i class="fas fa-users"></i> Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="manajemen_buku.php"><i class="fas fa-book"></i> Books</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="peminjaman_buku.php"><i class="fas fa-bookmark"></i> Borrowing</a>
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
        <div class="rbti-eyebrow"><i class="fas fa-bookmark"></i> Kontrol Sirkulasi</div>
        <h1>Manajemen Peminjaman</h1>
        <p>Konfirmasi permintaan peminjaman, perpanjangan masa pinjam, dan pengembalian buku dari anggota.</p>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-panel">
          <div class="rbti-panel-header">
            <h2 class="rbti-panel-title"><i class="fas fa-clipboard-list"></i> Catatan Sirkulasi</h2>
            <div style="width: 100%; max-width: 340px;">
              <div class="rbti-search-input-wrap">
                <i class="fas fa-filter"></i>
                <input type="text" id="search" class="rbti-search-input" style="height: 40px; font-size: 0.88rem;" placeholder="Cari ID, judul buku, atau status...">
              </div>
            </div>
          </div>

          <div class="rbti-panel-body">
            <div class="rbti-table-wrap">
              <table class="table rbti-table" id="borrowTable" style="width: 100%;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody></tbody>
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
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function () {
      function badgeClassFor(status) {
        const s = String(status || '').toLowerCase();
        if (s === 'returned') return 'rbti-badge--success';
        if (s === 'borrowed' || s === 'reserved') return 'rbti-badge--info';
        if (s === 'overdue' || s === 'unavailable') return 'rbti-badge--danger';
        if (s.includes('pending') || s === 'extended') return 'rbti-badge--warning';
        return 'rbti-badge--neutral';
      }

      function loadBorrowings() {
        $.ajax({
          url: 'proses-pinjam.php',
          method: 'GET',
          data: { action: 'fetch' },
          success: function (response) {
            if ($.fn.DataTable.isDataTable('#borrowTable')) {
              $('#borrowTable').DataTable().destroy();
            }

            const tbody = $('#borrowTable tbody');
            tbody.empty();

            if (!response || response.length === 0) {
              tbody.append('<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada catatan peminjaman.</td></tr>');
              return;
            }

            response.forEach(function (item) {
              const bClass = badgeClassFor(item.status);
              let actionHtml = '<span class="text-muted small">&mdash;</span>';
              if (item.status === 'borrow pending') {
                actionHtml = `<button class="btn btn-success rbti-btn-sm confirm-btn" data-id="${item.borrowID}" data-action="borrowing"><i class="fas fa-check"></i> Confirm Borrowing</button>`;
              } else if (item.status === 'extend pending') {
                actionHtml = `<button class="btn btn-warning rbti-btn-sm confirm-btn" data-id="${item.borrowID}" data-action="extending"><i class="fas fa-clock"></i> Confirm Extending</button>`;
              } else if (item.status === 'return pending') {
                actionHtml = `<button class="btn btn-danger rbti-btn-sm confirm-btn" data-id="${item.borrowID}" data-action="returning"><i class="fas fa-undo"></i> Confirm Returning</button>`;
              }

              tbody.append(`
                <tr>
                  <td>#${item.borrowID || '-'}</td>
                  <td><strong style="color: var(--rbti-ink-900);">${item.bookTitle || item.bookID || '-'}</strong></td>
                  <td>${item.borrowDate || '-'}</td>
                  <td>${item.returnDate || '-'}</td>
                  <td><span class="rbti-badge ${bClass}">${item.status}</span></td>
                  <td>${actionHtml}</td>
                </tr>
              `);
            });

            $('#borrowTable').DataTable({
              paging: true,
              lengthChange: true,
              searching: false,
              ordering: true,
              info: true,
              autoWidth: false,
              language: {
                paginate: { previous: "Sebelumnya", next: "Berikutnya" },
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri"
              }
            });
          },
          error: function (xhr) {
            console.error("Error fetching data:", xhr.responseText);
          }
        });
      }

      loadBorrowings();

      document.getElementById('search').addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#borrowTable tbody tr');

        rows.forEach(function (row) {
          if (row.cells.length < 5) return;
          const borrowID = row.cells[0].textContent.toLowerCase();
          const bookInfo = row.cells[1].textContent.toLowerCase();
          const status = row.cells[4].textContent.toLowerCase();

          row.style.display = (bookInfo.includes(filter) || borrowID.includes(filter) || status.includes(filter)) ? '' : 'none';
        });
      });

      $(document).on('click', '.confirm-btn', function () {
        const borrowID = $(this).data('id');
        const action = $(this).data('action');

        $.ajax({
          url: 'proses-pinjam.php',
          method: 'POST',
          data: { action: action, borrowID: borrowID },
          success: function (response) {
            if (response.success) {
              alert(response.message);
            } else {
              alert('Error: ' + response.message);
            }
            loadBorrowings();
          },
          error: function (xhr) {
            console.error("Error processing action:", xhr.responseText);
          }
        });
      });
    });
  </script>
</body>
</html>
