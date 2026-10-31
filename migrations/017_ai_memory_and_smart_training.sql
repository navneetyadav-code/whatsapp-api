-- Migration: 017_ai_memory_and_smart_training.sql
-- Description: Dual-Tier Contact-Scoped AI Memory, Auto-Expiry TTL, and Few-Shot Golden Training & Negative Constraints

-- 1. Upgrade ai_user_memory for Contact-Level Partitioning & Auto-Expiry TTL
ALTER TABLE `ai_user_memory` ADD COLUMN `contact_phone` VARCHAR(30) NOT NULL DEFAULT '' AFTER `user_id`;
ALTER TABLE `ai_user_memory` ADD COLUMN `is_durable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `confidence_score`;
ALTER TABLE `ai_user_memory` ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_durable`;
ALTER TABLE `ai_user_memory` ADD COLUMN `access_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `expires_at`;
ALTER TABLE `ai_user_memory` ADD COLUMN `last_accessed_at` TIMESTAMP NULL DEFAULT NULL AFTER `access_count`;

-- Update unique key to allow contact-scoped facts
ALTER TABLE `ai_user_memory` DROP INDEX `idx_user_mem_key`;
ALTER TABLE `ai_user_memory` ADD UNIQUE KEY `uniq_user_contact_mem_key` (`user_id`, `contact_phone`, `memory_key`);
ALTER TABLE `ai_user_memory` ADD KEY `idx_user_contact_expires` (`user_id`, `contact_phone`, `expires_at`);

-- 2. Add Session Summary cache to chat_conversations
ALTER TABLE `chat_conversations` ADD COLUMN `session_summary` VARCHAR(500) NULL DEFAULT NULL;

-- 3. Create Smart Few-Shot Golden Training Examples & Negative Constraints Table
CREATE TABLE IF NOT EXISTS `ai_training_examples` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `example_type` ENUM('few_shot', 'negative_constraint') NOT NULL DEFAULT 'few_shot',
  `user_prompt` TEXT NOT NULL,
  `assistant_response` TEXT NOT NULL,
  `category` VARCHAR(64) NOT NULL DEFAULT 'general',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_training_user_active` (`user_id`, `is_active`, `example_type`),
  CONSTRAINT `fk_ai_training_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
