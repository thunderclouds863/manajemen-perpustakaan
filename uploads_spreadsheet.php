<?php
include 'db_connection.php';

if ($_FILES['spreadsheet']['error'] == 0) {
    $file = $_FILES['spreadsheet']['tmp_name'];
    $fileType = pathinfo($_FILES['spreadsheet']['name'], PATHINFO_EXTENSION);

    if ($fileType == 'csv') {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Skip header row

        while (($data = fgetcsv($handle)) !== FALSE) {
            $query = "INSERT INTO Book (title, author, genre, totalCopies, availableCopies)
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssii", $data[0], $data[1], $data[2], $data[3], $data[4]);
            $stmt->execute();
        }
        fclose($handle);
        echo "success";
    } else {
        echo "Invalid file type. Please upload a CSV file.";
    }
} else {
    echo "Error uploading file.";
}
?>
