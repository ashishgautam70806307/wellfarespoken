# Phase 111 - Weekly Test Batch Cards, Random Paper Order, Winners, UI Fix

Main weekly test fixes:

1. Admin bottom question pagination removed.
   - Question pagination now remains in the top/right tools area only.

2. Batch/Test Paper cards added in Admin Weekly Test.
   - Each Basic / Previous / Upcoming paper appears as a card.
   - Published cards show light green.
   - Pending cards show light yellow.
   - Scheduled/closed papers are clearly labeled.
   - Clicking a paper card opens the same page scoped to that paper, so upload, questions and student copies are related to the selected paper only.

3. Publish/Pending controls added directly on paper card.
   - Publish makes the selected paper active, clears schedule block and activates questions.
   - Pending hides the paper from student start.

4. Batch completion / winner feature added.
   - Complete Test button computes Top 3 from submitted/checked copies.
   - Winners are published for 2 days in weekly_test_winners.
   - Winner board displays on weekly-test.php with green celebration styling and flowers animation.

5. Random question order for each student.
   - When students start the same test at the same time, question order is shuffled per attempt.
   - Attempt-specific question order is saved in weekly_test_attempts.question_order.
   - Resume and exam room keep the same saved order.
   - If admin sets Shuffle Questions = No, order remains serial.

6. Large question banks supported.
   - If a paper has 100 questions but total_questions is 30, system fetches the question pool, shuffles it, then gives only the required count.

7. Admin UI polish.
   - Admin-only border radius standardized to 7px for cards, inputs, buttons and key panels.
   - Test Setup / Upload layout spacing improved.
   - Paper card layout uses responsive grid.

8. Multiple accepted answers remain supported.
   - Excel/CSV expected_answer column can use new line, ||, or ;.
   - Example: I cannot go || I can't go

Files changed:
- admin/weekly-tests.php
- admin/weekly-test-ajax.php
- weekly-test.php
- weekly-test-api.php
- weekly-exam-room.php
- includes/functions.php
- assets/css/style.css
