# Phase 124 — Logo Theme + Application Polish

This phase continues from the reference-based v123 build and focuses on the user's actual Well Fare logo palette and a smoother professional application experience.

## Locked brand direction
- Deep logo navy: `#061A43`
- Dark navy: `#03112E`
- Logo gold: `#C68A18`
- Light metallic gold: `#F2CF7A`
- White and cool blue-gray backgrounds
- Red is reserved for actual danger/error states only

## Improvements
- Replaced the public red-accent theme with logo navy and metallic gold.
- Added Manrope body typography and Plus Jakarta Sans headings with safe fallbacks.
- Added fluid `clamp()` typography and mobile-specific heading guards.
- Added responsive rules for 1180px, 900px, 767px, and 390px.
- Redesigned cards with:
  - layered brand edge
  - soft wave corner
  - pointer glow on desktop
  - better icon color system
  - controlled hover motion
  - improved mobile padding and height
- Added smooth application interactions:
  - top scroll progress
  - CTA ripple feedback
  - staggered reveal flow
  - subtle hero parallax
  - sticky header compression
- Added controlled graphics:
  - light particles
  - ambient orbs
  - wave section separators
  - animated process connector
  - online-class status and score chips
- Improved the homepage learning journey to work as a connected process on desktop and a compact timeline on mobile.
- Improved the online-class visual to feel like a real learning application while remaining part of the institute theme.
- Applied the same responsive typography, card language, wave treatment, and brand styling across public inner pages.
- Updated PWA cache to v124.
- Updated the standalone weekly exam room favicon, font and logo colors.

## Main new assets
- `assets/css/frontend-v3.css`
- `assets/js/frontend-v3.js`

## Updated integration files
- `includes/header.php`
- `includes/footer.php`
- `index.php`
- `online-classes.php`
- `weekly-exam-room.php`
- `sw.js`

## Browser refresh
After replacing an older build, use Ctrl + F5. If the PWA was installed or cached, clear site data once so the v124 service worker and CSS are loaded.
