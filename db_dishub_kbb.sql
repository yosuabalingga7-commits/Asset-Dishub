-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2026 at 07:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_dishub_kbb`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `id_asset` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `jenis` varchar(255) NOT NULL,
  `icon_marker` varchar(255) NOT NULL DEFAULT '?',
  `merk` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Baik',
  `alamat` text DEFAULT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tgl_pemasangan` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `category_id`, `id_asset`, `nama`, `kategori`, `jenis`, `icon_marker`, `merk`, `status`, `alamat`, `lat`, `lng`, `foto`, `tgl_pemasangan`, `catatan`, `created_at`, `updated_at`) VALUES
(1, NULL, 'AST-KGDZFO', 'Zebra Cross Depan Kantor Bupati', 'Fasilitas Lalu Lintas', 'Road Barrier Beton', '🚧', NULL, 'Baik', 'Lembang, Bandung Barat, Jawa, 40391, Indonesia', -6.81110330, 107.61712390, 'assets/1775898751_zebra-cross-depan-kantor-bupati.jpg', NULL, NULL, '2026-04-11 09:12:32', '2026-04-11 09:12:32'),
(2, NULL, 'AST-5DYHZO', 'Lampu LED, Padalarang, Bandung Barat, Jawa', 'Penerangan Jalan Umum (PJU)', 'Lampu LED', '💡', NULL, 'Baik', 'Padalarang, Jalan Cihaliwung, Ngamprah, Bandung Barat, Jawa Barat, Jawa, 40553, Indonesia', -6.84226550, 107.49690930, 'assets/1775898813_lampu-led-padalarang-bandung-barat-jawa.jpg', NULL, NULL, '2026-04-11 09:13:33', '2026-04-11 09:13:33'),
(3, NULL, 'AST-J2FUNO', 'CCTV Monitoring  Padalarang', 'Pengendalian & Pengawasan', 'CCTV Surveilans', '📹', NULL, 'Rusak', 'Padalarang, Jalan Cihaliwung, Ngamprah, Bandung Barat, Jawa Barat, Jawa, 40553, Indonesia', -6.84226550, 107.49690930, 'assets/1775898874_cctv-monitoring-padalarang.png', NULL, NULL, '2026-04-11 09:14:34', '2026-04-11 09:14:34'),
(4, NULL, 'AST-5B84JS', 'Lampu Merah , Jl. Nanggeleng - Cirahayu No.367', 'Fasilitas Lalu Lintas', 'APILL (Traffic Light)', '🚦', NULL, 'Proses Perbaikan', 'Nanggeleng, Bandung Barat, Jawa, 40558, Indonesia', -6.76406560, 107.36512870, 'assets/1775898925_lampu-merah-jl-nanggeleng-cirahayu-no367.jpg', NULL, NULL, '2026-04-11 09:15:25', '2026-04-11 09:15:25'),
(5, NULL, 'AST-RRHFDY', 'Terminal Lembang', 'Prasarana Transportasi', 'Terminal', '🚏', NULL, 'Rusak', 'Cihanjuang, Bandung Barat, Jawa Barat, Jawa, 40559, Indonesia', -6.85335710, 107.57883905, 'assets/1775899415_terminal-lembang.jpg', NULL, NULL, '2026-04-11 09:23:35', '2026-04-11 09:23:35'),
(6, NULL, 'AST-I5OJ6L', 'CCTV Monitoring  SMA MUSLIMIN RONGG', 'Pengendalian & Pengawasan', 'CCTV Surveilans', '📹', NULL, 'Rusak', 'SMA MUSLIMIN RONGGA, 001/016, Jalan Akses Proyek Bendungan Cisokan, DUSUN 1 CIMAREL, Bandung Barat, Jawa Barat, Jawa, 40565, Indonesia', -6.96128880, 107.28231130, 'assets/1776076214_cctv-monitoring-sma-muslimin-rongg.png', NULL, NULL, '2026-04-13 10:30:15', '2026-04-13 10:30:15'),
(7, NULL, 'AST-XVQCPX', 'Terminal DESA CINTA ASIH KC. CIPONGKOR', 'Prasarana Transportasi', 'Terminal', '🚏', NULL, 'Kritis', 'DUSUN PALASARI DESA CINTA ASIH KC. CIPONGKOR KAB. BANDUNG BARAT, Cintaasih, Bandung Barat, Jawa, Indonesia', -6.96888690, 107.32405690, 'assets/1776076360_terminal-desa-cinta-asih-kc-cipongkor.jpg', NULL, NULL, '2026-04-13 10:32:40', '2026-04-13 10:32:40'),
(8, NULL, 'AST-PBIP35', 'Halte Bus Cihampelas', 'Prasarana Transportasi', 'Halte Bus', '🚏', NULL, 'Rusak', 'Cihampelas, Bandung Barat, Jawa Barat, Jawa, 40767, Indonesia', -6.92557210, 107.47967810, 'assets/1776076516_halte-bus-cihampelas.jpg', NULL, NULL, '2026-04-13 10:35:16', '2026-04-13 10:35:16'),
(9, NULL, 'AST-ZCUNMV', 'Rambu Larangan gununghalu', 'Perlengkapan Jalan', 'Rambu Larangan', '🛑', NULL, 'Kritis', 'Gununghalu, Bandung Barat, Jawa Barat, Jawa, Indonesia', -7.03179510, 107.30939120, 'assets/1776076623_rambu-larangan-gununghalu.png', NULL, NULL, '2026-04-13 10:37:03', '2026-04-13 10:37:03'),
(10, NULL, 'AST-A9D3WH', 'CCTV Alun Alun Cililin', 'Pengendalian & Pengawasan', 'CCTV Surveilans', '📹', NULL, 'Proses Perbaikan', 'Cililin, Bandung Barat, Jawa, 40767, Indonesia', -6.95079490, 107.45788450, 'assets/1776076736_cctv-alun-alun-cililin.png', NULL, NULL, '2026-04-13 10:38:56', '2026-04-13 10:38:56'),
(11, NULL, 'AST-L8ZOW5', 'Terminal Cipatat', 'Prasarana Transportasi', 'Terminal', '🚏', NULL, 'Proses Perbaikan', 'Cipatat, Jalan Orion, Cipatat, Bandung Barat, Jawa, 40554, Indonesia', -6.82203860, 107.38604570, 'assets/1776076840_terminal-cipatat.jpg', NULL, NULL, '2026-04-13 10:40:40', '2026-04-13 10:40:40'),
(12, NULL, 'AST-DPPXA0', 'Lampu merah', 'Fasilitas Lalu Lintas', 'Warning Light', '🚦', NULL, 'Baik', 'Sindangkerta, Bandung Barat, Jawa, Indonesia', -6.99129760, 107.40868950, 'assets/1776077082_lampu-merah.jpg', NULL, NULL, '2026-04-13 10:44:42', '2026-04-13 10:44:42'),
(13, NULL, 'AST-YHYIKZ', 'CCTV Saguling', 'Pengendalian & Pengawasan', 'CCTV Surveilans', '📹', NULL, 'Rusak', 'Saguling, Bandung Barat, Jawa, 40564, Indonesia', -6.89183120, 107.37056690, 'assets/1776077176_cctv-saguling.png', NULL, NULL, '2026-04-13 10:46:16', '2026-04-13 10:46:16');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `ikon_kategori` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_masyarakats`
--

CREATE TABLE `laporan_masyarakats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_number` varchar(255) NOT NULL,
  `nama_pelapor` varchar(255) NOT NULL,
  `kontak_pelapor` varchar(255) NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `deskripsi_keluhan` text NOT NULL,
  `kondisi_aset` varchar(255) NOT NULL DEFAULT 'Rusak',
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `lokasi_koordinat` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'masuk',
  `is_validated` tinyint(1) NOT NULL DEFAULT 0,
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kepemilikan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laporan_masyarakats`
--

