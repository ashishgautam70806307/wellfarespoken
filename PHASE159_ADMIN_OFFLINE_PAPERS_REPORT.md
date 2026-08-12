# Phase 159 - Admin Offline Papers & Weekly Card Repair

Base: Phase 158

## Scope

This phase only repairs Weekly Test Admin discoverability and card/action responsiveness. It does not change test scoring, ranking, question imports, student attempts, result release, database schema, or other business workflows.

## Fixed

1. Admin Dashboard now has a dedicated **Batch-wise Question Papers & Answer Keys** section for Upcoming Tests.
2. Every Upcoming paper card exposes:
   - Student Paper / PDF
   - Answer Key
   - Manage Questions
3. Dashboard also includes direct **Manage Upcoming Tests** and **Upload Questions** actions.
4. When no Upcoming paper exists, Dashboard shows a clear create-test empty state instead of hiding the feature.
5. Weekly Test paper cards now expose both **Student Paper / PDF** and **Answer Key**, not only one offline action.
6. The selected Upcoming paper also has PDF and Answer Key actions in the paper-board header.
7. The previously broken `#answer-sheet` anchor now points to the actual Upload Answer Sheet card.
8. Weekly Test card buttons/forms are bounded with `min-width:0`, `max-width:100%`, wrapping labels and responsive 3/2/1-column action grids so controls cannot escape the test card.
9. Very small Admin screens (<=380px) use one-column actions where two columns would become cramped.
10. Service-worker cache namespace updated to v159 and the Phase 159 minified stylesheet is pre-cached.

## Security / Permissions

All Dashboard offline-paper controls are rendered only for admins with `tests.manage`. The existing `weekly-test-offline-paper.php` route continues to enforce `tests.manage` server-side, so hiding/showing buttons is not the security boundary.

## Validation

- 105 PHP files: syntax PASS
- 16 JavaScript/service-worker files: syntax PASS
- Phase 148/149/150/151/158/159 static suites: PASS
- Weekly page fragment links: setup, answer-sheet, question-bank, student-copies all resolve to real IDs
- Phase 159 CSS braces: balanced
- No database schema change

## Runtime testing still recommended

On localhost/staging, sign in as an admin with `tests.manage`, create/select an Upcoming Test with questions, then verify Dashboard -> Student Paper / PDF and Answer Key, Weekly Tests -> paper-card actions, and layouts at 320px/360px/390px/768px/desktop widths.
