# Phase 131 — Visual System, Forms, Reviews and Page Repair

This update is built cumulatively on Phase 130. It keeps the existing PHP/database/security flow and repairs the reported frontend problems without introducing another navigation system.

## Main fixes

1. **Text contrast**
   - Home slider heading/subtitle are forced to readable white text.
   - A stronger responsive overlay protects text against bright/dark banner images.
   - Mobile uses a bottom-heavy overlay so the person/image remains visible while text stays readable.

2. **Student Reviews**
   - Added the missing marquee CSS.
   - First row moves right-to-left; second row moves left-to-right.
   - Equal duplicated sets prevent the empty jump in the loop.
   - Hover/focus pauses movement; reduced-motion preference is respected.

3. **Course buttons**
   - Course actions now use the shared `wf_button()` component.
   - Buttons remain inside every card and stay visible on desktop/mobile.
   - Mobile uses two actions when space permits and one column below 390px.

4. **Footer redesign**
   - The logo is displayed on a white logo plate; no white/invert filter is applied.
   - Website description, learning badges, useful links, test links, student links, institute login, contact details and office guidance are included.
   - Facebook, Instagram, YouTube and LinkedIn are read dynamically from Admin Settings; empty URLs are not shown.

5. **Universal fields**
   - One common height, border, radius, hover, focus, disabled, autofill and select-arrow system.
   - Mobile inputs use 16px text to avoid unwanted browser zoom.
   - Icons, labels and form grids no longer overlap.

6. **Roadmap Lesson**
   - Reduced unused vertical space.
   - Compact segmented Learn/Practice/Finish tabs with icons.
   - Better desktop/mobile padding and sticky mobile tabs.

7. **Weekly Test**
   - Strong page-specific styling and contrast.
   - Clear test cards, setup form and result panel.
   - The service worker no longer returns stale CSS/JS after deployment.

8. **Student Login/Register**
   - Balanced two-column presentation.
   - Register fields use a proper two-column form on desktop and one column on mobile.
   - Short visual benefits replace unnecessary paragraphs.
   - Login and Register share the same component styling.

9. **Admission**
   - More compact form and information layout.
   - Fields are preserved; only presentation changed.
   - Buttons use the same shared Student Login format.

10. **About and Contact**
   - About cards have consistent spacing, decorative accents and a clearer director/promise layout.
   - Contact actions use the shared button component.

11. **Cache correction**
   - Service worker cache upgraded to `wellfare-spoken-static-v131`.
   - CSS and JavaScript now use network-first exact-request caching, so `?v=filemtime` works correctly.

## Shared code used

- `includes/ui-components.php` — shared navigation and button helper.
- `includes/header.php` — one common public header.
- `includes/footer.php` — one common dynamic footer.
- `assets/css/phase130-design-system.css` — shared colors, buttons, fields, header and footer.
- Page CSS remains loaded after common CSS for module-specific layouts.

## Installation

1. Back up website files and database.
2. Extract the Phase 131 replace-only ZIP in the website root and overwrite matching files.
3. Open Chrome DevTools → Application → Service Workers.
4. Unregister the old worker once, then reload.
5. Clear website cache and use `Ctrl + Shift + R`.
6. Test the pages listed in `PHASE131_TEST_CHECKLIST.md`.

No database migration is required for this phase.

## Important environment note

The build environment did not have the project's live XAMPP/MySQL database. PHP syntax, JavaScript syntax, CSS structure, asset references and ZIP integrity were tested. Database-backed banners, reviews, courses and test records must be checked using the supplied localhost checklist.
