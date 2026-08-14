<?php
$root = dirname(__DIR__);
$checks = [];
function p169_check(bool $ok, string $label): void {
    global $checks;
    $checks[] = [$ok, $label];
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
}
function p169_read(string $path): string { return is_file($path) ? (string)file_get_contents($path) : ''; }

$header = p169_read($root . '/admin/_header.php');
$materials = p169_read($root . '/admin/materials.php');
$materialsAjax = p169_read($root . '/admin/materials-ajax.php');
$roadmap = p169_read($root . '/admin/roadmap.php');
$weekly = p169_read($root . '/admin/weekly-tests.php');
$practice = p169_read($root . '/spoken-materials.php');
$practiceJs = p169_read($root . '/assets/js/phase143-spoken-practice.js');
$adminCss = p169_read($root . '/assets/css/phase169-admin-usability.css');
$practiceCss = p169_read($root . '/assets/css/phase169-spoken-materials.css');
$mainJs = p169_read($root . '/assets/js/main.js');
$sw = p169_read($root . '/sw.js');

$enquiryPos = strpos($header, '>Enquiries</span>');
$batchPos = strpos($header, '>Batch Timing Management</span>');
$admissionPos = strpos($header, '>Admissions</span>');
p169_check($enquiryPos !== false && $batchPos !== false && $admissionPos !== false && $enquiryPos < $batchPos && $batchPos < $admissionPos, 'Batch Timing Management appears directly after Enquiries');
p169_check(strpos($mainJs, 'keep the active admin menu item centered') !== false && strpos($mainJs, 'active.offsetTop') !== false, 'Admin sidebar centers the active menu item');

p169_check(strpos($materials, 'sectionCategory') !== false && strpos($materials, 'sectionTopic') !== false, 'Materials uses Category then Topic workflow');
p169_check(strpos($materials, 'Create Category & Continue') !== false && strpos($materials, 'Select Category') !== false && strpos($materials, 'Select Topic') !== false, 'Materials workflow has simple guided controls');
p169_check(strpos($materials, 'wf169-material-advanced') !== false, 'Materials optional fields are collapsible');
p169_check(strpos($materialsAjax, "\$action === 'create_category'") !== false && strpos($materialsAjax, 'Selected topic does not belong to this category') !== false, 'Materials backend supports category creation and safe category/topic validation');
p169_check(strpos($materials, 'allow_row_topics') !== false && strpos($materialsAjax, 'lockToDefaultUnit') !== false, 'Easy bulk upload stays inside the selected topic unless Advanced row routing is enabled');

p169_check(substr_count($roadmap, 'roadmap-create-toggle') >= 2 && strpos($roadmap, "\$editUnit ? 'open' : ''") !== false && strpos($roadmap, "\$editItem ? 'open' : ''") !== false, 'Roadmap create/edit forms are compact expandable cards');

p169_check(strpos($weekly, 'Easy Weekly Test Setup — 3 Steps') === false, 'Weekly Test 3-step guide card is removed');
p169_check(strpos($weekly, 'wf169-copy-card') !== false && strpos($weekly, 'Open Copies') !== false, 'Student Answer Copies cards use improved layout and action');

p169_check(strpos($practice, 'phase169-spoken-materials.css') !== false, 'Spoken Materials loads Phase 169 mobile stylesheet');
p169_check(strpos($practiceJs, 'advanceAfterVoiceCorrect') !== false && strpos($practiceJs, 'currentIndex += 1') !== false, 'Correct hands-free voice answer advances automatically');
p169_check(strpos($practiceJs, 'retryVoiceAfterWrong') !== false && strpos($practiceJs, 'Now say the answer again') !== false, 'Wrong hands-free voice answer stays on sentence and retries');
p169_check(strpos($practiceCss, '.wf127-mobile-bottom') !== false && strpos($practiceCss, 'z-index:1200') !== false, 'Spoken Materials mobile bottom navigation is bounded above overlays');

p169_check(strpos($adminCss, '.roadmap-create-launchers') !== false && strpos($adminCss, '.wf169-copy-card') !== false, 'Phase 169 Admin responsive styles cover requested cards');
p169_check(strpos($sw, 'wellfare-spoken-static-v169') !== false && strpos($sw, 'phase169-spoken-materials.css') !== false && strpos($sw, 'phase169-admin-usability.css') !== false, 'Service worker cache is Phase 169 and includes new styles');

$failed = array_filter($checks, static fn(array $c): bool => !$c[0]);
if ($failed) {
    echo PHP_EOL . count($failed) . ' Phase 169 static check(s) failed.' . PHP_EOL;
    exit(1);
}
echo PHP_EOL . 'All Phase 169 static checks passed: ' . count($checks) . PHP_EOL;
