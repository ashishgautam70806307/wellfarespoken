<?php
$root = dirname(__DIR__);
function p162_check(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    echo "PASS: $message\n";
}
$dashboard = file_get_contents($root.'/admin/dashboard.php');
$performance = file_get_contents($root.'/admin/upcoming-test-performance.php');
$functions = file_get_contents($root.'/includes/functions.php');
$api = file_get_contents($root.'/weekly-test-api.php');
$student = file_get_contents($root.'/weekly-test.php');
$css = file_get_contents($root.'/assets/css/phase162-dashboard-performance.css');
$sw = file_get_contents($root.'/sw.js');

$pPaper = strpos($dashboard, 'Batch-wise Question Papers & Answer Keys');
$pLinks = strpos($dashboard, 'grid-4 admin-dashboard-links wf162-dashboard-priority-links');
$pSecurity = strpos($dashboard, 'Security & Access Control');
p162_check($pPaper !== false && $pLinks !== false && $pSecurity !== false && $pPaper < $pSecurity && $pLinks < $pSecurity, 'Offline paper center and dashboard quick links are before lower dashboard sections');
p162_check(strpos($dashboard, 'Separate Winner Card') !== false && strpos($dashboard, '1st – 3rd Winners') !== false, 'Dashboard has a separate 1st–3rd winner card');
p162_check(strpos($dashboard, 'Open Batch Performance') !== false && strpos($dashboard, 'wf162-batch-chip') !== false, 'Dashboard performance explicitly exposes selected batch');
p162_check(strpos($performance, '1. Choose Batch') !== false && strpos($performance, '2. Choose Test') !== false, 'Performance board has explicit batch then test selectors');
p162_check(strpos($performance, 'Top 10 — <?= e($selectedBatchLabel) ?>') !== false, 'Top 10 is visibly labeled by batch');
p162_check(strpos($performance, 'id="winner-cards"') !== false && strpos($performance, 'Official 1st – 3rd Winners') !== false, 'Performance board keeps winner cards separate from Top 10');
p162_check(strpos($functions, 'function weekly_test_student_batch_eligibility') !== false, 'Shared batch eligibility guard exists');
p162_check(strpos($api, 'weekly_test_student_batch_eligibility($studentId, $test)') !== false, 'Official Upcoming start enforces batch eligibility server-side');
p162_check(strpos($student, 'weekly_test_student_batch_eligibility($studentIdForBatch, $paper)') !== false, 'Student Test Center filters Upcoming papers to eligible batches');
p162_check(strpos($functions, "test_type='upcoming' AND id<>? AND COALESCE(batch_id,0)=?") !== false, 'Upcoming active-paper exclusivity is scoped per batch');
p162_check(strpos($css, '.page-admin-dashboard .btn') !== false && strpos($css, 'font-size:11px') !== false, 'Dashboard button labels use compact typography');
p162_check(strpos($sw, 'wellfare-spoken-static-v162') !== false && strpos($sw, 'phase162-dashboard-performance.min.css') !== false, 'Service worker cache and Phase 162 CSS are current');
