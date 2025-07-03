<?php
session_start();
include('db.php'); // Ensure this includes your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Query to check NIM and approved status
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && $user['password'] === $password) {
            // Successful login
            $_SESSION['username'] = $user['username'];

            // Redirect to the dashboard or welcome page
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Invalid credentials
            $_SESSION['error'] = "Invalid NIM or password.";
            header("Location: register.php#member-section");
            exit();
        }
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        header("Location: register.php#member-section");
        exit();
    }
} else {
    // Redirect if accessed without submitting the form
    header("Location: register.php#member-section");
    exit();
}
?>
