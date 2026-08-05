# Phase 131 Localhost Test Checklist

## Cache first

- [ ] DevTools → Application → Service Workers → unregister old worker once.
- [ ] Clear site data/cache.
- [ ] Hard refresh with `Ctrl + Shift + R`.
- [ ] Confirm `sw.js` contains cache name `wellfare-spoken-static-v131`.

## Home Page

- [ ] Banner heading and paragraph are clearly visible on every slide.
- [ ] Desktop and mobile banner images switch correctly.
- [ ] Auto slide, arrow, dots, pause/play and touch swipe work.
- [ ] Student Reviews first row moves left and second row moves right.
- [ ] Reviews pause on desktop hover.
- [ ] No review card touches another card.

## Courses

- [ ] Every card shows View Details and Join Course buttons.
- [ ] Buttons are fully visible and clickable.
- [ ] 320px/360px mobile does not overflow.

## Footer

- [ ] Logo is visible with its original colors.
- [ ] Description, phone, email and address are readable.
- [ ] Admin Settings social URLs show the matching icons/labels.
- [ ] Blank social URLs do not create empty buttons.
- [ ] Footer links open valid pages.

## Universal fields

Check Admission, Student Register, Student Login, Weekly Test and Contact forms:

- [ ] Inputs/selects/textareas use the same height/radius/focus style.
- [ ] Labels do not overlap controls.
- [ ] Mobile keyboard does not cause browser auto-zoom.
- [ ] Select arrow is visible.
- [ ] Error/success alerts remain readable.

## Roadmap Lesson

- [ ] Header uses balanced vertical space.
- [ ] Learn/Practice/Finish tabs are styled and clickable.
- [ ] Mobile tabs remain reachable while scrolling.
- [ ] Lesson content, Hindi text and answers do not overflow.
- [ ] Completion still saves correctly for a logged-in student.

## Weekly Test

- [ ] Basic, Previous and Upcoming cards appear.
- [ ] Test card CSS is visible after hard refresh.
- [ ] Selecting a card opens setup.
- [ ] Guest name/mobile validation works for allowed practice tests.
- [ ] Upcoming test requests student login.
- [ ] Start, autosave, timer, submit and secure result still work.

## Student Register

- [ ] Left guidance and right form feel balanced.
- [ ] Register form uses two columns on desktop, one on mobile.
- [ ] Password confirmation validation works.
- [ ] Current level selection remains selected after validation error.

## Admission

- [ ] All existing fields remain present.
- [ ] Desktop fields align in two columns.
- [ ] Mobile fields become one column.
- [ ] Online Class batch selection is prefilled when `batch_id` is present.
- [ ] Submission stores a new enquiry.

## About and Contact

- [ ] About cards have equal gaps and readable content.
- [ ] Director section does not stretch or overflow.
- [ ] Contact buttons match the Student Login button family.
- [ ] Call, WhatsApp and map links work.

## Responsive widths

- [ ] 320px
- [ ] 360px
- [ ] 390px
- [ ] 430px
- [ ] 768px
- [ ] 1024px
- [ ] 1366px
- [ ] 1920px
