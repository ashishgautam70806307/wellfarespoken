# Phase 148 Deployment / Real-Environment Checklist

## A. Before deployment — mandatory

- [ ] Back up the complete current database.
- [ ] Back up the current project files and private uploads.
- [ ] **Rotate the old production database password in hosting/database control panel.** Older project copies contained a live credential.
- [ ] Put only the new credential in the server `.env` using `DB_LIVE_*` or manual `DB_*` mode.
- [ ] Confirm `.env` is not inside a downloadable backup/ZIP/repository.
- [ ] Set `APP_DEBUG=false` on live.
- [ ] Set `APP_ALLOW_SCHEMA_UPDATES=false` for normal live traffic.

## B. Code + database upgrade

- [ ] Deploy the cumulative Phase 148 code.
- [ ] Import `sql/phase148_critical_backend_hardening.sql` into an existing Phase 147 database.
- [ ] For a fresh install, import `sql/wellfare_english_complete.sql` instead.
- [ ] Run `php tools/migration-status.php` from CLI if available.
- [ ] Open Admin → System Check and make every critical check green.
- [ ] Confirm foreign-key count is at least 40; Phase 148 canonical code defines 51.
- [ ] Review `data_integrity_orphans` after migration for any archived legacy relationship problems.

## C. Administrator security

- [ ] If there is no active Admin, set a strong random `ADMIN_SETUP_KEY`, visit `admin/setup.php`, create the owner, then rotate/remove the setup key.
- [ ] Confirm no untouched `admin@wellfare.local` predictable credential remains active.
- [ ] Give daily staff the minimum required role; do not share Super Admin.
- [ ] Enable Authenticator/TOTP MFA for the Super Admin.
- [ ] Change an Admin password and confirm the previous session becomes invalid.
- [ ] Confirm a Content Editor cannot open Students/Admissions/System/Admin pages.
- [ ] Confirm a Manager cannot manage Admin users/roles.
- [ ] Review Admin → Audit Log after test actions.

## D. Student no-OTP registration

- [ ] Register a new student with a 10-digit mobile and student-created 8–128 character password.
- [ ] Confirm official level is `Zero Level / Unassessed` regardless of client input.
- [ ] Confirm the account shows Mobile Identity = Unverified in Admin.
- [ ] In `STUDENT_REGISTRATION_MODE=open`, confirm the student can use normal learning features immediately.
- [ ] Confirm an Unverified student's phone does **not** automatically attach an old Admission.
- [ ] From Admin, mark the mobile Verified and enter a meaningful verification note.
- [ ] Confirm the verified student can then link to the intended admission lifecycle where applicable.
- [ ] Change the student's phone and confirm identity resets to Unverified and old sessions are invalidated.
- [ ] Test `STUDENT_REGISTRATION_MODE=approval` once if the institute may use approval mode later.

## E. Forgotten student password

- [ ] Admin sets a new student password without the old password.
- [ ] Reset Reason is required.
- [ ] Confirm the old password stops working.
- [ ] Confirm old browser/session is invalidated.
- [ ] Confirm the new password works.
- [ ] Confirm the password value itself does not appear in account/admin logs.
- [ ] Confirm staff does not share a reset password for an Unverified phone until identity is manually checked.

## F. Enquiry → Admission → Enrollment → Batch

- [ ] Create a public Enquiry with an existing course/batch.
- [ ] Admin converts the Enquiry to an Admission.
- [ ] Verify stable enquiry/course/batch IDs and text snapshots are both present.
- [ ] Verify the matching Student is linked only if the mobile is Verified.
- [ ] Activate/complete/cancel an Admission and confirm enrollment status follows correctly.
- [ ] Change an active batch and confirm the previous membership closes and the new membership is created.
- [ ] Soft-hide an Enquiry and confirm history remains available in the database.

## G. Payment/receipt ledger

- [ ] Create an Admission with a fee and initial payment.
- [ ] Add another Payment and verify unique receipt number and recalculated balance.
- [ ] Attempt to overpay and confirm it is rejected.
- [ ] Add a partial Refund with a required reason.
- [ ] Attempt a Refund larger than net received and confirm it is rejected.
- [ ] Add an Adjustment with a reason and confirm the ledger is append-only.
- [ ] Confirm historical payment rows are not silently overwritten.

## H. Private learning files

- [ ] On live, configure `PRIVATE_STORAGE_PATH` outside the public document root and make it writable by PHP only as needed.
- [ ] Upload a PDF/image learning asset and confirm the stored path begins with `private/materials/`.
- [ ] Directly requesting the physical legacy upload URL must be denied.
- [ ] Logged-out request to `material-file.php?id=...` must be denied.
- [ ] Logged-in Student/Admin can open a published asset through `material-file.php`.
- [ ] Unpublished/deleted asset returns not found.
- [ ] Confirm View/Download rows appear in `material_access_logs`.

## I. Rate-limit/security regression

- [ ] Repeated wrong Student logins eventually show a temporary rate-limit response.
- [ ] Repeated wrong Admin logins are throttled.
- [ ] CSRF failure blocks sensitive POST actions.
- [ ] Sessions use secure cookie settings on HTTPS.
- [ ] Security headers are present on live responses.

## J. Automated checks

Run from the project root when CLI PHP + MySQL are available:

```bash
php tests/run_phase148_static.php
php tests/phase148_database_smoke.php
php tools/migration-status.php
```

## K. Final regression

- [ ] Student Registration/Login/Logout/Dashboard.
- [ ] Roadmap and Roadmap Lesson voice/practice.
- [ ] Spoken Materials voice input/output.
- [ ] Weekly Basic/Previous/Upcoming flow, autosave, submit and Result.
- [ ] Admin Students, Enquiries, Admissions, Payments, Courses, Batches, Materials, Roadmap, Tests and Website Settings.
- [ ] Mobile 320/360/390/430 widths and desktop.
- [ ] Clear old service worker/site cache and reload; Phase 148 uses `wellfare-spoken-static-v148`.

## Rollback

Do not rely on a reverse SQL script for this migration: MySQL DDL can auto-commit. If the real migration fails, stop writes and restore the database + files from the pre-Phase-148 backup, then investigate on staging before retrying.
