-- ============================================================================
-- Migration 010: Universal Custom Tools & Multi-Tenant API Engine
-- ============================================================================
-- Adds the ai_custom_tools table allowing every user account to define
-- unlimited custom REST APIs, dual-environment shared hosting APIs,
-- outbound webhooks, and native built-in utility tools.
-- ============================================================================

USE `whatsapp_api`;

CREATE TABLE IF NOT EXISTS `ai_custom_tools` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `tool_slug` VARCHAR(64) NOT NULL COMMENT 'Unique identifier used by Gemini e.g. get_weather',
  `display_name` VARCHAR(128) NOT NULL COMMENT 'Human-readable title e.g. Real-Time Weather Forecaster',
  `category` ENUM('rest_api', 'shared_env_api', 'webhook', 'builtin') NOT NULL DEFAULT 'rest_api',
  `description` TEXT NOT NULL COMMENT 'Prompt instructions explaining to the AI when and how to call this tool',
  `parameters_schema` JSON NOT NULL COMMENT 'Gemini FunctionDeclaration parameters schema',
  `execution_config` JSON NOT NULL COMMENT 'Target URL, HTTP method, headers, tokens, and body templates',
  `rbac_roles` JSON NOT NULL COMMENT 'Allowed caller roles e.g. ["guest", "customer", "admin"]',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_tool` (`user_id`, `tool_slug`),
  KEY `idx_user_active` (`user_id`, `is_active`),
  CONSTRAINT `fk_custom_tools_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
