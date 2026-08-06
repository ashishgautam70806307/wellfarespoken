# Phase 136 Test and Verification Report

## Test environment and honesty boundary

- PHP 8.4 and Node.js were available.
- MySQL/MariaDB server, phpMyAdmin, live hosting and an interactive visual browser were not available.
- Therefore database import, login sessions, CRUD writes, file uploads, JavaScript interaction, responsive rendering and true end-to-end flows are marked **LIVE PENDING**, not passed.

## Automated/static checks completed

| Check | Result |
|---|---|
| PHP syntax | PASS - 68/68 PHP files |
| JavaScript syntax | PASS - 7/7 JS files |
| CSS parse | PASS - 36/36 CSS files |
| Literal internal link/asset scan | PASS - 180 references, 0 missing targets |
| Service-worker pre-cache files | PASS - 0 missing targets |
| Static HTTP smoke test | PASS - manifest, service worker and reusable component CSS returned HTTP 200 |
| Canonical SQL table definitions | STATIC PASS - 40 unique table definitions |
| Canonical SQL insert targets | STATIC PASS - all insert targets have a table definition |
| Duplicate default batch seed rows | PASS - removed |
| Local runtime profile selection | PASS - localhost selects local profile |
| Live runtime profile selection | PASS - public host selects live profile |
| Pure helper smoke checks | PASS - safe redirect, exact answer matching, result URL generation |

## Repaired flow checks to execute on staging

### Weekly test complete flow

1. Create one Basic, Previous and Upcoming test with published questions.
2. Login as a published student and start the Upcoming test in two browser tabs at nearly the same time. Confirm only one active attempt exists.
3. Answer several questions, wait for autosave, refresh, and confirm saved answers return.
4. Submit manually and confirm all snapshot questions have answer rows and the result opens only for the owner/token.
5. Start another test, save answers, let the timer expire, and confirm the same saved answers are graded/submitted.
6. Trigger the warning limit and confirm automatic submit uses the same result path.
7. Confirm repeated submit calls do not create duplicate grading or change a closed result.

### Roadmap flow

1. Login with a new student who has no progress.
2. Confirm the first unit opens.
3. Confirm a future unit URL and direct progress API request are rejected.
4. Complete the first unit and confirm only the next permitted unit unlocks.
5. Configure `unlock_after_unit_id` in admin and confirm the custom prerequisite is enforced.

### Admission flow

1. Submit a normal enquiry using published options.
2. Open Online Class, choose a batch, and confirm course/batch values are preselected from server data.
3. Tamper with course, batch, level and source values in browser dev tools; invalid values must be rejected/ignored.
4. Submit twice within five minutes with the same phone; only one enquiry row should exist.

### Course and variants

1. Add a course with several variants.
2. Edit all course and variant values.
3. Simulate/fix a database error during variant insertion and confirm old data is not partially deleted.
4. Delete a course and confirm course plus variants are removed together.

### Material practice

1. Load lists using each supported goal/direction.
2. Submit valid, empty, oversized and invalid pair requests.
3. Confirm no private API response is cached.
4. Exceed the request limit and confirm HTTP 429.

## Page/module verification matrix

| Page/module | Current status | Required remaining check |
|---|---|---|
| `index.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `about.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `courses.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `course-detail.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `admission.php` | LOGIC REPAIRED / LIVE PENDING | Phase 136 server-side repair applied; real database and browser regression remains required. |
| `online-class.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `student-auth.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `student-dashboard.php` | LOGIC REPAIRED / LIVE PENDING | Phase 136 server-side repair applied; real database and browser regression remains required. |
| `spoken-materials.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `student-revision.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `learning-roadmap.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `roadmap-lesson.php` | LOGIC REPAIRED / LIVE PENDING | Phase 136 server-side repair applied; real database and browser regression remains required. |
| `weekly-test.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `weekly-exam-room.php` | LOGIC REPAIRED / LIVE PENDING | Phase 136 server-side repair applied; real database and browser regression remains required. |
| `weekly-result.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `gallery.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `reviews.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `contact.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `faculty-profile.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `ai-teacher.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `pwa-check.php` | STATIC PASS / LIVE PENDING | PHP syntax and literal local file references passed; database/browser workflow requires real environment. |
| `admin/dashboard.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/enquiries.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/enquiry-view.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/admissions.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/admission-view.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/students.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/student-view.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/courses.php` | LOGIC REPAIRED / LIVE PENDING | Course and variant writes are now transactional; verify create/edit/delete on staging. |
| `admin/batches.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/faculty.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/testimonials.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/gallery.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/videos.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/materials.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/roadmap.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/weekly-tests.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/weekly-test-paper.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/weekly-student-record.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/faqs.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/content.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/hero-banners.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/form-options.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/nav-menus.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/seo.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/settings.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/ui-library.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/password.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |
| `admin/system-check.php` | STATIC PASS / LIVE PENDING | PHP syntax and local file references passed; authenticated CRUD/browser/database test remains required. |

## Responsive/UI checks still required

- Widths: 360, 390, 768, 1024, 1366 and 1920 px.
- Header, top bar, mobile navigation and dropdown keyboard behavior.
- Desktop/mobile banner selection, slider controls and swipe.
- Review slider, gallery lightbox and focus return.
- Forms, validation messages, disabled/read-only fields and error recovery.
- Cards, tables, empty states and long Hindi/English content wrapping.
- Admin sidebar, responsive tables, modal/confirm actions and upload previews.

## Security checks still required

- Rotate the live database password and move all live secrets out of tracked source.
- Replace/remove default admin seed credentials before production.
- Verify HTTPS, secure cookies, CSP and HSTS on the actual host.
- Add role/permission controls, MFA and immutable admin action logs in the security phase.
- Run authenticated authorization tests for every admin and student endpoint.
- Run controlled upload tests with extension/MIME mismatch, oversized files and malformed images.

## Release decision

**Static package status: PASS WITH LIVE VERIFICATION REQUIRED.** The package is suitable for staging installation and regression testing. It should not be declared production-final until every relevant LIVE PENDING item above is tested against the real MySQL/MariaDB database and target hosting environment.
