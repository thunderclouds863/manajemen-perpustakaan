    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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

        .table-container {
            overflow-x: auto;
        }

        .icon-library {
            font-size: 5rem;
            color: #7fb3d5;
            margin-bottom: 20px;
        }
    </style>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-book"></i> Library Member</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="katalog-pengguna.php">
                            <i class="fas fa-book"></i> Catalog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pencarian-buku.php">
                            <i class="fas fa-book"></i> Search
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="peminjaman_buku_pengguna.php">
                            <i class="fas fa-bookmark"></i> Borrowings
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