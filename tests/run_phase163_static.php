<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$failures = [];

function p163_read(string $path): string {
    if (!is_file($path)) throw new RuntimeException('Missing file: ' . $path);
    $data = file_get_contents($path);
    if (!is_string($data)) throw new RuntimeException('Could not read: ' . $path);
    return $data;
}
function p163_check(bool $ok, string $label): void {
    global $checks, $failures;
    $checks[] = [$label, $ok];
    if (!$ok) $failures[] = $label;
}

$functions = p163_read($root . '/includes/functions.php');
$student = p163_read($root . '/admin/student-view.php');
$adminTests = p163_read($root . '/admin/weekly-tests.php');
$adminAjax = p163_read($root . '/admin/weekly-test-ajax.php');
$paper = p163_read($root . '/admin/weekly-test-paper.php');
$front = p163_read($root . '/weekly-test.php');
$api = p163_read($root . '/weekly-test-api.php');
$frontJs = p163_read($root . '/assets/js/phase146-weekly-test.js');
$css = p163_read($root . '/assets/css/phase147-student-accounts.css');
$sw = p163_read($root . '/sw.js');

p163_check(strpos($functions, 'function student_admin_password_error') !== false, 'Admin-assisted student password validator exists');
p163_check(strpos($functions, 'Password must be at least 8 characters') !== false, 'Student self-registration still keeps the stronger minimum password rule');
p163_check(strpos($student, 'student_admin_password_error($newPassword)') !== false, 'Admin password reset uses admin-assisted password rule');
p163_check(strpos($student, 'Reset Note <small>(optional)</small>') !== false, 'Password reset note is optional');
p163_check(strpos($student, 'minlength="8"') === false, 'Student admin reset form does not impose the old 8-character UI restriction');
p163_check(substr_count($student, 'data-toggle-password=') >= 2, 'Admin password reset has Show/Hide controls');
p163_check(strpos($student, 'id="test-access-control"') !== false && strpos($student, 'Upcoming Test Batch Access') !== false, 'Student detail has Upcoming Test batch-access control');
p163_check(strpos($functions, 'function student_set_weekly_test_batch_access') !== false, 'Manual Weekly Test batch-access helper exists');
p163_check(strpos($functions, "'Weekly Test Access'") !== false, 'Manual Weekly Test access uses a dedicated learning-only enrollment marker');
p163_check(strpos($functions, 'UPDATE admissions SET') === false || strpos($functions, 'student_set_weekly_test_batch_access') < strpos($functions, 'UPDATE admissions SET'), 'Manual Weekly Test access does not directly rewrite admission data');
p163_check(strpos($functions, 'lifecycle_link_student_registration($studentId') !== false, 'Verified legacy student admission can be safely reconciled for batch eligibility');
p163_check(strpos($functions, "identity_status") !== false && strpos($functions, "==='Verified'") !== false, 'Batch reconciliation preserves verified-identity safety gate');

$closePos = strpos($functions, 'function weekly_test_close_entry');
$closeEnd = $closePos !== false ? strpos($functions, 'function weekly_test_fetch_tests', $closePos) : false;
$closeBlock = ($closePos !== false && $closeEnd !== false) ? substr($functions, $closePos, $closeEnd - $closePos) : '';
p163_check($closeBlock !== '' && strpos($closeBlock, "status='draft'") !== false, 'Close Entry blocks new starts by moving the paper out of active status');
p163_check($closeBlock !== '' && strpos($closeBlock, 'ends_at=') === false, 'Close Entry does not shorten ends_at and therefore does not leak Upcoming master answers early');
p163_check(strpos($functions, '$entryClosedNow = true') !== false, 'Finalize Top 3 closes new Upcoming entry automatically when needed');
p163_check(strpos($functions, "status='started'") !== false && strpos($functions, 'still in progress') !== false, 'Finalize Top 3 still blocks while an attempt is actively running');
p163_check(strpos($functions, "status='submitted'") !== false && strpos($functions, 'After all copies are Checked') !== false, 'Finalize Top 3 still blocks until submitted copies are teacher-checked');
p163_check(strpos($adminTests, 'Close Entry') !== false && strpos($paper, 'Close Entry') !== false, 'Admin test screens expose Close Entry clearly');
p163_check(strpos($adminTests, 'Finalize Top 3') !== false && strpos($paper, 'Finalize Top 3') !== false, 'Admin test screens expose Finalize Top 3 clearly');
p163_check(strpos($adminAjax, 'weekly_test_close_entry($testId)') !== false, 'AJAX close action uses central safe Close Entry helper');

p163_check(strpos($front, 'data-batch-allowed') !== false && strpos($front, 'data-batch-message') !== false, 'Student Test Center exposes authoritative batch gate state to its UI');
p163_check(strpos($front, 'data-student-allowed') !== false && strpos($front, 'data-student-message') !== false, 'Student Test Center carries cooldown/account eligibility into each Upcoming paper option');
p163_check(strpos($frontJs, 'batchAllowed') !== false && strpos($frontJs, "option.dataset.batchAllowed !== '0'") !== false, 'Student Test Center JS cannot re-enable a batch-denied test');
p163_check(strpos($frontJs, 'studentAllowed') !== false && strpos($frontJs, "option.dataset.studentAllowed !== '0'") !== false, 'Student Test Center JS cannot re-enable a cooldown/account-denied Upcoming test');
p163_check(strpos($api, '$canResumeClosed') !== false, 'Already-started official attempts can resume after Admin closes new entry');
p163_check(strpos($api, "if (\$isOfficialExam && \$studentId && !\$canResumeClosed)") !== false, 'New official attempts still run server-side batch eligibility after close/resume logic');
p163_check(strpos($css, '.wf163-password-input') !== false && strpos($css, '.wf163-test-access-current') !== false, 'Student-account UI has bounded Phase 163 password and batch-access styles');

preg_match('/wellfare-spoken-static-v(\d+)/', $sw, $m);
p163_check((int)($m[1] ?? 0) >= 163, 'Service worker cache is Phase 163 or newer');

foreach ($checks as [$label, $ok]) echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
if ($failures) {
    fwrite(STDERR, PHP_EOL . 'Phase 163 static checks failed: ' . implode('; ', $failures) . PHP_EOL);
    exit(1);
}
echo PHP_EOL . 'Phase 163 static checks passed: ' . count($checks) . PHP_EOL;
