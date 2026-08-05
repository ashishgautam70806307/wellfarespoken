# Phase 129 - Frontend Module Repair and Student Practice UX

## Purpose

Phase 129 repairs the frontend modules that became visually or functionally inconsistent after the shared-design refactor. It keeps the existing PHP/database logic wherever it was already secure, then adds page-specific CSS and reusable UI components around that logic.

The approved project identity remains:

- Deep navy and royal blue for primary structure
- Logo gold for active/highlight states
- Green only for WhatsApp, success and completed states
- Red only for errors, delete and danger
- Plus Jakarta Sans with Noto Sans Devanagari fallback

## Main fixes

### Admission

- Compact two-column desktop layout and single-column mobile layout.
- All existing fields remain available.
- Online Class batch selection is passed using `mode=online&batch_id=<id>`.
- The chosen batch, timing and days are shown and preselected in the form.
- Real admission FAQs are displayed in a split visual/accordion layout.
- All form controls use the common Phase 129 input system.

### Spoken Materials

- Dedicated responsive CSS for filters, practice goals, progress, questions and results.
- Desktop: progress panel + practice workspace.
- Mobile: single-column action-first layout with touch-friendly controls.
- Existing material APIs and database logic remain unchanged.

### Roadmap Lesson

- Rebuilt lesson header, progress, Learn/Practice/Finish navigation and content cards.
- Responsive mobile layout and light reveal/micro animations.
- Developer-only source text is no longer displayed.
- Completion still uses the existing roadmap progress endpoint.

### Weekly Test

The frontend again exposes all three admin-managed test types:

1. Basic Test
2. Previous Test
3. Upcoming Test

The existing secure backend remains in use:

- CSRF check
- Attempt access token
- Student ownership checks
- Server-side schedule/timer checks
- Question snapshots
- Autosave and secure result handling

Upcoming Test remains an official logged-in flow. Basic and Previous follow the existing backend rules configured by the institute.

### Gallery

- Click-to-open lightbox.
- Previous/next controls.
- Keyboard arrows and Escape.
- Touch swipe.
- Zoom and reset.
- No external gallery library is required.

### FAQ system

A reusable `wf_faq_split()` component is used for real database FAQs. It shows a visual explanation/count on one side and accordion questions on the other. No dummy FAQs are inserted.

### Footer

- Improved classic footer layout.
- Current learning/test/student links.
- Dynamic Facebook, Instagram, YouTube and LinkedIn icons from settings.
- Institute Login link.
- Contact details remain dynamic.

### Institute Login

The admin login is presented as **Institute Login**. Existing security remains:

- CSRF protection
- Honeypot
- Persistent throttling/rate limiting
- Prepared database query
- `password_verify()`
- Session ID regeneration

### AI cleanup

- `free-ai-english-practice.php` and its old APIs/admin page are removed.
- AI Teacher is prepared with page CSS but hidden by default.
- Set `APP_AI_TEACHER_ENABLED=true` only after the institute reviews and enables it.
- Old visible navigation/seed wording now points to Practice Room.

## Common reusable files

```text
includes/ui-components.php
includes/header.php
includes/footer.php
assets/css/phase129-design-system.css
assets/js/phase129-ui.js
```

Page CSS is separate so one module does not break another:

```text
phase129-admission.css
phase129-materials.css
phase129-online-class.css
phase129-roadmap-lesson.css
phase129-weekly-test.css
phase129-gallery.css
phase129-ai-teacher.css
```

Production loads the corresponding `.min.css` files.

## Installation

1. Back up website files and MySQL database.
2. Extract the Phase 129 replace-only ZIP in the website root.
3. Overwrite matching files.
4. Delete files listed in `DELETE_OLD_FILES_PHASE129.txt`.
5. Keep `APP_AI_TEACHER_ENABLED=false` in `.env`.
6. Clear browser cache and unregister/update the old service worker.
7. Hard refresh with `Ctrl + Shift + R`.
8. Complete `PHASE129_TEST_CHECKLIST.md` on localhost/staging.

No new database migration is required for the frontend changes in this phase.

## Important verification boundary

Static checks, PHP syntax checks, JavaScript syntax checks, CSS structure checks, ZIP integrity checks and literal public-link checks were completed. The actual XAMPP MySQL data and a full interactive browser session were not available in the build environment. Therefore registration, live test attempts, admin data saving and database-backed material/roadmap flows must still be completed on localhost/staging using the checklist.
