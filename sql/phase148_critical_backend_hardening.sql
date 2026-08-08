-- Well Fare English Spoken - Phase 148 critical backend hardening
-- Run once on an existing Phase 147 database after taking a backup.
SET NAMES utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `version` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1) Authentication / RBAC / audit / rate limiting
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `role_id` INT UNSIGNED NULL AFTER `id`;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `auth_version` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `password_hash`;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `must_change_password` ENUM('Yes','No') NOT NULL DEFAULT 'No' AFTER `auth_version`;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `mfa_secret` VARCHAR(128) NULL AFTER `must_change_password`;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `mfa_enabled` ENUM('Yes','No') NOT NULL DEFAULT 'No' AFTER `mfa_secret`;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `password_changed_at` DATETIME NULL AFTER `mfa_enabled`;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `last_login_at` DATETIME NULL AFTER `password_changed_at`;

-- Existing installations from older phases may still contain the predictable legacy seed.
-- Disable only the exact untouched legacy seed. If its password was already changed, keep the real owner account active but force another secure password change.
UPDATE `admins` SET `published`='No', `must_change_password`='Yes'
WHERE LOWER(`email`)='admin@wellfare.local'
  AND `password_hash`='$2y$12$DHCToBguTMZptJEHcBMUGuoAErIOUDX45NhgtxRT6i9LPRaojvz5u';
UPDATE `admins` SET `must_change_password`='Yes'
WHERE LOWER(`email`)='admin@wellfare.local' AND `published`='Yes';

CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(100) NOT NULL,
  `role_key` VARCHAR(100) NOT NULL,
  `is_system` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `published` ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_admin_role_key` (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_key` VARCHAR(120) NOT NULL,
  `permission_label` VARCHAR(160) NOT NULL,
  `permission_group` VARCHAR(100) NOT NULL DEFAULT 'General',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_admin_permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`,`permission_id`), KEY `idx_arp_permission` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_audit_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` INT UNSIGNED NULL,
  `event_type` VARCHAR(80) NOT NULL,
  `entity_type` VARCHAR(80) NULL,
  `entity_id` VARCHAR(80) NULL,
  `event_note` TEXT NULL,
  `request_path` VARCHAR(255) NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `idx_admin_audit_admin` (`admin_id`,`created_at`), KEY `idx_admin_audit_event` (`event_type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `security_rate_limits` (
  `bucket_key` CHAR(64) NOT NULL,
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `window_started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blocked_until` DATETIME NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`bucket_key`), KEY `idx_rate_blocked` (`blocked_until`), KEY `idx_rate_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_roles` (`role_name`,`role_key`,`is_system`,`published`) VALUES
('Super Admin','super_admin','Yes','Yes'),('Manager','manager','Yes','Yes'),('Academic Manager','academic_manager','Yes','Yes'),('Content Editor','content_editor','Yes','Yes')
ON DUPLICATE KEY UPDATE role_name=VALUES(role_name), published='Yes';

INSERT INTO `admin_permissions` (`permission_key`,`permission_label`,`permission_group`) VALUES
('dashboard.view','View Dashboard','Main'),
('enquiries.manage','Manage Enquiries','CRM'),
('admissions.manage','Manage Admissions & Payments','CRM'),
('students.manage','Manage Student Accounts','CRM'),
('courses.manage','Manage Courses','Academic'),
('batches.manage','Manage Batches','Academic'),
('materials.manage','Manage Study Materials','Academic'),
('roadmap.manage','Manage Learning Roadmap','Academic'),
('tests.manage','Manage Weekly Tests','Academic'),
('content.manage','Manage Website Content','Website'),
('settings.manage','Manage Site Settings','System'),
('system.manage','View System Check / UI Library','System'),
('admins.manage','Manage Admin Users & Roles','System')
ON DUPLICATE KEY UPDATE permission_label=VALUES(permission_label), permission_group=VALUES(permission_group);

-- Super Admin receives every permission.
INSERT IGNORE INTO `admin_role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM admin_roles r CROSS JOIN admin_permissions p WHERE r.role_key='super_admin';
-- Manager: daily CRM/academic operations, not system/admin management.
INSERT IGNORE INTO `admin_role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM admin_roles r JOIN admin_permissions p ON p.permission_key IN ('dashboard.view','enquiries.manage','admissions.manage','students.manage','courses.manage','batches.manage','materials.manage','roadmap.manage','tests.manage','content.manage') WHERE r.role_key='manager';
-- Academic Manager.
INSERT IGNORE INTO `admin_role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM admin_roles r JOIN admin_permissions p ON p.permission_key IN ('dashboard.view','students.manage','courses.manage','batches.manage','materials.manage','roadmap.manage','tests.manage') WHERE r.role_key='academic_manager';
-- Content Editor.
INSERT IGNORE INTO `admin_role_permissions` (`role_id`,`permission_id`)
SELECT r.id,p.id FROM admin_roles r JOIN admin_permissions p ON p.permission_key IN ('dashboard.view','content.manage') WHERE r.role_key='content_editor';

-- Existing installations must end with exactly one protected Super Admin owner.
-- Choose the oldest active existing Super Admin first; otherwise the oldest active admin; otherwise the oldest admin.
SET @wf_super_role_id := (SELECT id FROM admin_roles WHERE role_key='super_admin' LIMIT 1);
SET @wf_manager_role_id := (SELECT id FROM admin_roles WHERE role_key='manager' LIMIT 1);
SET @wf_primary_owner_id := COALESCE(
  (SELECT MIN(a.id) FROM admins a WHERE a.published='Yes' AND a.role_id=@wf_super_role_id),
  (SELECT MIN(a.id) FROM admins a WHERE a.published='Yes'),
  (SELECT MIN(a.id) FROM admins a)
);
-- Any legacy/unassigned administrator other than the chosen owner becomes Manager, never Super Admin.
UPDATE admins
SET role_id=@wf_manager_role_id
WHERE id<>COALESCE(@wf_primary_owner_id,0) AND (role_id IS NULL OR role_id=@wf_super_role_id);
UPDATE admins
SET role_id=@wf_super_role_id, published='Yes'
WHERE id=@wf_primary_owner_id;
-- Administrator management is owner-only even if an older/custom role accidentally received the permission.
DELETE rp FROM admin_role_permissions rp
JOIN admin_permissions p ON p.id=rp.permission_id AND p.permission_key='admins.manage'
JOIN admin_roles r ON r.id=rp.role_id
WHERE r.role_key<>'super_admin';

-- 2) Student self-registration governance (no paid OTP required)
ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `identity_status` ENUM('Unverified','Verified') NOT NULL DEFAULT 'Unverified' AFTER `email`;
ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `registration_source` VARCHAR(40) NOT NULL DEFAULT 'self' AFTER `identity_status`;
ALTER TABLE `students` ADD COLUMN IF NOT EXISTS `consent_at` DATETIME NULL AFTER `registration_source`;
UPDATE `students` SET `registration_source`='legacy' WHERE `consent_at` IS NULL AND `registration_source`='self';

