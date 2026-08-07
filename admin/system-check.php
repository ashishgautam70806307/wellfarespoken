<?php require_once __DIR__ . '/_header.php';
ensure_core_schema_columns();
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();
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
foreach (['current_level','preferred_batch','lead_source','enquiry_status','lead_priority','follow_up_date','last_contacted_at','admin_note','updated_at'] as $column) {
    $addCheck('Enquiries column: ' . $column, column_exists('enquiries', $column), 'Required for admission form and enquiry workflow.');
}
foreach (['desktop_image_url','mobile_image_url','show_content','content_position','overlay_strength'] as $column) {
    $addCheck('Responsive banner column: ' . $column, column_exists('hero_banners', $column), 'Included in the unified database import.');
}
foreach (['admins','site_settings','courses','course_variants','testimonials','videos','enquiries','faculty_members','gallery_images','faqs','batch_timings','content_blocks','form_options','nav_menus','hero_banners','students','student_account_events','student_activity_logs','admissions','practice_categories','practice_lessons','practice_questions','practice_common_mistakes','practice_attempts','practice_settings','practice_ai_logs','material_collections','material_assets','material_units','translation_pairs','material_practice_attempts','material_settings','weekly_tests','weekly_test_questions','weekly_test_attempts','weekly_test_answers','weekly_test_winners','roadmap_groups','roadmap_units','roadmap_items','student_roadmap_progress'] as $table) {
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
$addCheck('Runtime schema updates', !$schemaUpdatesEnabled, $schemaUpdatesEnabled ? 'Enabled for upgrade. Turn APP_ALLOW_SCHEMA_UPDATES=false after all checks are green.' : 'Disabled for normal production traffic.');
$addCheck('Brand logo dynamic setting', app_setting('site_logo', '') !== '' || app_setting('brand_short', '') !== '', 'Logo image optional; text mark fallback is active.');
?>
<div class="admin-top"><div><h1>System Check & Repair</h1><p>This page verifies the unified database and checks whether the project is ready on XAMPP/server.</p></div><div class="admin-actions"><a class="btn btn-soft" href="../admission.php" target="_blank">Test Admission</a><a class="btn btn-soft" href="../spoken-materials.php" target="_blank">Test Spoken Practice</a></div></div>
<?php if ($schemaUpdatesEnabled): ?><div class="alert alert-success">Upgrade mode is enabled. Prefer importing sql/wellfare_english_complete.sql; after every check is green, set <code>APP_ALLOW_SCHEMA_UPDATES=false</code> in <code>.env</code>.</div><?php else: ?><div class="alert alert-info">Production mode is active. Runtime database changes are disabled.</div><?php endif; ?>
<div class="panel-card table-wrap"><table><thead><tr><th>Check</th><th>Status</th><th>Note</th></tr></thead><tbody><?php foreach($checks as $check): ?><tr><td><?= e($check['label']) ?></td><td><span class="badge <?= $check['ok'] ? 'badge-yes' : 'badge-no' ?>"><?= $check['ok'] ? 'OK' : 'Needs attention' ?></span></td><td><?= e($check['note']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="panel-card"><h2>Important</h2><p>Import <code>sql/wellfare_english_complete.sql</code> once. It contains all 41 tables, all later columns and default module data. Keep <code>APP_ALLOW_SCHEMA_UPDATES=false</code> for normal production traffic. Always take a database backup before importing into an existing database.</p></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
