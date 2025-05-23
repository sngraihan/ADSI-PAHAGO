<?php
session_start();

// Tentukan variabel untuk status login
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PahaGo</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body>
    <!-- navbar -->
    <header class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="index.php" class="logo">PahaGo</a>
                <nav class="nav-menu">
                    <ul>
                        <li><a href="index.php" class="active">Beranda</a></li>
                        <li><a href="packages.php">Paket Wisata</a></li>
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

    <!-- hero -->
    <section class="hero" style="background-image: url('img/hero.png');">
        <div class="hero-content">
            <h1>Temukan Surga di Pulau Pahawang</h1>
            <p>
                Nikmati keindahan air laut sebening kristal, pantai yang masih alami,
                dan petualangan tak terlupakan.
            </p>
            <a href="packages.php" class="btn-primary">Jelajahi Sekarang →</a>
        </div>
    </section>

    <!-- content1 -->
    <section class="content1">
        <div class="container">
            <h2 class="section-title">Mengapa Memilih Pahago?</h2>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Destinasi Eksklusif</h3>
                    <p>Menawarkan pengalaman wisata terbaik</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Transportasi & Akomodasi</h3>
                    <p>Semua sudah termasuk dalam paket</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Pembayaran Aman</h3>
                    <p>Transaksi mudah & terpercaya</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Pemandu Berpengalaman</h3>
                    <p>Liburan tanpa khawatir</p>
                </div>
            </div>
        </div>
    </section>

    <!-- content2 -->
    <section class="packages">
        <div class="container">
            <h2 class="section-title">Rekomendasi Paket Wisata</h2>
            <div class="package-grid">
                <!-- Package 1 -->
                <div class="package-card">
                    <div class="package-image">
                        <img src="img/snorkeling.png" alt="Snorkeling Pahawang" />
                    </div>
                    <div class="package-content">
                        <h3>Paket Snorkeling Pahawang</h3>
                        <p class="duration">3 Hari 2 Malam</p>
                        <div class="price-section">
                            <span class="price-label">Mulai dari</span>
                            <span class="price">Rp1.500.000</span>
                        </div>
                        <button class="btn-primary full-width">Pesan Sekarang</button>
                    </div>
                </div>

                <!-- Package 2 -->
                <div class="package-card">
                    <div class="package-image">
                        <img src="img/camping.png" alt="Camping Pahawang" />
                    </div>
                    <div class="package-content">
                        <h3>Paket Camping Pahawang</h3>
                        <p class="duration">2 Hari 1 Malam</p>
                        <div class="price-section">
                            <span class="price-label">Mulai dari</span>
                            <span class="price">Rp950.000</span>
                        </div>
                        <button class="btn-primary full-width">Pesan Sekarang</button>
                    </div>
                </div>

                <!-- Package 3 -->
                <div class="package-card">
                    <div class="package-image">
                        <img src="img/luxury.png" alt="Luxury Pahawang" />
                    </div>
                    <div class="package-content">
                        <h3>Paket Luxury Pahawang</h3>
                        <p class="duration">4 Hari 3 Malam</p>
                        <div class="price-section">
                            <span class="price-label">Mulai dari</span>
                            <span class="price">Rp2.500.000</span>
                        </div>
                        <button class="btn-primary full-width">Pesan Sekarang</button>
                    </div>
                </div>

                <!-- Package 4 -->
                <div class="package-card">
                    <div class="package-image">
                        <img src="img/snorkeling.png" alt="Snorkeling Pahawang" />
                    </div>
                    <div class="package-content">
                        <h3>Paket Snorkeling Pahawang</h3>
                        <p class="duration">3 Hari 2 Malam</p>
                        <div class="price-section">
                            <span class="price-label">Mulai dari</span>
                            <span class="price">Rp1.500.000</span>
                        </div>
                        <button class="btn-primary full-width">Pesan Sekarang</button>
                    </div>
                </div>

                <!-- Package 5 -->
                <div class="package-card">
                    <div class="package-image">
                        <img src="img/camping.png" alt="Camping Pahawang" />
                    </div>
                    <div class="package-content">
                        <h3>Paket Camping Pahawang</h3>
                        <p class="duration">2 Hari 1 Malam</p>
                        <div class="price-section">
                            <span class="price-label">Mulai dari</span>
                            <span class="price">Rp950.000</span>
                        </div>
                        <button class="btn-primary full-width">Pesan Sekarang</button>
                    </div>
                </div>

                <!-- Package 6 -->
                <div class="package-card">
                    <div class="package-image">
                        <img src="img/luxury.png" alt="Luxury Pahawang" />
                    </div>
                    <div class="package-content">
                        <h3>Paket Luxury Pahawang</h3>
                        <p class="duration">4 Hari 3 Malam</p>
                        <div class="price-section">
                            <span class="price-label">Mulai dari</span>
                            <span class="price">Rp2.500.000</span>
                        </div>
                        <button class="btn-primary full-width">Pesan Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Tentang Pahago</h3>
                    <p>
                        Platform pemesanan paket wisata terpercaya untuk menjelajahi
                        keindahan Pahawang dan sekitarnya.
                    </p>
                </div>

                <div class="footer-section">
                    <h3>Navigasi Cepat</h3>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="packages.php">Paket Wisata</a></li>
                        <li><a href="status.php">Status Perjalanan</a></li>
                        <?php if (!$isLoggedIn): ?>
                            <li><a href="login.html">Masuk</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Hubungi Kami</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-phone"></i>+62 812 3456 7890</li>
                        <li><i class="fas fa-envelope"></i>info@pahago.com</li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Media Sosial</h3>
                    <div class="social-icons">
                        <a href=""><i class="fab fa-facebook"></i></a>
                        <a href=""><i class="fab fa-instagram"></i></a>
                        <a href=""><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Pahago. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/script.js"></script>
</body>

</html>