-- Migration 009: Autonomous Tools & Function Calling Engine with RBAC
-- Adds tools_config to ai_agent_settings and creates ai_tool_invocations audit table.

ALTER TABLE `ai_agent_settings`
  ADD COLUMN `tools_config` LONGTEXT DEFAULT NULL AFTER `frustration_threshold`;

CREATE TABLE IF NOT EXISTS `ai_tool_invocations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned DEFAULT NULL,
  `caller_phone` varchar(50) NOT NULL,
  `caller_role` enum('guest', 'customer', 'admin') NOT NULL DEFAULT 'guest',
  `tool_name` varchar(100) NOT NULL,
  `tool_arguments` longtext DEFAULT NULL,
  `tool_result` longtext DEFAULT NULL,
  `execution_time_ms` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('success', 'error', 'denied') NOT NULL DEFAULT 'success',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tool_invocations_user_tool` (`user_id`, `tool_name`),
  KEY `idx_tool_invocations_caller` (`caller_phone`),
  KEY `idx_tool_invocations_created` (`created_at`),
  CONSTRAINT `fk_tool_invocations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
