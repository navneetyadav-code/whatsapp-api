-- Migration 007: Enterprise AI Agent, Zero-Cloud RAG Knowledge Base & Human Escalation
-- Extends conversations with escalation status and adds AI Agent configuration, knowledge chunks, and escalation logs.

ALTER TABLE `chat_conversations`
  MODIFY COLUMN `status` enum('open', 'escalated', 'closed') NOT NULL DEFAULT 'open',
  ADD COLUMN `escalated_at` timestamp NULL DEFAULT NULL AFTER `status`,
  ADD COLUMN `escalation_reason` varchar(191) DEFAULT NULL AFTER `escalated_at`,
  ADD COLUMN `consecutive_bot_replies` int(10) unsigned NOT NULL DEFAULT 0 AFTER `escalation_reason`;

CREATE TABLE IF NOT EXISTS `ai_agent_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `provider_priority` enum('gemini', 'groq', 'openrouter') NOT NULL DEFAULT 'gemini',
  `gemini_api_key` text DEFAULT NULL,
  `groq_api_key` text DEFAULT NULL,
  `openrouter_api_key` text DEFAULT NULL,
  `gemini_model` varchar(100) NOT NULL DEFAULT 'gemini-1.5-flash',
  `groq_model` varchar(100) NOT NULL DEFAULT 'llama-3.3-70b-versatile',
  `openrouter_model` varchar(100) NOT NULL DEFAULT 'meta-llama/llama-3.3-70b-instruct:free',
  `system_prompt` text DEFAULT NULL,
  `temperature` decimal(3,2) NOT NULL DEFAULT 0.40,
  `max_tokens` int(10) unsigned NOT NULL DEFAULT 500,
  `auto_reply_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `frustration_threshold` decimal(3,2) NOT NULL DEFAULT 0.65,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_agent_user` (`user_id`),
  CONSTRAINT `fk_ai_agent_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_knowledge_chunks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `embedding` longtext DEFAULT NULL,
  `embedding_model` varchar(100) DEFAULT 'local-tfidf',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_knowledge_user_active` (`user_id`, `is_active`),
  CONSTRAINT `fk_knowledge_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_escalations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `reason_code` varchar(60) NOT NULL,
  `trigger_text` text DEFAULT NULL,
  `status` enum('active', 'acknowledged', 'resolved') NOT NULL DEFAULT 'active',
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_escalations_user_status` (`user_id`, `status`),
  KEY `idx_escalations_conv` (`conversation_id`),
  CONSTRAINT `fk_escalations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_escalations_conv` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
