<?php


try {
  $conn = new mysqli('localhost', 'root', '', 'rbti');

  if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookID = $_POST['bookID'];
    $availableCopies = $_POST['availableCopies'];

    // 1. Ambil nilai availableCopies saat ini dari database
    $selectQuery = "SELECT availableCopies FROM Book WHERE bookID = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("i", $bookID);
    $stmt->execute();
    $stmt->bind_result($currentAvailableCopies);
    $stmt->fetch();
    $stmt->close();

    // 2. Tentukan status berdasarkan availableCopies setelah update
    $newStatus = ($availableCopies == 0) ? 'unavailable' : 'available';

    // 3. Update availableCopies dan status berdasarkan data yang diperoleh
    $updateQuery = "UPDATE Book SET availableCopies = ?, status = ? WHERE bookID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("isi", $availableCopies, $newStatus, $bookID);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Book status updated successfully']);
  }
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
  if (isset($conn)) {
    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Roboto', Arial, sans-serif;
        background-color: #f8f9fa;
        color: #495057;
        padding-top: 56px; /* Sesuaikan dengan tinggi navbar */
    }

    .navbar {
        position: fixed; /* Navbar tetap di atas */
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1030;
        background-color: #2c3e50;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        font-weight: bold;
        color: #ffffff !important;
    }

    .nav-link {
        color: #f8f9fa !important;
    }

    .table thead {
        background-color: #e9ecef;
    }

    .btn {
        border-radius: 30px;
    }

    .btn-primary {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }

    .btn-success {
        background-color: #20c997;
        border-color: #20c997;
    }

    .btn-danger {
        background-color: #e74c3c;
        border-color: #e74c3c;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: none;
        border-color: #6f42c1;
    }

    .modal-content {
        border-radius: 15px;
    }

    .search-bar {
        margin-bottom: 20px; /* Tambahkan jarak antara search bar dan tabel */
    }

    /* Tabel dengan garis horizontal */
    .table-bordered {
        border-collapse: collapse;
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }

    /* Warna selang-seling untuk baris tabel */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #ffffff; /* Warna putih */

    }

    .table-striped tbody tr:nth-of-type(even) {
        background-color: #f9f9f10; /* Warna terang */

    }

    .table-hover tbody tr:hover {
        background-color: #f1f1f1; /* Warna ketika di-hover */
    }

    .table th,
    .table td {
        vertical-align: middle; /* Biar teks ada di tengah */
    }

    .table td:nth-child(5) {
        width: 15%; /* Available Copies */
    }
    .table td:nth-child(4) {
        width: 15%; /* Available Copies */
    }

    .table td:nth-child(7) {
        width: 20%; /* Actions */
    }

    .container h3 {
        margin-bottom: 35px; /* Menambah jarak antara judul dan tombol */
    }
    .actions-bar {
    margin-bottom: 10px; /* Jarak antara tombol dan tabel */
    display: flex;
    justify-content: space-between; /* Untuk meratakan tombol dan search bar */
    align-items: center;
}

.search-bar input {
    max-width: 300px; /* Atur lebar input search */
}


    .btn-edit,
    .btn-delete {
        margin-right: 10px; /* Memberi jarak antar tombol di kolom Actions */
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

    <!-- Content -->
    <div class="container mt-4">
        <h3><i class="fas fa-book"></i> Manage Books</h3>
        <div class="actions-bar">
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
            <i class="fas fa-plus"></i> Add Book
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadSpreadsheetModal">
            <i class="fas fa-file-upload"></i> Upload Spreadsheet
        </button>
    </div>
</div>

<table class="table table-bordered table-striped table-hover" id="booksTable">
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Genre</th>
            <th>Total Copies</th>
            <th>Available Copies</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <!-- Dynamic Content -->
    </tbody>
</table>

    </div>

        <!-- Add Book Modal -->
        <div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookForm">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label>Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label>Genre</label>
                            <select class="form-select" name="genre" required>
                                <option>Sirkulasi</option>
                                <option>Referensi</option>
                                <option>Tugas Akhir</option>
                                <option>KP</option>
                                <option>Thesis</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Total Copies</label>
                            <input type="number" class="form-control" name="totalCopies" required>
                        </div>
                        <div class="mb-3">
                            <label>Available Copies</label>
                            <input type="number" class="form-control" name="availableCopies" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Spreadsheet Modal -->
    <div class="modal fade" id="uploadSpreadsheetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Spreadsheet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadSpreadsheetForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Select File (CSV)</label>
                            <input type="file" class="form-control" name="spreadsheet" required>
                        </div>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editBookForm">
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label>Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label>Genre</label>
                            <select class="form-select" name="genre" required>
                                <option>Sirkulasi</option>
                                <option>Referensi</option>
                                <option>Tugas Akhir</option>
                                <option>KP</option>
                                <option>Thesis</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Total Copies</label>
                            <input type="number" class="form-control" name="totalCopies" required>
                        </div>
                        <div class="mb-3">
                            <label>Available Copies</label>
                            <input type="number" class="form-control" name="availableCopies" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> <!-- DataTables JS -->
    <script>
  $(document).ready(function () {
    // Inisialisasi DataTable
    const table = $('#booksTable').DataTable({
        ajax: {
            url: 'get_books.php',
            dataSrc: '',
        },
        columns: [
            { data: 'title' },
            { data: 'author' },
            { data: 'genre' },
            { data: 'totalCopies' },
            { data: 'availableCopies' },
            { data: 'status' },
            {
                data: null,
                render: function (data) {
                    return `
                        <button class="btn btn-primary btn-sm btn-edit" data-id="${data.bookID}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="${data.bookID}">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    `;
                }
            }
        ]
    });

    // Event Delete
    $('#booksTable tbody').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this book?')) {
            $.ajax({
                url: 'delete_book.php',
                method: 'POST',
                data: { id },
                success: function () {
                    table.ajax.reload();
                },
                error: function () {
                    alert('Failed to delete book.');
                }
            });
        }
    });

    // Event Edit
    $('#booksTable tbody').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        // Logika untuk membuka modal edit dengan data yang relevan
    });
});
$('#searchBar').on('input', function () {
    const searchQuery = $(this).val().trim(); // Ambil nilai input
    fetchBooks(searchQuery); // Panggil fetchBooks dengan query pencarian
});

            // Add Book
            $('#addBookForm').submit(function (e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: 'add_books.php',
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response === 'success') {
                            alert('Book added successfully!');
                            $('#addBookModal').modal('hide');
                            fetchBooks();
                            location.reload();
                        } else {
                            alert('Error adding book: ' + response);
                        }
                    },
                    error: function () {
                        alert('Error adding book.');
                    }
                });
            });

            // Upload Spreadsheet
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
                            alert('Spreadsheet uploaded successfully!');
                            $('#uploadSpreadsheetModal').modal('hide');
                            fetchBooks();
                        } else {
                            alert('Error uploading spreadsheet: ' + response);
                        }
                    },
                    error: function () {
                        alert('Error uploading spreadsheet.');
                    }
                });
            });


    </script>
</body>

</html>