# Phase 100 - Important Weekly Test Fixes

Implemented from current project priority list:

Frontend CBT exam:
1. Status colors fixed:
   - Current = Blue/Navy
   - Answered = Green
   - Not Answered = Red
   - Visited but Not Answered = Orange
   - Not Visited = Grey
2. Final Submit button remains visible in right sidebar.
3. Bottom/near-answer submit remains hidden to avoid accidental tap.
4. Timer bug fixed:
   - Remaining time is calculated from started_at + admin duration.
   - Remaining time is capped by duration_minutes, so a 30-minute test cannot show 239 minutes.
5. Question font and textarea made more compact.
6. Per-question timing log added.
7. Security warnings now can mark attempt as suspicious after admin warning limit.
8. Question order can be shuffled and saved per attempt.
9. Options can be shuffled.
10. Exam warnings/timing logs are saved for admin review.

Admin:
1. Weekly Test settings now include:
   - Shuffle Questions
   - Shuffle Options
   - Warning Limit
2. New test defaults:
   - 10 questions
   - 10 minutes
   - 10 marks
3. Admin review now shows:
   - warning count
   - suspicious flag
   - protected token status
   - activity warning log
   - question time log
4. Multiple accepted answers continue to work with new lines, || or ;.

Important:
A normal PHP/browser website still cannot fully lock a student's phone like a government CBT center.
This phase implements the strongest web-only controls currently inside this project without SEB/native kiosk.
