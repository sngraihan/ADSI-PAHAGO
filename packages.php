<?php
session_start();

// Tentukan variabel untuk status login
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "pahago");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set default filter dan pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Jumlah paket per halaman
$offset = ($page - 1) * $limit;

// Buat query dasar
$query = "SELECT * FROM packages WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM packages WHERE 1=1";

// Tambahkan filter pencarian jika ada
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
    $countQuery .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

// Tambahkan filter durasi jika dipilih
if ($duration != 'all') {
    switch ($duration) {
        case '1h':
            $query .= " AND duration_days = 0";
            $countQuery .= " AND duration_days = 0";
            break;
        case '2h1m':
            $query .= " AND duration_days = 2";
            $countQuery .= " AND duration_days = 2";
            break;
        case '3h2m':
            $query .= " AND duration_days = 3";
            $countQuery .= " AND duration_days = 3";
            break;
        case 'custom':
            $query .= " AND duration_days > 3";
            $countQuery .= " AND duration_days > 3";
            break;
    }
}

// Tambahkan limit dan offset untuk pagination
$query .= " ORDER BY is_popular DESC, is_bestseller DESC, rating DESC LIMIT $limit OFFSET $offset";

// Jalankan query
$result = $conn->query($query);
$countResult = $conn->query($countQuery);
$totalCount = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $limit);

// Format harga ke format Rupiah
function formatRupiah($angka) {
    return number_format($angka, 0, ',', '.');
}

// Format rating ke bintang
function formatRating($rating) {
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

// Format durasi
function formatDuration($days, $hours) {
    if ($days > 0) {
        return "$days Hari";
    } else {
        return "$hours Jam";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Wisata - PahaGo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/packages.css">
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

    <!-- Packages Section -->
    <section class="packages-page">
        <div class="container">
            <div class="packages-header">
                <h1>Temukan Paket Wisata Kami</h1>
                <p>Temukan pengalaman terbaik dengan berbagai pilihan paket wisata.</p>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <form action="packages.php" method="GET" id="searchForm">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" placeholder="Cari Paket Wisata..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?duration=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-tab <?php echo $duration == 'all' ? 'active' : ''; ?>">Semua Paket</a>
                <a href="?duration=1h<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-tab <?php echo $duration == '1h' ? 'active' : ''; ?>">1 Hari</a>
                <a href="?duration=2h1m<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-tab <?php echo $duration == '2h1m' ? 'active' : ''; ?>">2H1M</a>
                <a href="?duration=3h2m<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-tab <?php echo $duration == '3h2m' ? 'active' : ''; ?>">3H2M</a>
            </div>

            <!-- Packages Grid -->
            <div class="packages-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                <div class="package-card">
                    <div class="package-image">
                    <img src="<?php echo str_replace('../', '', $row['image_url']); ?>" alt="<?php echo $row['title']; ?>" class="card-img-top">
                    <?php if ($row['is_bestseller']): ?>
                            <div class="package-badge bestseller">Terlaris</div>
                        <?php endif; ?>
                        <?php if ($row['is_popular']): ?>
                            <div class="package-badge popular">Popular</div>
                        <?php endif; ?>
                    </div>
                    <div class="package-content">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="package-rating">
                            <div class="stars">
                                <?php echo formatRating($row['rating']); ?>
                            </div>
                            <span class="rating-text">(<?php echo $row['rating']; ?>/5)</span>
                        </div>
                        <div class="package-details">
                            <div class="detail">
                                <i class="far fa-clock"></i>
                                <?php echo formatDuration($row['duration_days'], $row['duration_hours']); ?>
                            </div>
                            <div class="detail">
                                <i class="fas fa-users"></i>
                                Maks <?php echo $row['max_participants']; ?> Orang
                            </div>
                        </div>
                        <p class="package-description"><?php echo htmlspecialchars($row['short_description']); ?></p>
                        <div class="package-footer">
                            <div class="price">
                                <span class="price-amount">Rp <?php echo formatRupiah($row['price']); ?></span>
                                <span class="price-unit">/orang</span>
                            </div>
                            <a href="package-detail.php?id=<?php echo $row['id']; ?>" class="btn-primary">Pesan</a>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="no-packages">Tidak ada paket wisata yang ditemukan.</div>';
                }
                ?>
            </div>

            <!-- Load More / Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-container">

                
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&duration=<?php echo $duration; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>
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