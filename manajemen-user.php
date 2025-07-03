<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
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
        body {
            padding-top: 56px;
        }
        .navbar-brand {
            font-weight: bold;
            color: #ffffff !important;
        }
        .nav-link {
            color: #f8f9fa !important;
        }
        .dashboard-section {
            padding: 30px;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
        .btn-approve {
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
            background-color: #4CAF50;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-approve:hover {
            background-color: #45a049;
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .toast {
            background-color: #fff;
            border-left: 5px solid #4CAF50;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .search-bar {
            margin-bottom: 20px;
            width: 100%;
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

    <div class="container dashboard-section">
        <h3><i class="fas fa-users"></i>    User Management</h3>
        <table id="membersTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data akan diisi menggunakan JavaScript -->
            </tbody>
        </table>
    </div>


    <!-- Toast Notification -->
    <div class="toast-container">
        <div class="toast" id="toast-notification">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-message">
                <!-- Message will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi DataTables
            const table = $('#membersTable').DataTable({
                ajax: {
                    url: 'approve_member.php?action=fetch', // Ganti dengan URL endpoint Anda
                    dataSrc: '' // Sesuaikan dengan format respons JSON
                },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    {
                        data: 'status',
                        render: function (data) {
                            return data === 'approved'
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-warning">Pending</span>';
                        }
                    },
                    {
                        data: null,
                        render: function (data) {
                            return data.status === 'pending'
                                ? `<button class="btn btn-success btn-approve" data-id="${data.id}">Approve</button>`
                                : '';
                        }
                    }
                ],
                responsive: true,
                lengthMenu: [5, 10, 25, 50],
                pageLength: 5
            });
    $(document).ready(function () {
    let selectedMemberIds = [];

    // Event handler untuk tombol Approve
    $('#membersTable').on('click', '.btn-approve', function () {
                const memberId = $(this).data('id');
                $.ajax({
                    url: 'approve_member.php',
                    method: 'POST',
                    data: { approve_ids: [memberId] },
                    success: function () {
                        table.ajax.reload();
                        alert('Member approved successfully!');
                    },
                    error: function () {
                        alert('Failed to approve the member!');
                    }
                });
            });
        });

    // Fetch pending and approved members
    function fetchMembers() {
        $('#loading-spinner').show();
        $.ajax({
            url: 'approve_member.php?action=fetch',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#loading-spinner').hide();
                const membersTableBody = $('#members-table tbody');
                membersTableBody.empty();

                if (data.length > 0) {
                    data.forEach(function (member) {
                        membersTableBody.append(`
                            <tr class="member-row" data-id="${member.id}">
                                <td>${member.id}</td>
                                <td>${member.name}</td>
                                <td>${member.email}</td>
                                <td><span class="badge ${member.status === 'approved' ? 'bg-success' : 'bg-warning'}">${member.status === 'approved' ? 'Active' : 'Pending'}</span></td>
                                <td>
                                    ${member.status === 'pending' ?
                                        `<input type="checkbox" class="approve-checkbox" data-id="${member.id}" />`
                                        : ''}
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    membersTableBody.append('<tr><td colspan="5">No members found.</td></tr>');
                }
            }
        });
    }

    // Handle single approve button click
    $(document).on('change', '.approve-checkbox', function () {
        const memberId = $(this).data('id');
        if (this.checked) {
            selectedMemberIds.push(memberId);
        } else {
            selectedMemberIds = selectedMemberIds.filter(id => id !== memberId);
        }
    });

    // Approve selected members
    $('#approve-selected').on('click', function () {
        if (selectedMemberIds.length > 0) {
            $.ajax({
                url: 'approve_member.php',
                method: 'POST',
                data: { approve_ids: selectedMemberIds },
                success: function () {
                    // Update the UI for approved members
                    selectedMemberIds.forEach(function (id) {
                        const row = $(`tr[data-id="${id}"]`);
                        row.find('.badge').removeClass('bg-warning').addClass('bg-success').text('Active');
                        row.find('input[type="checkbox"]').prop('disabled', true);

                        // Update status in the database as well
                        $.ajax({
                            url: 'approve_member.php',
                            method: 'POST',
                            data: { setActive: id },
                            success: function () {
                                console.log('User status updated to Active in database');
                            }
                        });
                    });
                    showToast('Members approved successfully!', true);
                },
                error: function () {
                    showToast('Failed to approve members!', false);
                }
            });
        } else {
            showToast('No members selected!', false);
        }
    });

    // Show toast notification
    function showToast(message, success) {
        const toastMessage = $('#toast-message');
        const toast = $('#toast-notification');

        toastMessage.text(message);
        if (success) {
            toast.removeClass('bg-danger').addClass('bg-success');
        } else {
            toast.removeClass('bg-success').addClass('bg-danger');
        }

        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
    }

    // Initial fetch of members
    fetchMembers();

    // Search functionality
    $('#searchInput').on('keyup', function () {
        const searchQuery = $(this).val().toLowerCase();
        $('.member-row').each(function () {
            const name = $(this).find('td').eq(1).text().toLowerCase();
            const email = $(this).find('td').eq(2).text().toLowerCase();
            if (name.indexOf(searchQuery) > -1 || email.indexOf(searchQuery) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
</body>
</html>