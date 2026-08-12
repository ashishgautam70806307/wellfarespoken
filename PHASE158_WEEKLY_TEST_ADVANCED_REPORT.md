# Phase 158 - Advanced Weekly Test Results, Ranking and Offline Paper

## Scope
This phase extends only the Weekly Test result/review workflow, Upcoming Test ranking, student dashboard presentation and Admin offline-paper workflow. There is no database schema migration in this phase.

## 1. Uploaded master answers in results
- Basic Test: after final submission the result page shows the student's answer and every accepted answer uploaded by Admin (`expected_answer`, including `||` variants).
- Previous Test: same behaviour as Basic.
- Upcoming Test: the student's submitted answer is visible immediately, but the master answer is intentionally locked while the paper is still open. It unlocks after `ends_at` passes or Admin completes/archives the test.
- Reason: immediately exposing an Upcoming Test answer key would allow one student to share the paper answers with students who have not submitted yet.

## 2. Student Dashboard answer history
- Final attempts now include an expandable **Questions & Answers** review inside Weekly Test Result History.
- The review is loaded only when opened, keeping the dashboard fast even when a student has many attempts/questions.
- It shows question, student's answer, marks, and the accepted Admin-uploaded answers when the release policy allows them.
- A complete result link is retained for the full review screen.

## 3. Upcoming Test marks and Top 3
The project already stored `auto_score`/`admin_score` in `weekly_test_attempts`. Phase 158 makes the ranking/finalization workflow safer:
- Upcoming Top 3 cannot be finalized while the paper is still open.
- Ranking cannot run while an attempt is still `started`.
- Ranking cannot run while submitted copies are waiting for teacher grading.
- Once all copies are checked, Admin uses **Complete + Rank Top 3**.
- Existing `weekly_test_winners` stores ranks 1, 2 and 3.
- The student's latest finalized Upcoming Test rank controls the dashboard achievement theme:
  - 1st: Gold
  - 2nd: Violet/Purple
  - 3rd: Parrot Green
- If the latest Upcoming attempt did not achieve Top 3, an older rank does not incorrectly keep recoloring the current dashboard.

## 4. Admin batch-wise offline question paper
New Admin route:
`admin/weekly-test-offline-paper.php?id=TEST_ID&mode=paper`

It is protected by `tests.manage` permission and uses the selected Upcoming Test's linked batch.

Student Paper contains:
- Institute logo/name
- Batch name, timing/days
- Duration and total marks
- Test schedule
- Student Name, Mobile/Roll, Date and Score fields
- Question number, question text, marks and MCQ options when applicable
- Compact handwritten-answer line for each question
- Institute/batch watermark and repeated print footer

Separate Admin Answer Key:
`admin/weekly-test-offline-paper.php?id=TEST_ID&mode=answer-key`

The student paper never contains master answers.

### PDF behaviour
The route is print-optimized A4 HTML. **Save as PDF / Print** opens the browser print dialog; Admin chooses **Save as PDF**. This deliberately avoids adding a heavy server PDF library and is more reliable for Hindi/Devanagari text on normal Chrome/Edge printing.

### Page cutting / 25-question target
- Each question block has `break-inside: avoid` and `page-break-inside: avoid`.
- A compact row size targets roughly 25 short text questions per A4 page.
- This is a target, not a forced rule: long questions, MCQ options, or long Hindi/English content expand naturally and move to the next page instead of being clipped/cut.

## Security / integrity
- Offline paper and answer key are Admin-only (`tests.manage`).
- Answer-key release for Upcoming Tests is server-side, not a CSS/JS hide.
- Dashboard answer review requires an authenticated student and verifies attempt ownership.
- Lazy review responses use no-store/private behaviour.
- Ranking reuses stored attempt scores and existing winners table; no duplicate ranking table or schema was added.

## Validation completed
- 105 PHP files syntax checked: PASS.
- 16 JavaScript/service-worker files syntax checked: PASS.
- 74 CSS files basic brace validation: PASS.
- Phase 148, 149, 150, 151 and 158 static suites: PASS.
- Phase 158 focused static checks: 30 PASS.
- 216 literal local references checked; only one parser-generated string false-positive was ignored, with no genuine missing asset/page reference.
- Duplicate literal HTML IDs: 0.
- Service-worker local references: 51 present, 0 missing.

## Environment limitation
A usable MySQL-backed authenticated browser and printable local HTTP route were not available in the build environment. Therefore real attempt creation/grading, multi-student ranking and the final Chrome/Edge Print -> Save as PDF output must be verified on localhost/staging with the Phase 158 checklist.
