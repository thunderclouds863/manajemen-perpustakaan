<?php
require_once 'auth.php';
rbti_require_admin();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookID'], $_POST['availableCopies'])) {
    header('Content-Type: application/json');
    $bookID = (int) $_POST['bookID'];
    $availableCopies = max(0, (int) $_POST['availableCopies']);
    $newStatus = ($availableCopies === 0) ? 'unavailable' : 'available';

    $stmt = $conn->prepare("UPDATE Book SET availableCopies = ?, status = ? WHERE bookID = ?");
    $stmt->bind_param('isi', $availableCopies, $newStatus, $bookID);
    $ok = $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => $ok, 'message' => $ok ? 'Status buku diperbarui.' : 'Gagal memperbarui status buku.']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Buku — Admin RBTI UNDIP</title>
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
              <a class="nav-link active" href="manajemen_buku.php"><i class="fas fa-book"></i> Books</a>
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
    <section class="rbti-masthead">
      <div class="container rbti-masthead-inner">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
          <div>
            <div class="rbti-eyebrow"><i class="fas fa-book"></i> Inventaris Perpustakaan</div>
            <h1>Manajemen Buku</h1>
            <p>Tambah, perbarui, hapus, atau impor data koleksi buku Ruang Baca Teknik Industri.</p>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="rbti-btn-gold" data-bs-toggle="modal" data-bs-target="#addBookModal">
              <i class="fas fa-plus"></i> Tambah Buku
            </button>
            <button type="button" class="rbti-btn-ghost-light" data-bs-toggle="modal" data-bs-target="#uploadSpreadsheetModal">
              <i class="fas fa-file-upload"></i> Upload CSV
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-panel">
          <div class="rbti-panel-header">
            <h2 class="rbti-panel-title"><i class="fas fa-table"></i> Daftar Koleksi Buku</h2>
          </div>
          <div class="rbti-panel-body">
            <div class="rbti-table-wrap">
              <table class="table rbti-table" id="booksTable" style="width: 100%;">
                <thead>
                  <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Genre</th>
                    <th>Total</th>
                    <th>Tersedia</th>
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

  <!-- Add Book Modal -->
  <div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Buku Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addBookForm">
            <div class="mb-3">
              <label class="form-label">Judul Buku</label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Penulis</label>
              <input type="text" class="form-control" name="author" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Genre / Kategori</label>
              <select class="form-select" name="genre" required>
                <option>Sirkulasi</option>
                <option>Referensi</option>
                <option>Tugas Akhir</option>
                <option>KP</option>
                <option>Thesis</option>
              </select>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Total Salinan</label>
                <input type="number" class="form-control" name="totalCopies" min="0" required>
              </div>
              <div class="col-6">
                <label class="form-label">Salinan Tersedia</label>
                <input type="number" class="form-control" name="availableCopies" min="0" required>
              </div>
            </div>
            <button type="submit" class="rbti-btn-primary w-100">Simpan Buku</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Upload Spreadsheet Modal -->
  <div class="modal fade" id="uploadSpreadsheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-file-csv me-2"></i>Upload Spreadsheet (CSV)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="uploadSpreadsheetForm" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Pilih File (CSV)</label>
              <input type="file" class="form-control" name="spreadsheet" accept=".csv" required>
            </div>
            <button type="submit" class="rbti-btn-primary w-100">Upload Data</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Book Modal -->
  <div class="modal fade" id="editBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Buku</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editBookForm">
            <input type="hidden" name="id">
            <div class="mb-3">
              <label class="form-label">Judul Buku</label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Penulis</label>
              <input type="text" class="form-control" name="author" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Genre / Kategori</label>
              <select class="form-select" name="genre" required>
                <option>Sirkulasi</option>
                <option>Referensi</option>
                <option>Tugas Akhir</option>
                <option>KP</option>
                <option>Thesis</option>
              </select>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Total Salinan</label>
                <input type="number" class="form-control" name="totalCopies" min="0" required>
              </div>
              <div class="col-6">
                <label class="form-label">Salinan Tersedia</label>
                <input type="number" class="form-control" name="availableCopies" min="0" required>
              </div>
            </div>
            <button type="submit" class="rbti-btn-primary w-100">Simpan Perubahan</button>
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

  <script>
    $(document).ready(function () {
      const table = $('#booksTable').DataTable({
        ajax: {
          url: 'get_books.php',
          dataSrc: ''
        },
        columns: [
          {
            data: 'title',
            render: function (data) {
              return '<strong style="color: var(--rbti-ink-900);">' + $('<div>').text(data).html() + '</strong>';
            }
          },
          { data: 'author' },
          { data: 'genre' },
          { data: 'totalCopies' },
          { data: 'availableCopies' },
          {
            data: 'status',
            render: function (data) {
              const isAvail = String(data).toLowerCase() === 'available';
              return isAvail
                ? '<span class="rbti-badge rbti-badge--success">Available</span>'
                : '<span class="rbti-badge rbti-badge--danger">Unavailable</span>';
            }
          },
          {
            data: null,
            orderable: false,
            render: function (data) {
              return `
                <div class="rbti-action-group">
                  <button class="btn btn-outline-primary rbti-btn-sm btn-edit" data-id="${data.bookID}">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-outline-danger rbti-btn-sm btn-delete" data-id="${data.bookID}">
                    <i class="fas fa-trash-alt"></i> Delete
                  </button>
                </div>
              `;
            }
          }
        ],
        language: {
          search: "Cari buku:",
          lengthMenu: "Tampilkan _MENU_ entri",
          info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ buku",
          paginate: { previous: "Sebelumnya", next: "Berikutnya" }
        }
      });

      $('#booksTable tbody').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
          $.ajax({
            url: 'delete_book.php',
            method: 'POST',
            data: { id },
            success: function () {
              table.ajax.reload();
            },
            error: function (xhr) {
              alert(xhr.responseText || 'Gagal menghapus buku.');
            }
          });
        }
      });

      $('#booksTable tbody').on('click', '.btn-edit', function () {
        const rowData = table.row($(this).closest('tr')).data();
        if (!rowData) return;
        const form = $('#editBookForm');
        form.find('[name="id"]').val(rowData.bookID);
        form.find('[name="title"]').val(rowData.title);
        form.find('[name="author"]').val(rowData.author);
        form.find('[name="genre"]').val(rowData.genre);
        form.find('[name="totalCopies"]').val(rowData.totalCopies);
        form.find('[name="availableCopies"]').val(rowData.availableCopies);
        new bootstrap.Modal(document.getElementById('editBookModal')).show();
      });

      $('#editBookForm').submit(function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
          url: 'edit_book.php',
          method: 'POST',
          data: formData,
          success: function () {
            $('#editBookModal').modal('hide');
            table.ajax.reload();
          },
          error: function () {
            alert('Gagal menyimpan perubahan buku.');
          }
        });
      });

      $('#addBookForm').submit(function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
          url: 'add_books.php',
          method: 'POST',
          data: formData,
          success: function (response) {
            if (response === 'success') {
              alert('Buku berhasil ditambahkan!');
              $('#addBookModal').modal('hide');
              table.ajax.reload();
              $('#addBookForm')[0].reset();
            } else {
              alert('Error adding book: ' + response);
            }
          },
          error: function () {
            alert('Error adding book.');
          }
        });
      });

      $('#uploadSpreadsheetForm').submit(function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
          url: 'upload_spreadsheet.php',
          method: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            if (response === 'success') {
              alert('Spreadsheet berhasil diunggah!');
              $('#uploadSpreadsheetModal').modal('hide');
              table.ajax.reload();
            } else {
              alert('Error uploading spreadsheet: ' + response);
            }
          },
          error: function () {
            alert('Error uploading spreadsheet.');
          }
        });
      });
    });
  </script>
</body>
</html>
