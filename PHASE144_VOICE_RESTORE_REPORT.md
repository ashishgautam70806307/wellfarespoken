# Phase 144 — Stable Voice Coach Restoration

## Root cause

Phase 143 correctly removed the unnecessary `practiceFilterForm` and the overlapping filter/request engines, but it also reduced the original hands-free voice experience to manual Listen and Speak buttons. That was the wrong scope reduction. Voice output and voice input were core learning features and should have been preserved.

## Corrected flow

`spoken-materials.php` now uses this explicit flow:

1. Student selects one of the four practice modes.
2. Exactly one cancellable list request loads a small practice set.
3. One current question is rendered.
4. With Voice Coach enabled, the question is spoken once.
5. The microphone opens for one bounded recognition session.
6. The captured answer is written into the answer field and checked.
7. The student uses Previous/Next; no hidden multi-question forms are created.

The removed `practiceFilterForm`, lesson/topic/search orchestration and repeated background loading were **not restored**.

## Voice features restored

- Automatic question voice output after a mode is selected.
- Automatic one-shot voice input after question playback.
- Manual **Listen**, **Speak answer** and **Stop** controls remain available.
- A visible Voice Coach switch lets the student use automatic or manual practice.
- “again”, “repeat”, “dobara bolo”, “phir se bolo” and related commands repeat the current question.
- A captured hands-free answer is checked automatically.
- Correct voice answers receive spoken positive feedback.
- Wrong answers display and can play the correct answer through **Listen Correct Answer**.
- The Voice Coach preference is stored locally.

## Stability protections

- Speech recognition is `continuous = false`; there is no endless microphone restart loop.
- Recognition is bounded to 30 seconds.
- Speech playback has start and completion watchdogs, so a browser voice failure cannot leave the page stuck forever.
- Mode changes, Previous/Next, Stop, page hide and answer checking cancel active speech, microphone sessions and timers.
- Stale list and answer requests remain abortable.
- Automatic loading still occurs only after a student chooses a mode.
- No database schema or API contract was changed.

## Files changed

- `spoken-materials.php`
- `assets/js/phase143-spoken-practice.js`
- `assets/css/phase143-practice-stability.css`
- `assets/css/phase143-practice-stability.min.css`
- `sw.js`

## Validation

- 68 PHP files passed syntax validation.
- 11 JavaScript/service-worker files passed syntax validation.
- 54 CSS files passed structural validation.
- 39 service-worker assets exist.
- No duplicate literal HTML IDs were found.
- A mocked end-to-end voice test passed: one list request → automatic question speech → “again” command → repeated speech → second voice answer → one answer request → correct result.

## Remaining live checks

Real microphone permission, installed device voices, authenticated Revision data and MySQL-backed answer storage must still be checked on localhost/staging in Chrome/Edge and on at least one Android phone.
