-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 03, 2026 at 05:17 PM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `surat`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf',
  `letter_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `path`, `filename`, `extension`, `letter_id`, `user_id`, `created_at`, `updated_at`) VALUES
(6, NULL, '1754630225-PPT(Kelompok7).pdf', 'pdf', 61, 1, '2025-08-08 06:17:05', '2025-08-08 06:17:05'),
(7, NULL, '1754630248-PPT(Kelompok7).pdf', 'pdf', 62, 1, '2025-08-08 06:17:28', '2025-08-08 06:17:28'),
(8, NULL, '1754630284-PPT(Kelompok7).pdf', 'pdf', 63, 1, '2025-08-08 06:18:04', '2025-08-08 06:18:04'),
(9, NULL, '1754630318-PPT(Kelompok7).pdf', 'pdf', 64, 1, '2025-08-08 06:18:38', '2025-08-08 06:18:38'),
(10, NULL, '1754743212-WhatsApp-Image-2025-08-08-at-20.22.19_b34574ea.jpg', 'jpg', 65, 1, '2025-08-09 13:40:12', '2025-08-09 13:40:12'),
(11, NULL, '1761390873-tes_ocr_ideal.pdf', 'pdf', 67, 1, '2025-10-25 12:14:33', '2025-10-25 12:14:33'),
(12, NULL, '1762102196-Screenshot-2025-11-02-234719.png', 'png', 68, 1, '2025-11-02 17:49:56', '2025-11-02 17:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `classifications`
--

CREATE TABLE `classifications` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classifications`
--