-- 3) Stable Enquiry -> Admission -> Student -> Enrollment -> Batch lifecycle
ALTER TABLE `batch_timings` ADD COLUMN IF NOT EXISTS `course_id` INT UNSIGNED NULL AFTER `id`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `course_id` INT UNSIGNED NULL AFTER `phone`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `batch_id` INT UNSIGNED NULL AFTER `course_id`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `converted_admission_id` INT UNSIGNED NULL AFTER `admin_note`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `converted_at` DATETIME NULL AFTER `converted_admission_id`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `status_deleted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `converted_at`;
ALTER TABLE `enquiries` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL AFTER `status_deleted`;
ALTER TABLE `admissions` ADD COLUMN IF NOT EXISTS `enquiry_id` INT UNSIGNED NULL AFTER `id`;
ALTER TABLE `admissions` ADD COLUMN IF NOT EXISTS `student_id` INT UNSIGNED NULL AFTER `enquiry_id`;
ALTER TABLE `admissions` ADD COLUMN IF NOT EXISTS `course_id` INT UNSIGNED NULL AFTER `student_id`;
ALTER TABLE `admissions` ADD COLUMN IF NOT EXISTS `batch_id` INT UNSIGNED NULL AFTER `course_id`;

CREATE TABLE IF NOT EXISTS `student_enrollments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `admission_id` INT UNSIGNED NULL,
  `course_id` INT UNSIGNED NULL,
  `course_title_snapshot` VARCHAR(180) NULL,
  `enrollment_status` ENUM('Pending','Active','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `joined_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_enrollment_admission` (`admission_id`), KEY `idx_enrollment_student` (`student_id`,`enrollment_status`), KEY `idx_enrollment_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_batch_memberships` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `batch_id` INT UNSIGNED NULL,
  `batch_name_snapshot` VARCHAR(180) NULL,
  `membership_status` ENUM('Active','Left','Transferred') NOT NULL DEFAULT 'Active',
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `left_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `idx_membership_student` (`student_id`,`membership_status`), KEY `idx_membership_enrollment` (`enrollment_id`,`membership_status`), KEY `idx_membership_batch` (`batch_id`,`membership_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Immutable payment / receipt ledger
CREATE TABLE IF NOT EXISTS `admission_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admission_id` INT UNSIGNED NOT NULL,
  `entry_type` ENUM('Payment','Refund','Adjustment','Opening') NOT NULL DEFAULT 'Payment',
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `payment_mode` VARCHAR(80) NULL,
  `reference_no` VARCHAR(160) NULL,
  `receipt_no` VARCHAR(120) NULL,
  `note` TEXT NULL,
  `entry_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_id` INT UNSIGNED NULL,
  `reversed_entry_id` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_payment_receipt` (`receipt_no`), KEY `idx_payment_admission` (`admission_id`,`entry_date`), KEY `idx_payment_admin` (`admin_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `material_access_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NULL,
  `admin_id` INT UNSIGNED NULL,
  `access_type` ENUM('View','Download') NOT NULL DEFAULT 'View',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(500) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material_access_asset` (`asset_id`,`created_at`),
  KEY `idx_material_access_student` (`student_id`,`created_at`),
  KEY `idx_material_access_admin` (`admin_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preserve pre-ledger paid amounts as immutable opening entries.
INSERT INTO admission_payments (admission_id,entry_type,amount,payment_mode,reference_no,receipt_no,note,entry_date,admin_id)
SELECT a.id,'Opening',a.paid_amount,NULLIF(a.payment_mode,''),NULL,CONCAT('OPEN-',LPAD(a.id,8,'0')),'Opening balance migrated from pre-Phase-148 admission record; original receipt snapshot remains on admissions',COALESCE(a.updated_at,a.created_at),NULL
FROM admissions a
WHERE a.status_deleted=0 AND a.paid_amount>0
AND NOT EXISTS (SELECT 1 FROM admission_payments p WHERE p.admission_id=a.id);

-- Backfill stable IDs from legacy snapshots only when the text match is unambiguous.
UPDATE batch_timings b
JOIN (SELECT LOWER(TRIM(title)) COLLATE utf8mb4_unicode_ci match_key, MIN(id) id FROM courses GROUP BY LOWER(TRIM(title)) COLLATE utf8mb4_unicode_ci HAVING COUNT(*)=1) c
  ON c.match_key=(LOWER(TRIM(b.course_name)) COLLATE utf8mb4_unicode_ci)
SET b.course_id=c.id
WHERE b.course_id IS NULL AND b.course_name IS NOT NULL AND b.course_name<>'';

UPDATE enquiries e
JOIN (SELECT LOWER(TRIM(title)) COLLATE utf8mb4_unicode_ci match_key, MIN(id) id FROM courses GROUP BY LOWER(TRIM(title)) COLLATE utf8mb4_unicode_ci HAVING COUNT(*)=1) c
  ON c.match_key=(LOWER(TRIM(e.course_interest)) COLLATE utf8mb4_unicode_ci)
SET e.course_id=c.id
WHERE e.course_id IS NULL AND e.course_interest IS NOT NULL AND e.course_interest<>'';

UPDATE admissions a
JOIN (SELECT LOWER(TRIM(title)) COLLATE utf8mb4_unicode_ci match_key, MIN(id) id FROM courses GROUP BY LOWER(TRIM(title)) COLLATE utf8mb4_unicode_ci HAVING COUNT(*)=1) c
  ON c.match_key=(LOWER(TRIM(a.course_interest)) COLLATE utf8mb4_unicode_ci)
SET a.course_id=c.id
WHERE a.course_id IS NULL AND a.course_interest IS NOT NULL AND a.course_interest<>'';

UPDATE enquiries e
JOIN (
  SELECT match_key, MIN(id) id FROM (
    SELECT id, LOWER(TRIM(batch_name)) COLLATE utf8mb4_unicode_ci match_key FROM batch_timings
    UNION ALL
    SELECT id, LOWER(TRIM(CONCAT(batch_name,' - ',COALESCE(timing,'')))) COLLATE utf8mb4_unicode_ci match_key FROM batch_timings
  ) batch_lookup
  WHERE match_key<>'' GROUP BY match_key HAVING COUNT(DISTINCT id)=1
) b ON b.match_key=(LOWER(TRIM(e.preferred_batch)) COLLATE utf8mb4_unicode_ci)
SET e.batch_id=b.id
WHERE e.batch_id IS NULL AND e.preferred_batch IS NOT NULL AND e.preferred_batch<>'';

UPDATE admissions a
JOIN (
  SELECT match_key, MIN(id) id FROM (
    SELECT id, LOWER(TRIM(batch_name)) COLLATE utf8mb4_unicode_ci match_key FROM batch_timings
    UNION ALL
    SELECT id, LOWER(TRIM(CONCAT(batch_name,' - ',COALESCE(timing,'')))) COLLATE utf8mb4_unicode_ci match_key FROM batch_timings
  ) batch_lookup
  WHERE match_key<>'' GROUP BY match_key HAVING COUNT(DISTINCT id)=1
) b ON b.match_key=(LOWER(TRIM(a.batch_preference)) COLLATE utf8mb4_unicode_ci)
SET a.batch_id=b.id
WHERE a.batch_id IS NULL AND a.batch_preference IS NOT NULL AND a.batch_preference<>'';

UPDATE admissions a
JOIN students s ON RIGHT(REPLACE(REPLACE(REPLACE(a.phone,' ',''),'-',''),'+91',''),10)=RIGHT(REPLACE(REPLACE(REPLACE(s.phone,' ',''),'-',''),'+91',''),10)
SET a.student_id=s.id
WHERE a.student_id IS NULL AND a.status_deleted=0 AND s.status_deleted=0 AND s.identity_status='Verified';

-- Preserve already-known enquiry/admission conversion links in both directions.
UPDATE admissions a JOIN enquiries e ON e.converted_admission_id=a.id
SET a.enquiry_id=e.id WHERE a.enquiry_id IS NULL AND e.status_deleted=0;
UPDATE enquiries e JOIN admissions a ON a.enquiry_id=e.id
SET e.converted_admission_id=a.id, e.converted_at=COALESCE(e.converted_at,a.created_at), e.enquiry_status='Converted'
WHERE e.converted_admission_id IS NULL AND e.status_deleted=0 AND a.status_deleted=0;

-- Backfill enrollment and active batch membership history for existing linked admissions.
INSERT IGNORE INTO student_enrollments
  (student_id,admission_id,course_id,course_title_snapshot,enrollment_status,joined_at,created_at)
SELECT a.student_id,a.id,a.course_id,a.course_interest,
       CASE WHEN a.admission_status='Joined' THEN 'Active'
            WHEN a.admission_status IN ('Cancelled','Not Approved') THEN 'Cancelled'
            ELSE 'Pending' END,
       CASE WHEN a.admission_status='Joined' THEN COALESCE(CONCAT(a.admission_date,' 00:00:00'),a.created_at) ELSE NULL END,
       COALESCE(a.created_at,NOW())
FROM admissions a
WHERE a.status_deleted=0 AND a.student_id IS NOT NULL;

UPDATE student_enrollments se
JOIN admissions a ON a.id=se.admission_id AND a.status_deleted=0
SET se.student_id=a.student_id,
    se.course_id=a.course_id,
    se.course_title_snapshot=a.course_interest,
    se.enrollment_status=CASE WHEN a.admission_status='Joined' THEN 'Active'
                              WHEN a.admission_status IN ('Cancelled','Not Approved') THEN 'Cancelled'
                              ELSE 'Pending' END,
    se.joined_at=CASE WHEN a.admission_status='Joined' THEN COALESCE(se.joined_at,CONCAT(a.admission_date,' 00:00:00'),a.created_at) ELSE se.joined_at END
WHERE a.student_id IS NOT NULL;

UPDATE student_batch_memberships sbm
JOIN student_enrollments se ON se.id=sbm.enrollment_id
JOIN admissions a ON a.id=se.admission_id
SET sbm.membership_status='Left', sbm.left_at=COALESCE(sbm.left_at,NOW())
WHERE sbm.membership_status='Active'
  AND (se.enrollment_status='Cancelled' OR a.batch_id IS NULL OR sbm.batch_id<>a.batch_id);

INSERT INTO student_batch_memberships
  (enrollment_id,student_id,batch_id,batch_name_snapshot,membership_status,joined_at,created_at)
SELECT se.id,se.student_id,a.batch_id,b.batch_name,'Active',
       COALESCE(se.joined_at,a.created_at,NOW()),COALESCE(a.created_at,NOW())
FROM student_enrollments se
JOIN admissions a ON a.id=se.admission_id AND a.status_deleted=0
JOIN batch_timings b ON b.id=a.batch_id
WHERE se.enrollment_status<>'Cancelled'
  AND NOT EXISTS (
    SELECT 1 FROM student_batch_memberships sbm
    WHERE sbm.enrollment_id=se.id AND sbm.batch_id=a.batch_id AND sbm.membership_status='Active'
  );

-- 5) Safe FK preparation: preserve orphan facts in an audit table, then null only the broken reference.
CREATE TABLE IF NOT EXISTS `data_integrity_orphans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `table_name` VARCHAR(100) NOT NULL,
  `row_id` BIGINT UNSIGNED NOT NULL,
  `relation_name` VARCHAR(120) NOT NULL,
  `orphan_value` BIGINT NULL,
  `detected_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_orphan_once` (`table_name`,`row_id`,`relation_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nullable FK columns for legacy rows that used zero as "not linked".
ALTER TABLE `course_variants` MODIFY `course_id` INT UNSIGNED NULL;
ALTER TABLE `practice_lessons` MODIFY `category_id` INT UNSIGNED NULL;
ALTER TABLE `practice_questions` MODIFY `category_id` INT UNSIGNED NULL, MODIFY `lesson_id` INT UNSIGNED NULL;
ALTER TABLE `practice_attempts` MODIFY `question_id` INT UNSIGNED NULL;
ALTER TABLE `practice_ai_logs` MODIFY `question_id` INT UNSIGNED NULL;
ALTER TABLE `material_assets` MODIFY `collection_id` INT UNSIGNED NULL;
ALTER TABLE `material_units` MODIFY `collection_id` INT UNSIGNED NULL;
ALTER TABLE `translation_pairs` MODIFY `collection_id` INT UNSIGNED NULL, MODIFY `unit_id` INT UNSIGNED NULL;
ALTER TABLE `material_practice_attempts` MODIFY `student_id` INT UNSIGNED NULL, MODIFY `pair_id` INT UNSIGNED NULL;
ALTER TABLE `roadmap_units` MODIFY `group_id` INT UNSIGNED NULL, MODIFY `unlock_after_unit_id` INT UNSIGNED NULL;
ALTER TABLE `roadmap_items` MODIFY `unit_id` INT UNSIGNED NULL;
ALTER TABLE `student_roadmap_progress` MODIFY `student_id` INT UNSIGNED NULL, MODIFY `unit_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_answers` MODIFY `question_id` INT UNSIGNED NULL;
ALTER TABLE `student_account_events` MODIFY `student_id` INT UNSIGNED NULL;
ALTER TABLE `student_activity_logs` MODIFY `student_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_questions` MODIFY `test_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_attempts` MODIFY `test_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_answers` MODIFY `attempt_id` INT UNSIGNED NULL;
ALTER TABLE `weekly_test_winners` MODIFY `test_id` INT UNSIGNED NULL, MODIFY `attempt_id` INT UNSIGNED NULL;

-- Convert legacy zeros to NULL.
UPDATE practice_lessons SET category_id=NULL WHERE category_id=0;
UPDATE practice_questions SET category_id=NULL WHERE category_id=0;
UPDATE practice_questions SET lesson_id=NULL WHERE lesson_id=0;
UPDATE practice_attempts SET question_id=NULL WHERE question_id=0;
UPDATE practice_ai_logs SET question_id=NULL WHERE question_id=0;
UPDATE material_assets SET collection_id=NULL WHERE collection_id=0;
UPDATE material_units SET collection_id=NULL WHERE collection_id=0;
UPDATE translation_pairs SET collection_id=NULL WHERE collection_id=0;
UPDATE translation_pairs SET unit_id=NULL WHERE unit_id=0;
UPDATE material_practice_attempts SET student_id=NULL WHERE student_id=0;
UPDATE material_practice_attempts SET pair_id=NULL WHERE pair_id=0;
UPDATE roadmap_units SET group_id=NULL WHERE group_id=0;
UPDATE roadmap_units SET unlock_after_unit_id=NULL WHERE unlock_after_unit_id=0;
UPDATE roadmap_items SET unit_id=NULL WHERE unit_id=0;
UPDATE student_roadmap_progress SET student_id=NULL WHERE student_id=0;
UPDATE student_roadmap_progress SET unit_id=NULL WHERE unit_id=0;

-- Archive + null broken legacy relations that would block FK creation.
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'course_variants',v.id,'course_id',v.course_id FROM course_variants v LEFT JOIN courses p ON p.id=v.course_id WHERE v.course_id IS NOT NULL AND p.id IS NULL;
UPDATE course_variants v LEFT JOIN courses p ON p.id=v.course_id SET v.course_id=NULL WHERE v.course_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'practice_lessons',v.id,'category_id',v.category_id FROM practice_lessons v LEFT JOIN practice_categories p ON p.id=v.category_id WHERE v.category_id IS NOT NULL AND p.id IS NULL;
UPDATE practice_lessons v LEFT JOIN practice_categories p ON p.id=v.category_id SET v.category_id=NULL WHERE v.category_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'practice_questions',v.id,'lesson_id',v.lesson_id FROM practice_questions v LEFT JOIN practice_lessons p ON p.id=v.lesson_id WHERE v.lesson_id IS NOT NULL AND p.id IS NULL;
UPDATE practice_questions v LEFT JOIN practice_lessons p ON p.id=v.lesson_id SET v.lesson_id=NULL WHERE v.lesson_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_answers',v.id,'question_id',v.question_id FROM weekly_test_answers v LEFT JOIN weekly_test_questions p ON p.id=v.question_id WHERE v.question_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_answers v LEFT JOIN weekly_test_questions p ON p.id=v.question_id SET v.question_id=NULL WHERE v.question_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'roadmap_units',v.id,'group_id',v.group_id FROM roadmap_units v LEFT JOIN roadmap_groups p ON p.id=v.group_id WHERE v.group_id IS NOT NULL AND p.id IS NULL;
UPDATE roadmap_units v LEFT JOIN roadmap_groups p ON p.id=v.group_id SET v.group_id=NULL WHERE v.group_id IS NOT NULL AND p.id IS NULL;

-- Clean stable relationship columns as well (important for partially upgraded installations).
UPDATE admins a LEFT JOIN admin_roles p ON p.id=a.role_id SET a.role_id=NULL WHERE a.role_id IS NOT NULL AND p.id IS NULL;
UPDATE batch_timings v LEFT JOIN courses p ON p.id=v.course_id SET v.course_id=NULL WHERE v.course_id IS NOT NULL AND p.id IS NULL;
UPDATE enquiries v LEFT JOIN courses p ON p.id=v.course_id SET v.course_id=NULL WHERE v.course_id IS NOT NULL AND p.id IS NULL;
UPDATE enquiries v LEFT JOIN batch_timings p ON p.id=v.batch_id SET v.batch_id=NULL WHERE v.batch_id IS NOT NULL AND p.id IS NULL;
UPDATE enquiries v LEFT JOIN admissions p ON p.id=v.converted_admission_id SET v.converted_admission_id=NULL WHERE v.converted_admission_id IS NOT NULL AND p.id IS NULL;
UPDATE admissions v LEFT JOIN enquiries p ON p.id=v.enquiry_id SET v.enquiry_id=NULL WHERE v.enquiry_id IS NOT NULL AND p.id IS NULL;
UPDATE admissions v LEFT JOIN students p ON p.id=v.student_id SET v.student_id=NULL WHERE v.student_id IS NOT NULL AND p.id IS NULL;
UPDATE admissions v LEFT JOIN courses p ON p.id=v.course_id SET v.course_id=NULL WHERE v.course_id IS NOT NULL AND p.id IS NULL;
UPDATE admissions v LEFT JOIN batch_timings p ON p.id=v.batch_id SET v.batch_id=NULL WHERE v.batch_id IS NOT NULL AND p.id IS NULL;
UPDATE admin_audit_events v LEFT JOIN admins p ON p.id=v.admin_id SET v.admin_id=NULL WHERE v.admin_id IS NOT NULL AND p.id IS NULL;
UPDATE student_account_events v LEFT JOIN admins p ON p.id=v.admin_id SET v.admin_id=NULL WHERE v.admin_id IS NOT NULL AND p.id IS NULL;
UPDATE admission_payments v LEFT JOIN admins p ON p.id=v.admin_id SET v.admin_id=NULL WHERE v.admin_id IS NOT NULL AND p.id IS NULL;

-- Complete orphan audit for every relation that will receive a foreign key.
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'practice_questions',v.id,'category_id',v.category_id FROM practice_questions v LEFT JOIN practice_categories p ON p.id=v.category_id WHERE v.category_id IS NOT NULL AND p.id IS NULL;
UPDATE practice_questions v LEFT JOIN practice_categories p ON p.id=v.category_id SET v.category_id=NULL WHERE v.category_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'practice_attempts',v.id,'question_id',v.question_id FROM practice_attempts v LEFT JOIN practice_questions p ON p.id=v.question_id WHERE v.question_id IS NOT NULL AND p.id IS NULL;
UPDATE practice_attempts v LEFT JOIN practice_questions p ON p.id=v.question_id SET v.question_id=NULL WHERE v.question_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'practice_ai_logs',v.id,'question_id',v.question_id FROM practice_ai_logs v LEFT JOIN practice_questions p ON p.id=v.question_id WHERE v.question_id IS NOT NULL AND p.id IS NULL;
UPDATE practice_ai_logs v LEFT JOIN practice_questions p ON p.id=v.question_id SET v.question_id=NULL WHERE v.question_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'material_assets',v.id,'collection_id',v.collection_id FROM material_assets v LEFT JOIN material_collections p ON p.id=v.collection_id WHERE v.collection_id IS NOT NULL AND p.id IS NULL;
UPDATE material_assets v LEFT JOIN material_collections p ON p.id=v.collection_id SET v.collection_id=NULL WHERE v.collection_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'material_units',v.id,'collection_id',v.collection_id FROM material_units v LEFT JOIN material_collections p ON p.id=v.collection_id WHERE v.collection_id IS NOT NULL AND p.id IS NULL;
UPDATE material_units v LEFT JOIN material_collections p ON p.id=v.collection_id SET v.collection_id=NULL WHERE v.collection_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'translation_pairs',v.id,'collection_id',v.collection_id FROM translation_pairs v LEFT JOIN material_collections p ON p.id=v.collection_id WHERE v.collection_id IS NOT NULL AND p.id IS NULL;
UPDATE translation_pairs v LEFT JOIN material_collections p ON p.id=v.collection_id SET v.collection_id=NULL WHERE v.collection_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'translation_pairs',v.id,'unit_id',v.unit_id FROM translation_pairs v LEFT JOIN material_units p ON p.id=v.unit_id WHERE v.unit_id IS NOT NULL AND p.id IS NULL;
UPDATE translation_pairs v LEFT JOIN material_units p ON p.id=v.unit_id SET v.unit_id=NULL WHERE v.unit_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'material_practice_attempts',v.id,'student_id',v.student_id FROM material_practice_attempts v LEFT JOIN students p ON p.id=v.student_id WHERE v.student_id IS NOT NULL AND p.id IS NULL;
UPDATE material_practice_attempts v LEFT JOIN students p ON p.id=v.student_id SET v.student_id=NULL WHERE v.student_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'material_practice_attempts',v.id,'pair_id',v.pair_id FROM material_practice_attempts v LEFT JOIN translation_pairs p ON p.id=v.pair_id WHERE v.pair_id IS NOT NULL AND p.id IS NULL;
UPDATE material_practice_attempts v LEFT JOIN translation_pairs p ON p.id=v.pair_id SET v.pair_id=NULL WHERE v.pair_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'roadmap_units',v.id,'unlock_after_unit_id',v.unlock_after_unit_id FROM roadmap_units v LEFT JOIN roadmap_units p ON p.id=v.unlock_after_unit_id WHERE v.unlock_after_unit_id IS NOT NULL AND p.id IS NULL;
UPDATE roadmap_units v LEFT JOIN roadmap_units p ON p.id=v.unlock_after_unit_id SET v.unlock_after_unit_id=NULL WHERE v.unlock_after_unit_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'roadmap_items',v.id,'unit_id',v.unit_id FROM roadmap_items v LEFT JOIN roadmap_units p ON p.id=v.unit_id WHERE v.unit_id IS NOT NULL AND p.id IS NULL;
UPDATE roadmap_items v LEFT JOIN roadmap_units p ON p.id=v.unit_id SET v.unit_id=NULL WHERE v.unit_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'student_roadmap_progress',v.id,'student_id',v.student_id FROM student_roadmap_progress v LEFT JOIN students p ON p.id=v.student_id WHERE v.student_id IS NOT NULL AND p.id IS NULL;
UPDATE student_roadmap_progress v LEFT JOIN students p ON p.id=v.student_id SET v.student_id=NULL WHERE v.student_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'student_roadmap_progress',v.id,'unit_id',v.unit_id FROM student_roadmap_progress v LEFT JOIN roadmap_units p ON p.id=v.unit_id WHERE v.unit_id IS NOT NULL AND p.id IS NULL;
UPDATE student_roadmap_progress v LEFT JOIN roadmap_units p ON p.id=v.unit_id SET v.unit_id=NULL WHERE v.unit_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'student_account_events',v.id,'student_id',v.student_id FROM student_account_events v LEFT JOIN students p ON p.id=v.student_id WHERE v.student_id IS NOT NULL AND p.id IS NULL;
UPDATE student_account_events v LEFT JOIN students p ON p.id=v.student_id SET v.student_id=NULL WHERE v.student_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'student_activity_logs',v.id,'student_id',v.student_id FROM student_activity_logs v LEFT JOIN students p ON p.id=v.student_id WHERE v.student_id IS NOT NULL AND p.id IS NULL;
UPDATE student_activity_logs v LEFT JOIN students p ON p.id=v.student_id SET v.student_id=NULL WHERE v.student_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_tests',v.id,'batch_id',v.batch_id FROM weekly_tests v LEFT JOIN batch_timings p ON p.id=v.batch_id WHERE v.batch_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_tests v LEFT JOIN batch_timings p ON p.id=v.batch_id SET v.batch_id=NULL WHERE v.batch_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_questions',v.id,'test_id',v.test_id FROM weekly_test_questions v LEFT JOIN weekly_tests p ON p.id=v.test_id WHERE v.test_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_questions v LEFT JOIN weekly_tests p ON p.id=v.test_id SET v.test_id=NULL WHERE v.test_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_attempts',v.id,'test_id',v.test_id FROM weekly_test_attempts v LEFT JOIN weekly_tests p ON p.id=v.test_id WHERE v.test_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_attempts v LEFT JOIN weekly_tests p ON p.id=v.test_id SET v.test_id=NULL WHERE v.test_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_attempts',v.id,'student_id',v.student_id FROM weekly_test_attempts v LEFT JOIN students p ON p.id=v.student_id WHERE v.student_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_attempts v LEFT JOIN students p ON p.id=v.student_id SET v.student_id=NULL WHERE v.student_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_answers',v.id,'attempt_id',v.attempt_id FROM weekly_test_answers v LEFT JOIN weekly_test_attempts p ON p.id=v.attempt_id WHERE v.attempt_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_answers v LEFT JOIN weekly_test_attempts p ON p.id=v.attempt_id SET v.attempt_id=NULL WHERE v.attempt_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_winners',v.id,'test_id',v.test_id FROM weekly_test_winners v LEFT JOIN weekly_tests p ON p.id=v.test_id WHERE v.test_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_winners v LEFT JOIN weekly_tests p ON p.id=v.test_id SET v.test_id=NULL WHERE v.test_id IS NOT NULL AND p.id IS NULL;
INSERT IGNORE INTO data_integrity_orphans(table_name,row_id,relation_name,orphan_value) SELECT 'weekly_test_winners',v.id,'attempt_id',v.attempt_id FROM weekly_test_winners v LEFT JOIN weekly_test_attempts p ON p.id=v.attempt_id WHERE v.attempt_id IS NOT NULL AND p.id IS NULL;
UPDATE weekly_test_winners v LEFT JOIN weekly_test_attempts p ON p.id=v.attempt_id SET v.attempt_id=NULL WHERE v.attempt_id IS NOT NULL AND p.id IS NULL;

COMMIT;

-- Add constraints idempotently using a temporary procedure.
DELIMITER $$
DROP PROCEDURE IF EXISTS wf_add_fk$$
CREATE PROCEDURE wf_add_fk(IN p_name VARCHAR(64), IN p_sql TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND CONSTRAINT_NAME=p_name AND CONSTRAINT_TYPE='FOREIGN KEY') THEN
    SET @wf_sql=p_sql; PREPARE wf_stmt FROM @wf_sql; EXECUTE wf_stmt; DEALLOCATE PREPARE wf_stmt;
  END IF;
END$$
DELIMITER ;

CALL wf_add_fk('fk_admin_role','ALTER TABLE admins ADD CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_arp_role','ALTER TABLE admin_role_permissions ADD CONSTRAINT fk_arp_role FOREIGN KEY (role_id) REFERENCES admin_roles(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_arp_permission','ALTER TABLE admin_role_permissions ADD CONSTRAINT fk_arp_permission FOREIGN KEY (permission_id) REFERENCES admin_permissions(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_admin_audit_admin','ALTER TABLE admin_audit_events ADD CONSTRAINT fk_admin_audit_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_batch_course','ALTER TABLE batch_timings ADD CONSTRAINT fk_batch_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_enquiry_course','ALTER TABLE enquiries ADD CONSTRAINT fk_enquiry_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_enquiry_batch','ALTER TABLE enquiries ADD CONSTRAINT fk_enquiry_batch FOREIGN KEY (batch_id) REFERENCES batch_timings(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_enquiry_admission','ALTER TABLE enquiries ADD CONSTRAINT fk_enquiry_admission FOREIGN KEY (converted_admission_id) REFERENCES admissions(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_admission_enquiry','ALTER TABLE admissions ADD CONSTRAINT fk_admission_enquiry FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_admission_student','ALTER TABLE admissions ADD CONSTRAINT fk_admission_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_admission_course','ALTER TABLE admissions ADD CONSTRAINT fk_admission_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_admission_batch','ALTER TABLE admissions ADD CONSTRAINT fk_admission_batch FOREIGN KEY (batch_id) REFERENCES batch_timings(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_enrollment_student','ALTER TABLE student_enrollments ADD CONSTRAINT fk_enrollment_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_enrollment_admission','ALTER TABLE student_enrollments ADD CONSTRAINT fk_enrollment_admission FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_enrollment_course','ALTER TABLE student_enrollments ADD CONSTRAINT fk_enrollment_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT');
CALL wf_add_fk('fk_membership_enrollment','ALTER TABLE student_batch_memberships ADD CONSTRAINT fk_membership_enrollment FOREIGN KEY (enrollment_id) REFERENCES student_enrollments(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_membership_student','ALTER TABLE student_batch_memberships ADD CONSTRAINT fk_membership_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_membership_batch','ALTER TABLE student_batch_memberships ADD CONSTRAINT fk_membership_batch FOREIGN KEY (batch_id) REFERENCES batch_timings(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_payment_admission','ALTER TABLE admission_payments ADD CONSTRAINT fk_payment_admission FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE RESTRICT');
CALL wf_add_fk('fk_payment_admin','ALTER TABLE admission_payments ADD CONSTRAINT fk_payment_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_course_variant_course','ALTER TABLE course_variants ADD CONSTRAINT fk_course_variant_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_student_event_student','ALTER TABLE student_account_events ADD CONSTRAINT fk_student_event_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_student_event_admin','ALTER TABLE student_account_events ADD CONSTRAINT fk_student_event_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_student_activity_student','ALTER TABLE student_activity_logs ADD CONSTRAINT fk_student_activity_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_practice_lesson_category','ALTER TABLE practice_lessons ADD CONSTRAINT fk_practice_lesson_category FOREIGN KEY (category_id) REFERENCES practice_categories(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_practice_question_category','ALTER TABLE practice_questions ADD CONSTRAINT fk_practice_question_category FOREIGN KEY (category_id) REFERENCES practice_categories(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_practice_question_lesson','ALTER TABLE practice_questions ADD CONSTRAINT fk_practice_question_lesson FOREIGN KEY (lesson_id) REFERENCES practice_lessons(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_practice_attempt_question','ALTER TABLE practice_attempts ADD CONSTRAINT fk_practice_attempt_question FOREIGN KEY (question_id) REFERENCES practice_questions(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_practice_ai_question','ALTER TABLE practice_ai_logs ADD CONSTRAINT fk_practice_ai_question FOREIGN KEY (question_id) REFERENCES practice_questions(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_material_access_asset','ALTER TABLE material_access_logs ADD CONSTRAINT fk_material_access_asset FOREIGN KEY (asset_id) REFERENCES material_assets(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_material_access_student','ALTER TABLE material_access_logs ADD CONSTRAINT fk_material_access_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_material_access_admin','ALTER TABLE material_access_logs ADD CONSTRAINT fk_material_access_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_material_asset_collection','ALTER TABLE material_assets ADD CONSTRAINT fk_material_asset_collection FOREIGN KEY (collection_id) REFERENCES material_collections(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_material_unit_collection','ALTER TABLE material_units ADD CONSTRAINT fk_material_unit_collection FOREIGN KEY (collection_id) REFERENCES material_collections(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_translation_collection','ALTER TABLE translation_pairs ADD CONSTRAINT fk_translation_collection FOREIGN KEY (collection_id) REFERENCES material_collections(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_translation_unit','ALTER TABLE translation_pairs ADD CONSTRAINT fk_translation_unit FOREIGN KEY (unit_id) REFERENCES material_units(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_material_attempt_student','ALTER TABLE material_practice_attempts ADD CONSTRAINT fk_material_attempt_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_material_attempt_pair','ALTER TABLE material_practice_attempts ADD CONSTRAINT fk_material_attempt_pair FOREIGN KEY (pair_id) REFERENCES translation_pairs(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_weekly_batch','ALTER TABLE weekly_tests ADD CONSTRAINT fk_weekly_batch FOREIGN KEY (batch_id) REFERENCES batch_timings(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_weekly_question_test','ALTER TABLE weekly_test_questions ADD CONSTRAINT fk_weekly_question_test FOREIGN KEY (test_id) REFERENCES weekly_tests(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_weekly_attempt_test','ALTER TABLE weekly_test_attempts ADD CONSTRAINT fk_weekly_attempt_test FOREIGN KEY (test_id) REFERENCES weekly_tests(id) ON DELETE RESTRICT');
CALL wf_add_fk('fk_weekly_attempt_student','ALTER TABLE weekly_test_attempts ADD CONSTRAINT fk_weekly_attempt_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_weekly_answer_attempt','ALTER TABLE weekly_test_answers ADD CONSTRAINT fk_weekly_answer_attempt FOREIGN KEY (attempt_id) REFERENCES weekly_test_attempts(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_weekly_answer_question','ALTER TABLE weekly_test_answers ADD CONSTRAINT fk_weekly_answer_question FOREIGN KEY (question_id) REFERENCES weekly_test_questions(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_weekly_winner_test','ALTER TABLE weekly_test_winners ADD CONSTRAINT fk_weekly_winner_test FOREIGN KEY (test_id) REFERENCES weekly_tests(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_weekly_winner_attempt','ALTER TABLE weekly_test_winners ADD CONSTRAINT fk_weekly_winner_attempt FOREIGN KEY (attempt_id) REFERENCES weekly_test_attempts(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_roadmap_unit_group','ALTER TABLE roadmap_units ADD CONSTRAINT fk_roadmap_unit_group FOREIGN KEY (group_id) REFERENCES roadmap_groups(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_roadmap_unit_unlock','ALTER TABLE roadmap_units ADD CONSTRAINT fk_roadmap_unit_unlock FOREIGN KEY (unlock_after_unit_id) REFERENCES roadmap_units(id) ON DELETE SET NULL');
CALL wf_add_fk('fk_roadmap_item_unit','ALTER TABLE roadmap_items ADD CONSTRAINT fk_roadmap_item_unit FOREIGN KEY (unit_id) REFERENCES roadmap_units(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_progress_student','ALTER TABLE student_roadmap_progress ADD CONSTRAINT fk_progress_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE');
CALL wf_add_fk('fk_progress_unit','ALTER TABLE student_roadmap_progress ADD CONSTRAINT fk_progress_unit FOREIGN KEY (unit_id) REFERENCES roadmap_units(id) ON DELETE CASCADE');
DROP PROCEDURE IF EXISTS wf_add_fk;

-- Backfill ledger snapshots after migration.
UPDATE admissions a SET a.paid_amount=(SELECT COALESCE(SUM(CASE WHEN p.entry_type IN ('Payment','Opening','Adjustment') THEN p.amount WHEN p.entry_type='Refund' THEN -p.amount ELSE 0 END),0) FROM admission_payments p WHERE p.admission_id=a.id), a.payment_status=CASE WHEN (SELECT COALESCE(SUM(CASE WHEN p.entry_type IN ('Payment','Opening','Adjustment') THEN p.amount WHEN p.entry_type='Refund' THEN -p.amount ELSE 0 END),0) FROM admission_payments p WHERE p.admission_id=a.id)<=0 THEN 'Unpaid' WHEN (SELECT COALESCE(SUM(CASE WHEN p.entry_type IN ('Payment','Opening','Adjustment') THEN p.amount WHEN p.entry_type='Refund' THEN -p.amount ELSE 0 END),0) FROM admission_payments p WHERE p.admission_id=a.id) >= GREATEST(0,a.total_fee-a.discount_amount) THEN 'Paid' ELSE 'Partial' END WHERE a.status_deleted=0;

INSERT INTO schema_migrations(version,description,applied_at) VALUES ('20260807_001_phase148','Critical backend hardening: secrets, RBAC, rate limiting, lifecycle, ledger, foreign keys and private files',NOW()) ON DUPLICATE KEY UPDATE description=VALUES(description);
