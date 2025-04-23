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

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect back to package list
    header("Location: kelola-paket.php");
    exit();
}

$package_id = (int)$_GET['id'];

// Fetch package details
$stmt = $conn->prepare("SELECT * FROM packages WHERE id = ? AND guide_id = ?");
$stmt->bind_param("ii", $package_id, $guide_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Package doesn't exist or doesn't belong to this guide
    $_SESSION['error_message'] = "Paket wisata tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.";
    header("Location: kelola-paket.php");
    exit();
}

$package = $result->fetch_assoc();
$stmt->close();

// Process form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = clean_input($conn, $_POST['title']);
    $description = clean_input($conn, $_POST['description']);
    $price = clean_input($conn, $_POST['price']);
    $max_participants = clean_input($conn, $_POST['max_participants']);
    $duration_days = clean_input($conn, $_POST['duration_days']);
    $status = clean_input($conn, $_POST['status']);
    
    // Validate input
    if (empty($title) || empty($description) || empty($price) || empty($max_participants) || empty($duration_days)) {
        $error = "Semua field harus diisi";
    } else {
        // Handle image upload if a new image is provided
        $image_url = $package['image_url']; // Keep existing image by default
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_dir = '../uploads/packages/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_url = $destination;
                } else {
                    $error = "Gagal mengupload gambar";
                }
            } else {
                $error = "Format gambar tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF";
            }
        }
        
        if (empty($error)) {
            // Update package in database
            $stmt = $conn->prepare("UPDATE packages SET title = ?, description = ?, price = ?, max_participants = ?, duration_days = ?, image_url = ?, status = ?, updated_at = NOW() WHERE id = ? AND guide_id = ?");
            $stmt->bind_param("ssiissiii", $title, $description, $price, $max_participants, $duration_days, $image_url, $status, $package_id, $guide_id);
            
            if ($stmt->execute()) {
                $message = "Paket wisata berhasil diperbarui";
                // Refresh package data
                $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ? AND guide_id = ?");
                $stmt->bind_param("ii", $package_id, $guide_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $package = $result->fetch_assoc();
            } else {
                $error = "Gagal memperbarui paket wisata: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paket Wisata - PahaGo Guide</title>
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
                    <h1>Edit Paket Wisata</h1>
                    <p>Perbarui informasi paket wisata Anda</p>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="form-card">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $package_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Judul Paket</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($package['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Gambar Paket</label>
                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($package['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>">
                            <?php else: ?>
                            <div class="image-preview-placeholder">
                                <i class="fas fa-image"></i> Pilih gambar untuk ditampilkan
                            </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <small>Biarkan kosong jika tidak ingin mengubah gambar</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Deskripsi Paket</label>
                        <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="price">Harga per Orang (Rp)</label>
                                <input type="number" id="price" name="price" class="form-control" value="<?php echo $package['price']; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="max_participants">Kapasitas Maksimum</label>
                                <input type="number" id="max_participants" name="max_participants" class="form-control" value="<?php echo $package['max_participants']; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="duration_days">Durasi (Hari)</label>
                                <input type="number" id="duration_days" name="duration_days" class="form-control" value="<?php echo $package['duration_days']; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active" <?php echo $package['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="draft" <?php echo $package['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="kelola-paket.php" class="btn-cancel">Batal</a>
                        <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(this.files[0]);
            } else {
                <?php if (!empty($package['image_url'])): ?>
                preview.innerHTML = '<img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>">';
                <?php else: ?>
                preview.innerHTML = '<div class="image-preview-placeholder"><i class="fas fa-image"></i> Pilih gambar untuk ditampilkan</div>';
                <?php endif; ?>
            }
        });
    </script>
</body>
</html>
