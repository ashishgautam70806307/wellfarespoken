# Phase 102 - Strict Exam Mode + Admin Login + Universal Typography

Important truth:
A normal PHP/HTML/JS website cannot truly lock Windows keys such as Win+D, OS task switching, notification shade, or another device.
The browser security model does not allow a web page to control the operating system.
This phase implements the strongest web-only exam discipline:
- fullscreen request after Start Test
- Keyboard Lock API attempt where supported
- tab/app switch detection through visibility/focus events
- copy, paste, cut, context menu, shortcut blocking
- server-side warning logs
- mark penalty after repeated warnings for previous/upcoming tests
- optional auto-submit when warning limit is reached

For real government-exam-style lock:
Use a lockdown browser/kiosk layer such as Safe Exam Browser or a controlled computer lab/kiosk app.
The PHP website remains the exam engine; the lockdown browser controls the device.

Implemented:
1. Weekly Test strict settings in Admin:
   - Strict Browser Mode
   - Auto Submit On Warning Limit
   - Allow Question Jump
   - Warning Limit
   - Penalty After Warnings
   - Penalty Per Warning
2. Exam page:
   - Strict fullscreen starts after user clicks Start Test.
   - Keyboard Lock API is attempted when supported.
   - Win+D/app switch/tab switch cannot be blocked, but gets recorded as warning.
   - Repeated warnings can auto-submit non-basic tests.
   - Basic Test stays practice mode: warning only, no penalty.
3. Admin login redesigned:
   - dynamic logo/site name
   - premium two-column design
   - CSRF validation
   - honeypot field
   - no-cache headers
   - email validation
   - attempts-left message
   - session regeneration after login
   - password show/hide and caps-lock hint
4. Universal frontend typography:
   - headings capped at 2.25rem desktop
   - responsive mobile headings
   - most heavy 800/900 visual weight reduced through overrides
   - Google font request reduced to 400-700 weights for better speed
5. Speed:
   - lighter font weights
   - no extra heavy frontend library added
