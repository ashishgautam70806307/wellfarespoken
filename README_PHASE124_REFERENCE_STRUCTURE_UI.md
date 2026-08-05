# Phase 124 — Reference Structure UI Upgrade

This phase follows the supplied BFS reference screenshots for layout behavior and interaction patterns while preserving the existing Well Fare Spoken English brand colors and PHP logic.

## Reference features added

- Compact information top bar
- Clean white sticky desktop navigation
- Rounded active navigation state
- Structured dropdown panels
- Large pill-shaped Student Login CTA
- Mobile off-canvas drawer with close button
- Fixed mobile login/admission actions
- Five-item mobile bottom navigation
- Expandable Call / WhatsApp / Top quick-action rail
- Reference-style section headings
- Icon-led compact information cards
- Responsive course and feature cards

## Brand colors

The code uses the existing navy, blue, red and white visual identity. Content, database queries, security logic and page URLs are unchanged.

## Changed files

- `includes/header.php`
- `includes/footer.php`
- `assets/css/phase124-reference-ui.css`
- `assets/css/phase124-reference-ui.min.css`
- `assets/js/phase124-reference-ui.js`

## Installation

1. Back up the website and database.
2. Extract the replace-only package in the project root.
3. Overwrite the changed files.
4. Hard refresh the browser.
5. Clear old service-worker/site cache once.
6. Test desktop at 1366, 1440 and 1920 widths.
7. Test mobile at 320, 360, 390 and 430 widths.

## Important design behavior

### Desktop

- Header remains sticky.
- Dropdowns open on hover and click.
- Student Login remains the strongest CTA.
- Content cards use the screenshot-inspired icon + title + short text pattern.

### Mobile

- Hamburger opens an off-canvas menu.
- Login and Admission actions remain at the bottom of the drawer.
- Bottom navigation displays Home, Courses, Roadmap, Test and More.
- More opens the same complete navigation drawer.
- Floating phone button expands into Call, WhatsApp and Top actions.

## Performance

- The new CSS is isolated in one phase file.
- A minified production copy is included.
- No image assets or heavy libraries were added.
- Existing Font Awesome loading is reused.

## Validation completed

- 68 PHP files passed syntax linting.
- Phase 124 JavaScript passed syntax validation.
- Both full and replace-only ZIP files pass archive integrity testing.
