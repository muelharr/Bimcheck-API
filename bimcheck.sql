-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 28, 2026 at 01:43 AM
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
-- Database: `bimcheck`
--

-- --------------------------------------------------------

--
-- Table structure for table `antrian`
--

CREATE TABLE `antrian` (
  `id_antrian` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_dosen` int NOT NULL,
  `nomor_antrian` int NOT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_panggil` datetime DEFAULT NULL,
  `waktu_kehadiran` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL COMMENT 'Waktu selesai bimbingan',
  `topik` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `catatan_dosen` text COMMENT 'Catatan/hasil bimbingan dari dosen',
  `file_dokumen` varchar(255) DEFAULT NULL,
  `waktu_booking` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('menunggu','dipanggil','proses','selesai','dilewati','dibatalkan') DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `antrian`
--

INSERT INTO `antrian` (`id_antrian`, `id_mahasiswa`, `id_dosen`, `nomor_antrian`, `tanggal`, `waktu_mulai`, `waktu_panggil`, `waktu_kehadiran`, `waktu_selesai`, `topik`, `deskripsi`, `catatan_dosen`, `file_dokumen`, `waktu_booking`, `status`) VALUES
(23, 4, 5, 1, '2026-01-27', '23:35:00', NULL, '2026-01-27 23:35:38', '2026-01-27 23:35:44', 'Bimbingan web ', '', NULL, NULL, '2026-01-27 23:35:30', 'selesai'),
(25, 4, 5, 1, '2026-01-28', '00:45:00', '2026-01-27 17:47:25', '2026-01-27 17:47:33', '2026-01-27 17:47:40', 'Bimbingan laporan ', '', NULL, NULL, '2026-01-28 00:45:35', 'selesai'),
(27, 4, 5, 2, '2026-01-28', '00:53:00', '2026-01-28 01:16:20', '2026-01-27 18:16:31', '2026-01-28 01:16:44', 'Bimbingan laporan ', '', NULL, NULL, '2026-01-28 00:53:08', 'selesai'),
(28, 4, 5, 3, '2026-01-28', '01:13:00', '2026-01-28 01:16:23', '2026-01-27 18:16:38', '2026-01-28 01:16:45', 'Bimbingan laporan ', '', NULL, NULL, '2026-01-28 01:13:17', 'selesai'),
(29, 4, 5, 4, '2026-01-28', '01:16:00', '2026-01-28 01:17:34', '2026-01-27 18:21:49', '2026-01-28 01:21:55', 'Bimbingan laporan ', '', NULL, NULL, '2026-01-28 01:16:57', 'selesai'),
(30, 4, 5, 5, '2026-01-28', '01:22:00', '2026-01-28 01:29:00', '2026-01-27 18:29:18', '2026-01-28 01:29:24', 'Bimbingan laporan ', '', NULL, NULL, '2026-01-28 01:22:02', 'selesai');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id_dosen` int NOT NULL,
  `kode_dosen` varchar(20) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL COMMENT 'Email dosen untuk login dan notifikasi',
  `no_telepon` varchar(20) DEFAULT NULL COMMENT 'Nomor telepon dosen',
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `keahlian` varchar(100) DEFAULT NULL,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif' COMMENT 'Status keaktifan dosen',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id_dosen`, `kode_dosen`, `nama_dosen`, `email`, `no_telepon`, `password`, `foto_profil`, `keahlian`, `status_aktif`, `created_at`) VALUES
(5, 'MYH', ' M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC.', '', NULL, '$2y$10$ONzSBcyNkwVBNIzonOP0Y.qCxIoGeYx72HFCoF6GWW/YkMMV696tK', NULL, 'Teknik Informatika', 'aktif', '2026-01-22 13:08:35');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL COMMENT 'Nomor telepon mahasiswa',
  `prodi` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id_mahasiswa`, `npm`, `nama`, `email`, `no_telepon`, `prodi`, `password`, `foto_profil`, `created_at`) VALUES
(4, '714240020', 'Samuel Harry Priatno Lumbantoruan', '714240020@std.ulbi.ac.id', NULL, 'Teknik Informatika', '$2y$10$dtrWQhFi.yS0TBQRZc0BT.Gb.Zs19CtPLGEKnCcndkbcu9N7g5l2S', NULL, '2026-01-22 13:06:07');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int NOT NULL,
  `user_type` enum('mahasiswa','dosen') NOT NULL COMMENT 'Tipe user penerima notifikasi',
  `user_id` int NOT NULL COMMENT 'ID mahasiswa atau dosen',
  `id_antrian` int DEFAULT NULL COMMENT 'Relasi ke antrian (jika ada)',
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `waktu_kirim` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Sistem notifikasi untuk mahasiswa dan dosen';

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `user_type`, `user_id`, `id_antrian`, `judul`, `pesan`, `is_read`, `waktu_kirim`) VALUES
(2, 'mahasiswa', 4, 23, '✅ Bimbingan Dimulai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah dimulai.', 1, '2026-01-27 23:35:38'),
(11, 'dosen', 5, 25, '📋 Booking Bimbingan Baru', 'Mahasiswa Samuel Harry Priatno Lumbantoruan telah booking bimbingan dengan topik: Bimbingan laporan ', 1, '2026-01-28 00:45:35'),
(12, 'mahasiswa', 4, 25, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 1, '2026-01-28 00:45:43'),
(13, 'mahasiswa', 4, 25, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 1, '2026-01-28 00:47:25'),
(14, 'mahasiswa', 4, 25, '✅ Bimbingan Dimulai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah dimulai.', 1, '2026-01-28 00:47:33'),
(15, 'mahasiswa', 4, 25, '🎉 Bimbingan Selesai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah selesai.', 1, '2026-01-28 00:47:40'),
(17, 'dosen', 5, 27, '📋 Booking Bimbingan Baru', 'Mahasiswa Samuel Harry Priatno Lumbantoruan telah booking bimbingan dengan topik: Bimbingan laporan ', 1, '2026-01-28 00:53:08'),
(18, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:55'),
(19, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:56'),
(20, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:57'),
(21, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:58'),
(22, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:58'),
(23, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:59'),
(24, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:59'),
(25, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:12:59'),
(26, 'dosen', 5, 28, '📋 Booking Bimbingan Baru', 'Mahasiswa Samuel Harry Priatno Lumbantoruan telah booking bimbingan dengan topik: Bimbingan laporan ', 1, '2026-01-28 01:13:17'),
(27, 'mahasiswa', 4, 28, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:13:25'),
(28, 'mahasiswa', 4, 27, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:16:20'),
(29, 'mahasiswa', 4, 28, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:16:23'),
(30, 'mahasiswa', 4, 27, '✅ Bimbingan Dimulai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah dimulai.', 0, '2026-01-28 01:16:31'),
(31, 'mahasiswa', 4, 28, '✅ Bimbingan Dimulai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah dimulai.', 0, '2026-01-28 01:16:38'),
(32, 'mahasiswa', 4, 27, '🎉 Bimbingan Selesai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah selesai.', 0, '2026-01-28 01:16:44'),
(33, 'mahasiswa', 4, 28, '🎉 Bimbingan Selesai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah selesai.', 0, '2026-01-28 01:16:45'),
(34, 'dosen', 5, 29, '📋 Booking Bimbingan Baru', 'Mahasiswa Samuel Harry Priatno Lumbantoruan telah booking bimbingan dengan topik: Bimbingan laporan ', 1, '2026-01-28 01:16:57'),
(35, 'mahasiswa', 4, 29, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:17:35'),
(36, 'mahasiswa', 4, 29, '✅ Bimbingan Dimulai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah dimulai.', 0, '2026-01-28 01:21:49'),
(37, 'mahasiswa', 4, 29, '🎉 Bimbingan Selesai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah selesai.', 0, '2026-01-28 01:21:55'),
(38, 'dosen', 5, 30, '📋 Booking Bimbingan Baru', 'Mahasiswa Samuel Harry Priatno Lumbantoruan telah booking bimbingan dengan topik: Bimbingan laporan ', 1, '2026-01-28 01:22:02'),
(39, 'mahasiswa', 4, 30, '🔔 Anda Dipanggil!', 'Silakan scan QR code dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. untuk memulai bimbingan.', 0, '2026-01-28 01:29:00'),
(40, 'mahasiswa', 4, 30, '✅ Bimbingan Dimulai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah dimulai.', 0, '2026-01-28 01:29:18'),
(41, 'mahasiswa', 4, 30, '🎉 Bimbingan Selesai', 'Bimbingan Anda dengan dosen  M. Yusril Helmi Setyawan, S.Kom., M.Kom.,SFPC. telah selesai.', 0, '2026-01-28 01:29:24');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_sistem`
--

