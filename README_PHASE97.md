# Phase 97 - Separate Secure Weekly Exam Room

Major fix:
- Weekly test no longer runs inside the normal website page.
- Start Test now creates a secure attempt and redirects to weekly-exam-room.php.
- weekly-exam-room.php has no header, no footer, no navbar, no WhatsApp, no gallery, no website distractions.

Security:
- Attempt access token added to weekly_test_attempts.
- Exam room requires attempt_id + access_token.
- Fullscreen request on Enter Exam Room.
- Tab/app switch, fullscreen exit, shortcut attempts, copy/paste/cut/right click/select/drag are blocked and logged.
- Timer, autosave and one-question-at-a-time flow remain.
- Warning count and activity log saved for admin review.

Important:
- Normal PHP/browser websites cannot 100% lock a student's phone. Full lock needs native app, kiosk mode, browser extension, or secure browser. This is the strongest possible no-paid-API web version.