INSERT INTO `laporan_masyarakats` (`id`, `ticket_number`, `nama_pelapor`, `kontak_pelapor`, `judul_laporan`, `deskripsi_keluhan`, `kondisi_aset`, `lat`, `lng`, `alamat`, `lokasi_koordinat`, `foto`, `status`, `is_validated`, `catatan_admin`, `created_at`, `updated_at`, `kepemilikan`) VALUES
(1, 'LP-20260411-R2BKD', 'Yosua Balingga', '0882001927007', 'Tiang PJU', '-', 'Rusak', -6.84226550, 107.49690930, 'Padalarang, Jalan Cihaliwung, Ngamprah, Bandung Barat, Jawa Barat, Jawa, 40553, Indonesia', '-6.8422655,107.4969093', 'laporan_masyarakat/1775900366_LP-20260411-R2BKD.jpg', 'masuk', 0, NULL, '2026-04-11 09:39:26', '2026-04-11 09:39:26', NULL),
(2, 'LP-20260413-G9BLK', 'Alex Balingga', '0882001927007', 'Halte  Cihanjuang, Bandung Barat,', '-', 'Rusak', -6.84983510, 107.56950220, 'Jalan Cihanjuang, RW 04 KEL. CIHANJUANG RAHAYU KEC. PAROMPONG KAB. BANDUNG BARAT, Girimulya, Cihanjuang, Bandung Barat, Jawa Barat, Jawa, 40559, Indonesia', '-6.8498351,107.5695022', 'laporan_masyarakat/1776078205_LP-20260413-G9BLK.jpg', 'masuk', 0, NULL, '2026-04-13 11:03:25', '2026-04-13 11:03:25', NULL),
(3, 'LP-20260413-6J0GH', 'naufal paman', '082181381282', 'CCTV Jalan  padalarang', '-', 'Rusak', -6.84226550, 107.49690930, 'Padalarang, Jalan Cihaliwung, Ngamprah, Bandung Barat, Jawa Barat, Jawa, 40553, Indonesia', '-6.8422655,107.4969093', 'laporan_masyarakat/1776078280_LP-20260413-6J0GH.png', 'Proses Perbaikan', 1, 'Validasi otomatis melalui pemilihan kategori.', '2026-04-13 11:04:40', '2026-04-13 11:16:58', 'dishub');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_petugas`
--

CREATE TABLE `laporan_petugas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_petugas` varchar(255) NOT NULL,
  `nip` varchar(255) NOT NULL,
  `no_wa` varchar(255) NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `kondisi_aset` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'masuk',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `status_after` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_tickets`
