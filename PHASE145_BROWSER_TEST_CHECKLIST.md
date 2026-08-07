# Phase 145 Browser, Database and Device Checklist

## Before testing

- Back up the database.
- Install the complete Phase 145 package.
- Unregister the previous service worker.
- Clear site data and cache.
- Hard reload with Ctrl + F5.

## Student Dashboard

- Login with an active student.
- Confirm the removed routine/profile/recent-practice/wrong-answer/note cards do not appear.
- Confirm hero Practice, Roadmap, Weekly Test and Logout actions work.
- Confirm desktop-header and mobile-drawer Logout work.
- Confirm the four compact metrics render without overflow at 320, 360, 390, 768 and desktop widths.
- Confirm result history shows correct date, time, status, score and percentage.
- Open both a completed result and an in-progress attempt from history.

## Weekly Test — guest

- Open Basic and Previous test cards without login.
- Confirm the selected setup opens and guest name/mobile fields are required.
- Confirm a non-10-digit phone is rejected.
- Start an open test and verify exactly one attempt is created and the browser redirects to the exam room.
- Open Upcoming/direct login-required URL and confirm the login gate appears instead of guest fields.
- Login through the gate and confirm return to the same selected setup.

## Weekly Test — logged-in student

- Open `weekly-test.php?type=basic&test_id=<valid-id>#wfTestSetup`.
- Confirm verified student information appears.
- Confirm open papers enable Start and closed/future/empty papers do not.
- Confirm one click creates one attempt and redirects to the exam room.
- Complete a test and confirm the new attempt appears with exact date/time.

## Weekly Test mobile carousel

- Test at 320, 360, 390 and 430 CSS pixels.
- Swipe between all three cards.
- Use both small arrow controls.
- Confirm dots follow the active card.
- Confirm no card text/button/status leaves its card.
- Confirm a deep Upcoming URL initially shows the Upcoming card.

## Weekly Result

- Open a valid student result and a valid guest token result.
- Confirm there is no empty gap above the page.
- Confirm date/time, percentage, marks, answered, correct, duration, and submission method are accurate.
- Confirm expected answers follow existing Basic/Checked visibility rules.
- Confirm long questions/answers/teacher notes wrap correctly.
- Test 320px through desktop widths.
- Confirm Dashboard, Test Center and Logout actions work.
- Confirm another student's result is denied.
- Confirm a guest result with a wrong token is denied.

## Learning Roadmap

- Confirm dark hero heading and subtitle are visible on desktop and mobile.
- Confirm progress summary and Continue Level action still use real student progress.

## Roadmap Lesson voice and completion

- Open a lesson with practice rows.
- Start practice with Voice Guide on and confirm the question is spoken once.
- Tap the speaker button and confirm manual replay.
- Select correct and wrong answers and confirm spoken feedback.
- Turn Voice Guide off and confirm automatic speech stops while manual replay still works.
- Move between Learn/Practice/Finish and confirm old speech stops.
- Finish all questions and confirm only the intended summary and Complete Level action are visible.
- Confirm hidden Start/Continue controls do not reappear.
- Complete the level once and confirm no duplicate progress write.

## Removed revision page

- Confirm no header/footer/dashboard/Practice Room link opens `student-revision.php`.
- Confirm the standalone file is absent.
- Confirm Revision mode inside `spoken-materials.php` still follows its existing login-aware logic.
