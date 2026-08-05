# Phase 128 — Frontend Flow, Navigation and Reusable UI System

## Purpose

Phase 128 stabilizes the public frontend after the Phase 127 redesign. It fixes the reported banner warning, removes the footer wave, makes dropdown navigation reliable, standardizes public buttons/cards/fonts, improves page spacing, adds Online Class, and connects logged-in roadmap progress to the database.

## Important deployment order

1. Back up files and database.
2. Extract the Phase 128 replace-only package over Phase 127.
3. Delete the obsolete files listed in `DELETE_OLD_FILES_PHASE128.txt`.
4. Clear browser cache and unregister/update the old service worker.
5. Hard refresh with `Ctrl + Shift + R`.
6. Complete the supplied page checklist on localhost/staging.

No new database migration is required specifically for the Phase 128 UI. Responsive banner columns introduced earlier must already exist; if they do not, run the Phase 126 banner upgrade once.

## Reported issues fixed

### 1. `content_position` warning

Both Home and Admin banner code now use safe null-coalescing defaults. Old banner rows without this column/value use `left` automatically.

### 2. One reusable navigation

Desktop navigation and the mobile drawer are generated from `includes/ui-components.php`. Do not hard-code a second public menu in an individual page.

### 3. Dropdown usability

`assets/js/phase128-ui.js` provides:

- click-to-open and click-outside close;
- hover support with a delayed close bridge;
- keyboard Arrow Down and Escape support;
- one open dropdown at a time;
- mobile accordion behavior;
- correct focus and ARIA state handling.

### 4. One public button formula

Public buttons use a pill shape, navy/blue primary surface, and gold circular action indicator. Secondary buttons use white/light-blue styling. Green remains reserved for success/WhatsApp and red for errors only.

### 5. Font

The public frontend now uses **Manrope** with Segoe UI, Roboto and Arial fallbacks. Remote font loading is controlled with:

```env
APP_REMOTE_FONTS=true
```

Set it to `false` when a fully local/system-font deployment is preferred.

### 6. Footer

The old wave element has been removed. The footer now contains current links for:

- Courses
- Online Class
- Learning Roadmap
- Practice Room
- Weekly Test
- AI Teacher
- Revision
- About
- Gallery
- Student Reviews
- Contact
- Admission and Student Account

### 7. Card and section spacing

All public pages use the shared grid gap and page spacing tokens. Contact and Student Login pages received specific fixes for the issues visible in the supplied screenshots.

### 8. Online Class module

New public page: `online-class.php`

It uses existing dynamic content blocks (`online_class_feature`) and batch records. It provides a clear four-step flow and admission/WhatsApp actions.

### 9. Roadmap progress

Logged-in students now save completed roadmap units to `student_roadmap_progress`. Guests continue using browser local storage.

New endpoint:

```text
roadmap-progress-api.php
```

It requires student authentication, CSRF protection for writes, rate limiting and prepared database statements.

## Common frontend files

```text
includes/ui-components.php
includes/header.php
includes/footer.php
assets/css/phase128-design-system.css
assets/css/phase128-design-system.min.css
assets/js/phase128-ui.js
```

Page-specific CSS should only contain unique page layout behavior. It should not redefine the header, footer, primary buttons or global colors.

## Brand color rule

```text
Deep navy  #061a38
Navy       #08244c
Royal blue #1f5c9f
Gold       #d8a62d
Light gold #efc75f
Success    #16865a
Danger     #c93434
```

- Navy/blue: primary UI and actions
- Gold: active/current/highlight
- Green: completed/success/WhatsApp
- Red: error/danger only

## Verified statically

- 71 PHP files passed `php -l`.
- Main public JavaScript files passed `node --check`.
- CSS source/minified brace structure passed.
- 79 literal public page links/assets were checked; no missing literal target was found.
- `wf127-footer-wave` has no runtime markup or Phase 128 CSS definition.

## Testing limitation

The build environment has PDO but not the MySQL PDO driver and does not contain the user's live XAMPP database. Therefore database-backed browser flows must be tested on the user's localhost/staging installation using `PHASE128_FRONTEND_TEST_CHECKLIST.md`.
