# Phase 76 - Advanced Roadmap Rebuild

Main fixes:
- Roadmap UI rebuilt into compact learning-app style.
- First lesson combines Basic Pronouns + This/That/These/Those.
- Lesson complete flow added with localStorage:
  - first step unlocked
  - after Complete & Unlock Next, next lesson opens/unlocks
  - roadmap page shows complete/active/locked states
- roadmap-lesson.php redesigned:
  - compact header
  - Previous / Practice / Complete & Unlock Next
  - pronouns in compact cards
  - demonstrative words in cards
  - uses/tense rows in dynamic rule cards

Admin updates:
- admin/roadmap.php now shows 3 clear manager blueprints:
  1. Word Meaning Manager
  2. Uses Pattern Manager
  3. Tense Manager
- CSV/TXT import added for roadmap items exported from Excel.
- Manual add/edit/delete remains.
- Fields renamed so admin can understand what to add:
  - Rule/Word
  - Hindi Meaning/Structure
  - English Answer/Example
  - Hindi Example
  - Accepted Variation
  - Type Tag

Notes:
- CSV order: key, col1, col2, col3, col4, col5, type/tag, notes, sort_order.
- This is still no-API and dynamic.
