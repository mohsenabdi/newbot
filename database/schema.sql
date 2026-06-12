-- ============================================
-- DATABASE SCHEMA - ربات تلگرامی آپلودر فایل‌ها
-- ============================================
-- تمام جداول لازم برای عملکرد ربات

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET CHARACTER SET utf8mb4;

-- ============================================
-- جدول تنظیمات ربات
-- ============================================
CREATE TABLE IF NOT EXISTS `bot_settings` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT,
  `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
  `description` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول کاربران
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `telegram_id` BIGINT NOT NULL UNIQUE,
  `username` VARCHAR(100),
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `phone` VARCHAR(20),
  `is_premium` BOOLEAN DEFAULT FALSE,
  `premium_expires_at` DATETIME,
  `downloads_count` INT DEFAULT 0,
  `free_downloads_remaining` INT DEFAULT 0,
  `total_uploaded` BIGINT DEFAULT 0,
  `is_blocked` BOOLEAN DEFAULT FALSE,
  `join_required_checked` BOOLEAN DEFAULT FALSE,
  `access_level` ENUM('user', 'admin_content', 'admin_users', 'admin_payments', 'admin_full') DEFAULT 'user',
  `subscription_id` INT,
  `last_activity` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_telegram_id` (`telegram_id`),
  INDEX `idx_is_premium` (`is_premium`),
  INDEX `idx_access_level` (`access_level`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول رسانه‌ها
-- ============================================
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `media_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `folder_id` INT,
  `telegram_file_id` VARCHAR(255) NOT NULL,
  `file_type` ENUM('photo', 'video', 'audio', 'document', 'animation', 'sticker', 'video_note', 'voice', 'album') NOT NULL,
  `file_name` VARCHAR(255),
  `file_size` BIGINT,
  `caption` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `thumbnail_text` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `password` VARCHAR(100),
  `download_limit` INT DEFAULT 0,
  `download_count` INT DEFAULT 0,
  `likes_fake` INT DEFAULT 0,
  `downloads_fake` INT DEFAULT 0,
  `force_like` BOOLEAN DEFAULT FALSE,
  `force_reaction` BOOLEAN DEFAULT FALSE,
  `is_verified` BOOLEAN DEFAULT FALSE,
  `is_public` BOOLEAN DEFAULT TRUE,
  `custom_link` VARCHAR(100),
  `mime_type` VARCHAR(100),
  `duration` INT,
  `width` INT,
  `height` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL,
  INDEX `idx_media_code` (`media_code`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_folder_id` (`folder_id`),
  INDEX `idx_file_type` (`file_type`),
  INDEX `idx_custom_link` (`custom_link`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول پوشه‌ها
-- ============================================
CREATE TABLE IF NOT EXISTS `folders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `parent_id` INT,
  `name` VARCHAR(255) NOT NULL CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_public` BOOLEAN DEFAULT TRUE,
  `order_index` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول مدیران
-- ============================================
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL UNIQUE,
  `role` ENUM('admin_full', 'admin_content', 'admin_users', 'admin_payments') NOT NULL,
  `permissions` JSON,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول کانال‌های جوین اجباری
-- ============================================
CREATE TABLE IF NOT EXISTS `join_required_channels` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `channel_identifier` VARCHAR(100) NOT NULL UNIQUE,
  `channel_type` ENUM('channel', 'group', 'supergroup') DEFAULT 'channel',
  `channel_name` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` BOOLEAN DEFAULT TRUE,
  `check_status` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_channel_identifier` (`channel_identifier`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول پلن‌های اشتراک
-- ============================================
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` DECIMAL(10, 2) NOT NULL,
  `duration_days` INT NOT NULL,
  `download_limit` INT,
  `storage_limit` BIGINT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `order_index` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول اشتراک‌های کاربران
-- ============================================
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `subscription_id` INT NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
  `payment_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_subscription_id` (`subscription_id`),
  INDEX `idx_end_date` (`end_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول روش‌های پرداخت
-- ============================================
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `method_name` VARCHAR(50) NOT NULL UNIQUE,
  `method_title` VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` BOOLEAN DEFAULT FALSE,
  `config` JSON,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_method_name` (`method_name`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول تراکنش‌های پرداخت
-- ============================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `subscription_id` INT,
  `amount` DECIMAL(10, 2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'IRR',
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(255) UNIQUE,
  `reference_id` VARCHAR(255),
  `status` ENUM('pending', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
  `description` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_transaction_id` (`transaction_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول گزارش‌های کاربران
-- ============================================
CREATE TABLE IF NOT EXISTS `reports` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `media_id` INT,
  `report_type` ENUM('spam', 'inappropriate', 'copyright', 'other') DEFAULT 'other',
  `description` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` ENUM('pending', 'reviewed', 'resolved', 'rejected') DEFAULT 'pending',
  `admin_notes` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`media_id`) REFERENCES `media`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول لاگ‌های سیستم
-- ============================================
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT,
  `action` VARCHAR(100),
  `action_type` ENUM('create', 'read', 'update', 'delete', 'error', 'info') DEFAULT 'info',
  `description` TEXT CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_action_type` (`action_type`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول حذف خودکار پیام‌ها
-- ============================================
CREATE TABLE IF NOT EXISTS `pending_deletes` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `chat_id` BIGINT NOT NULL,
  `message_id` INT NOT NULL,
  `delete_at` DATETIME NOT NULL,
  `is_processed` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_delete_at` (`delete_at`),
  INDEX `idx_is_processed` (`is_processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول جلسات کاربران
-- ============================================
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `session_data` JSON,
  `current_state` VARCHAR(100),
  `current_step` INT DEFAULT 0,
  `temp_data` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول پیغام‌های Pending
-- ============================================
CREATE TABLE IF NOT EXISTS `pending_messages` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT,
  `chat_id` BIGINT,
  `message_type` VARCHAR(50),
  `message_data` JSON,
  `retry_count` INT DEFAULT 0,
  `max_retries` INT DEFAULT 3,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_retry_count` (`retry_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول بکاپ‌ها
-- ============================================
CREATE TABLE IF NOT EXISTS `backups` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `backup_name` VARCHAR(100) NOT NULL,
  `backup_file` VARCHAR(255),
  `backup_size` BIGINT,
  `backup_type` ENUM('full', 'incremental') DEFAULT 'full',
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  `telegram_message_id` INT,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME,
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- درج تنظیمات پیش‌فرض
-- ============================================
INSERT IGNORE INTO `bot_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('bot_is_active', 'true', 'boolean', 'وضعیت فعال/غیرفعال ربات'),
('welcome_message', 'خوش‌آمدید به ربات آپلودر فایل‌ها', 'string', 'پیام خوشامدگویی'),
('admin_signature', 'ربات تلگرام آپلودر', 'string', 'امضای انتهای پیام‌ها'),
('free_downloads_limit', '5', 'number', 'تعداد دانلود رایگان'),
('delete_message_delay', '30', 'number', 'تأخیر حذف پیام (ثانیه)'),
('broadcast_delay', '1', 'number', 'تأخیر بین پیام‌های ارسال همگانی'),
('spam_limit_seconds', '2', 'number', 'محدودیت اسپم (ثانیه)'),
('upload_enabled', 'true', 'boolean', 'فعال/غیرفعال آپلود'),
('is_subscription_mode', 'false', 'boolean', 'استفاده از حالت اشتراکی'),
('force_join_channels', '[]', 'json', 'کانال‌های جوین اجباری'),
('forward_lock_enabled', 'true', 'boolean', 'فعال/غیرفعال قفل فروارد'),
('anti_filter_enabled', 'true', 'boolean', 'فعال/غیرفعال قفل ضد فیلتر'),
('report_button_enabled', 'true', 'boolean', 'فعال/غیرفعال دکمه گزارش'),
('like_button_enabled', 'true', 'boolean', 'فعال/غیرفعال دکمه لایک'),
('popular_button_enabled', 'true', 'boolean', 'فعال/غیرفعال دکمه پربازدیدترین‌ها'),
('support_button_enabled', 'true', 'boolean', 'فعال/غیرفعال دکمه پشتیبانی'),
('support_username', '', 'string', 'نام کاربری پشتیبانی'),
('support_link', '', 'string', 'لینک پشتیبانی');

INSERT IGNORE INTO `payment_methods` (`method_name`, `method_title`, `is_active`, `config`) VALUES
('zarinpal', 'زرین‌پال', false, '{}'),
('zibal', 'زیبال', false, '{}'),
('card_transfer', 'کارت به کارت', false, '{}'),
('crypto_ton', 'TON', false, '{}'),
('crypto_tron', 'TRON', false, '{}'),
('telegram_stars', 'استارز تلگرام', false, '{}');
