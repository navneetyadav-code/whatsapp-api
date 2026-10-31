-- 014_whatsapp_reachout_timelock.sql
-- Adds WhatsApp Reachout-Timelock detection columns to track account-level cold outreach restrictions

ALTER TABLE `whatsapp_sessions` ADD COLUMN `reachout_timelock_active` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `whatsapp_sessions` ADD COLUMN `reachout_timelock_ends_at` timestamp NULL DEFAULT NULL;
ALTER TABLE `whatsapp_sessions` ADD COLUMN `reachout_timelock_type` varchar(64) DEFAULT NULL;
