# Phase 91 - Frontend Font Letter Spacing Fix

Issue:
- Large frontend headings looked like words were joined:
  "UseofWas/Were"
  "Selectthecorrectanswer"
- Cause: previous global negative letter-spacing was too strong for large bold headings.

Fix:
1. Frontend-only heading typography override added.
2. Negative letter-spacing reduced heavily.
3. Word spacing restored for large headings.
4. Roadmap lesson headings and practice headings specifically fixed.
5. Admin pages are not affected.

Check:
- roadmap-lesson.php?id=7
- roadmap-lesson.php?id=6
- spoken-materials.php
- course-detail.php
- home page large headings
