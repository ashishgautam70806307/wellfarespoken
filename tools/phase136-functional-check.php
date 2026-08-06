<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once dirname(__DIR__) . '/includes/functions.php';

$rows = [];
$failures = 0;
$warnings = 0;

function p136_check(string $name, string $status, string $detail = ''): void
{
    global $rows, $failures, $warnings;
    $status = strtoupper($status);
    if ($status === 'FAIL') $failures++;
    if ($status === 'WARN') $warnings++;
    $rows[] = [$name, $status, $detail];
}

function p136_scalar(string $sql, array $params = []): mixed
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $value = $statement->fetchColumn();
    return $value === false ? null : $value;
}

p136_check('Runtime environment', 'PASS', APP_RUNTIME_ENV . ' / ' . APP_ENV);
p136_check('Database profile', 'PASS', DB_HOST . ':' . DB_PORT . '/' . DB_NAME . ' as ' . DB_USER);
p136_check('Runtime schema updates', APP_ALLOW_SCHEMA_UPDATES ? 'WARN' : 'PASS', APP_ALLOW_SCHEMA_UPDATES ? 'Enabled; disable after import.' : 'Disabled');
p136_check('Application debug', (APP_RUNTIME_ENV === 'live' && APP_DEBUG) ? 'FAIL' : 'PASS', APP_DEBUG ? 'Enabled' : 'Disabled');
p136_check('UTF-8 compatibility', function_exists('mb_strlen') && function_exists('mb_substr') && function_exists('mb_strimwidth') ? 'PASS' : 'FAIL', extension_loaded('mbstring') ? 'mbstring' : 'compatibility fallback');

$mysqlDriver = in_array('mysql', PDO::getAvailableDrivers(), true);
p136_check('PDO MySQL driver', $mysqlDriver ? 'PASS' : 'FAIL', implode(', ', PDO::getAvailableDrivers()) ?: 'No PDO drivers');
$connected = false;
if ($mysqlDriver) {
    try {
        db();
        $connected = true;
        p136_check('Database connection', 'PASS');
    } catch (Throwable $exception) {
        p136_check('Database connection', 'FAIL', 'Connection failed. Verify local/live .env settings.');
    }
} else {
    p136_check('Database connection', 'FAIL', 'pdo_mysql is not installed in this PHP runtime.');
}

