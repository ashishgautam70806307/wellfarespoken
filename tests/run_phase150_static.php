<?php
$root = dirname(__DIR__);
$fail = 0;
$ok = static function(bool $value, string $label) use (&$fail): void {
    echo ($value ? 'PASS' : 'FAIL') . ' - ' . $label . PHP_EOL;
    if (!$value) $fail++;
};
$backend = file_get_contents($root . '/includes/phase148_backend.php');
$users = file_get_contents($root . '/admin/admin-users.php');
$roles = file_get_contents($root . '/admin/roles.php');
$dashboard = file_get_contents($root . '/admin/dashboard.php');
$login = file_get_contents($root . '/admin/login.php');
$migration = file_get_contents($root . '/sql/phase148_critical_backend_hardening.sql');
$ownerMigration = file_get_contents($root . '/sql/phase150_admin_owner_lock.sql');
$css = file_get_contents($root . '/assets/css/phase150-security-ui.css');
$sw = file_get_contents($root . '/sw.js');

$ok(strpos($backend, "return in_array(\$permission, ['dashboard.view','system.manage'], true)") !== false, 'RBAC missing state fails closed except Dashboard/System Check');
$ok(strpos($backend, 'function admin_primary_owner_id()') !== false && strpos($backend, 'function admin_is_primary_owner') !== false, 'Protected primary-owner helpers exist');
$ok(strpos($backend, "if (\$permission === 'admins.manage') return false;") !== false, 'Staff roles cannot use admins.manage at runtime');
$ok(strpos($users, "role_key<>'super_admin'") !== false && strpos($users, 'A second Super Admin cannot be created.') !== false, 'Admin Users cannot assign or create a second Super Admin');
$ok(strpos($roles, 'admin_assert_primary_owner();') !== false && strpos($roles, "ownerOnlyKeys = ['admins.manage']") !== false, 'Role management is owner-only and filters administrator-management permission');
$ok(strpos($roles, 'admin_invalidate_sessions') !== false, 'Role changes invalidate affected staff sessions');
$ok(strpos($dashboard, "admin_can('enquiries.manage')") !== false && strpos($dashboard, "admin_can('tests.manage')") !== false && strpos($dashboard, 'Security & Access Control') !== false, 'Dashboard data/actions follow permissions and show access status');
$ok(strpos($login, 'admin.login_blocked_duplicate_super') !== false, 'Duplicate/legacy Super Admin login is blocked');
$ok(strpos($migration, 'Existing administrators are promoted to Super Admin') === false && strpos($migration, 'exactly one protected Super Admin owner') !== false, 'Old mass-Super-Admin upgrade logic is removed');
$ok(substr_count($migration, 'COLLATE utf8mb4_unicode_ci') >= 10, 'Legacy migration comparisons explicitly normalize collation');
$ok(strpos($ownerMigration, "p.permission_key='admins.manage'") !== false && strpos($ownerMigration, "r.role_key<>'super_admin'") !== false, 'Phase 150 DB migration removes admin-management permission from staff roles');
$ok(strpos($css, '.wf127-topbar-place') !== false && strpos($css, '.wf129-institute-link') !== false && strpos($css, '.wf127-topbar-phone') !== false && strpos($css, '.wf127-announcement') !== false, 'Mobile topbar restores location, announcement, institute login and phone access');
$ok(preg_match('/wellfare-spoken-static-v(1[5-9][0-9]|[2-9][0-9]{2,})/', $sw) === 1 && strpos($sw, 'phase150-security-ui.min.css') !== false, 'Service worker cache and Phase 150 CSS are updated');

if ($fail) {
    echo PHP_EOL . $fail . ' Phase 150 static check(s) failed.' . PHP_EOL;
    exit(1);
}
echo PHP_EOL . 'All Phase 150 static checks passed.' . PHP_EOL;
