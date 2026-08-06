<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/ui-components.php';

$root = dirname(__DIR__);
$passed = [];
$failed = [];

function p139_assert(bool $condition, string $label, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        $passed[] = $label;
    } else {
        $failed[] = $label . ($detail !== '' ? ': ' . $detail : '');
    }
}

function p139_source(string $relative): string
{
    global $root;
    $value = @file_get_contents($root . '/' . ltrim($relative, '/'));
    return $value === false ? '' : $value;
}

$future = date('Y-m-d H:i:s', time() + 95);
$remaining = weekly_attempt_remaining_seconds([
    'started_at' => date('Y-m-d H:i:s', time() - 600),
    'expires_at' => $future,
    'duration_minutes' => 1,
]);
p139_assert($remaining >= 90 && $remaining <= 95, 'Stored test expiry is authoritative', (string)$remaining);
p139_assert(weekly_attempt_remaining_seconds([
    'started_at' => date('Y-m-d H:i:s', time() - 600),
    'expires_at' => date('Y-m-d H:i:s', time() - 1),
    'duration_minutes' => 240,
]) === 0, 'Expired test returns zero remaining seconds');

$questions = [
    ['id'=>1, 'question_text'=>'One'],
    ['id'=>2, 'question_text'=>'Two'],
    ['id'=>3, 'question_text'=>'Three'],
];
$ordered = weekly_test_order_questions($questions, ['shuffle_questions'=>'No'], ['question_order'=>json_encode([2,1])]);
p139_assert(array_column($ordered, 'id') === [2,1], 'Saved question order remains stable');
$fallback = weekly_test_order_questions($questions, ['shuffle_questions'=>'No'], ['question_order'=>json_encode([99])]);
p139_assert(array_column($fallback, 'id') === [1,2,3], 'Legacy missing question IDs fall back safely');

$exact = weekly_test_match_answer('I am ready.', 'I am ready.||I am prepared.');
p139_assert(($exact['is_correct'] ?? '') === 'Yes' && (float)($exact['marks_ratio'] ?? 0) === 1.0, 'Exact accepted answer receives full score');
$partial = weekly_test_match_answer('I am redy', 'I am ready');
p139_assert(in_array(($partial['is_correct'] ?? ''), ['Yes','Review'], true), 'Close answer remains reviewable');

p139_assert(safe_local_redirect('weekly-test.php?type=upcoming&test_id=13#my-results', 'index.php') === 'weekly-test.php?type=upcoming&test_id=13#my-results', 'Local redirect preserves type, test ID and fragment');
p139_assert(safe_local_redirect('https://evil.example/path', 'index.php') === 'index.php', 'External redirect is blocked');
p139_assert(APP_ALLOW_SCHEMA_UPDATES === false, 'Runtime schema updates are disabled by default');
p139_assert(in_array(APP_RUNTIME_ENV, ['local','live'], true), 'Runtime environment is resolved');
p139_assert(function_exists('mb_strlen') && function_exists('mb_substr') && function_exists('mb_strimwidth'), 'UTF-8 functions exist with or without mbstring');
p139_assert(wf_text_limit('This is a long sentence for trimming.', 12) !== '', 'Text trimming works without fatal errors');

$api = p139_source('weekly-test-api.php');
$resultPage = p139_source('weekly-result.php');
$examRoom = p139_source('weekly-exam-room.php');
$weeklyJs = p139_source('assets/js/phase130-weekly-test.js');
$config = p139_source('includes/config.php');
$admission = p139_source('admission.php');
$auth = p139_source('student-auth.php');
$roadmap = p139_source('learning-roadmap.php');

p139_assert(!str_contains($api, 'INTERVAL ? MINUTE'), 'Weekly attempt insert avoids parameterized INTERVAL syntax');
p139_assert(str_contains($api, '$expiresAt') && str_contains($api, "VALUES (?,?,?,?,?,NOW(),?,'started'"), 'Weekly attempt stores explicit expiry value');
p139_assert(str_contains($api, "'redirect_url'=>'student-auth.php?redirect='"), 'Upcoming test login redirect preserves selected paper');
p139_assert(str_contains($weeklyJs, 'result.redirect_url') && str_contains($weeklyJs, 'currentTest.id'), 'Weekly Test JavaScript preserves selected paper after login');
p139_assert(str_contains($resultPage, "if (\$attemptStatus === 'started')"), 'Result page blocks active attempts');
p139_assert(str_contains($resultPage, "in_array(\$attemptStatus, ['submitted', 'checked'], true)"), 'Expected answers require submitted/checked result state');
p139_assert(str_contains($examRoom, 'COALESCE(a.status_deleted,0)=0'), 'Exam room rejects soft-deleted attempts');
p139_assert(str_contains($examRoom, 'This attempt has no saved questions'), 'Legacy attempt without questions fails safely');
p139_assert(str_contains($config, "\$_SERVER['HTTP_HOST'] ?? \$_SERVER['SERVER_NAME']"), 'Runtime host prefers actual HTTP host');
p139_assert(str_contains($config, 'TRUST_PROXY_HEADERS'), 'Forwarded host is used only when explicitly trusted');
p139_assert(str_contains($admission, 'admission-enquiry-phone:') && str_contains($admission, 'admission-enquiry-ip:'), 'Admission rate limit covers phone and IP');
p139_assert(str_contains($auth, 'student-auth-ip:') && str_contains($auth, '$rateKey'), 'Student authentication rate limit covers IP and account');
p139_assert(str_contains($roadmap, "\$unit['level'] ?? 'Beginner'"), 'Roadmap missing level uses safe fallback');

$sqlFiles = glob($root . '/sql/*.sql') ?: [];
p139_assert(count($sqlFiles) === 1 && basename($sqlFiles[0]) === 'wellfare_english_complete.sql', 'Exactly one canonical SQL file is present', implode(', ', array_map('basename', $sqlFiles)));

$browserReportPath = $root . '/PHASE139_BROWSER_VALIDATION.json';
if (is_file($browserReportPath)) {
    $browserReport = json_decode((string)file_get_contents($browserReportPath), true);
    p139_assert(is_array($browserReport) && (int)($browserReport['failed'] ?? 1) === 0, 'Browser fixture regression suite passes', json_encode($browserReport));
} else {
    p139_assert(false, 'Browser fixture regression suite report exists');
}

foreach ($passed as $label) echo "PASS  {$label}\n";
foreach ($failed as $label) echo "FAIL  {$label}\n";
echo 'Summary: ' . count($passed) . ' passed, ' . count($failed) . " failed\n";
exit($failed ? 1 : 0);
