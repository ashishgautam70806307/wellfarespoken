# Phase 134 — Stabilization and Functional Repair

Phase 134 is a focused maintenance release. It does **not** add a new page, section, navigation pattern, color system, card style, form style, or other visual redesign. The approved Phase 133 frontend remains unchanged.

## What this phase fixes

### Automatic local/live database configuration

The application now resolves the database profile automatically:

- `localhost`, `127.0.0.1`, `::1`, `*.localhost`, and `*.test` use the local profile.
- Other web hosts use the live profile.
- `APP_RUNTIME_MODE=local` or `APP_RUNTIME_MODE=live` can override detection.
- `DB_CONNECTION_MODE=manual` uses the generic `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` values.
- Auto mode uses only `DB_LOCAL_*` or `DB_LIVE_*`. This prevents a copied local `.env` file from accidentally overriding the live profile.

Recommended normal configuration:

```env
APP_RUNTIME_MODE=auto
DB_CONNECTION_MODE=auto
APP_ALLOW_SCHEMA_UPDATES=false
```

### Weekly Test reliability

- Basic, Previous, and Upcoming types remain mapped to `basic`, `previous`, and `upcoming`.
- A stale/deleted `test_id` cannot silently start another paper.
- A paper cannot be started under a different test type.
- Missing Weekly Test schema returns a controlled message instead of a raw database error.
- Login return URLs retain safe query strings and anchors.
- Logged-in attempts store the verified student's name and phone.
- Attempt time uses the stored `expires_at` deadline first, so later admin duration edits do not alter a running attempt.
- An expired resumed attempt now grades its saved answers, stores the final score/penalty, submits it, and opens the secure result path.
- AJAX start and normal form POST fallback are both retained.

### Deployment and schema safety

- Runtime `CREATE TABLE` and `ALTER TABLE` helpers return immediately when `APP_ALLOW_SCHEMA_UPDATES=false`.
- Public page requests do not seed Roadmap, Materials, or Weekly Test data in normal production mode.
- `sql/wellfare_english_complete.sql` remains the only canonical schema file.
- The service-worker cache version is updated to `v134` without caching private/dynamic PHP responses.

### Request and admin safety

- Unsafe dynamic URL schemes are rejected before rendering CMS links.
- Content Blocks, Form Options, Hero Banners, and publish-toggle destructive actions require POST and CSRF validation.
- Invalid CSRF on normal admin form saves now produces an explicit error instead of being ignored.
- Technical exceptions are logged server-side while users receive safe messages.
- Query limits used by public fetch helpers are clamped to safe ranges.
- Admission requests have server-side validation and persistent rate limiting.

### Maintenance cleanup

- Obsolete phase notes, old validation files, old cleanup scripts, and duplicate deployment instructions are removed from the complete package.
- Phase documentation files are blocked from public HTTP access by `.htaccess`.
- No runtime CSS or JavaScript file was removed merely because its filename contains an older phase number; referenced assets are preserved to avoid design regression.

## Installation

1. Back up the current website files and database.
2. Extract the Phase 134 replace-only ZIP into the project root and overwrite matching files.
3. Run `PHASE134_CLEANUP.bat` on Windows, or remove the old root-level phase notes listed by that script manually.
4. Confirm `.env` or server environment values.
5. Keep `APP_ALLOW_SCHEMA_UPDATES=false` after the canonical SQL is imported.
6. Unregister the old service worker once and clear site cache.
7. Complete `PHASE134_TEST_CHECKLIST.md` on localhost or staging.

No database migration is newly introduced in Phase 134. The canonical Phase 132 SQL remains current.

## Important environment limitation

The build environment has PHP/PDO but no MySQL PDO driver and no copy of the user's XAMPP/live database. PHP, JavaScript, CSS, schema references, links, and configuration branches were statically validated. Database-backed browser flows must still be executed on localhost/staging before deployment.
