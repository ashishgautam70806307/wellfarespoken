# Phase 167 — Mobile Responsive Repair

## Scope
This phase is intentionally limited to the mobile layout regression shown in the supplied Weekly Test screenshot and the same compatibility pattern elsewhere. No database schema, Weekly Test scoring, result, ranking, answer-release, scheduling, voice, authentication, or Admin business logic was changed.

## Root cause found
The Weekly Test history area already switched to `display:flex` on phones, but the card width used `flex: 0 0 min(86vw,310px)`. On the affected Android/mobile browser, that flex-basis value was not being honored, so the cards fell back to shrinkable flex items. All three attempt cards therefore compressed into the same viewport, causing narrow columns, wrapped status text, clipped action labels, and poor readability.

The same risky `min()`-inside-flex-basis pattern also existed in the shared Student Dashboard test history, the Home review slider, and an older materials slider stylesheet.

## Fixes
- Added a final reusable `phase167-mobile-layout-safety.css` layer after all public cumulative styles.
- Weekly Test history is now a stable one-column mobile list. Cards cannot shrink side-by-side.
- Student Dashboard Weekly Test Result History uses the same stable mobile layout.
- History cards use a compact responsive internal grid: status, title, date/time, score and action remain inside the card.
- <=380px phones stack the score/action area vertically so no CTA is clipped.
- Questions & Answers details on Student Dashboard span the full card width.
- Home review slider received a percentage/max-width fallback instead of `min()` in flex-basis.
- The older materials slider compatibility declaration was corrected too.
- A shared mobile safety rule keeps buttons/actions inside their own container and allows long labels to wrap instead of escaping.
- Existing Phase 145/146/126/130 source styles were also corrected so the project does not depend solely on the new override.
- Service Worker cache bumped to `v167` and the new minified stylesheet is pre-cached.

## Mobile compatibility audit
A source scan was run across all CSS assets for `flex: 0 0 min(...)`, the pattern responsible for this regression. No such source declarations remain after Phase 167.

Primary student mobile surfaces reviewed statically:
- Weekly Test Center
- Student Dashboard Weekly Test history
- Weekly result/review action labels
- Home review carousel
- Spoken Materials compatibility styles
- Shared mobile buttons/actions
- Bottom-navigation clearance remains unchanged from prior phases

## Regression safety
No PHP business behavior was changed except loading the final stylesheet. No SQL migration is needed.

Static validation:
- 114 PHP files: syntax PASS
- 17 JavaScript/Service Worker files: syntax PASS
- 84 CSS files: balanced PASS
- Phase 167 focused static checks: PASS
- Prior static suites 148/149/150/151/158/159/160/161/162/163/164: PASS
- Literal duplicate IDs: 0
- Remaining risky `flex: 0 0 min(...)` declarations: 0
- Service Worker local assets: 57, missing: 0

## Still requires real-device verification
The supplied screenshot proves the problem on a real phone, but the build environment does not have the user's authenticated MySQL/session data. After deployment, verify on the actual phone at 320/360/390/430 CSS-pixel widths and clear the old Service Worker/site cache once so v167 assets are loaded.
