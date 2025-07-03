<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $nim = $_POST['nim'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if NIM or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nim = :nim OR email = :email");
    $stmt->execute(['nim' => $nim, 'email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        // If user already exists
        $_SESSION['error'] = "NIM or email already registered.";
        header("Location: register.php#register-section");
        exit();
    }

    // Insert the new user into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, nim, email, password, status) VALUES (:name, :nim, :email, :password, 'pending')");
        $stmt->execute([
            'name' => $name,
            'nim' => $nim,
            'email' => $email,
            'password' => $password // Store the plain password
        ]);

        // Successful registration
        $_SESSION['success'] = "Registration successful! Please wait for admin verification.";
        header("Location: register.php#register-section");
        exit();
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        header("Location: register.php#register-section");
        exit();
    }
}
?>
