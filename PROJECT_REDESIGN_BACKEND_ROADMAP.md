# Well Fare English Spoken — Cumulative Completion Roadmap

The project will be updated in cumulative phases. Each new ZIP must include all earlier fixes. Existing working business behavior must be preserved unless a phase explicitly changes it.

## Phase 1 — Reusable UI Foundation — Completed in this package

- Central brand tokens for navy, gold, white, status colors, spacing, radii, shadows and controls.
- Shared public/admin component CSS.
- One reusable button system with variants and sizes.
- One reusable card system with purposeful variants.
- One reusable form-field and input system.
- Shared badges, alerts, tables and layout helpers.
- PHP helpers in `includes/ui-components.php`.
- Admin UI Library preview page.
- Compatibility layer for gradual migration without breaking old pages.

## Phase 2 — Full Public Page Migration

Migrate page-by-page to the shared components:

1. Home
2. Courses and course details
3. Admission
4. Student login/registration
5. Student dashboard and revision
6. Practice materials
7. Learning roadmap and lesson pages
8. Weekly tests, exam room and results
9. Online class and AI teacher
10. About, faculty, gallery, reviews and contact
11. Shared header, footer, empty states and errors

For each page: remove duplicate CSS, preserve unique layout rules, verify desktop/tablet/mobile and keep content hierarchy consistent.

## Phase 3 — Admin UI and Workflow Migration

- Common admin page header, filter bar, form card, data table, action buttons, modal, bulk action and empty state.
- Consistent add/edit/view/list workflow.
- Responsive tables and mobile actions.
- Remove inline styles and repeated form/table CSS.
- Standardize toast, validation and confirmation feedback.

## Phase 4 — Business Logic and Process Correction

- Connect Enquiry → Admission → Student → Course → Batch with stable IDs.
- Prevent duplicate student/admission creation.
- Server-controlled student level and roadmap progress.
- Correct fee calculations and status derivation.
- Add reliable receipt/payment-history workflow before calling fee management complete.
- Correct official weekly test timing, submission and grading workflow.
- Preserve immutable result/history records where required.

## Phase 5 — Security Hardening

- Remove production secrets from tracked source and use environment-only values.
- Rotate exposed credentials.
- Remove fixed/default admin seed credentials.
- Strong admin authentication, lockout and optional MFA-ready structure.
- Rate limiting that fails safely.
- Strong authorization checks on every API/action.
- Session, CSRF, upload, output escaping and security-header review.
- Non-destructive safe security testing and abuse-case checks.

## Phase 6 — Database Relations and Integrity

- Add missing indexes and relationship constraints after cleaning orphan data.
- Replace text relationships with IDs where business integrity requires it.
- Add unique constraints for registration IDs, receipts, attempt references and other identifiers.
- Use transactions and row locking for multi-step writes.
- Add migration scripts with rollback/backup guidance.
- Keep display names join-based rather than duplicating mutable names in transactions.

## Phase 7 — Code Structure, Performance and Automated Tests

- Split the oversized functions file into focused services/helpers without changing public behavior.
- Create repositories/services for admissions, students, tests and content.
- Centralize validation and response handling.
- Remove obsolete phase CSS only after every selector is verified unused.
- Optimize assets, caching and database queries.
- Add automated regression tests for authentication, admissions, roadmap progress, weekly tests and uploads.

## Phase 8 — Final Regression, Accessibility and Deployment

- Full page and action checklist.
- Mobile, tablet and laptop visual review.
- Keyboard, focus, contrast, labels and reduced-motion review.
- Fresh-install SQL test and existing-database upgrade test.
- Local/live environment test.
- Backup, deployment, rollback and production checklist.
- Final cumulative ZIP and change report.

## Safe redesign method

The redesign is possible without damaging the whole website because it uses a compatibility-first migration:

1. Add tokens and components.
2. Keep existing selectors working.
3. Migrate one page/module at a time.
4. Compare before/after.
5. Remove old CSS only after usage verification.

A full instant CSS replacement would be risky. Gradual migration gives the same final theme with much lower regression risk.
