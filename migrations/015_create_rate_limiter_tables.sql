-- Migration to create tables required by the RateLimiter utility.

CREATE TABLE IF NOT EXISTS `rate_limit_rules` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `action` VARCHAR(50) NOT NULL UNIQUE,
    `limit_count` INT NOT NULL DEFAULT 3,
    `window_seconds` INT NOT NULL DEFAULT 600, -- 10 minutes
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `rate_limit_tracker` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NULL,
    `ip_address` VARCHAR(45) NULL,
    `action` VARCHAR(50) NOT NULL,
    `attempts` INT NOT NULL DEFAULT 1,
    `window_start` DATETIME NOT NULL,
    `window_end` DATETIME NOT NULL,
    `last_attempt` DATETIME NOT NULL,
    `status` ENUM('active', 'blocked') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_action` (`user_id`, `action`),
    INDEX `idx_ip_action` (`ip_address`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default global rule to prevent system-wide abuse
INSERT INTO `rate_limit_rules` (`action`, `limit_count`, `window_seconds`)
VALUES ('global', 100, 3600)
ON DUPLICATE KEY UPDATE `action` = 'global';
