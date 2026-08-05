<?php require_once __DIR__ . '/_header.php';
ensure_core_schema_columns();
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();
$checks = [];
$schemaUpdatesEnabled = defined('APP_ALLOW_SCHEMA_UPDATES') ? APP_ALLOW_SCHEMA_UPDATES : true;
$addCheck = function(string $label, bool $ok, string $note = '') use (&$checks) {
    $checks[] = ['label' => $label, 'ok' => $ok, 'note' => $note];
};
foreach (['current_level','preferred_batch','lead_source','enquiry_status','lead_priority','follow_up_date','last_contacted_at','admin_note','updated_at'] as $column) {
    $addCheck('Enquiries column: ' . $column, column_exists('enquiries', $column), 'Required for admission form and enquiry workflow.');
}
foreach (['site_settings','practice_categories','practice_lessons','practice_questions','translation_pairs','material_collections','material_practice_attempts','hero_banners','nav_menus','weekly_tests','weekly_test_questions','weekly_test_attempts','weekly_test_answers'] as $table) {
    $addCheck('Table: ' . $table, table_exists($table), 'Required for dynamic CMS/practice modules.');
}
$addCheck('PHP cURL extension', function_exists('curl_init'), 'Required for optional AI/OpenAI and online translator calls. Local practice works without it.');
$addCheck('File uploads enabled', (bool)ini_get('file_uploads'), 'Required for dynamic logo, favicon, gallery and material uploads.');
$addCheck('Student practice tracking column: student_id', column_exists('material_practice_attempts', 'student_id'), 'Required for student dashboard progress and revision history.');
foreach (['access_token','result_token','question_snapshot','submission_reason','last_saved_at'] as $column) {
    $addCheck('Weekly attempt security column: ' . $column, column_exists('weekly_test_attempts', $column), 'Required for secure results, stable questions and server-side submissions.');
}
$addCheck('Runtime schema updates', !$schemaUpdatesEnabled, $schemaUpdatesEnabled ? 'Enabled for upgrade. Turn APP_ALLOW_SCHEMA_UPDATES=false after all checks are green.' : 'Disabled for normal production traffic.');
$addCheck('Brand logo dynamic setting', app_setting('site_logo', '') !== '' || app_setting('brand_short', '') !== '', 'Logo image optional; text mark fallback is active.');
?>
<div class="admin-top"><div><h1>System Check & Repair</h1><p>This page repairs missing database columns/tables and checks whether the project is ready on XAMPP/server.</p></div><div class="admin-actions"><a class="btn btn-soft" href="../admission.php" target="_blank">Test Admission</a><a class="btn btn-soft" href="../spoken-materials.php" target="_blank">Test Spoken Practice</a></div></div>
<?php if ($schemaUpdatesEnabled): ?><div class="alert alert-success">Upgrade mode is enabled. Repair helpers ran; after every check is green, set <code>APP_ALLOW_SCHEMA_UPDATES=false</code> in <code>.env</code>.</div><?php else: ?><div class="alert alert-info">Production mode is active. Runtime database changes are disabled.</div><?php endif; ?>
<div class="panel-card table-wrap"><table><thead><tr><th>Check</th><th>Status</th><th>Note</th></tr></thead><tbody><?php foreach($checks as $check): ?><tr><td><?= e($check['label']) ?></td><td><span class="badge <?= $check['ok'] ? 'badge-yes' : 'badge-no' ?>"><?= $check['ok'] ? 'OK' : 'Needs attention' ?></span></td><td><?= e($check['note']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="panel-card"><h2>Important</h2><p>If any database column is still missing, your MySQL user may not have ALTER permission. In XAMPP root user normally has permission, so opening this page once should fix it. For this Phase 122 upgrade, keep <code>APP_ALLOW_SCHEMA_UPDATES=true</code>, open this page once, verify all rows, then change it to <code>false</code>. Keep a database backup before upgrading.</p></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
