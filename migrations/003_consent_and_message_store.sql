-- Migration 003: Consent Suppression, Opt-Out Tracking, and Baileys Message Recovery Store

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
