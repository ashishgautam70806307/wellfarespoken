# Phase 138 - Mobile UI, Contrast and Requested Page Repair

Base: Phase 137.

This phase is a controlled visual repair. It does not add new business features or change database logic.

## Completed scope

- Added a reusable Phase 138 frontend token/component layer loaded after legacy page CSS.
- Added explicit light/dark surface contracts so headings remain readable on dark, gradient and image-backed sections while white-card headings remain dark.
- Added mobile-first typography, spacing, card, button, field and responsive layout refinements.
- Reworked the right-side mobile drawer into a compact, scrollable layout with visible test/menu options and smaller action controls.
- Improved the mobile topbar and small-device behavior at 767px, 390px and 340px widths.
- Redesigned the Spoken Materials Practice Room below its heading while preserving existing API hooks and backend flow.
- Redesigned Admin Login as one focused secure card while preserving CSRF, honeypot, rate limiting, password verification and session regeneration.
- Improved footer presentation and retained dynamic Facebook, Instagram, YouTube and LinkedIn links; blank links remain hidden.
- Updated service-worker cache to v138.

## Reusable assets

- `assets/css/phase138-ui-system.css`
- `assets/css/phase138-materials.css`
- `assets/css/phase138-admin-login.css`
- `assets/js/phase138-ui.js`

Production pages load the corresponding minified CSS files.

## Installation

1. Back up current files and database.
2. Extract the replace-only ZIP into the Phase 137 project root and overwrite matching files.
3. Unregister the old service worker, clear site data and hard refresh.
4. Check the pages listed in `PHASE138_TEST_CHECKLIST.md` on a real mobile device.

## Important limitation

Browser fixture verification passed, but this UI phase did not execute persistent writes against the user's live/XAMPP MySQL database. No database schema or backend business-flow changes were introduced in this phase.
