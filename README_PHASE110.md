# Phase 110 - Weekly Test Active/Publish + AJAX Fix

Root cause fixed:
- Admin AJAX forms were posting to weekly-tests.php, which loads the admin HTML header before returning JSON. Browser received HTML instead of JSON, so Save/Publish was failing.
- Because Publish Now was failing, starts_at/ends_at schedule was not being cleared, so frontend said: test is scheduled for later.

Fixes:
1. All AJAX admin weekly-test forms now post to admin/weekly-test-ajax.php.
2. weekly-test-ajax.php now supports:
   - save_test
   - publish_test_now
   - set_test_pending
   - upload_questions
   - clear_questions
   - save_question
   - grade_attempt
   - reset_attempt
   - create_demo_batch_tests
3. Publish Now now sets:
   - weekly_tests.status = active
   - weekly_tests.published = Yes
   - starts_at = NULL
   - ends_at = NULL
   - all selected paper questions published = Yes
4. Frontend weekly-test.php now blocks clearly if schedule is not open and tells admin to use Publish Now or check Available From/Until.
5. Admin upload/setup cards spacing fixed so Publish/Set Pending buttons no longer overlap the file field.
6. Added Create 2 Demo Batch Papers button for Previous/Upcoming tabs. It creates Morning/Evening demo papers with 30 active questions so admin can test the flow quickly.

Exact use:
Admin > Weekly Tests > Upcoming Test > select paper > Publish Now.
If testing data is needed: click Create 2 Demo Batch Papers, then open weekly-test.php.
