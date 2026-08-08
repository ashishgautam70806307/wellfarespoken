# Phase 148 — Critical Backend Hardening Report

## Decision: mobile + student-created password, no paid OTP

Yes. For this institute, **mobile number + a password created by the student** is a reasonable zero-cost registration flow as long as the mobile is not silently treated as verified identity.

Phase 148 therefore uses this model:

1. Student enters name, mobile and their own password.
2. Student confirms the mobile belongs to them or is being used with permission.
3. Account is created as **Unverified**.
4. With `STUDENT_REGISTRATION_MODE=open`, the student can immediately use learning features. With `approval`, the account waits for Admin activation.
5. Existing admission/payment/enrollment ownership is **not auto-linked** from the self-entered phone while it remains Unverified.
6. Staff can verify the phone later at no SMS cost: in person, by a staff-initiated call, or through an already trusted WhatsApp conversation. A verification note is mandatory.
7. Forgotten-password recovery remains Admin-controlled. Admin must record a reset reason and should manually confirm the student/guardian before sharing the new password.

This avoids fake-account identity claims without forcing the client to buy an OTP service today.

---

## C1 — Hard-coded live database credential

**Status: code fixed; one external action remains mandatory.**

- Production credential fallbacks were removed from `includes/config.php`.
- `.env.example` contains blank `DB_LIVE_*` values only.
- A live site fails with a safe configuration error if production DB name/user are missing.
- `.env` remains ignored and must never be distributed.

**External action:** rotate the database password that existed in older project copies from the hosting/database panel, then save only the new value in the server `.env`. A source-code update cannot rotate an external database user password.

## C2 — Predictable fixed Admin seed

**Status: fixed.**

- Fresh canonical SQL no longer inserts a fixed Admin account.
- `admin/setup.php` creates the first Super Admin only when no active Admin exists.
- Live first-run setup can require `ADMIN_SETUP_KEY`.
- Upgrade SQL disables the exact untouched legacy seed; a previously customized legacy owner is instead forced to change the password.
- Admin password policy is 12–128 characters with at least one letter and number.

## H1 — No database foreign keys / orphan risk

**Status: fixed at schema-migration level; real import must still be executed.**

- Added `data_integrity_orphans` to preserve an audit of invalid legacy relationships before cleanup.
- Invalid legacy FK values are normalized safely before constraints are created.
- Canonical schema/migration now contains **51 foreign-key constraints**.
- FK integer signedness validation passed with zero mismatches.
- All `ON DELETE SET NULL` child columns are nullable.
- Relationship rules use `CASCADE`, `RESTRICT` or `SET NULL` according to history/safety needs.

Because MySQL DDL is not fully transactional, take a database backup before importing the migration.

## H2 — Enquiry / Admission / Student / Course / Batch disconnected

**Status: fixed for the current business scope.**

- Added stable `course_id`, `batch_id`, `enquiry_id` and `student_id` relationships.
- Added `student_enrollments` and `student_batch_memberships` so history is preserved instead of overwriting one current record.
- Existing course/batch names remain as snapshots for audit/readability.
- Admin can explicitly convert an Enquiry to an Admission.
- Admission status changes synchronize enrollment state.
- Batch changes close the old active membership and create a new membership.
- Enquiries/admissions use soft-delete/cancel semantics rather than destructive deletion.
- Existing admission linkage by phone is allowed only after the student mobile is marked **Verified**, preventing a self-registered fake mobile from claiming someone else's admission.

## H3 — Fee snapshot instead of payment ledger

**Status: fixed for current institute fee collection.**

- Added append-only `admission_payments` rows for Payment, Refund, Adjustment and migrated Opening balances.
- Strong unique receipt numbers are generated for new entries.
- Admission `paid_amount` and payment status are recalculated from ledger history.
- Refunds cannot exceed the net amount received.
- Payments cannot exceed the current outstanding balance when a fee is due.
- Refund/Adjustment requires an audit note; Payment requires a payment mode.
- Direct edit of historical paid amount was removed from the normal admin update flow.

This is a fee/payment ledger, not a full double-entry accounting system.

