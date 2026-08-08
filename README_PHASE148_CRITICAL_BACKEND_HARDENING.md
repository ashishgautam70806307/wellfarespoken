# Well Fare English Spoken — Phase 148 Critical Backend Hardening

Phase 148 is a cumulative backend/security hardening update built on Phase 147. It preserves the current frontend, mobile UX, voice practice, weekly-test, roadmap and student-account features while repairing the critical/high-risk backend findings from the Phase 147 audit.

## Student registration without paid OTP

The project intentionally supports **mobile number + student-created password** without paid SMS OTP.

- New self-registered accounts are stored as `identity_status = Unverified`.
- In the default `STUDENT_REGISTRATION_MODE=open`, the student may immediately use low-risk learning features.
- The self-entered phone number is **not treated as proof of identity**.
- An Unverified account cannot automatically claim/link an existing admission by matching the phone number.
- Institute staff can mark the mobile **Verified** after a free manual check such as in-person confirmation, a staff-initiated call to the registered number, or an existing trusted WhatsApp conversation.
- `STUDENT_REGISTRATION_MODE=approval` is available if the institute later wants every new account to require administrator activation before login.
- Official learning level is always created as `Zero Level / Unassessed`; students cannot self-select a higher official level.

This is the recommended low-cost model until an OTP provider is budgeted.

## Critical/high-risk repairs

- Removed live database credential fallbacks from source; production secrets now belong only in server environment / `.env`.
- Removed the fixed production administrator seed from the fresh canonical SQL and added secure first-admin setup.
- Added admin roles, permissions, per-module enforcement, audit logging and optional free TOTP authenticator MFA.
- Added database-backed authentication rate limiting with fail-closed fallback behavior for auth endpoints.
- Added stable Enquiry → Admission → Student → Enrollment → Batch relationships while keeping human-readable snapshots/history.
- Added immutable admission payment/refund/adjustment ledger and receipt numbers.
- Added orphan-data audit plus foreign-key hardening with safe `SET NULL`, `CASCADE` and `RESTRICT` rules.
- Added private learning-file storage and authenticated file delivery with access logs.
- Added formal schema migration registry and CLI migration/database smoke checks.
- Added pagination to the highest-volume CRM/admin lists.
- Added student mobile verification governance and reason-based administrator password reset auditing.

## Existing Phase 147 installation

1. Back up the database and project files.
2. **Rotate the old live database password in the hosting/database control panel.** Phase 148 removes it from code, but code cannot rotate an external database account.
3. Put the new production database credentials only in the server `.env` file. Never include `.env` in a ZIP or repository.
4. Deploy the Phase 148 cumulative project or apply the replace-only package to an exact Phase 147 installation.
5. Import `sql/phase148_critical_backend_hardening.sql` once.
6. Open **Admin → System Check** and resolve every red item.
7. If no active administrator remains after the migration, create the first private administrator through `admin/setup.php`; on live hosting set a strong `ADMIN_SETUP_KEY` first, then rotate/remove the setup key after use.
8. Enable authenticator MFA for the Super Admin from the administrator password/security page.
9. Keep `APP_ALLOW_SCHEMA_UPDATES=false` during normal production traffic.
10. Complete the Phase 148 deployment/browser/database checklist before launch.

## Fresh installation

Import `sql/wellfare_english_complete.sql`, configure `.env`, then open `admin/setup.php` to create the first administrator. The fresh SQL contains no fixed admin account.

## Important test limitation

Static source/schema validation is complete. This build environment does not contain a MySQL/MariaDB server or `pdo_mysql`, so real database migration execution, multi-browser session invalidation, authenticated workflows and live file-upload/download tests remain pending on the user's XAMPP/staging server.
