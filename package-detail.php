<?php
// Display errors for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Determine login status
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];

// Include database connection
require_once 'includes/db.php';

// Get package ID from URL
$package_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Redirect if no valid package ID
if ($package_id === 0) {
    header("Location: packages.php");
    exit;
}

// Query to get package details with guide information
$query = "SELECT p.*, g.name as guide_name
          FROM packages p
          LEFT JOIN guides g ON p.guide_id = g.id
          WHERE p.id = ? AND p.status = 'active'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

// If package not found or not active, redirect
if ($result->num_rows === 0) {
    header("Location: packages.php");
    exit;
}

// Get package data
$package = $result->fetch_assoc();

// Format price to Rupiah format
function formatRupiah($angka)
{
    return number_format($angka, 0, ',', '.');
}

// Format rating to stars
function formatRating($rating)
{
    $stars = '';
    $fullStars = floor($rating);
    $halfStar = $rating - $fullStars >= 0.5;

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $stars .= '<i class="fas fa-star"></i>';
        } elseif ($halfStar && $i == $fullStars + 1) {
            $stars .= '<i class="fas fa-star-half-alt"></i>';
            $halfStar = false;
        } else {
            $stars .= '<i class="far fa-star"></i>';
        }
    }

    return $stars;
}

// Format duration
function formatDuration($days, $hours)
{
    if ($days > 0) {
        return "$days Hari";
    } else {
        return "$hours Jam";
    }
}

// Fix image path if needed
function fixImagePath($path)
{
    return str_replace('../', '', $path);
}

// Get package features (dynamic from database)
$featuresQuery = "SELECT feature_name, feature_icon FROM package_features WHERE package_id = ?";
$hasFeatures = false;

// Check if the package_features table exists
$tableCheckQuery = "SHOW TABLES LIKE 'package_features'";
$tableResult = $conn->query($tableCheckQuery);

if ($tableResult->num_rows > 0) {
    $featuresStmt = $conn->prepare($featuresQuery);
    $featuresStmt->bind_param("i", $package_id);
    $featuresStmt->execute();
    $featuresResult = $featuresStmt->get_result();

    if ($featuresResult->num_rows > 0) {
        $features = [];
        while ($feature = $featuresResult->fetch_assoc()) {
            $features[] = [
                'icon' => $feature['feature_icon'],
                'text' => $feature['feature_name']
            ];
        }
        $hasFeatures = true;
    }
}

// If no features found in database, use fallback
if (!$hasFeatures) {
    $features = [
        ['icon' => 'fa-ship', 'text' => 'Perahu Transportasi'],
        ['icon' => 'fa-fish', 'text' => 'Alat Snorkeling'],
        ['icon' => 'fa-home', 'text' => 'Penginapan'],
        ['icon' => 'fa-utensils', 'text' => '3 Kali Makan'],
        ['icon' => 'fa-user-tie', 'text' => 'Pemandu Wisata'],
        ['icon' => 'fa-shield-alt', 'text' => 'Asuransi']
    ];
}

// Get similar packages
$similarQuery = "SELECT * FROM packages 
                WHERE id != ? AND guide_id = ? AND status = 'active' 
                ORDER BY RAND() LIMIT 1";
$similarStmt = $conn->prepare($similarQuery);
$similarStmt->bind_param("ii", $package_id, $package['guide_id']);
$similarStmt->execute();
$similarResult = $similarStmt->get_result();

// If not enough similar packages by same guide, get other popular ones
if ($similarResult->num_rows < 3) {
    $popularQuery = "SELECT * FROM packages 
                    WHERE id != ? AND status = 'active' 
                    ORDER BY is_popular DESC, is_bestseller DESC, rating DESC 
                    LIMIT 3";
    $popularStmt = $conn->prepare($popularQuery);
    $popularStmt->bind_param("i", $package_id);
    $popularStmt->execute();
    $popularResult = $popularStmt->get_result();
}

// Set gallery images - use actual images if available
// For demo, we'll use a set of images
$galleryImages = [];
if (!empty($package['image_url'])) {
    $galleryImages[] = fixImagePath($package['image_url']);
}

