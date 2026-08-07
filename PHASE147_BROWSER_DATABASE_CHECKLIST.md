# Well Fare English Spoken — Phase 147 Browser & Database Checklist

## 1. Installation

1. Back up the current database and project folder.
2. Replace the project with the cumulative ZIP, or copy the replace-only files over an exact Phase 146 installation.
3. For an existing database, import `sql/phase147_student_account_migration.sql` once.
4. Open **Admin → System Check** and confirm:
   - Student auth version column: PASS
   - Student password changed column: PASS
   - Student account audit table: PASS
5. Clear the old service worker/site storage and hard refresh.

## 2. Student Account List

- Open **Admin → Student Accounts**.
- Confirm total, active, inactive, never-login, recent-login and test counters load.
- Test search by name, phone and email.
- Test Active/Inactive, Level and Login Activity filters.
- Open a student through **Manage Account**.
- Test bulk activation, deactivation and hiding with two disposable accounts.
- Confirm no action affects an unselected account.

## 3. Profile and Status Management

- Change name, phone, email, level, language, daily goal, target and admin note.
- Confirm duplicate phone numbers are rejected.
- Deactivate an account and confirm login is blocked.
- Activate it again and confirm login works.
- Hide a disposable account and confirm it disappears from normal lists while test/practice rows remain in the database.

## 4. Password Reset — Required End-to-End Test

1. Log in as a student in a second/private browser window.
2. In Admin → Student Accounts → Manage Account, enter a new password twice.
3. Submit **Change Password**.
4. In the already logged-in student window, open a protected student page.
5. Confirm the old student session is invalidated and redirected to login.
6. Confirm the old password no longer works.
7. Confirm the new password works.
8. Confirm the Account Change History contains a password-reset event but never contains the password itself.
9. Confirm `password_changed_at` is updated and `auth_version` increases.

## 5. Force Sign Out

- Log in as a student.
- Click **Force Sign Out** in the admin account page.
- Open another protected student page and confirm the student is logged out.
- Confirm the password still works afterward.
- Confirm the force-sign-out event appears in Account Change History.

## 6. Compatibility Test Without Migration

Only on a disposable copy:

- Temporarily use the Phase 146 schema without the new columns/table.
- Confirm password reset still changes the password and invalidates sessions through the compatibility signature.
- Confirm audit falls back to `student_activity_logs`.
- Restore the normal migrated database afterward.

## 7. Regression Checks

- Student registration and login.
- Student dashboard and logout.
- Spoken Materials and Voice Coach.
- Learning Roadmap and Roadmap Lesson.
- Weekly Test start, autosave, submit and result.
- Admin enquiries, admissions, courses, batches, content, settings and uploads.
- Desktop, tablet and 320/360/390/430px mobile layouts.

## 8. Security Checks

- Submit every account-management form with an invalid CSRF token and confirm rejection.
- Try an invalid student ID and confirm no account data is shown.
- Confirm passwords are not present in HTML after submission, logs or audit rows.
- Confirm browser back navigation does not reveal a submitted password.
- Confirm admin session expiry and logout still work.

## Pending Environment-Dependent Verification

Static validation cannot prove MySQL writes, browser session invalidation, real authentication, upload permissions, speech APIs or physical-device rendering. Those items must be completed on localhost/staging before production deployment.
