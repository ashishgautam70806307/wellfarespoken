<?php
$root = dirname(__DIR__);
$functions = file_get_contents($root . '/includes/functions.php');
$api = file_get_contents($root . '/weekly-test-api.php');
$front = file_get_contents($root . '/weekly-test.php');
$dashboard = file_get_contents($root . '/admin/dashboard.php');
$performance = file_get_contents($root . '/admin/upcoming-test-performance.php');
$weeklyAdmin = file_get_contents($root . '/admin/weekly-tests.php');
$backend = file_get_contents($root . '/includes/phase148_backend.php');
$css = file_get_contents($root . '/assets/css/phase161-upcoming-performance.css');
$sw = file_get_contents($root . '/sw.js');
$checks = [];
function p161_check(bool $ok, string $label): void { global $checks; $checks[] = [$label, $ok]; }

p161_check(strpos($dashboard, 'Upcoming Test Performance') !== false && strpos($dashboard, 'upcoming-test-performance.php') !== false, 'Admin Dashboard exposes Upcoming Test Performance card');
p161_check(strpos($performance, 'Top 10 by Marks') !== false, 'Performance page includes Top 10 leaderboard');
p161_check(strpos($performance, 'Marks 0–10') !== false && strpos($performance, 'Low-score Distribution') !== false, 'Performance page includes 0-10 score distribution');
p161_check(strpos($performance, 'Official Top 3') !== false && strpos($performance, 'Provisional Top 3') !== false, 'Performance page supports official/provisional Top 3');
p161_check(strpos($css, '@keyframes wf161WinnerWave') !== false && strpos($css, '.rank-1') !== false && strpos($css, '.rank-2') !== false && strpos($css, '.rank-3') !== false, 'Top 3 have slow animated rank-specific cards');
p161_check(strpos($backend, "'upcoming-test-performance.php'=>'tests.manage'") !== false, 'Performance page is protected by tests.manage');
p161_check(strpos($weeklyAdmin, 'upcoming-test-performance.php') !== false, 'Weekly Test Admin links to performance board');
p161_check(strpos($functions, "weekly_upcoming_min_gap_hours', '12'") !== false, 'Upcoming Test rapid-repeat gap defaults to 12 hours');
p161_check(strpos($functions, 'function weekly_test_upcoming_eligibility') !== false, 'Reusable Upcoming Test eligibility guard exists');
p161_check(strpos($functions, "a.status='started' AND (a.expires_at IS NULL OR a.expires_at>NOW())") !== false, 'Only live other Upcoming attempts block a new exam');
p161_check(strpos($api, 'SELECT id FROM students WHERE id=? AND status_deleted=0 FOR UPDATE') !== false, 'Official start serializes on student row');
p161_check(strpos($api, 'weekly_test_upcoming_eligibility($studentId') !== false, 'Weekly Test API enforces cross-paper eligibility server-side');
p161_check(strpos($front, 'weekly_test_upcoming_eligibility') !== false && strpos($front, '$selectedEligibility') !== false, 'Student Test Center explains/blocks active gap in UI');
p161_check(strpos($functions, "weekly_upcoming_min_gap_hours', '12'") !== false && strpos($functions, '$gapHours * 3600') !== false, 'Upcoming Test spacing is configurable in hours, not a fixed weekly cycle');
p161_check(strpos($sw, 'wellfare-spoken-static-v161') !== false, 'Service worker cache is v161');
p161_check(strpos($sw, './assets/css/phase161-upcoming-performance.min.css') !== false, 'Phase 161 CSS is included in static cache list');

$failed = 0;
foreach ($checks as [$label, $ok]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    if (!$ok) $failed++;
}
if ($failed) {
    fwrite(STDERR, "\nPhase 161 static checks failed: {$failed}\n");
    exit(1);
}
echo "\nAll Phase 161 static checks passed.\n";
