# Phase 108 - Weekly Test Active/Save Fix

Fixed:
1. Admin Weekly Test Save now posts to weekly-tests.php itself. This removes the 404 HTML error from weekly-test-ajax.php.
2. Frontend weekly-test.php now prefers ready active papers and shows a clear reason when a paper is draft/inactive or has no active questions.
3. Same 10 digit mobile number now merges through canonical_phone even if the student name changes.
4. Student record page also searches by canonical phone.
5. Added admin helper note: questions active is not enough; Test Status must also be Active and time window must allow the current time.

For Upcoming test to work:
Admin > Weekly Tests > Upcoming Test > select exact paper > Status = Active > Save Test.
Also ensure active questions exist and Available From/Until is blank or includes the current time.
