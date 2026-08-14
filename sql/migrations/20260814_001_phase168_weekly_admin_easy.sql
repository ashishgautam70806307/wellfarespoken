-- Phase 168: Weekly Test admin usability + Batch Management compatibility.
-- Safe to run on an existing database. No test/attempt data is deleted.
SET NAMES utf8mb4;
START TRANSACTION;

ALTER TABLE `batch_timings`
  ADD COLUMN IF NOT EXISTS `course_id` INT UNSIGNED NULL AFTER `id`;

INSERT INTO schema_migrations(version,description,applied_at)
VALUES (
  '20260814_001_phase168',
  'Weekly Test easy admin flow and batch_timings.course_id compatibility repair',
  NOW()
)
ON DUPLICATE KEY UPDATE description=VALUES(description);

COMMIT;
