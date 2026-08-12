# Phase 161 Browser / Database Checklist

1. Login as an Admin with `tests.manage` and open Dashboard.
2. Confirm **Upcoming Test Performance** card is visible and opens the new performance board.
3. Login as a restricted Admin without `tests.manage`; confirm the card/page is inaccessible.
4. Select an Upcoming Test with checked attempts and verify Top 10 order, Top 3 names, average/highest marks and 0–10 counts.
5. Finalize Top 3 and confirm the board switches from provisional to official names.
6. In the performance board set the minimum gap to 12 hours and save.
7. As a student complete Upcoming Test A.
8. Publish/schedule Upcoming Test B inside 12 hours and confirm the student receives the security-lock message and cannot start it.
9. After the gap (or temporarily set the setting to 0 for staging) confirm Test B can start.
10. Confirm the student cannot submit Test A twice.
11. Start an Upcoming Test in one tab and attempt to start another Upcoming paper in a second tab; confirm the second is blocked.
12. Confirm an expired abandoned old attempt does not permanently block a new paper.
13. Set `Available From` to tomorrow and verify the paper stays scheduled until that time; there is no fixed weekend/7-day assumption.
14. Check Dashboard and performance page at 320px, 375px, 768px and desktop widths.
