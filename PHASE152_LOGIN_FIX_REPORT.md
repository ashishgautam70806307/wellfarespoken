# Phase 152 Institute Login Fix Report

## Root cause
`admin_setup_needed()` intentionally ignored the historical legacy administrator seed. On an upgraded live database containing only that migrated account, `admin/login.php` interpreted the installation as having no administrator and automatically redirected every Institute Login request to `admin/setup.php`. Live setup then correctly refused to continue because `ADMIN_SETUP_KEY` was not configured, leaving the institute unable to reach its normal login screen.

## Repair
1. Setup state now means exactly `admins` table row count = 0.
2. Institute Login never automatically opens setup.
3. Fresh-install setup is exposed only as an explicit admin bootstrap action and remains protected by `ADMIN_SETUP_KEY` on live hosting.
4. Existing RBAC, single-owner, password-change, rate-limit and MFA rules are unchanged.

## Validation
- 80 PHP files passed `php -l`.
- No database schema change.
- Public Institute Login links still point to `admin/login.php`.
- Login page contains no automatic redirect to `setup.php`.
