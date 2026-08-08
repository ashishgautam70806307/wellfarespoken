-- Well Fare English Spoken - Phase 150 protected owner / RBAC correction
-- Run AFTER the corrected Phase 148 migration on an existing database.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @wf_super_role_id := (SELECT id FROM admin_roles WHERE role_key='super_admin' LIMIT 1);
SET @wf_manager_role_id := (SELECT id FROM admin_roles WHERE role_key='manager' LIMIT 1);
SET @wf_primary_owner_id := COALESCE(
  (SELECT MIN(a.id) FROM admins a WHERE a.published='Yes' AND a.role_id=@wf_super_role_id),
  (SELECT MIN(a.id) FROM admins a WHERE a.published='Yes'),
  (SELECT MIN(a.id) FROM admins a)
);

-- Keep exactly one owner. Any legacy duplicate/unassigned administrator becomes Manager.
UPDATE admins
SET role_id=@wf_manager_role_id
WHERE id<>COALESCE(@wf_primary_owner_id,0)
  AND (role_id IS NULL OR role_id=@wf_super_role_id);

UPDATE admins
SET role_id=@wf_super_role_id, published='Yes'
WHERE id=@wf_primary_owner_id;

-- No staff/custom role may retain administrator-management permission.
DELETE rp FROM admin_role_permissions rp
JOIN admin_permissions p ON p.id=rp.permission_id AND p.permission_key='admins.manage'
JOIN admin_roles r ON r.id=rp.role_id
WHERE r.role_key<>'super_admin';

-- Super Admin always has every permission, but application code only recognizes the oldest active one as owner.
INSERT IGNORE INTO admin_role_permissions(role_id,permission_id)
SELECT @wf_super_role_id,p.id FROM admin_permissions p WHERE @wf_super_role_id IS NOT NULL;

INSERT INTO schema_migrations(version,description,applied_at)
VALUES ('20260807_002_phase150','Single protected Super Admin owner, owner-only admin management and RBAC escalation lock',NOW())
ON DUPLICATE KEY UPDATE description=VALUES(description);
