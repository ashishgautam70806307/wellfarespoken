-- Phase 182: Upcoming Test Live Students + safe one-time Reopen Access
-- Idempotent on MySQL versions supporting ADD COLUMN IF NOT EXISTS.
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `reopen_count` INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `reopened_at` DATETIME NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `reopened_by_admin_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `reopen_reason` VARCHAR(255) NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `reopen_time_mode` VARCHAR(30) NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `reopen_seconds_granted` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `first_submitted_at` DATETIME NULL;
