<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$failures = [];

function read_file_or_fail(string $path): string {
    if (!is_file($path)) throw new RuntimeException('Missing file: ' . $path);
    $data = file_get_contents($path);
    if (!is_string($data)) throw new RuntimeException('Could not read: ' . $path);
    return $data;
}
function check_true(bool $ok, string $label): void {
    global $checks, $failures;
    $checks[] = [$label, $ok];
    if (!$ok) $failures[] = $label;
}

$functions = read_file_or_fail($root . '/includes/functions.php');
$result = read_file_or_fail($root . '/weekly-result.php');
$dashboard = read_file_or_fail($root . '/student-dashboard.php');
$review = read_file_or_fail($root . '/student-weekly-answer-review.php');
$offline = read_file_or_fail($root . '/admin/weekly-test-offline-paper.php');
$paper = read_file_or_fail($root . '/admin/weekly-test-paper.php');
$testsPage = read_file_or_fail($root . '/admin/weekly-tests.php');
$backend = read_file_or_fail($root . '/includes/phase148_backend.php');
$css = read_file_or_fail($root . '/assets/css/phase158-test-results.css');
$js = read_file_or_fail($root . '/assets/js/phase158-test-results.js');
$sw = read_file_or_fail($root . '/sw.js');

check_true(strpos($functions, 'function weekly_test_expected_answers_releasable') !== false, 'Answer-release helper exists');
check_true(strpos($functions, "\$type !== 'upcoming'") !== false, 'Basic/Previous answers release after final submit');
check_true(strpos($functions, "['archived', 'closed', 'completed']") !== false, 'Upcoming master answers unlock after paper closure');
check_true(strpos($functions, 'test_ends_at') !== false, 'Upcoming answer release considers end time');
check_true(strpos($functions, "status='started'") !== false && strpos($functions, 'before ranking') !== false, 'Top-3 completion blocks in-progress attempts');
check_true(strpos($functions, "status='submitted'") !== false && strpos($functions, 'before fixing the Top 3 positions') !== false, 'Top-3 completion blocks unreviewed submitted attempts');
check_true(strpos($functions, 'weekly_test_latest_upcoming_rank_for_student') !== false, 'Latest Upcoming rank helper exists');

check_true(strpos($result, 'weekly_test_expected_answers_releasable($attempt)') !== false, 'Result page uses safe answer release policy');
check_true(strpos($result, 'weekly_test_split_expected_answers') !== false, 'Result page shows all accepted uploaded answer variants');
check_true(strpos($dashboard, 'wf158-history-answers') !== false && strpos($dashboard, 'student-weekly-answer-review.php') !== false, 'Dashboard has lazy question/answer review');
check_true(strpos($dashboard, 'weekly_test_latest_upcoming_rank_for_student') !== false, 'Dashboard reads latest Upcoming Top-3 rank');
check_true(strpos($review, 'weekly_test_expected_answers_releasable') !== false && strpos($review, 'Your answer') !== false, 'Inline review shows student answer and safe master answer');
check_true(strpos($js, 'fetch(url') !== false && strpos($js, "cache: 'no-store'") !== false, 'Dashboard answer review lazy-loads without cache');

check_true(strpos($offline, "admin_require_permission('tests.manage')") !== false, 'Offline paper requires test-management permission');
check_true(strpos($offline, "['paper', 'answer-key']") !== false, 'Offline route separates student paper and answer key');
check_true(strpos($offline, 'Save as PDF / Print') !== false, 'Offline route exposes PDF/print action');
check_true(strpos($offline, 'watermark') !== false, 'Offline paper includes watermark');
check_true(strpos($offline, 'break-inside:avoid') !== false && strpos($offline, 'page-break-inside:avoid') !== false, 'Question blocks are protected from page cutting');
check_true(strpos($offline, 'Name:') !== false && strpos($offline, 'Mobile / Roll:') !== false && strpos($offline, 'Date:') !== false, 'Offline student paper has identity fields');
check_true(strpos($offline, 'answer-space') !== false, 'Offline student paper includes answer-writing space');
check_true(strpos($offline, 'batch_name') !== false && strpos($offline, 'batch_timing') !== false, 'Offline paper includes batch metadata');
check_true(strpos($paper, 'Offline PDF') !== false && strpos($testsPage, 'Offline PDF') !== false, 'Admin weekly-test screens link to offline paper');
check_true(strpos($paper, 'Complete + Rank Top 3') !== false || strpos($testsPage, 'Complete + Rank') !== false, 'Admin Upcoming completion exposes rank action');
check_true(strpos($backend, "'weekly-test-offline-paper.php'=>'tests.manage'") !== false, 'Offline paper is mapped to RBAC permission');

check_true(strpos($css, '#f0b72f') !== false, 'Rank 1 uses gold');
check_true(strpos($css, '#8b5cf6') !== false, 'Rank 2 uses violet/purple');
check_true(strpos($css, '#75d21e') !== false, 'Rank 3 uses parrot green');
check_true(strpos($sw, 'wellfare-spoken-static-v158') !== false, 'Service worker cache is v158');
check_true(strpos($sw, './assets/css/phase158-test-results.min.css') !== false, 'Service worker caches Phase 158 CSS');
check_true(strpos($sw, './assets/js/phase158-test-results.js') !== false, 'Service worker caches Phase 158 JS');

foreach ($checks as [$label, $ok]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
}
if ($failures) {
    fwrite(STDERR, PHP_EOL . 'Phase 158 static checks failed: ' . implode('; ', $failures) . PHP_EOL);
    exit(1);
}
echo PHP_EOL . 'Phase 158 static checks passed: ' . count($checks) . PHP_EOL;
