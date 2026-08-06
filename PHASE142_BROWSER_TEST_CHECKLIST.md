# Phase 142 Browser Test Checklist

Test after replacing files, clearing the site Service Worker/cache, and pressing Ctrl+F5.

## A. Spoken Materials — desktop and mobile

Test widths: 320, 360, 390, 430, 768, 1366.

1. Open `spoken-materials.php`.
2. Confirm the page remains stable and does not blink between options and practice.
3. Confirm no practice request starts automatically.
4. Tap each of the four modes. The selected state must change without auto-loading or jumping.
5. Change Lesson Group, Topic, and Search. The Ready state must remain visible.
6. Tap **Start Practice** once. Confirm one loading state and one API request.
7. Tap Start rapidly twice. Confirm stale/previous requests do not overwrite the newest result.
8. Confirm the active practice workspace appears only after successful loading.
9. Test Read Question, Speak Now, Stop Auto, Manual Check, Finish & Check, and Next.
10. Finish the final sentence. Confirm the completion card appears once.
11. Tap **Practise Again** and confirm the set restarts without flicker.
12. On mobile, tap **Change** in the compact session bar and confirm the options return cleanly.
13. Deny microphone permission and confirm typing/manual checking still works.
14. Test with no published sentences and confirm the empty state is readable.
15. Test a temporary offline/API failure and confirm the error state is stable.

## B. Roadmap Lesson

1. Open `roadmap-lesson.php?id=3` and another valid lesson ID.
2. Open Practice and choose a wrong answer.
3. Confirm `.duo-result-box.bad` appears below the options, not over them.
4. Confirm all answer options remain reachable.
5. Confirm the feedback content scrolls inside its own bounded area only when required.
6. Confirm Continue remains visible below feedback.
7. Advance to the next question and confirm the previous result state is removed.
8. Choose a correct answer and repeat the same checks for the success state.

## C. Student Login and Registration

1. Open login mode and registration mode.
2. Confirm no decorative icons appear inside text, email, phone, or password inputs.
3. Type long values and verify text never overlaps or clips.
4. Check autofill in Chrome/Edge.
5. At mobile widths, confirm form card appears before supporting information.
6. Confirm each field is one clean column and no horizontal scrolling occurs.
7. Submit invalid values and check error messages remain aligned.
8. Complete a real registration, login, logout, and session-protected-page test.

## D. Admission Form

1. Confirm no decorative input icons appear.
2. Check Name, Phone, Course, Level, Batch, and Goal alignment.
3. Test 320px mobile layout and long option values.
4. Submit valid and invalid data and verify messages.
5. Confirm the record appears in Admin with the expected mappings.

## E. Admin Login

1. Confirm a single centered card is shown.
2. Confirm email and password do not have decorative leading icons.
3. Confirm the password eye button does not overlap typed text.
4. Test Caps Lock hint, failed attempts, successful login, and logout.
5. Test 320px/360px mobile landscape and portrait.

## F. Regression

- Header and mobile drawer
- Footer and dynamic social links
- Weekly Test listing and exam room
- Learning Roadmap and lesson completion
- Student Dashboard and revision
- Admin tables and navigation
- Service Worker update and offline/static assets
