# Well Fare English Spoken — Phase 147 Student Accounts & Backend Audit

**Audit date:** 6 August 2026  
**Cumulative base:** Phase 146  
**Phase 147 scope:** advanced student account management, administrator-controlled password reset, session invalidation, account audit history, and a static audit of the remaining backend modules.

## Executive Result

The project has a substantial working foundation, but it is **not yet reasonable to call every backend module production-perfect**. Static code validation passed, and many security fundamentals are already present, but real MySQL/browser workflows remain unverified and several architecture, security and data-integrity gaps should be corrected in the next phases.

Phase 147 adds a complete administrator-facing Student Account Manager. An administrator can now find a student, edit account/profile settings, activate or deactivate access, force logout, hide an account safely, inspect learning/test activity and set a new password without knowing the old one. Password changes invalidate existing student sessions and are recorded in an audit timeline without storing the password.

## Phase 147 Feature Implementation

### Student Accounts dashboard

`Admin → Student Accounts` now provides:

- total, active, inactive, never-logged-in, recently active and test-attempt counters;
- search by name, phone, email and learning goal;
- filters by account status, level and login activity;
- responsive account cards with last login, level, practice and test counts;
- bulk activate, deactivate and soft-hide actions;
- direct access to the full account-management page.

### Full student account management

The account page now supports:

- name, phone, email, current level and preferred language;
- daily practice target and learning goal;
- internal administrator note;
- account activation/deactivation;
- administrator-selected password reset;
- force sign-out from all current sessions;
- safe account hiding while preserving history;
- recent tests, practice, wrong answers, manual activity and account-change timeline.

### Password-reset security behavior

- The administrator chooses the new password; the old password is not required.
- The new password must satisfy the same 8–128 character boundary used by student authentication.
- Only the password hash is stored.
- The password itself is not written to audit logs.
- Existing sessions are invalidated by incrementing `auth_version`.
- A compatibility signature based on the password hash and account update state is available when the migration has not yet been imported.
- Deactivation, hiding and force logout also invalidate sessions.

### Schema additions

- `students.auth_version`
- `students.password_changed_at`
- `student_account_events`

Fresh installations receive these through the canonical SQL. Existing installations must import the Phase 147 migration. Runtime schema alteration remains disabled by default and is not relied upon for production deployment.

## Static Validation Result

- PHP syntax: **67/67 passed**
- JavaScript/service-worker syntax: **13/13 passed**
- CSS brace validation: **62/62 passed**
- Literal local references checked: **208, zero missing**
- Duplicate literal IDs: **zero**
- Service-worker cache assets: **42/42 present**
- POST handlers detected: **25, all contain a CSRF marker**
- Canonical SQL tables: **41 unique tables**
- Foreign-key definitions: **0**
- Database schema and real browser workflows: **live verification pending**

## Existing Strengths

The project already has several good foundations:

- PDO prepared statements are widely used.
- CSRF validation is present across detected POST handlers.
- Passwords use PHP password hashing and rehash support.
- Admin and student sessions regenerate IDs on authentication.
- Output is generally escaped through a central helper.
- Redirect targets and public URLs have validation helpers.
- Upload helpers validate type/size and protect upload folders from PHP execution.
- SVG uploads are blocked in the central image workflow.
- Weekly tests have attempt tokens, ownership checks, autosave, timing and result controls.
- Roadmap prerequisite checks and transactional course-variant updates exist.
- The UI has reusable design tokens/components and cumulative responsive layers.
- PWA static assets are versioned and validated.

# Remaining Backend Findings

## Critical — fix before a production launch

### C1. Live database secret is hard-coded in source

`includes/config.php` contains a live database profile with a real credential fallback. Any source-code leak, backup leak or accidental repository upload exposes production access.

**Required next action:** rotate the live database password immediately, remove every live credential from source, store it only in a server-side `.env`/secret manager, and prevent `.env` from being web-accessible or included in public archives.

### C2. Canonical SQL contains a fixed default administrator seed

The canonical SQL inserts a fixed administrator account. A stable seed account creates a predictable production entry point and the same hash may be reused across installations.

**Required next action:** remove the production seed or replace it with a one-time setup command that creates a unique password and forces password rotation at first login.

## High priority

### H1. No database foreign-key constraints

The canonical schema contains 41 tables and zero `FOREIGN KEY` definitions. IDs such as student, test, attempt, question, roadmap unit and course references can become orphaned after deletion or manual data changes.

**Risk:** silent data corruption, inaccurate reports and difficult cleanup.

**Next phase:** first audit orphan data, then add compatible indexes and foreign keys with carefully chosen `RESTRICT`, `CASCADE` or soft-delete rules.

