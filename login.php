<?php
session_start();

$host = "localhost";
$user = "root";
$pass = ""; // Password default Laragon biasanya kosong
$db = "pahago";

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $full_name, $hashedPassword);
        $stmt->fetch();

        // Verifikasi password
        if (password_verify($password, $hashedPassword)) {
            // Set session
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['logged_in'] = true; // Menandai bahwa pengguna sudah login


            // Return success
            echo "success";
        } else {
            echo "Password salah!";
        }
    } else {
        echo "Email tidak ditemukan!";
    }

    $stmt->close();
}

$conn->close();

?>