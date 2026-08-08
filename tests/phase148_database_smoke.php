<?php
require_once __DIR__ . '/../includes/functions.php';

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only.\n"); }
$required = [
    'schema_migrations','admin_roles','admin_permissions','admin_role_permissions','admin_audit_events','security_rate_limits',
    'student_enrollments','student_batch_memberships','admission_payments','data_integrity_orphans','material_access_logs'
];
$failed = [];
foreach ($required as $table) {
    $exists = table_exists($table);
    echo ($exists ? 'PASS' : 'FAIL') . " - table {$table}\n";
    if (!$exists) $failed[] = $table;
}
$fk = (int)db()->query("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE()")->fetchColumn();
echo ($fk >= 40 ? 'PASS' : 'FAIL') . " - foreign keys detected: {$fk}\n";
if ($fk < 40) $failed[] = 'foreign keys';
$version = table_exists('schema_migrations') ? (string)(db()->query("SELECT version FROM schema_migrations WHERE version='20260807_001_phase148' LIMIT 1")->fetchColumn() ?: '') : '';
echo ($version !== '' ? 'PASS' : 'FAIL') . " - Phase 148 migration registry\n";
if ($version === '') $failed[] = 'migration registry';
foreach ([['students','identity_status'],['admins','role_id'],['admissions','student_id'],['admissions','course_id'],['admissions','batch_id']] as [$table,$column]) {
    $exists = column_exists($table,$column);
    echo ($exists ? 'PASS' : 'FAIL') . " - column {$table}.{$column}\n";
    if (!$exists) $failed[] = "{$table}.{$column}";
}
$privateDir = material_private_root() . '/materials';
if (!is_dir($privateDir)) @mkdir($privateDir, 0750, true);
echo (is_writable($privateDir) ? 'PASS' : 'FAIL') . " - private material storage writable\n";
if (!is_writable($privateDir)) $failed[] = 'private storage';

exit($failed ? 1 : 0);
