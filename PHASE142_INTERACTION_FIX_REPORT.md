# Well Fare English Spoken — Phase 142 Interaction Stability Fix

## Scope

Phase 142 is a focused repair built cumulatively on Phase 141. It changes no database schema, course/test scoring rule, admission workflow, student authorization rule, or API contract.

The phase addresses four reported areas:

1. `spoken-materials.php` blinking and unreliable option interaction.
2. `.duo-result-box.bad` covering practice options in `roadmap-lesson.php`.
3. Decorative icons inside input fields.
4. Login, registration, admission, and admin-login form layout and control quality.

## 1. Spoken Materials stability repair

### Root causes addressed

- Practice data previously loaded automatically on page entry, whenever a mode changed, whenever a select changed, and after a search delay.
- The mobile enhancement script observed `practiceApp.hidden` and could re-enter or leave the focused session while the page script was also changing visibility.
- Multiple overlapping fetches, speech-start callbacks, and voice-list callbacks could update the same interface.
- Re-rendering could restart the first question while another request or voice callback was still active.

### Applied repair

- Practice now loads only after the student taps **Start Practice**.
- Changing mode, lesson, topic, or search returns the interface to a stable ready state instead of auto-fetching.
- Added `AbortController` and a request-version guard so stale requests cannot replace the latest result.
- Added clear ready, loading, empty, error, active, and completion states.
- Removed the duplicate `speechSynthesis.onvoiceschanged` auto-start path.
- Replaced hidden-state observation with explicit `wf:practice-start` and `wf:practice-config` events.
- Preserved all existing API endpoints, CSRF handling, speech recognition, answer checking, revision handling, and activity recording.
- Added a clean completion screen instead of repeatedly clamping to the final sentence.

## 2. Roadmap lesson result-box repair

The successful roadmap lesson design and workflow are preserved.

- `.duo-result-box` now occupies normal layout space and cannot float above the answer grid.
- The exercise receives a temporary `.has-result` state only after an answer is selected.
- On compact phones, answer options contract slightly while feedback uses a bounded internal scroll area.
- The Continue action remains below the feedback block and accessible.
- Resetting or advancing a question removes the result state cleanly.

## 3. Input icon removal

Decorative leading icons were removed from:

- Student login
- Student registration
- Admission form
- Admin login email/password controls
- Spoken Materials lesson/topic/search controls

The password visibility button remains because it is an interactive accessibility control, not a decorative leading icon.

Obsolete automatic icon-injection JavaScript and dead icon-positioning CSS were removed. Inputs now have normal left padding, so typed values and placeholders cannot collide with icons.

## 4. Form UI/UX improvements

### Student login and registration

- Balanced two-column desktop form grid.
- Single-column mobile form with the action card displayed before supporting content.
- Cleaner labels, compact tabs, smaller mobile controls, and consistent focus states.
- Improved card radius, spacing, alert layout, and submit hierarchy.
- Supporting benefits become compact tiles on phones rather than pushing the form below the fold.

### Admission

- Consistent field rhythm and alignment.
- Reliable one-column mobile layout.
- Smaller, readable controls without decorative field icons.

### Admin login

- Maintains the Phase 137 single-card design.
- Removes decorative leading input icons.
- Keeps a correctly spaced password visibility action.
- Uses a 50px desktop control and compact mobile treatment.

## 5. Cleanup and optimization

- Removed obsolete icon shell logic from Phase 138 JavaScript.
- Simplified Phase 139 mobile practice logic to explicit events only.
- Removed unused public/mobile script loading from Admin pages.
- Removed dead input-icon declarations from older design-system stylesheets.
- Added one last-loaded page-safe stylesheet: `assets/css/phase142-interaction-fixes.css`.
- Generated production CSS copies with a parser-based minification process that preserves descendant selectors.
- Updated the service-worker cache namespace to `wellfare-spoken-static-v142`.

## Static validation

- PHP syntax: PASS
- JavaScript syntax: PASS
- Targeted inline JavaScript syntax: PASS
- CSS parsing: PASS
- Duplicate literal IDs: PASS
- Literal local asset/link references: PASS
- Service-worker precache assets: PASS
- Phase-specific interaction assertions: PASS

## Pending real-environment verification

The following require the user's actual localhost/staging MySQL data and an interactive browser/device:

- Real material API responses and published sentence sets
- Chrome/Edge microphone permission and continuous speech recognition
- Logged-in revision material behavior
- Student login/registration database writes and sessions
- Admission submission and admin visibility
- Physical 320px/360px/390px device rendering

Use `PHASE142_BROWSER_TEST_CHECKLIST.md` before treating Phase 142 as live-production verified.
