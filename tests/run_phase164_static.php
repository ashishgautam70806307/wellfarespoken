<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$failures = [];

function p164_read(string $path): string {
    if (!is_file($path)) throw new RuntimeException('Missing file: ' . $path);
    $data = file_get_contents($path);
    if (!is_string($data)) throw new RuntimeException('Could not read: ' . $path);
    return $data;
}
function p164_check(bool $ok, string $label): void {
    global $checks, $failures;
    $checks[] = [$label, $ok];
    if (!$ok) $failures[] = $label;
}

$front = p164_read($root . '/weekly-test.php');
$functions = p164_read($root . '/includes/functions.php');
$api = p164_read($root . '/weekly-test-api.php');
$room = p164_read($root . '/weekly-exam-room.php');
$js = p164_read($root . '/assets/js/phase146-weekly-test.js');
$sw = p164_read($root . '/sw.js');

p164_check(strpos($front, '$lockedUpcoming = [];') !== false, 'Published Upcoming papers have an explicit locked/discoverable bucket');
p164_check(strpos($front, '$firstActiveLockedPaper') !== false && strpos($front, "\$testPools['upcoming'] = [\$firstActiveLockedPaper];") !== false, 'A published active locked paper remains discoverable when no eligible paper exists');
p164_check(strpos($front, "['Batch access needed', 'is-scheduled']") !== false, 'Frontend shows a clear batch-access state instead of No paper');
p164_check(strpos($front, "['Temporarily locked', 'is-scheduled']") !== false, 'Cooldown/account lock is visible in the card status');
p164_check(strpos($front, '$lockedUpcoming ? [reset($lockedUpcoming)] : []') !== false, 'Upcoming visibility has a locked-paper fallback instead of an empty eligible-only result');
p164_check(strpos($front, "data-batch-allowed") !== false && strpos($front, "data-student-allowed") !== false, 'Existing authoritative client gate metadata remains');
p164_check(strpos($js, "option.dataset.batchAllowed !== '0'") !== false && strpos($js, "option.dataset.studentAllowed !== '0'") !== false, 'Client cannot enable a server-denied Upcoming paper');

p164_check(strpos($functions, 'function weekly_test_sync_linked_batch_access') !== false, 'Publishing can repair lifecycle membership for already-linked admissions');
p164_check(strpos($functions, 'weekly_test_sync_linked_batch_access($batchId)') !== false, 'Upcoming activation invokes safe linked-admission batch sync');
p164_check(strpos($functions, 'student_id IS NOT NULL AND student_id>0') !== false, 'Batch sync does not auto-link unverified phone-only accounts');
p164_check(strpos($functions, "function weekly_test_student_batch_eligibility") !== false, 'Server-side batch authorization remains in place');

p164_check(strpos($api, 'SELECT * FROM weekly_tests WHERE id=? AND status_deleted=0 FOR UPDATE') !== false, 'Official start re-fetches and locks the complete paper');
p164_check(strpos($api, 'This Upcoming Test is closed for new entries') !== false, 'Concurrent Admin close is rechecked after row lock');
p164_check(substr_count($api, 'weekly_test_student_batch_eligibility($studentId, $test)') >= 2, 'Batch authorization is checked again inside the locked start transaction');
p164_check(strpos($api, 'weekly_test_upcoming_eligibility($studentId, (int)$test[\'id\'])') !== false, 'Anti-repeat/cooldown eligibility remains server authoritative');

p164_check(strpos($room, 'submittingFinal=false') !== false, 'Exam room has a final-submit re-entry guard');
p164_check(strpos($room, 'function clearExamIntervals()') !== false, 'Exam room can stop timer/autosave loops before final submission');
p164_check(strpos($room, 'if(submittingFinal) return;') !== false, 'Timer or double click cannot fire repeated final submissions');
p164_check(strpos($room, 'clearExamIntervals(); location.href') !== false, 'Exam intervals are cleaned before leaving completed/cancelled attempts');

preg_match('/wellfare-spoken-static-v(\d+)/', $sw, $m);
p164_check((int)($m[1] ?? 0) >= 164, 'Service worker cache is Phase 164 or newer');

foreach ($checks as [$label, $ok]) echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
if ($failures) {
    fwrite(STDERR, PHP_EOL . 'Phase 164 static checks failed: ' . implode('; ', $failures) . PHP_EOL);
    exit(1);
}
echo PHP_EOL . 'Phase 164 static checks passed: ' . count($checks) . PHP_EOL;
