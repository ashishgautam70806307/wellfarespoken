# Phase 162 — Batch-wise Upcoming Test Performance Verification

This phase is a focused correction on top of Phase 161. It does not change the database schema, scoring formula, answer-release rules, offline-paper content, ranking storage, or unrelated modules.

## What was wrong in Phase 161

The Phase 161 code did contain a Top 10 board and Top 3 cards, but the user’s requested flow was not represented clearly enough:

1. The Admin Dashboard placed **Batch-wise Question Papers & Answer Keys** and the `grid-4 admin-dashboard-links` block below other dashboard sections instead of at the top.
2. The Dashboard merged the Top 3 preview inside the general performance card instead of giving winners their own card.
3. The performance page selected a test directly and did not make the batch-first workflow explicit.
4. A deeper business-logic gap existed: activating an Upcoming Test still deactivated every other active Upcoming Test globally, so two different batches could not independently have active Upcoming papers.
5. A logged-in student was not authoritatively checked against the batch assigned to a batch-specific Upcoming Test.

## Phase 162 corrections

- Dashboard priority order is now: Admin heading → Batch-wise Question Papers & Answer Keys → dashboard quick-link grid → Upcoming performance + separate winner card → security/student/director/other sections.
- Dashboard has a separate animated **1st – 3rd Winners** card using Gold, Purple and Parrot Green rank colors.
- Performance board now uses **Choose Batch → Choose Test** and labels the leaderboard as **Top 10 — [Batch Name]**.
- Winner podium remains separate from Top 10 on the performance page.
- Upcoming Test activation is scoped per batch. Basic/Previous behavior remains global, but Batch A and Batch B can each have their own active Upcoming Test.
- Batch-specific Upcoming Test starts are now server-side protected: only students with an active batch membership or matching valid admission may start that paper. Common/All-Batches papers remain available to all active logged-in students.
- The Student Test Center filters Upcoming papers to the logged-in student’s eligible batch(es), while the API repeats the same check authoritatively.
- Admin save messages now accurately explain same-batch activation behavior.
- Dashboard/performance button typography is smaller and bounded for dense grids and mobile layouts.

## No schema change

No SQL migration is required for Phase 162. It reuses the existing `weekly_tests.batch_id`, `student_batch_memberships`, `student_enrollments`, and `admissions.batch_id` relations introduced earlier.

## Important test note

Real MySQL data is still required to prove batch membership behavior with actual students. Test at least two batches with two active Upcoming Tests at the same time and verify each student sees/starts only their own batch paper plus any Common/All-Batches paper.
