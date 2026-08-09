-- Phase 153: repair stale legacy owner password gate without weakening staff temporary-password enforcement.
SET NAMES utf8mb4;
START TRANSACTION;

SET @wf_owner_id := (
  SELECT a.id
  FROM admins a
  JOIN admin_roles r ON r.id=a.role_id
  WHERE a.published='Yes' AND r.published='Yes' AND r.role_key='super_admin'
  ORDER BY a.id ASC LIMIT 1
);
UPDATE admins SET must_change_password='No'
WHERE id=@wf_owner_id AND @wf_owner_id IS NOT NULL;

INSERT INTO schema_migrations(version,description,applied_at)
VALUES ('20260808_001_phase153','Admin access flow repair: protected owner legacy password gate cleared; staff temporary-password gate preserved',NOW())
ON DUPLICATE KEY UPDATE description=VALUES(description);
COMMIT;
