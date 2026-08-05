# Phase 85 - Roadmap Uploaded Excel Data Not Showing Fix

Problem:
- Admin Excel upload can be imported into a duplicate/same-title roadmap unit.
- Frontend roadmap-lesson.php opens a different unit_id.
- Exact unit_id query returned no rows, so frontend showed no data.

Fix:
1. Added roadmap_fetch_unit_items_smart().
2. Frontend first checks exact unit_id.
3. If no rows found, it searches same unit_type + similar unit title.
   Example: "Use of Has / Have", "Use of Has/Have", duplicate Has Have unit.
4. roadmap-lesson.php now uses smart fetch.
5. Admin import now handles UTF-8 BOM and skips empty rows.

How to check:
- Open admin/roadmap.php?tab=uses
- Confirm rows exist under the Has/Have topic.
- Open frontend roadmap lesson for Has/Have.
- If fallback is used, a small note appears: Practice data loaded from: <source topic>.
