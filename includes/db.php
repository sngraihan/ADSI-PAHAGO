<?php

// Konfigurasi database
$db_host = 'localhost';     // Host database, biasanya localhost
$db_user = 'root';          // Username database
$db_pass = '';              // Password database, biasanya kosong untuk XAMPP/Laragon
$db_name = 'pahago';        // Nama database

// Membuat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set karakter encoding
$conn->set_charset("utf8mb4");

// Fungsi untuk membersihkan input
function clean_input($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi untuk debugging query
function debug_query($query) {
    echo "<pre>";
    echo htmlspecialchars($query);
    echo "</pre>";
    exit;
}
?>