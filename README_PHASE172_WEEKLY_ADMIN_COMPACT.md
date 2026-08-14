# Phase 172 - Weekly Test Admin Compact Workflow

Phase 172 is a focused Admin Weekly Test usability pass on top of Phase 171.

## Changes
- `admin/weekly-tests.php` Test Setup is now a compact 3-step accordion:
  1. Paper Details
  2. Opening
  3. Optional Settings
- Removed long helper paragraphs and duplicate instructional text from the Weekly Test page.
- Selected paper status/actions are now shown in a small action strip instead of a large publish panel.
- Question upload keeps only the useful sample actions: 3-question Excel sample, blank Excel, and manual question entry.
- Duplicate CSV sample buttons from the upload card were removed; CSV import support remains unchanged.
- The four large workflow explanation cards were replaced by a compact Setup / Questions / Manage / Copies rail.
- Question Bank and Student Copies navigation now clearly identifies the dataset (`Questions` vs `Students`) and uses compact Previous/Next paging instead of two similar numbered pagers.
- Both pagers preserve the other section's filters/page state.
- Out-of-range `qpage` and `apage` values are clamped to the last valid page after filters/data changes, preventing blank-list confusion.
- Added page-scoped responsive CSS `assets/css/phase172-weekly-admin-compact.css`.
- Service Worker cache bumped to v172 and the new stylesheet is precached.

## Preserved
- Phase 171 Basic / Previous / Upcoming scope-selection fix.
- Upcoming first-paper creation/upload flow.
- Phase 170 continuous voice practice behavior.
- Weekly Test scoring, ranking, answer release, uploads, question CRUD, student attempts, and database schema.

## Database
No schema change required.
