<?php
require_once 'auth.php';
rbti_require_member();
require_once 'db_connect.php';

$borrowRecords = [];
$userID = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT Borrow.*, Book.title
     FROM Borrow
     JOIN Book ON Borrow.bookID = Book.bookID
     WHERE Borrow.userID = ?
     ORDER BY Borrow.borrowID DESC"
);
$stmt->bind_param('i', $userID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $borrowRecords[] = $row;
}
$stmt->close();

// Fetch borrowable books (all available circulation/general books, excluding reference/archive-only genres)
$availableBooks = [];
$availRes = $conn->query(
    "SELECT bookID, title, author, genre, availableCopies
     FROM Book
     WHERE availableCopies > 0
       AND status != 'unavailable'
       AND genre NOT IN ('Referensi', 'Tugas Akhir', 'KP', 'Thesis')
     ORDER BY title ASC"
);
if ($availRes) {
    while ($row = $availRes->fetch_assoc()) {
        $availableBooks[] = $row;
    }
}

// Handle book actions (borrow, extend, return)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'borrow') {
        $bookID = (int) ($_POST['bookIdentifier'] ?? 0);
        if ($bookID <= 0) {
            echo json_encode(['success' => false, 'message' => 'Pilih buku yang valid.']);
            exit;
        }

        $bkStmt = $conn->prepare("SELECT bookID, title, availableCopies, status, genre FROM Book WHERE bookID = ?");
        $bkStmt->bind_param('i', $bookID);
        $bkStmt->execute();
        $book = $bkStmt->get_result()->fetch_assoc();
        $bkStmt->close();

        if (!$book) {
            echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan.']);
            exit;
        }

        if ((int) $book['availableCopies'] <= 0 || $book['status'] === 'unavailable') {
            echo json_encode(['success' => false, 'message' => 'Buku sedang tidak tersedia untuk dipinjam.']);
            exit;
        }

        if (in_array($book['genre'], ['Referensi', 'Tugas Akhir', 'KP', 'Thesis'], true)) {
            echo json_encode(['success' => false, 'message' => 'Koleksi referensi/arsip hanya dapat dibaca di tempat.']);
            exit;
        }

        // Prevent duplicate active/pending borrow of the same book by the same member
        $dupStmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM Borrow
             WHERE userID = ? AND bookID = ? AND status IN ('borrow pending', 'borrowed', 'extended', 'extend pending', 'return pending')"
        );
        $dupStmt->bind_param('ii', $userID, $bookID);
        $dupStmt->execute();
        $dupCount = (int) ($dupStmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $dupStmt->close();

        if ($dupCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Anda sudah memiliki permintaan atau pinjaman aktif untuk buku ini.']);
            exit;
        }

        $borrowDate = date('Y-m-d');
        $returnDate = date('Y-m-d', strtotime('+14 days'));
        $status = 'borrow pending';

        // Insert borrowing request (availableCopies is decremented once upon admin confirmation in proses-pinjam.php)
        $insStmt = $conn->prepare("INSERT INTO Borrow (userID, bookID, borrowDate, returnDate, status) VALUES (?, ?, ?, ?, ?)");
        $insStmt->bind_param('iisss', $userID, $bookID, $borrowDate, $returnDate, $status);
        $insStmt->execute();
        $ok = $insStmt->affected_rows > 0;
        $insStmt->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Permintaan peminjaman berhasil dikirim! Menunggu persetujuan admin.' : 'Gagal mengajukan peminjaman.']);
    } elseif ($action === 'return') {
        $borrowID = (int) ($_POST['borrowID'] ?? 0);
        $updStmt = $conn->prepare(
            "UPDATE Borrow SET status = 'return pending'
             WHERE borrowID = ? AND userID = ? AND status IN ('borrowed', 'extended', 'overdue')"
        );
        $updStmt->bind_param('ii', $borrowID, $userID);
        $updStmt->execute();
        $ok = $updStmt->affected_rows > 0;
        $updStmt->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Permintaan pengembalian dikirim! Menunggu konfirmasi admin.' : 'Tidak dapat mengajukan pengembalian untuk catatan ini.']);
    } elseif ($action === 'extend') {
        $borrowID = (int) ($_POST['borrowID'] ?? 0);

        $chkStmt = $conn->prepare("SELECT extend_count, status FROM Borrow WHERE borrowID = ? AND userID = ?");
        $chkStmt->bind_param('ii', $borrowID, $userID);
        $chkStmt->execute();
        $bRow = $chkStmt->get_result()->fetch_assoc();
        $chkStmt->close();

        if (!$bRow || !in_array($bRow['status'], ['borrowed', 'extended'], true)) {
            echo json_encode(['success' => false, 'message' => 'Peminjaman ini tidak dapat diperpanjang.']);
            exit;
        }

        if ((int) ($bRow['extend_count'] ?? 0) >= 2) {
            echo json_encode(['success' => false, 'message' => 'Batas maksimal perpanjangan (2 kali) telah tercapai.']);
            exit;
        }

        // Set status to extend pending (returnDate is extended once upon admin confirmation in proses-pinjam.php)
        $updStmt = $conn->prepare("UPDATE Borrow SET status = 'extend pending' WHERE borrowID = ? AND userID = ?");
        $updStmt->bind_param('ii', $borrowID, $userID);
        $updStmt->execute();
        $ok = $updStmt->affected_rows > 0;
        $updStmt->close();

        echo json_encode(['success' => $ok, 'message' => $ok ? 'Permintaan perpanjangan dikirim! Menunggu persetujuan admin.' : 'Gagal mengajukan perpanjangan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
    exit;
}

function rbtiStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'returned':
            return 'rbti-badge--success';
        case 'borrowed':
            return 'rbti-badge--info';
        case 'extended':
        case 'borrow pending':
        case 'extend pending':
        case 'return pending':
            return 'rbti-badge--warning';
        case 'overdue':
        case 'borrow rejected':
        case 'extend rejected':
            return 'rbti-badge--danger';
        default:
            return 'rbti-badge--neutral';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Peminjaman Buku — Portal Anggota RBTI UNDIP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
          <div>
            <div class="rbti-eyebrow"><i class="fas fa-bookmark"></i> Sirkulasi Anggota</div>
            <h1>Riwayat Peminjaman Buku</h1>
            <p>Pantau status peminjaman aktif, ajukan peminjaman baru, perpanjang masa pinjam, atau kembalikan buku.</p>
          </div>
          <div>
            <button type="button" class="rbti-btn-gold" data-bs-toggle="modal" data-bs-target="#borrowModal">
              <i class="fas fa-plus"></i> Ajukan Peminjaman
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-panel">
          <div class="rbti-panel-header">
            <h2 class="rbti-panel-title"><i class="fas fa-list-ul"></i> Catatan Peminjaman</h2>
            <div style="width: 100%; max-width: 340px;">
              <div class="rbti-search-input-wrap">
                <i class="fas fa-filter"></i>
                <input type="text" id="search" class="rbti-search-input" style="height: 40px; font-size: 0.88rem;" placeholder="Cari ID, judul buku, atau status...">
              </div>
            </div>
          </div>

          <div class="rbti-panel-body">
            <div class="rbti-table-wrap">
              <table class="table rbti-table" id="borrowTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Book ID</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($borrowRecords)): ?>
                    <?php foreach ($borrowRecords as $record): ?>
                      <tr>
                        <td>#<?= htmlspecialchars($record['borrowID']) ?></td>
                        <td><?= htmlspecialchars($record['bookID']) ?></td>
                        <td><strong style="color: var(--rbti-ink-900);"><?= htmlspecialchars($record['title']) ?></strong></td>
                        <td><?= htmlspecialchars($record['borrowDate']) ?></td>
                        <td><?= htmlspecialchars($record['returnDate']) ?></td>
                        <td>
                          <span class="rbti-badge <?= rbtiStatusBadgeClass($record['status']) ?>">
                            <?= htmlspecialchars($record['status']) ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($record['status'] === 'borrowed' || $record['status'] === 'extended'): ?>
                            <div class="rbti-action-group">
                              <button class="btn btn-outline-success rbti-btn-sm extend-btn" data-id="<?= $record['borrowID'] ?>">
                                <i class="fas fa-clock"></i> Extend
                              </button>
                              <button class="btn btn-outline-warning rbti-btn-sm return-btn" data-id="<?= $record['borrowID'] ?>">
                                <i class="fas fa-undo"></i> Return
                              </button>
                            </div>
                          <?php else: ?>
                            <span class="text-muted small">&mdash;</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center py-4 text-muted">Belum ada catatan peminjaman buku.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Borrow Modal -->
  <div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="borrowModalLabel"><i class="fas fa-book-open me-2"></i>Ajukan Peminjaman Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="borrowForm">
            <div class="mb-3">
              <label for="bookSelect" class="form-label">Pilih Buku Sirkulasi</label>
              <select class="form-select" id="bookSelect" name="bookIdentifier" required>
                <option value="">Cari berdasarkan judul, penulis, atau ID...</option>
                <?php foreach ($availableBooks as $book): ?>
                  <option value="<?= htmlspecialchars($book['bookID']) ?>"
                          data-title="<?= htmlspecialchars($book['title']) ?>"
                          data-author="<?= htmlspecialchars($book['author']) ?>">
                    <?= htmlspecialchars($book['author']) ?> - <?= htmlspecialchars($book['title']) ?> (ID: <?= htmlspecialchars($book['bookID']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <div id="bookDetails" class="alert alert-info mb-0" style="display: none;">
                <p class="mb-1"><strong>Detail Buku Dipilih:</strong></p>
                <p id="selectedBookInfo" class="mb-0 small"></p>
              </div>
            </div>
            <button type="submit" class="rbti-btn-primary w-100">
              <i class="fas fa-paper-plane"></i> Kirim Permintaan Peminjaman
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

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
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#bookSelect').select2({
        dropdownParent: $('#borrowModal'),
        placeholder: "Cari berdasarkan judul, penulis, atau ID...",
        allowClear: true,
        width: '100%'
      });

      $('#bookSelect').on('change', function () {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
          const title = selected.data('title');
          const author = selected.data('author');
          $('#selectedBookInfo').html(
            'Judul: ' + $('<div>').text(title).html() + '<br>' +
            'Penulis: ' + $('<div>').text(author).html() + '<br>' +
            'Book ID: ' + $('<div>').text(selected.val()).html()
          );
          $('#bookDetails').show();
        } else {
          $('#bookDetails').hide();
        }
      });

      const hasRecords = $('#borrowTable tbody tr td[colspan]').length === 0;
      if (hasRecords) {
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
      }
    });

    document.getElementById('search').addEventListener('input', function () {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll('#borrowTable tbody tr');

      rows.forEach(function (row) {
        if (row.cells.length < 6) return;
        const borrowID = row.cells[0].textContent.toLowerCase();
        const bookID = row.cells[1].textContent.toLowerCase();
        const title = row.cells[2].textContent.toLowerCase();
        const status = row.cells[5].textContent.toLowerCase();

        if (bookID.includes(filter) || borrowID.includes(filter) || title.includes(filter) || status.includes(filter)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    document.getElementById('borrowForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const selectedBook = document.getElementById('bookSelect');
      if (!selectedBook.value) {
        alert('Silakan pilih buku terlebih dahulu.');
        return;
      }

      const selectedOption = selectedBook.options[selectedBook.selectedIndex];
      const bookID = selectedBook.value;
      const bookTitle = selectedOption.getAttribute('data-title');

      if (confirm(`Ajukan peminjaman untuk:\nJudul: ${bookTitle}\nBook ID: ${bookID}?`)) {
        const formData = new FormData();
        formData.append('action', 'borrow');
        formData.append('bookIdentifier', bookID);

        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        submitButton.disabled = true;

        fetch('peminjaman_buku_pengguna.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
            alert(data.message);
            if (data.success) location.reload();
          })
          .catch(error => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
          });
      }
    });

    document.querySelectorAll('.extend-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        const borrowID = this.getAttribute('data-id');
        if (confirm('Ajukan perpanjangan masa pinjam selama 14 hari?')) {
          fetch('peminjaman_buku_pengguna.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=extend&borrowID=${borrowID}`
          })
            .then(response => response.json())
            .then(data => {
              alert(data.message);
              if (data.success) location.reload();
            })
            .catch(error => console.error('Error:', error));
        }
      });
    });

    document.querySelectorAll('.return-btn').forEach(function (button) {
      button.addEventListener('click', function () {
        const borrowID = this.getAttribute('data-id');
        if (confirm('Ajukan pengembalian buku ini?')) {
          fetch('peminjaman_buku_pengguna.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=return&borrowID=${borrowID}`
          })
            .then(response => response.json())
            .then(data => {
              alert(data.message);
              if (data.success) location.reload();
            })
            .catch(error => console.error('Error:', error));
        }
      });
    });
  </script>
</body>
</html>
