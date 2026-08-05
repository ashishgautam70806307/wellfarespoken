# Phase 130 Localhost Test Checklist

## Global UI

- [ ] Home, Courses, About, Contact and Student Login use the same header, font, colors and button pattern.
- [ ] Desktop dropdown opens by click and hover.
- [ ] Cursor can move from menu button into dropdown without it closing.
- [ ] Escape and outside click close the dropdown.
- [ ] Mobile drawer opens, scrolls, expands one group and closes correctly.
- [ ] Footer has no wave and social icons only appear for configured URLs.
- [ ] Check 320, 360, 390, 430, 768, 1024 and 1440 px widths.

## Admission

- [ ] Open `admission.php` and verify no label/input/select/textarea overlap.
- [ ] Desktop form is two columns and mobile form is one column.
- [ ] No field from the old form is missing.
- [ ] Submit invalid phone and verify clear validation.
- [ ] Submit a valid enquiry and verify it appears in Admin Enquiries.
- [ ] Open Online Class, choose a batch, and verify Admission auto-selects that batch.
- [ ] Verify course is auto-selected when the batch has a course name.

## Spoken Materials

- [ ] Open `spoken-materials.php` on laptop and mobile.
- [ ] Goal tabs, filters and Load Practice work.
- [ ] Practice content does not overflow horizontally.
- [ ] Answer, check, speak/listen and next/previous actions work.
- [ ] Progress updates correctly.

## Roadmap

- [ ] Open `learning-roadmap.php` and a lesson.
- [ ] `roadmap-lesson.php?id=1` starts directly below the common header with no blank area.
- [ ] Learn, Practice and Finish tabs work.
- [ ] Logged-in completion is saved after refresh.
- [ ] Locked next lesson cannot be opened directly before completing the previous lesson.

## Weekly Test

- [ ] Admin has at least one published paper for each type: Basic, Previous and Upcoming.
- [ ] Basic card opens Basic papers.
- [ ] Previous card opens Previous papers.
- [ ] Upcoming card asks guest to log in.
- [ ] After login, redirect returns to `weekly-test.php?type=upcoming` and opens Upcoming setup.
- [ ] Guest Basic/Previous requires name and valid 10-digit phone.
- [ ] Start opens the secure exam room.
- [ ] Refresh/resume preserves question and option order.
- [ ] Autosave, timer expiry, submit and secure result URL work.
- [ ] Student sees result history after login.

## FAQ and Gallery

- [ ] Home and Admission FAQ sections show real database FAQs.
- [ ] FAQ accordion keeps one item open at a time.
- [ ] Gallery image opens in large view.
- [ ] Next, previous, keyboard arrow, swipe, zoom and close work.

## Feature cleanup and security

- [ ] `free-ai-english-practice.php` is absent/404.
- [ ] No public menu or footer contains Free AI Practice.
- [ ] `ai-teacher.php` redirects while `APP_AI_TEACHER_ENABLED=false`.
- [ ] Public link is named Institute Login.
- [ ] Repeated invalid Institute Login attempts are rate-limited.
