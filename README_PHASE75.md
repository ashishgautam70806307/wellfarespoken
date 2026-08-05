# Phase 75 - Roadmap Lesson Detail + Content Manager

Added:
- New frontend page: roadmap-lesson.php
  - Opens a roadmap unit/step by ID.
  - Shows pronouns as premium cards.
  - Shows demonstrative/meaning rows as learning cards.
  - Shows other roadmap content in a clean dynamic table.
  - Includes Learn -> Practice flow.

- Updated learning-roadmap.php
  - Roadmap nodes now open roadmap-lesson.php?id=...
  - Lesson page then sends student to Practice Room.

- Updated admin/roadmap.php
  - Lesson Content Manager added.
  - Admin can add/edit/delete roadmap_items rows.
  - Content is dynamic for pronouns, demonstrative words, verbs, meanings, examples.
  - Preview Lesson button added.

Notes:
- Existing starter data remains.
- Admin can manage roadmap groups, steps, and now lesson rows.
- This keeps the roadmap dynamic instead of static.
