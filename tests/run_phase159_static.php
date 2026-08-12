<?php
$root = dirname(__DIR__);
$dashboard = file_get_contents($root . '/admin/dashboard.php');
$weekly = file_get_contents($root . '/admin/weekly-tests.php');
$css = file_get_contents($root . '/assets/css/phase159-admin-weekly-papers.css');
$sw = file_get_contents($root . '/sw.js');
$checks = [];
function p159_check(bool $ok, string $label): void { global $checks; $checks[] = [$label,$ok]; }
p159_check(strpos($dashboard, 'Batch-wise Question Papers & Answer Keys') !== false, 'Admin Dashboard exposes offline paper center');
p159_check(strpos($dashboard, 'mode=paper&autoprint=1') !== false, 'Dashboard has Student Paper/PDF action');
p159_check(strpos($dashboard, 'mode=answer-key') !== false, 'Dashboard has Answer Key action');
p159_check(strpos($dashboard, "admin_can('tests.manage')") !== false, 'Dashboard offline paper center is permission scoped');
p159_check(strpos($weekly, 'id="answer-sheet"') !== false, 'Weekly Answer Sheet anchor exists');
p159_check(substr_count($weekly, 'weekly-test-offline-paper.php') >= 4, 'Weekly Test paper cards expose offline routes');
p159_check(strpos($weekly, 'Student Paper / PDF') !== false && strpos($weekly, 'Answer Key') !== false, 'Weekly Test cards show paper and key actions');
p159_check(strpos($css, 'paper-card-actions') !== false && strpos($css, 'white-space:normal') !== false && strpos($css, 'min-width:0') !== false, 'Weekly card buttons are bounded and wrap safely');
p159_check(strpos($css, '@media(max-width:380px)') !== false, 'Very small Admin screens have one-column action fallback');
$swVersion = preg_match('/wellfare-spoken-static-v(\d+)/', $sw, $m) ? (int)$m[1] : 0;
p159_check($swVersion >= 159, 'Service worker cache is Phase 159 or newer');
p159_check(strpos($sw, './assets/css/phase159-admin-weekly-papers.min.css') !== false, 'Service worker caches Phase 159 Admin weekly styles');
$failed = 0; foreach ($checks as [$label,$ok]) { echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL; if(!$ok)$failed++; }
if($failed){ fwrite(STDERR, "\nPhase 159 static checks failed: $failed\n"); exit(1);}
echo "\nAll Phase 159 static checks passed.\n";
