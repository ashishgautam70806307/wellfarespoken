# Phase 180 - Old Design Restore + Mobile Action Safety

Phase 180 intentionally removes the Phase 177/178/179 mobile redesign direction from the three learning pages and restores the original Phase 174 page source/feature flow.

Pages:
- spoken-materials.php
- free-ai-english-practice.php (real Practice Materials page)
- learning-roadmap.php

Preserved exactly from the old working source after removing the Phase 180 stylesheet include:
- Spoken Materials feature/markup source
- Practice Materials feature/markup/JS source
- Learning Roadmap feature/markup/JS source
- Common includes/header.php
- Common includes/footer.php
- assets/js/phase170-spoken-practice.js

Phase 180 adds only assets/css/phase180-old-design-mobile-fix.css for mobile safety. It does not hide features or create a new header/footer/navigation. It fixes overflow, touch target access, button wrapping, short-height fixed-nav overlap, practice control layout and contact-dock overlap while retaining the original visual design.

Service worker cache: v180.
No database schema change.
