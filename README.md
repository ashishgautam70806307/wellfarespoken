# Well Fare English Spoken — Phase 147 Student Account Management

This is the current cumulative working package. It preserves all approved fixes through Phase 146 and adds advanced administrator-controlled student account management plus a complete static backend audit.

## Phase 147 focus

- Added a dedicated **Student Account Control** section to the Admin Dashboard.
- Upgraded **Admin → Student Accounts** with statistics, search, filters, bulk actions and responsive account cards.
- Added full profile, access, learning, test and practice management for each student.
- Added secure administrator-selected password reset without requiring the old password.
- Added force sign-out and automatic session invalidation after password/status/security changes.
- Added student account event history without storing passwords.
- Added Phase 147 schema checks and migration support.
- Added a detailed module-by-module backend audit for future correction phases.
- Updated service-worker cache to `wellfare-spoken-static-v147`.

## Existing database installation

1. Back up the project files and database.
2. Extract the cumulative ZIP into the web root, or apply the replace-only ZIP to an exact Phase 146 installation.
3. Import `sql/phase147_student_account_migration.sql` once for an existing database.
4. Open **Admin → System Check** and confirm the three Phase 147 student-account schema checks pass.
5. Unregister the previous service worker, clear site storage and hard refresh.
6. Complete `PHASE147_BROWSER_DATABASE_CHECKLIST.md` before production use.

Fresh installations can import `sql/wellfare_english_complete.sql`.

## Documents

- `README_PHASE147_STUDENT_ACCOUNTS.md`
- `PHASE147_BACKEND_AUDIT.md`
- `PHASE147_BROWSER_DATABASE_CHECKLIST.md`
- `PHASE147_CHANGED_FILES.txt`
- `PHASE147_VALIDATION.json`
