<?php
session_start();
include('db.php');

// Fetch members with status 'pending' or 'approved'
if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
    $stmt = $pdo->prepare("SELECT id, name, email, status FROM users WHERE status IN ('pending', 'approved')");
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($members);
    exit();
}



// Approve multiple members
if (isset($_POST['approve_ids'])) {
    $userIds = $_POST['approve_ids'];
    $userIds = implode(',', array_map('intval', $userIds)); // Sanitize the IDs

    // Change status to 'approved' for selected members
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id IN ($userIds)");
    $stmt->execute();

    echo "Members approved successfully!";
    exit();
}

// Update user status to 'active' (for when user has been approved)
if (isset($_POST['setActive'])) {
    $userId = $_POST['setActive'];

    // Update user status to 'approved'
    $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = :id");
    $stmt->execute(['id' => $userId]);

    echo "User status updated to 'Active'!";
    exit();
}
?>
