<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog - Ruang Baca FTI UNDIP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Styles */
        body {
            font-family: 'Arial', sans-serif;
            padding: 60px 20px;
            background-color: #f8f9fa;
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

        .hero {
            background-color: #4a1c40;
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-bottom: 3px solid #fff;
        }

        .hero h1 {
            font-size: 48px;
            letter-spacing: 2px;
        }

        .catalog-container {
            margin-top: 120px;
        }

        .catalog-item {
            text-align: center;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            padding: 20px;
            overflow: hidden;
            text-decoration: none;
            display: block;
            color: #333;
        }

        .catalog-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .catalog-icon {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .catalog-icon:hover {
            transform: scale(1.1);
        }

        .catalog-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .catalog-item:hover .catalog-title {
            color: #007bff;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }

            .catalog-item {
                width: 100%;
            }
        }

        /* Custom Tooltip */
        .tooltip-inner {
            background-color: #5f39a3;
            color: #fff;
        }

        .catalog-container .row {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
    <img src="undip.png" alt="Undip Logo" style="height: 40px; margin-right: 10px;">
</a>

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
</header>

    <!-- Hero Section -->
    <div class="hero">
        <h1>KATALOG</h1>
    </div>

    <!-- Catalog Section -->
    <div class="container catalog-container">
        <div class="row g-4">
            <!-- Catalog Item 1 -->
            <div class="col-12 col-md-4 col-lg-2">
                <a href="https://docs.google.com/spreadsheets/d/1kPDNEL63OlwpQxpTRaIZDZvRtkBD-2aLA3pd0pPljmY/edit?usp=sharing" class="catalog-item" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Buku Sirkulasi">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' fill='%23333'/%3E%3C/svg%3E" alt="Buku Sirkulasi" class="catalog-icon">
                    <div class="catalog-title">BUKU SIRKULASI</div>
                </a>
            </div>

            <!-- Catalog Item 2 -->
            <div class="col-12 col-md-4 col-lg-2">
                <a href="https://docs.google.com/spreadsheets/d/138QbRq1M_Vg9DfUEkglhX5lBCHJtS1wlj2M2O78AxSA/edit?usp=sharing" class="catalog-item" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Buku Referensi">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' fill='%23333'/%3E%3C/svg%3E" alt="Buku Referensi" class="catalog-icon">
                    <div class="catalog-title">BUKU REFERENSI</div>
                </a>
            </div>

            <div class="col-12 col-md-4 col-lg-2 mb-4">
                <a href="https://docs.google.com/spreadsheets/d/1JkA9dIa4KtsvSoDlC1AaQ8vMgEtoXbviCaU3b1pnpXk/edit?gid=1246111956#gid=1246111956" class="catalog-item" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="KP">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' fill='%23333'/%3E%3C/svg%3E" alt="KP" class="catalog-icon">
                    <div class="catalog-title"> <br>KP</div>
                </a>
            </div>

            <!-- Catalog Item 4 -->
            <div class="col-12 col-md-4 col-lg-2 mb-4">
                <a href="https://docs.google.com/spreadsheets/d/1NJa4gIzub_4ENb4SLLLXfFUu7HjHqz0Aaw8mUp6BKWg/edit?usp=sharing" class="catalog-item" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Tugas Akhir">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' fill='%23333'/%3E%3C/svg%3E" alt="Tugas Akhir" class="catalog-icon">
                    <div class="catalog-title"> <br> TUGAS AKHIR</div>
                </a>
            </div>

            <!-- Catalog Item 5 -->
            <div class="col-12 col-md-4 col-lg-2 mb-4">
                <a href="https://docs.google.com/spreadsheets/d/1-OQw7NAnoGnwq_ImnC8S-AHD-5xk0aNaQjkz_RzWnyY/edit?usp=sharing" class="catalog-item" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Thesis">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z' fill='%23333'/%3E%3C/svg%3E" alt="Thesis" class="catalog-icon">
                    <div class="catalog-title"> <br> THESIS</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        var tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    </script>
</body>
</html>
