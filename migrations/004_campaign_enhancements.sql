-- Migration 004: Campaign Lifecycle, Scheduling, and Status Enum Enhancements
ALTER TABLE `campaigns` MODIFY COLUMN `status` enum('queued','paused','completed','cancelled') NOT NULL DEFAULT 'queued';
ALTER TABLE `campaigns` ADD COLUMN `scheduled_at` timestamp NULL DEFAULT NULL;
