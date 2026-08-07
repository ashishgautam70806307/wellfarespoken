# Phase 147 — Student Account Management

Phase 147 extends the cumulative Phase 146 project with a professional administrator-facing Student Account Manager.

## Included

- Account statistics, search and filters
- Bulk activation, deactivation and safe hiding
- Full student profile/account editing
- Administrator-selected password reset without the old password
- Existing-session invalidation after password, status or security changes
- Force sign-out
- Student account event timeline
- Recent weekly tests, practice and activity visibility
- System Check coverage for the new schema
- Responsive Phase 147 UI

## Existing Database Upgrade

Import once:

```text
sql/phase147_student_account_migration.sql
```

Fresh installations can use:

```text
sql/wellfare_english_complete.sql
```

Automatic runtime schema updates are disabled by default. The migration should be imported explicitly on localhost/staging and then production after a backup.

## Required Verification

Use `PHASE147_BROWSER_DATABASE_CHECKLIST.md`. Static validation passed, but a connected database, multi-browser student session and real authentication workflow were not available during package generation.

## Backend Audit

Read `PHASE147_BACKEND_AUDIT.md` before planning the next phase. The most urgent remaining actions are production credential rotation/removal, administrator seed hardening, role/permission controls, relation integrity, connected student lifecycle and fee-ledger design.
