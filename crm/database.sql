-- CRM Database Schema and Initial Data
-- Generated from Laravel Migrations

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Database: Create database if not exists (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS `crm_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `crm_db`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dealer','client') NOT NULL DEFAULT 'client',
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_index` (`role`),
  KEY `users_sms_enabled_index` (`sms_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sessions`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cars`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cars` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vin` varchar(17) NOT NULL,
  `make_model` varchar(100) NOT NULL,
  `year` year(4) DEFAULT NULL,
  `lot_number` varchar(50) DEFAULT NULL,
  `auction_name` varchar(50) DEFAULT NULL,
  `auction_location` varchar(100) DEFAULT NULL,
  `container_number` varchar(50) DEFAULT NULL,
  `status` enum('purchased','warehouse','loaded','on_way','poti','green','delivered') NOT NULL DEFAULT 'purchased',
  `vehicle_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `additional_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_date` date DEFAULT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `client_id_number` varchar(20) DEFAULT NULL,
  `main_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cars_user_id_foreign` (`user_id`),
  KEY `cars_client_user_id_foreign` (`client_user_id`),
  KEY `cars_vin_index` (`vin`),
  KEY `cars_lot_number_index` (`lot_number`),
  KEY `cars_container_number_index` (`container_number`),
  KEY `cars_status_index` (`status`),
  KEY `cars_user_id_status_index` (`user_id`,`status`),
  KEY `cars_client_user_id_status_index` (`client_user_id`,`status`),
  CONSTRAINT `cars_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cars_client_user_id_foreign` FOREIGN KEY (`client_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `car_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `purpose` enum('vehicle','shipping','balance_topup','internal_transfer','other') NOT NULL DEFAULT 'other',
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_car_id_foreign` (`car_id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_user_id_payment_date_index` (`user_id`,`payment_date`),
  KEY `transactions_car_id_payment_date_index` (`car_id`,`payment_date`),
  KEY `transactions_purpose_index` (`purpose`),
  CONSTRAINT `transactions_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `car_files`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `car_files` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `car_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('image','video','document') NOT NULL DEFAULT 'image',
  `category` enum('auction','port','terminal') NOT NULL DEFAULT 'auction',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `car_files_car_id_foreign` (`car_id`),
  KEY `car_files_car_id_category_index` (`car_id`,`category`),
  KEY `car_files_file_type_index` (`file_type`),
  CONSTRAINT `car_files_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `car_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_car_id_foreign` (`car_id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sms_templates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sms_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_key` varchar(50) NOT NULL,
  `template_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sms_templates_status_key_unique` (`status_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sms_logs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sms_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
  `response` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_logs_phone_sent_at_index` (`phone`,`sent_at`),
  KEY `sms_logs_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `action_logs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `action_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `model_type` varchar(100) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `action_logs_user_id_foreign` (`user_id`),
  KEY `action_logs_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `action_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `action_logs_action_index` (`action`),
  CONSTRAINT `action_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert initial data
-- --------------------------------------------------------

-- IMPORTANT: Admin user should be created via Laravel seeders for proper password hashing
-- Run: php artisan db:seed
-- 
-- If you need to create admin user directly via SQL, uncomment and use the hash below:
-- Password: admin123
-- INSERT INTO `users` (`username`, `full_name`, `phone`, `password`, `role`, `balance`, `sms_enabled`, `created_at`, `updated_at`) VALUES
-- ('admin', 'Administrator', '', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00, 1, NOW(), NOW());
--
-- To generate a new password hash, use Laravel's Hash::make('your_password')

-- Insert SMS templates
INSERT INTO `sms_templates` (`status_key`, `template_text`, `created_at`, `updated_at`) VALUES
('purchased', 'თქვენი [მანქანა] [წელი] წარმატებით შეძენილია აუქციონზე! VIN: [ვინ], ლოტი: [ლოტი]', NOW(), NOW()),
('warehouse', 'თქვენი [მანქანა] მივიდა საწყობში და ემზადება ტრანსპორტირებისთვის. VIN: [ვინ]', NOW(), NOW()),
('loaded', 'თქვენი [მანქანა] ჩაიტვირთა კონტეინერში [კონტეინერი]. VIN: [ვინ]', NOW(), NOW()),
('on_way', 'თქვენი [მანქანა] გზაშია საქართველოსკენ! კონტეინერი: [კონტეინერი]. VIN: [ვინ]', NOW(), NOW()),
('poti', 'თქვენი [მანქანა] ჩამოვიდა ფოთის პორტში! VIN: [ვინ]', NOW(), NOW()),
('green', 'თქვენი [მანქანა] გავიდა განბაჟებაზე და მზად არის გასაყვანად! VIN: [ვინ]', NOW(), NOW()),
('delivered', 'თქვენი [მანქანა] გაყვანილია! გმადლობთ ნდობისთვის! VIN: [ვინ]', NOW(), NOW());

-- Insert migration records
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000001_create_users_table', 1),
('0001_01_01_000002_create_cars_table', 1),
('0001_01_01_000003_create_transactions_table', 1),
('0001_01_01_000004_create_car_files_table', 1),
('0001_01_01_000005_create_notifications_table', 1),
('0001_01_01_000006_create_sms_tables', 1),
('0001_01_01_000007_create_action_logs_table', 1);

COMMIT;

