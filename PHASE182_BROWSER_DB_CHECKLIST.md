# Phase 182 - Live Browser / DB Checklist

Run once after deployment on staging or with a safe demo Upcoming Test.

## A. Schema
- [ ] Open Admin > System Check.
- [ ] Confirm Weekly Test schema reports Phase 182 reopen fields as available.
- [ ] If ALTER is not permitted automatically, run `sql/phase182_upcoming_test_reopen_access.sql` once.

## B. Live Students - batch-wise
- [ ] Start the same Upcoming Test using students from two different batches.
- [ ] Open Admin > Weekly Tests > Live Test Students.
- [ ] `Live Now by Batch` counts both batches correctly.
- [ ] All Batches shows both students.
- [ ] Selecting Batch A shows only Batch A live students/results.
- [ ] Selecting Batch B shows only Batch B live students/results.
- [ ] For a Common / All-Batch paper, each student is grouped using active batch membership.
- [ ] Expired attempt disappears from Live after refresh and is finalized by server rules.

## C. Accidental Final Submit reopen
- [ ] Student answers a few questions and presses Final Submit manually before timer ends.
- [ ] Admin sees the copy under Submitted / Checked Results as Pending Check.
- [ ] Admin chooses Reopen Test Access, enters reason, leaves `Restore remaining time` selected.
- [ ] Same row moves out of finished results and back into Live Students.
- [ ] Student stays on the same account/login.
- [ ] Student Test Center shows `Admin reopened your submitted test`.
- [ ] `Resume Reopened Test` opens the same attempt.
- [ ] Previously saved answers are still present/editable.
- [ ] Question order/snapshot is unchanged.
- [ ] New timer is approximately the time that remained at the first Final Submit.
- [ ] Student can Final Submit again; copy returns to Pending Check.
- [ ] Admin can check/publish marks normally.

## D. Protection rules
- [ ] Second reopen attempt is blocked.
- [ ] Checked copy cannot reopen.
- [ ] Timer-expired copy cannot reopen.
- [ ] Warning auto-submitted copy cannot reopen.
- [ ] Admin Force Closed copy cannot reopen.
- [ ] Answer-released paper cannot reopen.
- [ ] Top-3 finalized paper cannot reopen.
- [ ] Ended test window cannot reopen.
- [ ] Student with another live Upcoming Test cannot be reopened into a conflicting second live test.

## E. Timer modes
- [ ] Remaining-time mode restores remaining time only.
- [ ] Full-duration override works only while test window has enough time.
- [ ] Neither mode extends beyond `weekly_tests.ends_at`.

## F. Audit / regression
- [ ] Admin audit log records `weekly_test.attempt_reopened` with reason/time information.
- [ ] Normal Close Entry still allows already-started attempts to finish.
- [ ] Force Close still final-submits live attempts.
- [ ] Answer Release still blocks while genuine attempts are running.
- [ ] Finalize Top 3 still requires all copies Checked.
- [ ] Rank 1/2/3 dashboard themes remain Gold/Purple/Parrot Green.
