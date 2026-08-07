-- Well Fare English Spoken - Phase 147 existing-database migration
-- Run this once on an existing Phase 146 database before using the new
-- Student Account Manager. The statements are idempotent on MariaDB and
-- MySQL versions that support ADD COLUMN IF NOT EXISTS.

ALTER TABLE `students`
    ADD COLUMN IF NOT EXISTS `auth_version` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `password_hash`;

ALTER TABLE `students`
    ADD COLUMN IF NOT EXISTS `password_changed_at` DATETIME NULL AFTER `auth_version`;

CREATE TABLE IF NOT EXISTS `student_account_events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` INT UNSIGNED NOT NULL,
    `admin_id` INT UNSIGNED NULL,
    `event_type` VARCHAR(60) NOT NULL,
    `event_title` VARCHAR(180) NOT NULL,
    `event_note` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_student_account_event` (`student_id`,`created_at`),
    KEY `idx_student_account_type` (`event_type`,`created_at`),
    KEY `idx_student_account_admin` (`admin_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
