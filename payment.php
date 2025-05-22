<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Redirect jika tidak ada data booking dari halaman sebelumnya
if (!isset($_SESSION['booking'])) {
    header("Location: packages.php");
    exit();
}

// Koneksi ke database
require_once 'includes/db.php';

$isLoggedIn = isset($_SESSION['user_id']) ? true : false;
$user_id = $_SESSION['user_id'];
$booking = $_SESSION['booking'];

// Ambil data user
if ($isLoggedIn) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();
}

// Jika tombol konfirmasi pembayaran ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $notes = isset($_POST['payment_notes']) ? htmlspecialchars($_POST['payment_notes']) : '';
    $order_id = $_SESSION['booking']['order_id'];
    
    // Upload bukti pembayaran
    $upload_success = false;
    $proof_image = '';
    
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['payment_proof']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/payments/' . $new_filename;
            
            // Buat direktori jika belum ada
            if (!file_exists('uploads/payments/')) {
                mkdir('uploads/payments/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_path)) {
                $proof_image = $upload_path;
                $upload_success = true;
            }
        }
    }
    
    // Jika upload berhasil atau tidak ada file yang diupload (bisa diproses nanti)
    if ($upload_success || !isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] == 4) {
        // Simpan data pembayaran
        $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, proof_image, status) 
                                        VALUES (?, ?, ?, ?, 'pending')");
        $payment_amount = $booking['total_price'];
        $payment_method = 'bank_transfer';
        $payment_stmt->bind_param("idss", $order_id, $payment_amount, $payment_method, $proof_image);
        
        if ($payment_stmt->execute()) {
            // Update order notes jika ada
            if (!empty($notes)) {
                $note_stmt = $conn->prepare("UPDATE orders SET notes = CONCAT(notes, '\n', ?) WHERE id = ?");
                $note_stmt->bind_param("si", $notes, $order_id);
                $note_stmt->execute();
                $note_stmt->close();
            }
            
            // Hapus session booking karena sudah tidak diperlukan
            unset($_SESSION['booking']);
            
            // Redirect ke halaman sukses
            header("Location: payment_success.php?order_id=" . $order_id);
            exit();
        } else {
            $error_message = "Terjadi kesalahan saat menyimpan pembayaran. Silakan coba lagi.";
        }
        $payment_stmt->close();
    } else {
        $error_message = "Terjadi kesalahan saat mengupload bukti pembayaran. Silakan coba lagi.";
    }
}

