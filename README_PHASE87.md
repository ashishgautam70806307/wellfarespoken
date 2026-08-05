# Phase 87 - Roadmap Frontend Data Not Showing Final Fix

Issue:
- Admin data is in unit_id=6: Use of Has / Have (150 records)
- Frontend opens unit_id=16: Use of Has/Have
- Previous fallback used published='Yes' only. Some roadmap units use published='Y', so fallback failed.

Fix:
1. roadmap_fetch_unit_items_smart() now accepts published values:
   Yes, Y, 1, NULL, empty
2. Strong title normalization added:
   Use of Has/Have = Use of Has / Have
3. Fallback also searches item_key and type tag.
4. Frontend note now shows source topic id if fallback loads data:
   Practice data loaded from: Use of Has / Have (#6)

Check:
- Open roadmap-lesson.php?id=16
- Has/Have records from admin unit_id=6 should now show.
