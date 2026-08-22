# Phase 182 - Upcoming Test Live Students + Safe Reopen Access

## Goal
Give Admin a clear batch-wise view of students who are **currently taking** an Upcoming Test, keep finished results separate, and safely restore access when a logged-in student accidentally presses **Final Submit**.

## Admin flow
Open **Admin > Weekly Tests > Live Test Students**.

1. **Live Now by Batch** shows current in-progress counts across Upcoming Tests.
2. Use **Batch**, **Test Paper**, **Result Status**, and **Search Student** filters.
3. **Live Test Students** contains only `status=started` attempts. Expired timers are finalized before the list is loaded.
4. **Submitted / Checked Results** is a separate list; live attempts never appear in it.
5. A safe Pending Check copy can show **Reopen Test Access**.

### Batch behavior
- Assigned test paper: the paper's assigned batch is authoritative.
- Common / All-Batch paper: the student's latest Active batch membership is used.
- Default page scope is **All Batches**.

## Reopen Test Access
Reopen is intentionally **not a retake** and does not create a second attempt.

It keeps:
- same student login
- same attempt ID
- immutable question snapshot
- saved answers
- warning history

It rotates access/result tokens, clears grading metadata, changes the same attempt back to `started`, and gives a new server timer.

### Time choices
- **Restore remaining time (recommended):** restores the time left at the accidental Final Submit.
- **Give full test duration (Admin override):** gives the original duration, but never beyond the test window.

### Reopen is blocked when
- attempt is already Checked or still running
- submission happened from timer expiry, warning auto-submit, or Admin Force Close
- attempt was already reopened once
- answer key/review was released
- Upcoming Test window has ended/final stage started
- Top-3 has been finalized
- student already has another live Upcoming Test

Every successful reopen is audit logged with Admin, reason, time mode, granted seconds, and timestamp.

## Student flow
No second login or special account is created.

On the student's existing **Test Center (`weekly-test.php`)** a highlighted **Admin reopened your submitted test** banner appears with **Resume Reopened Test**. The existing attempt history also remains resumable. If an old result URL is opened while the attempt is reopened, it redirects back to the exam room.

## Database change
Phase 182 adds these columns to `weekly_test_attempts`:
- `reopen_count`
- `reopened_at`
- `reopened_by_admin_id`
- `reopen_reason`
- `reopen_time_mode`
- `reopen_seconds_granted`
- `first_submitted_at`

The application schema ensure can add them automatically when the DB user has ALTER permission. A dedicated migration is also included:

`sql/phase182_upcoming_test_reopen_access.sql`

## Preserved safety
Phase 182 preserves Phase 173 timer/Force Close/Top-3 safety, answer-key gating, immutable question snapshots, server autosave/resume, Phase 174 security hardening, Phase 180 old learning design, and Phase 181 image/file lifecycle.

## Validation
Static/regression validation covers PHP syntax, JavaScript, CSS, Service Worker precache, Phase 148/150/151/163/164/170/174/180/181, and Phase 182 rules.

A real authenticated MySQL/browser test is still required after deployment because the build environment does not have the live application database.
