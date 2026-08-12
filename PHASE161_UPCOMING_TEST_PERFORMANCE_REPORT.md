# Phase 161 — Upcoming Test Performance & Flexible Security

## Scope
This phase changes only the Upcoming Weekly Test admin analytics/discoverability and official-test start eligibility. No database schema, question import format, scoring formula, answer-release rule, offline paper, result review, roadmap, voice, PWA, admission, payment or unrelated CMS logic was changed.

## Admin Dashboard
A new **Upcoming Test Performance** card appears for users with `tests.manage` permission. It links to `admin/upcoming-test-performance.php` and previews:
- current/most relevant Upcoming Test title;
- total attempts, checked and pending copies;
- current anti-repeat gap;
- official Top 3 if finalized, otherwise provisional Top 3 from checked copies;
- slow rank-wave animation for #1, #2 and #3.

## Performance Board
The new page provides:
- Upcoming Test selector;
- participation, checked, pending and currently-started counts;
- average/highest checked marks;
- official or provisional Top 3;
- Top 10 leaderboard by checked marks;
- exact 0–10 mark-count distribution plus 11+;
- direct link back to Weekly Test management;
- responsive Admin layout.

Top-10 order uses the same trustworthy tie-break principle as final ranking: highest score first, then earlier submission.

## Flexible schedule logic
There is **no fixed 7-day/weekend lock**. Existing `starts_at` / `ends_at` remain the source of truth, so Admin can schedule an Upcoming Test tomorrow or on any date/time.

## New anti-repeat security
A new application setting `weekly_upcoming_min_gap_hours` controls the minimum time between two *different* Upcoming Tests for the same logged-in student.

Default: **12 hours**.
Allowed Admin range: **0–168 hours**.
`0` is an explicit Admin override for a special retest.

Security rules:
1. The same Upcoming paper still allows only one submitted/checked attempt per student.
2. A different Upcoming paper is blocked until the configured gap passes after the student's latest submitted/checked Upcoming Test.
3. A student cannot open a second Upcoming Test while another non-expired Upcoming attempt is still running.
4. Official test starts lock both the selected test row and the student row (`FOR UPDATE`) so concurrent/double-click starts cannot race across different papers.
5. Expired abandoned attempts from another paper do not permanently lock the student.
6. The Test Center shows the lock message and disables the initially selected Upcoming Test CTA, while the API remains the final server-side authority.

## Why this solves the business case
- Admin can run a test tomorrow; no weekly calendar assumption exists.
- Students cannot immediately jump into a newly published Upcoming paper after completing one unless the configured security gap has passed.
- Admin can shorten the gap for a legitimate closely scheduled exam or explicitly set 0 for a controlled retest.
- One-attempt-per-paper and server row-lock protections remain active independently of this setting.

## Permissions
`admin/upcoming-test-performance.php` maps to `tests.manage`. It cannot be used by an Admin role without Weekly Test management permission.

## Validation
- All 109 PHP files: syntax PASS.
- All 16 JavaScript/Service Worker files: syntax PASS.
- 78 CSS files: brace-balance PASS.
- Service Worker pre-cache: 53 assets, 0 missing.
- Phase 148/149/150/151/158/159/160/161 static suites: PASS.
- Phase 161 focused checks: dashboard card, permission map, Top 10, 0–10 distribution, Top 3 animation, student row lock, cross-paper eligibility, UI message and SW v161: PASS.

## Live checks still required
This build environment does not have the project's real MySQL data or authenticated student browsers. Test the following on localhost/staging:
- submit one Upcoming Test then try another before the configured gap;
- verify the second opens after the gap;
- set gap to 0 and verify deliberate retest behavior;
- verify same-paper second attempt remains blocked;
- open two browser tabs and double-start different Upcoming tests;
- verify Top 10/Top 3 with real checked scores;
- verify responsive Admin performance board on phone/tablet/desktop.