--

CREATE TABLE `maintenance_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_code` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'dishub',
  `kepemilikan` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `jenis_aset` varchar(255) DEFAULT NULL,
  `edit_reason` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `asset_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `report_id` bigint(20) UNSIGNED DEFAULT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `location_address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `foto_perbaikan` varchar(255) DEFAULT NULL,
  `completion_notes` text DEFAULT NULL,
  `technician_name` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_tickets`
--

INSERT INTO `maintenance_tickets` (`id`, `ticket_code`, `category`, `kepemilikan`, `subject`, `jenis_aset`, `edit_reason`, `deadline`, `asset_id`, `category_id`, `report_id`, `priority`, `status`, `location_address`, `latitude`, `longitude`, `description`, `foto_perbaikan`, `completion_notes`, `technician_name`, `user_id`, `started_at`, `finished_at`, `created_at`, `updated_at`) VALUES
(1, 'MNT-81P2YBFF', 'PENERANGAN JALAN UMUM (PJU)', 'dishub', NULL, 'Lampu LED', NULL, '2026-04-03', NULL, 1, 3, 'Normal', 'proses', 'Padalarang, Jalan Cihaliwung, Ngamprah, Bandung Barat, Jawa Barat, Jawa, 40553, Indonesia', -6.84226550, 107.49690930, '-', NULL, NULL, 'Seksi PJU', NULL, '2026-04-13 11:16:58', NULL, '2026-04-13 11:16:58', '2026-04-13 11:16:58');

-- --------------------------------------------------------

--
-- Table structure for table `map_settings`
--

CREATE TABLE `map_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `latitude` double NOT NULL DEFAULT -6.8431,
  `longitude` double NOT NULL DEFAULT 107.4912,
  `zoom` int(11) NOT NULL DEFAULT 11,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `map_settings`
--

