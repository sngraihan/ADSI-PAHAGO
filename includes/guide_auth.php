<?php
/**
 * File: guide_auth.php
 * Deskripsi: File untuk validasi autentikasi pemandu wisata
 * Simpan file ini di folder includes/ dan gunakan pada setiap halaman yang memerlukan login
 */

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk memeriksa apakah pemandu wisata sudah login
 * @return bool True jika sudah login, false jika belum
 */
function isGuideLoggedIn() {
    return isset($_SESSION['guide_id']) && isset($_SESSION['guide_logged_in']) && $_SESSION['guide_logged_in'] === true;
}

/**
 * Fungsi untuk memastikan halaman hanya dapat diakses oleh pemandu wisata yang sudah login
 * Jika belum login, redirect ke halaman login
 */
function requireGuideLogin() {
    if (!isGuideLoggedIn()) {
        // Simpan halaman yang ingin diakses untuk redirect setelah login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirect ke halaman login
        header("Location: " . getGuideLoginUrl());
        exit();
    }
}

/**
 * Fungsi untuk mendapatkan URL halaman login pemandu
 * Menyesuaikan path berdasarkan struktur folder
 */
function getGuideLoginUrl() {
    // Deteksi apakah script berjalan di subfolder
    $script_path = $_SERVER['SCRIPT_NAME'];
    
    if (strpos($script_path, '/guide/') !== false) {
        return '../guide-login.php';
    } else {
        return 'guide-login.php';
    }
}
?>