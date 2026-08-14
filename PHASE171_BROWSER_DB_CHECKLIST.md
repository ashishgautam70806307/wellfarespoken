# Phase 171 Browser + DB Checklist

1. Open `admin/weekly-tests.php?type=upcoming` when there is no Upcoming paper.
   - PASS: Upcoming tab remains active.
   - PASS: Basic paper is not selected as a fallback.
   - PASS: Create New Test Paper is visible.
2. Create an Upcoming paper and save.
   - PASS: page returns to `type=upcoming&test_id=<new id>`.
3. Upload the provided sample Excel/CSV to that paper.
   - PASS: questions attach to the newly saved Upcoming paper.
4. Click Basic, Previous, Upcoming repeatedly.
   - PASS: each scope stays correct; no cross-type overwrite.
5. Desktop form check.
   - PASS: text/select/number/date controls have consistent height and aligned top edges.
6. File upload control check.
   - PASS: file field is readable and does not overflow its card.
7. Tablet/mobile check at ~1024, 768, 430 and 360 widths.
   - PASS: cards stack, tabs remain reachable, no horizontal page overflow.
8. Existing paper edit/save.
   - PASS: selected paper remains selected after save.
9. Manual Open/Close and Automatic Schedule.
   - PASS: existing scheduling behavior remains unchanged.
10. Regression check Basic/Previous question upload, Question Bank and Student Copies.
