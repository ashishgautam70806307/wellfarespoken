# Phase 171 - Weekly Test Admin Polish + Upcoming Scope Fix

## Scope
Focused repair on top of Phase 170. No database schema, scoring, ranking, student exam, answer-release, or spoken-practice logic changes.

## Critical bug fixed
`admin/weekly-tests.php?type=upcoming` could fall back to an existing Basic paper when no Upcoming paper existed. The selected paper then overwrote the requested tab, making the first Upcoming paper effectively impossible to create from the Upcoming tab.

Phase 171 keeps the requested Basic / Previous / Upcoming tab authoritative. A missing paper now stays in that scope with `test_id=0`, so the admin can create the first paper normally. A mismatched stale `test_id` is ignored instead of switching the tab.

## Easier paper creation/upload flow
1. Click Basic, Previous, or Upcoming.
2. If no paper exists, the page shows **Create New Test Paper** in that exact scope.
3. Save the paper.
4. The browser redirects to the saved paper using its returned `test_id`.
5. Upload Questions is then enabled for that same paper.

When a type has no paper yet, Upload Questions shows a clear create-first state instead of an empty required dropdown.

## UI fixes
- Compact three-tab Basic / Previous / Upcoming selector; remains horizontally reachable on small screens.
- Removed the remaining `Easy Upcoming flow` helper strip.
- Added a page-scoped professional form system for Weekly Tests only.
- Text, number, date and select controls use one consistent 46px desktop height (44px small mobile).
- Consistent border, radius, padding, font, focus state, label spacing, help text and textarea sizing.
- File input has a dedicated upload appearance and consistent selector button.
- Test Setup and Upload cards use matching padding, border, radius and shadow rules.
- Schedule controls are more compact while preserving Manual and Automatic behavior.
- Sample download links use a clean two-column layout and collapse to one column on mobile.
- Mobile/tablet layout stacks safely without changing business behavior.

## Service worker
Static cache bumped to `wellfare-spoken-static-v171` and the Phase 171 stylesheet is included in precache.

## Validation performed
- All 130 PHP files passed `php -l`.
- All project JavaScript files passed `node --check`.
- Phase 171 CSS brace integrity passed.
- Service Worker references 57 static assets with zero missing files.
- Static Phase 171 markers passed.
- Scope-model tests passed for:
  - first Upcoming paper when none exists,
  - stale Basic `test_id` with `type=upcoming`,
  - direct selected Basic paper.

## Live verification still required
Use the live database and authenticated Admin session to verify create/save/upload actions because this build environment does not have the production MySQL/session state.
