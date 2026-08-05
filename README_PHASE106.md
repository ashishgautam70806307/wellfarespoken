# Phase 106 - Weekly Exam Start Screen Fix

Fixed the start-screen problem shown in screenshot:
- Candidate name no longer breaks the summary layout.
- Mobile number no longer clips inside narrow card.
- Start summary now uses a clean responsive grid.
- Basic Test now shows Practice mode text and does not say strict penalty text.
- Basic Test strict mode is disabled in effective exam screen; warnings can still be logged without penalty.
- Entry card is scroll-safe on smaller screens.
- Test rule text is dynamic for Basic vs Previous/Upcoming.

Only weekly-exam-room.php was changed in this patch.
