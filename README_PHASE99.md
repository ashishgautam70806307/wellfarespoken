# Phase 99 - CBT Compact UI + Reusable Modal + Admin Test Management Guide

Exam room:
1. Replaced browser confirm with reusable custom exam modal.
2. Future confirm/alert needs can reuse examConfirm() style modal.
3. Moved final submit control to the right sidebar safe area.
4. Hidden answer-area submit button to avoid accidental tap near answer input.
5. Timer now shows minutes and seconds labels.
6. Reduced question font size, textarea height and extra spacing.
7. Question paper area is more compact and professional.
8. Report Issue also uses the reusable modal.

Admin weekly tests:
1. Added a compact management guide explaining:
   - Basic = 10 questions
   - Previous = 10 questions
   - Upcoming = 10 questions
   - multiple accepted answer format
   - answer copy/review tracking
2. New test default values changed to:
   - 10 questions
   - 10 minutes
   - 10 marks

Notes:
- Existing tests are not force-changed, to avoid breaking live data.
- Admin can still edit total questions/duration/marks manually.
