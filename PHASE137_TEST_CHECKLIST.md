# Phase 137 — Localhost/Staging Test Checklist

Use the actual XAMPP database and real admin-created records. Mark PASS only after observing the result.

## Installation and database

- [ ] `sql/wellfare_english_complete.sql` imports without an error on a fresh database.
- [ ] `APP_RUNTIME_MODE=auto` selects local settings on localhost.
- [ ] `APP_ALLOW_SCHEMA_UPDATES=false` is active after import.
- [ ] `php tools\phase137-functional-check.php` completes.
- [ ] Rollback write probe completes with `PHASE137_WRITE_TESTS=true`.

## Requirement 1 — visibility

- [ ] Home hero text is readable on every published banner.
- [ ] Left, center, and right content positions remain readable.
- [ ] No black text appears on a navy/blue hero background.

## Requirement 2 — Student Reviews

- [ ] Two review rows appear.
- [ ] First row moves in one direction and second in the opposite direction.
- [ ] No large empty gap appears after one cycle.
- [ ] Hover pauses movement on desktop.
- [ ] Mobile movement remains readable and does not create horizontal page overflow.

## Requirement 3 — Courses

- [ ] Every published course card has View Details and Join Course actions.
- [ ] Both buttons are fully visible on desktop and mobile.
- [ ] Course detail links open the correct database record.

## Requirement 4 — Footer

- [ ] Logo is clearly visible.
- [ ] Institute description, phone, email, address, and useful links render.
- [ ] Configured social icons open the correct URLs.
- [ ] Blank social URL does not create an empty icon.

## Requirement 5 — universal fields

- [ ] Login, Register, Admission, Contact, and Weekly Test controls have smooth border/radius.
- [ ] Labels do not overlap fields.
- [ ] Select arrows are visible.
- [ ] Placeholder is smaller/lighter than entered text.
- [ ] Focus ring is visible.
- [ ] Mobile input focus does not zoom unexpectedly.
- [ ] Admin Hero Banner fields are not affected by frontend layout rules.

## Requirement 6 — Roadmap Lesson

- [ ] Learn, Practice, and Finish tabs are visible.
- [ ] Active tab is clear.
- [ ] Tabs do not consume excessive height.
- [ ] Lesson content does not overlap or create horizontal scrolling.
- [ ] Practice and Finish actions save real student progress.

## Requirement 7 — Weekly Test

- [ ] Basic, Previous, and Upcoming cards show the correct admin-created papers.
- [ ] Selecting a card opens the correct setup.
- [ ] AJAX start creates an attempt.
- [ ] Normal POST fallback starts an attempt when JavaScript is disabled.
- [ ] Exam Room loads the expected questions.
- [ ] Autosave, refresh/resume, server timer, expiry, manual submit, and result work.
- [ ] Repeated submit does not create or grade twice.
- [ ] Result URL cannot open another student's attempt.

## Requirement 8 — Student Register

- [ ] Desktop columns are balanced.
- [ ] Mobile fields stack in one column.
- [ ] Password controls and eye buttons align.
- [ ] Registration persists in the real database.
- [ ] Login/logout works for the created account.

## Requirement 9 — Admission

- [ ] Online Class batch selection reaches Admission with `batch_id`.
- [ ] Batch and related course auto-select.
- [ ] All original form fields remain available.
- [ ] Desktop and mobile layouts have no overlap.
- [ ] Submission persists once and shows a controlled response.

## Requirement 10 — About

- [ ] Highlight cards have equal visual spacing.
- [ ] Cards do not touch each other.
- [ ] Director information remains readable on mobile.

## Requirement 11 — Contact buttons

- [ ] Call, WhatsApp, Directions, and Admission actions use the Student Login button family.
- [ ] Each link performs the correct action.

## Shared regression

- [ ] Desktop dropdown opens by click and keyboard and closes by Escape/outside click.
- [ ] Mobile topbar and drawer remain usable at 320, 360, 390, and 430px.
- [ ] Banner Next and touch swipe work.
- [ ] Gallery lightbox Next/Previous/Zoom work.
- [ ] Materials practice loads and submits real data.
- [ ] No PHP warning/notice/fatal appears.
- [ ] No JavaScript console error appears.
