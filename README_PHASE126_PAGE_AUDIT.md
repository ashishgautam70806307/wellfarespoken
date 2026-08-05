# Phase 126 - Page-by-page UI and contrast audit

## User-approved element retained
The compact homepage course-card design from v125 is retained without changing its structure, colour balance, spacing or metadata layout.

## Problems corrected
- White text inherited inside white cards placed in dark hero sections.
- Dark CTA panels without explicit heading and paragraph colours.
- Universal heading styling leaking into unrelated components.
- Inner pages using the homepage stylesheet without page-specific visual treatment.
- Oversized headings and unsafe wrapping on mobile.
- Inconsistent card borders, shadows, icon surfaces and spacing.
- Online class, test, dashboard and practice components not following the same contrast system.

## Page-specific work
- About
- Contact
- Admission
- Courses
- Course Detail
- Faculty Profile
- Student Login / Registration
- Student Dashboard
- Weekly Test
- Weekly Result
- Student Revision
- AI Teacher
- Free AI Practice
- Study Materials
- Online Classes
- Gallery
- Learning Roadmap
- Roadmap Lesson

## Technical changes
- Added `assets/css/page-polish-v126.css` after the approved v125 stylesheet.
- Rules are scoped through page-specific body classes such as `wf-page-about`, `wf-page-admission`, and `wf-page-weekly-test`.
- Added explicit light-surface and dark-surface colour contracts to prevent invisible text.
- Added responsive typography limits and page-specific mobile layouts.
- Added controlled wave and dotted-motion accents to page heroes and dark CTA panels.
- Updated PWA cache to v126 and included the new stylesheet.
