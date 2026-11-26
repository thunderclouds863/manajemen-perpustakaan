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
            $row['isOverdue'] = (
                in_array($row['status'], ['borrowed', 'extended'], true)
                && !empty($row['returnDate'])
                && $row['returnDate'] < date('Y-m-d')
            );
            $data[] = $row;
        }
        $stmt->close();

        echo json_encode($data);
        exit;
    }

    // Handle POST requests (atomic admin circulation actions with row-level locking)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = strtolower(trim($_POST['action'] ?? ''));
        $borrowID = (int) ($_POST['borrowID'] ?? 0);

        if ($borrowID <= 0) {
            echo json_encode(['success' => false, 'message' => 'Borrow ID tidak valid.']);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Lock both the Borrow row and its Book row for update to prevent concurrent races
            $lockStmt = $conn->prepare(
                "SELECT b.borrowID, b.userID, b.bookID, b.status, b.extend_count, bk.title, bk.availableCopies, bk.totalCopies
                 FROM Borrow b
                 JOIN Book bk ON b.bookID = bk.bookID
                 WHERE b.borrowID = ?
                 FOR UPDATE"
            );
            $lockStmt->bind_param('i', $borrowID);
            $lockStmt->execute();
            $record = $lockStmt->get_result()->fetch_assoc();
            $lockStmt->close();

            if (!$record) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Catatan peminjaman tidak ditemukan.']);
                exit;
            }

            $userID = (int) $record['userID'];
            $bookID = (int) $record['bookID'];
            $bookTitle = (string) $record['title'];

            switch ($action) {
                case 'borrowing':
                    if ($record['status'] !== 'borrow pending') {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Permintaan ini tidak dalam status menunggu persetujuan pinjam.']);
                        exit;
                    }
                    if ((int) $record['availableCopies'] < 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Stok buku habis, tidak dapat menyetujui peminjaman.']);
                        exit;
                    }

                    $u2 = $conn->prepare(
                        "UPDATE Book
                         SET availableCopies = availableCopies - 1,
                             status = CASE WHEN availableCopies - 1 <= 0 THEN 'unavailable' ELSE 'available' END
                         WHERE bookID = ? AND availableCopies > 0"
                    );
                    $u2->bind_param('i', $bookID);
                    $u2->execute();
                    $bookUpdated = $u2->affected_rows;
                    $u2->close();

                    if ($bookUpdated !== 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Stok buku tidak mencukupi.']);
                        exit;
                    }

                    $u1 = $conn->prepare(
                        "UPDATE Borrow
                         SET status = 'borrowed', borrowDate = CURDATE(), returnDate = DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                         WHERE borrowID = ? AND status = 'borrow pending'"
                    );
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $borrowUpdated = $u1->affected_rows;
                    $u1->close();

                    if ($borrowUpdated !== 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Status peminjaman sudah berubah.']);
                        exit;
                    }

                    rbti_notify_user($conn, $userID, "Peminjaman \"{$bookTitle}\" telah disetujui. Batas pengembalian 14 hari.");
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil dikonfirmasi.']);
                    break;

                case 'reject_borrowing':
                    $u1 = $conn->prepare("UPDATE Borrow SET status = 'borrow rejected' WHERE borrowID = ? AND status = 'borrow pending'");
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $updated = $u1->affected_rows;
                    $u1->close();

                    if ($updated !== 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Hanya permintaan berstatus borrow pending yang dapat ditolak.']);
                        exit;
                    }

                    rbti_notify_user($conn, $userID, "Permintaan peminjaman \"{$bookTitle}\" ditolak oleh admin.");
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Permintaan peminjaman telah ditolak.']);
                    break;

                case 'extending':
                    if ((int) $record['extend_count'] >= 2) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Batas maksimal perpanjangan (2 kali) telah tercapai.']);
                        exit;
                    }

                    $u1 = $conn->prepare(
                        "UPDATE Borrow
                         SET returnDate = DATE_ADD(returnDate, INTERVAL 14 DAY),
                             extend_count = extend_count + 1,
                             status = 'extended'
                         WHERE borrowID = ? AND status = 'extend pending'"
                    );
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $updated = $u1->affected_rows;
                    $u1->close();

                    if ($updated !== 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Permintaan ini tidak dalam status menunggu perpanjangan.']);
                        exit;
                    }

                    rbti_notify_user($conn, $userID, "Perpanjangan masa pinjam \"{$bookTitle}\" (+14 hari) telah disetujui.");
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Perpanjangan masa pinjam berhasil dikonfirmasi.']);
                    break;

                case 'reject_extending':
                    $u1 = $conn->prepare("UPDATE Borrow SET status = 'borrowed' WHERE borrowID = ? AND status = 'extend pending'");
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $updated = $u1->affected_rows;
                    $u1->close();

                    if ($updated !== 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Hanya permintaan berstatus extend pending yang dapat ditolak.']);
                        exit;
                    }

                    rbti_notify_user($conn, $userID, "Permintaan perpanjangan \"{$bookTitle}\" ditolak. Harap kembalikan sesuai tenggat semula.");
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Permintaan perpanjangan ditolak.']);
                    break;

                case 'returning':
                    $u1 = $conn->prepare("UPDATE Borrow SET status = 'returned', returnDate = CURDATE() WHERE borrowID = ? AND status = 'return pending'");
                    $u1->bind_param('i', $borrowID);
                    $u1->execute();
                    $borrowUpdated = $u1->affected_rows;
                    $u1->close();

                    if ($borrowUpdated !== 1) {
                        $conn->rollback();
                        echo json_encode(['success' => false, 'message' => 'Permintaan ini tidak dalam status menunggu pengembalian.']);
                        exit;
                    }

                    $u2 = $conn->prepare("UPDATE Book SET availableCopies = LEAST(totalCopies, availableCopies + 1), status = 'available' WHERE bookID = ?");
                    $u2->bind_param('i', $bookID);
                    $u2->execute();
                    $u2->close();

                    rbti_notify_user($conn, $userID, "Pengembalian buku \"{$bookTitle}\" telah dikonfirmasi. Terima kasih.");
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Pengembalian buku berhasil dikonfirmasi.']);
                    break;

                default:
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
                    break;
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan transaksi.']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
