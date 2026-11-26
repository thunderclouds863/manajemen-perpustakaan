<?php
require_once 'auth.php';
rbti_require_admin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Anggota — Admin RBTI UNDIP</title>
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
              <a class="nav-link active" href="manajemen-user.php"><i class="fas fa-users"></i> Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="manajemen_buku.php"><i class="fas fa-book"></i> Books</a>
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
        <div class="rbti-eyebrow"><i class="fas fa-users"></i> Verifikasi Keanggotaan</div>
        <h1>Manajemen Anggota</h1>
        <p>Tinjau daftar anggota aktif dan setujui pendaftaran mahasiswa baru Ruang Baca Teknik Industri.</p>
      </div>
    </section>

    <section class="rbti-section">
      <div class="container">
        <div class="rbti-panel">
          <div class="rbti-panel-header">
            <h2 class="rbti-panel-title"><i class="fas fa-id-card"></i> Daftar Anggota Terdaftar</h2>
          </div>
          <div class="rbti-panel-body">
            <div class="rbti-table-wrap">
              <table id="membersTable" class="table rbti-table" style="width: 100%;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Email</th>
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

  <!-- Toast Notification -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080; margin-top: 72px;">
    <div class="toast align-items-center text-white border-0" id="toast-notification" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toast-message"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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
      const table = $('#membersTable').DataTable({
        ajax: {
          url: 'approve_member.php?action=fetch',
          dataSrc: ''
        },
        columns: [
          { data: 'id' },
          {
            data: 'name',
            render: function (data) {
              return '<strong style="color: var(--rbti-ink-900);">' + $('<div>').text(data).html() + '</strong>';
            }
          },
          {
            data: 'nim',
            render: function (data) {
              return $('<div>').text(data || '-').html();
            }
          },
          { data: 'email' },
          {
            data: 'status',
            render: function (data) {
              return data === 'approved'
                ? '<span class="rbti-badge rbti-badge--success">Active</span>'
                : '<span class="rbti-badge rbti-badge--warning">Pending</span>';
            }
          },
          {
            data: null,
            orderable: false,
            render: function (data) {
              return data.status === 'pending'
                ? `<div class="rbti-action-group">
                    <button class="btn btn-success rbti-btn-sm btn-approve" data-id="${data.id}"><i class="fas fa-check"></i> Approve</button>
                    <button class="btn btn-outline-danger rbti-btn-sm btn-reject" data-id="${data.id}"><i class="fas fa-times"></i> Reject</button>
                  </div>`
                : '<span class="text-muted small">Terverifikasi</span>';
            }
          }
        ],
        lengthMenu: [5, 10, 25, 50],
        pageLength: 10,
        language: {
          search: "Cari anggota:",
          lengthMenu: "Tampilkan _MENU_ entri",
          info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ anggota",
          paginate: { previous: "Sebelumnya", next: "Berikutnya" }
        }
      });

      function showToast(message, success) {
        const toastEl = document.getElementById('toast-notification');
        $('#toast-message').text(message);
        toastEl.classList.remove('bg-success', 'bg-danger');
        toastEl.classList.add(success ? 'bg-success' : 'bg-danger');
        new bootstrap.Toast(toastEl).show();
      }

      $('#membersTable').on('click', '.btn-approve', function () {
        const memberId = $(this).data('id');
        $.ajax({
          url: 'approve_member.php',
          method: 'POST',
          data: { approve_ids: [memberId] },
          success: function () {
            table.ajax.reload();
            showToast('Anggota berhasil disetujui!', true);
          },
          error: function () {
            showToast('Gagal menyetujui anggota!', false);
          }
        });
      });

      $('#membersTable').on('click', '.btn-reject', function () {
        const memberId = $(this).data('id');
        if (!confirm('Tolak dan hapus pendaftaran anggota ini?')) return;
        $.ajax({
          url: 'approve_member.php',
          method: 'POST',
          data: { reject_id: memberId },
          success: function () {
            table.ajax.reload();
            showToast('Pendaftaran anggota ditolak.', true);
          },
          error: function () {
            showToast('Gagal menolak pendaftaran.', false);
          }
        });
      });
    });
  </script>
</body>
</html>
