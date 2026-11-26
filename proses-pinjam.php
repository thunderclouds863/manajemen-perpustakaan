<?php
require_once 'auth.php';
rbti_require_admin(true);
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Handle GET requests (fetch circulation records)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'fetch') {
        $search = trim($_GET['search'] ?? '');
        $like = '%' . $search . '%';

        $sql = "SELECT b.*, u.name AS userName, u.nim AS userNim, bk.title AS bookTitle
                FROM Borrow b
                LEFT JOIN users u ON b.userID = u.id
                LEFT JOIN Book bk ON b.bookID = bk.bookID
                WHERE CAST(b.userID AS CHAR) LIKE ?
                   OR CAST(b.bookID AS CHAR) LIKE ?
                   OR COALESCE(u.name, '') LIKE ?
                   OR COALESCE(bk.title, '') LIKE ?
                ORDER BY FIELD(b.status, 'borrow pending', 'extend pending', 'return pending', 'overdue', 'borrowed', 'extended', 'returned', 'borrow rejected', 'extend rejected'), b.borrowID DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $like, $like, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Flag overdue dynamically if past returnDate while still borrowed/extended
            if (in_array($row['status'], ['borrowed', 'extended'], true) && !empty($row['returnDate']) && $row['returnDate'] < date('Y-m-d')) {
                $row['isOverdue'] = true;
            } else {
                $row['isOverdue'] = false;
            }
            $data[] = $row;
        }
        $stmt->close();

        echo json_encode($data);
        exit;
    }

    // Handle POST requests (admin circulation actions)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = strtolower(trim($_POST['action'] ?? ''));
        $borrowID = (int) ($_POST['borrowID'] ?? 0);

        if ($borrowID <= 0) {
            echo json_encode(['success' => false, 'message' => 'Borrow ID tidak valid.']);
            exit;
        }

        // Fetch borrow record & book details
        $infoStmt = $conn->prepare(
            "SELECT b.borrowID, b.userID, b.bookID, b.status, b.extend_count, bk.title, bk.availableCopies, bk.totalCopies
             FROM Borrow b
             JOIN Book bk ON b.bookID = bk.bookID
             WHERE b.borrowID = ?"
        );
        $infoStmt->bind_param('i', $borrowID);
        $infoStmt->execute();
        $record = $infoStmt->get_result()->fetch_assoc();
        $infoStmt->close();

        if (!$record) {
            echo json_encode(['success' => false, 'message' => 'Catatan peminjaman tidak ditemukan.']);
            exit;
        }

        $userID = (int) $record['userID'];
        $bookID = (int) $record['bookID'];
        $bookTitle = (string) $record['title'];

        switch ($action) {
            case 'borrowing':
                if ($record['status'] !== 'borrow pending') {
                    echo json_encode(['success' => false, 'message' => 'Permintaan ini tidak dalam status menunggu persetujuan pinjam.']);
                    exit;
                }
                if ((int) $record['availableCopies'] < 1) {
                    echo json_encode(['success' => false, 'message' => 'Stok buku habis, tidak dapat menyetujui peminjaman.']);
                    exit;
                }

                $conn->begin_transaction();
                try {
                    $u1 = $conn->prepare("UPDATE Borrow SET status = 'borrowed', borrowDate = CURDATE(), returnDate = DATE_ADD(CURDATE(), INTERVAL 14 DAY) WHERE borrowID = ?");
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $u1->close();

                    $u2 = $conn->prepare("UPDATE Book SET availableCopies = availableCopies - 1, status = CASE WHEN availableCopies - 1 <= 0 THEN 'unavailable' ELSE 'available' END WHERE bookID = ? AND availableCopies > 0");
                    $u2->bind_param('i', $bookID);
                    $u2->execute();
                    $u2->close();

                    rbti_notify_user($conn, $userID, "Peminjaman \"{$bookTitle}\" telah disetujui. Batas pengembalian 14 hari.");

                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil dikonfirmasi.']);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => 'Gagal mengonfirmasi peminjaman.']);
                }
                break;

            case 'reject_borrowing':
                if ($record['status'] !== 'borrow pending') {
                    echo json_encode(['success' => false, 'message' => 'Hanya permintaan berstatus borrow pending yang dapat ditolak.']);
                    exit;
                }
                $u1 = $conn->prepare("UPDATE Borrow SET status = 'borrow rejected' WHERE borrowID = ?");
                $u1->bind_param('i', $borrowID);
                $u1->execute();
                $u1->close();

                rbti_notify_user($conn, $userID, "Permintaan peminjaman \"{$bookTitle}\" ditolak oleh admin.");
                echo json_encode(['success' => true, 'message' => 'Permintaan peminjaman telah ditolak.']);
                break;

            case 'extending':
                if ($record['status'] !== 'extend pending') {
                    echo json_encode(['success' => false, 'message' => 'Permintaan ini tidak dalam status menunggu perpanjangan.']);
                    exit;
                }
                $u1 = $conn->prepare("UPDATE Borrow SET returnDate = DATE_ADD(returnDate, INTERVAL 14 DAY), extend_count = extend_count + 1, status = 'extended' WHERE borrowID = ?");
                $u1->bind_param('i', $borrowID);
                $u1->execute();
                $u1->close();

                rbti_notify_user($conn, $userID, "Perpanjangan masa pinjam \"{$bookTitle}\" (+14 hari) telah disetujui.");
                echo json_encode(['success' => true, 'message' => 'Perpanjangan masa pinjam berhasil dikonfirmasi.']);
                break;

            case 'reject_extending':
                if ($record['status'] !== 'extend pending') {
                    echo json_encode(['success' => false, 'message' => 'Hanya permintaan berstatus extend pending yang dapat ditolak.']);
                    exit;
                }
                $u1 = $conn->prepare("UPDATE Borrow SET status = 'borrowed' WHERE borrowID = ?");
                $u1->bind_param('i', $borrowID);
                $u1->execute();
                $u1->close();

                rbti_notify_user($conn, $userID, "Permintaan perpanjangan \"{$bookTitle}\" ditolak. Harap kembalikan sesuai tenggat semula.");
                echo json_encode(['success' => true, 'message' => 'Permintaan perpanjangan ditolak.']);
                break;

            case 'returning':
                if ($record['status'] !== 'return pending') {
                    echo json_encode(['success' => false, 'message' => 'Permintaan ini tidak dalam status menunggu pengembalian.']);
                    exit;
                }

                $conn->begin_transaction();
                try {
                    $u1 = $conn->prepare("UPDATE Borrow SET status = 'returned', returnDate = CURDATE() WHERE borrowID = ?");
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $u1->close();

                    $u2 = $conn->prepare("UPDATE Book SET availableCopies = LEAST(totalCopies, availableCopies + 1), status = 'available' WHERE bookID = ?");
                    $u2->bind_param('i', $bookID);
                    $u2->execute();
                    $u2->close();

                    rbti_notify_user($conn, $userID, "Pengembalian buku \"{$bookTitle}\" telah dikonfirmasi. Terima kasih.");

                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Pengembalian buku berhasil dikonfirmasi.']);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => 'Gagal mengonfirmasi pengembalian.']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
                break;
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
