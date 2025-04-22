<?php
$host = "localhost";
$user = "root";
$pass = ""; // password default Laragon kosong
$db = "pahago";

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari form
$fullName = $_POST["fullName"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$password = $_POST["password"];
$confirmPassword = $_POST["confirmPassword"];

if ($password !== $confirmPassword) {
    echo "<script>alert('Konfirmasi password tidak cocok!'); window.location.href = 'register.html';</script>";
    exit();
}

$checkQuery = "SELECT * FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo "Email sudah terdaftar. Silakan gunakan email lain.";
    exit;
}


// Hash password untuk keamanan
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Simpan ke database
$sql = "INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $fullName, $email, $phone, $hashedPassword);

if ($stmt->execute()) {
    header("Location: index.html");
    exit();
} else {
    echo "<script>alert('Gagal mendaftar: Email sudah digunakan atau error lainnya'); window.location.href = 'register.html';</script>";
}

$stmt->close();
$conn->close();
?>
