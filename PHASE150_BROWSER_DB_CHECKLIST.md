# Phase 150 Browser / Database Checklist

1. Back up the current XAMPP database.
2. Import `sql/phase150_required_db_upgrade.sql`.
3. Open Admin > System Check.
4. Confirm exactly one active Super Admin is detected.
5. Confirm no non-Super role has `admins.manage`.
6. Login as the protected owner; Admin Users, Roles and Audit must be available.
7. Try creating a staff administrator; the role dropdown must not contain Super Admin.
8. Use DevTools or a modified POST to submit the Super Admin role ID; server must reject it.
9. Login as Manager / Academic Manager / Content Editor and confirm Admin Users / Roles / Audit are inaccessible.
10. Confirm Dashboard shows only modules allowed by that role.
11. Change a staff role and confirm its previous session is invalidated.
12. On 320px, 360px, 390px and 430px widths, confirm the public topbar shows compact location + announcement + Institute Login + Call access without horizontal overflow.
13. Confirm desktop topbar is unchanged.
14. Re-run the corrected Phase 148 migration on a backup copy with mixed collations; course/admission/batch backfill should not raise MySQL error 1267.
