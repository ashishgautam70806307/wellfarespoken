# Phase 165 — Upcoming Test Flow + No-Hint Student Paper

## Scope
Focused correction on top of Phase 164. No database schema change. No scoring, ranking, answer-release, offline-answer-key, Excel-import, dashboard-winner or unrelated module changes.

## Root cause found
The older Upcoming Test flow allowed a published official paper to be taken by logged-in students. Later batch-wise reporting work introduced strict student-to-batch authorization as a default requirement. That turned a reporting label into an access gate and caused legacy/self-registered students to see `Batch access needed` or `No paper` even when Admin had published a valid paper.

## Fix
- Added `weekly_test_batch_restriction_enabled()`.
- Default is OFF (`weekly_upcoming_enforce_batch_access=0` when no setting exists).
- Default behavior is restored: one published/open Upcoming paper is available to active logged-in students, while its batch is still retained for batch-wise reporting, Top 10, winners and offline-paper organisation.
- With default mode, publishing an Upcoming paper again keeps one active Upcoming paper globally, matching the earlier predictable flow.
- Strict batch authorization remains available as an opt-in setting. If explicitly enabled, the existing membership/admission/manual-access checks still apply.
- The API uses the same server-side eligibility helper, so frontend and official start logic remain consistent.

## Student question-paper privacy
- Offline Student Paper now fetches only safe fields: question text, MCQ options, marks and ordering.
- Topic/tense/use, question type, level and expected/master answers do not enter the Student Paper render data at all.
- Admin Answer Key still keeps topic/type and accepted answers.
- Student final result no longer displays Topic/Tense/Question Type labels above questions; it shows only `Question`, marks and result state.
- Weekly Exam Room already sends/displays only question text/options/marks and remains unchanged.

## Deployment note
No SQL migration is required. Replace the code, clear the service worker/site cache, hard-refresh, then publish the intended Upcoming paper again so any older active-paper state is normalized by the corrected publish logic.
