# Phase 163 — Real Browser / Database Checklist

Use a database backup/staging copy first.

## A. Student forgotten-password control
1. Login as the protected Super Admin / staff with student-management permission.
2. Open `admin/student-view.php?id=<student-id>#password-control`.
3. Set a short test password such as `abc` in both fields (only for this controlled test), leave Reset Note blank, submit.
4. Confirm success message appears.
5. In another/incognito browser, confirm the old student password fails.
6. Confirm the exact new password succeeds.
7. Confirm a previously logged-in student session is invalidated.
8. Restore a stronger password before production use.

## B. Batch-specific Upcoming Test
1. Create/select Batch A and Batch B with active students.
2. Create an Upcoming Test for Batch A with published questions.
3. Click Publish Now.
4. Login as a Batch A student. Test must appear and Start must be enabled unless the anti-repeat gap is legitimately active.
5. Login as a Batch B student. Batch A paper must not be startable.
6. If a legacy Batch A student is denied, open Admin → Student Accounts → that student → Upcoming Test Batch Access and select Batch A.
7. Refresh the student's Test Center. The paper should now be available.
8. Clear manual access and confirm admission-based access, if present, remains unchanged.

## C. Anti-repeat gap
1. Finish Upcoming Test #1 as a student.
2. Publish a different Upcoming Test for the same eligible batch.
3. Before the configured gap ends, confirm Start remains disabled and the exact available date/time is shown.
4. Confirm direct POST/API start is also rejected.
5. For a controlled retest, change Upcoming Test Performance → minimum gap to `0`, refresh, and confirm the next paper becomes eligible.
6. Restore the desired production gap (recommended default 12 hours unless institute policy differs).

## D. Close Entry / Finalize Top 3
1. Publish an Upcoming paper and start it as Student A.
2. While Student A is inside, click Finalize Top 3 as Admin.
3. Confirm new entry closes and Admin is told one attempt is still in progress.
4. Confirm Student A can still resume/finish the existing attempt.
5. Confirm Student B cannot start a new attempt after entry is closed.
6. Submit Student A's paper; before teacher checking, Finalize Top 3 must refuse and ask for review.
7. Mark all submitted copies Checked.
8. Click Finalize Top 3 again; Top 3 should be saved and paper archived.
9. Confirm Upcoming master answers are not exposed before safe closure/finalization.

## E. Multi-batch regression
1. Publish one Upcoming Test for Batch A.
2. Publish another Upcoming Test for Batch B.
3. Confirm both remain independently active for their own batch.
4. Publish a second paper for Batch A; only the previous Batch A active paper should move to draft.
5. Basic and Previous tests should retain their existing global active-paper behavior.