### H2. Enquiry, admission, student, course and batch are not one controlled lifecycle

The public admission form creates an `enquiries` row. Admin admissions are a separate manual record. Student accounts are another independent entity. Course and batch selections are stored mainly as text instead of stable IDs.

**Risk:** duplicates, mismatched names, repeated data entry, no reliable conversion reporting and loss of history when names change.

**Next phase:** implement an approval-based conversion flow: Enquiry → Admission → Student Account → Enrollment → Batch Membership. Preserve source IDs and snapshots rather than overwriting history.

### H3. Fee management is a snapshot, not a financial ledger

Admissions store total fee, discount, paid amount, payment status, payment mode and one receipt number in the same row. There are no immutable payment entries, receipt versions, refunds, reversals or adjustment history.

**Risk:** paid amount and payment status can contradict each other, previous payments can be overwritten, and financial audit is weak.

**Next phase:** add a payment/receipt ledger with transaction rows and compute admission balances from the ledger.

### H4. Administrator authorization is all-or-nothing

The project has central admin login, but no role/permission matrix, branch/data scope, maker-checker approval or module-level authorization. Any admin session can access every module and sensitive student password controls.

**Next phase:** add roles, permissions, per-action policies, account-lock rules and complete admin action auditing. Add optional MFA for sensitive accounts.

### H5. File-based rate limiting fails open

When the rate-limit directory cannot be opened or locked, the limiter deliberately allows the request. A permissions or storage problem therefore silently disables brute-force protection.

**Next phase:** use database/Redis-backed rate limits or fail safely for authentication endpoints while showing a controlled temporary-error message.

### H6. New student accounts are active immediately

Self-registration creates a published account and logs the student in immediately. There is no OTP, email/mobile verification, institute approval or admission match.

**Risk:** fake accounts, duplicate identities and access without institute verification.

**Next phase:** configurable registration states: pending verification, pending institute approval and active. Link approved registrations to admission/enrollment data.

### H7. Student can choose an official level during registration

The registration form accepts Zero, Basic, Intermediate or Advanced. Although roadmap completion is protected, a new user can select a higher official profile level without assessment.

**Next phase:** default registrations to Zero/Unassessed, then let an assessment or authorized admin assign the official level.

### H8. Private learning files are served from public paths

Material and document URLs are stored and rendered as public assets. There is no signed download route, enrollment authorization, expiry or malware-scanning workflow.

**Next phase:** private storage, authorized streaming/download controllers, random internal filenames, audit logs and optional antivirus scanning.

## Medium priority

### M1. No consistent migration/versioning system

Schema helpers attempt runtime `ALTER/CREATE` operations and broadly catch failures. Production defaults disable automatic changes, which is safer, but there is no formal migration runner or applied-version table.

**Risk:** environments can silently have different schemas.

**Next phase:** numbered SQL migrations, migration history, dry-run/status command and rollback guidance.

### M2. Several admin lists load all matching rows

Student Accounts and multiple CRUD modules fetch all matching records without database pagination.

**Risk:** slow pages and high memory usage as data grows.

**Next phase:** reusable server-side pagination, bounded page sizes and indexed filters.

### M3. Large central files increase regression risk

`includes/functions.php` is approximately 4,909 lines and the main unminified stylesheet is approximately 452 KB.

**Next phase:** extract authentication, students, weekly tests, materials, uploads, roadmap and settings into services/repositories while preserving public helper APIs. Split legacy CSS only after visual regression tests exist.

### M4. Silent catch blocks hide operational failures

Multiple schema/update/statistics blocks catch all exceptions and continue. This keeps pages alive but may show incomplete data without telling the administrator.

**Next phase:** central error IDs, structured logs, an admin health dashboard and explicit degraded-state warnings.

### M5. Audit logging is incomplete outside student accounts

Phase 147 audits student account security actions, but destructive changes to courses, batches, admissions, enquiries, content, settings and files do not have one consistent append-only audit system.

### M6. Hard-delete and soft-delete behavior is inconsistent

Some entities use `status_deleted`; other child rows or older CRUD actions are physically deleted. Historical relationships can be lost.

### M7. Course/batch relations use names instead of stable identifiers

`batch_timings.course_name`, admission course interest and batch preference are text values. Renaming a course can create inconsistent historical records.

### M8. No enrollment and batch-membership entities

A student account does not have a formal dated enrollment and membership history. Course changes, transfers, batch changes, completion and cancellation cannot be represented cleanly.

### M9. External frontend dependencies require hardening

Remote fonts/icons may fail offline and complicate Content Security Policy/privacy. Consider local optimized assets or strict allowlists/SRI where supported.

