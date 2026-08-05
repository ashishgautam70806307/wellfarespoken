# Phase 122 - Secure Deploy Upgrade

Project: Well Fare English Spoken  
Prepared: 03 August 2026

This upgrade keeps the existing navy, gold and green visual identity. It focuses on the first production-critical phase: security, Weekly Test reliability, student clarity and safe deployment configuration.

## What has been fixed

1. PWA/service worker now caches only static assets. Student, admin, test, result and API pages are network-only.
2. Guest test results require a long random result token. A sequential attempt ID alone is no longer enough.
3. Warning, timing, autosave, cancel and submit actions require the exact attempt access token.
4. Official Weekly Exam always requires an active student login.
5. Test time is validated on the server. Expired attempts are submitted using saved answers.
6. Questions, answers, marks and shuffled option order are snapshotted when the attempt starts.
7. Resumed attempts display the same questions/options and previously saved answers.
8. Correct answers for official tests stay hidden until teacher checking.
9. Image/file uploads validate real MIME type, use random names and block SVG/PHP execution.
10. Student/admin sessions use secure cookie settings, regeneration, inactivity checks and no-cache headers.
11. Login/test-start throttling is stored outside PHP session.
12. CSV formula injection is neutralized in reviewed exports.
13. Database and application configuration moved to `.env`.
14. Database schema updates can be switched off after the one-time upgrade.
15. The Test Center is simplified to Practice Test, Weekly Exam and My Results.
16. Admin/public navigation and important actions use Font Awesome icons instead of mixed emoji symbols.
17. Self-entered practice notes no longer change official score/progress analytics.
18. Old nested ZIP and backup PHP files were removed from the deploy package.

## Before replacing files

1. Take a full website backup.
2. Export the current MySQL database.
3. Keep a copy of the current `includes/config.php` so you can read the existing database credentials.
4. Test the upgrade on a staging/subdomain first when possible.

## Required `.env` setup

Copy `.env.example` to `.env` in the project root and update:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-real-domain.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database
DB_USER=your_database_user
DB_PASS="your-strong-database-password"

SESSION_SECURE_COOKIE=true
SESSION_SAMESITE=Lax
TRUST_PROXY_HEADERS=false

APP_ALLOW_SCHEMA_UPDATES=true
```

Important:

- Use HTTPS before setting `SESSION_SECURE_COOKIE=true`.
- When Cloudflare/reverse proxy terminates HTTPS, set `TRUST_PROXY_HEADERS=true` only when that proxy is trusted.
- `.env` is blocked by Apache and ignored by Git, but it must never be shared publicly.

## One-time database upgrade

1. Set `APP_ALLOW_SCHEMA_UPDATES=true`.
2. Upload/replace the Phase 122 files.
3. Login to admin.
4. Open `Admin > System Check` once.
5. Open Test Center once and confirm the weekly-test security columns are green.
6. Test one Practice attempt and one logged-in Weekly Exam attempt.
7. Change `.env` to:

```env
APP_ALLOW_SCHEMA_UPDATES=false
```

This is important. Normal public requests should not keep running database DDL/schema checks.

## Clear the old PWA cache

Because the old service worker could cache dynamic pages:

1. Open the website in Chrome.
2. Press F12 > Application > Service Workers.
3. Click Unregister for the old worker if it remains.
4. Application > Storage > Clear site data.
5. Reload twice.

The new service worker uses cache version `wellfare-spoken-static-v122` and does not cache PHP/student/admin pages.

## Minimum deployment test

### Student account

- Register with a 10-digit mobile number and 8+ character password.
- Confirm password validation.
- Login and logout.
- Confirm the browser Back button does not show private dashboard content after logout.
- Disable the account in admin and confirm the active session is rejected.

### Practice Test

- Start as guest.
- Refresh the exam room and confirm saved answers remain.
- Submit and confirm the result opens.
- Remove/change the result token and confirm access is denied.

### Weekly Exam

- Confirm guest cannot start it.
- Login and start it.
- Refresh and resume the same attempt.
- Confirm question/option order remains unchanged.
- Let timer expire and confirm server-side submission.
- Confirm correct answers stay hidden before teacher checking.
- Check the test in admin and confirm expected answers/feedback become visible.

### Security/uploads

- Try uploading `.php`, renamed PHP, SVG and oversized images. They must be rejected.
- Confirm upload folders do not list files.
- Confirm `/storage/`, `.env`, `.sql`, `.zip` and backup files cannot be downloaded.

## Apache and Nginx note

The package includes Apache `.htaccess` protection. On Nginx, add equivalent server rules to deny:

- dotfiles and `.env`
- `/storage/`
- SQL/ZIP/backup/log files
- PHP execution inside `/assets/uploads/`

Do not deploy on Nginx without those equivalent rules.

## Files to replace

See `PHASE122_CHANGED_FILES.txt`. The full ZIP can replace the complete project while preserving the original `spoken/` root format. The replace-only ZIP contains only changed/new files with their original relative paths.

## Intentionally remaining for the next management phase

The following broader modules are not claimed as completed in this security ZIP:

- Staff roles and granular permissions
- Admin activity/audit log
- Connected Enquiry -> Admission -> Student conversion
- OTP/forgot-password flow
- Full soft-delete/restore center
- Notification center
- Complete CSS architecture refactor
- Server-side pagination for every large admin list

They should be implemented after this deploy-critical phase is installed and tested.
