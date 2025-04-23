<!-- includes/guide-header.php -->
<?php
if (!isset($_SESSION)) session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PahaGo Guide</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    /* Tambahan dropdown dan sidebar toggle */
    .sidebar-toggle {
      cursor: pointer;
    }
    .dropdown-menu {
      left: auto;
      right: 0;
    }
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <div id="sidebar" class="bg-white border-end p-3" style="width: 250px; min-height: 100vh;">
    <h4 class="fw-bold">PahaGo<small class="text-primary">guide</small></h4>
    <ul class="nav flex-column mt-4">
      <li class="nav-item"><a href="status-perjalanan.php" class="nav-link">🗺️ Status Perjalanan</a></li>
      <li class="nav-item"><a href="konfirmasi-kehadiran.php" class="nav-link">✔️ Konfirmasi Kehadiran</a></li>
      <li class="nav-item"><a href="chat.php" class="nav-link">💬 Chat Pelanggan</a></li>
      <li class="nav-item"><a href="kelola-paket.php" class="nav-link active text-primary">🎒 Kelola Paket Wisata</a></li>
      <li class="nav-item"><a href="laporan.php" class="nav-link">📄 Laporan Perjalanan</a></li>
    </ul>
  </div>

  <!-- Content -->
  <div class="flex-grow-1">
    <!-- Topbar -->
    <nav class="navbar navbar-light bg-light shadow-sm px-4">
      <div class="d-flex align-items-center">
        <span class="sidebar-toggle me-3" onclick="toggleSidebar()">☰</span>
        <h5 class="mb-0">Kelola Paket Wisata</h5>
      </div>
      <div class="dropdown">
        <img src="../assets/img/profile.png" alt="Profile" width="40" height="40" class="rounded-circle dropdown-toggle" data-bs-toggle="dropdown" />
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </nav>

    <!-- Mulai konten utama -->
    <div class="container-fluid mt-4">
