# Phase 168 — Weekly Test Easy Admin Flow

This phase is a focused repair of Admin → Weekly Tests and Admin → Batches. Existing student scoring, ranking, answer release and exam logic remain in place.

## Easier Upcoming Test workflow

1. Create/select the test and choose its batch.
2. Upload questions using the 3-example Excel sample, or add questions manually.
3. Use **Publish Now** when students should enter. Use **Close Entry** when new starts should stop.

Manual Open/Close is the recommended default. Automatic scheduling is optional and now asks only for date, a 12-hour start time, and a simple entry-window choice. Detailed exam controls remain available inside Advanced Test Settings.

## Question upload repair

The XLSX reader now supports both inline-string and shared-string Excel workbooks, preserves blank column positions, and has decompression/size guards. The main Excel sample contains three real examples and a Read Me sheet. A separate blank Excel template is also included.

## Batch Management repair

The normal form is reduced to Batch Name, Course, Timing and Days; secondary controls are under Advanced Details. `batch_timings.course_id` is now part of the canonical schema and a safe migration is included. The page also keeps a legacy save fallback if automatic schema changes are disabled before the migration is applied.

## Responsive admin UI

A page-scoped Phase 168 stylesheet improves Weekly Test and Batch layouts for desktop, tablet and small phones without changing unrelated frontend pages.

## Deployment note

If production has `APP_ALLOW_SCHEMA_UPDATES=false`, run:

`sql/migrations/20260814_001_phase168_weekly_admin_easy.sql`

Then open Admin → System Check and confirm **Weekly Test complete schema** and **Batch course link column** are green.
