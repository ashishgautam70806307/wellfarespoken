# Phase 177 - Original Feature Restore + Mobile-Only UI Polish

Phase 177 corrects the Phase 175/176 direction. The user requested mobile design improvement only, not feature replacement.

## Locked rule
- Reuse the real common `includes/header.php` and `includes/footer.php`.
- Do not create a separate learning-app header or alternate footer/navigation structure.
- Preserve original page markup, actions, AJAX flows, voice logic, roadmap progress logic and all existing user options.
- Mobile improvements are presentation-only unless the user explicitly asks for behavior changes.

## Restored source
The following source was restored to the pre-mobile-redesign Phase 174 feature implementation, with only the Phase 177 stylesheet include added to the three pages:
- `spoken-materials.php`
- `free-ai-english-practice.php` (the real Practice Materials page; `poken-materials.php` does not exist)
- `learning-roadmap.php`
- `includes/header.php` - byte-identical to Phase 174 common header
- `includes/footer.php` - byte-identical to Phase 174 common footer
- `assets/js/phase170-spoken-practice.js` - byte-identical to Phase 174/Phase 170 continuous voice controller

Phase 175/176 alternate app-header/nav markup and JS are no longer referenced.

## Mobile-only design improvements
New file: `assets/css/phase177-mobile-learning-ui.css`

### Shared/common shell
- Keeps the existing common header/menu/drawer.
- Makes touch controls easier on mobile without replacing the header.
- Keeps the existing common bottom navigation.
- Keeps the contact dock feature, but moves it into normal document flow on these three mobile pages so it cannot cover practice fields/buttons.
- For short-height screens such as 320x455, bottom navigation moves to document flow instead of covering page controls.

### Spoken Materials
All original modes and Phase 170 voice behavior remain:
- Speak Daily
- Hindi to English
- English to Hindi
- Revision
- History
- Change Mode
- Continuous Voice Coach
- Listen / Speak / Stop
- typed answer
- Check / Clear
- Previous / Next

Only mobile sizing, spacing, card styling, readable fonts and touch targets were improved.

### Practice Materials (`free-ai-english-practice.php`)
All original features remain:
- Quick Translator + Sentence Corrector
- Correct Sentence
- Hindi to English
- English to Hindi
- microphone input
- Practice Now / Clear
- result panel
- all category/lesson buttons
- AJAX lesson loading
- one-question-at-a-time workspace
- Previous / Check Answer / Next Question
- Teacher Help / Study Material links

On mobile the existing lesson groups become horizontal, thumb-friendly rows instead of being replaced with a new selector.

### Learning Roadmap
All original roadmap features remain:
- common hero/progress
- total/completed/stars/lesson summary
- stage hierarchy
- current/completed/locked levels
- real level actions
- progress settings
- Reset Progress
- Admin Manage Roadmap link when authorized

Mobile uses a straight vertical visual path while retaining the original roadmap DOM and behavior.

## Visual QA
Rendered with Chromium/Playwright fixtures using the actual project CSS load order at:
- 390x844 Spoken browse
- 390x844 Spoken active practice
- 320x455 Spoken active practice
- 390x844 Practice Materials
- 390x844 Learning Roadmap

The 320x455 check confirmed that the bottom navigation does not cover the practice controls in short-height mode.

## Validation
- 134 PHP files linted successfully.
- 35 JavaScript/service-worker syntax checks passed.
- 145 CSS files passed brace-integrity check.
- 60/60 Service Worker precache assets exist.
- Phase 170 continuous voice static suite: 13/13 PASS.
- Phase 174 security suite: 19/19 PASS.
- Phase 177 feature-restore suite: 23/23 PASS.
- No database schema change.

Physical authenticated Android/iPhone + live MySQL/microphone testing is still environment-dependent and remains the final deployment QA step.
