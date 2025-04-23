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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($guide_name); ?>&background=1e6aff&color=fff" alt="Profile" class="profile-image">
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($guide_name); ?></div>
                    <div class="profile-role">Pemandu Wisata</div>
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
            <div class="package-grid">
                <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <div class="package-image">
                        <img src="<?php echo htmlspecialchars($package['image_url'] ?? '../img/snorkeling.png'); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>">
                        <div class="package-status <?php echo $package['status'] == 'active' ? 'status-active' : 'status-draft'; ?>">
                            <?php echo $package['status'] == 'active' ? 'Aktif' : 'Draft'; ?>
                        </div>
                    </div>
                    <div class="package-content">
                        <h3 class="package-title"><?php echo htmlspecialchars($package['title']); ?></h3>
                        <div class="package-info">
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Kapasitas: <?php echo $package['max_participants']; ?> orang</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <span>Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>/orang</span>
                            </div>
                        </div>
                        <div class="package-actions">
                            <a href="edit-paket.php?id=<?php echo $package['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="hapus-paket.php?id=<?php echo $package['id']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus paket ini?')">
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
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
    </script>
</body>
</html>