## H4 — Admin authorization all-or-nothing

**Status: fixed for current single-institute scope.**

Added:

- Super Admin, Manager, Academic Manager and Content Editor roles.
- Central permissions for Dashboard, CRM, Admissions, Students, Courses, Batches, Materials, Roadmap, Tests, Website Content, Settings, System and Admin Management.
- Server-side page/action enforcement; UI hiding is only supplemental.
- Table-specific publish permissions.
- Append-only administrator audit events for sensitive and generic POST actions.
- Administrator session signature/auth-version invalidation.
- Optional free RFC6238 TOTP MFA compatible with common authenticator apps.
- Strong first-admin setup and Admin password-change flow.

Branch-level authorization is not relevant to the current single-institute project and is therefore not invented in this phase.

## H5 — Rate limiter fails open

**Status: fixed.**

- Authentication rate limits prefer a database-backed `security_rate_limits` table with row locking.
- Student registration/login have per-mobile and per-IP limits.
- Admin login/setup/MFA are protected by auth rate-limit buckets.
- Before migration, file fallback remains for compatibility, but authentication endpoints now **fail closed** if the fallback cannot be locked/written.

## H6 — New student account instantly trusted

**Status: fixed without paid OTP.**

- Self-registration identity = `Unverified`.
- Configurable `STUDENT_REGISTRATION_MODE=open|approval`.
- Open mode permits low-risk learning access but does not make the phone trusted.
- Approval mode blocks login until Admin activation.
- Manual verification note is required before first marking a phone Verified.
- Changing the phone automatically resets verification and invalidates current sessions.
- Unverified accounts cannot auto-link admission ownership.

## H7 — Student self-selects official level

**Status: fixed.**

- Registration always creates `Zero Level / Unassessed`.
- Official level is assessment/Admin controlled.
- Existing roadmap prerequisite enforcement remains intact.

## H8 — Learning files publicly reachable

**Status: core exposure fixed.**

- New uploads are stored under private storage using random filenames.
- Public material upload directories deny direct HTTP access for legacy assets.
- `material-file.php` requires an authenticated Student or Admin and validates the asset, published state, storage path and MIME type before streaming.
- Physical storage paths are no longer exposed in material API output.
- View/Download events are recorded in `material_access_logs` as best-effort append-only audit records.
- Live System Check warns when private storage is still inside the document root; production should configure `PRIVATE_STORAGE_PATH` outside public web root.

Course-by-course file entitlements are not introduced because current material collections are shared learning content. If future paid/private course material is added, collection/enrollment targeting should become a separate permission layer.

---

## Additional medium-risk improvements completed

- Formal `schema_migrations` registry and numbered migration file.
- CLI migration-status tool and DB smoke-test script.
- Pagination for Students, Admissions and Enquiries.
- Split shared security/rate-limit code and Phase 148 lifecycle/RBAC/ledger helpers out of the large legacy functions file.
- Soft-delete Enquiries and preserve history.
- Stable IDs plus snapshots instead of text-only relations.
- System Check expanded for RBAC, rate limits, private storage, migration state, foreign keys, legacy seed and environment security.

## Important remaining non-critical engineering work

These are not the previous C1/C2/H1–H8 blockers, but they should be planned later:

- Finish breaking the large legacy `functions.php` and large CSS bundle into smaller domain modules.
- Add pagination to every remaining large CMS listing if its data volume grows.
- Replace scattered `error_log`/silent catches with centralized structured error logging and monitoring.
- Add full browser/integration tests in CI; current Phase 148 includes static and DB smoke scripts only.
- Consider self-hosting or integrity-locking third-party frontend assets and tightening CSP when external asset requirements are finalized.
- Define explicit privacy retention/export/anonymization rules before collecting larger amounts of student data.
- If AI provider features are enabled later, add quota, cost and provider-data/privacy controls.

## Validation limitation

The package passes PHP/JavaScript/CSS/static schema validation. The current build environment has no MySQL/MariaDB server or `pdo_mysql`, so the Phase 148 migration and real authenticated workflows cannot honestly be marked runtime-PASS until tested on XAMPP/staging using the included checklist.
