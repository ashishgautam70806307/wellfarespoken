# Phase 137 — Requirements 1–11 Completion and Controlled Verification

Phase 137 is based only on Phase 136. It does not add a new public page, module, section, or unrelated visual direction. It completes the previously requested 1–11 frontend repairs and verifies the affected flows with fixture-backed browser and API checks.

## Completed requirements

1. **Text and color visibility** — Home hero title/subtitle/button contrast is forced to remain readable over dark, bright, left-, right-, and center-positioned banners.
2. **Student Reviews** — Both rows run continuously in opposite directions. JavaScript measures the real duplicated row width to prevent a large blank gap.
3. **Course buttons** — Every course card keeps two visible actions at the card bottom. Buttons use the same navy/gold pill structure as Student Login.
4. **Footer** — Logo visibility, institute details, useful links, contact data, and dynamic social icons are verified. Blank social URLs remain hidden through the existing PHP logic.
5. **Universal input controls** — Inputs, selects, and textareas use one visual layer with smooth border, 14px radius, balanced height, small placeholder, focus ring, autofill, disabled, and mobile states. The layer does not force form grids or page columns.
6. **Roadmap Lesson tabs** — Learn, Practice, and Finish tabs use a compact 46px segmented workspace with controlled spacing and no horizontal overflow.
7. **Weekly Test** — Basic, Previous, and Upcoming cards render; setup opens; Basic attempt creation works through AJAX; normal POST fallback redirects securely to Exam Room.
8. **Student registration** — Desktop has a balanced two-panel layout; mobile stacks correctly; fields keep the universal control styling without overlap.
9. **Admission form** — Fields remain unchanged, batch/course preselection is preserved, desktop fields align in two columns, long controls stay full-width, and mobile uses one column.
10. **About page** — Existing content is preserved while card height, spacing, icon area, and director information alignment are corrected.
11. **Contact page** — Call, WhatsApp, Directions, and Admission actions use the same reusable Student Login CTA family.

## Additional regression repair

- Desktop dropdown no longer opens on focus and then immediately closes on click. Keyboard Enter/Space/Arrow Down behavior remains available.
- Review marquee distance and duration are calculated from rendered content.
- Service Worker cache namespace is updated to `v137`.
- Existing Phase 136 local/live DB selection, Weekly Test security, Roadmap protection, Materials API validation, CSRF, sessions, prepared statements, and upload protections are preserved.

## Verification performed in this build environment

- PHP syntax: 70 files passed.
- JavaScript syntax: 9 files passed.
- CSS parsing: 32 files passed.
- Canonical database schema: 40 tables statically validated.
- Page smoke rendering: 23 pages passed.
- Browser interaction checks: 54 passed, 0 failed.
- Weekly Test fixture API checks: 3 passed, 0 failed.
- Configuration branches: 8 passed.
- Regression logic checks: 27 passed.
- Missing declared frontend assets: 0.
- Horizontal overflow in tested desktop/mobile pages: 0.
- Browser JavaScript runtime errors in the fixture suite: 0.

## Real database limitation

The build container does not provide `pdo_mysql` or a running MySQL/MariaDB service. Therefore, the fixture verified rendering, request contracts, CSRF, selection, API responses, redirects, and interaction behavior, but it cannot honestly certify persistence against the user's XAMPP database.

Run these on localhost/staging after importing the canonical SQL:

```bat
php tools\phase137-functional-check.php
set PHASE137_WRITE_TESTS=true
php tools\phase137-functional-check.php
```

The write mode inserts test enquiry/student rows inside a transaction and rolls them back.

## Installation

1. Back up project files and database.
2. Extract the Replace-Only ZIP into the current Phase 136 project root and overwrite matching files.
3. Run `PHASE137_CLEANUP.bat` to remove obsolete Phase 136 report files only.
4. Keep `APP_ALLOW_SCHEMA_UPDATES=false`.
5. Unregister the old Service Worker once, clear Site Data, and hard refresh.
6. Run the Phase 137 functional checker on XAMPP/staging.
