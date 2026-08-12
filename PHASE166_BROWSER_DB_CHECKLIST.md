# Phase 166 Browser / DB Checklist

Use localhost/staging with a database backup.

## Upcoming Answer Release

- [ ] Publish an Upcoming Test and submit it as a student.
- [ ] While paper is still open, click **Release Answer Key**; confirm it is blocked.
- [ ] Use **Close Entry**.
- [ ] Confirm no student attempt is still running.
- [ ] Click **Release Answer Key**; confirm success and “Answers Released” state.
- [ ] Open the student's Weekly Result / Dashboard Q&A review; uploaded accepted answers should now be visible.
- [ ] Re-publish the same Upcoming paper; confirm the release state is locked again.
- [ ] Confirm scheduled automatic answer release after `Available Until` still works without using the button.

## 12-Hour Time UI

- [ ] Weekly Test Available From shows Date + 1–12 Hour + Minute + AM/PM.
- [ ] Weekly Test Available Until shows Date + 1–12 Hour + Minute + AM/PM.
- [ ] Edit an existing Weekly Test and verify saved 24-hour DB time converts back to the correct 12-hour UI.
- [ ] Save and re-open; verify the database time remains correct.
- [ ] Enquiry Last Contacted uses the same UI and saves correctly.
- [ ] Admission Ledger Entry Date & Time uses the same UI and saves correctly.
- [ ] Leave a schedule fully blank; save should still work.
- [ ] Partially fill a date/time; form should ask for all Date/Hour/Minute/AM-PM pieces.

## Excel / Manual Questions

- [ ] Download `Blank Excel Template` from Weekly Tests.
- [ ] Confirm Questions sheet first row is exactly: question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d.
- [ ] Add 2–3 rows and upload; confirm they appear in the selected Question Bank.
- [ ] Put two accepted answers separated by `||`; confirm both appear in Admin answer key/result review after release.
- [ ] Click **No Excel? Add Manually** and confirm the manual question editor opens.
- [ ] Save a manual question; confirm it appears in the same Question Bank.
- [ ] Confirm Basic/Previous/Upcoming CSV example downloads still work.
