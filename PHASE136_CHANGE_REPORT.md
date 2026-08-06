# Phase 136 Change Report

## Purpose

Stabilize the current project before the full reusable-UI migration. No new page, visual theme or product feature was introduced.

## Corrected workflows

### Weekly tests

- Added a shared attempt loader containing all grading settings.
- Added a shared immutable snapshot loader/creator.
- Added a single atomic finalization service for manual submit, timer expiry and recovery.
- Finalization merges posted answers with previously autosaved answers before grading.
- Finalization is row-locked, transactional and idempotent.
- Official upcoming-test start is protected against concurrent duplicate attempts by locking the test and latest student attempt.
- Autosave locks the attempt before writing answers.
- Exam-room timer expiry now submits and grades saved answers instead of merely closing the attempt.
- Exam-room question loading now uses the same snapshot service as the API.

### Learning roadmap

- Added one server-side unit-access rule used by the lesson page and progress API.
- `unlock_after_unit_id` is respected; when absent, the previous roadmap unit is used as the prerequisite.
- Direct API requests can no longer mark a locked future unit complete.
- Added an index on `roadmap_units.unlock_after_unit_id` in the canonical SQL.

### Student profile

- Removed student-controlled updates to `current_level`.
- The current level is displayed as read-only and remains assessment/admin controlled.
- Students may still update learning goal and daily practice minutes.

### Admission enquiry

- Online batch selection is resolved from published batch data on the server.
- Course, level and preferred-batch values are validated against published/admin-managed options.
- Lead source is server-controlled.
- Invalid or removed online batches are rejected.
- Rapid duplicate submissions for the same phone are suppressed for five minutes.

### Material practice APIs

- Added private/no-store response handling.
- Added request rate limiting for answer evaluation.
- Added pair ID, answer length, query, goal and direction validation.
- Added server error logging without exposing internal exceptions to the learner.

### Course administration

- Course deletion and its variant deletion now run in one transaction.
- Course create/update plus variant replacement now run in one transaction.
- Variant arrays, lengths and numeric values receive defensive normalization.
- Existing variants are preserved when a later database operation fails because the transaction rolls back.

### Canonical SQL

- Removed known duplicate default batch seed IDs 4, 5 and 6.
- Added a narrow cleanup statement for those legacy duplicate seed rows.
- Added `idx_roadmap_unlock`.
- Added `project_phase_marker=phase136_regression_repair_v1`.
- Bumped the service-worker cache namespace to `wellfare-spoken-static-v136` so older cached assets are retired after deployment.

## Changed application files

- `admin/courses.php`
- `admission.php`
- `includes/functions.php`
- `material-practice-api.php`
- `material-practice-list-api.php`
- `roadmap-lesson.php`
- `roadmap-progress-api.php`
- `student-dashboard.php`
- `weekly-exam-room.php`
- `weekly-test-api.php`
- `sql/wellfare_english_complete.sql`
- `sw.js`
- `README.md`

## Files added for delivery/audit

- `README_PHASE136_REGRESSION.md`
- `PHASE136_CHANGE_REPORT.md`
- `PHASE136_TEST_REPORT.md`
- `PHASE136_CHANGED_FILES.txt`
- `PHASE136_CLEANUP_LIST.txt`
- `PHASE136_VALIDATION.json`

## Intentionally deferred

These require the later security/business/database phases and were not hidden or falsely marked fixed:

- Removing the configured live database fallback from source and rotating the live password.
- Removing/replacing default admin seed credentials.
- Full foreign-key strategy and orphan-data migration.
- Complete enquiry -> admission -> student -> course -> batch workflow.
- Fee ledger, receipts, reversals and payment history.
- Role/permission matrix, MFA and admin activity audit.
- Fail-closed/distributed rate limiting.
- Full automated integration/browser test suite.
- Public/admin reusable-UI migration and old CSS cleanup.
