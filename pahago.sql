-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 24, 2025 at 05:52 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pahago`
--

-- --------------------------------------------------------

--
-- Table structure for table `guides`
--

CREATE TABLE `guides` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guides`
--

INSERT INTO `guides` (`id`, `name`, `email`, `password`, `phone`, `profile_image`, `bio`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Budi Santoso', 'budi@pahago.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', NULL, NULL, 'active', '2025-04-23 16:54:52', NULL),
(2, 'Ahmad Sulaiman', 'ahmad@pahago.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', NULL, NULL, 'active', '2025-04-23 16:54:52', NULL),
(3, 'Dede Putra', 'dede@pahago.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567892', NULL, NULL, 'active', '2025-04-23 16:54:52', NULL),
(4, 'Siti Rahayu', 'siti@pahago.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567893', NULL, NULL, 'active', '2025-04-23 16:54:52', NULL),
(5, 'Rudi Hermawan', 'rudi@pahago.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567894', NULL, NULL, 'active', '2025-04-23 16:54:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int NOT NULL,
  `guide_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_participants` int NOT NULL,
  `duration_days` int NOT NULL,
  `duration_hours` int DEFAULT '0',
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','draft') NOT NULL DEFAULT 'draft',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `is_popular` tinyint(1) DEFAULT '0',
  `rating` decimal(3,1) DEFAULT '0.0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `guide_id`, `title`, `description`, `short_description`, `price`, `max_participants`, `duration_days`, `duration_hours`, `image_url`, `status`, `is_bestseller`, `is_popular`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 'Liburan Akhir Pekan 2D1N', 'Nikmati liburan akhir pekan di Pulau Pahawang dengan paket 2 hari 1 malam. Termasuk transportasi, penginapan, dan makan 3x.', 'Paket liburan akhir pekan di Pulau Pahawang', 1250000.00, 8, 2, 2, '../img/akhirpekan.png', 'active', 1, 1, 4.8, '2025-04-23 16:55:11', '2025-04-24 17:42:48'),
(3, 1, 'Petualangan Snorkeling', 'Jelajahi keindahan bawah laut Pahawang dengan paket snorkeling seharian. Termasuk peralatan snorkeling dan makan siang.', 'Paket snorkeling di spot terbaik Pahawang', 850000.00, 10, 1, 6, '../img/snorkeling2.png', 'active', 0, 1, 4.5, '2025-04-23 16:55:11', '2025-04-24 17:42:24'),
(5, 2, 'Paket Luxury Pahawang', 'Nikmati pengalaman mewah di Pahawang dengan akomodasi villa pribadi, makanan premium, dan layanan eksklusif.', 'Pengalaman mewah di Pulau Pahawang', 2500000.00, 6, 3, 0, '../img/luxury.png', 'active', 0, 0, 4.9, '2025-04-23 16:55:11', NULL),
(7, 2, 'Paket Snorkeling Pahawang 3H2M', 'Pengalaman lengkap menjelajahi pulau dengan menyelam, memancing, dan aktivitas budaya.', 'Pengalaman lengkap menjelajahi pulau dengan menyelam, memancing, dan aktivitas budaya.', 2150000.00, 6, 3, 0, '../img/snorkeling.png', 'active', 1, 0, 4.3, '2025-04-23 16:55:11', NULL),
(9, 3, 'Berkemah di Bawah Bintang-bintang', 'Berkemah di pantai Pahawang dengan pemandangan langit berbintang. Termasuk peralatan camping dan BBQ malam.', 'Pengalaman camping di pantai Pahawang', 750000.00, 8, 2, 0, '../img/camping.png', 'active', 0, 1, 4.6, '2025-04-23 16:55:11', NULL),
(11, 3, 'Tur Fotografi', 'Tur khusus untuk penggemar fotografi dengan kunjungan ke spot-spot instagramable di Pahawang.', 'Tur fotografi di lokasi terbaik Pahawang', 1100000.00, 6, 2, 0, '../img/fotografi.png', 'draft', 0, 0, 4.2, '2025-04-23 16:55:11', NULL),
(13, 1, 'Sehari Menjelajah Pahawang', 'Nikmati snorkeling, island hopping, dan aktivitas pantai dalam perjalanan sehari penuh ini.', 'Nikmati snorkeling, island hopping, dan aktivitas pantai dalam perjalanan sehari penuh ini.', 550000.00, 10, 0, 8, '../img/sehari-menjelajah.png', 'active', 1, 0, 4.5, '2025-04-24 06:40:19', NULL),
(15, 2, 'Eksplorasi Hutan Bakau', 'Eksplorasi ekosistem mangrove Pahawang menggunakan kano. Panduan ekowisata dan camilan lokal disediakan.', 'Tur edukatif menyusuri hutan mangrove', 500000.00, 8, 0, 8, '../img/mangrove.png', 'active', 0, 8, 4.4, '2025-04-24 06:40:19', NULL),
(17, 2, 'Pengalaman Menyelam', 'Paket diving untuk pemula dan profesional. Termasuk pelatihan singkat dan penyewaan alat selam.', 'Paket diving bawah laut Pahawang', 1350000.00, 6, 0, 4, '../img/diving.png', 'active', 1, 1, 4.9, '2025-04-24 06:40:19', '2025-04-24 17:48:03'),
(19, 3, 'Paket Petualangan Pulau Pahawang', 'Nikmati snorkeling, island hopping, dan aktivitas pantai dalam perjalanan sehari penuh ini.', 'Nikmati snorkeling, island hopping, dan aktivitas pantai dalam perjalanan sehari penuh ini.', 1250000.00, 15, 3, 0, '../img/petualangan.png', 'active', 0, 0, 4.8, '2025-04-24 06:40:19', NULL),
(21, 3, 'Sewa Kapal Pribadi', 'Sewa kapal pribadi untuk tur fleksibel sesuai keinginan Anda. Cocok untuk grup kecil atau pasangan.', 'Kapal pribadi dengan itinerary bebas', 2000000.00, 4, 0, 12, '../img/boat.png', 'active', 1, 0, 4.8, '2025-04-24 06:40:19', NULL),
(23, 1, 'Menginap di Pinggir Laut Pahawang', 'Liburan akhir pekan yang sempurna dengan snorkeling, berkemah, dan barbekyu di bawah bintang.', 'Liburan akhir pekan yang sempurna dengan snorkeling, berkemah, dan barbekyu di bawah bintang.', 1250000.00, 8, 2, 0, '../uploads/packages/680a763f722ee.png', 'draft', 0, 1, 5.0, '2025-04-24 06:40:19', '2025-04-24 17:40:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`) VALUES
(11, 'Raihan Andi Saungnaga', 'raihan.sng123@gmail.com', '081383341841', '$2y$10$A9WbdRx32wDvm2hVuBf7deqa.kdhNQldr69biT/N6.v.ITpF62rY6'),
(13, 'Raihan Andi Saungnaga', 'raihan.sng1234@gmail.com', '081383341841', '$2y$10$.8nzlLpeGU3Uxx2k5PtCw.FIPdm2A7wZ8.qyCVyVEY29j3G5aHXhi'),
(17, 'koron salim', 'koronasalim@gmail.com', '081383341841', '$2y$10$lP.eF2eOjevRTUD6suZ3deGueKH.r8Bm6mAgGVqXxSOEJecoTASYa');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guides`
--
ALTER TABLE `guides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guide_id` (`guide_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guides`
--
ALTER TABLE `guides`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
