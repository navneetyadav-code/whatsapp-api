-- Initial application schema, extracted from the repository canonical database.sql.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS only; no destructive statements or seed data.

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `api_key_hash` char(64) DEFAULT NULL,
  `api_key_prefix` varchar(32) DEFAULT NULL,
  `api_secret_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL DEFAULT 'Default key',
  `key_hash` char(64) NOT NULL,
  `key_prefix` varchar(32) NOT NULL,
  `secret_hash` varchar(255) NOT NULL,
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `ip_allowlist` text DEFAULT NULL,
  `rate_per_minute` int(10) unsigned NOT NULL DEFAULT 20,
  `rate_per_hour` int(10) unsigned NOT NULL DEFAULT 300,
  `rate_per_day` int(10) unsigned NOT NULL DEFAULT 1000,
  `allow_humanize` tinyint(1) NOT NULL DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_hash` (`key_hash`),
  KEY `idx_api_keys_user_status` (`user_id`,`status`),
  CONSTRAINT `fk_api_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `secret` varchar(120) DEFAULT NULL,
  `secret_alt` varchar(120) DEFAULT NULL,
  `secret_rotated_at` timestamp NULL DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_webhooks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(160) NOT NULL,
  `message_template` text NOT NULL,
  `target_count` int(10) unsigned NOT NULL DEFAULT 0,
  `queued_count` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('queued','completed','cancelled') NOT NULL DEFAULT 'queued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_campaigns_user_created` (`user_id`,`created_at`),
  CONSTRAINT `fk_campaigns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chatbot_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `match_type` enum('exact','contains','starts_with','catch_all') DEFAULT 'exact',
  `reply_text` text NOT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `chatbot_rules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(80) NOT NULL,
  `normalized_phone` varchar(80) DEFAULT NULL,
  `opt_in` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contacts_user_phone` (`user_id`,`phone`),
  KEY `idx_contacts_user` (`user_id`),
  CONSTRAINT `fk_contacts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_email_ip_time` (`email`,`ip_address`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `message_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `recipient` varchar(80) NOT NULL,
  `message_preview` varchar(255) NOT NULL,
  `status` enum('queued','sent','delivered','read','failed') NOT NULL DEFAULT 'queued',
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_logs_user_created` (`user_id`,`created_at`),
  CONSTRAINT `fk_message_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` enum('idle','connecting','qr','connected','disconnected','error') NOT NULL DEFAULT 'idle',
  `sleeping` tinyint(1) NOT NULL DEFAULT 0,
  `auto_sleep_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `auto_sleep_timeout_seconds` int(10) unsigned NOT NULL DEFAULT 60,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `push_name` varchar(120) DEFAULT NULL,
  `qr_updated_at` timestamp NULL DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_whatsapp_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_request_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `api_key_id` bigint(20) unsigned DEFAULT NULL,
  `endpoint` varchar(120) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `status_code` int(10) unsigned NOT NULL,
  `request_id` varchar(64) NOT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_api_request_logs_user_created` (`user_id`,`created_at`),
  KEY `idx_api_request_logs_key_created` (`api_key_id`,`created_at`),
  CONSTRAINT `fk_api_request_logs_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_api_request_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `webhook_id` bigint(20) unsigned DEFAULT NULL,
  `message_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(80) NOT NULL,
  `target_url` varchar(500) NOT NULL,
  `status_code` int(10) unsigned DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `attempt` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_webhook_deliveries_webhook` (`webhook_id`),
  KEY `idx_webhook_deliveries_user_created` (`user_id`,`created_at`),
  KEY `idx_webhook_deliveries_message` (`message_id`),
  CONSTRAINT `fk_webhook_deliveries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_webhook_deliveries_webhook` FOREIGN KEY (`webhook_id`) REFERENCES `webhooks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `message_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `api_key_id` bigint(20) unsigned DEFAULT NULL,
  `recipient` varchar(80) NOT NULL,
  `normalized_recipient` varchar(80) DEFAULT NULL,
  `message_type` enum('text','image','document','video') NOT NULL DEFAULT 'text',
  `body` text NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `status` enum('queued','processing','sent','delivered','read','failed','cancelled') NOT NULL DEFAULT 'queued',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `max_attempts` int(10) unsigned NOT NULL DEFAULT 3,
  `available_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` varchar(80) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `provider_message_id` varchar(120) DEFAULT NULL,
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_queue_worker` (`status`,`available_at`,`locked_at`),
  KEY `idx_message_queue_worker_priority` (`status`,`priority`,`available_at`,`locked_at`),
  KEY `idx_message_queue_user_created` (`user_id`,`created_at`),
  KEY `idx_message_queue_api_key_created` (`api_key_id`,`created_at`),
  KEY `idx_message_queue_provider_msg` (`provider_message_id`),
  CONSTRAINT `fk_message_queue_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_message_queue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `user_security` (
  `user_id` bigint(20) unsigned NOT NULL,
  `totp_secret` varchar(128) DEFAULT NULL,
  `totp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `google_linked` tinyint(1) NOT NULL DEFAULT 0,
  `google_id` varchar(190) DEFAULT NULL,
  `google_email` varchar(190) DEFAULT NULL,
  `password_set` tinyint(1) NOT NULL DEFAULT 1,
  `backup_codes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_security_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media_assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `storage_key` varchar(500) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'upload',
  `reference_count` int(10) unsigned NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_media_assets_user` (`user_id`),
  KEY `idx_media_assets_storage_key` (`storage_key`(191)),
  KEY `idx_media_assets_expires` (`expires_at`),
  CONSTRAINT `fk_media_assets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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


CREATE TABLE IF NOT EXISTS `recipient_suppressions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(80) NOT NULL,
  `normalized_phone` varchar(80) NOT NULL,
  `reason` varchar(60) NOT NULL DEFAULT 'opt_out',
  `source` varchar(60) NOT NULL DEFAULT 'inbound_keyword',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_suppression_user_phone` (`user_id`, `normalized_phone`),
  KEY `idx_suppression_user_created` (`user_id`, `created_at`),
  CONSTRAINT `fk_recipient_suppressions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `baileys_message_store` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `remote_jid` varchar(80) NOT NULL,
  `message_id` varchar(120) NOT NULL,
  `from_me` tinyint(1) NOT NULL DEFAULT 1,
  `participant` varchar(80) DEFAULT NULL,
  `message_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`message_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_baileys_store_user_msg` (`user_id`, `remote_jid`, `message_id`),
  KEY `idx_baileys_store_created` (`created_at`),
  CONSTRAINT `fk_baileys_message_store_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
  `status` enum('open', 'closed') NOT NULL DEFAULT 'open',
  `closed_at` timestamp NULL DEFAULT NULL,
  `awaiting_feedback` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_conv_user_phone` (`user_id`, `phone`),
  KEY `idx_chat_conv_user_last_msg` (`user_id`, `last_message_at`),
  KEY `idx_chat_conv_status` (`user_id`, `status`),
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
