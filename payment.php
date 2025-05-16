<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Redirect ke booking jika tidak ada data booking
if (!isset($_SESSION['booking'])) {
    header("Location: packages.php");
    exit();
}

// Koneksi ke database
require_once 'includes/db.php';

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$booking = $_SESSION['booking'];


// Proses konfirmasi pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $payment_id = uniqid('PAY-');
    $pemesanan_id = $booking['pemesanan_id'];
    $amount = $booking['total_price'];
    $status = 'pending'; // Default status pending sampai admin verifikasi
    
    // Upload bukti pembayaran jika ada
    $proof_image = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $target_dir = "uploads/payments/";
        
        // Buat direktori jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $filename = $payment_id . '.' . $extension;
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
            $proof_image = $target_file;
        }
    }
    
    $note = isset($_POST['payment_note']) ? htmlspecialchars($_POST['payment_note']) : '';
    
    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO payments (payment_id, pemesanan_id, amount, status, proof_image, note) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $payment_id, $pemesanan_id, $amount, $status, $proof_image, $note);
    
    if ($stmt->execute()) {
        // Update status pemesanan
        $update_stmt = $conn->prepare("UPDATE pemesanan SET status = 'waiting_confirmation' WHERE id = ?");
        $update_stmt->bind_param("i", $pemesanan_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Hapus data booking dari session
        unset($_SESSION['booking']);
        
        // Redirect ke halaman sukses
        header("Location: payment_success.php?payment_id=" . $payment_id);
        exit();
    } else {
        $error_message = "Gagal menyimpan pembayaran. Silakan coba lagi.";
    }
    
    $stmt->close();
}

// Hitung waktu pembayaran (2 jam dari sekarang)
$deadline = time() + 2 * 60 * 60; // 2 jam dalam detik
$hours = floor(($deadline - time()) / 3600);
$minutes = floor((($deadline - time()) % 3600) / 60);
$seconds = ($deadline - time()) % 60;
$countdown = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PahaGo - Pembayaran Paket Wisata</title>
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
                        <li><a href="packages.php" class="active">Paket Wisata</a></li>
                        <li><a href="status.php">Status Perjalanan</a></li>
                        <?php if ($isLoggedIn): ?>
                            <li class="profile-dropdown">
                                <div class="profile-trigger">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=1e6aff&color=fff&size=32"
                                        class="profile-image" alt="Profile">
                                </div>
                                <div class="dropdown-menu">
                                    <div class="dropdown-header">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=1e6aff&color=fff&size=64"
                                            class="dropdown-profile-image" alt="Profile">
                                        <div class="dropdown-profile-info">
                                            <span class="dropdown-profile-name"><?php echo $_SESSION['user_name']; ?></span>
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
        <h1>Pembayaran Paket Wisata</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>
        
        <div class="payment-content">
            <!-- Left Column (Payment Info) -->
            <div class="payment-info">
                <div class="payment-summary">
                    <h2>Nama Paket</h2>
                    <p><?= $booking['package_name'] ?></p>
                    
                    <h2>Tanggal Keberangkatan</h2>
                    <p><?= date('d F Y', strtotime($booking['tanggal'])) ?></p>
                    
                    <h2>Jumlah Peserta</h2>
                    <p><?= $booking['jumlah_peserta'] ?> Orang</p>
                    
                    <h2>Total Pembayaran</h2>
                    <p class="price">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></p>
                </div>
                
                <div class="payment-instructions">
                    <div class="instruction-alert">
                        <i class="fas fa-info-circle"></i>
                        <p>Silakan lakukan transfer ke rekening berikut dalam waktu yang ditentukan.</p>
                    </div>
                    
                    <div class="bank-info">
                        <div class="bank-logo">
                            <img src="img/banks/<?= strtolower(str_replace(' ', '-', $payment_method['bank_name'])) ?>.png" alt="<?= $payment_method['bank_name'] ?>" onerror="this.src='img/banks/default-bank.png';">
                        </div>
                        <div class="bank-details">
                            <h3><?= $payment_method['bank_name'] ?></h3>
                            <div class="account-number">
                                <span class="number"><?= $payment_method['account_number'] ?></span>
                                <button class="copy-btn" onclick="copyToClipboard('<?= $payment_method['account_number'] ?>')">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                            <p class="account-name"><?= $payment_method['account_name'] ?></p>
                        </div>
                    </div>
                    
                    <div class="payment-amount">
                        <h3>Jumlah Transfer</h3>
                        <div class="amount-box">
                            <span>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></span>
                            <button class="copy-btn" onclick="copyToClipboard('<?= $booking['total_price'] ?>')">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="countdown-timer">
                        <h3>Selesaikan pembayaran dalam:</h3>
                        <div class="timer" id="countdown"><?= $countdown ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column (Payment Confirmation) -->
            <div class="payment-confirmation">
                <div class="confirmation-container">
                    <h2>Konfirmasi Pembayaran</h2>
                    
                    <form method="post" action="" class="confirmation-form" enctype="multipart/form-data">
                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <p>Upload bukti transfer disini</p>
                            <div class="file-input-container">
                                <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required>
                                <label for="payment_proof" class="file-label">Pilih File</label>
                            </div>
                            <div id="file-name-display" class="file-name"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_note">Keterangan Tambahan (Opsional)</label>
                            <textarea id="payment_note" name="payment_note" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>
                        
                        <button type="submit" name="confirm_payment" class="btn-confirm">
                            Konfirmasi Pembayaran
                        </button>
                    </form>
                    
                    <div class="help-section">
                        <h3>Butuh Bantuan?</h3>
                        <p>Jika Anda mengalami kesulitan dalam melakukan pembayaran, silakan hubungi customer service kami:</p>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fab fa-whatsapp"></i>
                                <span>082112345678</span>
                            </div>
                            <div class="contact-item">
                                <i class="far fa-envelope"></i>
                                <span>cs@pahago.com</span>
                            </div>
                        </div>
                    </div>
                </div>
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
        // File input display
        document.getElementById('payment_proof').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : '';
            document.getElementById('file-name-display').textContent = fileName;
            
            if (fileName) {
                document.querySelector('.upload-area').classList.add('has-file');
            } else {
                document.querySelector('.upload-area').classList.remove('has-file');
            }
        });
        
        // Copy to clipboard function
        function copyToClipboard(text) {
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            // Show copied notification
            const notification = document.createElement('div');
            notification.className = 'copy-notification';
            notification.textContent = 'Tersalin!';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 1500);
            }, 10);
        }
        
        // Countdown timer
        const countdownElement = document.getElementById('countdown');
        let timeLeft = <?= $deadline - time() ?>;
        
        function updateCountdown() {
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;
            
            countdownElement.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                countdownElement.textContent = '00:00:00';
                alert('Waktu pembayaran habis. Silakan lakukan pemesanan ulang.');
                window.location.href = 'packages.php';
            }
            
            timeLeft--;
        }
        
        const countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown();
        
        // Toggle profile dropdown
        const profileTrigger = document.querySelector('.profile-trigger');
        if (profileTrigger) {
            profileTrigger.addEventListener('click', function() {
                const dropdown = this.closest('.profile-dropdown');
                dropdown.classList.toggle('active');
            });
        }
        
        // Logout functionality
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Send AJAX request to logout script
            fetch('logout.php', {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = 'login.html';
                }
            });
        });
    </script>
</body>
</html>