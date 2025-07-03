<?php
// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rbti";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil query pencarian dari parameter GET
$searchQuery = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

// Query database dengan filter pencarian
$sql = "SELECT * FROM book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

// Konversi hasil ke array
$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// Kembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode($books);

$stmt->close();
$conn->close();
?>
