# Phase 149 Real Browser / Database Checklist

## A. Required database upgrade
- Back up the current database.
- Import `sql/phase148_critical_backend_hardening.sql` once on the existing database.
- Open Admin > System Check.
- Confirm `admin_roles`, `admin_permissions`, `admin_role_permissions`, `admin_audit_events`, `security_rate_limits`, `admission_payments`, `student_enrollments`, and `student_batch_memberships` are present.

## B. Admin Users
- Before migration, direct `admin/admin-users.php` must show a safe database-upgrade screen and no Fatal Error.
- After migration, Admin Users list must open normally.
- Create/edit one non-Super-Admin account and confirm roles load.

## C. Admissions form recovery
- Fill Student Name, Phone, Email, Course, Batch, Fee Plan, fee values, dates and notes.
- Intentionally create a validation error (for example Discount > Total Fee).
- Submit.
- Confirm the page reloads with every entered non-file value restored.
- Confirm a clear restored-form message appears.
- Correct the error and save successfully.
- Confirm the draft does not unexpectedly reappear after success.

## D. Missing migration protection
- On a copy of an old database, open Admissions.
- Confirm a database-upgrade warning is visible before Save.
- Submit a filled form and confirm values are restored instead of disappearing.

## E. Image preview
Test image selection without submitting on:
- Admissions student photo
- Faculty photo
- Course image
- Testimonial student photo
- Gallery image
- Hero Banner desktop/mobile/fallback
- Settings logo/favicon/director photo

The selected image should appear immediately.

## F. Student Account Control
- Open Admin Dashboard on desktop and 320/375/768px widths.
- Confirm Student Account Control heading, text, buttons and all metrics are readable.

## G. Student password visibility
- Open Student Login and toggle Password show/hide.
- Open Student Register and toggle both Password and Confirm Password.
- Confirm the value is not cleared when toggled.
- Confirm mobile fields/buttons do not overlap.

## H. UI Library
- Confirm UI Library is absent from Admin sidebar and Admin quick search.
- Existing frontend/admin component styling must remain unchanged.

## I. Cache
- Clear/unregister the previous Service Worker once after deployment.
- Hard refresh.
- Confirm Service Worker cache is `wellfare-spoken-static-v149`.
