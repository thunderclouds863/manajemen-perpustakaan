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

// Periksa apakah ini adalah permintaan Ajax untuk pencarian
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchQuery = '%' . $_POST['query'] . '%'; // Wildcards untuk query LIKE

    // Hitung total records tanpa filter
    $sql_count = "SELECT COUNT(*) AS total FROM Book";
    $result_count = $conn->query($sql_count);
    $totalRecords = $result_count->fetch_assoc()['total'];

    // Hitung total records dengan filter
    $sql_count_filtered = "SELECT COUNT(*) AS total FROM Book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ?";
    $stmt_count = $conn->prepare($sql_count_filtered);
    $stmt_count->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $stmt_count->execute();
    $result_filtered = $stmt_count->get_result();
    $totalFiltered = $result_filtered->fetch_assoc()['total'];

    // Query untuk mengambil data buku
    $query = "SELECT * FROM Book WHERE title LIKE ? OR author LIKE ? OR genre LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = [];

    // Ambil data buku
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    $stmt->close();

    // Kirim data dalam format DataTables
    echo json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalFiltered,
        'data' => $books
    ]);
    exit;
}
?>
