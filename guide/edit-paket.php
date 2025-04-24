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
    // Get and validate data
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
    $max_participants = isset($_POST['max_participants']) ? (int)$_POST['max_participants'] : 0;
    $duration_days = isset($_POST['duration_days']) ? (int)$_POST['duration_days'] : 0;
    
    // Strict validation for status - only allow exactly 'active' or 'draft'
    if (isset($_POST['status']) && $_POST['status'] === 'active') {
        $status = 'active';
    } else if (isset($_POST['status']) && $_POST['status'] === 'draft') {
        $status = 'draft';
    } else {
        $error = "Status tidak valid. Nilai harus tepat 'active' atau 'draft'.";
        $status = '';
    }
    
if (empty($status)) {
        // Status validation failed
        // Error already set above
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
            // Title and description need mysqli_real_escape_string
            $title = mysqli_real_escape_string($conn, $title);
            $description = mysqli_real_escape_string($conn, $description);
            
            // Use a direct query instead of prepared statement for debugging
            $query = "UPDATE packages SET 
                    title = '$title', 
                    description = '$description', 
                    price = $price, 
                    max_participants = $max_participants, 
                    duration_days = $duration_days";
            
            // Only update image if we have a valid URL
            if (!empty($image_url)) {
                $image_url = mysqli_real_escape_string($conn, $image_url);
                $query .= ", image_url = '$image_url'";
            }
            
            $query .= ", status = '$status', updated_at = NOW() WHERE id = $package_id AND guide_id = $guide_id";
            
            if ($conn->query($query)) {
                $message = "Paket wisata berhasil diperbarui";
                // Refresh package data
                $result = $conn->query("SELECT * FROM packages WHERE id = $package_id AND guide_id = $guide_id");
                $package = $result->fetch_assoc();
            } else {
                $error = "Gagal memperbarui paket wisata: " . $conn->error;
            }
        }
    }
}

// Rest of your HTML code remains the same...
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
    <style>
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background-color: #f5f8ff;
        }
        
        .form-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #1e6aff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(30, 106, 255, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }
        
        .btn-cancel, .btn-submit {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-cancel {
            background-color: #f2f2f2;
            color: #666;
            border: none;
        }
        
        .btn-submit {
            background-color: #1e6aff;
            color: white;
            border: none;
        }
        
        .btn-cancel:hover {
            background-color: #e5e5e5;
        }
        
        .btn-submit:hover {
            background-color: #0052cc;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            overflow: hidden;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview-placeholder {
            color: #888;
            font-size: 14px;
            text-align: center;
        }
        
        .image-preview-placeholder i {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-cancel, .btn-submit {
                width: 100%;
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
                        <label for="description">Deskripsi Perjalanan</label>
                        <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Harga Per Orang (Rupiah)</label>
                        <input type="number" id="price" name="price" class="form-control" value="<?php echo $package['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_participants">Kapasitas Maksimum</label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" value="<?php echo $package['max_participants']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration_days">Durasi (Hari)</label>
                        <input type="number" id="duration_days" name="duration_days" class="form-control" value="<?php echo $package['duration_days']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="" disabled>-- Pilih Status --</option>
                            <option value="active" <?php echo $package['status'] == 'active' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="draft" <?php echo $package['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <a href="kelola-paket.php" class="btn-cancel">Batal</a>
                        <button type="submit" class="btn-submit">Simpan</button>
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