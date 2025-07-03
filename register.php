<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Informasi Ruang Baca</title>
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #333;
      min-height: 100vh;
      padding: 20px 0;
      overflow-y: auto;
    }

    .container {
      background: rgba(255, 255, 255, 0.95);
      width: 90%;
      max-width: 500px;
      margin: 20px auto;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo {
      width: 120px;
      height: 120px;
      margin-bottom: 15px;
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-10px);
      }

      100% {
        transform: translateY(0px);
      }
    }

    h1 {
      font-size: 2.2rem;
      color: #2c3e50;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 700;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-section {
      display: none;
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.5s ease;
    }

    .form-section.active {
      display: block;
      opacity: 1;
      transform: translateY(0);
      animation: slideUp 0.5s ease forwards;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
    }

    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #666;
    }

    input {
      width: 100%;
      padding: 15px 15px 15px 45px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    input:focus {
      border-color: #667eea;
      box-shadow: 0 0 15px rgba(102, 126, 234, 0.1);
      outline: none;
    }

    .btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      width: 100%;
      margin: 10px 0;
      position: relative;
      overflow: hidden;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn:active {
      transform: translateY(0);
    }

    .btn-outline {
      background: transparent;
      border: 2px solid #667eea;
      color: #667eea;
    }

    .btn-outline:hover {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .options {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 20px;
    }

    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 20px 0;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #e0e0e0;
    }

    .divider span {
      padding: 0 10px;
      color: #666;
      font-size: 0.9rem;
    }

    .alert {
      padding: 15px;
      border-radius: 10px;
      margin: 15px 0;
      display: none;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .feature-icons {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin: 30px 0;
    }

    .feature-icon {
      text-align: center;
      color: #667eea;
    }

    .feature-icon i {
      font-size: 2rem;
      margin-bottom: 10px;
    }

    .loading-spinner {
      display: none;
      text-align: center;
      margin: 20px 0;
    }

    .loading-spinner i {
      font-size: 2rem;
      color: #667eea;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="logo-container">
      <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" alt="Library Logo" class="logo">
      <h1><i class="fas fa-book-reader"></i> Ruang Baca Teknik Industri</h1>
    </div>

    <div class="feature-icons">
      <div class="feature-icon">
        <i class="fas fa-book"></i>
        <p>Buku</p>
      </div>
      <div class="feature-icon">
        <i class="fas fa-users"></i>
        <p>Komunitas</p>
      </div>
      <div class="feature-icon">
        <i class="fas fa-graduation-cap"></i>
        <p>Edukasi</p>
      </div>
    </div>

    <!-- Visitor Section -->
    <div class="form-section active" id="visitor-section">
      <h2 class="text-center mb-4">Masuk Sebagai Pengunjung</h2>
      <form id="visitor-form" action="login-visitor.php" method="POST">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" name="name" placeholder="Nama Lengkap" required>
        </div>
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="Email" required>
        </div>
        <button type="submit" class="btn">
          <i class="fas fa-sign-in-alt"></i> Masuk
        </button>
      </form>

      <div class="divider">
        <span>atau</span>
      </div>
      <div class="options">
        <button class="btn btn-outline" id="go-to-member">
          <i class="fas fa-user-graduate"></i> Masuk Sebagai Anggota
        </button>
        <button class="btn btn-outline" id="go-to-admin">
          <i class="fas fa-user-shield"></i> Admin Login
        </button>
      </div>
    </div>

    <!-- Member Section -->
<div class="form-section" id="member-section">
  <h2 class="text-center mb-4">Login Anggota</h2>
  <form id="member-form" action="login.php" method="POST">
    <div class="input-group">
      <i class="fas fa-id-card"></i>
      <input type="text" placeholder="ID Anggota" name="nim" required>
    </div>
    <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" placeholder="Password" name="password" required>
    </div>
    <button type="submit" class="btn">
      <i class="fas fa-sign-in-alt"></i> Login
    </button>
  </form>
  <div class="options">
    <button class="btn btn-outline" id="go-to-register">
      <i class="fas fa-user-plus"></i> Daftar Baru
    </button>
    <button class="btn btn-outline" id="back-to-visitor">
      <i class="fas fa-arrow-left"></i> Kembali
    </button>
  </div>
</div>


    <!-- Registration Section -->
<div class="form-section" id="register-section">
  <h2 class="text-center mb-4">Pendaftaran Anggota Baru</h2>
  <form id="register-form" action="register-process.php" method="POST">
    <div class="input-group">
      <i class="fas fa-user"></i>
      <input type="text" placeholder="Nama Lengkap" name="name" required>
    </div>
    <div class="input-group">
      <i class="fas fa-id-badge"></i>
      <input type="text" placeholder="NIM" name="nim" required>
    </div>
    <div class="input-group">
      <i class="fas fa-envelope"></i>
      <input type="email" placeholder="Email" name="email" required>
    </div>
    <div class="input-group">
      <i class="fas fa-lock"></i>
      <input type="password" placeholder="Password" name="password" required>
    </div>
    <button type="submit" class="btn">
      <i class="fas fa-user-plus"></i> Daftar
    </button>
  </form>
  <div class="options">
    <button class="btn btn-outline" id="back-to-member">
      <i class="fas fa-arrow-left"></i> Kembali
    </button>
  </div>
  <div class="alert alert-success" id="register-success">
    <i class="fas fa-check-circle"></i> Pendaftaran berhasil! Silakan menunggu verifikasi admin.
  </div>
  <div class="alert alert-error" id="register-error">
    <i class="fas fa-exclamation-circle"></i> Pendaftaran gagal! Silakan coba lagi.
  </div>
</div>


    <!-- Admin Section -->
    <div class="form-section" id="admin-section" action="admin-login.php" method="POST">
      <h2 class="text-center mb-4">Admin Login</h2>
      <form id="admin-form" action="admin-login.php" method="POST">
        <div class="input-group">
          <i class="fas fa-user-shield"></i>
          <input type="text" placeholder="Username Admin" name="username" required>
        </div>
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" placeholder="Email Admin" name="email" required>
        </div>
        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input type="password" placeholder="Password" name="password" required>
        </div>
        <button type="submit" class="btn">
          <i class="fas fa-sign-in-alt"></i> Login Admin
        </button>
      </form>
      <div class="options">
        <button class="btn btn-outline" id="back-to-visitor-admin">
          <i class="fas fa-arrow-left"></i> Kembali
        </button>
      </div>
    </div>

    <div class="loading-spinner">
      <i class="fas fa-circle-notch"></i>
    </div>
  </div>

  <script>
    // Get all sections
    const sections = {
      visitor: document.getElementById('visitor-section'),
      member: document.getElementById('member-section'),
      register: document.getElementById('register-section'),
      admin: document.getElementById('admin-section')
    };

    // Get all navigation buttons
    const buttons = {
      goToMember: document.getElementById('go-to-member'),
      goToAdmin: document.getElementById('go-to-admin'),
      goToRegister: document.getElementById('go-to-register'),
      backToVisitor: document.getElementById('back-to-visitor'),
      backToVisitorAdmin: document.getElementById('back-to-visitor-admin'),
      backToMember: document.getElementById('back-to-member')
    };

    // Navigation function
    function showSection(sectionId) {
      Object.values(sections).forEach(section => {
        section.classList.remove('active');
      });
      sections[sectionId].classList.add('active');
    }

    // Add click events for navigation
    buttons.goToMember.addEventListener('click', () => showSection('member'));
    buttons.goToAdmin.addEventListener('click', () => showSection('admin'));
    buttons.goToRegister.addEventListener('click', () => showSection('register'));
    buttons.backToVisitor.addEventListener('click', () => showSection('visitor'));
    buttons.backToVisitorAdmin.addEventListener('click', () => showSection('visitor'));
    buttons.backToMember.addEventListener('click', () => showSection('member'));

    // Form submissions
const forms = document.querySelectorAll('form');
forms.forEach(form => {
    form.addEventListener('submit', (e) => {
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;

        // Show loading state
        submitButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
        submitButton.disabled = true;

        // Form will automatically submit after this
    });
});


    // Add hover effects to buttons
    document.querySelectorAll('.btn').forEach(button => {
      button.addEventListener('mouseover', () => {
        button.style.transform = 'translateY(-2px)';
      });
      button.addEventListener('mouseout', () => {
        button.style.transform = 'translateY(0)';
      });
    });
  </script>
</body>

</html>