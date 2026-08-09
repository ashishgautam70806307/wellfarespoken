# Phase 153 - Admin Access Flow Repair

- Protected primary Super Admin is no longer trapped on Account Security by the stale Phase 148 legacy migration flag.
- Temporary-password enforcement remains active for normal staff accounts.
- While a staff password change is required, only Password & MFA and Logout are shown; dead menu/search links are hidden.
- Protected owner password cannot be reset from Admin Users; it must be changed from Account Security with the current password.
- No database schema change. Optional idempotent migration clears the stale owner flag on existing installations.
