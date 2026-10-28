-- Migration 008: Chat Sessions Lifecycle, Auto-Resolution & Token Optimization
-- Extends conversations and messages with session_uuid, resolution remarks, and auto-close flags.

ALTER TABLE `chat_conversations`
  ADD COLUMN `session_uuid` varchar(64) NULL AFTER `phone`,
  ADD COLUMN `resolution_remark` text NULL AFTER `escalation_reason`,
  ADD COLUMN `is_auto_closed` tinyint(1) NOT NULL DEFAULT 0 AFTER `awaiting_feedback`;

ALTER TABLE `chat_messages`
  ADD COLUMN `session_uuid` varchar(64) NULL AFTER `conversation_id`;

ALTER TABLE `chat_conversations` ADD INDEX `idx_conv_session_uuid` (`session_uuid`);
ALTER TABLE `chat_messages` ADD INDEX `idx_msg_session_uuid` (`session_uuid`);
