# Phase 109 - Weekly Test Publish/Pending Fix

Root cause fixed:
- Question rows were Active, but the selected test paper itself could still be Draft/Pending.
- The frontend only starts a test when the test paper is Published/Active and it has active questions.

Added:
1. Admin > Weekly Tests > Test Setup now shows a clear Student Status panel.
2. New Publish Now button:
   - sets selected paper status to Active
   - sets test published to Yes
   - clears Available From/Until blocks
   - activates all questions in that selected paper
3. New Set Pending button:
   - moves selected paper back to Draft/Pending
   - students cannot start it
4. Status wording changed:
   - Pending / Draft
   - Published / Active
   - Archived
5. Frontend check is now case-insensitive for active status.
6. API start validation is now case-insensitive and gives a clearer message.
7. If the paper is not published, frontend message tells admin to use Publish Now.

How to use:
- Go to Admin > Weekly Tests
- Select Basic / Previous / Upcoming
- Select exact Existing Test paper
- Upload/confirm active questions
- Click Publish Now
- Open weekly-test.php and start the selected paper

Important:
- Active question rows alone are not enough. The test paper must be Published/Active too.
