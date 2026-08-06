# Phase 137 Visual Consistency and Reusable UI Repair

## Scope

Phase 137 is a compatibility-first visual repair on top of Phase 136. It does not change database tables or core business workflows. The work is limited to the problems reported from the localhost screenshots and the same underlying patterns found across public pages.

## Root cause found

`assets/css/phase133-controlled-ui.css` is loaded after several page styles and applies one global heading color to every public `h1`-`h6`. On white cards this is correct, but it could override headings inside navy/dark cards and make them nearly invisible.

## Contrast-safe reusable fix

A reusable semantic surface utility was added:

- `.wf-surface-dark`
- `[data-wf-surface="dark"]`

Any heading inside a marked dark surface is forced to white, while paragraph and muted text use a readable light tone. New dark components should use this semantic class instead of adding one-off heading fixes.

Dark surfaces reviewed or marked in this phase:

- Shared public page hero helper
- Home learning-path card
- Home batch card
- Home final admission card
- About teaching promise
- Admission hero
- Weekly-test hero
- Course summary panel
- Student login/register information panel
- Roadmap lesson header
- Online-class CTA
- Student dashboard and revision heroes
- Spoken Practice question panel and teacher CTA
- Contact information panel
- New public footer

## Spoken Practice Room redesign

The section below `Practice Room` was rebuilt while preserving existing API endpoints and JavaScript hooks.

Improvements:

- Guided command-centre layout
- Four premium practice-mode cards
- Clear step labels for mode, lesson, topic and search
- Better loader and empty state
- Sticky progress card on desktop
- Clear sentence count and progress meter
- Premium dark question panel with guaranteed text contrast
- Better hands-free microphone control
- Clear primary and secondary actions
- Improved answer area and result states
- Horizontal mode scrolling and one-column actions on mobile
- ARIA selected state now updates when the mode changes

## Admin login redesign

The old two-panel page was replaced with one unique secure login card.

Improvements:

- Single focused card
- Logo, institute name and secure-access badge
- Clear email and password controls
- Password visibility button
- Caps Lock notice
- Remaining-attempt information
- Trusted-device and no-cache guidance
- Mobile responsive layout
- Preserved CSRF, honeypot, rate limiting, password verification and session regeneration

The login page no longer downloads the large legacy `style.css` or `phase123-ui-core.css`. It now loads only design tokens and its isolated login stylesheet.

## Footer redesign

The footer was rebuilt as a premium navy/gold information area with:

- Counselling CTA
- Dynamic institute logo, name, tagline and description
- Learning highlights
- Four organized navigation groups
- Contact card
- Call and admission buttons
- Dynamic WhatsApp button
- Dynamic social links with icons
- Better responsive layout and mobile spacing

Social icons are shown only when their URL exists in Admin > Settings. Supported settings:

- Facebook
- Instagram
- YouTube
- LinkedIn
- Twitter / X

## CSS optimization and cleanup

- Removed obsolete two-card admin-login rules from the global stylesheet.
- Preserved the generic password wrapper because student authentication still uses it.
- Replaced the previous Spoken Practice stylesheet instead of stacking another conflicting file.
- Kept Phase 137 fixes isolated in reusable stylesheets.
- Regenerated all affected production `.min.css` files.
- Changed global `style.min.css` from 433,984 bytes to 424,887 bytes, a reduction of 9,097 bytes.
- The new admin login local CSS payload is roughly 8.7 KB minified including design tokens, instead of loading the 424 KB global stylesheet.

## Files introduced

- `assets/css/phase137-visual-repair.css`
- `assets/css/phase137-visual-repair.min.css`
- `assets/css/phase137-admin-login.css`
- `assets/css/phase137-admin-login.min.css`

## Validation completed

- 68 PHP files passed `php -l`.
- 7 JavaScript files, including `sw.js`, passed Node syntax checks.
- 40 CSS files passed parser validation.
- 201 literal local links/assets were reviewed with project-root-aware resolution; no real missing local target was found.
- No duplicate literal HTML IDs were found.
- All 28 service-worker static entries exist.
- New CSS production/minified counterparts exist.
- Service-worker cache namespace updated to `v137`.

## Honest runtime limitation

The code was not interactively rendered in a real browser from this environment and no MySQL server was available. The fixes are based on the supplied screenshots, source-level dependency review and static validation. After installation, use the included Phase 137 browser checklist at desktop and mobile widths before live deployment.
