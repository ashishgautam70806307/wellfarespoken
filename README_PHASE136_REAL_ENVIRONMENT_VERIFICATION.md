# Phase 136 - Real Environment Verification and Regression Repair

## Scope

Phase 136 is based on Phase 135. No new page, section, feature, visual style, or design system was added. The work is limited to verification, runtime compatibility, security-preserving regression repair, and deployment checks.

## Important result

The available build container does not include the `pdo_mysql` PHP driver or a running MySQL/MariaDB server. Therefore, real database writes could not be executed inside this environment. The project now includes a real-environment functional checker that must be run on XAMPP or staging before final production deployment.

All non-database verification that could be executed here was completed:

- 70 PHP files passed syntax checks.
- 8 JavaScript files passed syntax checks.
- 32 CSS files passed parsing checks.
- 40 unique tables are present in the single canonical SQL.
- 23 page routes passed PHP rendering smoke checks.
- 37 browser/interaction assertions passed.
- 8 local/live configuration branches passed.
- 27 regression logic assertions passed.
- No missing declared frontend asset was found.

## Actual bugs repaired

### Runtime compatibility

- Added safe UTF-8 compatibility functions for servers where `mbstring` is not enabled. This prevents fatal errors in text limiting and validation code.
- Added safe defaults for older course and roadmap records with missing optional keys. This prevents PHP warnings on course detail and roadmap pages.

### Automatic local/live configuration

- Request host detection prefers the actual `HTTP_HOST`.
- Forwarded proxy headers are trusted only when `TRUST_PROXY_HEADERS=true`.
- Local domains continue to use the local profile.
- Live domains continue to use the live profile.
- CLI and cron jobs can use `APP_ENV=production` or `APP_RUNTIME_MODE=live`.
- Environment variables retain the highest priority.

### Student authentication and admission

- Student login and registration rate limits now cover both the account/phone and the requester IP.
- Admission submissions are rate-limited by both phone and IP.
- Logout can display a safe success message.

### Weekly Test

- Replaced the unreliable prepared SQL pattern `INTERVAL ? MINUTE` with an explicit expiry timestamp. This is compatible across normal MySQL/MariaDB prepared statements.
- The selected test ID is preserved through login and redirect.
- An active attempt can no longer open the result/answer-review page early.
- Expected answers appear only after the attempt is submitted or checked and the result policy allows it.
- Soft-deleted attempts are rejected by the exam room.
- A legacy attempt without a valid question snapshot fails with a controlled response instead of opening a broken exam.
- Existing attempt-token, result-token, autosave, resume, server timer, question snapshot, and ownership controls remain intact.

### PWA/cache

- Service Worker cache version updated to `wellfare-spoken-static-v136`.
- Existing private-page and API cache exclusions remain intact.

## Installation

1. Back up the current project files and database.
2. Extract the Phase 136 replace-only ZIP into the current project root and overwrite matching files.
3. Run `PHASE136_CLEANUP.bat` once on Windows to remove obsolete Phase 135 reports/tools.
4. Keep the normal environment settings:

```env
APP_RUNTIME_MODE=auto
DB_CONNECTION_MODE=auto
APP_ALLOW_SCHEMA_UPDATES=false
APP_DEBUG=false
TRUST_PROXY_HEADERS=false
```

5. On a reverse-proxy/CDN deployment, enable `TRUST_PROXY_HEADERS=true` only when the proxy is controlled and correctly configured.
6. Enable the PHP MySQL extension in XAMPP (`pdo_mysql`) and ensure MySQL is running.
7. Import only:

```text
sql/wellfare_english_complete.sql
```

8. Run from the project root:

```bat
php tools\phase136-functional-check.php
php tools\phase136-logic-check.php
```

9. To execute rollback-safe database write probes on a test/staging database:

```bat
set PHASE136_WRITE_TESTS=true
php tools\phase136-functional-check.php
```

Do not enable write probes against a production database without a verified backup.

10. Clear the old Service Worker and site cache once, then hard refresh.

## Optional browser regression tools

These are included for repeatable local/staging verification when Node.js, Python, and Chromium are available:

```bat
python tools\phase136-page-smoke-check.py
python tools\phase136-config-check.py
node tools\phase136-browser-check.mjs
python tools\phase136-static-validate.py
```

## Database

Phase 136 keeps one canonical database file only:

```text
sql/wellfare_english_complete.sql
```

No database schema change was introduced after Phase 135. The SQL still contains all required banner, student, admission, roadmap, materials, and Weekly Test structures.

## Production note

Keep live credentials in the live server environment or `.env` whenever possible. The project supports automatic local/live profiles, but environment-managed secrets are safer than source-controlled credentials.
