-- Migration 002: Inbound Webhooks, Message Status Tracking, and Configurable Session Auto-Sleep

CREATE TABLE IF NOT EXISTS `inbound_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `remote_jid` varchar(80) NOT NULL,
  `message_id` varchar(120) NOT NULL,
  `message_type` varchar(40) NOT NULL DEFAULT 'text',
  `sender_name` varchar(120) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `message_timestamp` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_inbound_user_message` (`user_id`, `message_id`),
  KEY `idx_inbound_user_created` (`user_id`, `created_at`),
  KEY `idx_inbound_remote_jid` (`user_id`, `remote_jid`),
  CONSTRAINT `fk_inbound_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` varchar(64) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `webhook_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(80) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` enum('queued','processing','delivered','failed','dead_letter') NOT NULL DEFAULT 'queued',
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `max_attempts` int(10) unsigned NOT NULL DEFAULT 5,
  `available_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` varchar(80) DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_webhook_queue_event_id` (`event_id`),
  KEY `idx_webhook_queue_worker` (`status`, `available_at`, `locked_at`),
  KEY `idx_webhook_queue_user` (`user_id`, `created_at`),
  CONSTRAINT `fk_webhook_queue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_webhook_queue_webhook` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
