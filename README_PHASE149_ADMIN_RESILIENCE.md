# Phase 149 - Admin Resilience & Form Recovery

Phase 149 is a focused cumulative repair on top of Phase 148. It does not change the database schema.

## What changed

- The internal UI Library remains in the codebase for design-system development but is hidden from the normal Admin sidebar and quick search.
- Admin pages now show a clear database-upgrade warning when the Phase 148 migration has not been imported.
- Admin Users, Roles and Audit Log no longer crash with missing-table fatal errors; they show a safe migration-required screen instead.
- Admissions checks the Phase 148 lifecycle/payment schema before saving and preserves the complete entered form after validation/database errors.
- A reusable Admin form-recovery script keeps non-sensitive text/select/textarea values in sessionStorage and restores them after an error/reload. Passwords and file contents are never stored.
- Image file controls receive immediate client-side previews. Admissions uses a fixed preview target; other Admin image inputs receive a generic inline preview when necessary.
- Student Account Control on the Admin Dashboard receives final contrast-safe styles so its text and metrics remain visible.
- Student Login/Register now have explicit Show/Hide password controls for Password and Confirm Password.
- Service-worker cache namespace is v149.

## Existing databases

If Admin shows the Phase 148 database-upgrade warning, back up the database and import:

`sql/phase148_critical_backend_hardening.sql`

Then open `Admin > System Check` and verify the Phase 148 tables/columns before using RBAC and the new Admission lifecycle/payment controls.
