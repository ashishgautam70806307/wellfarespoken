# Phase 86 - Admin Roadmap Records + Pagination Fix

Fixed:
1. Admin roadmap records now show with pagination.
2. 25 records per page.
3. Search added for key, answer, Hindi, tag, notes.
4. Topic dropdown now shows record count beside each topic:
   Use of Has / Have (50)
5. If selected unit_id is invalid for current tab, admin automatically opens the first valid topic.
6. Empty state now tells admin to check topic dropdown counts.
7. Table spacing and readability improved.
8. Bulk delete remains available.

Why this was needed:
- Excel may upload into one topic while admin was viewing another topic.
- Without counts and pagination it looked like data was missing.
- Large Excel imports needed paginated listing.
