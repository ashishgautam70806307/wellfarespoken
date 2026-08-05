# Phase 129 - Page and Module Audit

## Public page status

| Page | Purpose | Phase 129 status | Required localhost QA |
|---|---|---|---|
| `index.php` | Home | Existing approved home retained; FAQ split and old AI link cleanup | Banner autoplay, arrows, dots, touch swipe, mobile/desktop images |
| `courses.php` | Course listing | Common design system retained | Data, links, mobile cards |
| `course-detail.php` | Course details | Common design/button/input system retained | Course ID, CTA links, empty state |
| `online-class.php` | Online batches | Batch CTA now sends `batch_id` to admission | Correct batch/time auto-selection |
| `admission.php` | Enquiry/admission | Compact responsive redesign; all fields retained | Save enquiry, validation, selected batch, duplicate options |
| `student-auth.php` | Student login/register | Common input/button system applied | Register, login, errors, session redirect |
| `student-dashboard.php` | Student hub | Common shell preserved | Logged-in data and actions |
| `learning-roadmap.php` | Learning path | Existing connected path retained | Lock/current/completed states on real data |
| `roadmap-lesson.php` | Lesson detail | Fully redesigned responsive learning workspace | Learn/Practice/Finish, completion save, locked URL |
| `spoken-materials.php` | Practice/materials | Dedicated responsive student-practice UI | Filters, AJAX list, answer submit, mobile controls |
| `weekly-test.php` | Test centre | Basic/Previous/Upcoming restored | Test lists from admin, validation, start redirect |
| `weekly-exam-room.php` | Exam room | Existing secure standalone exam UI preserved | Timer, autosave, refresh/resume, submit |
| `weekly-result.php` | Test result | Existing secure ownership/token result flow preserved | Student/guest access and teacher release rules |
| `student-revision.php` | Revision | Existing logic/common shell retained | Attempt data and revision links |
| `gallery.php` | Gallery | Responsive lightbox, zoom, next/previous, swipe | Images, keyboard, touch, empty state |
| `reviews.php` | Reviews | Existing review page/common system retained | Dynamic reviews and responsive cards |
| `about.php` | Institute | Common system retained | Dynamic sections and mobile spacing |
| `contact.php` | Contact | Common buttons/inputs/footer retained | Phone, WhatsApp, map/direction links |
| `faculty-profile.php` | Faculty details | Common system retained | Valid/invalid profile ID |
| `ai-teacher.php` | Hidden future module | Redirects to Practice Room by default | Only test if explicitly enabled |
| `pwa-check.php` | PWA diagnostics | Utility preserved | Service worker/version/cache status |

## Weekly Test backend mapping

Admin and frontend use the same values:

| Admin type | Frontend card | Backend value |
|---|---|---|
| Basic Test | Basic Test | `basic` |
| Previous Test | Previous Test | `previous` |
| Upcoming Test | Upcoming Test | `upcoming` |

`weekly-test-api.php` accepts only these values and keeps secure attempt controls.

## Online class to admission flow

```text
Online Class batch card
        ↓ batch_id
admission.php?mode=online&batch_id=ID
        ↓
Batch record matched from database
        ↓
Course + timing preselected
        ↓
Enquiry stored with Online Class Admission source
```

## Admin management confirmed in source

- Weekly test types/questions/attempt checking: `admin/weekly-tests.php`
- Hero desktop/mobile banners: `admin/hero-banners.php`
- FAQs: existing FAQ admin/content records
- Online class features: content blocks using `online_class_feature`
- Social links: site settings (`facebook_url`, `instagram_url`, `youtube_url`, `linkedin_url`)
- Institute access: secure `admin/login.php`

## Flow assessment

The learner-facing order is logically sound:

```text
Home
  ↓
Course / Online Class
  ↓
Admission or Student Account
  ↓
Roadmap
  ↓
Lesson
  ↓
Practice Materials
  ↓
Weekly Test
  ↓
Result and Revision
```

The next major non-frontend management phase should connect:

```text
Enquiry → Counsellor Follow-up → Admission → Student → Course/Batch → Roadmap Assignment
```

That database workflow was not mixed into this frontend repair phase to avoid destabilising existing production data.
