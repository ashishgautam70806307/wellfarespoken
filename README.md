# Well Fare English Spoken — Phase 148 Critical Backend Hardening

This is the current cumulative working package. It preserves the approved frontend/admin/mobile/voice/test/student-account work through Phase 147 and repairs the critical/high-risk backend findings from the Phase 147 audit.

## Phase 148 highlights

- Secure environment-only production database credentials; no live secret fallback in source.
- Secure first-Admin setup; fresh SQL contains no fixed administrator account.
- Admin roles/permissions, audit log, session invalidation and optional free Authenticator/TOTP MFA.
- Database-backed/fail-closed authentication rate limiting.
- No-paid-OTP student model: mobile + student-created password, identity starts **Unverified**, optional Admin approval mode, manual staff verification before identity-sensitive linking.
- Student official level starts at Zero Level / Unassessed.
- Stable Enquiry → Admission → Student → Enrollment → Batch relationships and history.
- Immutable admission payment/refund/adjustment ledger and receipt history.
- Orphan audit and 51 planned foreign-key constraints.
- Private authenticated learning-file delivery and access audit.
- Formal schema migration registry, CLI migration status, static tests and database smoke test.
- Service-worker cache namespace `wellfare-spoken-static-v148`.

## Existing Phase 147 database

1. Back up DB/files.
2. Rotate the previously exposed live DB password externally and configure the new secret only in server `.env`.
3. Deploy Phase 148.
4. Import `sql/phase148_critical_backend_hardening.sql` once.
5. Open Admin → System Check.
6. Complete `PHASE148_DEPLOYMENT_CHECKLIST.md` before production use.

## Fresh installation

Import `sql/wellfare_english_complete.sql`, configure `.env`, then create the first administrator through `admin/setup.php`.

## Documentation

- `README_PHASE148_CRITICAL_BACKEND_HARDENING.md`
- `PHASE148_CRITICAL_BACKEND_HARDENING_REPORT.md`
- `PHASE148_DEPLOYMENT_CHECKLIST.md`
- `tests/run_phase148_static.php`
- `tests/phase148_database_smoke.php`
- `tools/migration-status.php`
