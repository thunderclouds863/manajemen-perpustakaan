
<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan ke halaman register.php
    header('Location: register.php');
    exit(); // Hentikan eksekusi lebih lanjut
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ruang Baca Teknik Industri</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
      <!-- Bootstrap CSS -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      color: white;
      background: linear-gradient(to bottom, #003366, #004080, #00509e);
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

    /* Logo CSS */
    .navbar-brand .logo {
      width: 40px;
      height: 40px;
      background: linear-gradient(45deg, #f0ad4e, #d9534f);
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-weight: bold;
      font-size: 18px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    .content {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  text-align: center;
  position: relative;
  background: linear-gradient(45deg, #ff7f50, #87cefa, #6a5acd, #ff1493); /* Gradasi bergerak */
  background-size: 400% 400%;
  animation: gradient-animation 15s ease infinite;
}

@keyframes gradient-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Mengatur posisi ikon buku terbuka dan pena */
.floating-icon {
  position: absolute;
  top: 10%; /* Mengatur posisi di atas */
  font-size: 6rem; /* Ukuran ikon yang lebih besar */
  color: white; /* Warna ikon */
  animation: float 4s ease-in-out infinite;
}

/* Animasi mengapung */
@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}

h1 {
  font-size: 3rem;
  color: white;
}


@keyframes gradient-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}


    .content h1 {
      font-size: 3rem;
      font-weight: bold;
      text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.8);
      margin-bottom: 20px;
    }


    .services {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  padding: 40px;
  background: rgba(255, 255, 255, 0.9);
  color: black;
  gap: 20px;
  text-align: center;
  position: relative;
}

.service-card {
  width: 300px;
  padding: 20px;
  background: linear-gradient(to bottom, #fff, #f0f8ff);
  border-radius: 15px;
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s, box-shadow 0.3s;
}

.service-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}

.service-card .icon {
  margin-bottom: 15px;
}

.service-card .service-icon {
  width: 80px; /* Adjust icon size */
  height: 80px;
  object-fit: cover;
}

.service-card h3 {
  color: #00509e;
  margin-bottom: 10px;
}

.service-card p {
  font-size: 0.9rem;
  color: #555;
}
/* Style the service cards as clickable links */
.service-link {
  display: block;  /* Ensure the entire card is clickable */
  text-decoration: none;
  color: inherit;  /* Inherit text color */
}

.service-link:hover .service-card {
  transform: translateY(-10px);  /* Optional: Add hover effect for links */
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}



    .footer {
      text-align: center;
      padding: 20px;
      background: #003366;
      color: #aaa;
      position: relative;
    }

    .footer span {
      color: #ddd;
    }

    .footer::after {
      content: '';
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80%;
      height: 2px;
      background: linear-gradient(to right, #ff7f50, #87cefa);
    }

    @media (max-width: 768px) {
      .content h1 {
        font-size: 2rem;
      }

      .service-card {
        width: 90%;
      }
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

<!-- Main Content -->
<div class="content">
  <!-- Floating Icon (Buku Terbuka dan Pena) -->
  <div class="floating-icon">
    <i class="fas fa-book-open"></i> <i class="fas fa-pen"></i> <!-- Ikon Buku Terbuka dan Pena -->
  </div>
  <h1>Ruang Baca <br>Teknik Industri</h1>
</div>



<!-- Services Section -->
<div class="services">
  <div class="service-card">
    <a href="katalog.php" class="service-link"> <!-- Link to katalog.php -->
      <div class="icon">
        <img src="katalog.png" alt="Katalog Icon" class="service-icon">
      </div>
      <h3>Katalog</h3>
      <p>Buku, KP, Tugas Akhir, Thesis</p>
    </a>
  </div>

  <div class="service-card">
    <a href="peminjaman_buku_pengguna.php" class="service-link"> <!-- Link to peminjaman.php -->
      <div class="icon">
        <img src="peminjaman.png" alt="Peminjaman Icon" class="service-icon">
      </div>
      <h3>Peminjaman</h3>
      <p>Member Only, Max. 2 Weeks</p>
    </a>
  </div>

  <div class="service-card">
    <a href="peminjaman_buku_pengguna.php" class="service-link"> <!-- Link to pengembalian.php -->
      <div class="icon">
        <img src="pengembalian.png" alt="Pengembalian Icon" class="service-icon">
      </div>
      <h3>Pengembalian</h3>
      <p>Member Only, Extendable (T&C Apply)</p>
    </a>
  </div>
</div>



  <!-- Footer Section -->
  <div class="footer">
    <span>&copy; Kelompok 02 APSI 2024 - RBTI UNDIP. All Rights Reserved.</span>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const loginLogoutLink = document.getElementById('loginLogoutLink');
    if (isLoggedIn) {
      loginLogoutLink.textContent = 'Logout';
      loginLogoutLink.href = 'logout.php';
    } else {
      loginLogoutLink.textContent = 'Login/Sign Up';
      loginLogoutLink.href = 'register.php';
    }
  </script>
</body>
</html>
