<?php
// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rbti";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Jika ada permintaan pencarian Ajax
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $searchQuery = '%' . $_POST['query'] . '%';
    $query = "SELECT * FROM Book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = [];

    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    $stmt->close();
    echo json_encode(['data' => $books]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pencarian Buku — Ruang Baca Teknik Industri UNDIP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
              <a class="nav-link active" href="pencarian-buku.php"><i class="fas fa-search"></i> Search</a>
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
    <section class="rbti-masthead" style="padding-bottom: 62px;">
      <div class="container rbti-masthead-inner">
        <div class="rbti-eyebrow"><i class="fas fa-magnifying-glass"></i> Pencarian Koleksi</div>
        <h1>Cari Buku Perpustakaan</h1>
        <p>Temukan buku berdasarkan judul, penulis, atau genre beserta status ketersediaan salinan secara langsung.</p>
      </div>
    </section>

    <section class="pb-5">
      <div class="container">
        <div class="rbti-search-panel mb-4">
          <form id="searchForm" class="rbti-search-form">
            <div class="rbti-search-input-wrap">
              <i class="fas fa-search"></i>
              <input type="text" id="searchInput" name="query" class="rbti-search-input" placeholder="Masukkan judul buku, nama penulis, atau genre...">
            </div>
            <button type="submit" class="rbti-btn-primary">
              <i class="fas fa-search"></i> Cari Buku
            </button>
          </form>
        </div>

        <div class="rbti-panel">
          <div class="rbti-panel-header">
            <h2 class="rbti-panel-title"><i class="fas fa-book"></i> Daftar Koleksi</h2>
            <a href="peminjaman_buku_pengguna.php" class="rbti-btn-outline">
              <i class="fas fa-bookmark"></i> Pinjam Buku
            </a>
          </div>
          <div class="rbti-panel-body">
            <div class="rbti-table-wrap">
              <table id="booksTable" class="table rbti-table" style="width: 100%;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Genre</th>
                    <th>Total</th>
                    <th>Tersedia</th>
                    <th>Status</th>
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
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function () {
      const table = $('#booksTable').DataTable({
        ajax: {
          url: '',
          type: 'POST',
          data: function (d) {
            d.query = $('#searchInput').val();
          },
          dataSrc: function (json) {
            return json.data || [];
          }
        },
        columns: [
          { data: 'bookID' },
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
                ? '<span class="rbti-badge rbti-badge--success">Tersedia</span>'
                : '<span class="rbti-badge rbti-badge--danger">Tidak Tersedia</span>';
            }
          }
        ],
        language: {
          search: "Filter cepat:",
          lengthMenu: "Tampilkan _MENU_ entri",
          info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
          emptyTable: "Tidak ada buku yang sesuai dengan kata kunci pencarian.",
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "Berikutnya",
            previous: "Sebelumnya"
          }
        }
      });

      $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
      });
    });
  </script>
</body>
</html>
