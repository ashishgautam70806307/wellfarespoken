-- Well Fare English Spoken - Complete Unified Database
-- Phase 136 Regression Repair: one-file fresh install + safe existing database upgrade
-- Generated 2026-08-05. Target: MariaDB 10.4+ / XAMPP.
-- IMPORTANT: Create/select your target database in phpMyAdmin first, then import this file.
-- The file intentionally does not force a database name, so it works on localhost and prefixed hosting databases.
-- Always take a backup before importing into an existing database.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================
-- 1. Complete final table structures (40 tables)
-- =============================================================

-- Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`),
  KEY `idx_admin_active` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: site_settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_site_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: courses
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `short_description` TEXT NULL,
  `duration` VARCHAR(80) NULL,
  `level` VARCHAR(80) NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `pay_url` VARCHAR(500) NULL,
  `course_image` VARCHAR(500) NULL,
  `class_time` VARCHAR(160) NULL,
  `class_days` VARCHAR(160) NULL,
  `total_tests` INT NOT NULL DEFAULT 0,
  `lessons_count` INT NOT NULL DEFAULT 0,
  `course_details` TEXT NULL,
  `outcomes` TEXT NULL,
  `includes_text` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_courses_active` (`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: course_variants
CREATE TABLE IF NOT EXISTS `course_variants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `variant_title` VARCHAR(180) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `class_time` VARCHAR(160) NULL,
  `class_days` VARCHAR(160) NULL,
  `total_tests` INT NOT NULL DEFAULT 0,
  `details` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_course_variants_course` (`course_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_name` VARCHAR(120) NOT NULL,
  `message` TEXT NOT NULL,
  `student_image` VARCHAR(500) NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `reviewer_role` VARCHAR(160) NULL,
  `review_date` VARCHAR(80) NULL,
  `source_label` VARCHAR(120) NULL,
  `avatar_initials` VARCHAR(8) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_testimonials_active` (`published`,`sort_order`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: videos
CREATE TABLE IF NOT EXISTS `videos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `youtube_url` VARCHAR(255) NOT NULL,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_videos_active` (`published`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: enquiries
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `course_interest` VARCHAR(160) NULL,
  `current_level` VARCHAR(120) NULL,
  `preferred_batch` VARCHAR(120) NULL,
  `lead_source` VARCHAR(80) NULL,
  `message` TEXT NULL,
  `enquiry_status` VARCHAR(40) NOT NULL DEFAULT 'New',
  `lead_priority` VARCHAR(30) NOT NULL DEFAULT 'Normal',
  `follow_up_date` DATE NULL,
  `last_contacted_at` DATETIME NULL,
  `admin_note` TEXT NULL,
  `ip_address` VARCHAR(80) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enquiries_workflow` (`enquiry_status`,`lead_priority`,`follow_up_date`),
  KEY `idx_enquiries_phone` (`phone`),
  KEY `idx_enquiries_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: faculty_members
CREATE TABLE IF NOT EXISTS `faculty_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `faculty_name` VARCHAR(180) NOT NULL,
  `designation` VARCHAR(180) NULL,
  `experience` VARCHAR(80) NULL,
  `qualification` VARCHAR(255) NULL,
  `short_bio` TEXT NULL,
  `full_bio` TEXT NULL,
  `expertise` TEXT NULL,
  `image_url` VARCHAR(500) NULL,
  `phone` VARCHAR(80) NULL,
  `email` VARCHAR(180) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faculty_pub` (`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: gallery_images
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `category` VARCHAR(100) NULL,
  `image_url` VARCHAR(500) NULL,
  `image_alt` VARCHAR(180) NULL,
  `description` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_active` (`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: faqs
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` VARCHAR(220) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faq_active` (`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: batch_timings
CREATE TABLE IF NOT EXISTS `batch_timings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_name` VARCHAR(160) NOT NULL,
  `course_name` VARCHAR(160) NULL,
  `timing` VARCHAR(120) NULL,
  `days` VARCHAR(120) NULL,
  `seats_note` VARCHAR(160) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_batches_active` (`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: content_blocks
CREATE TABLE IF NOT EXISTS `content_blocks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `block_type` VARCHAR(80) NOT NULL,
  `block_key` VARCHAR(120) NULL,
  `icon` VARCHAR(40) NULL,
  `eyebrow` VARCHAR(160) NULL,
  `title` VARCHAR(220) NOT NULL,
  `subtitle` TEXT NULL,
  `body` TEXT NULL,
  `link_text` VARCHAR(120) NULL,
  `link_url` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blocks_type` (`block_type`,`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: form_options
CREATE TABLE IF NOT EXISTS `form_options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `option_group` VARCHAR(80) NOT NULL,
  `option_label` VARCHAR(160) NOT NULL,
  `option_value` VARCHAR(160) NULL,
  `helper_text` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_options_group` (`option_group`,`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: nav_menus
CREATE TABLE IF NOT EXISTS `nav_menus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_area` VARCHAR(40) NOT NULL DEFAULT 'header',
  `label` VARCHAR(120) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `is_cta` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nav_area` (`menu_area`,`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: hero_banners
CREATE TABLE IF NOT EXISTS `hero_banners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_key` VARCHAR(80) NOT NULL DEFAULT 'home',
  `eyebrow` VARCHAR(160) NULL,
  `title` VARCHAR(220) NOT NULL,
  `subtitle` TEXT NULL,
  `image_url` VARCHAR(500) NULL,
  `desktop_image_url` VARCHAR(500) NULL,
  `mobile_image_url` VARCHAR(500) NULL,
  `image_alt` VARCHAR(180) NULL,
  `show_content` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `content_position` ENUM('left','center','right') NOT NULL DEFAULT 'left',
  `overlay_strength` TINYINT UNSIGNED NOT NULL DEFAULT 58,
  `badge_one` VARCHAR(120) NULL,
  `badge_two` VARCHAR(120) NULL,
  `stat_one_label` VARCHAR(120) NULL,
  `stat_one_value` VARCHAR(120) NULL,
  `stat_two_label` VARCHAR(120) NULL,
  `stat_two_value` VARCHAR(120) NULL,
  `primary_text` VARCHAR(120) NULL,
  `primary_url` VARCHAR(255) NULL,
  `secondary_text` VARCHAR(120) NULL,
  `secondary_url` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hero_page` (`page_key`,`published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: students
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `email` VARCHAR(160) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `auth_version` INT UNSIGNED NOT NULL DEFAULT 1,
  `password_changed_at` DATETIME NULL,
  `current_level` VARCHAR(80) NOT NULL DEFAULT 'Zero Level',
  `target_goal` VARCHAR(180) NULL,
  `preferred_language` VARCHAR(40) NOT NULL DEFAULT 'Hindi',
  `daily_goal_minutes` INT NOT NULL DEFAULT 20,
  `admin_note` TEXT NULL,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_students_phone` (`phone`),
  KEY `idx_students_active` (`published`,`status_deleted`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_account_events
CREATE TABLE IF NOT EXISTS `student_account_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED NULL,
  `event_type` VARCHAR(60) NOT NULL,
  `event_title` VARCHAR(180) NOT NULL,
  `event_note` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_account_event` (`student_id`,`created_at`),
  KEY `idx_student_account_type` (`event_type`,`created_at`),
  KEY `idx_student_account_admin` (`admin_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_activity_logs
CREATE TABLE IF NOT EXISTS `student_activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `activity_type` VARCHAR(80) NOT NULL,
  `activity_title` VARCHAR(180) NULL,
  `score` INT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_student_activity` (`student_id`,`created_at`),
  KEY `idx_student_activity_type` (`activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: admissions
CREATE TABLE IF NOT EXISTS `admissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_photo` VARCHAR(255) NULL,
  `student_name` VARCHAR(180) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `alt_phone` VARCHAR(40) NULL,
  `email` VARCHAR(180) NULL,
  `gender` VARCHAR(30) NULL,
  `dob` DATE NULL,
  `guardian_name` VARCHAR(180) NULL,
  `address` TEXT NULL,
  `course_interest` VARCHAR(180) NULL,
  `batch_preference` VARCHAR(160) NULL,
  `current_level` VARCHAR(120) NULL,
  `source_label` VARCHAR(120) NULL,
  `admission_status` VARCHAR(40) NOT NULL DEFAULT 'New',
  `fee_plan_name` VARCHAR(180) NULL,
  `total_fee` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `payment_status` VARCHAR(40) NOT NULL DEFAULT 'Unpaid',
  `payment_mode` VARCHAR(80) NULL,
  `receipt_no` VARCHAR(120) NULL,
  `admission_date` DATE NULL,
  `due_date` DATE NULL,
  `next_follow_up` DATE NULL,
  `documents_received` TEXT NULL,
  `counselor_name` VARCHAR(160) NULL,
  `admin_note` TEXT NULL,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admissions_status` (`admission_status`,`payment_status`,`status_deleted`),
  KEY `idx_admissions_phone` (`phone`),
  KEY `idx_admissions_date` (`admission_date`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_categories
CREATE TABLE IF NOT EXISTS `practice_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(160) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(40) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_practice_category_slug` (`slug`),
  KEY `idx_practice_cat` (`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_lessons
CREATE TABLE IF NOT EXISTS `practice_lessons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `lesson_title` VARCHAR(180) NOT NULL,
  `lesson_type` VARCHAR(80) NOT NULL DEFAULT 'tense',
  `level` VARCHAR(80) NULL,
  `tense_name` VARCHAR(120) NULL,
  `short_description` TEXT NULL,
  `instructions` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_practice_lessons` (`category_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_questions
CREATE TABLE IF NOT EXISTS `practice_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `lesson_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `question_type` VARCHAR(60) NOT NULL DEFAULT 'fill_blank',
  `question_text` TEXT NOT NULL,
  `option_a` VARCHAR(255) NULL,
  `option_b` VARCHAR(255) NULL,
  `option_c` VARCHAR(255) NULL,
  `option_d` VARCHAR(255) NULL,
  `correct_answer` TEXT NULL,
  `sample_answer` TEXT NULL,
  `explanation` TEXT NULL,
  `tense_name` VARCHAR(120) NULL,
  `level` VARCHAR(80) NULL,
  `accepted_answers` TEXT NULL,
  `answer_match_mode` VARCHAR(40) NOT NULL DEFAULT 'smart',
  `answer_help` TEXT NULL,
  `ai_prompt_hint` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_practice_questions` (`lesson_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_common_mistakes
CREATE TABLE IF NOT EXISTS `practice_common_mistakes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `wrong_pattern` VARCHAR(220) NOT NULL,
  `correct_pattern` VARCHAR(220) NOT NULL,
  `explanation` TEXT NULL,
  `example_sentence` TEXT NULL,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mistakes` (`published`,`status_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_attempts
CREATE TABLE IF NOT EXISTS `practice_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(120) NOT NULL,
  `student_name` VARCHAR(160) NULL,
  `phone` VARCHAR(40) NULL,
  `question_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `user_answer` TEXT NULL,
  `correct_answer` TEXT NULL,
  `score` INT NOT NULL DEFAULT 0,
  `local_feedback` TEXT NULL,
  `suggested_next_step` VARCHAR(220) NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `match_type` VARCHAR(80) NULL,
  `ai_feedback` TEXT NULL,
  `ai_status` VARCHAR(40) NULL,
  `ai_model` VARCHAR(120) NULL,
  `corrected_answer` TEXT NULL,
  `natural_answer` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempt_session` (`session_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_settings
CREATE TABLE IF NOT EXISTS `practice_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_practice_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: practice_ai_logs
CREATE TABLE IF NOT EXISTS `practice_ai_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(120) NOT NULL,
  `question_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `provider` VARCHAR(60) NULL,
  `model` VARCHAR(120) NULL,
  `request_type` VARCHAR(80) NULL,
  `prompt_chars` INT NOT NULL DEFAULT 0,
  `response_chars` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(40) NOT NULL DEFAULT 'skipped',
  `error_message` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_logs_session` (`session_id`,`created_at`),
  KEY `idx_ai_logs_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: material_collections
CREATE TABLE IF NOT EXISTS `material_collections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(180) NOT NULL,
  `slug` VARCHAR(180) NULL,
  `category` VARCHAR(120) NULL,
  `level` VARCHAR(80) NULL,
  `description` TEXT NULL,
  `cover_image` VARCHAR(500) NULL,
  `practice_priority` INT NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material_collection` (`published`,`status_deleted`,`sort_order`),
  KEY `idx_material_collection_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: material_assets
CREATE TABLE IF NOT EXISTS `material_assets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(180) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `original_name` VARCHAR(255) NULL,
  `file_type` VARCHAR(40) NULL,
  `notes` TEXT NULL,
  `practice_priority` INT NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material_assets` (`collection_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: material_units
CREATE TABLE IF NOT EXISTS `material_units` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(180) NOT NULL,
  `unit_type` VARCHAR(80) NOT NULL DEFAULT 'lesson',
  `tense_name` VARCHAR(120) NULL,
  `level` VARCHAR(80) NULL,
  `instructions` TEXT NULL,
  `practice_priority` INT NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material_units` (`collection_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: translation_pairs
CREATE TABLE IF NOT EXISTS `translation_pairs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `unit_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `hindi_text` TEXT NOT NULL,
  `english_text` TEXT NOT NULL,
  `roman_text` TEXT NULL,
  `tense_name` VARCHAR(120) NULL,
  `situation_tag` VARCHAR(120) NULL,
  `level` VARCHAR(80) NULL,
  `explanation` TEXT NULL,
  `accepted_english_answers` TEXT NULL,
  `accepted_hindi_answers` TEXT NULL,
  `answer_match_mode` VARCHAR(40) NOT NULL DEFAULT 'smart',
  `sentence_type` VARCHAR(80) NULL,
  `difficulty_level` VARCHAR(80) NULL,
  `common_mistakes` TEXT NULL,
  `teacher_hint` TEXT NULL,
  `practice_priority` INT NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_translation_pairs` (`collection_id`,`unit_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: material_practice_attempts
CREATE TABLE IF NOT EXISTS `material_practice_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(120) NOT NULL,
  `student_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `pair_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `practice_direction` VARCHAR(40) NOT NULL DEFAULT 'hindi_to_english',
  `user_answer` TEXT NULL,
  `correct_answer` TEXT NULL,
  `score` INT NOT NULL DEFAULT 0,
  `feedback` TEXT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `match_type` VARCHAR(80) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material_attempts` (`session_id`,`created_at`),
  KEY `idx_material_pair` (`pair_id`),
  KEY `idx_material_attempt_student` (`student_id`,`created_at`),
  KEY `idx_material_attempt_correct` (`student_id`,`is_correct`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: material_settings
CREATE TABLE IF NOT EXISTS `material_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_material_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_tests
CREATE TABLE IF NOT EXISTS `weekly_tests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(180) NOT NULL,
  `test_type` VARCHAR(40) NOT NULL DEFAULT 'basic',
  `instructions` TEXT NULL,
  `duration_minutes` INT UNSIGNED NOT NULL DEFAULT 30,
  `total_questions` INT UNSIGNED NOT NULL DEFAULT 30,
  `total_marks` INT UNSIGNED NOT NULL DEFAULT 30,
  `status` VARCHAR(30) NOT NULL DEFAULT 'draft',
  `requires_login` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `starts_at` DATETIME NULL,
  `ends_at` DATETIME NULL,
  `shuffle_questions` ENUM('No','Yes') NOT NULL DEFAULT 'Yes',
  `shuffle_options` ENUM('No','Yes') NOT NULL DEFAULT 'Yes',
  `warning_limit` INT UNSIGNED NOT NULL DEFAULT 3,
  `penalty_after_warnings` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `penalty_per_warning` DECIMAL(6,2) NOT NULL DEFAULT 1,
  `strict_exam_mode` ENUM('No','Yes') NOT NULL DEFAULT 'Yes',
  `auto_submit_on_warning_limit` ENUM('No','Yes') NOT NULL DEFAULT 'Yes',
  `allow_question_jump` ENUM('No','Yes') NOT NULL DEFAULT 'Yes',
  `batch_id` INT UNSIGNED NULL,
  `batch_label` VARCHAR(180) NULL,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_weekly_tests` (`test_type`,`status`,`published`,`status_deleted`,`starts_at`),
  KEY `idx_weekly_batch` (`batch_id`,`status`,`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_test_questions
CREATE TABLE IF NOT EXISTS `weekly_test_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_id` INT UNSIGNED NOT NULL,
  `question_type` VARCHAR(60) NOT NULL DEFAULT 'hindi_to_english',
  `topic_name` VARCHAR(160) NULL,
  `level` VARCHAR(80) NULL,
  `question_text` TEXT NOT NULL,
  `expected_answer` TEXT NULL,
  `option_a` TEXT NULL,
  `option_b` TEXT NULL,
  `option_c` TEXT NULL,
  `option_d` TEXT NULL,
  `marks` DECIMAL(6,2) NOT NULL DEFAULT 1,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_weekly_questions` (`test_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_test_attempts
CREATE TABLE IF NOT EXISTS `weekly_test_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NULL,
  `guest_name` VARCHAR(160) NULL,
  `guest_phone` VARCHAR(40) NULL,
  `canonical_phone` VARCHAR(20) NULL,
  `access_token` VARCHAR(80) NULL,
  `result_token` VARCHAR(80) NULL,
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitted_at` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `last_saved_at` DATETIME NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'started',
  `submission_reason` VARCHAR(40) NULL,
  `auto_score` DECIMAL(8,2) NULL,
  `admin_score` DECIMAL(8,2) NULL,
  `penalty_marks` DECIMAL(8,2) NOT NULL DEFAULT 0,
  `total_marks` DECIMAL(8,2) NULL,
  `warning_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `suspicious_flag` ENUM('No','Yes') NOT NULL DEFAULT 'No',
  `question_order` TEXT NULL,
  `question_snapshot` LONGTEXT NULL,
  `timing_log` MEDIUMTEXT NULL,
  `activity_log` TEXT NULL,
  `admin_note` TEXT NULL,
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_weekly_attempts` (`test_id`,`student_id`,`status`,`submitted_at`),
  KEY `idx_weekly_guest_phone` (`guest_phone`),
  KEY `idx_weekly_access_token` (`access_token`),
  KEY `idx_weekly_result_token` (`result_token`),
  KEY `idx_weekly_canonical_phone` (`canonical_phone`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_test_answers
CREATE TABLE IF NOT EXISTS `weekly_test_answers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attempt_id` INT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `answer_text` TEXT NULL,
  `is_correct` ENUM('Yes','No','Review') NOT NULL DEFAULT 'Review',
  `marks_awarded` DECIMAL(6,2) NULL,
  `admin_note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attempt_question` (`attempt_id`,`question_id`),
  KEY `idx_weekly_answers` (`attempt_id`,`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_test_winners
CREATE TABLE IF NOT EXISTS `weekly_test_winners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_id` INT UNSIGNED NOT NULL,
  `attempt_id` INT UNSIGNED NOT NULL,
  `rank_no` INT UNSIGNED NOT NULL DEFAULT 0,
  `student_name` VARCHAR(180) NULL,
  `student_phone` VARCHAR(30) NULL,
  `score` DECIMAL(8,2) NOT NULL DEFAULT 0,
  `total_marks` DECIMAL(8,2) NOT NULL DEFAULT 0,
  `published_until` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_weekly_winner_attempt` (`test_id`,`attempt_id`),
  KEY `idx_weekly_winners` (`test_id`,`rank_no`,`published_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: roadmap_groups
CREATE TABLE IF NOT EXISTS `roadmap_groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(180) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(20) NULL,
  `color` VARCHAR(40) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_roadmap_groups` (`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: roadmap_units
CREATE TABLE IF NOT EXISTS `roadmap_units` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(180) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `unit_type` VARCHAR(60) NOT NULL DEFAULT 'lesson',
  `level` VARCHAR(80) NULL,
  `target_url` VARCHAR(500) NULL,
  `icon` VARCHAR(20) NULL,
  `reward_points` INT UNSIGNED NOT NULL DEFAULT 10,
  `unlock_after_unit_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_roadmap_units` (`group_id`,`published`,`status_deleted`,`sort_order`),
  KEY `idx_roadmap_unlock` (`unlock_after_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: roadmap_items
CREATE TABLE IF NOT EXISTS `roadmap_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `item_key` VARCHAR(120) NULL,
  `col_1` VARCHAR(255) NULL,
  `col_2` VARCHAR(255) NULL,
  `col_3` VARCHAR(255) NULL,
  `col_4` VARCHAR(255) NULL,
  `col_5` VARCHAR(255) NULL,
  `col_6` VARCHAR(255) NULL,
  `example_text` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_roadmap_items` (`unit_id`,`published`,`status_deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_roadmap_progress
CREATE TABLE IF NOT EXISTS `student_roadmap_progress` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `unit_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(30) NOT NULL DEFAULT 'started',
  `score` INT UNSIGNED NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_student_unit` (`student_id`,`unit_id`),
  KEY `idx_progress` (`student_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 2. Upgrade known older installations with every later column
-- MariaDB supports ADD COLUMN IF NOT EXISTS.
-- =============================================================
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `price` DECIMAL(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `pay_url` VARCHAR(500) NULL;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `course_image` VARCHAR(500) NULL;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `class_time` VARCHAR(160) NULL;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `class_days` VARCHAR(160) NULL;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `total_tests` INT NOT NULL DEFAULT 0;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `lessons_count` INT NOT NULL DEFAULT 0;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `course_details` TEXT NULL;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `outcomes` TEXT NULL;
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `includes_text` TEXT NULL;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `student_image` VARCHAR(500) NULL;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `reviewer_role` VARCHAR(160) NULL;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `review_date` VARCHAR(80) NULL;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `source_label` VARCHAR(120) NULL;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `avatar_initials` VARCHAR(8) NULL;
ALTER TABLE `testimonials` ADD COLUMN IF NOT EXISTS `sort_order` INT NOT NULL DEFAULT 0;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `current_level` VARCHAR(120) NULL;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `preferred_batch` VARCHAR(120) NULL;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `lead_source` VARCHAR(80) NULL;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `enquiry_status` VARCHAR(40) NOT NULL DEFAULT 'New';
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `lead_priority` VARCHAR(30) NOT NULL DEFAULT 'Normal';
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `follow_up_date` DATE NULL;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `last_contacted_at` DATETIME NULL;
ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `auth_version` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `password_hash`;
ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `password_changed_at` DATETIME NULL AFTER `auth_version`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `admin_note` TEXT NULL;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE `gallery_images` ADD COLUMN IF NOT EXISTS `image_alt` VARCHAR(180) NULL;
ALTER TABLE `hero_banners` ADD COLUMN IF NOT EXISTS `desktop_image_url` VARCHAR(500) NULL;
ALTER TABLE `hero_banners` ADD COLUMN IF NOT EXISTS `mobile_image_url` VARCHAR(500) NULL;
ALTER TABLE `hero_banners` ADD COLUMN IF NOT EXISTS `show_content` ENUM('Yes','No') NOT NULL DEFAULT 'Yes';
ALTER TABLE `hero_banners` ADD COLUMN IF NOT EXISTS `content_position` ENUM('left','center','right') NOT NULL DEFAULT 'left';
ALTER TABLE `hero_banners` ADD COLUMN IF NOT EXISTS `overlay_strength` TINYINT UNSIGNED NOT NULL DEFAULT 58;
ALTER TABLE `practice_questions` ADD COLUMN IF NOT EXISTS `accepted_answers` TEXT NULL;
ALTER TABLE `practice_questions` ADD COLUMN IF NOT EXISTS `answer_match_mode` VARCHAR(40) NOT NULL DEFAULT 'smart';
ALTER TABLE `practice_questions` ADD COLUMN IF NOT EXISTS `answer_help` TEXT NULL;
ALTER TABLE `practice_questions` ADD COLUMN IF NOT EXISTS `ai_prompt_hint` TEXT NULL;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `is_correct` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `match_type` VARCHAR(80) NULL;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `ai_feedback` TEXT NULL;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `ai_status` VARCHAR(40) NULL;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `ai_model` VARCHAR(120) NULL;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `corrected_answer` TEXT NULL;
ALTER TABLE `practice_attempts` ADD COLUMN IF NOT EXISTS `natural_answer` TEXT NULL;
ALTER TABLE `material_collections` ADD COLUMN IF NOT EXISTS `practice_priority` INT NOT NULL DEFAULT 0;
ALTER TABLE `material_units` ADD COLUMN IF NOT EXISTS `practice_priority` INT NOT NULL DEFAULT 0;
ALTER TABLE `material_assets` ADD COLUMN IF NOT EXISTS `practice_priority` INT NOT NULL DEFAULT 0;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `accepted_english_answers` TEXT NULL;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `accepted_hindi_answers` TEXT NULL;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `answer_match_mode` VARCHAR(40) NOT NULL DEFAULT 'smart';
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `sentence_type` VARCHAR(80) NULL;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `difficulty_level` VARCHAR(80) NULL;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `common_mistakes` TEXT NULL;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `teacher_hint` TEXT NULL;
ALTER TABLE `translation_pairs` ADD COLUMN IF NOT EXISTS `practice_priority` INT NOT NULL DEFAULT 0;
ALTER TABLE `material_practice_attempts` ADD COLUMN IF NOT EXISTS `student_id` INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `material_practice_attempts` ADD COLUMN IF NOT EXISTS `is_correct` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `material_practice_attempts` ADD COLUMN IF NOT EXISTS `match_type` VARCHAR(80) NULL;
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `shuffle_questions` ENUM('No','Yes') NOT NULL DEFAULT 'Yes';
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `shuffle_options` ENUM('No','Yes') NOT NULL DEFAULT 'Yes';
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `warning_limit` INT UNSIGNED NOT NULL DEFAULT 3;
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `penalty_after_warnings` ENUM('Yes','No') NOT NULL DEFAULT 'Yes';
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `penalty_per_warning` DECIMAL(6,2) NOT NULL DEFAULT 1;
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `strict_exam_mode` ENUM('No','Yes') NOT NULL DEFAULT 'Yes';
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `auto_submit_on_warning_limit` ENUM('No','Yes') NOT NULL DEFAULT 'Yes';
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `allow_question_jump` ENUM('No','Yes') NOT NULL DEFAULT 'Yes';
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `batch_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `batch_label` VARCHAR(180) NULL;
ALTER TABLE `weekly_tests` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL;
ALTER TABLE `weekly_test_questions` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `warning_count` INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `activity_log` TEXT NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `access_token` VARCHAR(80) NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `result_token` VARCHAR(80) NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `question_snapshot` LONGTEXT NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `submission_reason` VARCHAR(40) NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `last_saved_at` DATETIME NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `question_order` TEXT NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `timing_log` MEDIUMTEXT NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `suspicious_flag` ENUM('No','Yes') NOT NULL DEFAULT 'No';
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `penalty_marks` DECIMAL(8,2) NOT NULL DEFAULT 0;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `status_deleted` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL;
ALTER TABLE `weekly_test_attempts` ADD COLUMN IF NOT EXISTS `canonical_phone` VARCHAR(20) NULL;

-- Migrate legacy desktop banner image values.
UPDATE `hero_banners` SET `desktop_image_url`=`image_url` WHERE (`desktop_image_url` IS NULL OR `desktop_image_url`='') AND `image_url` IS NOT NULL AND `image_url`<>'';

-- =============================================================
-- 3. Existing project seed/content data (safe INSERT IGNORE)
-- =============================================================
INSERT IGNORE INTO `admins` (`id`, `name`, `email`, `password_hash`, `published`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@wellfare.local', '$2y$12$DHCToBguTMZptJEHcBMUGuoAErIOUDX45NhgtxRT6i9LPRaojvz5u', 'Yes', '2026-06-20 06:23:11', NULL);;
INSERT IGNORE INTO `batch_timings` (`id`, `batch_name`, `course_name`, `timing`, `days`, `seats_note`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Morning Speaking Batch', 'Basic Spoken English', '7:00 AM - 8:00 AM', 'Mon to Sat', 'Limited seats available', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Evening Confidence Batch', 'Advanced Spoken English', '6:00 PM - 7:00 PM', 'Mon to Sat', 'Best for students and working professionals', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'Weekend Interview Batch', 'Interview Preparation', '10:00 AM - 12:00 PM', 'Saturday and Sunday', 'Admission open this week', 3, 'Yes', '2026-06-20 06:23:11');

-- Phase 136: remove only the known duplicate seed rows from older packages.
DELETE FROM `batch_timings` WHERE `id` IN (4,5,6)
  AND (`batch_name`,`course_name`,`timing`,`days`) IN (
    ('Morning Speaking Batch','Basic Spoken English','7:00 AM - 8:00 AM','Mon to Sat'),
    ('Evening Confidence Batch','Advanced Spoken English','6:00 PM - 7:00 PM','Mon to Sat'),
    ('Weekend Interview Batch','Interview Preparation','10:00 AM - 12:00 PM','Saturday and Sunday')
  );
INSERT IGNORE INTO `content_blocks` (`id`, `block_type`, `block_key`, `icon`, `eyebrow`, `title`, `subtitle`, `body`, `link_text`, `link_url`, `sort_order`, `published`, `created_at`) VALUES
(1, 'home_feature', 'conversation', '💬', '', 'Conversation Practice', 'Daily sentence speaking, question-answer and real-life conversation drills.', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'home_feature', 'grammar', '🧠', '', 'Grammar Made Easy', 'Learn grammar in a practical way so students can use it while speaking.', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'home_feature', 'confidence', '🎯', '', 'Confidence Training', 'Remove hesitation with classroom activities, presentation and correction.', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'home_feature', 'interview', '💼', '', 'Interview Support', 'Prepare introduction, common questions, answers and professional communication.', '', '', '', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'hero_stat', 'practice', '', '', 'Daily', 'Speaking Practice', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(6, 'hero_stat', 'grammar', '', '', 'Basic+', 'Grammar to Fluency', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(7, 'hero_stat', 'trust', '', '', 'Local', 'Trusted Institute', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(8, 'about_highlight', 'trust', '🏫', '', 'Local Trust', 'Designed for students of Mariahu and nearby areas who want better English communication.', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(9, 'about_highlight', 'teacher', '👨‍🏫', '', 'Teacher-Led Practice', 'Classroom guidance, correction and repeated speaking practice help students improve faster.', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(10, 'about_highlight', 'goal', '🚀', '', 'Goal-Based Learning', 'Suitable for school, college, job interview, business and daily English speaking needs.', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(11, 'admission_benefit', 'beginner', '✅', '', 'Beginner friendly classes', 'Start from basic sentences and daily-use speaking.', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(12, 'admission_benefit', 'practice', '🎤', '', 'Practical speaking practice', 'Improve confidence with role-play, correction and conversation.', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(13, 'admission_benefit', 'contact', '💬', '', 'Fast contact options', 'Call or WhatsApp directly for fee, timing and demo class details.', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(14, 'home_feature', 'conversation', '💬', '', 'Conversation Practice', 'Daily sentence speaking, question-answer and real-life conversation drills.', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(15, 'home_feature', 'grammar', '🧠', '', 'Grammar Made Easy', 'Learn grammar in a practical way so students can use it while speaking.', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(16, 'home_feature', 'confidence', '🎯', '', 'Confidence Training', 'Remove hesitation with classroom activities, presentation and correction.', '', '', '', 3, 'Yes', '2026-06-21 22:09:35'),
(17, 'home_feature', 'interview', '💼', '', 'Interview Support', 'Prepare introduction, common questions, answers and professional communication.', '', '', '', 4, 'Yes', '2026-06-21 22:09:35'),
(18, 'hero_stat', 'practice', '', '', 'Daily', 'Speaking Practice', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(19, 'hero_stat', 'grammar', '', '', 'Basic+', 'Grammar to Fluency', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(20, 'hero_stat', 'trust', '', '', 'Local', 'Trusted Institute', '', '', '', 3, 'Yes', '2026-06-21 22:09:35'),
(21, 'about_highlight', 'trust', '🏫', '', 'Local Trust', 'Designed for students of Mariahu and nearby areas who want better English communication.', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(22, 'about_highlight', 'teacher', '👨‍🏫', '', 'Teacher-Led Practice', 'Classroom guidance, correction and repeated speaking practice help students improve faster.', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(23, 'about_highlight', 'goal', '🚀', '', 'Goal-Based Learning', 'Suitable for school, college, job interview, business and daily English speaking needs.', '', '', '', 3, 'Yes', '2026-06-21 22:09:35'),
(24, 'admission_benefit', 'beginner', '✅', '', 'Beginner friendly classes', 'Start from basic sentences and daily-use speaking.', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(25, 'admission_benefit', 'practice', '🎤', '', 'Practical speaking practice', 'Improve confidence with role-play, correction and conversation.', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(26, 'admission_benefit', 'contact', '💬', '', 'Fast contact options', 'Call or WhatsApp directly for fee, timing and demo class details.', '', '', '', 3, 'Yes', '2026-06-21 22:09:35');;
INSERT IGNORE INTO `courses` (`id`, `title`, `short_description`, `duration`, `level`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Basic Spoken English', 'Start from basic words, sentence formation, tense clarity and daily-use conversation.', '3 Months', 'Beginner', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Advanced Spoken English', 'Improve fluency, confidence, pronunciation, vocabulary and natural communication.', '3 Months', 'Advanced', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'Grammar With Speaking', 'Learn grammar practically so you can use it while speaking and writing.', '2 Months', 'All Levels', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Interview Preparation', 'Practice self-introduction, HR questions, answers and professional communication.', '1 Month', 'Job Seekers', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'Personality Development', 'Build confidence, presentation style, public speaking and professional behaviour.', '1 Month', 'Confidence', 5, 'Yes', '2026-06-20 06:23:11'),
(6, 'Student English Practice', 'Special guided English practice for school and college students.', 'Flexible', 'Students', 6, 'Yes', '2026-06-20 06:23:11'),
(7, 'Basic Spoken English', 'Start from basic words, sentence formation, tense clarity and daily-use conversation.', '3 Months', 'Beginner', 1, 'Yes', '2026-06-21 22:09:35'),
(8, 'Advanced Spoken English', 'Improve fluency, confidence, pronunciation, vocabulary and natural communication.', '3 Months', 'Advanced', 2, 'Yes', '2026-06-21 22:09:35'),
(9, 'Grammar With Speaking', 'Learn grammar practically so you can use it while speaking and writing.', '2 Months', 'All Levels', 3, 'Yes', '2026-06-21 22:09:35'),
(10, 'Interview Preparation', 'Practice self-introduction, HR questions, answers and professional communication.', '1 Month', 'Job Seekers', 4, 'Yes', '2026-06-21 22:09:35'),
(11, 'Personality Development', 'Build confidence, presentation style, public speaking and professional behaviour.', '1 Month', 'Confidence', 5, 'Yes', '2026-06-21 22:09:35'),
(12, 'Student English Practice', 'Special guided English practice for school and college students.', 'Flexible', 'Students', 6, 'Yes', '2026-06-21 22:09:35');;
INSERT IGNORE INTO `enquiries` (`id`, `name`, `phone`, `course_interest`, `current_level`, `preferred_batch`, `lead_source`, `message`, `enquiry_status`, `lead_priority`, `follow_up_date`, `last_contacted_at`, `admin_note`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'Tranding Topic..', '89908908', 'Basic Spoken English', 'Beginner', 'Morning Speaking Batch - 7:00 AM - 8:00 AM', 'Website Admission Form', 'i have', 'New', 'Normal', NULL, NULL, '', '::1', '2026-06-21 20:04:11', '2026-06-21 20:05:02');;
INSERT IGNORE INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Can beginners join the spoken English course?', 'Yes. Beginners can join. The course starts from basic sentence formation, daily-use words and confidence practice.', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Do you provide interview preparation?', 'Yes. Interview preparation includes self-introduction, common HR questions, answer practice and confidence correction.', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'How can I know the right batch for me?', 'Submit the admission form or WhatsApp us. We will guide you based on your current level and available timing.', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Can beginners join the spoken English course?', 'Yes. Beginners can join. The course starts from basic sentence formation, daily-use words and confidence practice.', 1, 'Yes', '2026-06-21 22:09:35'),
(5, 'Do you provide interview preparation?', 'Yes. Interview preparation includes self-introduction, common HR questions, answer practice and confidence correction.', 2, 'Yes', '2026-06-21 22:09:35'),
(6, 'How can I know the right batch for me?', 'Submit the admission form or WhatsApp us. We will guide you based on your current level and available timing.', 3, 'Yes', '2026-06-21 22:09:35');;
INSERT IGNORE INTO `form_options` (`id`, `option_group`, `option_label`, `option_value`, `helper_text`, `sort_order`, `published`, `created_at`) VALUES
(1, 'current_level', 'Beginner', 'Beginner', 'New learner starting from basics', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'current_level', 'Can understand but cannot speak', 'Can understand but cannot speak', 'Understands English but hesitates while speaking', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'current_level', 'Basic speaking', 'Basic speaking', 'Can speak simple English and wants fluency', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'current_level', 'Interview preparation', 'Interview preparation', 'Needs interview and professional communication practice', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'enquiry_status', 'New', 'New', '', 1, 'Yes', '2026-06-20 06:23:11'),
(6, 'enquiry_status', 'Contacted', 'Contacted', '', 2, 'Yes', '2026-06-20 06:23:11'),
(7, 'enquiry_status', 'Converted', 'Converted', '', 3, 'Yes', '2026-06-20 06:23:11'),
(8, 'enquiry_status', 'Not Interested', 'Not Interested', '', 4, 'Yes', '2026-06-20 06:23:11'),
(9, 'current_level', 'Beginner', 'Beginner', 'New learner starting from basics', 1, 'Yes', '2026-06-21 22:09:35'),
(10, 'current_level', 'Can understand but cannot speak', 'Can understand but cannot speak', 'Understands English but hesitates while speaking', 2, 'Yes', '2026-06-21 22:09:35'),
(11, 'current_level', 'Basic speaking', 'Basic speaking', 'Can speak simple English and wants fluency', 3, 'Yes', '2026-06-21 22:09:35'),
(12, 'current_level', 'Interview preparation', 'Interview preparation', 'Needs interview and professional communication practice', 4, 'Yes', '2026-06-21 22:09:35'),
(13, 'enquiry_status', 'New', 'New', '', 1, 'Yes', '2026-06-21 22:09:35'),
(14, 'enquiry_status', 'Contacted', 'Contacted', '', 2, 'Yes', '2026-06-21 22:09:35'),
(15, 'enquiry_status', 'Converted', 'Converted', '', 3, 'Yes', '2026-06-21 22:09:35'),
(16, 'enquiry_status', 'Not Interested', 'Not Interested', '', 4, 'Yes', '2026-06-21 22:09:35');;
INSERT IGNORE INTO `gallery_images` (`id`, `title`, `category`, `image_url`, `image_alt`, `description`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Classroom Speaking Practice', 'Classroom', '', 'Students practicing spoken English in classroom', 'Students practicing daily spoken English in a guided class environment.', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Interview Confidence Session', 'Activity', '', 'Interview practice session for spoken English students', 'Role-play and self-introduction practice for job seekers and students.', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'Grammar With Speaking Batch', 'Learning', '', 'Grammar with speaking batch activity', 'Practical grammar lessons connected with real spoken English usage.', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Classroom Speaking Practice', 'Classroom', '', 'Students practicing spoken English in classroom', 'Students practicing daily spoken English in a guided class environment.', 1, 'Yes', '2026-06-21 22:09:35'),
(5, 'Interview Confidence Session', 'Activity', '', 'Interview practice session for spoken English students', 'Role-play and self-introduction practice for job seekers and students.', 2, 'Yes', '2026-06-21 22:09:35'),
(6, 'Grammar With Speaking Batch', 'Learning', '', 'Grammar with speaking batch activity', 'Practical grammar lessons connected with real spoken English usage.', 3, 'Yes', '2026-06-21 22:09:35'),
(8, 'testing pic', 'Spoken', 'assets/uploads/gallery/gallery-20260621-200631-a4ba4e65.jpg', 'Student practising spoken English .....Student practising spoken English', 'Student practising spoken EnglishStudent practising spoken EnglishStudent practising spoken English', 0, 'Yes', '2026-06-21 23:36:31');;
INSERT IGNORE INTO `hero_banners` (`id`, `page_key`, `eyebrow`, `title`, `subtitle`, `image_url`, `image_alt`, `badge_one`, `badge_two`, `stat_one_label`, `stat_one_value`, `stat_two_label`, `stat_two_value`, `primary_text`, `primary_url`, `secondary_text`, `secondary_url`, `sort_order`, `published`, `created_at`) VALUES
(1, 'home', 'Free Counselling Open', 'Speak English confidently in daily life', 'A premium, admin-managed hero banner area. Upload institute photos or keep the elegant fallback visual.', 'assets/uploads/banners/banner-20260622-080529-a9a3ce6f.png', 'Student practising spoken English', '🎤 Speak Daily', '📚 Easy to Advanced', 'Daily Practice', 'Yes', 'Interview Support', 'Included', 'Book Free Counselling', 'admission.php', 'Practice Room', 'spoken-materials.php', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'practice', 'Practice Room', 'Spoken English Practice Room', 'Practise tenses, daily situations, sentence correction and speaking confidence without login.', '', 'English practice lab', 'Free + Safe', 'Works Without API', 'Tense Practice', 'Free', 'Voice Input', 'Browser', 'Start Practice', '#practice-lessons', 'Book Free Demo', 'admission.php', 1, 'Yes', '2026-06-20 06:23:11'),
(3, 'home', '', 'Hero', '', 'assets/uploads/banners/banner-20260620-025947-3f0dbc56.jpg', '', '', '', '', '', '', '', '', '', '', '', 0, 'No', '2026-06-20 06:29:47');;
INSERT IGNORE INTO `material_assets` (`id`, `collection_id`, `title`, `file_path`, `original_name`, `file_type`, `notes`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 1, 'Spoken Note Image 1', 'assets/uploads/materials/notes/note_01.jpeg', 'note_01.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 1, 'Yes', 0, '2026-06-21 22:28:52'),
(2, 1, 'Spoken Note Image 2', 'assets/uploads/materials/notes/note_02.jpeg', 'note_02.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 2, 'Yes', 0, '2026-06-21 22:28:52'),
(3, 1, 'Spoken Note Image 3', 'assets/uploads/materials/notes/note_03.jpeg', 'note_03.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 3, 'Yes', 0, '2026-06-21 22:28:52'),
(4, 1, 'Spoken Note Image 4', 'assets/uploads/materials/notes/note_04.jpeg', 'note_04.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 4, 'Yes', 0, '2026-06-21 22:28:52'),
(5, 1, 'Spoken Note Image 5', 'assets/uploads/materials/notes/note_05.jpeg', 'note_05.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 5, 'Yes', 0, '2026-06-21 22:28:52'),
(6, 1, 'Spoken Note Image 6', 'assets/uploads/materials/notes/note_06.jpeg', 'note_06.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 6, 'Yes', 0, '2026-06-21 22:28:52'),
(7, 1, 'Spoken Note Image 7', 'assets/uploads/materials/notes/note_07.jpeg', 'note_07.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 7, 'Yes', 0, '2026-06-21 22:28:52'),
(8, 1, 'Spoken Note Image 8', 'assets/uploads/materials/notes/note_08.jpeg', 'note_08.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 8, 'Yes', 0, '2026-06-21 22:28:52'),
(9, 1, 'Spoken Note Image 9', 'assets/uploads/materials/notes/note_09.jpeg', 'note_09.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 9, 'Yes', 0, '2026-06-21 22:28:52'),
(10, 1, 'Spoken Note Image 10', 'assets/uploads/materials/notes/note_10.jpeg', 'note_10.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 10, 'Yes', 0, '2026-06-21 22:28:52'),
(11, 1, 'Spoken Note Image 11', 'assets/uploads/materials/notes/note_11.jpeg', 'note_11.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 11, 'Yes', 0, '2026-06-21 22:28:52'),
(12, 1, 'Spoken Note Image 12', 'assets/uploads/materials/notes/note_12.jpeg', 'note_12.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 12, 'Yes', 0, '2026-06-21 22:28:52'),
(13, 1, 'Spoken Note Image 13', 'assets/uploads/materials/notes/note_13.jpeg', 'note_13.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 13, 'Yes', 0, '2026-06-21 22:28:52'),
(14, 1, 'Spoken Note Image 14', 'assets/uploads/materials/notes/note_14.jpeg', 'note_14.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 14, 'Yes', 0, '2026-06-21 22:28:52'),
(15, 1, 'Spoken Note Image 15', 'assets/uploads/materials/notes/note_15.jpeg', 'note_15.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 15, 'Yes', 0, '2026-06-21 22:28:52'),
(16, 1, 'Spoken Note Image 16', 'assets/uploads/materials/notes/note_16.jpeg', 'note_16.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 16, 'Yes', 0, '2026-06-21 22:28:52'),
(17, 1, 'Spoken Note Image 17', 'assets/uploads/materials/notes/note_17.jpeg', 'note_17.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 17, 'Yes', 0, '2026-06-21 22:28:52'),
(18, 1, 'Spoken Note Image 18', 'assets/uploads/materials/notes/note_18.jpeg', 'note_18.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 18, 'Yes', 0, '2026-06-21 22:28:52'),
(19, 1, 'Spoken Note Image 19', 'assets/uploads/materials/notes/note_19.jpeg', 'note_19.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 19, 'Yes', 0, '2026-06-21 22:28:52'),
(20, 1, 'Spoken Note Image 20', 'assets/uploads/materials/notes/note_20.jpeg', 'note_20.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 20, 'Yes', 0, '2026-06-21 22:28:52'),
(21, 1, 'Spoken Note Image 21', 'assets/uploads/materials/notes/note_21.jpeg', 'note_21.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 21, 'Yes', 0, '2026-06-21 22:28:52'),
(22, 1, 'Spoken Note Image 22', 'assets/uploads/materials/notes/note_22.jpeg', 'note_22.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 22, 'Yes', 0, '2026-06-21 22:28:52'),
(23, 1, 'Spoken Note Image 23', 'assets/uploads/materials/notes/note_23.jpeg', 'note_23.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 23, 'Yes', 0, '2026-06-21 22:28:52'),
(24, 1, 'Spoken Note Image 24', 'assets/uploads/materials/notes/note_24.jpeg', 'note_24.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 24, 'Yes', 0, '2026-06-21 22:28:52'),
(25, 1, 'Spoken Note Image 25', 'assets/uploads/materials/notes/note_25.jpeg', 'note_25.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 25, 'Yes', 0, '2026-06-21 22:28:52'),
(26, 1, 'Spoken Note Image 26', 'assets/uploads/materials/notes/note_26.jpeg', 'note_26.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 26, 'Yes', 0, '2026-06-21 22:28:52'),
(27, 1, 'Spoken Note Image 27', 'assets/uploads/materials/notes/note_27.jpeg', 'note_27.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 27, 'Yes', 0, '2026-06-21 22:28:52'),
(28, 1, 'Spoken Note Image 28', 'assets/uploads/materials/notes/note_28.jpeg', 'note_28.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 28, 'Yes', 0, '2026-06-21 22:28:52'),
(29, 1, 'Spoken Note Image 29', 'assets/uploads/materials/notes/note_29.jpeg', 'note_29.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 29, 'Yes', 0, '2026-06-21 22:28:52'),
(30, 1, 'Spoken Note Image 30', 'assets/uploads/materials/notes/note_30.jpeg', 'note_30.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 30, 'Yes', 0, '2026-06-21 22:28:52'),
(31, 1, 'Spoken Note Image 31', 'assets/uploads/materials/notes/note_31.jpeg', 'note_31.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 31, 'Yes', 0, '2026-06-21 22:28:52'),
(32, 1, 'Spoken Note Image 32', 'assets/uploads/materials/notes/note_32.jpeg', 'note_32.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 32, 'Yes', 0, '2026-06-21 22:28:52'),
(33, 1, 'Spoken Note Image 33', 'assets/uploads/materials/notes/note_33.jpeg', 'note_33.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 33, 'Yes', 0, '2026-06-21 22:28:52'),
(34, 1, 'Spoken Note Image 34', 'assets/uploads/materials/notes/note_34.jpeg', 'note_34.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 34, 'Yes', 0, '2026-06-21 22:28:52'),
(35, 1, 'Spoken Note Image 35', 'assets/uploads/materials/notes/note_35.jpeg', 'note_35.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 35, 'Yes', 0, '2026-06-21 22:28:52'),
(36, 1, 'Spoken Note Image 36', 'assets/uploads/materials/notes/note_36.jpeg', 'note_36.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 36, 'Yes', 0, '2026-06-21 22:28:52'),
(37, 1, 'Spoken Note Image 37', 'assets/uploads/materials/notes/note_37.jpeg', 'note_37.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 37, 'Yes', 0, '2026-06-21 22:28:52');;
INSERT IGNORE INTO `material_collections` (`id`, `title`, `slug`, `category`, `level`, `description`, `cover_image`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 'Uploaded Spoken Notes', 'uploaded-spoken-notes', 'Notes', 'Beginner to Advanced', 'Your uploaded spoken English WhatsApp note images are stored here. Admin can add more images and convert important lines into Hindi-English practice pairs.', '', 1, 'Yes', 0, '2026-06-20 06:23:12'),
(2, 'Daily Hindi to English Sentences', 'daily-hindi-to-english', 'Translation Practice', 'Beginner', 'Daily-use Hindi sentences with simple spoken English answers for regular practice.', '', 2, 'Yes', 0, '2026-06-20 06:23:12'),
(3, 'Testing Hindi-English Practice Data', 'testing-hindi-english-practice-data', 'Translation Practice', 'Beginner to Intermediate', 'Ready-made testing sentences for Hindi to English and English to Hindi practice.', '', 3, 'Yes', 0, '2026-06-20 06:38:23');;
INSERT IGNORE INTO `material_practice_attempts` (`id`, `session_id`, `pair_id`, `practice_direction`, `user_answer`, `correct_answer`, `score`, `feedback`, `created_at`, `is_correct`, `match_type`) VALUES
(1, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'he cj', 'I try to speak English every day.', 0, 'Keep practising. Read the correct answer and say it loudly three times.', '2026-06-20 06:36:33', 0, NULL),
(2, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'he cj', 'I try to speak English every day.', 0, 'Keep practising. Read the correct answer and say it loudly three times.', '2026-06-20 06:38:34', 0, NULL),
(3, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'i try to speAK ENGLISH EVERY DAY', 'I try to speak English every day.', 10, 'Excellent. Your answer matches the expected sentence.', '2026-06-20 06:39:21', 0, NULL),
(4, '7aqh6dddrd138837r28mraegch', 9, 'hindi_to_english', 'i speak english every day', 'I speak English every day.', 10, 'Excellent. Your answer matches the expected sentence.', '2026-06-20 22:55:43', 0, NULL),
(5, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'nmb', 'I try to speak English every day.', 0, 'Keep practising. Read the correct answer and say it loudly three times.', '2026-06-20 22:55:57', 0, NULL);;
INSERT IGNORE INTO `material_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'material_library_enabled', 'Yes', '2026-06-20 06:23:12'),
(2, 'material_public_title', 'Spoken English Material & Hindi-English Practice', '2026-06-20 06:23:12'),
(3, 'material_public_subtitle', 'Learn from notes, practise Hindi to English and English to Hindi, and improve sentence making daily.', '2026-06-20 06:23:12'),
(4, 'material_upload_max_note', 'Recommended: upload notes in small batches. Images/PDF/TXT are supported; CSV/text import is best for very big sentence data.', '2026-06-20 06:23:12'),
(5, 'material_daily_practice_limit', '50', '2026-06-20 06:23:12'),
(1196, 'auto_translate_enabled', 'No', '2026-06-21 22:28:52'),
(1197, 'auto_translate_provider', 'none', '2026-06-21 22:28:52'),
(1198, 'auto_translate_note', 'Use teacher-approved material first. External translation requires a legal provider/API key and is optional.', '2026-06-21 22:28:52');;
INSERT IGNORE INTO `material_units` (`id`, `collection_id`, `title`, `unit_type`, `tense_name`, `level`, `instructions`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 2, 'Daily Life Sentences', 'translation', 'Mixed Tenses', 'Beginner', 'Read Hindi, speak/write English, then compare with the natural answer.', 1, 'Yes', 0, '2026-06-20 06:32:10'),
(2, 3, 'Daily Use Sentences - Testing Set', 'translation', 'Mixed', 'Beginner', 'Practise these lines both Hindi to English and English to Hindi.', 1, 'Yes', 0, '2026-06-20 06:38:23');;
INSERT IGNORE INTO `nav_menus` (`id`, `menu_area`, `label`, `url`, `is_cta`, `sort_order`, `published`, `created_at`) VALUES
(1, 'header', 'Home', 'index.php', 'No', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'header', 'About', 'about.php', 'No', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'header', 'Courses', 'courses.php', 'No', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'header', 'Gallery', 'gallery.php', 'No', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'header', 'Reviews', 'reviews.php', 'No', 5, 'Yes', '2026-06-20 06:23:11'),
(6, 'header', 'Contact', 'contact.php', 'No', 6, 'Yes', '2026-06-20 06:23:11'),
(7, 'header', 'Admission', 'admission.php', 'Yes', 7, 'Yes', '2026-06-20 06:23:11'),
(8, 'footer', 'Courses', 'courses.php', 'No', 1, 'Yes', '2026-06-20 06:23:11'),
(9, 'footer', 'Gallery', 'gallery.php', 'No', 2, 'Yes', '2026-06-20 06:23:11'),
(10, 'footer', 'Reviews', 'reviews.php', 'No', 3, 'Yes', '2026-06-20 06:23:11'),
(11, 'footer', 'Admission', 'admission.php', 'No', 4, 'Yes', '2026-06-20 06:23:11'),
(12, 'header', 'Practice Room', 'spoken-materials.php', 'No', 7, 'Yes', '2026-06-20 06:38:23'),
(13, 'header', 'Study Practice', 'spoken-materials.php', 'No', 8, 'Yes', '2026-06-20 06:38:23'),
(14, 'footer', 'Practice Room', 'spoken-materials.php', 'No', 5, 'Yes', '2026-06-20 06:38:23'),
(15, 'footer', 'Study Materials', 'spoken-materials.php', 'No', 6, 'Yes', '2026-06-20 06:38:23');;
INSERT IGNORE INTO `practice_ai_logs` (`id`, `session_id`, `question_id`, `provider`, `model`, `request_type`, `prompt_chars`, `response_chars`, `status`, `error_message`, `created_at`) VALUES
(1, '7aqh6dddrd138837r28mraegch', 16, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 06:40:26'),
(2, '7aqh6dddrd138837r28mraegch', 16, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 06:47:22'),
(3, '7aqh6dddrd138837r28mraegch', 16, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 06:50:00'),
(4, '7aqh6dddrd138837r28mraegch', 9, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 22:32:35'),
(5, '7aqh6dddrd138837r28mraegch', 1, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:00:54'),
(6, '7aqh6dddrd138837r28mraegch', 2, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:02:28'),
(7, '7aqh6dddrd138837r28mraegch', 3, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:05:28'),
(8, '7aqh6dddrd138837r28mraegch', 3, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:05:54'),
(9, '7aqh6dddrd138837r28mraegch', 1, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-21 22:32:39'),
(10, '7aqh6dddrd138837r28mraegch', 26, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-21 22:33:25'),
(11, '7aqh6dddrd138837r28mraegch', 1, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-22 11:38:48');;
INSERT IGNORE INTO `practice_attempts` (`id`, `session_id`, `student_name`, `phone`, `question_id`, `user_answer`, `correct_answer`, `score`, `is_correct`, `match_type`, `local_feedback`, `suggested_next_step`, `ai_feedback`, `ai_status`, `ai_model`, `corrected_answer`, `natural_answer`, `created_at`) VALUES
(1, '7aqh6dddrd138837r28mraegch', NULL, NULL, 16, 'hello', 'I want to improve my English.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'I want to improve my English.', 'I want to improve my English.', '2026-06-20 06:40:26'),
(2, '7aqh6dddrd138837r28mraegch', NULL, NULL, 16, 'hello', 'I want to improve my English.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'I want to improve my English.', 'I want to improve my English.', '2026-06-20 06:47:22'),
(3, '7aqh6dddrd138837r28mraegch', NULL, NULL, 16, 'I want to improve my English', 'I want to improve my English.', 10, 0, NULL, 'Correct. Good job. Repeat it loudly three times for speaking confidence.', 'Try the next question or practise this sentence by speaking.', '', 'off', '', 'I want to improve my English.', 'I want to improve my English.', '2026-06-20 06:50:00'),
(4, '7aqh6dddrd138837r28mraegch', NULL, NULL, 9, 'sockets right there have', 'Sorry, teacher. I am late because of traffic. It will not happen again.', 5, 0, NULL, 'Your answer has effort. Now make it more natural using the sample answer.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'Sorry, teacher. I am late because of traffic. It will not happen again.', 'Sorry, teacher. I am late because of traffic. It will not happen again.', '2026-06-20 22:32:35'),
(5, '7aqh6dddrd138837r28mraegch', NULL, NULL, 1, 'speak', 'I speak English every day.', 10, 0, NULL, 'Correct. Good job. Repeat it loudly three times for speaking confidence.', 'Try the next question or practise this sentence by speaking.', '', 'off', '', 'I speak English every day.', 'I speak English every day.', '2026-06-20 23:00:54'),
(6, '7aqh6dddrd138837r28mraegch', NULL, NULL, 2, 'drink', 'She drinks tea in the morning.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'She drinks tea in the morning.', 'She drinks tea in the morning.', '2026-06-20 23:02:28'),
(7, '7aqh6dddrd138837r28mraegch', NULL, NULL, 3, 'I goes to school', 'I go to school every day.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'I go to school every day.', 'I go to school every day.', '2026-06-20 23:05:28'),
(8, '7aqh6dddrd138837r28mraegch', NULL, NULL, 3, 'I go to school every day', 'I go to school every day.', 10, 0, NULL, 'Correct. Good job. Repeat it loudly three times for speaking confidence.', 'Try the next question or practise this sentence by speaking.', '', 'off', '', 'I go to school every day.', 'I go to school every day.', '2026-06-20 23:05:54'),
(9, '7aqh6dddrd138837r28mraegch', NULL, NULL, 1, 'speaks', 'I speak English every day.', 9, 1, 'smart_close_match', 'Correct. Good job. This answer matches the teacher-approved answer set.', 'Try the next question or speak this sentence three times.', '', 'off', '', 'I speak English every day.', 'I speak English every day.', '2026-06-21 22:32:40'),
(10, '7aqh6dddrd138837r28mraegch', NULL, NULL, 26, 'I want to improve my English speaking confidence', 'I want to improve my English speaking confidence.', 10, 1, 'exact_or_accepted', 'Correct. Good job. This answer matches the teacher-approved answer set.', 'Try the next question or speak this sentence three times.', '', 'off', '', 'I want to improve my English speaking confidence.', 'I want to improve my English speaking confidence.', '2026-06-21 22:33:26'),
(11, '7aqh6dddrd138837r28mraegch', NULL, NULL, 1, 'speak', 'I speak English every day.', 10, 1, 'exact_or_accepted', 'Correct. Good job. This answer matches the teacher-approved answer set.', 'Try the next question or speak this sentence three times.', '', 'off', '', 'I speak English every day.', 'I speak English every day.', '2026-06-22 11:38:48');;
INSERT IGNORE INTO `practice_categories` (`id`, `category_name`, `slug`, `description`, `icon`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 'Tense Practice', 'tense-practice', 'Practise English tenses with fill blanks, sentence making and Hindi-English examples.', '⏱️', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(2, 'Situation Practice', 'situation-practice', 'Learn what to say in real-life situations like class, market, phone call and office.', '💬', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(3, 'Sentence Correction', 'sentence-correction', 'Fix common grammar mistakes and speak more naturally.', '✅', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(4, 'Interview English', 'interview-english', 'Practise self introduction, strengths, goals and interview answers.', '🎯', 4, 'Yes', 0, '2026-06-20 06:38:23'),
(5, 'Voice Speaking', 'voice-speaking', 'Speak answers using browser voice input and practise pronunciation confidence.', '🎤', 5, 'Yes', 0, '2026-06-20 06:38:23'),
(6, 'Voice Practice', 'voice-practice', 'Speak using browser voice input and compare your spoken sentence with the correct answer.', '🎤', 4, 'Yes', 0, '2026-06-21 22:28:51');;
INSERT IGNORE INTO `practice_common_mistakes` (`id`, `wrong_pattern`, `correct_pattern`, `explanation`, `example_sentence`, `published`, `status_deleted`, `created_at`) VALUES
(1, 'I am go', 'I am going', 'Use am + verb-ing for present continuous.', 'I am going to the market now.', 'Yes', 0, '2026-06-20 06:38:23'),
(2, 'I am go to market yesterday', 'I went to the market yesterday', 'For yesterday, use Past Simple: went.', 'I went to the market yesterday.', 'Yes', 0, '2026-06-20 06:38:23'),
(3, 'He go', 'He goes', 'With He/She/It in Present Simple, add s/es.', 'He goes to school every day.', 'Yes', 0, '2026-06-20 06:38:23'),
(4, 'I has', 'I have', 'Use have with I/You/We/They.', 'I have a book.', 'Yes', 0, '2026-06-20 06:38:23'),
(5, 'did not went', 'did not go', 'After did/did not, use base verb.', 'I did not go there.', 'Yes', 0, '2026-06-20 06:38:23'),
(6, 'more better', 'better', 'Do not use more with better.', 'This answer is better.', 'Yes', 0, '2026-06-20 06:38:23');;
INSERT IGNORE INTO `practice_lessons` (`id`, `category_id`, `lesson_title`, `lesson_type`, `level`, `tense_name`, `short_description`, `instructions`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 1, 'Present Simple - Daily Habits', 'tense', 'Beginner', 'Present Simple', 'Practise habits and daily routine sentences.', 'Use base verb with I/You/We/They. Use verb+s/es with He/She/It.', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(2, 1, 'Present Continuous - Right Now', 'tense', 'Beginner', 'Present Continuous', 'Practise actions happening now.', 'Use am/is/are + verb ing for actions happening now.', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(3, 1, 'Past Simple - Yesterday Actions', 'tense', 'Beginner', 'Past Simple', 'Practise completed past actions.', 'Use verb 2 for positive past sentences. Use did not + base verb for negative.', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(4, 1, 'Future Simple - Plans', 'tense', 'Beginner', 'Future Simple', 'Practise future plans and promises.', 'Use will + base verb for simple future actions.', 4, 'Yes', 0, '2026-06-20 06:38:23'),
(5, 2, 'Daily Speaking Situations', 'situation', 'Beginner', NULL, 'Practise common real-life spoken English lines.', 'Write a polite and natural answer. Compare with the sample answer and repeat loudly.', 5, 'Yes', 0, '2026-06-20 06:38:23'),
(6, 4, 'Interview Self Introduction', 'situation', 'Intermediate', NULL, 'Practise simple interview answers.', 'Keep answers clear, short and confident. Do not memorize too much.', 6, 'Yes', 0, '2026-06-20 06:38:23'),
(7, 3, 'Common Grammar Mistake Correction', 'correction', 'Beginner', NULL, 'Correct common Indian learner mistakes.', 'Type the corrected sentence. Then say it loudly three times.', 7, 'Yes', 0, '2026-06-20 06:38:23'),
(8, 5, 'Speak and Check Basic Answers', 'voice', 'Beginner', NULL, 'Use voice input to practise speaking simple English answers.', 'Click Start Speaking, say the answer clearly, then check it.', 8, 'Yes', 0, '2026-06-20 06:38:23'),
(9, 1, 'Present Simple Practice', 'tense', 'Beginner', 'Present Simple', 'Practise daily routine sentences using base verb and s/es.', 'Use base verb with I/You/We/They. Use s/es with He/She/It.', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(10, 1, 'Past Simple Practice', 'tense', 'Beginner', 'Past Simple', 'Practise completed actions using verb second form.', 'Use V2 for positive past sentences and did + base verb for negative/questions.', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(11, 1, 'Present Continuous Practice', 'tense', 'Beginner', 'Present Continuous', 'Practise actions happening now using am/is/are + verb-ing.', 'Use am/is/are + verb-ing for current actions.', 3, 'Yes', 0, '2026-06-21 22:28:51'),
(12, 2, 'Daily Life Situations', 'situation', 'Beginner', '', 'Practise simple answers for real daily speaking situations.', 'Write a natural answer for the situation. Focus on clear and polite English.', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(13, 2, 'Interview Speaking', 'situation', 'Intermediate', '', 'Practise interview answers such as self introduction and strengths.', 'Write a short confident answer. Keep it natural and professional.', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(14, 3, 'Correct My Sentence', 'correction', 'All Levels', '', 'Type or practise incorrect sentences and learn the correct version.', 'Compare your answer with the corrected sample and explanation.', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(15, 6, 'Speak and Compare', 'voice', 'All Levels', '', 'Use browser voice typing to practise English pronunciation and sentence flow.', 'Click Start Speaking, say your answer, then compare it with the correct answer.', 1, 'Yes', 0, '2026-06-21 22:28:51');;
INSERT IGNORE INTO `practice_questions` (`id`, `category_id`, `lesson_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `sample_answer`, `accepted_answers`, `answer_match_mode`, `answer_help`, `explanation`, `ai_prompt_hint`, `tense_name`, `level`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 1, 1, 'fill_blank', 'I ___ English every day.', 'speak', 'speaks', 'speaking', 'spoke', 'speak', 'I speak English every day.', NULL, 'smart', NULL, 'With I, use base verb: speak.', NULL, 'Present Simple', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(2, 1, 1, 'fill_blank', 'She ___ tea in the morning.', 'drink', 'drinks', 'drinking', 'drank', 'drinks', 'She drinks tea in the morning.', NULL, 'smart', NULL, 'With She/He/It, add s/es to the verb.', NULL, 'Present Simple', 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(3, 1, 1, 'hindi_to_english', 'मैं रोज स्कूल जाता हूँ।', NULL, NULL, NULL, NULL, 'I go to school every day', 'I go to school every day.', NULL, 'smart', NULL, 'Habit sentence, so use Present Simple.', NULL, 'Present Simple', 'Beginner', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(4, 1, 2, 'fill_blank', 'I ___ English now.', 'am learning', 'is learning', 'learn', 'learned', 'am learning', 'I am learning English now.', NULL, 'smart', NULL, 'Use am + verb ing with I.', NULL, 'Present Continuous', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(5, 1, 2, 'fill_blank', 'They ___ cricket at the moment.', 'are playing', 'is playing', 'play', 'played', 'are playing', 'They are playing cricket at the moment.', NULL, 'smart', NULL, 'Use are + verb ing with They/We/You.', NULL, 'Present Continuous', 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(6, 1, 3, 'fill_blank', 'I ___ to the market yesterday.', 'go', 'went', 'gone', 'going', 'went', 'I went to the market yesterday.', NULL, 'smart', NULL, 'Yesterday shows past time, so use verb 2: went.', NULL, 'Past Simple', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(7, 1, 3, 'hindi_to_english', 'मैंने कल अपना होमवर्क किया।', NULL, NULL, NULL, NULL, 'I did my homework yesterday', 'I did my homework yesterday.', NULL, 'smart', NULL, 'Completed action in the past: did.', NULL, 'Past Simple', 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(8, 1, 4, 'fill_blank', 'I ___ call you tomorrow.', 'will', 'am', 'was', 'did', 'will', 'I will call you tomorrow.', NULL, 'smart', NULL, 'Tomorrow shows future plan. Use will + base verb.', NULL, 'Future Simple', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(9, 2, 5, 'situation', 'You are late for class. What will you say to your teacher?', NULL, NULL, NULL, NULL, 'Sorry teacher I am late because of traffic', 'Sorry, teacher. I am late because of traffic. It will not happen again.', NULL, 'smart', NULL, 'Be polite and give a short reason.', NULL, NULL, 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(10, 2, 5, 'situation', 'You want to ask the price in a shop. What will you say?', NULL, NULL, NULL, NULL, 'What is the price of this', 'Excuse me, what is the price of this?', NULL, 'smart', NULL, 'Use Excuse me to sound polite.', NULL, NULL, 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(11, 2, 5, 'situation', 'You did not understand someone. What will you say?', NULL, NULL, NULL, NULL, 'Could you please repeat that', 'Sorry, could you please repeat that?', NULL, 'smart', NULL, 'This is more polite than saying What?', NULL, NULL, 'Beginner', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(12, 4, 6, 'situation', 'Interview question: Tell me about yourself.', NULL, NULL, NULL, NULL, 'My name is Rahul. I am from Jaunpur. I am learning spoken English to improve my confidence.', 'My name is Rahul. I am from Jaunpur. I have completed my studies and I am learning spoken English to improve my confidence and communication skills.', NULL, 'smart', NULL, 'Keep self introduction simple: name, place, education/work, goal.', NULL, NULL, 'Intermediate', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(13, 4, 6, 'situation', 'Interview question: Why do you want this job?', NULL, NULL, NULL, NULL, 'I want this job because I want to learn and grow', 'I want this job because it matches my skills and gives me a chance to learn, grow and contribute to the company.', NULL, 'smart', NULL, 'Connect your answer with skills, growth and contribution.', NULL, NULL, 'Intermediate', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(14, 3, 7, 'correction', 'Correct this sentence: I am go to market yesterday.', NULL, NULL, NULL, NULL, 'I went to the market yesterday', 'I went to the market yesterday.', NULL, 'smart', NULL, 'For yesterday, use Past Simple: went.', NULL, NULL, 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(15, 3, 7, 'correction', 'Correct this sentence: He go to school every day.', NULL, NULL, NULL, NULL, 'He goes to school every day', 'He goes to school every day.', NULL, 'smart', NULL, 'With He/She/It in Present Simple, use verb+s/es.', NULL, NULL, 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(16, 5, 8, 'voice', 'Speak this answer: I want to improve my English.', NULL, NULL, NULL, NULL, 'I want to improve my English', 'I want to improve my English.', NULL, 'smart', NULL, 'Speak slowly and clearly. Voice input works best in Chrome.', NULL, NULL, 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(17, 1, 9, 'fill_blank', 'I ___ tea every morning.', '', '', '', '', 'drink', 'I drink tea every morning.', NULL, 'smart', NULL, 'With I, use base verb in Present Simple.', NULL, 'Present Simple', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(18, 1, 9, 'fill_blank', 'She ___ English every day.', '', '', '', '', 'speaks', 'She speaks English every day.', NULL, 'smart', NULL, 'With She/He/It, add s/es to the verb.', NULL, 'Present Simple', 'Beginner', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(19, 1, 10, 'fill_blank', 'I ___ to the market yesterday.', '', '', '', '', 'went', 'I went to the market yesterday.', NULL, 'smart', NULL, 'Yesterday shows past time, so use went.', NULL, 'Past Simple', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(20, 1, 10, 'conversion', 'Convert to negative: I watched the class video.', '', '', '', '', 'I did not watch the class video.', 'I did not watch the class video.', NULL, 'smart', NULL, 'In Past Simple negative, use did not + base verb.', NULL, 'Past Simple', 'Beginner', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(21, 1, 11, 'fill_blank', 'They ___ speaking English now.', '', '', '', '', 'are', 'They are speaking English now.', NULL, 'smart', NULL, 'They uses are in Present Continuous.', NULL, 'Present Continuous', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(22, 2, 12, 'situation', 'You are late for class. What will you say to your teacher?', '', '', '', '', 'Sorry, teacher. I am late because there was traffic.', 'Sorry, teacher. I am late because there was traffic. It will not happen again.', NULL, 'smart', NULL, 'Use polite apology + clear reason + promise.', NULL, '', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(23, 2, 12, 'situation', 'You want to ask someone for help in English. What will you say?', '', '', '', '', 'Could you please help me?', 'Excuse me, could you please help me with this?', NULL, 'smart', NULL, 'Use could you please for polite requests.', NULL, '', 'Beginner', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(24, 2, 13, 'situation', 'Answer this interview question: Tell me about yourself.', '', '', '', '', 'My name is Rahul. I am a hardworking student and I want to improve my communication skills.', 'My name is Rahul. I have completed my studies and I am improving my English communication to build a better career.', NULL, 'smart', NULL, 'Keep your answer short, confident and relevant.', NULL, '', 'Intermediate', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(25, 3, 14, 'correction', 'Correct this sentence: I am go market yesterday.', '', '', '', '', 'I went to the market yesterday.', 'I went to the market yesterday.', NULL, 'smart', NULL, 'Yesterday needs Past Simple. Use went, not am go.', NULL, '', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(26, 6, 15, 'voice', 'Speak this sentence clearly: I want to improve my English speaking confidence.', '', '', '', '', 'I want to improve my English speaking confidence.', 'I want to improve my English speaking confidence.', NULL, 'smart', NULL, 'Speak slowly, clearly and repeat until the sentence feels natural.', NULL, '', 'All Levels', 1, 'Yes', 0, '2026-06-21 22:28:51');;
INSERT IGNORE INTO `practice_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'ai_correction_enabled', 'Yes', '2026-06-20 06:23:11'),
(2, 'ai_fallback_enabled', 'Yes', '2026-06-20 06:23:11'),
(3, 'ai_provider', 'openai', '2026-06-20 06:23:11'),
(4, 'openai_api_key', '', '2026-06-20 06:23:11'),
(5, 'openai_model', 'gpt-4o-mini', '2026-06-20 06:23:11'),
(6, 'openai_endpoint', 'https://api.openai.com/v1/chat/completions', '2026-06-20 06:23:11'),
(7, 'ai_daily_limit', '10', '2026-06-20 06:23:11'),
(8, 'ai_timeout_seconds', '18', '2026-06-20 06:23:11'),
(9, 'ai_temperature', '0.2', '2026-06-20 06:23:11'),
(10, 'ai_system_prompt', 'You are a friendly spoken English practice coach for Indian learners. Correct grammar, make answers natural, explain simply, and keep feedback short.', '2026-06-20 06:23:11'),
(11, 'practice_enabled', 'Yes', '2026-06-20 06:38:23'),
(12, 'local_mode_enabled', 'Yes', '2026-06-20 06:38:23'),
(13, 'browser_voice_enabled', 'Yes', '2026-06-20 06:38:23'),
(14, 'ai_enabled', 'No', '2026-06-20 06:38:23'),
(49, 'free_daily_limit', '20', '2026-06-21 22:28:51'),
(50, 'practice_intro_note', 'Start with free local practice. AI enhancement can be enabled later from settings without breaking the core practice engine.', '2026-06-21 22:28:51');;
INSERT IGNORE INTO `site_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Well Fare English Spoken', '2026-06-20 06:23:11'),
(2, 'site_logo', 'assets/uploads/brand/logo_20260620_191105_6c0c3e25.png', '2026-06-20 22:41:05'),
(3, 'site_favicon', 'assets/uploads/brand/favicon_20260620_191105_32a3449f.png', '2026-06-20 22:41:05'),
(4, 'brand_logo_alt', 'Institute logo', '2026-06-20 06:23:11'),
(5, 'brand_mark_mode', 'text', '2026-06-20 06:23:11'),
(6, 'phone', '9506617831', '2026-06-22 12:36:51'),
(7, 'whatsapp', '910000000000', '2026-06-20 06:23:11'),
(8, 'email', 'info@wellfare.local', '2026-06-20 06:23:11'),
(9, 'address', 'Station Road, Mariahu, Jaunpur', '2026-06-20 06:23:11'),
(10, 'map_url', 'https://www.google.com/maps', '2026-06-20 06:23:11'),
(11, 'hero_headline', 'Speak English confidently in daily life, interviews and career conversations.', '2026-06-20 06:23:11'),
(12, 'hero_subtitle', 'Join practical spoken English classes designed for students, job seekers, working professionals and homemakers who want real speaking confidence.', '2026-06-20 06:23:11'),
(13, 'admission_note', 'Admission open for spoken English, grammar, confidence and interview preparation batches.', '2026-06-20 06:23:11'),
(14, 'seo_home_title', 'Well Fare English Spoken | Spoken English Institute in Mariahu Jaunpur', '2026-06-20 06:23:11'),
(15, 'seo_home_description', 'Join practical spoken English, grammar, interview preparation and personality development classes at Well Fare English Spoken in Mariahu Jaunpur.', '2026-06-20 06:23:11'),
(16, 'seo_courses_title', 'Spoken English Courses | Well Fare English Spoken', '2026-06-20 06:23:11'),
(17, 'seo_courses_description', 'Explore beginner, advanced, grammar, interview and personality development English speaking courses.', '2026-06-20 06:23:11'),
(18, 'seo_admission_title', 'Admission Enquiry | Well Fare English Spoken', '2026-06-20 06:23:11'),
(19, 'seo_admission_description', 'Book free counselling for spoken English classes and get batch timing and course details.', '2026-06-20 06:23:11'),
(20, 'seo_contact_title', 'Contact Well Fare English Spoken', '2026-06-20 06:23:11'),
(21, 'seo_contact_description', 'Call, WhatsApp or visit Well Fare English Spoken for course details and admission counselling.', '2026-06-20 06:23:11'),
(22, 'seo_gallery_title', 'Gallery | Well Fare English Spoken', '2026-06-20 06:23:11'),
(23, 'seo_gallery_description', 'View classroom, activity and student practice photos from Well Fare English Spoken.', '2026-06-20 06:23:11'),
(24, 'seo_reviews_title', 'Student Reviews | Well Fare English Spoken', '2026-06-20 06:23:11'),
(25, 'seo_reviews_description', 'Read student reviews and feedback for spoken English classes.', '2026-06-20 06:23:11'),
(26, 'seo_about_title', 'About Well Fare English Spoken', '2026-06-20 06:23:11'),
(27, 'seo_about_description', 'Learn about Well Fare English Spoken, practical spoken English and confidence training institute.', '2026-06-20 06:23:11'),
(28, 'practice_tool_label', 'Free Smart English Practice Tool', '2026-06-20 06:23:11'),
(29, 'practice_tool_note', 'Free local practice works for everyone. Optional OpenAI can be enabled from admin for advanced feedback.', '2026-06-20 06:23:11'),
(30, 'brand_short', 'WF', '2026-06-20 06:23:11'),
(31, 'brand_title', 'Well Fare', '2026-06-20 06:23:11'),
(32, 'brand_subtitle', 'English Spoken', '2026-06-20 06:23:11'),
(33, 'facebook_url', '', '2026-06-20 06:23:11'),
(34, 'instagram_url', '', '2026-06-20 06:23:11'),
(35, 'youtube_url', '', '2026-06-20 06:23:11'),
(36, 'footer_about', 'Practical spoken English classes for students, job seekers and working professionals.', '2026-06-20 06:23:11'),
(37, 'footer_copyright', 'All rights reserved.', '2026-06-20 06:23:11'),
(38, 'hero_eyebrow', 'Trusted Spoken English Institute in Mariahu', '2026-06-20 06:23:11'),
(39, 'hero_primary_text', 'Book Free Counselling', '2026-06-20 06:23:11'),
(40, 'hero_primary_url', 'admission.php', '2026-06-20 06:23:11'),
(41, 'hero_secondary_text', 'Call Now', '2026-06-20 06:23:11'),
(42, 'home_features_title', 'Built for students who want real speaking confidence.', '2026-06-20 06:23:11'),
(43, 'home_features_subtitle', 'Simple lessons, daily practice and guided correction make English easier for school students, college students, job seekers and working professionals.', '2026-06-20 06:23:11'),
(44, 'home_courses_title', 'Popular Courses', '2026-06-20 06:23:11'),
(45, 'home_courses_subtitle', 'Choose a course based on your current level, confidence and career goal.', '2026-06-20 06:23:11'),
(46, 'home_batches_eyebrow', 'Batch Timings', '2026-06-20 06:23:11'),
(47, 'home_batches_title', 'Choose a comfortable speaking practice batch.', '2026-06-20 06:23:11'),
(48, 'home_batches_subtitle', 'Admin-managed batch timings help students quickly decide when to join.', '2026-06-20 06:23:11'),
(49, 'home_gallery_title', 'Inside the institute', '2026-06-20 06:23:11'),
(50, 'home_gallery_subtitle', 'Show real classroom trust with admin-managed gallery photos.', '2026-06-20 06:23:11'),
(51, 'home_reviews_title', 'Student Reviews', '2026-06-20 06:23:11'),
(52, 'home_reviews_subtitle', 'Real testimonials can be managed from the admin panel.', '2026-06-20 06:23:11'),
(53, 'home_videos_title', 'Class Videos', '2026-06-20 06:23:11'),
(54, 'home_videos_subtitle', 'Add YouTube links from admin and they will appear here.', '2026-06-20 06:23:11'),
(55, 'home_faq_eyebrow', 'Common Questions', '2026-06-20 06:23:11'),
(56, 'home_faq_title', 'Before you join', '2026-06-20 06:23:11'),
(57, 'home_faq_subtitle', 'Answers to common admission and course questions.', '2026-06-20 06:23:11'),
(58, 'home_cta_title', 'Admission open for spoken English batches.', '2026-06-20 06:23:11'),
(59, 'admission_eyebrow', 'Admission Open', '2026-06-20 06:23:11'),
(60, 'admission_title', 'Book your free spoken English counselling call.', '2026-06-20 06:23:11'),
(61, 'admission_privacy_note', 'Your details are safe with us.', '2026-06-20 06:23:11'),
(62, 'admission_faq_title', 'Admission FAQs', '2026-06-20 06:23:11'),
(63, 'admission_faq_subtitle', 'Helpful answers managed from admin.', '2026-06-20 06:23:11'),
(64, 'about_eyebrow', 'About Institute', '2026-06-20 06:23:11'),
(65, 'about_title', 'About Well Fare English Spoken', '2026-06-20 06:23:11'),
(66, 'about_subtitle', 'A student-friendly English speaking institute focused on practical learning and confidence building.', '2026-06-20 06:23:11'),
(67, 'about_promise_title', 'Our teaching promise', '2026-06-20 06:23:11'),
(68, 'about_promise_body', 'Students do not need only theory. They need habit, correction and practice. We make English simple, practical and confidence-focused.', '2026-06-20 06:23:11'),
(69, 'courses_page_title', 'Choose the right spoken English course', '2026-06-20 06:23:11'),
(70, 'courses_page_subtitle', 'Every course is designed to improve confidence, grammar clarity and practical communication.', '2026-06-20 06:23:11'),
(71, 'gallery_page_title', 'Gallery', '2026-06-20 06:23:11'),
(72, 'gallery_page_subtitle', 'Classroom moments, student practice and institute activities managed from admin.', '2026-06-20 06:23:11'),
(73, 'reviews_page_title', 'Student Reviews', '2026-06-20 06:23:11'),
(74, 'reviews_page_subtitle', 'Student feedback and success stories managed from admin.', '2026-06-20 06:23:11'),
(75, 'contact_page_title', 'Contact the institute', '2026-06-20 06:23:11'),
(76, 'contact_page_subtitle', 'Call, WhatsApp or visit for admission counselling and batch details.', '2026-06-20 06:23:11'),
(306, 'seo_practice_title', 'Spoken English Practice Room | Tense and Speaking Practice', '2026-06-21 22:28:51'),
(307, 'seo_practice_description', 'Practise English tenses, sentences, situations and speaking for free with a local AI-style practice lab.', '2026-06-21 22:28:51'),
(308, 'practice_page_title', 'Spoken English Practice Room', '2026-06-21 22:28:51'),
(309, 'practice_page_subtitle', 'Practise tenses, daily situations, sentence correction and speaking confidence without login. The free practice engine works even without paid AI API.', '2026-06-21 22:28:51'),
(310, 'practice_cta_title', 'Want teacher guidance after practice?', '2026-06-21 22:28:51'),
(311, 'practice_cta_body', 'Share your practice score with the counsellor and book a free demo class for personal spoken English correction.', '2026-06-21 22:28:51');;
INSERT IGNORE INTO `testimonials` (`id`, `student_name`, `message`, `published`, `created_at`) VALUES
(1, 'Aman Singh', 'The classes helped me speak English without fear. Daily practice is very useful.', 'Yes', '2026-06-20 06:23:11'),
(2, 'Priya Yadav', 'Grammar and speaking both became easier. The teaching style is simple and clear.', 'Yes', '2026-06-20 06:23:11'),
(3, 'Rohit Verma', 'I improved my interview introduction and confidence after joining the course.', 'Yes', '2026-06-20 06:23:11'),
(4, 'Aman Singh', 'The classes helped me speak English without fear. Daily practice is very useful.', 'Yes', '2026-06-21 22:09:35'),
(5, 'Priya Yadav', 'Grammar and speaking both became easier. The teaching style is simple and clear.', 'Yes', '2026-06-21 22:09:35'),
(6, 'Rohit Verma', 'I improved my interview introduction and confidence after joining the course.', 'Yes', '2026-06-21 22:09:35');;
INSERT IGNORE INTO `translation_pairs` (`id`, `collection_id`, `unit_id`, `hindi_text`, `english_text`, `roman_text`, `tense_name`, `situation_tag`, `level`, `explanation`, `sort_order`, `published`, `status_deleted`, `created_at`, `accepted_english_answers`, `accepted_hindi_answers`, `answer_match_mode`) VALUES
(1, 2, 1, 'मैं रोज अंग्रेजी बोलने की कोशिश करता हूँ।', 'I try to speak English every day.', NULL, 'Present Simple', 'Daily Practice', 'Beginner', 'Use try to + base verb for habit/practice.', 1, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(2, 2, 1, 'मैंने कल अपना होमवर्क पूरा किया।', 'I completed my homework yesterday.', NULL, 'Past Simple', 'Daily Practice', 'Beginner', 'Yesterday shows past time, so use completed.', 2, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(3, 2, 1, 'मैं अभी अंग्रेजी सीख रहा हूँ।', 'I am learning English right now.', NULL, 'Present Continuous', 'Daily Practice', 'Beginner', 'Right now shows action happening now: am/is/are + verb-ing.', 3, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(4, 2, 1, 'क्या आप मेरी मदद कर सकते हैं?', 'Can you help me?', NULL, 'Modal Verb', 'Polite Speaking', 'Beginner', 'Can you is a simple polite request.', 4, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(5, 2, 1, 'मुझे इंटरव्यू के लिए अंग्रेजी सुधारनी है।', 'I want to improve my English for an interview.', NULL, 'Present Simple', 'Interview', 'Beginner', 'Want to + verb is used for goals.', 5, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(6, 2, 1, 'मैं पहले अंग्रेजी बोलने से डरता था।', 'I used to be afraid of speaking English.', NULL, 'Past Habit', 'Confidence', 'Intermediate', 'Used to describes a past habit or past condition.', 6, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(7, 2, 1, 'कृपया इस वाक्य को दोहराइए।', 'Please repeat this sentence.', NULL, 'Imperative', 'Classroom', 'Beginner', 'Please makes the command polite.', 7, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(8, 2, 1, 'मैं फोन पर स्पष्ट बोलना चाहता हूँ।', 'I want to speak clearly on the phone.', NULL, 'Present Simple', 'Phone English', 'Beginner', 'Clearly describes how you want to speak.', 8, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(9, 3, 2, 'मैं रोज अंग्रेजी बोलता हूँ।', 'I speak English every day.', 'Main roz angrezi bolta hoon.', 'Present Simple', 'Daily Practice', 'Beginner', 'Habit sentence: use Present Simple.', 1, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(10, 3, 2, 'मैं अंग्रेजी सीख रहा हूँ।', 'I am learning English.', 'Main angrezi seekh raha hoon.', 'Present Continuous', 'Learning', 'Beginner', 'Action happening now: am learning.', 2, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(11, 3, 2, 'मैं कल बाजार गया था।', 'I went to the market yesterday.', 'Main kal bazaar gaya tha.', 'Past Simple', 'Daily Practice', 'Beginner', 'Yesterday means past: went.', 3, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(12, 3, 2, 'मैं कल आपको फोन करूंगा।', 'I will call you tomorrow.', 'Main kal aapko phone karunga.', 'Future Simple', 'Phone Call', 'Beginner', 'Tomorrow means future: will call.', 4, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(13, 3, 2, 'कृपया इसे फिर से दोहराइए।', 'Please repeat it again.', 'Kripya ise phir se dohraiye.', 'Imperative', 'Classroom', 'Beginner', 'Polite request sentence.', 5, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(14, 3, 2, 'मुझे अंग्रेजी बोलने में झिझक होती है।', 'I hesitate while speaking English.', 'Mujhe angrezi bolne mein jhijhak hoti hai.', 'Present Simple', 'Confidence', 'Beginner', 'Use hesitate while + verb-ing.', 6, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(15, 3, 2, 'आपका नाम क्या है?', 'What is your name?', 'Aapka naam kya hai?', 'Question', 'Introduction', 'Beginner', 'Basic question sentence.', 8, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(16, 3, 2, 'मैं जौनपुर से हूँ।', 'I am from Jaunpur.', 'Main Jaunpur se hoon.', 'Present Simple', 'Introduction', 'Beginner', 'Use I am from + place.', 9, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(17, 3, 2, 'मुझे थोड़ा समय चाहिए।', 'I need some time.', 'Mujhe thoda samay chahiye.', 'Present Simple', 'Daily Practice', 'Beginner', 'Useful polite sentence.', 10, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(18, 3, 2, 'मैं अपनी अंग्रेजी सुधारना चाहता हूँ।', 'I want to improve my English.', 'Main apni angrezi sudharna chahta hoon.', 'Present Simple', 'Learning', 'Beginner', 'Use want to + base verb.', 11, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(19, 3, 2, 'मैंने आज नया शब्द सीखा।', 'I learned a new word today.', 'Maine aaj naya shabd seekha.', 'Past Simple', 'Learning', 'Beginner', 'Completed action today: learned.', 12, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(20, 3, 2, 'मैं अभी व्यस्त हूँ।', 'I am busy right now.', 'Main abhi vyast hoon.', 'Present Simple', 'Daily Practice', 'Beginner', 'Short daily-use sentence.', 13, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(21, 3, 2, 'कृपया धीरे बोलिए।', 'Please speak slowly.', 'Kripya dheere boliye.', 'Imperative', 'Conversation', 'Beginner', 'Polite request.', 14, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(22, 3, 2, 'मुझे समझ में नहीं आया।', 'I did not understand.', 'Mujhe samajh mein nahi aaya.', 'Past Simple', 'Conversation', 'Beginner', 'Use did not + base verb.', 15, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart');;
INSERT IGNORE INTO `videos` (`id`, `title`, `youtube_url`, `published`, `created_at`) VALUES
(1, 'English Speaking Practice Demo', 'https://www.youtube.com/watch?v=cuE2Jx9g5vk', 'Yes', '2026-06-20 06:23:11'),
(2, 'English Speaking Practice Demo', 'https://www.youtube.com/watch?v=cuE2Jx9g5vk', 'Yes', '2026-06-21 22:09:35');;

-- Faculty seed data.
INSERT INTO `faculty_members` (`faculty_name`,`designation`,`experience`,`qualification`,`short_bio`,`full_bio`,`expertise`,`image_url`,`sort_order`,`published`) SELECT 'Spoken English Trainer', 'Spoken English Faculty', '7+ Years', 'MA, B.Ed', 'Conversation, grammar and confidence building.', 'Practical spoken English teacher focused on daily-use conversation, sentence correction and confidence practice.', 'Conversation, Grammar, Pronunciation', '', 0, 'Yes' WHERE NOT EXISTS (SELECT 1 FROM `faculty_members` WHERE `faculty_name`='Spoken English Trainer');
INSERT INTO `faculty_members` (`faculty_name`,`designation`,`experience`,`qualification`,`short_bio`,`full_bio`,`expertise`,`image_url`,`sort_order`,`published`) SELECT 'Grammar Mentor', 'Grammar Faculty', '5+ Years', 'BA, Diploma', 'Grammar made easy with examples.', 'Helps students understand tense, uses and daily English patterns with simple practice.', 'Tense, Uses, Translation', '', 1, 'Yes' WHERE NOT EXISTS (SELECT 1 FROM `faculty_members` WHERE `faculty_name`='Grammar Mentor');
INSERT INTO `faculty_members` (`faculty_name`,`designation`,`experience`,`qualification`,`short_bio`,`full_bio`,`expertise`,`image_url`,`sort_order`,`published`) SELECT 'Interview Coach', 'Interview & Personality Faculty', '4+ Years', 'MBA, Communication Skills', 'Interview support and personality development.', 'Guides students for interview answers, speaking confidence and professional communication.', 'Interview, Personality, Speaking', '', 2, 'Yes' WHERE NOT EXISTS (SELECT 1 FROM `faculty_members` WHERE `faculty_name`='Interview Coach');

-- Default Basic / Previous / Upcoming weekly tests.
INSERT INTO `weekly_tests` (`title`,`test_type`,`requires_login`,`status`,`instructions`,`duration_minutes`,`total_questions`,`total_marks`,`published`) SELECT 'Basic Spoken Test','basic','No','active','Any visitor can try this basic spoken English test.',30,30,30,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0);
INSERT INTO `weekly_tests` (`title`,`test_type`,`requires_login`,`status`,`instructions`,`duration_minutes`,`total_questions`,`total_marks`,`published`) SELECT 'Previous Weekly Test','previous','No','active','For students who missed the weekly test day.',30,30,30,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0);
INSERT INTO `weekly_tests` (`title`,`test_type`,`requires_login`,`status`,`instructions`,`duration_minutes`,`total_questions`,`total_marks`,`published`) SELECT 'Upcoming Weekly Test','upcoming','Yes','draft','Login required. Admin can activate this when ready.',30,30,30,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,1,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=1 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,2,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=2 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,3,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=3 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,4,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=4 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,5,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=5 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,6,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=6 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,7,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=7 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,8,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=8 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,9,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=9 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,10,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=10 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,11,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=11 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,12,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=12 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,13,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=13 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,14,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=14 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,15,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=15 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,16,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=16 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,17,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=17 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,18,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=18 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,19,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=19 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,20,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=20 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,21,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=21 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,22,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=22 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,23,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=23 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,24,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=24 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,25,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=25 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,26,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=26 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,27,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=27 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,28,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=28 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,29,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=29 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='basic' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,30,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='basic' AND q.sort_order=30 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,1,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=1 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,2,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=2 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,3,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=3 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,4,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=4 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,5,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=5 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,6,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=6 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,7,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=7 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,8,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=8 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,9,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=9 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,10,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=10 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,11,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=11 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,12,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=12 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,13,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=13 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,14,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=14 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,15,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=15 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,16,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=16 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,17,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=17 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,18,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=18 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,19,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=19 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,20,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=20 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,21,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=21 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,22,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=22 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,23,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=23 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,24,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=24 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,25,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=25 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,26,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=26 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,27,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=27 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,28,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=28 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,29,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=29 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='previous' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,30,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='previous' AND q.sort_order=30 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,1,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=1 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,2,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=2 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,3,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=3 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,4,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=4 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,5,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=5 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,6,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=6 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,7,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=7 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,8,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=8 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,9,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=9 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,10,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=10 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,11,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=11 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,12,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=12 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,13,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=13 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,14,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=14 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,15,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=15 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,16,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=16 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,17,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=17 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,18,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=18 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,19,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=19 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,20,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=20 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,21,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=21 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,22,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=22 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,23,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=23 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,24,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=24 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.',1,25,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=25 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1,26,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=26 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।',1,27,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=27 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1,28,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=28 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today.',1,29,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=29 AND q.status_deleted=0);
INSERT INTO `weekly_test_questions` (`test_id`,`question_type`,`topic_name`,`level`,`question_text`,`expected_answer`,`marks`,`sort_order`,`published`) SELECT (SELECT `id` FROM `weekly_tests` WHERE `test_type`='upcoming' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1,30,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `weekly_test_questions` q JOIN `weekly_tests` t ON t.id=q.test_id WHERE t.test_type='upcoming' AND q.sort_order=30 AND q.status_deleted=0);

-- Default learning roadmap groups and units.
INSERT INTO `roadmap_groups` (`title`,`subtitle`,`description`,`icon`,`color`,`sort_order`,`published`) SELECT 'Foundation','Start with basic English building blocks','Words, pronouns, demonstratives and basic meanings.','📘','#1a3565',1,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_groups` WHERE `title`='Foundation' AND `status_deleted`=0);
INSERT INTO `roadmap_groups` (`title`,`subtitle`,`description`,`icon`,`color`,`sort_order`,`published`) SELECT 'Verb Mastery','Learn action words with forms','V1, V2, V3 and Hindi meaning for spoken practice.','⚡','#0f766e',2,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_groups` WHERE `title`='Verb Mastery' AND `status_deleted`=0);
INSERT INTO `roadmap_groups` (`title`,`subtitle`,`description`,`icon`,`color`,`sort_order`,`published`) SELECT 'Use-Based English','Daily spoken English structure','Has/have, should, can, could, must and similar uses.','🧩','#9a5b00',3,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0);
INSERT INTO `roadmap_groups` (`title`,`subtitle`,`description`,`icon`,`color`,`sort_order`,`published`) SELECT 'Tense Mastery','Speak in correct time','Present, past and future tense step by step.','⏱','#6d28d9',4,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_groups` WHERE `title`='Tense Mastery' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Foundation' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Basic Pronouns + This/That','I, Me, My + This, That, These, Those','Understand pronouns and pointing words in one foundation lesson.','meaning','Beginner','spoken-materials.php?roadmap=pronouns','🔤',10,1,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Foundation' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Demonstrative Words','This, That, These, Those','Learn pointing words for near, far, singular and plural objects.','meaning','Beginner','spoken-materials.php?roadmap=demonstrative','👉',10,2,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Demonstrative Words' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Foundation' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Daily Word Meaning','Common daily words','Learn daily-use word meanings before sentence practice.','meaning','Beginner','spoken-materials.php?goal=revision','📖',15,3,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Daily Word Meaning' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Verb Mastery' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Verb Forms V1 V2 V3','Go Went Gone','Learn verb forms with Hindi meaning and daily examples.','verb','Beginner','spoken-materials.php?goal=speak','⚡',20,4,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Verb Forms V1 V2 V3' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Use of Is / Am / Are','Present identity and state','Learn simple, negative and question forms.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=is%20am%20are','✅',20,5,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Use of Is / Am / Are' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Use of Has / Have','Possession and relation','Learn has/have in daily spoken English.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=has%20have','🧩',20,6,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Use of Has / Have' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Use of Was / Were','Past state','Learn was/were in simple and question sentences.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=was%20were','🕰',20,7,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Use of Was / Were' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Use of Has To / Have To','Compulsion / duty','Learn duty sentences: मुझे जाना है, उसे पढ़ना है.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=have%20to','🎯',20,8,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Use of Has To / Have To' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Use of Should / Should Have','Advice and past advice','Learn should, should not and should have patterns.','use','Intermediate','spoken-materials.php?goal=hindi_to_english&q=should','💡',25,9,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Use of Should / Should Have' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Use-Based English' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Use of Can / Could / Must','Ability, polite request, compulsion','Learn practical modal verbs for daily speaking.','use','Intermediate','spoken-materials.php?goal=hindi_to_english&q=can%20could%20must','🚀',25,10,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Use of Can / Could / Must' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Tense Mastery' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Present Simple','Daily habits','Learn present simple with simple, negative and questions.','tense','Beginner','spoken-materials.php?goal=hindi_to_english&q=present%20simple','🌱',30,11,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Present Simple' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Tense Mastery' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Present Continuous','Right now actions','Learn is/am/are + verb ing.','tense','Beginner','spoken-materials.php?goal=hindi_to_english&q=present%20continuous','🏃',30,12,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Present Continuous' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Tense Mastery' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Past Simple','Completed actions','Learn past daily sentences and questions.','tense','Intermediate','spoken-materials.php?goal=hindi_to_english&q=past%20simple','📌',30,13,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Past Simple' AND `status_deleted`=0);
INSERT INTO `roadmap_units` (`group_id`,`title`,`subtitle`,`description`,`unit_type`,`level`,`target_url`,`icon`,`reward_points`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_groups` WHERE `title`='Tense Mastery' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'Future Simple','Will / future actions','Learn future sentences for daily speaking.','tense','Intermediate','spoken-materials.php?goal=hindi_to_english&q=future','🔮',30,14,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_units` WHERE `title`='Future Simple' AND `status_deleted`=0);
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'I','I (मैं)','Me (मुझे)','My (मेरा/मेरी)','Mine (मेरा ही)','Myself (मैं खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',1,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='I' AND ri.col_6='pronoun');
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'We','We (हम)','Us (हमें)','Our (हमारा/हमारी)','Ours (हमारा ही)','Ourselves (हम खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',2,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='We' AND ri.col_6='pronoun');
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'You','You (तुम/आप)','You (तुम्हें)','Your (तुम्हारा/आपका)','Yours (तुम्हारा ही)','Yourself (तुम खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',3,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='You' AND ri.col_6='pronoun');
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'He','He (वह - Male)','Him (उसे)','His (उसका/उसकी)','His (उसका ही)','Himself (वह खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',4,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='He' AND ri.col_6='pronoun');
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'She','She (वह - Female)','Her (उसे)','Her (उसका/उसकी)','Hers (उसका ही)','Herself (वह खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',5,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='She' AND ri.col_6='pronoun');
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'It','It (यह - निर्जीव)','It (इसे)','Its (इसका/इसकी)','Not used','Itself (यह खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',6,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='It' AND ri.col_6='pronoun');
INSERT INTO `roadmap_items` (`unit_id`,`item_key`,`col_1`,`col_2`,`col_3`,`col_4`,`col_5`,`col_6`,`example_text`,`sort_order`,`published`) SELECT (SELECT `id` FROM `roadmap_units` WHERE `title`='Basic Pronouns + This/That' AND `status_deleted`=0 ORDER BY `id` LIMIT 1),'They','They (वे/उन्होंने)','Them (उन्हें)','Their (उनका/उनकी)','Theirs (उनका ही)','Themselves (वे खुद)','pronoun','Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive',7,'Yes' WHERE NOT EXISTS (SELECT 1 FROM `roadmap_items` ri JOIN `roadmap_units` ru ON ru.id=ri.unit_id WHERE ru.title='Basic Pronouns + This/That' AND ri.item_key='They' AND ri.col_6='pronoun');

-- Required settings and schema markers.
INSERT INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('schema_marker','phase126_home_roadmap_v1') ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
INSERT INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('material_schema_marker','phase84_material_schema_v1') ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
INSERT INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('weekly_schema_marker','phase122_weekly_schema_v1') ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
INSERT INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('roadmap_schema_marker','phase122_roadmap_schema_v1') ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
INSERT INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('project_phase_marker','phase136_regression_repair_v1') ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
-- Defaults below use INSERT IGNORE so an upgrade never erases live website values.
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('site_tagline','Learn • Practice • Test • Improve');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('facebook_url','');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('instagram_url','');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('youtube_url','');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('linkedin_url','');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('twitter_url','');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('footer_about','Practical spoken English classes for students, job seekers and working professionals.');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('contact_office_time','Call or visit for admission guidance.');
INSERT IGNORE INTO `site_settings` (`setting_key`,`setting_value`) VALUES ('footer_copyright','All rights reserved.');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- End of unified database file.
