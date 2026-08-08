# Well Fare English Spoken — Phase 149 Admin Resilience & Form Recovery

## Scope
Phase 149 is a cumulative repair on top of Phase 148. It focuses on the exact problems reported from the real localhost screenshots. No new database schema is introduced in Phase 149.

## Root cause found in the screenshots

### Admin Users fatal error
The error `Table '...admin_roles' doesn't exist` is not a design problem. It means the Phase 148 database migration was not imported on the current localhost database. The Phase 148 code correctly contains RBAC, but the database still has the older schema.

Phase 149 now prevents the fatal crash:
- Admin Users shows a migration-required screen instead of executing missing-table queries.
- Roles & Permissions does the same.
- Admin Audit Log does the same.
- The Admin shell shows a visible database-upgrade warning before the user enters a module that needs the new schema.
- RBAC pages are hidden from the normal sidebar/quick search until the required schema is present.

The real fix is still to back up the database and import `sql/phase148_critical_backend_hardening.sql`, then verify Admin > System Check.

## 1. UI Library hidden from Admin
`admin/ui-library.php` is retained internally because it is a design-system developer reference, but it is removed from:
- Admin sidebar
- Admin quick search

No frontend component depends on opening this page, so hiding it has no effect on the live design system.

## 2. Form data loss protection
The admission form now has two layers of protection.

### Server-side Admission recovery
When Admission Save fails because of validation, missing schema, CSRF expiry or a database exception:
- the submitted non-secret values are saved temporarily in the PHP session;
- the user is redirected safely;
- all entered values are restored into the form;
- an already-uploaded admission photo path is retained when the server accepted the upload before a later error;
- the user sees a clear “Form restored” message.

The admission page also checks the required Phase 148 lifecycle/payment tables and columns before attempting the save, so a missing migration is shown clearly instead of wasting a long filled form.

### Reusable Admin form recovery
A single reusable Admin script now protects normal POST forms across Admin pages:
- saves non-sensitive text/select/textarea/checkbox/radio values into `sessionStorage` while the form is being filled;
- restores them after an error redirect or page reload;
- automatically clears the current page draft after a success state;
- expires drafts after one hour in the same browser tab;
- never stores passwords, file contents, CSRF tokens, action IDs or submit-button data.

Delete/status/bulk/toggle forms are intentionally excluded.

## 3. Immediate image preview
A reusable image-preview handler now runs across Admin image fields.
- Admissions has a fixed photo-preview target.
- Hero Banner existing targets continue working.
- Gallery existing preview target continues working.
- Faculty, Course, Testimonial, Settings logo/favicon/director image and similar image inputs receive a live inline preview even when the old page did not define one.

The preview is client-side only and does not upload the file until the form is submitted.

## 4. Student Account Control visibility
A late, contrast-safe Admin style now guarantees that the Dashboard Student Account Control section has:
- white visible heading;
- readable light body text;
- visible gold label;
- white metrics;
- responsive 2-column metric layout on small screens;
- stronger card/background separation.

This fixes the dark-card text becoming unreadable because of competing global heading colors.

## 5. Student Login/Register password show-hide
Both Student Login and Student Registration now provide explicit show/hide controls:
- Password
- Confirm Password (registration)

The control is an actual accessible button with `aria-pressed`, changes eye/eye-slash state, and does not alter the submitted password value.

Student auth fields also receive a cleaner grid and compact mobile treatment.

## 6. Admin search alignment polish
The Admin top search icon and input padding are corrected so the magnifying-glass icon cannot overlap the placeholder/text.

## 7. Workflow / broken-link checks
Static project checks found:
- no active reference to the previously removed `student-revision.php`;
- 271 genuine literal local file/page references checked with zero missing targets;
- zero duplicate literal HTML IDs;
- all 46 Service Worker static assets exist;
- no new redirect loop was introduced by Phase 149 guards/recovery redirects.

## Database action required on the user's current localhost
The screenshot proves the current DB is older than the Phase 148 code. Back up the database, then import:

`sql/phase148_critical_backend_hardening.sql`

After import:
1. Open Admin > System Check.
2. Confirm Admin Roles, Admin Permissions, Audit, Rate Limit, Admission Payments, Student Enrollments and Student Batch Memberships are available.
3. Reload Admin Users.
4. Test one Admission save.

## Validation
- 78 PHP files: syntax PASS
- 15 JavaScript/Service Worker files: syntax PASS
- 66 CSS files: balanced syntax PASS
- Phase 148 static security suite: PASS
- Phase 149 focused static suite: PASS (15/15)
- 271 literal local references: 0 missing
- Duplicate literal IDs: 0
- Service Worker assets: 46/46 present
- Detected real POST handlers: CSRF marker present; the one scanner exception is the shared request-audit helper, not a POST endpoint

## Remaining limitation
MySQL/MariaDB is not available inside this build environment, so the actual Phase 148 migration cannot be executed here against the user's localhost database. The missing-table screenshot is therefore fixed at code/UX level and the required migration is included, but the user must import that SQL on the real XAMPP database.
