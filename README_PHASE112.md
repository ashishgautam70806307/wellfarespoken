# Phase 112 - Weekly batch edit/delete + student winner dashboard

Changes:
- Batch/test paper cards now include Edit, Questions, Publish, Pending, Complete, and Delete/Archive actions.
- Delete is soft-delete: hidden in admin, preserved in DB for 15 days.
- Added single batch paper detail page: admin/weekly-test-paper.php?id=ID.
- Paper cards show status, batch label, question count, duration, copies, pending copies and winner status.
- Published cards remain light green; pending cards light yellow.
- Winner board added to student dashboard and weekly-test page with stronger flower celebration.
- Admin card spacing/grid improved and border radius kept at 7px in admin.
- Multiple accepted answers in expected_answer are still supported using ||, ;, or new lines.
