USE test;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
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

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `api_key_hash`, `api_key_prefix`, `api_secret_hash`, `created_at`, `updated_at`) VALUES ('1', 'Navneet Kumar Yadav', 'navneetyadav982008@gmail.com', '9507286092', '$2y$10$YFw8Dduqg72lMevXa2t2Nu/15VrBXHTBV9IH4EoYBIWkYqxN2.Z4O', '58901c3bfb7c80d3923f6a2f9705de7a80b751a4e63513ea060eb2768f91163d', 'wa_live_f89b0312c06de1', '$2y$10$9m3OyoMuxsOOnWLNgx9aMuYPtUOppNmHHNSI.G9IO9BIsBT3W1xGK', '2026-06-02 12:46:18', '2026-09-29 23:22:41');
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `api_key_hash`, `api_key_prefix`, `api_secret_hash`, `created_at`, `updated_at`) VALUES ('9', 'Unknown Kumar', 'unknownkumar1113@gmail.com', '', '$2y$10$jlcGR.5IU6sOQIA82aaapuKuL/B0n3Fn6H6EtDfghIctY6p9W8cXy', NULL, NULL, NULL, '2026-10-13 14:27:12', '2026-10-13 14:27:12');

DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE `api_keys` (
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

INSERT INTO `api_keys` (`id`, `user_id`, `name`, `key_hash`, `key_prefix`, `secret_hash`, `status`, `ip_allowlist`, `rate_per_minute`, `rate_per_hour`, `rate_per_day`, `allow_humanize`, `last_used_at`, `created_at`, `updated_at`) VALUES ('16', '1', 'key', '96fab6d2a873b79d5ce60db0ef49bae4d19dec4ca99faefee49fd399fa926e96', 'wa_live_24f504ef8a7990', '$2y$10$DV/ZfFQo73M/FY/pURHgT.xJ7DJ/d8DXCHu.EXBD.btabXYTH169K', 'active', NULL, '20', '300', '1000', '1', '2026-09-29 23:22:12', '2026-09-29 23:18:21', '2026-09-30 11:38:20');
INSERT INTO `api_keys` (`id`, `user_id`, `name`, `key_hash`, `key_prefix`, `secret_hash`, `status`, `ip_allowlist`, `rate_per_minute`, `rate_per_hour`, `rate_per_day`, `allow_humanize`, `last_used_at`, `created_at`, `updated_at`) VALUES ('17', '1', 'N-Key', 'f1e0049a73dbc09bd6772ab2b2ff1bc093791b671d7169e1594c8f856bfa7558', 'wa_live_cb78eb11b575d9', '$2y$10$xMcb9RcLhMMNAdWgOaHPZet/a5qGoZ6Yqr8wv0QohTJFZVHhOpJjW', 'active', '185.27.134.37', '20', '300', '1000', '1', '2026-09-30 17:12:43', '2026-09-30 12:23:52', '2026-09-30 17:12:43');
INSERT INTO `api_keys` (`id`, `user_id`, `name`, `key_hash`, `key_prefix`, `secret_hash`, `status`, `ip_allowlist`, `rate_per_minute`, `rate_per_hour`, `rate_per_day`, `allow_humanize`, `last_used_at`, `created_at`, `updated_at`) VALUES ('18', '9', 'Test Key User 9', 'e6f0d8f46862498d07d98f2deb579632ac15d27069a3c7b37815b48c40771ede', 'wa_live_671e8845f7b671', '$2y$10$ny4BkZsdbCSUsuM3hiYv7.Nm/q0NCUqsvAG6zsjY0vDOfjyEznXNG', 'active', NULL, '20', '300', '1000', '0', '2026-10-13 15:24:24', '2026-10-13 15:17:41', '2026-10-13 15:24:24');

DROP TABLE IF EXISTS `api_request_logs`;
CREATE TABLE `api_request_logs` (
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

DROP TABLE IF EXISTS `campaigns`;
CREATE TABLE `campaigns` (
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

DROP TABLE IF EXISTS `chatbot_rules`;
CREATE TABLE `chatbot_rules` (
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

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
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

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_email_ip_time` (`email`,`ip_address`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `message_logs`;
CREATE TABLE `message_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `recipient` varchar(80) NOT NULL,
  `message_preview` varchar(255) NOT NULL,
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_logs_user_created` (`user_id`,`created_at`),
  CONSTRAINT `fk_message_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `message_queue`;
CREATE TABLE `message_queue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `api_key_id` bigint(20) unsigned DEFAULT NULL,
  `recipient` varchar(80) NOT NULL,
  `normalized_recipient` varchar(80) DEFAULT NULL,
  `message_type` enum('text','image','document','video') NOT NULL DEFAULT 'text',
  `body` text NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `status` enum('queued','processing','sent','failed','cancelled') NOT NULL DEFAULT 'queued',
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `max_attempts` int(10) unsigned NOT NULL DEFAULT 3,
  `available_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `locked_by` varchar(80) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `provider_message_id` varchar(120) DEFAULT NULL,
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_queue_worker` (`status`,`available_at`,`locked_at`),
  KEY `idx_message_queue_user_created` (`user_id`,`created_at`),
  KEY `idx_message_queue_api_key_created` (`api_key_id`,`created_at`),
  CONSTRAINT `fk_message_queue_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_message_queue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(190) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_contacts`;
CREATE TABLE `user_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `country_code` varchar(10) NOT NULL DEFAULT '+91',
  `display_name` varchar(150) NOT NULL,
  `contact_unique_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_contact_unique` (`user_id`,`contact_phone`),
  KEY `idx_contact_unique_id` (`contact_unique_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_contact_phone` (`contact_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `webhooks`;
CREATE TABLE `webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `secret` varchar(120) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_webhooks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `webhook_deliveries`;
CREATE TABLE `webhook_deliveries` (
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

DROP TABLE IF EXISTS `whatsapp_sessions`;
CREATE TABLE `whatsapp_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` enum('idle','connecting','qr','connected','disconnected','error') NOT NULL DEFAULT 'idle',
  `sleeping` tinyint(1) NOT NULL DEFAULT 0,
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

INSERT INTO `whatsapp_sessions` (`id`, `user_id`, `status`, `sleeping`, `phone`, `push_name`, `qr_updated_at`, `connected_at`, `disconnected_at`, `last_error`, `created_at`, `updated_at`) VALUES ('1', '1', 'disconnected', '0', NULL, NULL, '2026-10-13 11:14:52', NULL, '2026-10-13 14:51:02', 'QR refs attempts ended', '2026-06-02 12:46:18', '2026-10-13 14:51:02');
INSERT INTO `whatsapp_sessions` (`id`, `user_id`, `status`, `sleeping`, `phone`, `push_name`, `qr_updated_at`, `connected_at`, `disconnected_at`, `last_error`, `created_at`, `updated_at`) VALUES ('8', '9', 'connected', '0', '917491954139', 'Assist Group', NULL, '2026-10-13 12:41:58', NULL, NULL, '2026-10-13 14:27:12', '2026-10-13 16:11:58');

SET FOREIGN_KEY_CHECKS = 1;
