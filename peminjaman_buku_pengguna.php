<?php
session_start();
require_once 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$borrowRecords = [];
$userID = $_SESSION['user_id'];

$sql = "SELECT Borrow.*, Book.title
        FROM Borrow
        JOIN Book ON Borrow.bookID = Book.bookID
        WHERE Borrow.userID = $userID";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrowRecords[] = $row;
    }
}


// Fetch available books for the dropdown
$availableBooks = [];
$result = $conn->query("SELECT bookID, title, author, availableCopies FROM Book
    WHERE availableCopies > 0 AND status != 'unavailable' AND genre = 'Sirkulasi'");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $availableBooks[] = $row;
    }
}

function checkDuplicateTitle($conn, $title) {
    $sql = "SELECT COUNT(*) as count FROM Book WHERE title = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0; // Return true if duplicates exist
}


// Handle book actions (borrow, extend, return)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'borrow') {
    $bookIdentifier = $_POST['bookIdentifier'];
    $isID = is_numeric($bookIdentifier);

    // Query based on identifier type
    if ($isID) {
        $bookQuery = $conn->query("SELECT * FROM Book WHERE bookID = '$bookIdentifier'");
    } else {
        // Check for duplicate titles
        if (checkDuplicateTitle($conn, $bookIdentifier)) {
            echo json_encode(['success' => false, 'message' => 'Multiple books found with this title. Please use Book ID instead.']);
            exit;
        }
        $bookQuery = $conn->query("SELECT * FROM Book WHERE title = '$bookIdentifier'");
    }


        // Check if the selected book is available and category is Sirkulasi
        $bookQuery = $conn->query("SELECT availableCopies, status, genre FROM Book WHERE bookID = $bookIdentifier");
        $book = $bookQuery->fetch_assoc();

        if (!$book) {
            echo json_encode(['success' => false, 'message' => 'Book not found.']);
            exit;
        }


// Inside book borrowing logic
if ($book['availableCopies'] <= 0 || $book['status'] === 'unavailable') {
    echo json_encode(['success' => false, 'message' => 'This book is unavailable for borrowing.']);
    exit;
}


        if ($book['genre'] !== 'Sirkulasi') {
            echo json_encode(['success' => false, 'message' => 'Only circulation books can be borrowed.']);
            exit;
        }

        $userID = $_SESSION['user_id'];
        $borrowDate = date('Y-m-d');
        $returnDate = date('Y-m-d', strtotime('+14 days'));
        $status = 'borrow pending';

        // Insert borrowing record
        $sql = "INSERT INTO Borrow (userID, bookID, borrowDate, returnDate, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $userID, $bookIdentifier, $borrowDate, $returnDate, $status);
        $stmt->execute();

        // Update book availability
        $updateBookSql = "UPDATE Book SET availableCopies = availableCopies - 1 WHERE bookID = ?";
        $updateStmt = $conn->prepare($updateBookSql);
        $updateStmt->bind_param("i", $bookID);
        $updateStmt->execute();

        echo json_encode(['success' => $stmt->affected_rows > 0, 'message' => 'Borrowing request submitted successfully! Pending admin approval.']);
    } elseif ($action === 'return') {
        $borrowID = $_POST['borrowID'];

        // Update the borrowing record to returned
        $sql = "UPDATE Borrow SET status = 'return pending', returnDate = NOW() WHERE borrowID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $borrowID);
        $stmt->execute();

        echo json_encode(['success' => $stmt->affected_rows > 0, 'message' => 'Returning request submitted successfully! Pending admin approval.']);
    } elseif ($action === 'extend') {
        $borrowID = $_POST['borrowID'];

        // Extend the borrowing period
        $sql = "UPDATE Borrow SET returnDate = DATE_ADD(returnDate, INTERVAL 14 DAY), status = 'extend pending' WHERE borrowID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $borrowID);
        $stmt->execute();

        echo json_encode(['success' => $stmt->affected_rows > 0, 'message' => 'Extending request submitted successfully! Pending admin approval.']);
    }
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Panel - Peminjaman Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="fas fa-book"></i> Library Member</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="katalog-pengguna.php"><i class="fas fa-book"></i> Catalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pencarian-buku.php"><i class="fas fa-book"></i> Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="peminjaman_buku_pengguna.php"><i class="fas fa-bookmark"></i> Borrowing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 30px;">
    <h3><i class="fas fa-bookmark"></i>    Borrowing Histories</h3>
        <div class="row mb-3">
        <div class="col-md-6">
    <input type="text" id="search" class="form-control" placeholder="Search by Book ID or Borrow ID or Status...">
</div>

            <div class="d-flex justify-content-end mb-3">
                <!-- Add Borrowing Button -->
                <button class="btn btn-primary btn-lg d-flex align-items-center me-3" data-bs-toggle="modal" data-bs-target="#borrowModal">
                    <i class="fas fa-book-open me-2"></i>  Add Borrowing
                </button>
                <!-- Reserve Book Button
                <button class="btn btn-warning btn-lg d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#reserveModal">
                    <i class="fas fa-calendar-check me-2"></i>  Reserve Book
                </button> -->
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
                        <th>Book ID</th>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($borrowRecords)): ?>
    <?php foreach ($borrowRecords as $record): ?>
        <tr>
            <td><?= htmlspecialchars($record['borrowID']) ?></td>
            <td><?= htmlspecialchars($record['bookID']) ?></td>
            <td><?= htmlspecialchars($record['title']) ?></td>
            <td><?= htmlspecialchars($record['borrowDate']) ?></td>
            <td><?= htmlspecialchars($record['returnDate']) ?></td>
            <td><?= htmlspecialchars($record['status']) ?></td>
            <td>
                <?php if ($record['status'] === 'borrowed' || $record['status'] === 'extended'): ?>
                    <button class="btn btn-sm btn-success extend-btn" data-id="<?= $record['borrowID'] ?>">
                        <i class="fas fa-clock"></i> Extend
                    </button>
                    <button class="btn btn-sm btn-warning return-btn" data-id="<?= $record['borrowID'] ?>">
                        <i class="fas fa-undo"></i> Return
                    </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="text-center">No borrowing records found.</td>
    </tr>