INSERT INTO `classifications` (`id`, `code`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'ADM', 'Administrasi', 'Jenis surat yang berkaitan dengan administrasi', '2025-02-23 03:14:07', '2025-02-23 03:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `configs`
--

CREATE TABLE `configs` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configs`
--

INSERT INTO `configs` (`id`, `code`, `value`, `created_at`, `updated_at`) VALUES
(1, 'default_password', 'admin', NULL, NULL),
(2, 'page_size', '5', NULL, NULL),
(3, 'app_name', 'Aplikasi Surat Menyurat', NULL, NULL),
(4, 'institution_name', '404nfid', NULL, NULL),
(5, 'institution_address', 'Jl. Padat Karya', NULL, NULL),
(6, 'institution_phone', '082121212121', NULL, NULL),
(7, 'institution_email', 'admin@admin.com', NULL, NULL),
(8, 'language', 'id', NULL, NULL),
(9, 'pic', 'M. Iqbal Effendi', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dispositions`
--

CREATE TABLE `dispositions` (
  `id` bigint UNSIGNED NOT NULL,
  `to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `letter_status` bigint UNSIGNED NOT NULL,
  `letter_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `letters`
--

CREATE TABLE `letters` (
  `id` bigint UNSIGNED NOT NULL,
  `reference_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor Surat',
  `agenda_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `letter_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incoming' COMMENT 'Surat Masuk (incoming)/Surat Keluar (outgoing)',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Baru',
  `classification_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `letters`
--

INSERT INTO `letters` (`id`, `reference_number`, `agenda_number`, `from`, `to`, `letter_date`, `received_date`, `description`, `note`, `type`, `status`, `classification_code`, `user_id`, `created_at`, `updated_at`) VALUES
(52, '910374893466', '1122334455', NULL, 'nanad', '2025-08-16', NULL, 'tes lagi', NULL, 'outgoing', 'Selesai', 'ADM', 1, '2025-08-05 16:54:40', '2025-08-08 06:15:55'),
(54, '910374893485', '1122334455', NULL, 'nanad', '2025-08-23', NULL, 'coba lagi', 'coba coba', 'outgoing', 'Selesai', 'ADM', 1, '2025-08-05 17:24:09', '2025-08-08 06:15:52'),
(58, '910374893400', '1122334455', 'Ammar', NULL, '2025-08-16', '2025-08-12', 'tes', 'coba coba', 'incoming', 'Selesai', 'ADM', 1, '2025-08-08 04:11:03', '2025-08-08 04:14:43'),
(60, '9103748934888', '1122334455', 'nanad', NULL, '2025-08-23', '2025-08-12', 'coba lagi', 'tes', 'incoming', 'Selesai', 'ADM', 1, '2025-08-08 05:40:13', '2025-08-08 06:05:26'),
(61, '437856', '1122334455', 'nanad', NULL, '2025-08-29', '2025-08-29', 'tes 4', 'tes', 'incoming', 'Selesai', 'ADM', 1, '2025-08-08 06:17:05', '2025-08-08 06:19:00'),
(62, '780978508970', '1122334455', 'Ammar', NULL, '2025-08-23', '2025-08-19', 'tes 5', 'coba coba', 'incoming', 'Selesai', 'ADM', 1, '2025-08-08 06:17:28', '2025-08-08 06:19:03'),
(63, '2123213123', '46456', 'Ammar', NULL, '2025-08-27', '2025-08-20', 'tes lagi', 'tes 2', 'incoming', 'Selesai', 'ADM', 1, '2025-08-08 06:18:04', '2025-08-08 06:19:01'),
(64, '2132312413435', '79789686r', NULL, 'nanad', '2025-08-29', NULL, 'keluar', 'coba coba', 'outgoing', 'Selesai', 'ADM', 1, '2025-08-08 06:18:38', '2025-08-08 06:19:07'),
(65, '0002', '421', 'amar', NULL, '2025-08-06', '2025-08-09', 'suratttt aoaaaa yaaa', NULL, 'incoming', 'Selesai', 'ADM', 1, '2025-08-09 13:40:12', '2025-09-06 17:59:19'),
(66, '910374893489', '46456', 'Ammar', NULL, '2025-09-08', '2025-09-09', 'surat', NULL, 'incoming', 'Selesai', 'ADM', 1, '2025-09-09 17:15:40', '2025-09-09 17:19:08'),
(67, '24141', '21412414', 'dsgsgs', NULL, '0124-04-12', '0121-04-12', 'nabsfbfb', NULL, 'incoming', 'Baru', 'ADM', 1, '2025-10-25 12:14:33', '2025-10-25 12:14:33'),
(68, '600/2886', '21412410', 'nanad', NULL, '2025-08-02', '2025-11-13', 'Peningkatan Kapasitas SDM Operator Sitranspor', NULL, 'incoming', 'Baru', 'ADM', 1, '2025-11-02 17:49:56', '2025-11-02 17:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `letter_statuses`
--

CREATE TABLE `letter_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `letter_statuses`
--

INSERT INTO `letter_statuses` (`id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rahasia', '2025-02-23 03:14:07', '2025-02-23 03:14:07'),
(2, 'Segera', '2025-02-23 03:14:07', '2025-02-23 03:14:07'),
(3, 'Biasa', '2025-02-23 03:14:07', '2025-02-23 03:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2014_10_12_200000_add_two_factor_columns_to_users_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2022_12_05_081849_create_configs_table', 1),
(7, '2022_12_05_083409_create_letter_statuses_table', 1),
(8, '2022_12_05_083945_create_classifications_table', 1),
(9, '2022_12_05_084544_create_letters_table', 1),
(10, '2022_12_05_092303_create_dispositions_table', 1),
(11, '2022_12_05_093329_create_attachments_table', 1),
(12, '2025_08_06_230721_add_status_to_letters_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `phone`, `role`, `is_active`, `profile_picture`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@admin.com', '2025-02-23 03:14:07', '$2y$10$r0vCZVkH9fmSsBH4CDJCgO03q9UAe9sDbFKGAdqf188y6heVAdvnm', NULL, NULL, '082121212121', 'admin', 1, NULL, 'YHyusstPuvlcMDbRHuGrnRrq1VGGRPA9OBZf7ScRooJt5YpXfhM2Iyhw5Vt8', '2025-02-23 03:14:07', '2025-02-23 03:14:07'),
(2, 'Ammar Naufal', 'amarnfl238@gmail.com', NULL, '$2y$10$Vzxuxl5X0EGSn7ZsWCtB2OOfgFm9bCPMi5XsP6RMu/gisvcikx8xC', NULL, NULL, '089502874178', 'staff', 1, NULL, NULL, '2025-09-07 14:35:32', '2025-09-07 14:35:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_letter_id_foreign` (`letter_id`),
  ADD KEY `attachments_user_id_foreign` (`user_id`);

--
-- Indexes for table `classifications`
--
ALTER TABLE `classifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `classifications_code_unique` (`code`);

--
-- Indexes for table `configs`
--
ALTER TABLE `configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `configs_code_unique` (`code`);

--
-- Indexes for table `dispositions`
--
ALTER TABLE `dispositions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispositions_letter_status_foreign` (`letter_status`),
  ADD KEY `dispositions_letter_id_foreign` (`letter_id`),
  ADD KEY `dispositions_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `letters`
--
ALTER TABLE `letters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `letters_reference_number_unique` (`reference_number`),
  ADD KEY `letters_classification_code_foreign` (`classification_code`),
  ADD KEY `letters_user_id_foreign` (`user_id`);

--
-- Indexes for table `letter_statuses`
--
ALTER TABLE `letter_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

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
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `classifications`
--
ALTER TABLE `classifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `configs`
--
ALTER TABLE `configs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `dispositions`
--
ALTER TABLE `dispositions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `letters`
--
ALTER TABLE `letters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `letter_statuses`
--
ALTER TABLE `letter_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_letter_id_foreign` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `dispositions`
--
ALTER TABLE `dispositions`
  ADD CONSTRAINT `dispositions_letter_id_foreign` FOREIGN KEY (`letter_id`) REFERENCES `letters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispositions_letter_status_foreign` FOREIGN KEY (`letter_status`) REFERENCES `letter_statuses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dispositions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `letters`
--
ALTER TABLE `letters`
  ADD CONSTRAINT `letters_classification_code_foreign` FOREIGN KEY (`classification_code`) REFERENCES `classifications` (`code`),
  ADD CONSTRAINT `letters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
