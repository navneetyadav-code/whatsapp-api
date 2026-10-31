-- Migration: 012_ai_action_drafts.sql
-- Description: Two-Phase Action Protocol (Human-in-the-Loop Confirmation Storage)

CREATE TABLE IF NOT EXISTS `ai_action_drafts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `draft_code` VARCHAR(32) NOT NULL,
  `action_type` ENUM('send_email', 'crm_update', 'custom_dispatch') NOT NULL DEFAULT 'send_email',
  `recipient` VARCHAR(255) NULL,
  `subject` VARCHAR(255) NULL,
  `body_content` LONGTEXT NOT NULL,
  `metadata` JSON NULL,
  `status` ENUM('pending', 'approved', 'sent', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_draft_code` (`draft_code`),
  KEY `idx_user_status` (`user_id`, `status`),
  KEY `idx_conversation` (`conversation_id`),
  CONSTRAINT `fk_draft_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
