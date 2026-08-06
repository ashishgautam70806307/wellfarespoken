# Phase 138 - Universal Mobile UX Upgrade

Phase 138 is a compatibility-first mobile redesign built cumulatively on Phase 137. It does not replace desktop layouts or change business workflows. It adds one reusable mobile stylesheet and one reusable enhancement script that are loaded last across the public website, student pages, weekly exam room, admin dashboard, and admin login.

## What changed

- Introduced a shared mobile visual language for typography, spacing, section headings, cards, buttons, forms, tables, and responsive grids.
- Redesigned the public right-side drawer so navigation options stay visible while action buttons use a compact three-item layout.
- Added context-aware leading icons to ordinary form fields without changing their names, IDs, validation rules, or submission logic.
- Added automatic admin table labels so wide desktop tables become readable mobile record cards.
- Improved mobile header, announcement bar, bottom navigation, footer, public content pages, student screens, spoken-material practice, weekly tests, and roadmap screens.
- Added dedicated mobile treatment for the single-card Admin Login and the official Weekly Exam Room.
- Reduced expensive mobile effects such as backdrop filters and long reveal animations.
- Removed the obsolete Phase 133 mobile drawer block now superseded by the Phase 138 reusable system.
- Updated the service-worker cache namespace to `wellfare-spoken-static-v138`.

## New reusable assets

- `assets/css/phase138-mobile-ux.css`
- `assets/css/phase138-mobile-ux.min.css`
- `assets/js/phase138-mobile-ux.js`

The CSS is scoped through body classes such as `wf138-mobile-ui`, `wf138-admin-mobile`, `wf138-admin-login`, and `wf138-exam-mobile`. Desktop layouts remain controlled by the existing project styles.

## Installation

1. Back up the current project and database.
2. Replace the existing project with the cumulative Phase 138 package, or apply the replace-only package over Phase 137.
3. Keep the same database; Phase 138 contains no database migration.
4. Open the website once while online so the new service worker can activate.
5. Perform a hard refresh with `Ctrl + F5`. If an old design remains, clear the site cache/service worker and reload.
6. Complete `PHASE138_BROWSER_TEST_CHECKLIST.md` on the real localhost or staging server.

## Validation completed

- PHP syntax validation
- JavaScript syntax validation
- CSS parser validation
- Literal local link/asset validation
- Duplicate literal ID validation
- Service-worker pre-cache validation
- Phase 138 public/admin/login/exam inclusion checks

Interactive visual testing, authenticated workflows, microphone behavior, and database-backed actions still require the real browser/MySQL environment.
