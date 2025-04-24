<?php
// Include authentication file
require_once '../includes/guide_auth.php';

// Ensure guide is logged in
requireGuideLogin();

// Database connection
require_once '../includes/db.php';

// Get guide information
$guide_id = $_SESSION['guide_id'];
$guide_name = $_SESSION['guide_name'];

// Fetch packages for this guide
$packages = [];
$query = "SELECT * FROM packages WHERE guide_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $guide_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
}
$stmt->close();

function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket Wisata - PahaGo Guide</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/guide.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Additional styles to match the new design */
        .sidebar {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            font-size: 24px;
        }
        
        .sidebar-logo span {
            font-size: 16px;
            font-weight: normal;
        }
        
        .menu-item {
            border-radius: 8px;
            margin: 5px 15px;
            padding: 10px 15px;
        }
        
        .menu-item.active {
            background-color: #f0f7ff;
            border-left: none;
            color: #1e6aff;
            font-weight: 600;
        }
        
        .menu-item i {
            font-size: 16px;
            width: 24px;
        }
        
        .content {
            background-color: #f9fbff;
        }
        
        .content-header {
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .notification {
            cursor: pointer;
        }
        
        .btn-add {
            background-color: #1e6aff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: white;
        }
        
        .package-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        
        .package-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .package-status {
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background-color: #00C28C;
        }
        
        .status-draft {
            background-color: #FF9F1C;
        }
        
        .package-title {
            font-weight: 600;
            font-size: 18px;
            margin: 10px 0 15px;
        }
        
        .info-item {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-item i {
            color: #666;
        }
        
        .package-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-edit, .btn-delete {
            flex: 1;
            text-align: center;
            padding: 8px 0;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-edit {
            background-color: #f0f7ff;
            color: #1e6aff;
            border: 1px solid #1e6aff;
        }
        
        .btn-delete {
            background-color: #fff0f0;
            color: #ff3b3b;
            border: 1px solid #ff3b3b;
        }
        
        .user-profile {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 250px;
            padding: 15px;
            background-color: white;
            border-top: 1px solid #eaeaea;
        }
        
        .profile-wrapper {
            display: flex;
            align-items: center;
            padding: 10px;
            cursor: pointer;
            border-radius: 8px;
        }
        
        .profile-wrapper:hover {
            background-color: #f5f8ff;
        }
        
        .profile-dropdown {
            position: absolute;
            bottom: 80px;
            left: 20px;
            width: 210px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }
        
        .profile-dropdown.active {
            display: block;
        }
        
        .profile-dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
        }
        
        .profile-dropdown-item:hover {
            background-color: #f5f8ff;
        }

        @media (max-width: 1200px) {
            .package-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .package-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    PahaGo<span>guide</span>
                </div>
            </div>
            <div class="sidebar-menu">
                <a href="status-perjalanan.php" class="menu-item">
                    <i class="fas fa-route"></i>
                    <span>Status Perjalanan</span>
                </a>
                <a href="konfirmasi-kehadiran.php" class="menu-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Konfirmasi Kehadiran</span>
                </a>
                <a href="chat-pelanggan.php" class="menu-item">
                    <i class="fas fa-comments"></i>
                    <span>Chat Pelanggan</span>
                </a>
                <a href="kelola-paket.php" class="menu-item active">
                    <i class="fas fa-box"></i>
                    <span>Kelola Paket Wisata</span>
                </a>
                <a href="laporan-perjalanan.php" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan Perjalanan</span>
                </a>
            </div>

            <!-- User Profile -->
            <div class="user-profile">
                <div class="profile-wrapper" id="profileWrapper">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($guide_name); ?>&background=1e6aff&color=fff"
                        alt="Profile" class="profile-image">
                    <div class="profile-info">
                        <div class="profile-name"><?php echo htmlspecialchars($guide_name); ?></div>
                        <div class="profile-role">Pemandu Wisata</div>
                    </div>
                    <i class="fas fa-chevron-down profile-arrow"></i>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    <a href="../guide-logout.php" class="profile-dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="content-header">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="content-title">
                    <h1>Kelola Paket Wisata</h1>
                    <p>Kelola paket wisata Anda dengan mudah. Tambah, edit, atau hapus sesuai kebutuhan</p>
                </div>

                <div style="display: flex; align-items: center;">
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                        <div class="notification-badge">2</div>
                    </div>
                    <a href="tambah-paket.php" class="btn-add">
                        <i class="fas fa-plus"></i> Tambah Paket Wisata
                    </a>
                </div>
            </div>

            <?php if (empty($packages)): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>Belum Ada Paket Wisata</h3>
                    <p>Anda belum memiliki paket wisata. Mulai tambahkan paket wisata pertama Anda.</p>
                    <a href="tambah-paket.php" class="btn-add">
                        <i class="fas fa-plus"></i> Tambah Paket Wisata
                    </a>
                </div>
            <?php else: ?>
                <!-- Package Grid -->
                <div class="package-grid" style="margin-top: 24px;">
                    <?php foreach ($packages as $package): ?>
                        <div class="package-card">
                            <div class="package-image">
                                <img src="<?php echo htmlspecialchars($package['image_url'] ?? '../img/camping.png'); ?>"
                                    alt="<?php echo htmlspecialchars($package['title']); ?>">
                                <div
                                    class="package-status <?php echo $package['status'] == 'active' ? 'status-active' : 'status-draft'; ?>">
                                    <?php echo $package['status'] == 'active' ? 'Aktif' : 'Draft'; ?>
                                </div>
                            </div>
                            <div class="package-content">
                                  <h3 class="package-title"><?php echo htmlspecialchars($package['title']); ?></h3>
                                  <div class="package-info">
                                    <div class="info-item">
                                        <i class="fas fa-users"></i>
                                        Kapasitas: <?php echo htmlspecialchars($package['max_participants']); ?> orang
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-tag"></i>
                                        <span>Rp <?php echo formatRupiah($package['price']); ?>/orang</span>
                                    </div>
                                </div>
                                <div class="package-actions">
                                    <a href="edit-paket.php?id=<?php echo $package['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="#" class="btn-delete" onclick="confirmDelete(<?php echo $package['id']; ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

        // Toggle profile dropdown
        document.getElementById('profileWrapper').addEventListener('click', function (event) {
            event.stopPropagation();
            document.getElementById('profileDropdown').classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const profileWrapper = document.getElementById('profileWrapper');
            const profileDropdown = document.getElementById('profileDropdown');

            if (!profileWrapper.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
        });
        
        // Function to confirm delete
        function confirmDelete(packageId) {
            if (confirm('Apakah Anda yakin ingin menghapus paket ini?')) {
                window.location.href = 'hapus-paket.php?id=' + packageId;
            }
        }
    </script>
</body>

</html>