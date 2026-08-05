# Phase 130 — Final Frontend and Module Repair

This package is built on the Phase 129 project and corrects the public frontend through one shared design system instead of another page-by-page patch.

## Most important architecture correction

The CSS order is now:

1. lightweight/base shell
2. shared UI core
3. Phase 130 brand/design system
4. Phase 130 shared public-page patterns
5. dedicated page CSS

Previously, common CSS loaded after page CSS and could overwrite page-specific layouts. This was a major cause of broken forms and apparently missing page designs.

## Common reusable frontend

- `includes/ui-components.php` — navigation, page hero, section heading, FAQ split and reusable button helper
- `includes/header.php` — one desktop/mobile header and one CSS loading pipeline
- `includes/footer.php` — one footer, mobile navigation and dynamic social links
- `assets/css/phase130-design-system.css` — brand, navigation, buttons, forms, FAQ and footer
- `assets/css/phase130-public-pages.css` — shared Courses, About, Contact, Login, Dashboard, Reviews and Faculty layouts
- `assets/js/phase130-ui.js` — navigation, mobile drawer, FAQ, contact dock and common interactions

## Brand formula

- Deep Navy `#04162f`
- Navy `#071f43`
- Royal Blue `#2266ad`
- Logo Gold `#d8a62d`
- White and light-blue surfaces
- Green only for success/WhatsApp
- Red only for error/danger

## Requirements repaired

### Admission

- All existing fields remain.
- Desktop two-column and mobile one-column form.
- No label, select or textarea overlap.
- Online Class batch selection passes `batch_id` to Admission.
- Selected batch name, time and days are shown and selected automatically.
- Existing CSRF, phone validation and enquiry save logic remain.

### Spoken Materials

- Responsive learning-goal tabs.
- Course, level and topic filters.
- Desktop progress sidebar and mobile inline progress.
- Large practice workspace, answer states and touch-friendly actions.
- Existing material/practice API logic remains.

### Roadmap Lesson

- Full lesson shell with progress header.
- Learn, Practice and Finish tabs.
- Clear lesson blocks, examples, practice states and completion feedback.
- Responsive mobile layout and restrained animation.
- Existing roadmap progress API remains.

### Weekly Test

- Basic Test, Previous Test and Upcoming Test are restored as separate flows.
- Admin values remain `basic`, `previous` and `upcoming`.
- Query links support `weekly-test.php?type=basic|previous|upcoming`.
- More reliable JSON/error handling was added to test start.
- Login redirect returns directly to Upcoming Test.
- Existing CSRF, attempt tokens, server timer, snapshot, autosave and secure result flow remain.

### Gallery

Existing Phase 129 lightbox remains active:

- full image
- next/previous
- keyboard arrows
- mobile swipe
- zoom/reset
- caption

### FAQ

Real database FAQs use one reusable half-visual/half-accordion component. No dummy questions are inserted.

### Footer

- Improved classic footer without the old wave.
- Latest learning, test, institute and student links.
- Facebook, Instagram, YouTube and LinkedIn are displayed dynamically when saved in Admin Settings.
- Institute Login is included.

### AI cleanup

- `free-ai-english-practice.php` and its old APIs are not included.
- Old AI/free-practice navigation URLs are blocked.
- AI Teacher remains hidden while `APP_AI_TEACHER_ENABLED=false`.

### Institute Login

Public naming is `Institute Login`. Existing CSRF, honeypot, persistent rate limit, password verification and session regeneration remain.

## Reusable button formula

Use:

```php
<?= wf_button('Student Login', 'student-auth.php', 'primary', 'fa-solid fa-user-graduate') ?>
```

Available variants:

- `primary`
- `secondary`
- `success`
- `danger`

All public buttons use the same pill structure, hover, focus and right-side action circle.

## Deployment

1. Back up website files and database.
2. Extract the Phase 130 replace-only ZIP in the website root.
3. Overwrite matching files.
4. Run `PHASE130_CLEANUP.bat` on Windows, or manually delete files listed in `DELETE_OLD_FILES_PHASE130.txt`.
5. Keep in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_REMOTE_FONTS=true
APP_AI_TEACHER_ENABLED=false
```

6. Open DevTools → Application → Service Workers and update/unregister the old worker.
7. Clear cache and press `Ctrl + Shift + R`.
8. Complete `PHASE130_TEST_CHECKLIST.md` on localhost before live deployment.

## Database note

Phase 130 does not introduce a new table. It uses the database structure already present in the supplied Phase 129 project.
