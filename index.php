<?php
require_once 'config.php';

// Get featured packages
$sql = "SELECT * FROM packages ORDER BY rating DESC LIMIT 6";
$result = $conn->query($sql);
$featuredPackages = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featuredPackages[] = $row;
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-md-7 hero-content">
                <h1 class="display-4 fw-bold mb-3">Temukan Surga di Pulau Pahawang</h1>
                <p class="lead mb-4">Nikmati keindahan air laut sebening kristal, pantai yang masih alami, dan petualangan tak terlupakan.</p>
                <a href="packages.php" class="btn btn-primary btn-lg rounded-pill">
                    Jelajahi Sekarang <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <h2 class="text-center fw-bold mb-5">Mengapa Memilih Pahago?</h2>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon">
                    <i class="fas fa-star text-primary"></i>
                </div>
                <h3 class="h5 fw-semibold mb-2">Destinasi Eksklusif</h3>
                <p class="text-muted">Menawarkan pengalaman wisata terbaik</p>
            </div>

            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon">
                    <i class="fas fa-car text-primary"></i>
                </div>
                <h3 class="h5 fw-semibold mb-2">Transportasi & Akomodasi</h3>
                <p class="text-muted">Semua sudah termasuk dalam paket</p>
            </div>

            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon">
                    <i class="fas fa-check-circle text-primary"></i>
                </div>
                <h3 class="h5 fw-semibold mb-2">Pembayaran Aman</h3>
                <p class="text-muted">Transaksi mudah & terpercaya</p>
            </div>

            <div class="col-md-6 col-lg-3 text-center">
                <div class="feature-icon">
                    <i class="fas fa-users text-primary"></i>
                </div>
                <h3 class="h5 fw-semibold mb-2">Pemandu Berpengalaman</h3>
                <p class="text-muted">Liburan tanpa khawatir</p>
            </div>
        </div>
    </div>
</section>

<!-- Recommended Packages Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <h2 class="text-center fw-bold mb-5">Rekomendasi Paket Wisata</h2>

        <div class="row g-4">
            <?php foreach ($featuredPackages as $package): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <img src="<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['title']); ?>">
                    <div class="card-body">
                        <h3 class="card-title h5 fw-bold"><?php echo htmlspecialchars($package['title']); ?></h3>
                        <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($package['duration']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-primary small">Mulai dari</span>
                            <span class="fw-bold">Rp<?php echo number_format($package['price'], 0, ',', '.'); ?></span>
                        </div>
                        <a href="book.php?id=<?php echo $package['id']; ?>" class="btn btn-primary w-100">Pesan Sekarang</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
