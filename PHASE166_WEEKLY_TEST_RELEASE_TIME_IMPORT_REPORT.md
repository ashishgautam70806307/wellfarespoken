# Well Fare English Spoken — Phase 166 Report

## Scope

Phase 166 fixes three requested areas only: manual Upcoming answer-key release, 12-hour AM/PM Admin time inputs, and a clearer Weekly Test question-upload/manual-entry workflow.

## 1. Upcoming Answer Key Release

Each Upcoming Test paper card now has two clearly different answer-key actions:

- **Admin Answer Key** — opens the confidential Admin-only printable answer key.
- **Release Answer Key** — releases the uploaded accepted/master answers to students who submitted that Upcoming Test.

Security rules for manual release:

1. The paper must be an Upcoming Test.
2. At least one student copy must have been submitted/checked.
3. No student attempt may still be `started`.
4. New entry must be closed, or the configured `Available Until` time must already have passed.

If the paper is still open, the release request is rejected with a clear message instructing Admin to use **Close Entry** first. Re-publishing/re-opening that paper clears the manual release flag so an old answer key cannot leak into a reused test.

The normal automatic release remains unchanged: Upcoming master answers are still shown after the scheduled end time or after finalization/archive even when Admin does not use the manual button.

## 2. 12-Hour AM/PM Time Inputs

All existing Admin `datetime-local` inputs are enhanced at runtime into:

- Date
- Hour: 1–12
- Minute: 00–59
- AM / PM

The original field name/value remains the same hidden backend field, so existing PHP/database code continues receiving `YYYY-MM-DDTHH:MM`. No database or controller contract changed.

Current affected Admin inputs include:

- Weekly Test: Available From
- Weekly Test: Available Until
- Enquiry: Last Contacted
- Admission Ledger: Entry Date & Time

Free-text batch/class timing fields already use AM/PM examples and remain unchanged so existing flexible descriptions are not broken.

## 3. Upload Questions / Answer Sheet

The Admin help now documents the exact importer columns:

`question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d`

Rules shown in Admin:

- `question_text`: required
- `expected_answer`: fill for automatic checking and result answer display
- `question_type`: optional accepted types
- `topic_name` / `level`: Admin metadata only; not revealed as student hints
- `marks`: defaults to 1
- `option_a`–`option_d`: for MCQ
- multiple accepted answers: separate with `||`

A new blank Excel template is included. Its first worksheet contains only the exact import headers; a second Instructions sheet explains each field. The existing Basic/Previous/Upcoming sample CSV files were not modified.

If Admin has no Excel file, **Add Question Manually** opens the existing Question Bank editor, which stores questions in the same database table as imported rows.

The testing-only **Create 2 Demo Batch Papers** button is no longer displayed in the normal production Admin UI.

## Database / Business Logic Impact

- No schema migration.
- No scoring change.
- No ranking change.
- No student-paper hint change.
- No Excel parser contract change.
- No Basic/Previous test-flow change.

## Validation

- Full PHP syntax validation passed.
- Full JavaScript syntax validation passed.
- Phase 148/149/150/151/158/159/160/161/162/163/164/166 static suites passed.
- New XLSX first-sheet headers match the actual importer exactly.
- Existing Weekly Test sample CSV hashes are unchanged from Phase 165.
- Service Worker cache is v166 and all listed assets exist.
- Duplicate literal HTML IDs check passed.
