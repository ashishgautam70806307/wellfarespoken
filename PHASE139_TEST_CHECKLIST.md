# Phase 139 Test Checklist

## Installation/cache

- [ ] Back up Phase 138 files and database.
- [ ] Extract the replace-only ZIP at the project root.
- [ ] Run `PHASE139_CLEANUP.bat`.
- [ ] Unregister the old service worker and clear site data.
- [ ] Confirm the active cache is `wellfare-spoken-static-v139`.

## Mobile Roadmap Lesson

- [ ] Open `roadmap-lesson.php?id=3` at 320px, 360px, 390px and 430px.
- [ ] Tap Practice; lesson hero/progress should no longer consume the working frame.
- [ ] Four choices should appear as two rows of two.
- [ ] Selecting a choice should keep feedback and Continue close to the question.
- [ ] Very long translated content may grow naturally; it must never be clipped merely to avoid scrolling.

## Weekly Test and Exam Room

- [ ] Select Basic, Previous and Upcoming Test on mobile.
- [ ] After selection, setup should replace the distant hero/card flow.
- [ ] Name/phone/select controls should be compact and remain inside the viewport.
- [ ] Start a real test on XAMPP and confirm question/options/actions are the primary mobile frame.
- [ ] Confirm autosave, resume, timer, submit and result still work.

## Spoken Materials

- [ ] Four mode cards should appear in a 2 x 2 mobile grid.
- [ ] Filters, progress and sentence card should not use excessive height.
- [ ] Listen/Speak/Check actions should remain in one row where space permits.
- [ ] Long Hindi/English text should wrap and remain readable.

## Universal UI

- [ ] Course `View Details` and `Join Course` labels are complete at all mobile widths.
- [ ] Check other shared CTA labels for clipping.
- [ ] Input icons remain small and fixed while entering text.
- [ ] Typed text does not overlap the icon or password control.
- [ ] Normal input/select height is approximately 46px on mobile.
- [ ] Mobile menu icon has no visible background, border or shadow.
- [ ] No horizontal page overflow.

## Footer social settings

- [ ] Save Facebook, Instagram, YouTube, LinkedIn and X URLs in Admin Settings.
- [ ] Only non-empty networks appear in the footer.
- [ ] Each icon opens the saved URL in a safe new tab.
- [ ] Clear one URL and confirm only that icon disappears.

## Regression

- [ ] Desktop header, cards, forms and footer remain unchanged functionally.
- [ ] Registration, login and admission forms submit normally.
- [ ] Roadmap progress and Materials APIs still work on the real database.
- [ ] Browser console contains no application JavaScript errors.
