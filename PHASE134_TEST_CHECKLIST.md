# Phase 134 Localhost / Staging Test Checklist

Do not deploy before completing the database-backed items below.

## 1. Configuration

- [ ] Open the project through `localhost`, `127.0.0.1`, and the normal local folder URL; confirm the existing local database is selected.
- [ ] Open the live/staging domain; confirm the live profile is selected.
- [ ] Test `APP_RUNTIME_MODE=local` and `APP_RUNTIME_MODE=live` overrides.
- [ ] Test `DB_CONNECTION_MODE=manual` with temporary non-production credentials.
- [ ] Keep `APP_ALLOW_SCHEMA_UPDATES=false` during normal use.
- [ ] Confirm database errors do not display usernames/passwords in the browser.

## 2. Canonical database

- [ ] Back up the database.
- [ ] Import `sql/wellfare_english_complete.sql` into a test database.
- [ ] Open Admin → System Check.
- [ ] Confirm responsive banner, Student, Roadmap, Materials, and Weekly Test tables/columns are ready.
- [ ] Confirm a normal public page request does not execute `ALTER TABLE` or seed default records.

## 3. Weekly Test — highest priority

### Basic
- [ ] Publish an active Basic paper with questions.
- [ ] Start as guest with valid name/mobile.
- [ ] Confirm a wrong/stale test ID never starts another paper.
- [ ] Confirm exam room opens with attempt ID + access token.
- [ ] Answer, refresh, and verify saved answers remain.
- [ ] Submit and open the token-protected result.

### Previous
- [ ] Publish an active Previous paper.
- [ ] Repeat the guest start/autosave/submit/result flow.
- [ ] Confirm a Basic paper cannot be started as Previous and vice versa.

### Upcoming
- [ ] Publish/schedule an Upcoming paper and assign questions.
- [ ] Open while logged out; login and confirm return to the selected Upcoming setup.
- [ ] Start as an active student.
- [ ] Refresh and resume the same attempt.
- [ ] Try starting again after submission; confirm duplicate official submission is blocked.
- [ ] Let a test expire after autosaving answers; confirm saved answers are graded/submitted and the secure result opens.
- [ ] Confirm an inactive/deleted student cannot continue the attempt.

### Fallback
- [ ] Disable JavaScript temporarily and submit the Test setup form.
- [ ] Confirm normal POST opens the secure exam room or returns a clear error.

## 4. Student and admission

- [ ] Register a new student.
- [ ] Attempt duplicate phone registration.
- [ ] Login, logout, inactive-account block, and session expiry.
- [ ] Submit Admission with all fields.
- [ ] Open Admission from Online Class with `batch_id`; confirm batch/course selection.
- [ ] Confirm repeated admission spam is rate-limited.

## 5. Roadmap and Materials

- [ ] Open Learning Roadmap as guest and student.
- [ ] Complete a Roadmap Lesson and confirm database persistence for logged-in student.
- [ ] Confirm a locked lesson cannot be opened directly.
- [ ] Load Spoken Materials filters/content.
- [ ] Submit a material practice answer and confirm progress/result update.

## 6. Banner, Gallery, Reviews

- [ ] Upload separate desktop and mobile Hero images.
- [ ] Save image-only and text-overlay banners.
- [ ] Confirm no `content_position` warning.
- [ ] Confirm autoplay, arrows, dots, pause, and touch swipe.
- [ ] Open Gallery lightbox; test next/previous/keyboard/swipe/zoom.
- [ ] Confirm Student Reviews load real published records and both moving rows behave correctly.

## 7. Admin security and regression

- [ ] Invalid CSRF cannot save/delete Content Blocks, Form Options, Hero Banners, or publish toggles.
- [ ] Valid POST actions work and redirect correctly.
- [ ] Institute Login, logout, throttling, and inactive admin behavior work.
- [ ] Check PHP/server error log after every failed operation.
- [ ] Confirm no PHP warning/notice and no browser-console JavaScript error on all public pages.

## 8. Deployment cache and HTTPS

- [ ] Unregister the previous service worker once.
- [ ] Clear site storage/cache and reload.
- [ ] Confirm `wellfare-spoken-static-v134` becomes active.
- [ ] Confirm private/admin/student PHP pages are never served from service-worker cache.
- [ ] Install/verify the live SSL certificate and force HTTPS at hosting/server level.
