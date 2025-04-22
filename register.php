<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = $_POST['fullName'] ?? '';
    $email    = $_POST['email'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$phone || !$password) {
        echo "Semua field harus diisi.";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Koneksi ke DB
    $conn = new mysqli("localhost", "root", "", "pahago");

    if ($conn->connect_error) {
        echo "Koneksi database gagal: " . $conn->connect_error;
        exit;
    }

    // Cek apakah email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "Email sudah terdaftar.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Simpan data baru
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);

    if ($stmt->execute()) {
        // Set session untuk menandai bahwa pengguna sudah login
        $_SESSION['user_id'] = $stmt->insert_id; // Menggunakan ID dari pengguna yang baru terdaftar
        $_SESSION['user_name'] = $name;
        $_SESSION['logged_in'] = true;

        echo "success";
    } else {
        echo "Gagal mendaftarkan pengguna.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Metode tidak diizinkan.";
}
?>