# Phase 153 - Admin Access Flow Repair

## Root cause
The protected owner could be trapped on `admin/password.php` because the older hardening migration set `must_change_password='Yes'` on the active legacy owner. `require_admin()` then redirected every other Admin page back to Account Security, while the sidebar still rendered the owner's full menu. This made the interface look broken even though the permission checks were working.

## Fixes
- Protected primary Super Admin no longer obeys a stale legacy `must_change_password` flag.
- A stale owner flag is automatically cleared after the existing password is successfully authenticated.
- Normal staff accounts created/reset with a temporary password still must change it on first login.
- While a staff password change is required, the Admin sidebar shows only Password & MFA + Logout; search is disabled and the logo points to Account Security instead of a dead Dashboard link.
- After the required password change, the user is sent directly to Dashboard and only role-permitted modules appear.
- Protected owner password reset was removed from Admin Users; owner password changes must go through Account Security and require the current password.
- Old migration SQL no longer forces an active legacy owner to change password just because of the legacy email address.
- Optional idempotent Phase 153 migration clears the stale owner flag on existing databases.

## Security behavior preserved
- Exactly one protected Super Admin owner.
- Staff RBAC remains server-side.
- Second Super Admin cannot be created or assigned.
- Temporary staff passwords still require a first-login password change.
- Password changes invalidate sessions through auth versioning.
- MFA, rate limiting and audit logging remain intact.

## Existing installations
Code self-heals the stale owner flag after successful owner login. For database cleanup, optionally import:
`sql/migrations/20260808_001_phase153_admin_access_flow.sql`
