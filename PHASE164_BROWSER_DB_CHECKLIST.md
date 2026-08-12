# Phase 164 — Browser + Database Checklist

Use two test student accounts and at least two batches.

## A. Published paper visibility

1. Admin creates/selects an Upcoming paper for Batch A with 30 active questions.
2. Click **Publish Now**.
3. Confirm Admin card is Published/Active.
4. Login as a Batch A student.
5. Open `/weekly-test.php`.
6. Upcoming card must show the real paper and `Exam open`, not `No paper`.
7. Open Upcoming setup and confirm Start Test is enabled unless cooldown is active.

## B. Wrong/unlinked batch

1. Login as a Batch B or unassigned student.
2. Open `/weekly-test.php`.
3. The published Batch A paper should still be discoverable.
4. It must show `Batch access needed` / exact explanation.
5. Start Test must remain disabled.
6. Direct POST to `weekly-test-api.php` must return 403 for batch denial.

## C. Manual batch access

1. Admin → Student Accounts → affected student.
2. Upcoming Test Batch Access → select Batch A → save.
3. Reload student Test Center.
4. Paper should become eligible immediately (subject to cooldown).

## D. Publish sync for already-linked admissions

1. Use a student whose `admissions.student_id` and `admissions.batch_id` already point to Batch A.
2. Publish Batch A Upcoming paper again.
3. Verify the student can enter without separately creating manual access.

## E. Cooldown

1. Submit Upcoming Paper 1.
2. Publish Upcoming Paper 2.
3. If configured gap is active, Paper 2 remains visible but locked with exact available time.
4. After the gap or Admin setting 0, Start becomes available.

## F. Close Entry race

1. Open Upcoming setup but do not start yet.
2. Admin clicks Close Entry.
3. Student immediately presses Start.
4. API must reject the new start.
5. A student who had already started before Close Entry must still be able to resume until their own timer ends.

## G. Final submit

1. Start a short test.
2. Click Final Submit rapidly twice.
3. Only one final result should be created.
4. Repeat with timer expiry; no duplicate submit/result should appear.

## H. Regression

Verify Basic, Previous, Upcoming result review, Admin checked copies, Top 3 finalization, offline Student Paper and Answer Key.
