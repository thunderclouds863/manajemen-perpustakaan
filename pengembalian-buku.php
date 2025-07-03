<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengembalian Buku - Pengguna</title>
  <!-- Link to Bootstrap CSS for styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .card {
      margin: 20px 0;
    }

    .notification {
      background-color: #f0f0f0;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .notification-unread {
      background-color: #fff3cd;
    }

    .overdue {
      background-color: #f8d7da;
    }

    .extended {
      background-color: #d4edda;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Panel Pengguna</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Log Out</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <!-- Form Pencarian Buku yang Dipinjam -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Cari Buku yang Dipinjam</h5>
        <div class="mb-3">
          <input type="text" id="searchBook" class="form-control" placeholder="Cari berdasarkan judul buku..." />
        </div>
      </div>
    </div>

    <!-- Daftar Buku yang Dipinjam -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Daftar Buku yang Dipinjam</h5>
        <table class="table table-striped" id="borrowTable">
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
          <tbody id="borrowList">
            <!-- Data peminjaman buku akan ditampilkan di sini -->
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

  <!-- JQuery for AJAX -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    $(document).ready(function () {
      // Menampilkan data buku yang dipinjam
      function fetchBorrowedBooks() {
        $.ajax({
          url: 'backend_api.php', // Ganti dengan API Anda
          type: 'GET',
          dataType: 'json',
          success: function (data) {
            const borrowList = data.borrowList;
            const borrowTable = $('#borrowList');
            borrowTable.empty();
            borrowList.forEach(function (borrow) {
              const borrowDate = new Date(borrow.borrowDate);
              const returnDate = new Date(borrow.returnDate);
              const today = new Date();
              const overdue = today > returnDate;
              const overdueClass = overdue ? 'overdue' : '';
              const extensionClass = borrow.extensionCount >= 2 ? 'extended' : '';

              let extensionButton = '';
              if (borrow.status === 'pending' && borrow.extensionCount < 2) {
                extensionButton = `<button class="btn btn-info btn-sm" onclick="extendBorrow(${borrow.borrowID})">Perpanjang</button>`;
              } else if (borrow.status === 'pending' && borrow.extensionCount >= 2) {
                extensionButton = `<button class="btn btn-secondary btn-sm" disabled>Perpanjang</button>`;
              }

              borrowTable.append(`
                <tr class="${overdueClass} ${extensionClass}">
                  <td>${borrow.bookID}</td>
                  <td>${borrow.bookTitle}</td>
                  <td>${borrow.borrowDate}</td>
                  <td>${borrow.returnDate}</td>
                  <td>${borrow.status === 'pending' ? 'Sedang Dipinjam' : 'Kembali'}</td>
                  <td>${extensionButton}</td>
                </tr>
              `);
            });
          }
        });
      }

      // Pencarian Buku yang Dipinjam
      $('#searchBook').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('#borrowList tr').each(function () {
          const bookTitle = $(this).find('td').eq(1).text().toLowerCase();
          if (bookTitle.indexOf(searchTerm) > -1) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      });

      // Perpanjangan Peminjaman Buku
      window.extendBorrow = function (borrowID) {
        $.ajax({
          url: 'backend_api.php',
          type: 'POST',
          dataType: 'json',
          data: { action: 'extend', borrowID: borrowID },
          success: function (response) {
            alert(response.message);
            fetchBorrowedBooks(); // Update daftar buku setelah perpanjangan
          },
          error: function () {
            alert('Gagal memperpanjang peminjaman.');
          }
        });
      };

      // Ambil data buku yang dipinjam saat halaman dimuat
      fetchBorrowedBooks();
    });
  </script>
</body>

</html>
