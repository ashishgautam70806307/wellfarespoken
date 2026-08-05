# Phase 105 - Weekly Test Admin Scope, Dashboard Counts and Spacing Fix

Changes included:

1. Weekly Test admin page now works by selected test type scope:
   - Basic tab shows only Basic test setup, question bank and student copies.
   - Previous tab shows only Previous records.
   - Upcoming tab shows only Upcoming records.
   - Question bank pagination/search preserves selected type and test.
   - Student copy pagination/search preserves selected type and test.

2. Student Answer Copies section:
   - Cards are filtered by selected test type by default.
   - Card layout has better spacing, padding, hover and responsive behavior.
   - Cards now show only relevant stats for the current type.

3. Student record page:
   - Supports type filter from copy cards.
   - Date-wise records are cleaner.
   - Questions and logs stay inside accordions, not open by default.
   - Type filter tabs added: All, Basic, Previous, Upcoming.

4. Weekly dashboard cards:
   - Counts are scoped to selected test type.
   - Better spacing and card design.

5. Main admin dashboard counts:
   - Table detection made more reliable using information_schema with fallback.
   - Weekly test/question/copy counts should no longer show zero because of SHOW TABLES placeholder issues.

6. UI/UX cleanup:
   - More breathing space between cards.
   - Buttons and filters improved for responsive layout.
   - Admin cards, student cards, record cards, date groups, and question sections are visually separated.

PHP syntax check passed for modified files.
