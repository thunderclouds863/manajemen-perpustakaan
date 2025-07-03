<?php
session_start(); // Memulai sesi

// Hancurkan semua data sesi
session_unset();  // Menghapus semua variabel sesi
session_destroy(); // Menghancurkan sesi

// Redirect ke halaman login atau halaman utama
header("Location: login.php");
exit;
?>
