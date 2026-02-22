-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 22, 2026 at 01:13 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_workshop_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE `form_fields` (
  `id` int NOT NULL,
  `workshop_id` int NOT NULL,
  `label` varchar(255) NOT NULL,
  `field_type` enum('text','email','tel','select','radio','textarea') NOT NULL,
  `options` text,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `placeholder` varchar(255) DEFAULT NULL,
  `urutan` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `form_fields`
--

INSERT INTO `form_fields` (`id`, `workshop_id`, `label`, `field_type`, `options`, `is_required`, `placeholder`, `urutan`) VALUES
(1, 1, 'Jenis Pelatihan', 'email', '', 1, '<---- Piih Jawaban ---->', 0),
(2, 1, 'No Telepon', 'text', '', 1, '', 0),
(3, 1, 'hi', 'text', '', 1, '1', 0),
(4, 3, 'dd', 'text', '', 1, 'd', 0),
(5, 6, 'Jenis Pelatihan', 'select', 'AI, ML, FF', 1, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int NOT NULL,
  `workshop_id` int NOT NULL,
  `santri_id` int DEFAULT NULL,
  `nama_peserta` varchar(255) NOT NULL,
  `jenis_kelamin` varchar(20) DEFAULT NULL,
  `email_peserta` varchar(255) NOT NULL,
  `telepon_peserta` varchar(20) DEFAULT NULL,
  `kode_unik` varchar(100) NOT NULL,
  `qr_source` varchar(255) DEFAULT NULL,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `status_pembayaran` enum('pending','paid','failed','free') NOT NULL DEFAULT 'pending',
  `status_kehadiran` enum('absen','hadir') NOT NULL DEFAULT 'absen',
  `sertifikat_status` enum('belum_dikirim','terkirim') NOT NULL DEFAULT 'belum_dikirim',
  `sertifikat_nomor` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_url` text,
  `payment_expiry` datetime DEFAULT NULL,
  `check_in_at` datetime DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `status_denda` enum('aman','kena_denda','lunas') DEFAULT 'aman',
  `didaftarkan_oleh` enum('admin','user','mandiri') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `workshop_id`, `santri_id`, `nama_peserta`, `jenis_kelamin`, `email_peserta`, `telepon_peserta`, `kode_unik`, `qr_source`, `last_accessed_at`, `status_pembayaran`, `status_kehadiran`, `sertifikat_status`, `sertifikat_nomor`, `created_at`, `payment_url`, `payment_expiry`, `check_in_at`, `check_out_at`, `status_denda`, `didaftarkan_oleh`) VALUES
(1, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-4BBD981A', NULL, '2025-10-18 03:20:38', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 03:20:38', NULL, NULL, NULL, NULL, 'aman', 'user'),
(2, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-9EF8E759', NULL, '2025-10-18 03:28:21', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 03:28:21', NULL, NULL, NULL, NULL, 'aman', 'user'),
(3, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-0095B747', NULL, '2025-10-18 03:47:57', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 03:47:57', NULL, NULL, NULL, NULL, 'aman', 'user'),
(5, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-331026B4', NULL, '2025-10-18 04:02:55', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:02:55', NULL, NULL, NULL, NULL, 'aman', 'user'),
(6, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-775CF9EA', NULL, '2025-10-18 04:04:30', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:04:30', NULL, NULL, NULL, NULL, 'aman', 'user'),
(7, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-F36E1B7F', NULL, '2025-10-18 04:11:32', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:11:32', NULL, NULL, NULL, NULL, 'aman', 'user'),
(8, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-31BA8A61', NULL, '2025-10-18 04:11:59', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:11:59', NULL, NULL, NULL, NULL, 'aman', 'user'),
(9, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-DA7E0374', NULL, '2025-10-18 04:18:39', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:18:39', NULL, NULL, NULL, NULL, 'aman', 'user'),
(10, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-A80ED925', NULL, '2025-10-18 04:27:09', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:27:09', NULL, NULL, NULL, NULL, 'aman', 'user'),
(12, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-28A7D648', NULL, '2025-10-18 04:42:28', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:42:28', NULL, NULL, NULL, NULL, 'aman', 'user'),
(13, 1, NULL, 'Muhammad Akbar Firdaus', '', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-5F290815', NULL, '2025-10-18 04:42:46', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 04:42:46', NULL, NULL, NULL, NULL, 'aman', 'user'),
(14, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-3D155F68', NULL, '2025-10-18 05:10:49', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:10:49', NULL, NULL, NULL, NULL, 'aman', 'user'),
(15, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '85894317397', 'WS-1-30E67F05', NULL, '2025-10-18 05:11:22', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:11:22', NULL, NULL, NULL, NULL, 'aman', 'user'),
(16, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-762C387C', NULL, '2025-10-18 05:37:20', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:37:20', NULL, NULL, NULL, NULL, 'aman', 'user'),
(17, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-B8AC0981', NULL, '2025-10-18 05:37:46', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:37:46', NULL, NULL, NULL, NULL, 'aman', 'user'),
(18, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '6285894317397', 'WS-1-A409C447', NULL, '2025-10-18 05:42:00', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:42:00', NULL, NULL, NULL, NULL, 'aman', 'user'),
(19, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-DE1B4923', NULL, '2025-10-18 05:43:24', 'pending', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:43:24', 'https://passport.duitku.com/topup/topupdirectv2.aspx?ref=SQ252M61P4C5W5E8FI1', '2025-10-18 13:43:26', NULL, NULL, 'aman', 'user'),
(20, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-41EC0389', NULL, '2025-10-18 05:45:25', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 05:45:25', NULL, NULL, NULL, NULL, 'aman', 'user'),
(21, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-6247D9E7', NULL, '2025-10-18 08:15:43', 'free', 'hadir', 'terkirim', '001/SRT/TABLIGH/LDK/STL/IX/2025', '2025-10-18 08:15:43', NULL, NULL, NULL, NULL, 'aman', 'user'),
(22, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-DF0DA969', NULL, '2025-10-18 11:52:53', 'free', 'absen', 'belum_dikirim', NULL, '2025-10-18 11:52:52', NULL, NULL, NULL, NULL, 'aman', 'user'),
(23, 1, NULL, 'Muhammad Akbar', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-7C13F0B8', NULL, '2025-11-24 11:44:33', 'free', 'absen', 'belum_dikirim', NULL, '2025-11-24 11:44:33', NULL, NULL, NULL, NULL, 'aman', 'user'),
(24, 1, NULL, 'Muhammad Akbar', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-75818D72', NULL, '2025-11-24 11:50:43', 'free', 'absen', 'belum_dikirim', NULL, '2025-11-24 11:50:43', NULL, NULL, NULL, NULL, 'aman', 'user'),
(25, 1, NULL, 'Muhammad Akbar', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-4F47501E', NULL, '2025-11-24 11:51:48', 'free', 'absen', 'belum_dikirim', NULL, '2025-11-24 11:51:48', NULL, NULL, NULL, NULL, 'aman', 'user'),
(26, 1, NULL, 'Muhammad Akbar', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-E8AEEFC1', NULL, '2025-11-24 11:54:45', 'free', 'absen', 'belum_dikirim', NULL, '2025-11-24 11:54:45', NULL, NULL, NULL, NULL, 'aman', 'user'),
(27, 1, NULL, 'Muhammad Akbar', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-A46D09DF', NULL, '2025-11-24 11:56:01', 'free', 'absen', 'belum_dikirim', NULL, '2025-11-24 11:56:01', NULL, NULL, NULL, NULL, 'aman', 'user'),
(28, 1, NULL, 'Muhammad Akbar', 'Laki-laki', 'akbarfirdaus009@gmail.com', '085894317397', 'WS-1-6EF50657', NULL, '2025-11-24 12:15:45', 'free', 'absen', 'belum_dikirim', NULL, '2025-11-24 12:15:45', NULL, NULL, NULL, NULL, 'aman', 'user'),
(29, 3, NULL, 'Payat', 'Laki-laki', 'akbarfirdaus09@gmail.com', '083890101434', 'WS-3-4783ABD8', NULL, '2025-12-15 10:26:33', 'free', 'absen', 'belum_dikirim', NULL, '2025-12-15 10:26:33', NULL, NULL, NULL, NULL, 'aman', 'user'),
(30, 3, NULL, 'Faiz', 'Laki-laki', 'faiz@gmail.com', '0888626262', 'WS-3-DB5B35CF', NULL, '2025-12-19 12:35:14', 'free', 'absen', 'belum_dikirim', NULL, '2025-12-19 12:35:14', NULL, NULL, NULL, NULL, 'aman', 'user'),
(31, 3, NULL, 'Payat', 'Perempuan', 'akbarfirdaus09@gmail.com', '083890101434', 'WS-3-58A2020A', NULL, '2025-12-19 12:36:20', 'free', 'hadir', 'terkirim', '001/SRT/BEM/2025', '2025-12-19 12:36:20', NULL, NULL, NULL, NULL, 'aman', 'user'),
(32, 3, NULL, 'Muhammad Akbar Firdaus', 'Perempuan', 'akbarfirdaus09@gmail.com', '083890101434', 'WS-3-251672A8', NULL, '2025-12-20 19:20:29', 'free', 'absen', 'belum_dikirim', NULL, '2025-12-20 19:20:28', NULL, NULL, NULL, NULL, 'aman', 'user'),
(33, 4, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus0009@gmail.com', '083890101434', 'WS-4-754CCA', NULL, NULL, 'free', 'absen', 'belum_dikirim', NULL, '2025-12-22 11:58:19', NULL, NULL, NULL, NULL, 'aman', 'user'),
(34, 4, NULL, 'Akbar', 'Laki-laki', 'akbarfirdaus09@gmail.com', '083890101434', 'WS-4-F968B3', NULL, NULL, 'free', 'absen', 'belum_dikirim', NULL, '2025-12-22 11:58:38', NULL, NULL, NULL, NULL, 'aman', 'user'),
(35, 4, NULL, 'Dede', 'Laki-laki', 'dede09@gmail.com', '083890101434', 'WS-4-1039C4', NULL, NULL, 'pending', 'absen', 'belum_dikirim', NULL, '2025-12-22 12:02:07', NULL, NULL, NULL, NULL, 'aman', 'user'),
(40, 4, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirda9@gmail.com', '083890101434', 'WS-4-977B7C', NULL, NULL, 'pending', 'absen', 'belum_dikirim', NULL, '2025-12-22 12:29:19', 'https://passport.duitku.com/topup/topupdirectv2.aspx?ref=SQ25OCA0X0M4P84YPX6', NULL, NULL, NULL, 'aman', 'user'),
(41, 4, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdau09@gmail.com', '083890101434', 'WS-4-ABB28F', NULL, NULL, 'pending', 'absen', 'belum_dikirim', NULL, '2025-12-28 03:50:58', 'https://passport.duitku.com/topup/topupdirectv2.aspx?ref=SQ25O0V76OO8IA75T6N', NULL, NULL, NULL, 'aman', 'user'),
(42, 3, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus09@gmail.com', '083890101434', 'WS-3-60E461', NULL, NULL, 'free', 'absen', 'belum_dikirim', NULL, '2026-01-11 19:26:06', NULL, NULL, NULL, NULL, 'aman', 'user'),
(43, 1, NULL, 'Muhammad Akbar Firdaus', 'Laki-laki', 'akbarfirdaus09@gmail.com', '083890101434', 'WS-1-57578C', NULL, NULL, 'free', 'absen', 'belum_dikirim', NULL, '2026-01-11 19:39:15', NULL, NULL, NULL, NULL, 'aman', 'user'),
(44, 6, 3, 'Siti Aminah', 'Perempuan', 'akbarfirdaus009@gmail.com', '081234567892', 'WS-6-17EB49', NULL, NULL, 'free', 'absen', 'belum_dikirim', NULL, '2026-01-21 19:27:28', NULL, NULL, NULL, NULL, 'aman', 'mandiri'),
(45, 6, 1, 'Ahmad Zaki Al-Fatih', 'Laki-laki', '2025001@santri.ponpes', '6285894317397', 'WS-6-BD0C46', NULL, NULL, 'free', 'hadir', 'belum_dikirim', NULL, '2026-01-21 19:50:23', NULL, NULL, '2026-01-21 20:02:48', '2026-01-21 20:09:42', 'aman', 'admin'),
(46, 6, 2, 'Muhammad Rizky Pratama', 'Laki-laki', '2025002@santri.ponpes', '081234567891', 'WS-6-A89333', NULL, NULL, 'free', 'hadir', 'belum_dikirim', NULL, '2026-01-21 20:10:54', NULL, NULL, '2026-01-21 20:12:06', NULL, 'aman', 'admin'),
(47, 6, 2, 'Ibunda', 'Laki-laki', 'akbarfirdaus009@gmail.com', '081234567891', 'WS-6-300004', NULL, NULL, 'free', 'absen', 'belum_dikirim', NULL, '2026-01-21 20:15:54', NULL, NULL, NULL, NULL, 'aman', 'mandiri'),
(48, 3, 1, 'Ahmad Zaki Al-Fatih', 'Laki-laki', '2025001@santri.ponpes', '6285894317397', 'WS-3-BAA630', NULL, NULL, 'free', 'hadir', 'terkirim', '014/SRT/BEM/2025', '2026-01-21 20:19:35', NULL, NULL, '2026-01-26 09:48:42', NULL, 'aman', 'admin'),
(49, 3, 2, 'Muhammad Rizky Pratama', 'Laki-laki', '2025002@santri.ponpes', '081234567891', 'WS-3-3D4F33', NULL, NULL, 'free', 'hadir', 'terkirim', '013//SRT/BEM/2025', '2026-01-21 20:19:58', NULL, NULL, '2026-01-21 20:32:50', NULL, 'aman', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran_data`
--

CREATE TABLE `pendaftaran_data` (
  `id` int NOT NULL,
  `pendaftaran_id` int NOT NULL,
  `field_id` int NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendaftaran_data`
--

INSERT INTO `pendaftaran_data` (`id`, `pendaftaran_id`, `field_id`, `value`) VALUES
(1, 21, 1, 'IoT'),
(2, 22, 1, 'IoT'),
(3, 23, 1, 'Workshop'),
(4, 23, 2, '088242'),
(5, 23, 3, 'akbarfirdaus009@gmail.com'),
(6, 24, 1, 'Workshop'),
(7, 24, 2, '088242'),
(8, 24, 3, 'hii'),
(9, 25, 1, 'Workshop'),
(10, 25, 2, '088242'),
(11, 25, 3, 'akbarfirdaus009@gmail.com'),
(12, 26, 1, 'Workshop'),
(13, 26, 2, '088242'),
(14, 26, 3, 'akbarfirdaus009@gmail.com'),
(15, 27, 1, 'Workshop'),
(16, 27, 2, '088242'),
(17, 27, 3, 'akbarfirdaus009@gmail.com'),
(18, 28, 1, 'Workshop'),
(19, 28, 2, '088242'),
(20, 28, 3, 'akbarfirdaus009@gmail.com'),
(21, 44, 5, 'AI'),
(22, 47, 5, 'AI');

-- --------------------------------------------------------

--
-- Table structure for table `santri`
--

CREATE TABLE `santri` (
  `id` int NOT NULL,
  `nis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `nama_lengkap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Laki-laki',
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_wali` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_wali` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_santri` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `santri`
--

INSERT INTO `santri` (`id`, `nis`, `password`, `otp_code`, `otp_expires_at`, `nama_lengkap`, `email`, `jenis_kelamin`, `kelas`, `nama_wali`, `no_hp_wali`, `barcode_code`, `foto_santri`, `created_at`) VALUES
(1, '2025001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-01-26 10:02:58', 'Ahmad Zaki Al-Fatih', 'ahmadzaki@gmail.com', 'Laki-laki', '11 SMA', 'Bpk. Herman', '', 'STR-2025-001', NULL, '2026-01-21 18:51:57'),
(2, '2025002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Muhammad Rizky Pratama', NULL, 'Laki-laki', '11 SMA', 'Bpk. Supriyadi', '081234567891', 'STR-2025-002', NULL, '2026-01-21 18:51:57'),
(3, '2025003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Siti Aminah', NULL, 'Laki-laki', '10 SMA', 'Ibu Nurhasanah', '081234567892', 'STR-2025-003', NULL, '2026-01-21 18:51:57'),
(4, '2025004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Fajar Sidik', NULL, 'Laki-laki', '12 SMA', 'Bpk. Rohmat', '081234567893', 'STR-2025-004', NULL, '2026-01-21 18:51:57'),
(5, '2025005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Dewi Sartika', 'dewisartika@gmail.com', 'Perempuan', '11 SMA', 'Bpk. Kurniawan', '081234567894', 'STR-2025-005', NULL, '2026-01-21 18:51:57'),
(6, '2210010021', '$2y$10$QpitwmvpgNRlnJKEqSBMjum9gq0XjxVSPwUU3K4bn56dZix/Xhgru', NULL, NULL, 'Intan Safitri', 'intansafitri@gmail.com', 'Perempuan', 'IX IPA', 'Udin', '03853059539', 'STR-2026-5E80', NULL, '2026-01-26 09:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `role` enum('penyelenggara','peserta') NOT NULL,
  `owner_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `no_whatsapp`, `jenis_kelamin`, `foto_profil`, `password`, `otp_code`, `otp_expires_at`, `role`, `owner_id`, `created_at`) VALUES
(1, 'Muhammad Akbar Firdaus', 'akbarfirdaus009@gmail.com', '085894317397', 'Laki-laki', '69693f2e52d23.png', '$2y$10$6OaovVy5eYZllq4nB/BWw.B/4DM94wi4o014mJIQQ6m9v62PsIrUm', '961644', '2026-01-27 07:22:03', 'penyelenggara', NULL, '2025-09-16 16:59:41'),
(4, 'Agus Heriawan', 'agusheriawan.mahasiswa@gmail.com', NULL, 'Laki-laki', NULL, '$2y$10$6SOauA3/unZqtu8HIHTRJ.VE6alorXX3t2wdi/746cNTHa3V7KMaO', NULL, NULL, 'penyelenggara', 1, '2025-09-19 18:10:53'),
(5, 'Pahrudin', 'pahrufahlevi127@gmail.com', NULL, NULL, NULL, '$2y$10$nshUSHY9uZqtrkz69cfL/.oajc.VsMA2hjXRIYXfLioAAUjr.Z08y', NULL, NULL, 'penyelenggara', 1, '2025-09-19 18:15:23'),
(8, 'Akbar', 'akbar@gmail.com', NULL, NULL, NULL, '$2y$10$eu/B.RP8BVi43zzv.LyHLOylGZ82Vv7amfBAv9wVF0SwrgimCP7E6', NULL, NULL, 'penyelenggara', 1, '2025-10-18 07:23:49'),
(9, 'Akbar Firdaus', 'Admin@gmail.com', NULL, NULL, NULL, '$2y$10$qc.1t1v8rvPGciT7x0nns.DISgeDPCcSeTPw929FaFp9DkHuVy1hy', NULL, NULL, 'penyelenggara', 1, '2025-10-22 14:34:55'),
(14, 'Abayyya', 'akbare@gmail.com', '085894317392', NULL, NULL, '$2y$10$OjPOeGj8z/OZk3Pip8pU9uoN0L4kiUGFnbTf97vMvDK2NuhoH5pNG', NULL, NULL, 'penyelenggara', 1, '2026-01-15 20:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `workshops`
--

CREATE TABLE `workshops` (
  `id` int NOT NULL,
  `penyelenggara_id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `visibilitas` enum('public','internal') DEFAULT 'public',
  `deskripsi` text NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `tanggal_waktu` datetime NOT NULL,
  `jam_selesai` datetime DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `tipe_event` enum('gratis','berbayar') NOT NULL DEFAULT 'gratis',
  `harga` int DEFAULT '0',
  `nominal_denda` decimal(10,2) DEFAULT '0.00',
  `sertifikat_template` varchar(255) DEFAULT NULL,
  `sertifikat_prefix` varchar(100) DEFAULT NULL,
  `sertifikat_nomor_awal` int NOT NULL DEFAULT '1',
  `sertifikat_nama_fs` int NOT NULL DEFAULT '120',
  `sertifikat_nama_y_percent` decimal(7,2) NOT NULL DEFAULT '50.00',
  `sertifikat_nama_x_percent` decimal(7,2) NOT NULL DEFAULT '50.00',
  `sertifikat_nomor_fs` int NOT NULL DEFAULT '40',
  `sertifikat_nomor_y_percent` decimal(7,2) NOT NULL DEFAULT '60.00',
  `sertifikat_font` varchar(255) DEFAULT 'Poppins-SemiBold.ttf',
  `sertifikat_orientasi` enum('portrait','landscape') NOT NULL DEFAULT 'portrait',
  `sertifikat_nomor_x_percent` decimal(7,2) NOT NULL DEFAULT '50.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_kuesioner_active` tinyint(1) DEFAULT '0',
  `is_diskusi_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workshops`
--

INSERT INTO `workshops` (`id`, `penyelenggara_id`, `judul`, `visibilitas`, `deskripsi`, `poster`, `tanggal_waktu`, `jam_selesai`, `lokasi`, `tipe_event`, `harga`, `nominal_denda`, `sertifikat_template`, `sertifikat_prefix`, `sertifikat_nomor_awal`, `sertifikat_nama_fs`, `sertifikat_nama_y_percent`, `sertifikat_nama_x_percent`, `sertifikat_nomor_fs`, `sertifikat_nomor_y_percent`, `sertifikat_font`, `sertifikat_orientasi`, `sertifikat_nomor_x_percent`, `created_at`, `is_kuesioner_active`, `is_diskusi_active`) VALUES
(1, 1, 'SkillUp Training : Buat Project-mu Sendiri', 'public', 'ss', 'poster_68f3094267690.jpg', '2025-01-01 10:20:00', NULL, 'STIKOM El Rahma', 'gratis', 0, 0.00, 'template_68f3434973b42.png', '/SRT/TABLIGH/LDK/STL/IX/2025', 1, 120, 36.95, 50.15, 40, 27.19, 'Poppins-ExtraBoldItalic.ttf', 'landscape', 50.40, '2025-10-18 03:20:22', 0, 1),
(3, 1, 'Testing Event', 'internal', '-', 'poster_693ff71d55718.png', '2026-02-01 17:24:00', NULL, 'Masjid Pondok Pesantren Al Ihsan Baron', 'berbayar', 0, 0.00, 'template_696a04f58173c.png', '/SRT/BEM/2025', 12, 99, 23.91, 3.12, 17, 26.12, 'Poppins-ExtraBoldItalic.ttf', 'landscape', 52.48, '2025-12-15 10:24:14', 1, 1),
(4, 1, 'Testing Event', 'public', 'Hallo', 'poster_694924307f7f1.png', '2025-12-29 17:48:00', NULL, 'hii', 'berbayar', 10000, 0.00, 'template_696960f897cf2.jpg', '/SRT/BEM/2025', 1, 120, 50.00, 50.00, 40, 60.00, 'Montserrat-Italic-VariableFont_wght.ttf', 'portrait', 50.00, '2025-12-22 10:49:04', 0, 1),
(5, 1, 'Event Baruu ', 'public', 'hallo', 'poster_69674b2094c0d.png', '2026-01-14 14:51:00', NULL, 'Masjid Pondok Pesantren Al Ihsan Baron', 'gratis', 0, 0.00, NULL, '', 1, 120, 50.00, 50.00, 40, 60.00, 'Montserrat-Italic-VariableFont_wght.ttf', 'portrait', 50.00, '2026-01-14 07:52:00', 0, 1),
(6, 1, 'Mengetuk Pintu Langit dengan Lantunan Ayat Suci', 'public', 'hahaha', 'poster_69674d66cca61.png', '2026-01-31 15:01:00', NULL, 'Masjid Pondok Pesantren Al Ihsan Baron', 'gratis', 0, 0.00, NULL, '', 1, 120, 50.00, 50.00, 40, 60.00, 'Montserrat-Italic-VariableFont_wght.ttf', 'portrait', 50.00, '2026-01-14 08:01:42', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `workshop_answers`
--

CREATE TABLE `workshop_answers` (
  `id` int NOT NULL,
  `workshop_id` int NOT NULL,
  `santri_id` int NOT NULL,
  `question_id` int NOT NULL,
  `answer_text` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workshop_answers`
--

INSERT INTO `workshop_answers` (`id`, `workshop_id`, `santri_id`, `question_id`, `answer_text`, `created_at`) VALUES
(1, 3, 1, 1, 'hallo bang', '2026-01-26 12:08:47'),
(2, 3, 1, 2, 'baik', '2026-01-26 12:08:47');

-- --------------------------------------------------------

--
-- Table structure for table `workshop_discussions`
--

CREATE TABLE `workshop_discussions` (
  `id` int NOT NULL,
  `workshop_id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_type` enum('admin','santri') NOT NULL DEFAULT 'admin',
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workshop_discussions`
--

INSERT INTO `workshop_discussions` (`id`, `workshop_id`, `user_id`, `user_type`, `message`, `created_at`) VALUES
(3, 3, 1, 'admin', 'hiyaa', '2026-01-15 20:25:20'),
(4, 3, 1, 'admin', 'hallo', '2026-01-15 20:32:36'),
(5, 3, 1, 'admin', 'haii', '2026-01-15 20:39:20'),
(9, 6, 3, 'santri', 'haii', '2026-01-21 19:33:36'),
(10, 6, 1, 'admin', 'haii', '2026-01-26 09:33:46'),
(11, 3, 1, 'santri', 'hii', '2026-01-26 10:34:11');

-- --------------------------------------------------------

--
-- Table structure for table `workshop_materials`
--

CREATE TABLE `workshop_materials` (
  `id` int NOT NULL,
  `workshop_id` int NOT NULL,
  `judul_materi` varchar(255) NOT NULL,
  `deskripsi` text,
  `nama_file` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workshop_materials`
--

INSERT INTO `workshop_materials` (`id`, `workshop_id`, `judul_materi`, `deskripsi`, `nama_file`, `uploaded_at`) VALUES
(1, 3, 'Materi Sesi 1', 'haha', '1768305786_6966347ad66d8.pdf', '2026-01-13 12:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `workshop_questions`
--

CREATE TABLE `workshop_questions` (
  `id` int NOT NULL,
  `workshop_id` int NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('text','textarea','rating','radio','dropdown') DEFAULT 'text',
  `options` text,
  `is_required` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workshop_questions`
--

INSERT INTO `workshop_questions` (`id`, `workshop_id`, `question_text`, `question_type`, `options`, `is_required`, `created_at`) VALUES
(1, 3, 'Bagaiman Pendapata anda tentang acara', 'text', NULL, 1, '2026-01-11 20:16:55'),
(2, 3, 'Bagaiman cara mengisinya', 'radio', 'baik,tidak baik', 1, '2026-01-11 20:17:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_fields_ibfk_1` (`workshop_id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_unik` (`kode_unik`),
  ADD KEY `idx_santri` (`santri_id`);

--
-- Indexes for table `pendaftaran_data`
--
ALTER TABLE `pendaftaran_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_data_ibfk_2` (`field_id`),
  ADD KEY `pendaftaran_data_ibfk_1` (`pendaftaran_id`);

--
-- Indexes for table `santri`
--
ALTER TABLE `santri`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `barcode_code` (`barcode_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `no_whatsapp` (`no_whatsapp`);

--
-- Indexes for table `workshops`
--
ALTER TABLE `workshops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workshop_answers`
--
ALTER TABLE `workshop_answers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `workshop_discussions`
--
ALTER TABLE `workshop_discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workshop_id` (`workshop_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `workshop_materials`
--
ALTER TABLE `workshop_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workshop_id` (`workshop_id`);

--
-- Indexes for table `workshop_questions`
--
ALTER TABLE `workshop_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workshop_id` (`workshop_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `form_fields`
--
ALTER TABLE `form_fields`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `pendaftaran_data`
--
ALTER TABLE `pendaftaran_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `santri`
--
ALTER TABLE `santri`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `workshops`
--
ALTER TABLE `workshops`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `workshop_answers`
--
ALTER TABLE `workshop_answers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `workshop_discussions`
--
ALTER TABLE `workshop_discussions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `workshop_materials`
--
ALTER TABLE `workshop_materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `workshop_questions`
--
ALTER TABLE `workshop_questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD CONSTRAINT `form_fields_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_santri_fk_final` FOREIGN KEY (`santri_id`) REFERENCES `santri` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pendaftaran_data`
--
ALTER TABLE `pendaftaran_data`
  ADD CONSTRAINT `pendaftaran_data_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pendaftaran_data_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workshop_discussions`
--
ALTER TABLE `workshop_discussions`
  ADD CONSTRAINT `workshop_discussions_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workshop_materials`
--
ALTER TABLE `workshop_materials`
  ADD CONSTRAINT `workshop_materials_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workshop_questions`
--
ALTER TABLE `workshop_questions`
  ADD CONSTRAINT `workshop_questions_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
