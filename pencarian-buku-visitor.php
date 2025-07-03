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
    $searchQuery = '%' . $_POST['query'] . '%'; // Wildcards untuk query LIKE
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
    echo json_encode(['data' => $books]); // Kirim data dalam format DataTables
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Buku</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f8fa;
        }

        .navbar {
            position: fixed;
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

        header {
            background-color: #7fb3d5;
            color: white;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
        }

        .search-section {
            padding: 40px;
            background-color: #eaf6f9;
            text-align: center;
        }

        .search-section form {
            display: inline-block;
            max-width: 600px;
            width: 100%;
        }

        .search-section input {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 70%;
        }

        .search-section button {
            padding: 10px 20px;
            background-color: #5dade2;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .search-section button:hover {
            background-color: #3498db;
        }

        .results-section {
            padding: 40px;
        }

        footer {
            background-color: #7fb3d5;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .table-container {
            overflow-x: auto;
        }

        .icon-library {
            font-size: 5rem;
            color: #7fb3d5;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="index-visitor.php"><i class="fas fa-book"></i> Library Member</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index-visitor.php">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="katalog.php">
                                <i class="fas fa-book"></i> Catalog
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pencarian-buku-visitor.php">
                                <i class="fas fa-book"></i> Search
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
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <i class="fas fa-search icon-library"></i>
            <h2>Cari Buku</h2>
            <p>Temukan buku berdasarkan judul, penulis, atau genre yang tersedia di perpustakaan.</p>
            <form id="searchForm">
                <input type="text" id="searchInput" name="query" placeholder="Masukkan kata kunci pencarian..." required>
                <button type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>
    </section>

    <!-- Results Section -->
    <section class="results-section">
        <div class="container">
            <div class="table-container">
                <table id="booksTable" class="table table-striped table-bordered" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Genre</th>
                            <th>Total Salinan</th>
                            <th>Tersedia</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan dimuat oleh DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Ruang Baca Teknik Industri. All rights reserved.</p>
    </footer>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inisialisasi DataTables
            const table = $('#booksTable').DataTable({
                ajax: {
                    url: '', // Tidak perlu URL khusus karena sudah dalam satu file PHP
                    type: 'POST',
                    data: function (d) {
                        // Mengirimkan query pencarian ke server
                        d.query = $('#searchInput').val();  // Ambil nilai dari input pencarian
                    },
                    dataSrc: function (json) {
                        return json.data;
                    }
                },
                columns: [
                    { data: 'bookID' },
                    { data: 'title' },
                    { data: 'author' },
                    { data: 'genre' },
                    { data: 'totalCopies' },
                    { data: 'availableCopies' },
                    { data: 'status' }
                ],
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Reload DataTables ketika form dikirim
            $('#searchForm').on('submit', function (e) {
                e.preventDefault(); // Mencegah form melakukan refresh
                table.ajax.reload(); // Reload DataTables dengan query pencarian baru
            });
        });
    </script>
</body>

</html>
