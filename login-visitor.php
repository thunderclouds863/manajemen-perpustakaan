<?php
session_start();
include('db.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    try {
        // Directly insert the new visitor into the table
        $stmt = $pdo->prepare("INSERT INTO visitor (name, email) VALUES (:name, :email)");
        $stmt->execute(['name' => $name, 'email' => $email]);

        // Set a success message
        $_SESSION['message'] = "Welcome, " . htmlspecialchars($name) . "! Your visit has been logged.";

        // Redirect to the main index or dashboard page
        header("Location: index-visitor.php");
        exit();
    } catch (PDOException $e) {
        // Handle database errors gracefully
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
} else {
    // Redirect to the login page if accessed directly
    header("Location: register.php");
    exit();
}
?>
