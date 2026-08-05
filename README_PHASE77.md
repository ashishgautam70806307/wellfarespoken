# Phase 77 - Corrected Roadmap Logic + Same Lesson Practice

User corrections handled:
- Roadmap visual improved back into a journey/game-style map with active/current/locked/completed states.
- Practice no longer depends on redirect to spoken-materials.php.
- roadmap-lesson.php now includes tabs:
  1. Learn
  2. Mini Practice
  3. Summary
- Mini Practice uses only the current lesson rows, so if this lesson has data, practice happens on the same page.
- Complete Level shows flower/confetti celebration and unlocks next lesson using localStorage.
- After complete, the next lesson opens automatically.
- Admin roadmap page now has clearer tabs:
  1. Word Meaning
  2. Uses / Modal Pattern
  3. Tense Manager
  4. Practice Questions
  5. Manage Records
  6. Import Excel/CSV

Important:
- Admin can manage records manually and import CSV.
- CSV order: key, col1, col2, col3, col4, col5, type/tag, notes, sort_order.
- Mini practice uses lesson rows from roadmap_items; no external API needed.
