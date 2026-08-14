<?php require_once __DIR__ . '/_header.php';
ensure_core_schema_columns();
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();
batch_ensure_schema();
student_account_ensure_schema();
$checks = [];
$schemaUpdatesEnabled = defined('APP_ALLOW_SCHEMA_UPDATES') ? APP_ALLOW_SCHEMA_UPDATES : true;
$addCheck = function(string $label, bool $ok, string $note = '') use (&$checks) {
    $checks[] = ['label' => $label, 'ok' => $ok, 'note' => $note];
};
$addCheck('Runtime environment', in_array(APP_RUNTIME_ENV, ['local','live'], true), 'Detected: ' . APP_RUNTIME_ENV . '; database mode: ' . DB_CONNECTION_MODE . '.');
$addCheck('Database import file', is_file(dirname(__DIR__) . '/sql/wellfare_english_complete.sql'), 'Single canonical database file.');
$weeklySchemaStatus = weekly_test_schema_status();
$addCheck('Weekly Test complete schema', (bool)($weeklySchemaStatus['ready'] ?? false), ($weeklySchemaStatus['ready'] ?? false) ? 'All required test tables and columns are available.' : ('Missing: ' . implode(', ', (array)($weeklySchemaStatus['missing'] ?? []))));
$addCheck('Batch course link column', column_exists('batch_timings', 'course_id'), column_exists('batch_timings', 'course_id') ? 'Batch Management can save the selected course normally.' : 'Missing batch_timings.course_id. Apply the latest migration if automatic schema updates are disabled.');
foreach (['current_level','preferred_batch','lead_source','enquiry_status','lead_priority','follow_up_date','last_contacted_at','admin_note','updated_at'] as $column) {
    $addCheck('Enquiries column: ' . $column, column_exists('enquiries', $column), 'Required for admission form and enquiry workflow.');
}
foreach (['desktop_image_url','mobile_image_url','show_content','content_position','overlay_strength'] as $column) {
    $addCheck('Responsive banner column: ' . $column, column_exists('hero_banners', $column), 'Included in the unified database import.');
}
foreach (['schema_migrations','admins','admin_roles','admin_permissions','admin_role_permissions','admin_audit_events','security_rate_limits','site_settings','courses','course_variants','testimonials','videos','enquiries','faculty_members','gallery_images','faqs','batch_timings','content_blocks','form_options','nav_menus','hero_banners','students','student_account_events','student_activity_logs','admissions','student_enrollments','student_batch_memberships','admission_payments','data_integrity_orphans','practice_categories','practice_lessons','practice_questions','practice_common_mistakes','practice_attempts','practice_settings','practice_ai_logs','material_collections','material_assets','material_access_logs','material_units','translation_pairs','material_practice_attempts','material_settings','weekly_tests','weekly_test_questions','weekly_test_attempts','weekly_test_answers','weekly_test_winners','roadmap_groups','roadmap_units','roadmap_items','student_roadmap_progress'] as $table) {
    $addCheck('Table: ' . $table, table_exists($table), 'Required for dynamic CMS/practice modules.');
}
$addCheck('PHP cURL extension', function_exists('curl_init'), 'Required for optional AI/OpenAI and online translator calls. Local practice works without it.');
$addCheck('File uploads enabled', (bool)ini_get('file_uploads'), 'Required for dynamic logo, favicon, gallery and material uploads.');
$addCheck('Student practice tracking column: student_id', column_exists('material_practice_attempts', 'student_id'), 'Required for student dashboard progress and revision history.');
$addCheck('Student account session version', column_exists('students', 'auth_version'), 'Recommended for immediate force sign-out after password or access changes. Fallback protection remains available.');
$addCheck('Student password change timestamp', column_exists('students', 'password_changed_at'), 'Required for accurate password reset history on the account screen.');
$addCheck('Student account audit table', table_exists('student_account_events'), 'Stores admin password resets, access changes and force sign-out events.');
foreach (['access_token','result_token','question_snapshot','submission_reason','last_saved_at'] as $column) {
    $addCheck('Weekly attempt security column: ' . $column, column_exists('weekly_test_attempts', $column), 'Required for secure results, stable questions and server-side submissions.');
}
$addCheck('Student mobile identity marker', column_exists('students', 'identity_status'), 'Self-registration without OTP is supported as an Unverified learning account; staff may verify the mobile later.');
$addCheck('Registration mode', in_array(defined('STUDENT_REGISTRATION_MODE') ? STUDENT_REGISTRATION_MODE : 'open', ['open','approval'], true), 'Current mode: ' . (defined('STUDENT_REGISTRATION_MODE') ? STUDENT_REGISTRATION_MODE : 'open') . '.');
$legacySeedActive = 0;
try {
    $legacy = db()->prepare("SELECT COUNT(*) FROM admins WHERE LOWER(email)='admin@wellfare.local' AND password_hash=? AND published='Yes'");
    $legacy->execute(['$2y$12$DHCToBguTMZptJEHcBMUGuoAErIOUDX45NhgtxRT6i9LPRaojvz5u']);
    $legacySeedActive = (int)$legacy->fetchColumn();
} catch (Throwable $e) { $legacySeedActive = 0; }
$addCheck('Predictable legacy admin disabled', $legacySeedActive === 0, $legacySeedActive === 0 ? 'No active untouched Phase 147 default admin credential detected.' : 'Critical: disable the untouched legacy admin and create/reset a private owner credential.');
$addCheck('Admin RBAC', admin_rbac_ready(), 'Roles and permission enforcement require the Phase 148 migration.');
$activeSuperAdmins = 0;
try {
    if (admin_rbac_ready()) $activeSuperAdmins = (int)db()->query("SELECT COUNT(*) FROM admins a JOIN admin_roles r ON r.id=a.role_id WHERE a.published='Yes' AND r.role_key='super_admin'")->fetchColumn();
} catch (Throwable $e) { $activeSuperAdmins = 0; }
$addCheck('Single protected Super Admin owner', admin_rbac_ready() && $activeSuperAdmins === 1, admin_rbac_ready() ? ($activeSuperAdmins . ' active Super Admin account(s) detected. Phase 150 requires exactly one.') : 'RBAC migration must be installed first.');
$ownerOnlyAdminPermissionLeaks = 0;
try {
    if (admin_rbac_ready()) $ownerOnlyAdminPermissionLeaks = (int)db()->query("SELECT COUNT(*) FROM admin_role_permissions rp JOIN admin_permissions p ON p.id=rp.permission_id JOIN admin_roles r ON r.id=rp.role_id WHERE p.permission_key='admins.manage' AND r.role_key<>'super_admin'")->fetchColumn();
} catch (Throwable $e) { $ownerOnlyAdminPermissionLeaks = 0; }
$addCheck('Admin-management permission owner-only', admin_rbac_ready() && $ownerOnlyAdminPermissionLeaks === 0, $ownerOnlyAdminPermissionLeaks === 0 ? 'No staff/custom role can manage administrator accounts.' : ($ownerOnlyAdminPermissionLeaks . ' non-owner role assignment(s) must be removed by the Phase 150 migration.'));
$addCheck('Admin audit log', table_exists('admin_audit_events'), 'Sensitive and generic admin POST activity is recorded append-only.');
$addCheck('Database-backed rate limits', table_exists('security_rate_limits'), 'Authentication rate limiting uses the database with a fail-closed file fallback.');
$addCheck('Payment ledger', table_exists('admission_payments'), 'Admission totals and payment status are derived from immutable ledger entries.');
$addCheck('Enrollment lifecycle', table_exists('student_enrollments') && table_exists('student_batch_memberships'), 'Links Student → Admission → Course → Batch without overwriting history.');
$privateStorage = material_private_root() . '/materials';
if (!is_dir($privateStorage)) @mkdir($privateStorage, 0750, true);
$addCheck('Private learning-file storage', is_dir($privateStorage) && is_writable($privateStorage), $privateStorage);
$docRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? '')) ?: '';
$privateReal = realpath(material_private_root()) ?: material_private_root();
$privateOutsideWeb = $docRoot === '' || !str_starts_with(str_replace('\\','/',$privateReal), rtrim(str_replace('\\','/',$docRoot),'/') . '/');
$addCheck('Private storage outside web root', APP_RUNTIME_ENV !== 'live' || $privateOutsideWeb, APP_RUNTIME_ENV !== 'live' ? 'Local XAMPP may use the protected project storage folder.' : ($privateOutsideWeb ? 'Private learning files are stored outside the public document root.' : 'Set PRIVATE_STORAGE_PATH in .env to a writable directory outside public_html/document root.'));
$fkCount = 0;
try {
    $fkCount = (int)db()->query("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE()")->fetchColumn();
} catch (Throwable $e) { $fkCount = 0; }
$addCheck('Database foreign keys', $fkCount >= 40, $fkCount . ' foreign-key constraints detected. Phase 148 expects the relationship-hardening migration to be fully applied.');
$liveSecretsOk = APP_RUNTIME_ENV !== 'live' || (defined('DB_NAME') && DB_NAME !== '' && defined('DB_USER') && DB_USER !== '');
$addCheck('Live database secrets configured', $liveSecretsOk, APP_RUNTIME_ENV === 'live' ? 'Live credentials must come from environment/.env; no project source fallback is shipped.' : 'Local runtime does not require live credentials.');
$addCheck('Runtime schema updates', !$schemaUpdatesEnabled, $schemaUpdatesEnabled ? 'Enabled for upgrade. Turn APP_ALLOW_SCHEMA_UPDATES=false after all checks are green.' : 'Disabled for normal production traffic.');
$addCheck('Brand logo dynamic setting', app_setting('site_logo', '') !== '' || app_setting('brand_short', '') !== '', 'Logo image optional; text mark fallback is active.');
?>
<div class="admin-top"><div><h1>System Check & Repair</h1><p>This page verifies the unified database and checks whether the project is ready on XAMPP/server.</p></div><div class="admin-actions"><a class="btn btn-soft" href="../admission.php" target="_blank">Test Admission</a><a class="btn btn-soft" href="../spoken-materials.php" target="_blank">Test Spoken Practice</a></div></div>
<?php if ($schemaUpdatesEnabled): ?><div class="alert alert-success">Upgrade mode is enabled. Prefer importing sql/wellfare_english_complete.sql; after every check is green, set <code>APP_ALLOW_SCHEMA_UPDATES=false</code> in <code>.env</code>.</div><?php else: ?><div class="alert alert-info">Production mode is active. Runtime database changes are disabled.</div><?php endif; ?>
<div class="panel-card table-wrap"><table><thead><tr><th>Check</th><th>Status</th><th>Note</th></tr></thead><tbody><?php foreach($checks as $check): ?><tr><td><?= e($check['label']) ?></td><td><span class="badge <?= $check['ok'] ? 'badge-yes' : 'badge-no' ?>"><?= $check['ok'] ? 'OK' : 'Needs attention' ?></span></td><td><?= e($check['note']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="panel-card"><h2>Important</h2><p>For an existing pre-Phase-148 database, rerun the corrected <code>sql/phase148_critical_backend_hardening.sql</code> first, then run <code>sql/phase150_admin_owner_lock.sql</code>. Fresh installs may use <code>sql/wellfare_english_complete.sql</code>. New installations create the first admin securely through admin/setup.php. Keep <code>APP_ALLOW_SCHEMA_UPDATES=false</code> for normal production traffic. Always take a database backup before importing into an existing database.</p></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
