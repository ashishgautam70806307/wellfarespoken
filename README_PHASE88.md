# Phase 88 - Roadmap Page Unresponsive Fix

Issue:
- Browser showed Page Unresponsive on roadmap-lesson.php?id=16.
- Root cause was JavaScript infinite loop while generating 4 answer choices.
- If only 1 unique answer/options existed after duplicate data filtering, the loop never ended.

Fix:
1. Removed endless random while loop.
2. Options now use unique pool and a safe for-loop.
3. If only 1 or 2 unique options exist, it shows only available options instead of freezing.
4. Backend duplicate-topic fallback query optimized:
   - First finds matching units.
   - Then loads rows by unit_id.
   - Avoids heavy OR joins across all roadmap_items.
5. Practice rows are deduplicated and capped to 80 rows for safe browser performance.

Check:
- Open roadmap-lesson.php?id=16
- Page should not freeze.
- Practice should show Has/Have rows loaded from admin topic.
