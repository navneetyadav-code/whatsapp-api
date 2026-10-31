-- Migration 015: User-Level Multi-Key AI Providers & Isolated SMTP Credentials
-- Adds multi-key auto-failover support for Gemini, Groq, OpenRouter and isolated user SMTP servers.

CREATE TABLE IF NOT EXISTS `ai_provider_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `provider` enum('gemini', 'groq', 'openrouter') NOT NULL,
  `label` varchar(100) NOT NULL DEFAULT 'Primary Key',
  `api_key` text NOT NULL,
  `priority` int(10) unsigned NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active', 'rate_limited', 'quota_exceeded', 'invalid', 'error') NOT NULL DEFAULT 'active',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `fail_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_provider_keys_user_priority` (`user_id`, `provider`, `is_active`, `priority`),
  CONSTRAINT `fk_ai_provider_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_smtp_credentials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `label` varchar(100) NOT NULL DEFAULT 'Primary SMTP',
  `host` varchar(191) NOT NULL,
  `port` int(10) unsigned NOT NULL DEFAULT 587,
  `encryption` enum('tls', 'ssl', 'none') NOT NULL DEFAULT 'tls',
  `username` varchar(191) NOT NULL,
  `password` text NOT NULL,
  `from_email` varchar(191) NOT NULL,
  `from_name` varchar(191) NOT NULL DEFAULT 'AI Assistant',
  `priority` int(10) unsigned NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active', 'auth_failed', 'connection_failed', 'error') NOT NULL DEFAULT 'active',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `fail_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_smtp_user_priority` (`user_id`, `is_active`, `priority`),
  CONSTRAINT `fk_user_smtp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing non-empty keys from ai_agent_settings
INSERT INTO `ai_provider_keys` (`user_id`, `provider`, `label`, `api_key`, `priority`, `is_active`, `status`)
SELECT `user_id`, 'gemini', 'Primary Gemini Key', `gemini_api_key`, 1, 1, 'active'
FROM `ai_agent_settings`
WHERE `gemini_api_key` IS NOT NULL AND TRIM(`gemini_api_key`) != '';

INSERT INTO `ai_provider_keys` (`user_id`, `provider`, `label`, `api_key`, `priority`, `is_active`, `status`)
SELECT `user_id`, 'groq', 'Primary Groq Key', `groq_api_key`, 1, 1, 'active'
FROM `ai_agent_settings`
WHERE `groq_api_key` IS NOT NULL AND TRIM(`groq_api_key`) != '';

INSERT INTO `ai_provider_keys` (`user_id`, `provider`, `label`, `api_key`, `priority`, `is_active`, `status`)
SELECT `user_id`, 'openrouter', 'Primary OpenRouter Key', `openrouter_api_key`, 1, 1, 'active'
FROM `ai_agent_settings`
WHERE `openrouter_api_key` IS NOT NULL AND TRIM(`openrouter_api_key`) != '';
