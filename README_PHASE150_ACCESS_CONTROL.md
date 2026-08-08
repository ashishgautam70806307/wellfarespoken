# Phase 150 - Protected Owner RBAC + Mobile Topbar Restore

Phase 150 is a focused security and navigation correction on top of Phase 149.

## Core changes

- Restores the public top information bar on mobile with compact location, announcement, Institute Login and Call access.
- Changes pre-RBAC behavior from fail-open to fail-closed: before the migration, only Dashboard and System Check are available.
- Treats Super Admin as one protected institute-owner identity rather than a normal assignable staff role.
- Only the protected owner can open Admin Users, Roles & Permissions and Admin Audit Log.
- Admin Users never offers Super Admin as an assignable role and forged POST assignment is rejected server-side.
- Roles & Permissions cannot edit Super Admin and cannot grant `admins.manage` to any staff/custom role.
- Role changes invalidate affected staff sessions.
- First Admin Setup never reopens merely because legitimate administrators were deactivated; the exact historical predictable seed is the only setup exception.
- Dashboard cards, actions and recent data now follow the logged-in role permissions instead of exposing links/counts for unauthorized modules.
- Dashboard adds a Security & Access Control summary and warns if the database contains anything other than exactly one active Super Admin.
- Corrects the Phase 148 upgrade SQL collation mismatch by explicitly comparing legacy text with `utf8mb4_unicode_ci`.
- Corrects the old Phase 148 migration behavior that promoted every unassigned administrator to Super Admin; only one owner is retained and other legacy/unassigned admins become Manager.

## Existing database order

1. Back up the database.
2. Rerun the corrected `sql/phase148_critical_backend_hardening.sql` (it is idempotent and fixes the collation error).
3. Run `sql/phase150_admin_owner_lock.sql`.
4. Open Admin > System Check.
5. Confirm `Single protected Super Admin owner` and `Admin-management permission owner-only` are green.

`sql/phase150_required_db_upgrade.sql` combines steps 2 and 3 for convenience.
