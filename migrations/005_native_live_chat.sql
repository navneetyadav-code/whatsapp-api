-- Migration 005: Native WhatsApp Live Chat Engine
-- Adds conversation summary and unified message history tables

CREATE TABLE IF NOT EXISTS `chat_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(80) NOT NULL,
  `contact_name` varchar(120) DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unread_count` int(10) unsigned NOT NULL DEFAULT 0,
  `bot_paused` tinyint(1) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_conv_user_phone` (`user_id`, `phone`),
  KEY `idx_chat_conv_user_last_msg` (`user_id`, `last_message_at`),
  CONSTRAINT `fk_chat_conv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_type` enum('contact', 'agent', 'bot') NOT NULL DEFAULT 'contact',
  `sender_name` varchar(120) DEFAULT NULL,
  `message_type` enum('text', 'image', 'document', 'audio', 'video') NOT NULL DEFAULT 'text',
  `body` text DEFAULT NULL,
  `media_url` varchar(500) DEFAULT NULL,
  `wa_message_id` varchar(120) DEFAULT NULL,
  `status` enum('sent', 'delivered', 'read', 'failed') NOT NULL DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_messages_conv_created` (`conversation_id`, `created_at`),
  KEY `idx_chat_messages_user_created` (`user_id`, `created_at`),
  KEY `idx_chat_messages_wa_id` (`wa_message_id`),
  CONSTRAINT `fk_chat_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_messages_conv` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
