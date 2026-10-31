-- Migration: 013_ai_user_memory.sql
-- Description: Dual-Tier Cognitive Memory: Persistent Fact Notebook & Profile Scratchpad

CREATE TABLE IF NOT EXISTS `ai_user_memory` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `conversation_id` BIGINT UNSIGNED NULL,
  `memory_type` ENUM('profile', 'fact', 'preference', 'entity', 'rule') NOT NULL DEFAULT 'fact',
  `memory_key` VARCHAR(64) NOT NULL,
  `memory_value` TEXT NOT NULL,
  `confidence_score` DECIMAL(3,2) NOT NULL DEFAULT 1.00,
  `source_context` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_mem_key` (`user_id`, `memory_key`),
  KEY `idx_user_type` (`user_id`, `memory_type`),
  CONSTRAINT `fk_ai_user_memory_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
