-- Bảng để lưu remember tokens
CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `token` (`token`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng để lưu log hoạt động của user
CREATE TABLE IF NOT EXISTS `user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text, 
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng để lưu password reset tokens
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `token` (`token`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cập nhật bảng users nếu chưa có các trường cần thiết
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `last_login` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `login_attempts` int(11) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `locked_until` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `email_verified_at` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `avatar` varchar(255) DEFAULT NULL;

-- Index cho hiệu suất
ALTER TABLE `users` 
ADD INDEX IF NOT EXISTS `idx_email` (`email`),
ADD INDEX IF NOT EXISTS `idx_username` (`username`),
ADD INDEX IF NOT EXISTS `idx_status` (`status`),
ADD INDEX IF NOT EXISTS `idx_last_login` (`last_login`);
