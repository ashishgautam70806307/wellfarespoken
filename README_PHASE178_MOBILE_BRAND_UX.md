# Phase 178 - Mobile Brand UX Repair

Phase 178 corrects the Phase 177 mobile presentation for the three learning pages without replacing the project's shared structure or removing existing features.

## Scope

- `spoken-materials.php`
- `free-ai-english-practice.php` (this is the real Practice Materials page; there is no `poken-materials.php` file)
- `learning-roadmap.php`

## Non-negotiable preservation

- `includes/header.php` is unchanged.
- `includes/footer.php` is unchanged.
- `assets/js/phase170-spoken-practice.js` is unchanged.
- Existing database/business logic is unchanged.
- Existing learning controls remain available.
- No alternate app shell/header/footer/navigation is introduced.

## Project design language used

The mobile UI now uses the existing Well Fare design tokens instead of a separate palette:

- Navy 950: `#04162f`
- Navy 900: `#071f43`
- Blue 700: `#174e8f`
- Gold 500: `#d8a62d`
- Gold 300: `#f1ce74`
- Soft surface: `#f4f7fb`

Mobile body/supporting text is no longer pushed into unreadably tiny sizes. Phase 178 does not define sub-11px typography and important actions use approximately 46-48px touch targets.

## Spoken Materials

All original modes and controls remain:

- Speak Daily
- Hindi to English
- English to Hindi
- Revision
- Change Mode
- Continuous Voice Coach
- Listen
- Speak
- Stop
- Type answer
- Check Answer
- Clear
- Previous
- Next

The presentation is now project-branded: navy/gold mode cards, stronger question hierarchy, readable answer controls, clear primary/secondary actions, and no floating contact control over the learning controls.

## Practice Materials

All original practice functionality remains:

- Quick Translator / quick practice helper
- Correct Sentence
- Hindi to English
- English to Hindi
- Quick microphone
- All lesson categories and lesson buttons
- Lesson AJAX loading
- Text/option answers
- Speak Answer
- Previous / Check Answer / Next
- Result, natural answer, accepted answers, feedback and score

A 12-second client request watchdog was added to lesson loading, answer checking and quick practice. A slow request now returns control to the student instead of leaving the interface looking permanently stuck.

## Learning Roadmap

All original roadmap functionality remains:

- Progress ring
- Summary counts
- All roadmap stages
- Completed/current/locked states
- Level descriptions
- Level metadata
- Open/Review/Locked actions
- Continue action
- Reset Progress
- Admin Manage Roadmap link when authorized

Mobile presentation uses a readable vertical flow while keeping the original roadmap data and state logic.

## Responsive rules

- Mobile-only stylesheet: `assets/css/phase178-mobile-learning-premium.css`
- Main breakpoint: `max-width: 760px`
- Additional compact rules: 390px and 340px
- Short-height rule: <=600px prevents sticky/fixed learning controls from covering content.
- Common project header/footer remain the source of navigation and identity.

## Reliability and compatibility

- Service Worker cache: v178
- No database schema change
- No route change
- No API contract change
- No Phase 170 voice-controller change
- No Phase 174 security regression

## Visual QA limitation

Static/source validation was completed. Chromium headless in the current build environment did not successfully start even for a minimal data URL, so Phase 178 does not claim a browser screenshot from that environment. Final authenticated visual/touch verification should be performed on the live/local project in Android Chrome and iPhone Safari/PWA using the attached device checklist.