CREATE TABLE `pengaturan_sistem` (
  `id_pengaturan` int NOT NULL,
  `nama_pengaturan` varchar(100) NOT NULL,
  `nilai` text NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Pengaturan konfigurasi sistem';

--
-- Dumping data for table `pengaturan_sistem`
--

INSERT INTO `pengaturan_sistem` (`id_pengaturan`, `nama_pengaturan`, `nilai`, `deskripsi`, `updated_at`) VALUES
(1, 'timeout_qr_scan', '60', 'Batas waktu scan QR setelah dipanggil (dalam menit)', '2026-01-27 14:18:57'),
(2, 'durasi_qr_token', '5', 'Durasi validitas token QR (dalam menit)', '2026-01-27 14:18:57'),
(3, 'max_antrian_per_dosen', '20', 'Maksimal antrian per dosen per hari', '2026-01-27 14:18:57'),
(4, 'waktu_toleransi_keterlambatan', '10', 'Toleransi keterlambatan mahasiswa (dalam menit)', '2026-01-27 14:18:57');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_status`
--

CREATE TABLE `riwayat_status` (
  `id_riwayat` int NOT NULL,
  `id_antrian` int NOT NULL,
  `status_lama` enum('menunggu','dipanggil','proses','selesai','revisi','dilewati','dibatalkan') DEFAULT NULL,
  `status_baru` enum('menunggu','dipanggil','proses','selesai','revisi','dilewati','dibatalkan') NOT NULL,
  `waktu_perubahan` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` varchar(255) DEFAULT NULL COMMENT 'Keterangan perubahan status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tracking history perubahan status antrian';

--
-- Dumping data for table `riwayat_status`
--

INSERT INTO `riwayat_status` (`id_riwayat`, `id_antrian`, `status_lama`, `status_baru`, `waktu_perubahan`, `keterangan`) VALUES
(4, 23, 'proses', 'selesai', '2026-01-27 23:35:44', 'Status diubah oleh dosen'),
(12, 25, 'menunggu', 'dipanggil', '2026-01-28 00:45:43', 'Status diubah oleh dosen'),
(13, 25, 'dilewati', 'dipanggil', '2026-01-28 00:47:25', 'Status diubah oleh dosen'),
(14, 25, 'proses', 'selesai', '2026-01-28 00:47:40', 'Status diubah oleh dosen'),
(15, 27, 'menunggu', 'dipanggil', '2026-01-28 01:12:55', 'Status diubah oleh dosen'),
(16, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:56', 'Status diubah oleh dosen'),
(17, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:57', 'Status diubah oleh dosen'),
(18, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:58', 'Status diubah oleh dosen'),
(19, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:58', 'Status diubah oleh dosen'),
(20, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:59', 'Status diubah oleh dosen'),
(21, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:59', 'Status diubah oleh dosen'),
(22, 27, 'dilewati', 'dipanggil', '2026-01-28 01:12:59', 'Status diubah oleh dosen'),
(23, 28, 'menunggu', 'dipanggil', '2026-01-28 01:13:25', 'Status diubah oleh dosen'),
(24, 27, 'dilewati', 'dipanggil', '2026-01-28 01:16:20', 'Status diubah oleh dosen'),
(25, 28, 'dilewati', 'dipanggil', '2026-01-28 01:16:23', 'Status diubah oleh dosen'),
(26, 27, 'proses', 'selesai', '2026-01-28 01:16:44', 'Status diubah oleh dosen'),
(27, 28, 'proses', 'selesai', '2026-01-28 01:16:45', 'Status diubah oleh dosen'),
(28, 29, 'menunggu', 'dipanggil', '2026-01-28 01:17:34', 'Status diubah oleh dosen'),
(29, 29, 'proses', 'selesai', '2026-01-28 01:21:55', 'Status diubah oleh dosen'),
(30, 30, 'menunggu', 'dipanggil', '2026-01-28 01:29:00', 'Status diubah oleh dosen'),
(31, 30, 'proses', 'selesai', '2026-01-28 01:29:24', 'Status diubah oleh dosen');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL COMMENT 'Waktu login terakhir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `last_login`) VALUES
(1, 'admin', '$2y$10$44Rme0qv30vnjETvQ9RDre4/Zw5lHQ27/HekY5jtFbWAWRrXppoYe', 'admin', '2026-01-27 23:34:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id_antrian`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal_status` (`tanggal`,`status`),
  ADD KEY `idx_id_dosen_tanggal` (`id_dosen`,`tanggal`),
  ADD KEY `idx_id_mahasiswa_tanggal` (`id_mahasiswa`,`tanggal`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD UNIQUE KEY `kode_dosen` (`kode_dosen`),
  ADD UNIQUE KEY `unique_email_dosen` (`email`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `npm` (`npm`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_antrian` (`id_antrian`),
  ADD KEY `idx_user_type_id` (`user_type`,`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_waktu_kirim` (`waktu_kirim`);

--
-- Indexes for table `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  ADD PRIMARY KEY (`id_pengaturan`),
  ADD UNIQUE KEY `unique_nama_pengaturan` (`nama_pengaturan`);

--
-- Indexes for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `idx_id_antrian` (`id_antrian`),
  ADD KEY `idx_waktu_perubahan` (`waktu_perubahan`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id_antrian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id_dosen` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `pengaturan_sistem`
--
ALTER TABLE `pengaturan_sistem`
  MODIFY `id_pengaturan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  MODIFY `id_riwayat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `antrian_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD CONSTRAINT `riwayat_status_ibfk_1` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
