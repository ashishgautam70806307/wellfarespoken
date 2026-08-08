# Phase 150 Access-Control and Mobile Header Report

## Root causes found

### 1. Multiple Super Admin risk
The old Phase 148 upgrade contained an unsafe compatibility statement that assigned the Super Admin role to every existing administrator whose `role_id` was NULL. On an older installation with multiple admin rows, this could create multiple Super Admins.

The Admin Users page also allowed the protected role to appear to the owner as a normal assignable role. Although non-Super staff were blocked from selecting it, the business rule should be stronger: the owner role should never be assignable from normal admin management.

### 2. RBAC before migration was fail-open
`admin_can()` returned `true` for every permission while the RBAC schema was missing. That was useful for compatibility but unsafe. It is now fail-closed for business/admin modules and allows only Dashboard + System Check until the migration is completed.

### 3. Dashboard did not fully follow permissions
Sidebar links were permission-aware, but Dashboard top actions, metric cards and recent enquiries were rendered regardless of the logged-in staff role. Those UI/data surfaces now use the same server-side permission checks.

### 4. Phase 148 collation failure
Legacy fields use mixed `utf8mb4_general_ci` / `utf8mb4_unicode_ci`. Text backfill joins compared those implicit collations directly, causing MySQL error 1267. The migration now explicitly normalizes both sides of course/batch text comparisons to `utf8mb4_unicode_ci` and sets the connection collation.

### 5. Mobile topbar was intentionally hidden by older responsive CSS
Phase 130 hid `.wf127-topbar` below 980px, Phase 138 only partially reconstructed it, and later learning-page CSS hid it on selected pages. Phase 150 adds the final last-loaded mobile rule and restores all four topbar functions in a compact two-row layout.

## Security rules now enforced

- Exactly one active Super Admin owner is expected.
- First-run `admin/setup.php` is the only normal place that creates the Super Admin role.
- Admin Users cannot create a second Super Admin.
- Protected owner cannot be demoted or deactivated through Admin Users.
- Staff/custom roles cannot receive `admins.manage`, including forged POST requests.
- Admin Users / Roles / Audit are protected-owner only.
- Extra/legacy Super Admin login is blocked until the owner-lock migration normalizes the database.
- Role permission changes invalidate all affected staff sessions.
- Missing RBAC schema no longer grants blanket access.

## Database repair

For a database where the old Phase 148 import stopped with error 1267, use the new combined upgrade file after taking a backup:

`Well_Fare_Phase150_Required_DB_Upgrade.sql`

It reruns the corrected idempotent Phase 148 migration and then applies the Phase 150 owner lock.

## Testing limitation

Static source validation passed. A MySQL/MariaDB server is not available in this build environment, so the real migration must still be executed on the user's XAMPP/staging database and verified through Admin > System Check.
