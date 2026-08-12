# Phase 166 — Weekly Test Release, 12-Hour Time & Import UX

Phase 166 is a focused update on Phase 165. It does not change the database schema, scoring engine, ranking logic, student-paper hint rules, or existing Weekly Test sample CSV files.

## Included

- Upcoming Test cards now show whether uploaded/master answers are locked or released to students.
- Admin can use **Release Answer Key** after the exam is safely closed and no student is still inside.
- Re-publishing an Upcoming paper automatically locks the answer key again.
- All Admin `datetime-local` controls render as Date + Hour (1–12) + Minute + AM/PM while preserving the existing backend datetime field/value contract.
- Weekly Test upload instructions now match the real importer columns.
- A blank `.xlsx` template is included at `assets/downloads/weekly_test_upload_template.xlsx`.
- If Admin has no Excel file, **Add Question Manually** opens the existing question editor; both methods save to the same Question Bank.
- The old testing-only “Create 2 Demo Batch Papers” control is hidden from the normal Admin UI.
- Existing Basic / Previous / Upcoming CSV samples remain byte-for-byte unchanged.

No SQL migration is required.
