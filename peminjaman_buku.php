<!-- peminjaman_admin.php (Updated Admin Page) -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Peminjaman Buku</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1030;
      background-color: #2c3e50;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    body {
      padding-top: 56px;
    }

    .btn {
      padding: 5px 10px;
      font-size: 0.8rem;
      border-radius: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
      transform: translateY(-2px);
    }

    .btn i {
      font-size: 0.8rem;
    }

    .btn-lg {
      padding: 12px 24px;
    }

    .navbar-brand,
    .nav-link {
      color: white !important;
    }

    .card {
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .table th, .table td {
      vertical-align: middle;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php"><i class="fas fa-book"></i> Library Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manajemen-user.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manajemen_buku.php">
                            <i class="fas fa-book"></i> Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="peminjaman_buku.php">
                            <i class="fas fa-bookmark"></i> Borrowing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

  <div class="container" style="margin-top: 30px;">
  <h3><i class="fas fa-bookmark"></i>    Borrowing Management</h3>
    <div class="row mb-3">
    <div class="col-md-6">
    <input type="text" id="search" class="form-control" placeholder="Search by Book ID or Borrow ID or Status...">
</div>
      <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end gap-2">
          <!-- <button class="btn btn-lg btn-primary" data-bs-toggle="modal" data-bs-target="#borrowModal">
            <i class="fas fa-plus"></i> Add Borrowing
          </button>
          <button class="btn btn-lg btn-info" data-bs-toggle="modal" data-bs-target="#reserveModal">
            <i class="fas fa-bookmark"></i> Reserve Book
          </button> -->
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Borrowing Records</h5>
        <div class="table-responsive">
    <table class="table table-hover" id="borrowTable">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Book</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data akan di-load dengan AJAX -->
        </tbody>
    </table>
</div>

      </div>
    </div>
  </div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function () {
        // Fungsi untuk memuat data ke dalam tabel
        function loadBorrowings() {
            $.ajax({
                url: 'proses-pinjam.php',
                method: 'GET',
                data: {
                    action: 'fetch'
                },
                success: function (response) {
                    const tbody = $('#borrowTable tbody');
                    tbody.empty();

                    if (response.length === 0) {
                        tbody.append('<tr><td colspan="6" class="text-center">No records found</td></tr>');
                        return;
                    }

                    response.forEach(function (item) {
                        const statusClass = {
                            'borrowed': 'primary',
                            'returned': 'success',
                            'overdue': 'danger',
                            'extended': 'warning',
                            'reserved': 'info',
                            'unavailable': 'dark',
                            'borrow pending': 'secondary',
                            'extend pending': 'secondary',
                            'return pending': 'secondary'
                        }[item.status] || 'secondary';

                        tbody.append(`
                            <tr>
                                <td>${item.borrowID || '-'}</td>
                                <td>${item.bookTitle || item.bookID || '-'}</td>
                                <td>${item.borrowDate || '-'}</td>
                                <td>${item.returnDate || '-'}</td>
                                <td><span class="badge bg-${statusClass}">${item.status}</span></td>
                                <td>
                                    ${item.status === 'borrow pending' ? `<button class="btn btn-sm btn-success confirm-btn" data-id="${item.borrowID}" data-action="borrowing">Confirm Borrowing</button>` : ''}
                                    ${item.status === 'extend pending' ? `<button class="btn btn-sm btn-warning confirm-btn" data-id="${item.borrowID}" data-action="extending">Confirm Extending</button>` : ''}
                                    ${item.status === 'return pending' ? `<button class="btn btn-sm btn-danger confirm-btn" data-id="${item.borrowID}" data-action="returning">Confirm Returning</button>` : ''}
                                </td>
                            </tr>
                        `);
                    });

                    // Inisialisasi DataTable setelah data berhasil dimuat
                    if ($.fn.DataTable.isDataTable('#borrowTable')) {
                        $('#borrowTable').DataTable().destroy();
                    }

                    $('#borrowTable').DataTable({
                        "paging": true,         // Aktifkan pagination
                        "lengthChange": true,   // Opsi jumlah entri yang tampil
                        "searching": false,
                        "ordering": true,       // Aktifkan pengurutan kolom
                        "info": true,           // Tampilkan info "Showing X to Y of Z entries"
                        "autoWidth": false,     // Sesuaikan lebar kolom
                        "language": {
                            "paginate": {
                                "previous": "Previous",
                                "next": "Next"
                            },
                            "lengthMenu": "Show _MENU_ entries",
                            "search": "Search:",
                            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                            "infoFiltered": "(filtered from _MAX_ total entries)"
                        }
                    });
                },
                error: function (xhr) {
                    console.error("Error fetching data:", xhr.responseText);
                }
            });
        }

        // Panggil fungsi loadBorrowings untuk memuat data awal
        loadBorrowings();
        document.getElementById('search').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#borrowTable tbody tr');

    rows.forEach(row => {
        const bookID = row.cells[1].textContent.toLowerCase();
        const borrowID = row.cells[0].textContent.toLowerCase();
        const status = row.cells[4].textContent.toLowerCase();

        // Cek apakah salah satu kolom sesuai dengan filter
        if (bookID.includes(filter) || borrowID.includes(filter) || status.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

        // Fungsi untuk konfirmasi tindakan
        $(document).on('click', '.confirm-btn', function () {
            const borrowID = $(this).data('id');
            const action = $(this).data('action');

            $.ajax({
                url: 'proses-pinjam.php',
                method: 'POST',
                data: {
                    action: action,
                    borrowID: borrowID
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                    } else {
                        alert('Error: ' + response.message);
                    }
                    loadBorrowings();
                    location.reload();
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
