# Phase 95 - Weekly Test Start Flow Fixed

Fixed:
1. Removed duplicate left-side Select Test list.
2. Top 3 tabs are now the only test selector.
3. Basic Test auto-selects on page load.
4. Tab click auto-selects Basic / Previous / Upcoming test.
5. Start button activates only after:
   - test exists
   - status is active
   - name is valid
   - mobile is 10 digits
   - upcoming login condition is satisfied
6. Start API call now receives selected test correctly.
7. Design is compact, exam-style and no repeated same feature blocks.

Check:
- weekly-test.php
- Enter name + 10 digit mobile
- Basic tab should allow Start Test immediately.
