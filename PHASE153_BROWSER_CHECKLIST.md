# Phase 153 Browser Checklist

1. Login as the protected existing Super Admin whose account previously opened only Account Security.
   - Expected: Dashboard opens directly.
   - Expected: all modules allowed to Super Admin open normally.
2. Open Admin Users and edit the protected owner.
   - Expected: no owner password reset field; Account Security link is shown.
3. Create a Manager with a temporary password.
   - Expected: first login opens Account Security only.
   - Expected: sidebar does not show unusable business modules.
   - Change password, then expected: Dashboard opens and Manager-permitted modules appear.
4. Login as Content Editor.
   - Expected: only permitted content modules appear and open.
5. Try direct URL to a module outside the staff role.
   - Expected: 403 Access denied.
6. Confirm Logout works from both normal Admin and temporary-password gate.
