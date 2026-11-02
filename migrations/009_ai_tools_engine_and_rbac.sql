-- Migration 009: AI Custom Tools Engine, Role-Based Invocation & Safety Protocol
-- Adds tools configuration and tool execution auditing tables

ALTER TABLE `ai_agent_settings`
  ADD COLUMN `tools_config` LONGTEXT DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `ai_tool_invocations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned DEFAULT NULL,
  `caller_phone` varchar(50) NOT NULL,
  `caller_role` enum('guest', 'customer', 'admin') NOT NULL DEFAULT 'guest',
  `tool_name` varchar(100) NOT NULL,
  `tool_arguments` longtext DEFAULT NULL,
  `tool_result` longtext DEFAULT NULL,
  `status` enum('success', 'failed', 'blocked') NOT NULL DEFAULT 'success',
  `execution_time_ms` int(10) unsigned NOT NULL DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tool_inv_user` (`user_id`, `created_at`),
  KEY `idx_tool_inv_conv` (`conversation_id`),
  KEY `idx_tool_inv_caller` (`caller_phone`),
  CONSTRAINT `fk_tool_inv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
