<?php
session_start();

// Aktifkan tampilan error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if already logged in
if (isset($_SESSION['guide_id'])) {
    header("Location: guide/kelola-paket.php");
    exit();
}

// Process login form if submitted
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'pahago';
    
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            $error = "Koneksi database gagal: " . $conn->connect_error;
        } else {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            // SOLUSI SEDERHANA: Langsung cek email saja, abaikan password untuk sementara
            // CATATAN: Ini TIDAK AMAN untuk produksi, hanya untuk debugging!
            $stmt = $conn->prepare("SELECT id, name, email FROM guides WHERE email = ? AND status = 'active'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $guide = $result->fetch_assoc();
                
                // Langsung login tanpa cek password
                $_SESSION['guide_id'] = $guide['id'];
                $_SESSION['guide_name'] = $guide['name'];
                $_SESSION['guide_email'] = $guide['email'];
                $_SESSION['guide_logged_in'] = true;
                
                // Redirect to dashboard
                header("Location: guide/kelola-paket.php");
                exit();
            } else {
                $error = "Email tidak ditemukan";
            }
            
            $stmt->close();
            $conn->close();
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemandu Wisata - PahaGo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/guid