// Set timer (1 jam)
$expiry_time = time() + 60 * 60;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PahaGo - Pembayaran</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="logo">PahaGo</a>
                <nav class="nav-menu">
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="packages.php">Paket Wisata</a></li>
                        <li><a href="status.php">Status Perjalanan</a></li>
                        <?php if ($isLoggedIn): ?>
                            <li class="profile-dropdown">
                                <div class="profile-trigger">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'User'); ?>&background=1e6aff&color=fff&size=32"
                                        class="profile-image" alt="Profile">
                                </div>
                                <div class="dropdown-menu">
                                    <div class="dropdown-header">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'User'); ?>&background=1e6aff&color=fff&size=64"
                                            class="dropdown-profile-image" alt="Profile">
                                        <div class="dropdown-profile-info">
                                            <span class="dropdown-profile-name"><?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item" id="logoutLink">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li><a href="login.html">Masuk</a></li>
                            <li><a href="register.html" class="btn-primary">Daftar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <button class="menu-toggle"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </header>

    <main class="container payment-container">
        <h1 class="payment-title">Pembayaran Paket Wisata</h1>
        
        <div class="payment-content">
            <!-- Informasi Booking -->
            <div class="booking-summary">
                <div class="summary-item">
                    <span class="summary-label">Nama Paket</span>
                    <span class="summary-value"><?php echo $booking['package_name']; ?></span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Tanggal Keberangkatan</span>
                    <span class="summary-value"><?php echo date('d F Y', strtotime($booking['tanggal'])); ?></span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Jumlah Peserta</span>
                    <span class="summary-value"><?php echo $booking['jumlah_peserta']; ?> Orang</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Total Pembayaran</span>
                    <span class="summary-value price">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <!-- Informasi Pembayaran -->
            <div class="payment-info">
                <h2>Informasi Pembayaran</h2>
                
                <div class="payment-alert">
                    <p>Silakan lakukan transfer ke rekening berikut dalam waktu yang ditentukan.</p>
                </div>
                
                <div class="payment-method">
                    <div class="bank-info">
                        <img src="img/bca.png" alt="Bank BCA" class="bank-logo">
                        <span class="bank-name">Bank BCA</span>
                    </div>

                    <div class="account-details">
                        <div class="account-item">
                            <span class="account-label">Nomor Rekening</span>
                            <div class="account-value-container">
                                <span class="account-value">1234 5678 9012</span>
                                <button class="copy-btn" data-clipboard-text="1234567890123">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="account-item">
                            <span class="account-label">Atas Nama</span>
                            <span class="account-value">Pahago Travel</span>
                        </div>
                        
                        <div class="account-item">
                            <span class="account-label">Jumlah Transfer</span>
                            <span class="account-value price">Rp <?php echo number_format($booking['total_price'] - 50000, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-timer">
                    <span>Selesaikan pembayaran dalam:</span>
                    <div class="timer" id="countdown">59:59</div>
                </div>
            </div>
            
            <!-- Form Konfirmasi -->
            <div class="payment-confirmation">
                <h2>Konfirmasi Pembayaran</h2>
                
                <form method="post" action="" enctype="multipart/form-data" class="confirmation-form">
                    <div class="upload-container">
                        <label for="payment_proof" class="upload-label">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <span class="upload-text">Upload bukti transfer disini</span>
                            <span class="upload-hint">Klik untuk memilih file</span>
                        </label>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/jpeg,image/png,application/pdf" class="upload-input">
                        <div id="file-preview" class="file-preview"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_notes">Keterangan Tambahan (Opsional)</label>
                        <textarea id="payment_notes" name="payment_notes" rows="4" placeholder="Masukkan catatan tambahan jika diperlukan"></textarea>
                    </div>
                    
                    <button type="submit" name="confirm_payment" class="btn-confirm">Konfirmasi Pembayaran</button>
                </form>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-copyright">
                    <p>&copy; 2025 PahaGo. All rights reserved.</p>
                </div>
                <div class="footer-links">
                    <a href="#">Syarat & Ketentuan</a>
                    <a href="#">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Timer Countdown
        const countdownElement = document.getElementById('countdown');
        let timeLeft = <?php echo $expiry_time - time(); ?>; // Waktu dalam detik
        
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                countdownElement.textContent = '00:00';
                alert('Waktu pembayaran habis. Silakan pesan ulang.');
                window.location.href = 'packages.php';
            }
            
            timeLeft--;
        }
        
        updateCountdown();
        const timerInterval = setInterval(updateCountdown, 1000);
        
        // File Upload Preview
        const fileInput = document.getElementById('payment_proof');
        const filePreview = document.getElementById('file-preview');
        
        fileInput.addEventListener('change', function() {
            filePreview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileType = file.type;
                
                if (fileType.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.classList.add('preview-image');
                    img.file = file;
                    filePreview.appendChild(img);
                    
                    const reader = new FileReader();
                    reader.onload = (function(aImg) {
                        return function(e) {
                            aImg.src = e.target.result;
                        };
                    })(img);
                    
                    reader.readAsDataURL(file);
                } else {
                    const fileInfo = document.createElement('div');
                    fileInfo.classList.add('file-info');
                    fileInfo.innerHTML = `<i class="far fa-file-pdf"></i> <span>${file.name}</span>`;
                    filePreview.appendChild(fileInfo);
                }
                
                // Tambahkan tombol hapus
                const removeBtn = document.createElement('button');
                removeBtn.classList.add('remove-file');
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    filePreview.innerHTML = '';
                    fileInput.value = '';
                });
                filePreview.appendChild(removeBtn);
            }
        });
        
        // Copy to clipboard functionality
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const text = this.getAttribute('data-clipboard-text');
                navigator.clipboard.writeText(text).then(function() {
                    const originalIcon = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => {
                        btn.innerHTML = originalIcon;
                    }, 2000);
                });
            });
        });

        // Toggle profile dropdown
        const profileTrigger = document.querySelector('.profile-trigger');
        if (profileTrigger) {
            profileTrigger.addEventListener('click', function() {
                const dropdown = this.closest('.profile-dropdown');
                dropdown.classList.toggle('active');
            });
        }
        
        // Handle logout
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('logout.php')
                .then(response => {
                    window.location.href = 'login.html';
                })
                .catch(error => {
                    console.error('Logout failed:', error);
                });
        });
    </script>
</body>
</html>