<?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


    <!-- Borrow Modal -->
<!-- Replace your existing borrow modal with this -->
<div class="modal fade" id="borrowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Borrowing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="borrowForm">
                    <div class="mb-3">
                        <label for="bookSelect" class="form-label">Select Book</label>
                        <select class="form-select" id="bookSelect" name="bookIdentifier" required>
                            <option value="">Search by title/ID/author or select from list...</option>
                            <?php foreach ($availableBooks as $book): ?>
                                <option value="<?= htmlspecialchars($book['bookID']) ?>"
                                        data-title="<?= htmlspecialchars($book['title']) ?>"
                                        data-author="<?= htmlspecialchars($book['author']) ?>">
                                    <?= htmlspecialchars($book['author'])?> - <?=htmlspecialchars($book['title']) ?> - ID: <?= htmlspecialchars($book['bookID']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div id="bookDetails" class="alert alert-info" style="display: none;">
                            <p><strong>Selected Book Details:</strong></p>
                            <p id="selectedBookInfo"></p>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Borrowing Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- Reserve Modal -->
    <!-- <div class="modal fade" id="reserveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Reservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reserveForm">
                        <div class="mb-3">
                            <label for="bookID" class="form-label">Book ID</label>
                            <input type="text" class="form-control" id="bookID" name="bookID" required>
                        </div>
                        <div class="mb-3">
                            <label for="desiredBorrowDate" class="form-label">Desired Borrow Date</label>
                            <input type="date" class="form-control" id="desiredBorrowDate" name="desiredBorrowDate" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Submit Reservation Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
    // Initialize select2
    $('#bookSelect').select2({
        dropdownParent: $('#borrowModal'),
        placeholder: "Search by title/ID/author or select from list...",
        allowClear: true,
        width: '100%'
    });

    // Handle selection change
    $('#bookSelect').on('change', function() {
        const selected = $(this).find('option:selected');
        if (selected.val()) {
            const title = selected.data('title');
            const author = selected.data('author');
            $('#selectedBookInfo').html(`
                Title: ${title}<br>
                Author: ${author}<br>
                Book ID: ${selected.val()}
            `);
            $('#bookDetails').show();
        } else {
            $('#bookDetails').hide();
        }
    });
});
            $(document).ready(function () {
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
    });
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
        // Submit the borrowing form
        document.getElementById('borrowForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const selectedBook = document.getElementById('bookSelect');
    if (!selectedBook.value) {
        alert('Please select a book first');
        return;
    }

    // Get the selected option details
    const selectedOption = selectedBook.options[selectedBook.selectedIndex];
    const bookID = selectedBook.value;
    const bookTitle = selectedOption.getAttribute('data-title');

    // Create confirmation message
    const confirmMessage = `Are you sure you want to borrow:\nTitle: ${bookTitle}\nBook ID: ${bookID}`;

    if (confirm(confirmMessage)) {
        const formData = new FormData();
        formData.append('action', 'borrow');
        formData.append('bookIdentifier', bookID);

        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitButton.disabled = true;

        fetch('peminjaman_buku_pengguna.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;

            if (data.success) {
                alert(data.message);
                location.reload(); // Reload page to update records
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
    console.error('Error:', error);
    alert('An error occurred: ' + error.message);
});
    }
});

        // // Submit the reservation form
        // document.getElementById('reserveForm').addEventListener('submit', function(e) {
        //     e.preventDefault();

        //     const bookID = document.getElementById('bookID').value;
        //     const desiredBorrowDate = document.getElementById('desiredBorrowDate').value;
        //     const formData = new FormData();
        //     formData.append('action', 'reserve');
        //     formData.append('bookID', bookID);
        //     formData.append('desiredBorrowDate', desiredBorrowDate);

        //     fetch('peminjaman_buku_pengguna.php', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         alert(data.message);
        //         if (data.success) {
        //             location.reload();
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //     });
        // });
// Extend borrow action
document.querySelectorAll('.extend-btn').forEach(button => {
    button.addEventListener('click', function () {
        const borrowID = this.getAttribute('data-id');
        if (confirm('Are you sure you want to extend this borrowing?')) {
            fetch('peminjaman_buku_pengguna.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=extend&borrowID=${borrowID}`
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(error => console.error('Error:', error));
        }
    });
});

// Return book action
document.querySelectorAll('.return-btn').forEach(button => {
    button.addEventListener('click', function () {
        const borrowID = this.getAttribute('data-id');
        if (confirm('Are you sure you want to return this book?')) {
            fetch('peminjaman_buku_pengguna.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=return&borrowID=${borrowID}`
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(error => console.error('Error:', error));
        }
    });
});



    </script>
</body>
</html>
