# Phase 163 — Student Password + Upcoming Test Flow Repair

## Scope
This phase is a focused correction on top of Phase 162. No database schema, scoring formula, answer-release policy, ranking storage, offline-paper format, dashboard winner design, or unrelated module was changed.

## Root causes found

### 1. Admin student password control felt broken
The Admin student detail page still applied the normal student self-registration password rule and required an extra reset reason. That conflicted with the intended institute-assisted forgot-password workflow, where Admin should be able to set the exact agreed password without knowing the old password.

### 2. Published Upcoming Test could still be unavailable
Publishing alone is not enough for a batch-specific official Upcoming Test. A logged-in student must also have authoritative access to that test's batch. Older/legacy admissions may not have been connected to the newer enrollment/membership tables, so the test was filtered from the student's Test Center even though the paper was Published/Active.

A second valid lock can also exist: the configured anti-repeat Upcoming Test gap (default 12 hours). Phase 162 calculated this on the server, but the browser-side setup script could re-enable the Start button after page load because it knew about batch readiness but not the student's cooldown/account eligibility.

### 3. Top-3 finalization produced a confusing “test still open” dead end
The previous flow required Admin to manually close/wait before ranking. Phase 163 makes `Finalize Top 3` close *new entry* automatically when necessary, while still protecting students who are already inside the exam and copies that are still waiting for teacher review.

## Fixes

### Admin-assisted forgotten-password reset
- Admin can set any non-blank password up to 128 characters.
- The student self-registration rule remains minimum 8 characters; it was not weakened.
- Reset note is optional and gets a safe default audit reason if omitted.
- Show/Hide controls are available for New Password and Confirm Password.
- Existing password hashing, audit logging and session invalidation remain in place.
- Existing sessions are invalidated after a successful reset.

### Upcoming Test Batch Access on Student Account
A new `Upcoming Test Batch Access` control is available on `admin/student-view.php`.

It can grant/revoke a manual Weekly Test batch assignment without modifying the student's Admission record. It uses the existing lifecycle tables with a dedicated `Weekly Test Access` learning-only enrollment marker.

The server eligibility check now:
1. accepts Common / All-Batches Upcoming Tests for active logged-in students;
2. checks active batch membership;
3. checks a valid linked Admission batch;
4. for verified legacy student identities only, safely retries lifecycle linking from the existing verified Admission;
5. otherwise returns a clear Admin action telling staff to use Student Accounts → Upcoming Test Batch Access.

Unverified self-entered mobile numbers are never auto-linked to an Admission.

### Upcoming Test Test-Center lock state
Each Upcoming paper option now carries both:
- batch eligibility, and
- student/account/cooldown eligibility.

The browser script is not allowed to re-enable Start Test when either server-derived eligibility is denied. The exact server reason remains visible (wrong/missing batch or anti-repeat time lock).

### Close Entry and Finalize Top 3
Admin wording/actions are now explicit:
- `Publish Now` — opens the paper for eligible students;
- `Close Entry` — blocks new starts;
- `Finalize Top 3` — closes new entry automatically if still open, then checks safety conditions and ranks only when ready.

Finalization still refuses while:
- any student attempt is `started`; or
- any submitted copy is still `submitted` rather than teacher-checked.

`Close Entry` does **not** overwrite `ends_at`. This is intentional: shortening the scheduled end could reveal the Upcoming master answer while a student who already started the exam is still working.

### Resume after Admin closes entry
A student who already has a valid live `started` attempt may resume it after Admin clicks Close Entry. New students cannot start after closure.

## Regression verification
- Phase 148 static suite: PASS
- Phase 149 static suite: PASS
- Phase 150 static suite: PASS
- Phase 151 static suite: PASS
- Phase 158 static suite: PASS (updated only for the safer Phase 163 labels/messages)
- Phase 159 static suite: PASS
- Phase 160 static suite: PASS
- Phase 161 static suite: PASS
- Phase 162 static suite: PASS (cache assertion made forward-compatible)
- Phase 163 focused suite: PASS
- PHP syntax: PASS
- JavaScript syntax: PASS
- CSS brace sanity: PASS
- Service-worker local assets: PASS
- Literal local references: PASS

## Real-environment checks still required
The build environment does not have the user's live MySQL data/session/browser state. Test the exact real workflow using `PHASE163_BROWSER_DB_CHECKLIST.md` after installing Phase 163.
