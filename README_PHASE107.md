# Phase 107 - Weekly Test Batch Scope, Active Frontend, Soft Delete, Mobile Merge

Implemented based on weekly test admin requirements:

1. Batch-wise test management
- weekly_tests now supports batch_id and batch_label.
- Admin Test Setup has Batch / Group and Batch Label fields.
- Available From and Available Until fields added for scheduled tests.
- Basic / Previous / Upcoming can each have different batch papers.
- Upload questions still belongs to exact selected test paper, so each batch can have a separate answer sheet.

2. Frontend weekly-test.php active paper selection
- Frontend now uses all available papers for each test type.
- It chooses an active paper first.
- If multiple papers exist for Basic / Previous / Upcoming, a Batch / Test Paper dropdown appears.
- Upcoming no longer stays stuck on an old draft paper when an active paper exists.
- Test start validates status and schedule time on the server.

3. Type/test scoped admin records
- When Basic / Previous / Upcoming scope is selected, Student Answer Copies are filtered to that selected test paper.
- This prevents Basic, Previous and Upcoming records from mixing in one place.

4. Same mobile number merge
- Student answer copy cards now group by normalized 10-digit mobile number.
- If the same mobile number is used with different names, it appears as one student/mobile record instead of many duplicate cards.
- Latest name is shown on the card.

5. Soft delete policy
- Attempts are not permanently deleted immediately.
- Admin hide/delete marks records as status_deleted=1 and deleted_at=NOW().
- Hidden records do not show in admin lists.
- Cleanup permanently removes hidden attempts and their answers after 15 days.
- Questions hidden/deleted also get deleted_at and are cleaned later.

6. Admin/student record UI polish
- Student copy cards spacing improved.
- Record page has Hide Attempt button.
- Activity logs and question answers remain hidden in accordion by default.
- Buttons and filters improved for responsive layout.

7. Dashboard counts
- Weekly attempt and pending-review counts ignore hidden/deleted records.

Files changed:
- includes/functions.php
- weekly-test.php
- weekly-test-api.php
- admin/weekly-tests.php
- admin/weekly-test-ajax.php
- admin/weekly-student-record.php
- admin/dashboard.php
- assets/css/style.css
- admin/_header.php
- includes/header.php
