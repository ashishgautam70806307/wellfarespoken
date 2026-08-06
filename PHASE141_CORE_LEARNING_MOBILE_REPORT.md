# Phase 141 — Core Learning Mobile Repair

## Scope

This phase is intentionally limited to the three mobile pages reported by the user:

1. `spoken-materials.php`
2. `learning-roadmap.php`
3. `weekly-test.php`

No database schema, test scoring, roadmap progression, material API, authentication, or admin workflow was changed.

## Problems addressed

- Text, status labels, icons, and actions escaping narrow cards at 320–380px widths.
- Three weekly-test choices becoming difficult to use because each card inherited large desktop button spacing.
- Roadmap stage headings, status badges, metadata, and locked actions competing for limited width.
- Spoken Materials setup requiring too much scrolling before practice could start.
- Fixed bottom navigation and the floating contact button covering learning controls.
- Oversized introductory content consuming the first mobile frame.
- Active Spoken Materials session using a height calculation that did not sufficiently account for the mobile header and bottom navigation.

## Implemented repairs

### Weekly Test

- Added a compact mobile-only hero title while preserving the full desktop title.
- Removed the announcement bar and floating support dock only on the three focused learning pages.
- Kept the four-step process bounded on larger phones and hides it on compact phones where it competes with the actual test actions.
- Rebuilt Basic and Previous as two readable primary cards, with Upcoming as a full-width official-test card below them. This avoids the cramped three-column layout seen at 320px.
- Added mobile-safe status labels (`Open`, `Soon`, `Closed`) without changing desktop status text.
- Removed decorative button arrows and shimmer layers from the tiny test-card actions.
- Prevented action icons, labels, headings, and status chips from overflowing their cards while retaining readable mobile type sizes.
- Added safe bottom spacing above the fixed mobile navigation.

### Learning Roadmap

- Added the compact mobile heading `Learn. Practice. Unlock.` while retaining the existing desktop heading.
- Reduced introductory height and retained the four roadmap steps in a readable grid.
- Rebuilt the progress card, summary counters, stage headers, level rows, status badges, metadata, and actions for narrow screens.
- Removed mobile path connector overflow and eliminated left/right card margins that could push content beyond the viewport.
- Converted long mobile statuses to compact `Current`, `Done`, and `Locked` labels.
- Limited titles safely to two lines and kept the primary level action visible.

### Spoken Materials

- Removed the large page hero and duplicate section heading on mobile only.
- Added the compact mobile heading `Choose practice mode` while retaining desktop wording.
- Rebuilt the four practice modes as a stable 2×2 grid.
- Reduced the Lesson Group and Topic selectors to two compact columns.
- Kept search available on normal phones and hides only the optional search field at 380px and below.
- Prevented select values, labels, and practice-mode names from leaving their controls.
- Kept Start Practice full-width and visible.
- Corrected the active practice-session height so it sits between the sticky header and fixed bottom navigation.
- Added overflow guards to the question panel, action buttons, answer field, and session header.

## Reusable implementation

A single page-scoped responsive layer was added:

- `assets/css/phase141-learning-pages-mobile.css`
- `assets/css/phase141-learning-pages-mobile.min.css`

It loads after Phase 139 and affects only the three named page body classes. Desktop layout and all other pages remain unchanged.

## Cache update

Service-worker cache namespace:

- `wellfare-spoken-static-v141`

The new minified stylesheet is included in the pre-cache list.

## Static validation

- PHP syntax: 68 files passed.
- JavaScript/service worker syntax: 9 files passed.
- CSS parsing: 48 files passed; 0 parse errors.
- Duplicate literal IDs: 0.
- Service-worker static assets: 34 checked; 0 missing.
- Full ZIP and replace-only ZIP integrity: passed.

## Pending real-environment checks

The project still requires testing on the user's localhost/staging environment with real MySQL data and authenticated sessions. The included Phase 141 browser checklist covers 320px, 360px, 390px, and 430px widths.
