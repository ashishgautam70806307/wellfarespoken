# Phase 176 - Mobile Reference Match + Visual QA

Phase 176 corrects the Phase 175 mobile implementation for the three learning pages after direct comparison with the approved mobile mockup and the user's 320x455 DevTools screenshots.

## Scope

- `spoken-materials.php`
- `free-ai-english-practice.php` (the real Practice Materials page; `poken-materials.php` does not exist)
- `learning-roadmap.php`
- Mobile only. Desktop behavior is intentionally preserved.

## Main corrections

### Shared learning app shell
- Added a compact dark mobile app bar with real drawer/menu access.
- Reworked the 5-item bottom navigation into an attached app-style bar: Home / Materials / Practice / Tests / Roadmap.
- Removed the floating contact/headset dock from these three mobile pages so it cannot overlap learning actions.
- Removed the normal footer from these three mobile app views.
- Added immediate tap feedback and `touch-action: manipulation` to important actions.

### Spoken Materials
- Rebuilt the mobile browse state to match the approved reference structure: compact title area, search, five quick filters, and clean single-column mode cards.
- Quick filters are functional and work together with the existing search.
- Active practice has a compact mode/counter/change-mode strip, navy question card, voice coach, Listen/Speak, answer, Check/Clear, Previous/Next.
- On very short 320x455-style viewports, navigation/actions become normal-flow instead of sticky so controls cannot hide each other.
- Continuous voice logic from Phase 170 is preserved; the only Phase 176 change in that JS is immediate Change Mode scrolling on mobile.

### Practice Materials
- Added a real mobile search field for lessons.
- Preserved the one-tap lesson selector and compact workspace from Phase 175.
- Corrected the actual lesson-panel selector discovered during browser visual QA (`.practice-lesson-panel`).
- Compact title/search/shortcuts/stats/lesson/workspace layout follows the approved reference hierarchy.

### Learning Roadmap
- Compact dark title area and progress-first layout.
- My Progress / All Levels controls, summary metrics, vertical status timeline, and Continue action remain easy to reach.
- Bottom Roadmap state is visually clear.

## Visual verification

Unlike Phase 175, Phase 176 was browser-rendered during QA. Chromium rendering was performed at:

- 320 x 455 (the same short viewport class shown in the user's DevTools screenshots)
- 390 x 844 (normal modern phone viewport)

The checks covered browse and active Spoken Materials, Practice Materials, and Learning Roadmap. The browser pass caught a real Practice Materials selector mismatch before packaging, which was corrected and re-rendered.

These render checks use the real Phase 176 CSS and page class/DOM patterns in Chromium fixtures. They are not a substitute for an authenticated live MySQL/device microphone test on the user's server.

## Backend / database

No database schema change. No scoring, ranking, admission, Weekly Test, material data, roadmap data, or security behavior is changed.
