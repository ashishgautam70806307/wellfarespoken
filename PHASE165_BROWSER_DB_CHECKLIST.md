# Phase 165 — Browser / Database Checklist

1. Backup the live database before deployment.
2. Deploy Phase 165 files; no SQL import is required.
3. Clear site data / unregister old service worker and hard refresh.
4. Admin → Weekly Tests → Upcoming: publish the intended paper again.
5. Confirm exactly one Upcoming paper is Published/Active in normal/default mode.
6. Login as an existing legacy/self-registered active student with no manual batch membership.
7. Open `weekly-test.php`: Upcoming card must show the published paper, real duration/question count and `Exam open` when schedule is open.
8. Start the test; API must create/resume the attempt without the old red batch-access warning.
9. Confirm anti-repeat/cooldown and one-attempt protections still work.
10. Open Student Paper/PDF: no Present Simple / tense / use / Hindi-to-English / question-type / level / expected-answer hint may appear.
11. Open Admin Answer Key: metadata and accepted answers may still appear for staff.
12. Submit a test and open Weekly Result: question cards must not show topic/tense/question-type labels.
13. Verify Top 10 / Top 3 / batch-wise Admin reports still identify the paper batch.
14. If strict batch authorization is deliberately required later, set `weekly_upcoming_enforce_batch_access` to `1`; then test membership/admission/manual access before enabling it in production.
