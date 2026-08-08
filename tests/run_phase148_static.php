<?php
require_once __DIR__ . '/../includes/functions.php';

$failures = [];
$ok = static function(bool $condition, string $label) use (&$failures): void {
    echo ($condition ? "PASS" : "FAIL") . " - {$label}\n";
    if (!$condition) $failures[] = $label;
};

$ok(admin_password_error('StrongAdmin2026') === '', 'admin password accepts strong value');
$ok(admin_password_error('short1') !== '', 'admin password rejects short value');
$ok(student_password_error('student123') === '', 'student password accepts 8+ characters');
$ok(student_password_error(str_repeat('x',129)) !== '', 'student password rejects values over 128 characters');
$ok(safe_local_redirect('student-dashboard.php?x=1') === 'student-dashboard.php?x=1', 'local redirect accepts safe local URL');
$ok(safe_local_redirect('https://evil.example/', 'student-dashboard.php') === 'student-dashboard.php', 'local redirect rejects external URL');
$ok(admin_mfa_code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 59) === '287082', 'RFC6238 SHA1 6-digit compatibility vector');

$config = file_get_contents(__DIR__ . '/../includes/config.php');
$ok(strpos($config, "'name' => '',\n    'user' => '',\n    'pass' => '',") !== false, 'live database credentials have no source-code fallback');
$canonical = file_get_contents(__DIR__ . '/../sql/wellfare_english_complete.sql');
$ok(!preg_match('/INSERT\s+INTO\s+`?admins`?/i', $canonical), 'canonical SQL ships no fixed administrator account');
$ok(strpos($canonical, 'password_hash`=\'$2y$12$DHCToBguTMZptJEHcBMUGuoAErIOUDX45NhgtxRT6i9LPRaojvz5u\'') !== false && strpos($canonical, "`published`='No'") !== false, 'upgrade migration disables the exact untouched legacy admin seed');
$ok(substr_count($canonical, 'CALL wf_add_fk(') >= 40, 'canonical SQL contains relationship constraints');
$ok(strpos($canonical, 'admission_payments') !== false, 'payment ledger is present');
$ok(strpos($canonical, 'student_enrollments') !== false && strpos($canonical, 'student_batch_memberships') !== false, 'student enrollment lifecycle tables are present');
$ok(strpos(file_get_contents(__DIR__ . '/../includes/functions.php'), "return 'private/materials/' . \$name;") !== false, 'new learning uploads use private storage');
$ok(strpos(file_get_contents(__DIR__ . '/../admin/enquiries.php'), 'DELETE FROM enquiries') === false, 'enquiries are not hard-deleted from admin workflow');
$authSource = file_get_contents(__DIR__ . '/../student-auth.php');
$ok(strpos($authSource, '$level = \'Zero Level\'') !== false && strpos($authSource, "'Unverified','self'") !== false, 'self-registration is unverified and cannot self-select official learning level');
$backendSource = file_get_contents(__DIR__ . '/../includes/phase148_backend.php');
$ok(strpos($backendSource, "'toggle-publish.php'=>'content.manage'") === false, 'publish toggle defers to table-specific permission instead of content-only access');
$ok(strpos($canonical, 'material_access_logs') !== false && strpos(file_get_contents(__DIR__ . '/../material-file.php'), 'INSERT INTO material_access_logs') !== false, 'private learning-file access is audited');
$ok(strpos($backendSource, "Institute-verified mobile number matched an existing admission.") !== false && strpos($backendSource, "!== 'Verified') return") !== false, 'unverified self-registration cannot automatically claim an admission lifecycle');
$studentViewSource = file_get_contents(__DIR__ . '/../admin/student-view.php');
$ok(strpos($studentViewSource, 'reset_reason') !== false && strpos($studentViewSource, 'identity_verification_note') !== false, 'admin password reset and manual mobile verification require an audit reason');

if ($failures) {
    fwrite(STDERR, "\n" . count($failures) . " Phase 148 static check(s) failed.\n");
    exit(1);
}
echo "\nAll Phase 148 static checks passed.\n";
