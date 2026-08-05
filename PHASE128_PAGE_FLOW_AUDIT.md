# Phase 128 — Public Page and Student Flow Audit

## Recommended student journey

```text
Home
  → Courses or Online Class
  → Admission / Student Login
  → Learning Roadmap
  → Roadmap Lesson
  → Practice Room / AI Practice
  → Weekly Test
  → Result and Revision
  → Student Dashboard
```

## Page-wise status

| Page/module | Source-level status | What was checked |
|---|---|---|
| Home | Ready for localhost QA | Responsive desktop/mobile banner source, safe old-row fallback, autoplay controls, touch swipe, quick actions, two-direction reviews |
| Hero Banner Admin | Ready for localhost QA | Desktop and mobile image fields, preview, position/overlay defaults, old-schema fallback |
| Courses | Ready for content QA | Common header/hero/buttons, course grid, course detail links |
| Course Detail | Ready for content QA | Responsive layout, variants, actions and normalized headings |
| Online Class | Added | Dynamic feature blocks, batches, process flow, admission and WhatsApp actions |
| Admission | Ready for form QA | Shared navigation, forms, button styling and responsive layout |
| Student Login/Register | Layout fixed; live auth QA required | Removed legacy CSS conflict/large blank offset, shortened copy, consistent form/buttons |
| Learning Roadmap | Improved; live DB QA required | Visual path, lock flow, database progress for logged-in users, local progress for guests |
| Roadmap Lesson | Improved; live DB QA required | Previous-unit eligibility, server completion, lesson tabs and progress |
| Practice Room | Ready for AJAX QA | Common shell, responsive controls and existing API links preserved |
| AI Teacher | Ready for API QA | Common shell, chat layout and existing API preserved |
| Quick Practice | Ready for API QA | Common shell and existing practice endpoint preserved |
| Weekly Test | Ready for complete exam QA | Common shell; earlier token/timer/result protections preserved |
| Weekly Result | Ready for ownership QA | Common shell; secure result ownership/token flow preserved |
| Student Revision | Ready for login QA | Common shell and existing result-based revision logic preserved |
| Student Dashboard | Ready for login/data QA | Common shell, responsive grids and existing data widgets preserved |
| About | Ready | Common hero, shared cards and spacing |
| Gallery | Ready for media QA | Common hero/grid, responsive images |
| Student Reviews | Ready | Dedicated page plus equal-length two-direction auto-moving Home rows |
| Contact | Layout fixed | Professional contact cards, aligned actions, responsive single/two/three column behavior |
| Footer | Updated | No wave, latest links and contact/account shortcuts |
| Desktop navigation | Updated | Hover/click/keyboard dropdown handling |
| Mobile navigation | Updated | Drawer accordions plus five-action bottom navigation |

## Functional items that still require real localhost/database testing

1. Register a new student and log in/out.
2. Save roadmap completion, refresh and verify persistence.
3. Reset roadmap progress and verify database/local behavior.
4. Create two Home banners with separate mobile and desktop images.
5. Verify autoplay, pause, arrows, dots and finger swipe on a physical phone.
6. Submit an admission/contact form.
7. Open every course detail and variant action.
8. Complete Practice Room AJAX actions.
9. Start, autosave, submit and review a Weekly Test.
10. Verify a guest cannot open another result by changing an ID.
11. Verify footer, dropdown and mobile drawer links from every main page.

## Recommended next functional phase

The public learning path is now coherent. The next management phase should connect:

```text
Enquiry → Follow-up → Admission → Student Account → Course/Batch → Roadmap
```

That should include staff roles/permissions, audit history and notification events. It should be implemented as a separate data/workflow phase rather than mixed into frontend styling.
