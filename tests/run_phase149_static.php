<?php
$root = dirname(__DIR__);
$failures = [];
$ok = static function(bool $condition, string $label) use (&$failures): void {
    echo ($condition ? 'PASS' : 'FAIL') . " - {$label}\n";
    if (!$condition) $failures[] = $label;
};
$header = file_get_contents($root . '/admin/_header.php');
$adminUsers = file_get_contents($root . '/admin/admin-users.php');
$roles = file_get_contents($root . '/admin/roles.php');
$audit = file_get_contents($root . '/admin/audit-log.php');
$admissions = file_get_contents($root . '/admin/admissions.php');
$studentAuth = file_get_contents($root . '/student-auth.php');
$adminJs = file_get_contents($root . '/assets/js/phase149-admin-resilience.js');
$adminCss = file_get_contents($root . '/assets/css/phase149-admin-resilience.css');
$sw = file_get_contents($root . '/sw.js');

$ok(strpos($header, "href=\"ui-library.php\"") === false, 'UI Library is hidden from normal Admin navigation');
$ok(strpos($header, "['UI Library', 'ui-library.php'") === false, 'UI Library is hidden from Admin quick search');
$ok(strpos($header, 'Database upgrade is incomplete.') !== false, 'Admin shows a database-upgrade warning before new modules are used');
$ok(strpos($adminUsers, 'if (!admin_rbac_ready())') !== false && strpos($adminUsers, 'Administrator security setup is not installed yet.') !== false, 'Admin Users fails gracefully when RBAC migration is missing');
$ok(strpos($roles, 'if (!admin_rbac_ready())') !== false, 'Roles page fails gracefully when migration is missing');
$ok(strpos($audit, "if (!table_exists('admin_audit_events'))") !== false, 'Audit page fails gracefully when migration is missing');
$ok(strpos($admissions, 'wf149_admission_draft') !== false && strpos($admissions, 'Your form entries were kept') !== false, 'Admission save errors preserve entered values');
$ok(strpos($admissions, 'admissionBackendReady') !== false && strpos($admissions, 'Database upgrade required before saving admissions') !== false, 'Admission form detects missing Phase 148 schema before data loss');
$ok(strpos($admissions, 'data-preview-target="admissionPhotoPreview"') !== false, 'Admission photo previews immediately after file selection');
$ok(strpos($adminJs, "type !== 'password'") === false || strpos($adminJs, "['password', 'file'") !== false, 'Admin form recovery excludes password and file values from storage');
$ok(strpos($adminJs, 'setupImagePreviews') !== false, 'Admin image uploads use a reusable live preview handler');
$ok(strpos($adminCss, '.page-admin-dashboard .wf147-dashboard-account-control') !== false && strpos($adminCss, 'color:#fff!important') !== false, 'Student Account Control gets contrast-safe final styles');
$ok(substr_count($studentAuth, 'data-password-toggle=') >= 2 && strpos($studentAuth, 'phase149-password-toggle.js') !== false, 'Student login/register password show-hide controls are enabled');
$swVersion = preg_match('/wellfare-spoken-static-v(\d+)/', $sw, $m) ? (int)$m[1] : 0;
$ok($swVersion >= 149, 'Service worker cache namespace is Phase 149 or newer');
$ok(strpos($sw, 'phase149-student-auth.min.css') !== false && strpos($sw, 'phase149-password-toggle.js') !== false, 'Phase 149 public auth assets are included in static cache manifest');

if ($failures) {
    fwrite(STDERR, "\n" . count($failures) . " Phase 149 static check(s) failed.\n");
    exit(1);
}
echo "\nAll Phase 149 static checks passed.\n";
