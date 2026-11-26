<?php
require_once 'auth.php';
rbti_require_admin(true);
require_once 'db.php';

// Fetch members with status 'pending' or 'approved'
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'fetch') {
    header('Content-Type: application/json');
    $stmt = $pdo->prepare("SELECT id, name, nim, email, status, created_at FROM users WHERE status IN ('pending', 'approved') ORDER BY FIELD(status, 'pending', 'approved'), id DESC");
    $stmt->execute();
    echo json_encode($stmt->fetchAll());
    exit();
}

// Approve one or more members
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_ids'])) {
    $rawIds = is_array($_POST['approve_ids']) ? $_POST['approve_ids'] : [$_POST['approve_ids']];
    $ids = array_values(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0));

    if (empty($ids)) {
        http_response_code(400);
        echo 'Tidak ada ID anggota yang valid.';
        exit();
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    echo 'Members approved successfully!';
    exit();
}

// Reject / remove pending registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_id'])) {
    $rejectId = (int) $_POST['reject_id'];
    if ($rejectId > 0) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND status = 'pending'");
        $stmt->execute([':id' => $rejectId]);
    }
    echo 'Pending registration rejected.';
    exit();
}

// Legacy single setActive endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setActive'])) {
    $userId = (int) $_POST['setActive'];
    if ($userId > 0) {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }
    echo "User status updated to 'Active'!";
    exit();
}