### M10. AI Teacher requires provider-level controls

When enabled, the AI Teacher depends on an external provider and cURL. It needs quota limits, cost controls, prompt/input privacy rules, timeout/retry policy, content moderation and user-visible failure handling.

### M11. Privacy and retention controls are incomplete

There is no complete student consent, data export, retention, deletion/anonymization or document-access policy.

### M12. No automated integration/regression suite

Static validation is useful but does not prove database transactions, authorization, concurrency, speech APIs, uploads or responsive interactions.

## Low / polish priority

- Standardize status values and badges across all modules.
- Replace remaining inline styles with shared components gradually.
- Add consistent empty/error/loading states to older admin pages.
- Add CSV import/export validation reports and duplicate previews where bulk data will grow.
- Add accessibility regression tests for keyboard focus, labels, dialogs and tables.

# Module-by-Module Static Audit

| Module | Static status | What is already present | Main remaining issue / live test |
|---|---|---|---|
| Admin authentication | Pass with risks | Hash verification, CSRF, rate limit, session regeneration | Fixed seed, hard-coded DB secret, no MFA/RBAC, rate limit fail-open |
| Admin dashboard | Pass | Metrics, links, Phase 147 account-control panel | Verify all counts against live data; add role-scoped widgets later |
| Student accounts | Phase 147 static pass | Search, filters, profile, password reset, status, force logout, audit | Import migration and perform real two-browser session invalidation test |
| Student registration/login | Static pass | Hashing, duplicate phone check, login sessions | Immediate activation, no verification/approval, self-selected level |
| Enquiries | Static pass | Public lead capture, duplicate suppression, CRM fields | No formal conversion workflow or full follow-up history |
| Admissions | Static pass | Rich admin record, fee snapshot, follow-up fields | Not linked to enquiry/student/enrollment; no payment ledger |
| Courses/variants | Static pass | Transactional save and published course data | No enrollment relation; deletion/history rules need standardization |
| Batches | Static pass | CRUD and course dropdown | Saves course name text; no capacity/membership/attendance relation |
| Faculty | Static pass | CRUD, image upload, publishing | No assignment/history model; live upload permissions pending |
| Testimonials/reviews | Static pass | CRUD, rating, image, publishing | No approval/version/audit workflow |
| Gallery/videos | Static pass | Media CRUD and public rendering | Public storage, no malware scan/version/audit |
| FAQs/content/menus/SEO/settings | Static pass | Dynamic CMS controls | No revision history, approval or full audit trail |
| Spoken Materials | Static pass | Four modes, Voice Coach, answer API, revision logic | Real microphone/voice/API/authenticated revision test pending; file privacy |
| Learning Roadmap | Static pass | Groups, units, items, prerequisite checks, progress | No FKs; registration level governance; real concurrent completion test |
| Weekly Tests | Static pass | Guest/auth access, attempts, autosave, scoring, results, history | Full MySQL/browser/concurrency/timer-expiry regression still required |
| AI Teacher | Feature-flagged / pending | Disabled-ready provider integration | Privacy, quota, cost, moderation, timeout and provider configuration |
| PWA/service worker | Static pass | Versioned static cache, asset check | Install/update/offline testing on real browsers pending |
| System Check | Improved | Phase 147 schema checks added | Expand into actionable migration/storage/queue/security health report |

# Recommended Next Phases

## Phase 148 — Critical security and environment hardening

- rotate/remove the exposed live database credential;
- remove fixed admin seed and build first-run setup/password rotation;
- strengthen authentication rate limits and admin lockouts;
- introduce admin action auditing and initial roles/permissions;
- verify security headers, backups and production `.env` isolation.

## Phase 149 — Student lifecycle and data integrity

- enquiry-to-admission conversion;
- student enrollment and batch membership;
- stable course/batch IDs with history snapshots;
- duplicate detection and controlled merge;
- orphan report, indexes and safe foreign-key rollout.

## Phase 150 — Fees and receipts

- immutable payment ledger;
- receipt generation/history;
- discount/refund/reversal approvals;
- computed balance/status consistency.

## Phase 151 — Architecture, pagination and automated tests

- modular services/repositories;
- reusable pagination/filter layer;
- integration tests for auth, password reset, weekly tests, roadmap and materials;
- CI/static analysis and deployment checks.

## Honest Limitations of This Audit

This report is based on source-code, schema and static validation. No connected MySQL server, authenticated multi-browser session, real email/SMS provider, microphone, file-system permission matrix or physical-device browser was available. Therefore modules described as “static pass” must still complete the supplied browser/database checklist before production use.
