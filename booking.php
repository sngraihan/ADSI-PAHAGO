<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Koneksi ke database
require_once 'includes/db.php'; 

$isLoggedIn = isset($_SESSION['user_id']) ? true : false;
$user_id = $_SESSION['user_id'];

// Ambil data user
if ($isLoggedIn) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();
}

// Ambil package dari database
$package_id = isset($_GET['package_id']) ? intval($_GET['package_id']) : 1;
$stmt = $conn->prepare("SELECT p.*, g.id as guide_id, g.name as guide_name 
                       FROM packages p 
                       JOIN guides g ON p.guide_id = g.id 
                       WHERE p.id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();
$package_data = $result->fetch_assoc();
$stmt->close();

// Jika tidak ditemukan, set default
if ($package_data) {
    $package = [
        'id' => $package_data['id'],
        'name' => $package_data['title'],
        'duration_days' => $package_data['duration_days'],
        'duration_hours' => $package_data['duration_hours'],
        'duration' => ($package_data['duration_days'] > 0 ? "{$package_data['duration_days']} Hari" : "") . 
                     (($package_data['duration_days'] > 0 && $package_data['duration_hours'] > 0) ? ", " : "") .
                     ($package_data['duration_hours'] > 0 ? "{$package_data['duration_hours']} Jam" : ""),
        'price' => (int)$package_data['price'],
        'service_fee' => 50000,
        'image' => fixImagePath($package_data['image_url']),
        'max_participants' => $package_data['max_participants'],
        'guide_id' => $package_data['guide_id'],
        'guide_name' => $package_data['guide_name'],
        'location' => "Pulau Pahawang, Lampung" // Default location
    ];
} else {
    // fallback
    $package = [
        'id' => 1,
        'name' => 'Paket Tidak Ditemukan',
        'location' => 'Pulau Pahawang, Lampung',
        'duration' => '-',
        'price' => 0,
        'service_fee' => 0,
        'image' => 'img/placeholder.jpg',
        'guide_id' => 1
    ];
}

function fixImagePath($path) {
    if (empty($path)) return 'img/placeholder.jpg';
    
    // Jika path dimulai dengan ../
    if (strpos($path, '../') === 0) {
        return ltrim(str_replace('../', '', $path), '/');
    }
    
    // Jika path dimulai dengan ./
    if (strpos($path, './') === 0) {
        return ltrim(str_replace('./', '', $path), '/');
    }
    
    return $path;
}

// Proses form booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $nama = htmlspecialchars($_POST['nama_lengkap']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $telepon = htmlspecialchars($_POST['telepon']);
    $jumlah_peserta = intval($_POST['jumlah_peserta']);
    $tanggal = htmlspecialchars($_POST['tanggal']);
    $catatan = htmlspecialchars($_POST['catatan'] ?? '');

    if (empty($nama) || !$email || empty($telepon) || $jumlah_peserta < 1 || empty($tanggal)) {
        $error_message = "Semua field wajib diisi dengan benar.";
    } else {
        $travel_date = date('Y-m-d', strtotime($tanggal));
        $total_price = ($package['price'] * $jumlah_peserta) + $package['service_fee'];

        // Simpan ke tabel orders
        $stmt = $conn->prepare("INSERT INTO orders (user_id, package_id, guide_id, travel_date, participants, total_price, status, notes) 
                               VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("iiisids", $user_id, $package['id'], $package['guide_id'], $travel_date, $jumlah_peserta, $total_price, $catatan);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            $stmt->close();

            // Simpan detail ke session
            $_SESSION['booking'] = [
                'order_id' => $order_id,
                'package_id' => $package['id'],
                'package_name' => $package['name'],
                'guide_id' => $package['guide_id'],
                'guide_name' => $package['guide_name'],
                'nama' => $nama,
                'email' => $email,
                'telepon' => $telepon,
                'jumlah_peserta' => $jumlah_peserta,
                'tanggal' => $tanggal,
                'catatan' => $catatan,
                'total_price' => $total_price,
                'service_fee' => $package['service_fee']
            ];

            header("Location: payment.php");
            exit();
        } else {
            $error_message = "Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PahaGo - Pemesanan Paket Wisata</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/booking.css">
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

    <main class="container booking-container">
        <!-- Package Header -->
        <div class="package-header">
            <div class="package-image">
                <img src="<?= $package['image'] ?>" alt="<?= $package['name'] ?>">
            </div>
            <div class="package-info">
                <h1><?= $package['name'] ?></h1>
                <div class="package-meta">
                    <div class="location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= $package['location'] ?></span>
                    </div>
                    <div class="duration">
                        <i class="far fa-clock"></i>
                        <span><?= $package['duration'] ?></span>
                    </div>
                </div>
                <div class="package-price">
                    <span class="price">Rp <?= number_format($package['price'], 0, ',', '.') ?></span>
                    <span class="price-per">/orang</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="booking-content">
            <!-- Left Column (Form) -->
            <div class="booking-form-container">
                <div class="section-header">
                    <h2>Informasi Pribadi</h2>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <form id="booking-form" method="post" action="" class="booking-form">
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap Anda" 
                               value="<?= $user_data['full_name'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <input type="email" id="email" name="email" placeholder="Masukkan email Anda" 
                               value="<?= $user_data['email'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telepon">Nomor Telepon</label>
                        <input type="tel" id="telepon" name="telepon" placeholder="Masukkan nomor telepon Anda" 
                               value="<?= $user_data['phone'] ?? '' ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="jumlah_peserta">Jumlah Peserta</label>
                            <div class="select-container">
                                <select id="jumlah_peserta" name="jumlah_peserta" required>
                                    <option value="">Pilih Angka</option>
                                    <?php for ($i = 1; $i <= $package['max_participants']; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tanggal">Tanggal Keberangkatan</label>
                            <div class="date-input-container">
                                <input type="date" id="tanggal" name="tanggal" required>
                                <i class="far fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan Tambahan (Opsional)</label>
                        <textarea id="catatan" name="catatan" placeholder="Permintaan khusus atau kebutuhan lainnya?"></textarea>
                    </div>

                    <!-- Hidden input fields -->
                    <input type="hidden" name="guide_id" value="<?= $package['guide_id'] ?>">

                    <!-- FAQ Section -->
                    <div class="faq-section">
                        <h2>Pertanyaan yang Sering Diajukan</h2>
                        
                        <div class="accordion">
                            <div class="accordion-item">
                                <div class="accordion-header" id="faq1">
                                    <h3>Apa saja yang termasuk dalam paket?</h3>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="accordion-content">
                                    <ul>
                                        <li>Transportasi pergi & pulang</li>
                                        <li>Penginapan <?= $package['duration_days'] ?> malam (kamar twin sharing)</li>
                                        <li>Makan 3x (1x breakfast, 1x lunch, 1x dinner)</li>
                                        <li>Pemandu wisata lokal</li>
                                        <li>Alat snorkeling lengkap</li>
                                        <li>Tiket masuk wisata</li>
                                        <li>Dokumentasi foto</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <div class="accordion-header" id="faq2">
                                    <h3>Bagaimana cara mengubah tanggal pemesanan saya?</h3>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="accordion-content">
                                    <p>Untuk mengubah tanggal pemesanan, silakan hubungi customer service kami di nomor 
                                    082112345678 atau melalui email info@pahago.com minimal 7 hari sebelum tanggal keberangkatan.
                                    Perubahan tanggal tergantung ketersediaan dan mungkin dikenakan biaya tambahan.</p>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <div class="accordion-header" id="faq3">
                                    <h3>Apa kebijakan pembatalannya?</h3>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="accordion-content">
                                    <ul>
                                        <li>Pembatalan 14 hari atau lebih sebelum keberangkatan: refund 75%</li>
                                        <li>Pembatalan 7-13 hari sebelum keberangkatan: refund 50%</li>
                                        <li>Pembatalan 3-6 hari sebelum keberangkatan: refund 25%</li>
                                        <li>Pembatalan kurang dari 3 hari sebelum keberangkatan: tidak ada refund</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_booking" class="btn-payment-mobile">
                        Lanjutkan ke Pembayaran
                    </button>
                </form>
            </div>

            <!-- Right Column (Order Summary) -->
            <div class="order-summary">
                <div class="summary-container">
                    <h2>Ringkasan Pesanan</h2>
                    
                    <div class="summary-item">
                        <span>Harga Paket</span>
                        <span>Rp <?= number_format($package['price'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Peserta</span>
                        <span id="display_peserta">1 Orang</span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Biaya Layanan</span>
                        <span>Rp <?= number_format($package['service_fee'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span id="display_total">Rp <?= number_format($package['price'] + $package['service_fee'], 0, ',', '.') ?></span>
                    </div>
                    
                    <button type="submit" name="submit_booking" form="booking-form" class="btn-payment">
                        Lanjutkan ke Pembayaran
                    </button>
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
        // Toggle accordion
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                const item = header.parentElement;
                item.classList.toggle('active');
            });
        });
        
        // Update order summary based on number of participants
        document.getElementById('jumlah_peserta').addEventListener('change', function() {
            const peserta = parseInt(this.value) || 1;
            const hargaPaket = <?= $package['price'] ?>;
            const biayaLayanan = <?= $package['service_fee'] ?>;
            const total = (hargaPaket * peserta) + biayaLayanan;
            
            document.getElementById('display_peserta').textContent = peserta + ' Orang';
            document.getElementById('display_total').textContent = 'Rp ' + formatNumber(total);
        });
        
        // Format number with thousand separator
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
        
        // Set minimum date for booking (tomorrow)
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('tanggal').min = tomorrow.toISOString().split('T')[0];
        
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