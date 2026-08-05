# Phase 74 - Dynamic Learning Roadmap Engine

What is added:
- New app-style learning-roadmap.php page.
- Dynamic roadmap database schema:
  - roadmap_groups
  - roadmap_units
  - roadmap_items
  - student_roadmap_progress
- Auto starter roadmap:
  1. Basic Pronouns
  2. Demonstrative Words
  3. Daily Word Meaning
  4. Verb Forms V1/V2/V3
  5. Use of Is/Am/Are
  6. Use of Has/Have
  7. Use of Was/Were
  8. Use of Has To / Have To
  9. Use of Should / Should Have
  10. Use of Can / Could / Must
  11. Present Simple
  12. Present Continuous
  13. Past Simple
  14. Future Simple
- Pronoun and demonstrative starter data included.
- Admin Roadmap Manager added at admin/roadmap.php.
- Admin sidebar and admin search include Learning Roadmap link.
- Frontend roadmap has mobile app style vertical path, active/locked/completed UI, points, current target and jump buttons.

Notes:
- The system seeds default roadmap only if roadmap_groups is empty.
- Admin can change titles, order, icons, group, target URL and published status.
- Start Practice URLs can be connected to existing spoken-materials.php filters.
