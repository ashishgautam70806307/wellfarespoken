# Phase 134 Audit Report

## Scope

The Phase 133 project was frozen as the base. Phase 134 changes only configuration, security, error handling, schema safety, Weekly Test behavior, and maintenance files. No new design or frontend feature was added.

## Mistakes and pending items found

1. Local/live database switching still depended on manually changing generic database values.
2. In auto mode, generic local `DB_*` values could override live defaults after copying the same `.env` to hosting.
3. Safe login return URLs lost or rejected valid query/fragment combinations used by Upcoming Test.
4. A stale test ID could fall back to another paper at page/API level.
5. Test type and selected paper type were not consistently enforced together.
6. Running-attempt time could be recalculated from the current test duration instead of its stored deadline.
7. An expired resumed Upcoming attempt was closed without calculating saved-answer marks.
8. Missing Weekly Test tables/columns could produce a raw failure path.
9. Logged-in attempts could retain a guest-style display name.
10. Runtime schema/data setup helpers were still callable from normal page bootstrapping.
11. Several dynamic CMS URLs were escaped as HTML but not scheme-validated.
12. Some destructive admin actions were GET-based or silently ignored invalid CSRF.
13. Some admin/public exceptions could expose implementation details or provide no useful server log.
14. Public fetch helpers accepted unbounded limits in a few places.
15. Old phase notes and cleanup artifacts made the deployable package confusing.
16. The service worker needed a clean cache revision after functional JavaScript changes.

## Repairs completed

- Added automatic local/live/manual database profiles.
- Auto profiles no longer consume generic `DB_*` values.
- Added controlled runtime-mode overrides and proxy-aware HTTPS handling.
- Preserved safe redirect query strings and fragments while blocking external/header-injection paths.
- Added exact paper/type enforcement and stale-paper rejection.
- Changed attempt timing to prefer `expires_at`.
- Finalized expired resumed attempts from saved answers and exposed the secure result redirect.
- Added safe schema-readiness messages to Weekly Test page/API.
- Stored verified student identity on logged-in attempts.
- Disabled runtime DDL/seeding when `APP_ALLOW_SCHEMA_UPDATES=false`.
- Added URL scheme validation for CMS links/assets.
- Converted/verified destructive admin operations as POST + CSRF.
- Added explicit invalid-CSRF handling to Content, Form Options, and Hero Banner saves.
- Replaced raw technical user errors with server logging plus safe messages.
- Clamped public query limits.
- Added admission request validation/rate limiting.
- Removed obsolete root deployment artifacts and blocked phase docs from HTTP access.
- Updated the static cache name to `wellfare-spoken-static-v134`.

## Static verification completed

- 67 PHP files passed `php -l`.
- 7 JavaScript files, including `sw.js`, passed syntax checking.
- 32 CSS files passed PostCSS parsing.
- Canonical SQL contains 40 tables.
- 632 direct schema/table references were checked against the canonical SQL with no missing table/column result.
- 29 declared CSS/JavaScript asset references were checked with no missing asset.
- 247 literal internal links were checked with no missing target.
- 24 service-worker static assets were checked with no missing file.
- One `.sql` file exists in the project: `sql/wellfare_english_complete.sql`.
- Local, live, explicit override, manual, and profile-specific database configuration branches were executed in isolated PHP processes.

## Not claimed as verified

The following require the user's actual MySQL database and browser session:

- Real student registration/login database writes.
- Real admission insertion and batch preselection data.
- Basic/Previous/Upcoming attempt creation, autosave, submit, resume, and result records.
- Roadmap progress persistence.
- Materials database/API content.
- Hero Banner file upload and database save.
- Gallery and review records loaded from the live database.

These are listed in `PHASE134_TEST_CHECKLIST.md` and must be completed before deployment.

## Remaining external/roadmap items

- HTTPS certificate installation/renewal is a hosting task; PHP cannot remove a browser's “Not secure” warning on an HTTP site.
- File-based rate limiting requires the `storage/rate-limits` folder to be writable.
- Staff roles/permissions, OTP, notification center, and Enquiry → Admission → Student conversion remain future modules and were intentionally not added.
- The live database credential shared previously should be rotated after deployment.
