# Phase 94 - Advanced Weekly Test System

Frontend:
- Premium compact weekly test UI.
- Basic / Previous / Upcoming tabs show related tests only.
- Student name and 10 digit mobile validation for guest attempts.
- Upcoming tests require student login.
- One-question-at-a-time exam flow.
- Next, Previous, Cancel and Submit buttons.
- Timer, autosave, fullscreen request, copy/paste block and tab-switch warning log.
- Mobile number connects basic, previous and upcoming test records.

Admin:
- Weekly test page made compact and cleaner.
- Question bank search + pagination.
- Multiple question select delete.
- Submission search + pagination.
- Multiple submission select delete.
- Review panel shows warnings/activity log.
- Multiple accepted answers supported in Expected Answer using new lines, || or ;.
- Auto checking uses accepted answers, close-match review, and teacher final marks.

Cheating note:
Browsers cannot fully lock a student's phone without a native app/kiosk mode. This project now uses practical browser-level safeguards:
fullscreen request, visibility/tab-switch warning, copy/paste/context-menu block, timer, one-by-one questions, and admin-visible warning logs.
