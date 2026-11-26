<?php
require_once 'auth.php';
rbti_require_member(true);
require_once 'db.php';

header('Content-Type: application/json');

$userID = (int) $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

switch ($action) {
    case 'extend':
        $borrowID = (int) ($_POST['borrowID'] ?? 0);
        $stmt = $pdo->prepare("SELECT borrowID, status, extend_count FROM Borrow WHERE borrowID = :id AND userID = :uid");
        $stmt->execute([':id' => $borrowID, ':uid' => $userID]);
        $row = $stmt->fetch();

        if (!$row || !in_array($row['status'], ['borrowed', 'extended'], true)) {
            echo json_encode(['success' => false, 'message' => 'Peminjaman tidak memenuhi syarat untuk diperpanjang.']);
            exit;
        }
        if ((int) $row['extend_count'] >= 2) {
            echo json_encode(['success' => false, 'message' => 'Batas maksimal perpanjangan (2 kali) telah tercapai.']);
            exit;
        }

        $upd = $pdo->prepare("UPDATE Borrow SET status = 'extend pending' WHERE borrowID = :id AND userID = :uid");
        $upd->execute([':id' => $borrowID, ':uid' => $userID]);
        echo json_encode(['success' => true, 'message' => 'Permintaan perpanjangan dikirim. Menunggu konfirmasi admin.']);
        break;

    case 'fetch':
    default:
        $stmt = $pdo->prepare(
            "SELECT b.borrowID, b.bookID, bk.title AS bookTitle, b.borrowDate, b.returnDate, b.status, b.extend_count AS extensionCount
             FROM Borrow b
             INNER JOIN Book bk ON b.bookID = bk.bookID
             WHERE b.userID = :uid AND b.status IN ('borrowed', 'extended', 'overdue', 'extend pending', 'return pending')
             ORDER BY b.borrowID DESC"
        );
        $stmt->execute([':uid' => $userID]);
        echo json_encode(['borrowList' => $stmt->fetchAll()]);
        break;
}
