-- -------------------------------------------------------------
-- Migration 018: AI Tool Action Audit Log
-- Provides token-efficient conversation action audit trail
-- -------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `ai_action_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `conversation_id` BIGINT UNSIGNED NULL,
  `session_uuid` VARCHAR(64) NULL,
  `tool_slug` VARCHAR(64) NOT NULL,
  `input_payload` JSON NULL,
  `output_payload` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user_conv` (`user_id`, `conversation_id`),
  KEY `idx_audit_session` (`session_uuid`),
  CONSTRAINT `fk_action_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
