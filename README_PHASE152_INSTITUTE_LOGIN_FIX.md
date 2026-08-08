# Phase 152 - Institute Login Bootstrap Fix

Focused fix on top of Phase 151.

## What changed
- Public "Institute Login" always opens `admin/login.php`.
- `admin/login.php` no longer auto-redirects visitors to `admin/setup.php`.
- First-run setup is now considered necessary only when the `admins` table contains zero rows.
- Existing migrated/legacy administrator rows are treated as existing accounts; RBAC/password migration rules continue to control their security.
- If a truly fresh installation has no admin, the login screen shows a setup notice instead of unexpectedly opening the setup page.
- Live first-run setup still requires `ADMIN_SETUP_KEY` and must be opened explicitly once.
- `admin/setup.php` includes a Back to Institute Login link.

No database schema, RBAC permission model, upload security, student flow, frontend design, or business logic was changed.
