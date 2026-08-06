# Phase 136 Localhost and Staging Test Checklist

## Setup

- [ ] Back up files and database.
- [ ] Enable `pdo_mysql` in PHP/XAMPP.
- [ ] Start Apache and MySQL.
- [ ] Import `sql/wellfare_english_complete.sql` into a fresh test database.
- [ ] Set `APP_ALLOW_SCHEMA_UPDATES=false`.
- [ ] Run `php tools\phase136-functional-check.php`.
- [ ] Run `php tools\phase136-logic-check.php`.
- [ ] Clear old Service Worker/site data once.

## Configuration

- [ ] `localhost` selects the local DB profile.
- [ ] `127.0.0.1` selects the local DB profile.
- [ ] Live hostname selects the live DB profile.
- [ ] `.env` profile overrides are respected.
- [ ] Proxy headers are ignored when `TRUST_PROXY_HEADERS=false`.
- [ ] CLI/cron live job uses `APP_ENV=production` or `APP_RUNTIME_MODE=live`.
- [ ] Connection failure does not reveal credentials.

## Student authentication

- [ ] Registration creates one student record.
- [ ] Duplicate phone is rejected clearly.
- [ ] Correct login works.
- [ ] Wrong password is rejected.
- [ ] Disabled/unpublished student cannot continue.
- [ ] Session ID changes after login.
- [ ] Logout destroys the authenticated session.
- [ ] CSRF failure is rejected.
- [ ] Repeated account/IP attempts trigger rate limiting.

## Admission and Online Class

- [ ] Online Class batch link contains `mode=online&batch_id=...`.
- [ ] Correct batch is selected on Admission.
- [ ] Related course is selected where mapped.
- [ ] Batch time/days are visible.
- [ ] Required-field validation works.
- [ ] Valid submission inserts one enquiry.
- [ ] Rapid duplicate requests are rate-limited.

## Weekly Test - Basic

- [ ] Basic card shows available Basic papers.
- [ ] Guest/student policy matches configured backend rules.
- [ ] Start creates one attempt with access/result tokens.
- [ ] Exam room shows snapshot questions/options.
- [ ] Autosave persists answers.
- [ ] Refresh resumes the same order and answers.
- [ ] Manual submit finalizes once.
- [ ] Secure result URL opens only the correct attempt.

## Weekly Test - Previous

- [ ] Previous card shows Previous papers only.
- [ ] Wrong/deleted test ID does not fall back to another paper.
- [ ] Full start/autosave/resume/submit/result flow works.

## Weekly Test - Upcoming

- [ ] Upcoming card shows Upcoming papers only.
- [ ] Login is required where configured.
- [ ] Selected test ID is preserved through login.
- [ ] Schedule and eligibility are enforced.
- [ ] Stored `expires_at` remains authoritative.
- [ ] Timer expiry submits saved answers.
- [ ] Double submit does not create a second finalization.
- [ ] Started attempt cannot open answer review early.
- [ ] Result answer visibility follows release policy.

## Roadmap

- [ ] Current/completed/locked states render correctly.
- [ ] Direct locked lesson URL is rejected.
- [ ] Lesson tabs work.
- [ ] Finish action saves progress.
- [ ] Refresh and new login retain database progress.
- [ ] Guest local progress still behaves correctly.

## Spoken Materials

- [ ] Goal tabs render.
- [ ] Course/level/topic options load from real data.
- [ ] Practice items load.
- [ ] Answer submission returns correct/incorrect feedback.
- [ ] Progress saves for logged-in student.
- [ ] API rejects invalid method/CSRF/oversized input.
- [ ] Speak/listen controls work on supported browser.

## Admin-to-frontend mapping

- [ ] Publish desktop/mobile banner; both responsive sources appear.
- [ ] Banner autoplay, arrows, dots, pause, and swipe work.
- [ ] Publish/unpublish course and verify frontend visibility.
- [ ] Publish/unpublish batch and verify Online Class/Admission.
- [ ] Publish/unpublish Basic/Previous/Upcoming test and questions.
- [ ] Publish/unpublish FAQ, review, gallery, and materials records.
- [ ] Footer social URLs show only non-empty icons.

## Navigation and responsive pages

Test at 320, 360, 390, 430, 768, 1024, 1366, and 1920 px:

- [ ] Mobile topbar remains readable.
- [ ] Mobile drawer opens/closes and does not overflow.
- [ ] Desktop dropdown can be selected without closing early.
- [ ] Home slider and reviews move correctly.
- [ ] Course buttons remain visible.
- [ ] Forms do not overlap.
- [ ] Roadmap, Materials, Weekly Test, Gallery, Footer have no horizontal overflow.
- [ ] Browser console has no application JavaScript error.
- [ ] PHP logs have no warning/notice/fatal.