if ($connected) {
    $expectedTables = [
        'admins','site_settings','courses','course_variants','testimonials','videos','enquiries','faculty_members',
        'gallery_images','faqs','hero_banners','content_blocks','nav_menus','form_options','batch_timings','students','student_activity_logs',
        'admissions','practice_categories','practice_lessons','practice_questions','practice_attempts','practice_common_mistakes',
        'practice_settings','practice_ai_logs','material_collections','material_assets','material_units','translation_pairs',
        'material_practice_attempts','material_settings','weekly_tests','weekly_test_questions','weekly_test_attempts',
        'weekly_test_answers','weekly_test_winners','roadmap_groups','roadmap_units','roadmap_items','student_roadmap_progress'
    ];
    $missing = array_values(array_filter($expectedTables, static fn(string $table): bool => !table_exists($table)));
    p136_check('Canonical database tables', $missing ? 'FAIL' : 'PASS', $missing ? implode(', ', $missing) : count($expectedTables) . ' tables available');

    $requiredColumns = [
        'hero_banners' => ['desktop_image_url','mobile_image_url','show_content','content_position','overlay_strength'],
        'students' => ['full_name','phone','password_hash','published','status_deleted','last_login_at'],
        'enquiries' => ['course_interest','current_level','preferred_batch','lead_source','enquiry_status','ip_address'],
        'weekly_test_attempts' => ['access_token','result_token','expires_at','question_order','question_snapshot','last_saved_at','status_deleted'],
        'weekly_test_answers' => ['attempt_id','question_id','answer_text','is_correct','marks_awarded'],
        'roadmap_units' => ['group_id','level','reward_points','unlock_after_unit_id','status_deleted'],
        'student_roadmap_progress' => ['student_id','unit_id','status','score','completed_at'],
        'translation_pairs' => ['hindi_text','english_text','answer_match_mode','teacher_hint','status_deleted'],
    ];
    foreach ($requiredColumns as $table => $columns) {
        $missingColumns = array_values(array_filter($columns, static fn(string $column): bool => !column_exists($table, $column)));
        p136_check($table . ' columns', $missingColumns ? 'FAIL' : 'PASS', $missingColumns ? implode(', ', $missingColumns) : count($columns) . ' required columns');
    }

    $weeklySchema = weekly_test_schema_status();
    p136_check('Weekly Test schema', ($weeklySchema['ready'] ?? false) ? 'PASS' : 'FAIL', implode(', ', $weeklySchema['missing'] ?? []));
    foreach (['basic','previous','upcoming'] as $type) {
        try {
            $tests = weekly_test_fetch_tests($type);
            $questionCount = array_sum(array_map(static fn(array $row): int => (int)($row['question_count'] ?? 0), $tests));
            $ready = count(array_filter($tests, static fn(array $row): bool => (int)($row['ready_now'] ?? 0) === 1));
            p136_check(ucfirst($type) . ' test mapping', !$tests ? 'WARN' : ($questionCount > 0 ? 'PASS' : 'FAIL'), count($tests) . ' paper(s), ' . $ready . ' ready, ' . $questionCount . ' question(s)');
        } catch (Throwable $exception) {
            p136_check(ucfirst($type) . ' test mapping', 'FAIL', 'Query failed.');
        }
    }

    $moduleQueries = [
        'Published courses' => "SELECT COUNT(*) FROM courses WHERE published='Yes'",
        'Published batches' => "SELECT COUNT(*) FROM batch_timings WHERE published='Yes'",
        'Published FAQs' => "SELECT COUNT(*) FROM faqs WHERE published='Yes'",
        'Published reviews' => "SELECT COUNT(*) FROM testimonials WHERE published='Yes'",
        'Published gallery images' => "SELECT COUNT(*) FROM gallery_images WHERE published='Yes'",
        'Published desktop banners' => "SELECT COUNT(*) FROM hero_banners WHERE page_key='home' AND published='Yes' AND COALESCE(NULLIF(desktop_image_url,''),NULLIF(image_url,'')) IS NOT NULL",
        'Published mobile banners' => "SELECT COUNT(*) FROM hero_banners WHERE page_key='home' AND published='Yes' AND NULLIF(mobile_image_url,'') IS NOT NULL",
        'Roadmap units' => "SELECT COUNT(*) FROM roadmap_units WHERE published='Yes' AND status_deleted=0",
        'Material pairs' => "SELECT COUNT(*) FROM translation_pairs WHERE published='Yes' AND status_deleted=0",
    ];
    foreach ($moduleQueries as $label => $sql) {
        try {
            $count = (int)p136_scalar($sql);
            p136_check($label, $count > 0 ? 'PASS' : 'WARN', (string)$count);
        } catch (Throwable $exception) {
            p136_check($label, 'FAIL', 'Query failed.');
        }
    }

    foreach (['facebook_url','instagram_url','youtube_url','linkedin_url'] as $settingKey) {
        try {
            $value = trim((string)p136_scalar('SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1', [$settingKey]));
            p136_check('Footer setting: ' . $settingKey, $value !== '' ? 'PASS' : 'WARN', $value !== '' ? 'Configured' : 'Blank; icon will remain hidden');
        } catch (Throwable $exception) {
            p136_check('Footer setting: ' . $settingKey, 'FAIL', 'Query failed.');
        }
    }

    try {
        db()->prepare('INSERT INTO enquiries (name, phone, course_interest, current_level, preferred_batch, lead_source, message, enquiry_status, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        db()->prepare('INSERT INTO students (full_name, phone, email, password_hash, current_level, target_goal, preferred_language, daily_goal_minutes, published, status_deleted, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())');
        db()->prepare("INSERT INTO weekly_test_attempts (test_id,student_id,guest_name,guest_phone,canonical_phone,started_at,expires_at,status,total_marks,access_token,result_token,question_order,question_snapshot) VALUES (?,?,?,?,?,NOW(),?,'started',?,?,?,?,?)");
        p136_check('Core insert statements', 'PASS', 'Admission, student and weekly-attempt statements prepared.');
    } catch (Throwable $exception) {
        p136_check('Core insert statements', 'FAIL', 'One or more statements could not be prepared.');
    }

    if (filter_var(getenv('PHASE136_WRITE_TESTS') ?: 'false', FILTER_VALIDATE_BOOL)) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $suffix = substr((string)time(), -7);
            $phone = '9' . str_pad($suffix, 9, '0', STR_PAD_LEFT);
            $pdo->prepare('INSERT INTO enquiries (name, phone, course_interest, current_level, preferred_batch, lead_source, message, enquiry_status, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())')
                ->execute(['Phase136 Check', $phone, 'Basic Spoken English', 'Basic', 'Morning', 'Phase136 CLI', 'Rollback test', 'New', '127.0.0.1']);
            $pdo->prepare('INSERT INTO students (full_name, phone, email, password_hash, current_level, target_goal, preferred_language, daily_goal_minutes, published, status_deleted, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())')
                ->execute(['Phase136 Student', $phone, null, password_hash('Temporary#136', PASSWORD_DEFAULT), 'Basic', 'Verification', 'Hindi', 20, 'Yes']);
            $inserted = (int)p136_scalar('SELECT COUNT(*) FROM enquiries WHERE phone=?', [$phone]) === 1
                && (int)p136_scalar('SELECT COUNT(*) FROM students WHERE phone=?', [$phone]) === 1;
            $pdo->rollBack();
            p136_check('Transactional write/rollback', $inserted ? 'PASS' : 'FAIL', $inserted ? 'Rows inserted and rolled back.' : 'Inserted rows were not readable.');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            p136_check('Transactional write/rollback', 'FAIL', 'Write test failed; no data was committed.');
        }
    } else {
        p136_check('Transactional write/rollback', 'WARN', 'Skipped. Run with PHASE136_WRITE_TESTS=true on staging/local DB.');
    }
}

$storage = dirname(__DIR__) . '/storage';
p136_check('Storage directory', is_dir($storage) && is_writable($storage) ? 'PASS' : 'FAIL', $storage);
$rateLimits = $storage . '/rate-limits';
if (!is_dir($rateLimits)) @mkdir($rateLimits, 0775, true);
p136_check('Rate-limit directory', is_dir($rateLimits) && is_writable($rateLimits) ? 'PASS' : 'FAIL', $rateLimits);

$width = max(32, max(array_map(static fn(array $row): int => strlen($row[0]), $rows)) + 2);
echo "Phase 136 Functional Check\n" . str_repeat('=', 92) . "\n";
foreach ($rows as [$name, $status, $detail]) printf("%-{$width}s %-5s %s\n", $name, $status, $detail);
echo str_repeat('-', 92) . "\nFailures: {$failures} | Warnings: {$warnings}\n";
exit($failures > 0 ? 1 : 0);
