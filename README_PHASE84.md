# Phase 84 - Spoken Materials Speed + Free AI Link Removal + Practice UX

Fixes:
1. spoken-materials.php speed
   - material_ensure_schema() now runs once per request.
   - Heavy material CREATE/ALTER/seed checks now run once per install/version.
   - Marker saved: material_schema_marker = phase84_material_schema_v1

2. Removed free-ai-english-practice.php links from visible places
   - Header practice dropdown now points to Spoken Practice + Learning Roadmap.
   - Footer practice links now point to Spoken Practice + Learning Roadmap.
   - Admin sidebar AI Practice Lab link removed.
   - Admin system-check test button now points to spoken-materials.php.
   - File free-ai-english-practice.php is kept to avoid breaking old direct URLs.

3. Roadmap lesson practice UX
   - Start Practice scrolls to the practice panel instead of footer/bottom.
   - Start and Continue buttons are in one row.
   - Practice options are slightly smaller and cleaner.
   - Correct feedback Good/Amazing/Excellent is now spoken with browser voice.
