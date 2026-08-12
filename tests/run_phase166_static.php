<?php
$root = dirname(__DIR__);
$fail = 0;
function p166_check(bool $ok, string $label): void { global $fail; echo ($ok ? "[PASS] " : "[FAIL] ") . $label . PHP_EOL; if (!$ok) $fail++; }
function p166_read(string $path): string { $s = @file_get_contents($path); return is_string($s) ? $s : ''; }

$functions = p166_read($root . '/includes/functions.php');
$ajax = p166_read($root . '/admin/weekly-test-ajax.php');
$weekly = p166_read($root . '/admin/weekly-tests.php');
$dash = p166_read($root . '/admin/dashboard.php');
$head = p166_read($root . '/admin/_header.php');
$foot = p166_read($root . '/admin/_footer.php');
$timeJs = p166_read($root . '/assets/js/phase166-time12.js');
$css = p166_read($root . '/assets/css/phase166-admin-workflow.css');
$sw = p166_read($root . '/sw.js');

p166_check(strpos($functions, 'function weekly_test_release_answers_to_students') !== false, 'Safe manual Upcoming answer-release helper exists');
p166_check(strpos($functions, 'weekly_test_answers_manually_released($testId)') !== false, 'Student answer release policy checks Admin manual release');
p166_check(strpos($functions, 'weekly_test_clear_manual_answer_release($testId)') !== false, 'Republishing Upcoming paper relocks prior manual answer release');
p166_check(strpos($ajax, "if(\$action==='release_answer_key')") !== false, 'Admin AJAX release_answer_key action exists');
p166_check(strpos($weekly, 'Release Answer Key') !== false && strpos($weekly, 'Answers Released') !== false, 'Upcoming paper card exposes clear answer-release state/action');
p166_check(strpos($dash, 'Release Answers') !== false || strpos($dash, 'Answers Released') !== false, 'Dashboard paper card links Admin to answer release workflow');
p166_check(strpos($weekly, 'weekly_test_upload_template.xlsx') !== false, 'Weekly Test Admin exposes blank Excel template');
p166_check(strpos($weekly, 'No Excel? Add Manually') !== false && strpos($weekly, 'id="manual-question-editor"') !== false, 'No-Excel manual question-entry path is explicit');
p166_check(strpos($weekly, 'Create 2 Demo Batch Papers') === false, 'Testing-only demo paper generator is hidden from normal Weekly Test Admin UI');
p166_check(is_file($root . '/assets/downloads/weekly_test_upload_template.xlsx'), 'Blank XLSX upload template exists');
p166_check(strpos($head, 'phase166-admin-workflow.css') !== false && strpos($foot, 'phase166-time12.js') !== false, 'Reusable 12-hour Admin UI assets load globally');
p166_check(strpos($timeJs, "for(var h=1;h<=12;h++)") !== false && strpos($timeJs, "option('AM','AM')") !== false && strpos($timeJs, "option('PM','PM')") !== false, 'Time enhancer uses 1-12 + AM/PM controls');
p166_check(strpos($timeJs, "input[type=\"datetime-local\"]") !== false, 'Existing backend datetime fields are enhanced without changing field names/contracts');
p166_check(strpos($css, '.wf166-datetime12') !== false && strpos($css, '.wf166-format-guide') !== false, 'Phase 166 responsive Admin styles present');
p166_check(strpos($sw, "wellfare-spoken-static-v166") !== false && strpos($sw, 'phase166-admin-workflow.min.css') !== false && strpos($sw, 'phase166-time12.js') !== false, 'Service Worker v166 references new assets');

exit($fail ? 1 : 0);