// Add other gallery images
$defaultImages = ['img/snorkeling.png', 'img/camping.png', 'img/fotografi.png', 'img/luxury.png'];
foreach ($defaultImages as $img) {
    if (count($galleryImages) < 5) {
        $galleryImages[] = $img;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package['title']); ?> - PahaGo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/package-detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- navbar -->
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

    <!-- Package Detail Section -->
    <section class="package-detail">
        <div class="package-hero">
            <img src="<?php echo fixImagePath($package['image_url']); ?>"
                alt="<?php echo htmlspecialchars($package['title']); ?>" class="hero-image">

            <!-- Thumbnails -->
            <div class="container">
                <div class="thumbnails-container">
                    <div class="thumbnails">
                        <?php foreach ($galleryImages as $index => $img): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($package['title']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="package-content">
                <div class="package-info">
                    <h1 class="package-title"><?php echo htmlspecialchars($package['title']); ?></h1>

                    <div class="package-meta">
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Pulau Pahawang, Lampung</span>
                        </div>
                        <div class="meta-item">
                            <i class="far fa-clock"></i>
                            <span><?php echo formatDuration($package['duration_days'], $package['duration_hours']); ?></span>
                        </div>
                        <div class="meta-item">
                            <div class="package-rating">
                                <?php echo formatRating($package['rating']); ?>
                                <span>(<?php echo $package['rating']; ?>/5)</span>
                            </div>
                        </div>
                    </div>

                    <div class="package-tags">
                        <span class="tag">Pantai</span>
                        <span class="tag">Snorkeling</span>
                        <span class="tag">Petualangan</span>
                        <?php if ($package['is_bestseller']): ?>
                            <span class="tag bestseller">Terlaris</span>
                        <?php endif; ?>
                        <?php if ($package['is_popular']): ?>
                            <span class="tag popular">Popular</span>
                        <?php endif; ?>
                        <span class="tag status">Tersedia</span>
                    </div>

                    <div class="package-tabs">
                        <button class="tab-btn active" data-tab="deskripsi">Deskripsi</button>
                        <button class="tab-btn" data-tab="itinerary">Itinerary</button>
                        <button class="tab-btn" data-tab="ulasan">Ulasan</button>
                    </div>

                    <div class="tab-content active" id="deskripsi">
                        <h2>Deskripsi Paket</h2>
                        <div class="description">
                            <?php echo nl2br(htmlspecialchars($package['description'])); ?>
                        </div>

                        <h2>Apa Saja yang Termasuk?</h2>
                        <div class="features-grid">
                            <?php foreach ($features as $feature): ?>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas <?php echo $feature['icon']; ?>"></i>
                                    </div>
                                    <div class="feature-text"><?php echo $feature['text']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tab-content" id="itinerary">
                        <h2>Itinerary</h2>
                        <div class="itinerary-timeline">
                            <div class="timeline-item">
                                <div class="timeline-point"></div>
                                <div class="timeline-content">
                                    <h3>Hari 1</h3>
                                    <ul>
                                        <li>07.00 - Berkumpul di meeting point</li>
                                        <li>08.00 - Berangkat menuju Pulau Pahawang</li>
                                        <li>10.00 - Tiba di Pulau Pahawang</li>
                                        <li>11.00 - Snorkeling di spot pertama</li>
                                        <li>13.00 - Makan siang</li>
                                        <li>15.00 - Snorkeling di spot kedua</li>
                                        <li>17.00 - Check-in penginapan</li>
                                        <li>19.00 - Makan malam</li>
                                        <li>20.00 - Acara bebas</li>
                                    </ul>
                                </div>
                            </div>

                            <?php if ($package['duration_days'] >= 2): ?>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <h3>Hari 2</h3>
                                        <ul>
                                            <li>07.00 - Sarapan</li>
                                            <li>08.30 - Jelajah Pulau Pahawang</li>
                                            <li>12.00 - Makan siang</li>
                                            <li>13.30 - Snorkeling di spot ketiga</li>
                                            <li>15.30 - Persiapan kembali</li>
                                            <li>16.00 - Perjalanan pulang</li>
                                            <li>18.00 - Tiba di meeting point</li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($package['duration_days'] >= 3): ?>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <h3>Hari 3</h3>
                                        <ul>
                                            <li>07.00 - Sarapan</li>
                                            <li>08.30 - Eksplorasi hutan mangrove</li>
                                            <li>12.00 - Makan siang</li>
                                            <li>13.30 - Waktu bebas di pantai</li>
                                            <li>15.30 - Persiapan kembali</li>
                                            <li>16.00 - Perjalanan pulang</li>
                                            <li>18.00 - Tiba di meeting point</li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-content" id="ulasan">
                        <h2>Ulasan Pelanggan</h2>
                        <div class="reviews">
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer">
                                        <img src="https://ui-avatars.com/api/?name=John+Doe&background=1e6aff&color=fff&size=48"
                                            alt="John Doe" class="reviewer-img">
                                        <div class="reviewer-info">
                                            <h4>John Doe</h4>
                                            <div class="review-date">April 2025</div>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>Pengalaman yang luar biasa! Pemandangan bawah laut sangat indah dan pemandu
                                        wisata sangat ramah dan profesional.</p>
                                </div>
                            </div>

                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer">
                                        <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=1e6aff&color=fff&size=48"
                                            alt="Jane Smith" class="reviewer-img">
                                        <div class="reviewer-info">
                                            <h4>Jane Smith</h4>
                                            <div class="review-date">Maret 2025</div>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>Paket wisata yang sangat worth it! Makanan enak, penginapan nyaman, dan spot
                                        snorkeling yang indah.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="booking-sidebar">
                    <div class="booking-card">
                        <div class="price-section">
                            <h2 class="price">Rp <?php echo formatRupiah($package['price']); ?></h2>
                            <span class="price-unit">/orang</span>
                        </div>

                        <form action="booking.php" method="GET" class="booking-form">
                            <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">

                            <div class="form-group">
                                <label for="tanggal">Tanggal Keberangkatan</label>
                                <input type="date" id="tanggal" name="tanggal" required
                                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>

                            <div class="form-group">
                                <label for="jumlah">Jumlah Peserta</label>
                                <select id="jumlah" name="jumlah" required>
                                    <?php for ($i = 1; $i <= $package['max_participants']; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Orang</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="price-summary">
                                <div class="summary-item">
                                    <span>Subtotal</span>
                                    <span id="subtotal">Rp <?php echo formatRupiah($package['price']); ?></span>
                                </div>
                                <div class="summary-total">
                                    <span>Total</span>
                                    <span id="total">Rp <?php echo formatRupiah($package['price']); ?></span>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary full-width">Pesan Sekarang</button>
                        </form>

                        <div class="booking-info">
                            <div class="info-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Pembayaran aman & terenkripsi</span>
                            </div>
                        </div>
                    </div>

                    <div class="guide-card">
                        <h3>Pemandu Wisata</h3>
                        <div class="guide-info">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($package['guide_name'] ?? 'Tour Guide'); ?>&background=1e6aff&color=fff&size=64"
                                alt="<?php echo htmlspecialchars($package['guide_name'] ?? 'Tour Guide'); ?>"
                                class="guide-img">
                            <div>
                                <h4><?php echo htmlspecialchars($package['guide_name'] ?? 'Tour Guide'); ?></h4>
                                <p>Pemandu berpengalaman</p>
                            </div>
                        </div>
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
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Pahago. Seluruh hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const tabId = btn.getAttribute('data-tab');

                    // Remove active class from all buttons and contents
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));

                    // Add active class to clicked button and corresponding content
                    btn.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Thumbnail functionality
            const thumbnails = document.querySelectorAll('.thumbnail');
            const heroImage = document.querySelector('.hero-image');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', () => {
                    // Remove active class from all thumbnails
                    thumbnails.forEach(t => t.classList.remove('active'));

                    // Add active class to clicked thumbnail
                    thumb.classList.add('active');

                    // Update hero image
                    const thumbImg = thumb.querySelector('img');
                    heroImage.src = thumbImg.src;
                    heroImage.alt = thumbImg.alt;
                });
            });

            // Price calculation based on number of participants
            const jumlahSelect = document.getElementById('jumlah');
            const basePrice = <?php echo $package['price']; ?>;
            const subtotalElement = document.getElementById('subtotal');
            const totalElement = document.getElementById('total');

            jumlahSelect.addEventListener('change', () => {
                const jumlah = parseInt(jumlahSelect.value);
                const subtotal = basePrice * jumlah;

                subtotalElement.textContent = 'Rp ' + formatRupiah(subtotal);
                totalElement.textContent = 'Rp ' + formatRupiah(subtotal);
            });

            // Format number to Rupiah
            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            // Profile dropdown
            const profileTrigger = document.querySelector('.profile-trigger');
            const profileDropdown = document.querySelector('.profile-dropdown');

            if (profileTrigger) {
                profileTrigger.addEventListener('click', () => {
                    profileDropdown.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (profileDropdown && !profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                        profileDropdown.classList.remove('active');
                    }
                });
            }

            // Logout functionality
            const logoutLink = document.getElementById('logoutLink');

            if (logoutLink) {
                logoutLink.addEventListener('click', (e) => {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Logout',
                        text: 'Apakah Anda yakin ingin keluar?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Keluar',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#1e6aff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                });
            }

            // Mobile menu toggle
            const menuToggle = document.querySelector('.menu-toggle');
            const navMenu = document.querySelector('.nav-menu');

            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });
            }
        });
    </script>
</body>

</html>