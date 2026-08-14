<?php
$root = dirname(__DIR__);
$checks = [];
function p170_check(bool $ok, string $label): void {
    global $checks;
    $checks[] = [$ok, $label];
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
}
function p170_read(string $path): string { return is_file($path) ? (string)file_get_contents($path) : ''; }

$page = p170_read($root . '/spoken-materials.php');
$js = p170_read($root . '/assets/js/phase170-spoken-practice.js');
$listApi = p170_read($root . '/material-practice-list-api.php');
$answerApi = p170_read($root . '/material-practice-api.php');
$functions = p170_read($root . '/includes/functions.php');
$sw = p170_read($root . '/sw.js');

p170_check(strpos($page, "phase170-spoken-practice.js") !== false, 'Spoken Materials loads the Phase 170 practice controller');
p170_check(strpos($page, 'Practice does not stop after 20 sentences') !== false, 'UI explains continuous practice clearly');
p170_check(strpos($js, 'offset: String(Math.max(0, offset))') !== false && strpos($js, 'continuePractice') !== false && strpos($js, 'hasMore') !== false, 'Client loads paged sentence sets instead of stopping after 20');
p170_check(strpos($listApi, "\$offset = min(100000") !== false && strpos($listApi, "'next_offset'") !== false && strpos($listApi, "'has_more'") !== false, 'Practice list API exposes pagination state');
p170_check(strpos($functions, 'int $offset = 0') !== false && strpos($functions, 'LIMIT {$offset}, {$limit}') !== false, 'Translation pair fetch supports safe paging');
p170_check(strpos($js, "['no-speech', 'network', 'aborted']") !== false && strpos($js, 'scheduleMicRecovery') !== false, 'Recoverable microphone interruptions auto-resume');
p170_check(strpos($js, "document.addEventListener('visibilitychange'") !== false && strpos($js, 'Welcome back. Voice coach is resuming') !== false, 'Mobile app/tab return resumes the voice coach');
p170_check(strpos($js, 'answerTimedOut') !== false && strpos($js, '12000') !== false, 'Answer checking has a timeout watchdog instead of hanging forever');
p170_check(strpos($js, 'status === 419') !== false && strpos($js, 'Refreshing the practice session') !== false, 'Expired CSRF/session token can self-refresh during voice practice');
p170_check(strpos($answerApi, '$practiceLimit = $studentId > 0 ? 600 : 300') !== false, 'Hands-free practice rate limit no longer interrupts normal long sessions');
p170_check(strpos($functions, "!empty(\$result['is_correct'])") !== false && strpos($functions, 'Every retry remains in material_practice_attempts') !== false, 'Wrong retries remain tracked without bloating the dashboard activity log');
p170_check(strpos($js, 'voicePausedByUser') !== false && strpos($js, 'Voice paused. Tap Speak answer to resume') !== false, 'Student can explicitly pause continuous voice recovery');
p170_check(strpos($sw, 'wellfare-spoken-static-v170') !== false && strpos($sw, 'phase170-spoken-practice.js') !== false, 'Service Worker cache is Phase 170 and pre-caches the new controller');

$failed = array_filter($checks, static fn(array $c): bool => !$c[0]);
if ($failed) {
    echo PHP_EOL . count($failed) . ' Phase 170 static check(s) failed.' . PHP_EOL;
    exit(1);
}
echo PHP_EOL . 'All Phase 170 static checks passed: ' . count($checks) . PHP_EOL;
