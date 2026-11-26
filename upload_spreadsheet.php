<?php
require_once 'auth.php';
rbti_require_admin(true);
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['spreadsheet'])) {
    http_response_code(400);
    echo 'File spreadsheet tidak ditemukan.';
    exit;
}

$upload = $_FILES['spreadsheet'];
if ($upload['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo 'Gagal mengunggah file.';
    exit;
}

if ($upload['size'] > 2 * 1024 * 1024) {
    http_response_code(400);
    echo 'Ukuran file maksimal 2MB.';
    exit;
}

$ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    http_response_code(400);
    echo 'Format file tidak didukung. Harap unggah file CSV.';
    exit;
}

$handle = fopen($upload['tmp_name'], 'r');
if ($handle === false) {
    http_response_code(500);
    echo 'Gagal membaca file CSV.';
    exit;
}

$header = fgetcsv($handle);
$inserted = 0;

$stmt = $conn->prepare(
    "INSERT INTO Book (title, author, genre, totalCopies, availableCopies, status) VALUES (?, ?, ?, ?, ?, ?)"
);

while (($data = fgetcsv($handle)) !== false) {
    if (count($data) < 5) {
        continue;
    }
    $title = trim((string) $data[0]);
    $author = trim((string) $data[1]);
    $genre = trim((string) $data[2]);
    $total = max(0, (int) $data[3]);
    $avail = max(0, min($total, (int) $data[4]));

    if ($title === '' || $author === '' || $genre === '') {
        continue;
    }

    $status = ($avail === 0) ? 'unavailable' : 'available';
    $stmt->bind_param('sssiis', $title, $author, $genre, $total, $avail, $status);
    if ($stmt->execute()) {
        $inserted++;
    }
}

fclose($handle);
$stmt->close();
$conn->close();

if ($inserted > 0) {
    echo 'success';
} else {
    http_response_code(400);
    echo 'Tidak ada baris buku valid yang dapat diimpor. Pastikan kolom: title, author, genre, totalCopies, availableCopies.';
}
