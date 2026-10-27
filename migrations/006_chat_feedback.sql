-- Migration 006: Chat Feedback & Conversation Lifecycle
-- Adds status and awaiting_feedback flag to chat_conversations
-- Creates chat_feedback table for 5-star customer ratings and reviews

ALTER TABLE `chat_conversations` ADD COLUMN `status` enum('open', 'closed') NOT NULL DEFAULT 'open' AFTER `is_pinned`;
ALTER TABLE `chat_conversations` ADD COLUMN `closed_at` timestamp NULL DEFAULT NULL AFTER `status`;
ALTER TABLE `chat_conversations` ADD COLUMN `awaiting_feedback` tinyint(1) NOT NULL DEFAULT 0 AFTER `closed_at`;

CREATE TABLE IF NOT EXISTS `chat_feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned DEFAULT NULL,
  `phone` varchar(80) NOT NULL,
  `contact_name` varchar(120) DEFAULT NULL,
  `rating` tinyint(1) unsigned NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_feedback_user_created` (`user_id`, `created_at`),
  KEY `idx_feedback_phone` (`user_id`, `phone`),
  KEY `idx_feedback_conv` (`conversation_id`),
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_conv` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
