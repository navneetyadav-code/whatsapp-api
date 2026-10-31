-- ============================================================
-- Migration 011: Personal AI Assistant Mode & 24/7 Engine
-- Adds agent_mode, feedback_enabled, is_always_on, and owner_name
-- to ai_agent_settings. Enables 1-click conversion between
-- Business Support Bot and Personal Executive Assistant.
-- ============================================================

ALTER TABLE `ai_agent_settings`
  ADD COLUMN `agent_mode` ENUM('support', 'personal') NOT NULL DEFAULT 'support' AFTER `auto_reply_enabled`,
  ADD COLUMN `feedback_enabled` TINYINT(1) NOT NULL DEFAULT 1 AFTER `agent_mode`,
  ADD COLUMN `is_always_on` TINYINT(1) NOT NULL DEFAULT 1 AFTER `feedback_enabled`,
  ADD COLUMN `owner_name` VARCHAR(100) NOT NULL DEFAULT 'Boss' AFTER `is_always_on`;
