-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 28 déc. 2025 à 23:34
-- Version du serveur : 9.1.0
-- Version de PHP : 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_depenses`
--

-- --------------------------------------------------------

--
-- Structure de la table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
CREATE TABLE IF NOT EXISTS `budgets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `period` enum('monthly','weekly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `threshold_percentage` int NOT NULL DEFAULT '80',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budgets_user_id_category_id_period_start_date_unique` (`user_id`,`category_id`,`period`,`start_date`),
  KEY `budgets_category_id_foreign` (`category_id`),
  KEY `budgets_user_id_start_date_end_date_index` (`user_id`,`start_date`,`end_date`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `budgets`
--

INSERT INTO `budgets` (`id`, `user_id`, `category_id`, `amount`, `period`, `start_date`, `end_date`, `notifications_enabled`, `threshold_percentage`, `created_at`, `updated_at`) VALUES
(13, 2, 2, 5000.00, 'monthly', '2025-12-01', '2025-12-31', 1, 80, '2025-12-28 16:53:54', '2025-12-28 16:53:54'),
(11, 1, 13, 30000.00, 'monthly', '2025-12-01', '2025-12-31', 1, 80, '2025-12-28 18:17:07', '2025-12-28 18:17:07'),
(10, 1, 2, 50000.00, 'monthly', '2025-12-01', '2025-12-31', 1, 80, '2025-12-28 18:17:07', '2025-12-28 18:17:07'),
(9, 1, 1, 100000.00, 'monthly', '2025-12-01', '2025-12-31', 1, 80, '2025-12-28 18:17:07', '2025-12-28 18:17:07');

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('expense','income') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fas fa-receipt',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `type`, `color`, `icon`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alimentation', 'expense', '#EF4444', 'fas fa-utensils', 1, '2025-12-28 15:35:41', '2025-12-28 15:35:41'),
(2, 1, 'Transport', 'expense', '#F59E0B', 'fas fa-bus', 1, '2025-12-28 15:35:41', '2025-12-28 15:35:41'),
(3, 1, 'Logement', 'expense', '#3B82F6', 'fas fa-home', 1, '2025-12-28 15:35:41', '2025-12-28 15:35:41'),
(4, 1, 'Santé', 'expense', '#10B981', 'fas fa-heartbeat', 1, '2025-12-28 15:35:41', '2025-12-28 15:35:41'),
(13, 1, 'Loisirs', 'expense', '#36A2EB', 'gamepad', 0, '2025-12-28 18:03:04', '2025-12-28 18:03:04'),
(7, 1, 'Salaire', 'income', '#22C55E', 'fas fa-money-bill-wave', 1, '2025-12-28 15:36:07', '2025-12-28 15:36:07'),
(8, 1, 'Freelance', 'income', '#14B8A6', 'fas fa-laptop-code', 0, '2025-12-28 15:36:07', '2025-12-28 15:36:07'),
(9, 1, 'Business', 'income', '#0EA5E9', 'fas fa-briefcase', 0, '2025-12-28 15:36:07', '2025-12-28 15:36:07'),
(10, 1, 'Autres revenus', 'income', '#84CC16', 'fas fa-coins', 0, '2025-12-28 15:36:07', '2025-12-28 15:36:07'),
(12, 1, 'Alimentation', 'expense', '#FF6384', 'utensils', 1, '2025-12-28 18:03:04', '2025-12-28 18:03:04'),
(14, 2, 'transport', 'expense', '#6ab982', 'car', 0, '2025-12-28 16:41:03', '2025-12-28 16:41:03');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_25_151304_create_categories_table', 1),
(5, '2025_12_25_151304_create_transactions_table', 1),
(6, '2025_12_25_151305_create_budgets_table', 1),
(7, '2025_12_25_151306_create_savings_goals_table', 1);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `savings_goals`
--

DROP TABLE IF EXISTS `savings_goals`;
CREATE TABLE IF NOT EXISTS `savings_goals` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `current_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deadline` date DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#10B981',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `savings_goals_user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3Tq021x9OAi8Upv0vYNJXsxhMmJj2hBdCWDS1HEf', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidTZRRE5iYlBSekNDSjhyMVh6T1U2RUhnOUdUcUVRZkZJS1BtUTIzQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9maWxlIjtzOjU6InJvdXRlIjtzOjEyOiJwcm9maWxlLmVkaXQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1766964858);

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('expense','income') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('cash','card','transfer','mobile_money') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurring_frequency` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_date_index` (`user_id`,`date`),
  KEY `transactions_category_id_index` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `amount`, `type`, `description`, `date`, `receipt_path`, `location`, `payment_method`, `is_recurring`, `recurring_frequency`, `tags`, `created_at`, `updated_at`) VALUES
(11, 1, 13, 10000.00, 'expense', 'Sortie cinéma', '2025-12-12', NULL, NULL, 'card', 0, NULL, NULL, '2025-12-28 18:17:25', '2025-12-28 18:17:25'),
(10, 1, 2, 15000.00, 'expense', 'Bus et taxi Djibouti-ville', '2025-12-08', NULL, NULL, 'cash', 0, NULL, NULL, '2025-12-28 18:17:25', '2025-12-28 18:17:25'),
(9, 1, 1, 25000.00, 'expense', 'Courses marché Djibouti-ville', '2025-12-05', NULL, NULL, 'cash', 0, NULL, NULL, '2025-12-28 18:17:25', '2025-12-28 18:17:25'),
(12, 1, 13, 15000.00, 'expense', 'pèche', '2025-12-28', NULL, NULL, 'cash', 0, NULL, NULL, '2025-12-28 16:22:07', '2025-12-28 18:28:05'),
(13, 2, 2, 2500.00, 'expense', 'bus', '2025-12-28', NULL, NULL, 'cash', 0, NULL, NULL, '2025-12-28 16:42:31', '2025-12-28 16:42:31'),
(14, 1, 7, 200000.00, 'income', 'le salaire mensuelle', '2025-12-01', NULL, 'Bank BCIMR', 'card', 0, NULL, NULL, '2025-12-28 19:19:18', '2025-12-28 19:25:00');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'liban', 'abdillahiadenliban@gmail.com', NULL, '$2y$12$FHc4e6phnnh3DJHiS0G0quLuhjNIk3yil8xS.j0A248oTvfWPSNe6', NULL, '2025-12-26 15:04:44', '2025-12-26 15:04:44'),
(2, 'Rahima', 'rahisamireh0@gmail.com', NULL, '$2y$12$702y49rLPwWMWdeHlQIoXO.gLQH8Sb7eLZJz3bqU03dMf1x2OTPOe', 'tcpjuswEG2FDRPvlRLlpO4DRppuqMWQll4Y4tn41spsKJ4nFQV2VlQNCnKCn', '2025-12-28 16:39:00', '2025-12-28 16:39:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
