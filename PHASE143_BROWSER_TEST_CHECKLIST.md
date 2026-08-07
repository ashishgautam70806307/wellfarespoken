# Phase 143 Browser and Database Test Checklist

Use a backup database or staging environment first. Clear the old service worker/site cache and press Ctrl + F5 before testing.

## A. Deployment/cache

- [ ] Extract the cumulative ZIP over a backup copy of the project.
- [ ] Confirm `sw.js` contains `wellfare-spoken-static-v143`.
- [ ] In DevTools -> Application, unregister the previous service worker.
- [ ] Clear site storage/cache and hard reload.
- [ ] Confirm the new Phase 143 CSS/JS files return HTTP 200.

## B. Spoken Materials - general stability

Open `spoken-materials.php` on desktop and mobile.

- [ ] Page settles without continuous loading activity.
- [ ] No `practiceFilterForm` appears in the DOM.
- [ ] No lesson/topic/search filter row appears.
- [ ] Four mode buttons are visible and usable.
- [ ] Page does not blink when idle.
- [ ] Browser CPU/network activity becomes idle after load.
- [ ] Console has no JavaScript error.
- [ ] Repeatedly switching modes does not show stale content.
- [ ] Rapidly tapping two modes leaves only the final selected mode active.
- [ ] Change Mode returns to the four mode choices without reloading the page.

## C. Spoken Materials - content/API

For each normal mode:

- [ ] Speak Daily loads published practice rows.
- [ ] Hindi to English displays the expected source/answer direction.
- [ ] English to Hindi displays the expected source/answer direction.
- [ ] Counter and progress bar update correctly.
- [ ] Previous and Next show one question at a time.
- [ ] Empty published data shows a readable empty/error state rather than hanging.
- [ ] Network response from `material-practice-list-api.php` is valid JSON.

## D. Spoken Materials - answer checking

- [ ] Typing does not trigger automatic answer checks.
- [ ] Check Answer sends one request.
- [ ] Correct answer shows success feedback.
- [ ] Incorrect answer shows teacher-approved correct answer/feedback.
- [ ] Double-clicking Check does not duplicate the action.
- [ ] Clear empties answer and result state.
- [ ] Moving to the next question resets answer/result state.

## E. Speech and microphone

Test in Chrome/Edge desktop and Android Chrome where supported.

- [ ] Listen speaks only after tapping Listen.
- [ ] Starting another Listen cancels the previous utterance cleanly.
- [ ] Speak answer asks for microphone permission only after a tap.
- [ ] Recognition stops after one answer or timeout.
- [ ] Recognition does not restart by itself.
- [ ] Stop immediately ends an active microphone session.
- [ ] Changing mode stops current speech/microphone activity.
- [ ] Leaving/minimising the page stops active speech/microphone activity.
- [ ] Unsupported/denied microphone displays a clear message and typing remains usable.

## F. Revision business logic

Logged out:

- [ ] Revision does not silently show ordinary material.
- [ ] A login-required message/action is displayed.

Logged in as a student with wrong attempts:

- [ ] Revision shows only the student's latest incorrect practice pairs.
- [ ] Corrected/latest state does not create duplicate visible questions.
- [ ] Each revision question keeps its stored practice direction.

Logged in with no wrong attempts:

- [ ] A clear no-mistakes/empty state is displayed.

## G. Roadmap Lesson practice

Open several permitted `roadmap-lesson.php?id=...` pages.

- [ ] Existing approved Learn/Practice/Finish design is unchanged.
- [ ] Start Practice creates one question and up to four unique choices.
- [ ] No infinite loading occurs with fewer than four source rows.
- [ ] Question audio starts only after tapping the sound icon.
- [ ] Selecting an answer only once locks the question.
- [ ] Correct feedback appears below the options.
- [ ] Incorrect `.duo-result-box.bad` appears below the options and never covers them.
- [ ] Continue shows the next question and resets feedback.
- [ ] Practice completion displays the expected completion state.
- [ ] Complete Level sends one request and preserves prerequisite/access rules.
- [ ] Guest local progress still works.
- [ ] Logged-in server progress still works.

## H. Device widths

Repeat the primary flows at:

- [ ] 320 x 568
- [ ] 360 x 800
- [ ] 390 x 844
- [ ] 412 x 915
- [ ] Tablet portrait
- [ ] Desktop

Check:

- [ ] No horizontal scrollbar.
- [ ] No button/card text is cut.
- [ ] Bottom navigation does not cover practice controls.
- [ ] Keyboard opening does not hide the answer actions permanently.
- [ ] Long Hindi/English text wraps inside cards.

## I. Regression

- [ ] Student Login/Register still work.
- [ ] Weekly Test and Weekly Exam still work.
- [ ] Learning Roadmap list still enforces locked levels.
- [ ] Admin material publishing still controls public practice availability.
- [ ] Footer/header/drawer remain unchanged outside the two corrected pages.