INSERT INTO `map_settings` (`id`, `latitude`, `longitude`, `zoom`, `created_at`, `updated_at`) VALUES
(1, -6.8431, 107.4912, 11, '2026-04-11 09:11:08', '2026-04-11 09:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_19_053122_create_map_settings_table', 1),
(5, '2026_02_20_025627_create_laporan_masyarakats_table', 1),
(6, '2026_02_22_021348_create_assets_table', 1),
(7, '2026_02_22_093242_create_maintenance_tables', 1),
(8, '2026_02_23_090347_create_task_logs_table', 1),
(9, '2026_02_25_054709_create_categories_table', 1),
(10, '2026_03_02_024508_add_role_to_users_table', 1),
(11, '2026_03_02_025332_add_role_to_users_table', 1),
(12, '2026_03_03_080017_create_pengaduans_table', 1),
(13, '2026_03_03_080226_create_pengaduan_logs_table', 1),
(14, '2026_03_06_082937_create_laporan_petugas_table', 1),
(15, '2026_03_09_081203_create_maintenance_logs_table', 1),
(16, '2026_03_13_085527_add_category_id_to_assets_table', 1),
(17, '2026_03_26_034732_add_alamat_to_laporan_masyarakats_table', 1),
(18, '2026_03_26_072522_add_kepemilikan_to_laporan_masyarakats_table', 1),
(19, '2026_03_26_074925_add_validation_columns_to_laporan_masyarakats', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaduans`
--

CREATE TABLE `pengaduans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_number` varchar(255) NOT NULL,
  `nama_pelapor` varchar(255) NOT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `kontak_pelapor` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `kategori_aset` varchar(255) DEFAULT NULL,
  `jenis_aset` varchar(255) NOT NULL,
  `kondisi_aset` varchar(255) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `alamat_manual` text DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'masuk',
  `is_validated` tinyint(1) NOT NULL DEFAULT 0,
  `kategori_laporan` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `petugas_nama` varchar(255) DEFAULT NULL,
  `petugas_nip` varchar(255) DEFAULT NULL,
  `petugas_jabatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan_logs`
--

CREATE TABLE `pengaduan_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pengaduan_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `aksi` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('k31hGTud6moQTVcd3pCJATmn8blYxrkSmAnHx4Fv', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia1Y5UkNzeHZSU3ZZbkxSM0YwOTEzUk1lM21rNjlJRmwwcjNiRGpzVyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9wZW5nYWR1YW4vZGV0YWlsLzMiO3M6NToicm91dGUiO3M6MjA6ImFkbWluLnBlbmdhZHVhbi5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776079171);

-- --------------------------------------------------------

--
-- Table structure for table `task_logs`
--

CREATE TABLE `task_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `status_from` varchar(255) DEFAULT NULL,
  `status_to` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','dinas','seksi') NOT NULL DEFAULT 'petugas',
  `no_wa` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assets_id_asset_unique` (`id_asset`),
  ADD KEY `assets_category_id_foreign` (`category_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_nama_kategori_unique` (`nama_kategori`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laporan_masyarakats`
--
ALTER TABLE `laporan_masyarakats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `laporan_masyarakats_ticket_number_unique` (`ticket_number`);

--
-- Indexes for table `laporan_petugas`
--
ALTER TABLE `laporan_petugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_petugas_nip_index` (`nip`);

--
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_tickets`
--
ALTER TABLE `maintenance_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `maintenance_tickets_ticket_code_unique` (`ticket_code`),
  ADD KEY `maintenance_tickets_asset_id_foreign` (`asset_id`),
  ADD KEY `maintenance_tickets_report_id_foreign` (`report_id`),
  ADD KEY `maintenance_tickets_user_id_foreign` (`user_id`);

--
-- Indexes for table `map_settings`
--
ALTER TABLE `map_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengaduans`
--
ALTER TABLE `pengaduans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengaduans_ticket_number_unique` (`ticket_number`);

--
-- Indexes for table `pengaduan_logs`
--
ALTER TABLE `pengaduan_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengaduan_logs_user_id_foreign` (`user_id`),
  ADD KEY `pengaduan_logs_pengaduan_id_index` (`pengaduan_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `task_logs`
--
ALTER TABLE `task_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_logs_ticket_id_foreign` (`ticket_id`),
  ADD KEY `task_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_masyarakats`
--
ALTER TABLE `laporan_masyarakats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `laporan_petugas`
--
ALTER TABLE `laporan_petugas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_tickets`
--
ALTER TABLE `maintenance_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `map_settings`
--
ALTER TABLE `map_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `pengaduans`
--
ALTER TABLE `pengaduans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaduan_logs`
--
ALTER TABLE `pengaduan_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `task_logs`
--
ALTER TABLE `task_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_tickets`
--
ALTER TABLE `maintenance_tickets`
  ADD CONSTRAINT `maintenance_tickets_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_tickets_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `laporan_masyarakats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pengaduan_logs`
--
ALTER TABLE `pengaduan_logs`
  ADD CONSTRAINT `pengaduan_logs_pengaduan_id_foreign` FOREIGN KEY (`pengaduan_id`) REFERENCES `pengaduans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengaduan_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_logs`
--
ALTER TABLE `task_logs`
  ADD CONSTRAINT `task_logs_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `maintenance_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
