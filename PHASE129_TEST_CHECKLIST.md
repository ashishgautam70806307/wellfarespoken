# Phase 129 - Localhost / Staging Test Checklist

## Preparation

- [ ] Back up files and database.
- [ ] Replace Phase 129 files.
- [ ] Delete items in `DELETE_OLD_FILES_PHASE129.txt`.
- [ ] Keep `APP_AI_TEACHER_ENABLED=false`.
- [ ] Clear browser cache.
- [ ] Unregister/update the old service worker.
- [ ] Test with `APP_DEBUG=true` on localhost and confirm there are no warnings/notices.

## Global UI

Test at 320, 360, 390, 430, 768, 1024, 1366 and 1920 px.

- [ ] Header position is consistent on every public page.
- [ ] Desktop dropdown stays open while moving from button to panel.
- [ ] Dropdown opens by click and keyboard.
- [ ] Mobile drawer opens, scrolls and closes correctly.
- [ ] Current page is highlighted.
- [ ] Primary buttons use the same navy/gold pill style.
- [ ] Inputs, selects and textareas do not overlap labels.
- [ ] Cards have visible gaps and do not touch each other.
- [ ] Footer links, social icons and Institute Login work.

## Home and banner

- [ ] No `content_position` warning.
- [ ] Multiple banners autoplay smoothly.
- [ ] Previous/next arrows work.
- [ ] Dots and pause/play work.
- [ ] Horizontal touch swipe changes slides without blocking vertical page scroll.
- [ ] Desktop image is used on laptop/desktop.
- [ ] Mobile image is used below 768px.
- [ ] Text position/overlay settings work for old and new banner records.

## Admission

- [ ] Form is two-column on desktop and one-column on mobile.
- [ ] All original fields are present.
- [ ] Required validation works.
- [ ] Ten-digit phone validation works.
- [ ] Successful enquiry is stored once.
- [ ] Flash success/error messages display correctly.
- [ ] FAQ split uses actual database FAQs.

## Online Class → Admission

- [ ] Open an online batch card.
- [ ] URL includes `mode=online&batch_id=<id>`.
- [ ] Correct batch name, timing and days appear on admission page.
- [ ] Preferred Batch select is automatically selected.
- [ ] Course is preselected when the batch contains a course.
- [ ] Submitted enquiry stores source as Online Class Admission.

## Spoken Materials

- [ ] Page CSS loads with no unstyled layout.
- [ ] Goal tabs work.
- [ ] Level/topic/type filters load correct material.
- [ ] AJAX practice list loads.
- [ ] Student can answer and submit.
- [ ] Result/feedback area appears.
- [ ] Progress sidebar stays usable on desktop.
- [ ] Mobile layout is one column and buttons remain visible.
- [ ] Hindi/English text wraps without horizontal overflow.

## Roadmap and lesson

- [ ] Roadmap path shows current/completed/locked levels correctly.
- [ ] Locked lesson direct URL is rejected for logged-in student.
- [ ] Lesson page loads title, description and content without blank top space.
- [ ] Learn, Practice and Finish tabs work.
- [ ] Completion saves for logged-in student.
- [ ] Guest completion uses the existing guest behaviour.
- [ ] Refresh preserves expected progress.
- [ ] Mobile tabs/buttons remain touch-friendly.

## Weekly Test

- [ ] Basic Test card lists admin-created basic papers.
- [ ] Previous Test card lists admin-created previous papers.
- [ ] Upcoming Test card lists admin-created upcoming papers.
- [ ] Empty type shows a clear empty message instead of a broken form.
- [ ] Guest fields appear only where backend rules allow them.
- [ ] Upcoming Test requires active student login.
- [ ] Start action reaches `weekly-test-api.php` successfully.
- [ ] Exam room receives valid attempt ID/token.
- [ ] Autosave survives refresh.
- [ ] Server timer expires correctly.
- [ ] Double submit is blocked.
- [ ] Result ownership/token rules remain correct.

## Gallery

- [ ] Clicking an image opens the lightbox.
- [ ] Next/Previous controls work.
- [ ] Arrow keys work.
- [ ] Escape closes.
- [ ] Touch swipe works.
- [ ] Zoom and reset work.
- [ ] Body scroll returns after closing.

## Removed/hidden modules

- [ ] No public menu/footer link points to `free-ai-english-practice.php`.
- [ ] Old deleted URL returns 404 instead of showing a broken feature.
- [ ] `ai-teacher.php` redirects to Practice Room while disabled.
- [ ] AI Teacher is absent from public navigation.

## Institute Login

- [ ] Header/footer Institute Login opens admin login.
- [ ] Wrong credentials show a generic error.
- [ ] CSRF failure is rejected.
- [ ] Repeated failed logins are throttled.
- [ ] Successful login regenerates the session and opens admin dashboard.
