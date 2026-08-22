<?php
$root = dirname(__DIR__);
$checks = [];
$assert = static function (bool $ok, string $name) use (&$checks): void {
    $checks[] = [$ok, $name];
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
$read = static fn(string $file): string => (string)@file_get_contents($root . '/' . $file);
$spoken = $read('spoken-materials.php');
$practice = $read('free-ai-english-practice.php');
$roadmap = $read('learning-roadmap.php');
$header = $read('includes/header.php');
$css = $read('assets/css/phase176-mobile-reference-match.css');
$js = $read('assets/js/phase176-mobile-reference-match.js');
$js175 = $read('assets/js/phase175-mobile-learning.js');
$voice = $read('assets/js/phase170-spoken-practice.js');
$sw = $read('sw.js');

$assert(str_contains($spoken, 'phase176-mobile-reference-match.css') && str_contains($spoken, 'phase176-mobile-reference-match.js'), 'Spoken Materials loads Phase 176 reference-match assets');
$assert(str_contains($practice, 'phase176-mobile-reference-match.css') && str_contains($roadmap, 'phase176-mobile-reference-match.css'), 'Practice and Roadmap load Phase 176 reference-match CSS');
$assert(str_contains($header, 'wf176-learning-appbar') && str_contains($header, 'data-drawer-open'), 'Three-page opt-in mobile app bar retains real navigation drawer access');
$assert(str_contains($css, '.wf127-contact-dock') && str_contains($css, '.wf137-footer') && str_contains($css, 'display:none!important'), 'Learning-page mobile shell removes floating contact/footer overlap');
$assert(str_contains($css, 'border-radius:0!important') && str_contains($css, 'height:64px!important'), 'Bottom learning navigation uses attached app-style geometry');
$assert(str_contains($spoken, 'wf176-mode-filter') && substr_count($spoken, 'wf176-mode-progress') === 4, 'Spoken browse screen has five quick filters and four reference-style lesson rows');
$assert(str_contains($css, 'wf175-practice-active .wf175-materials-head{display:block') && str_contains($css, 'wf175-practice-active .wf176-mode-filter'), 'Active spoken practice keeps compact page identity while hiding browse controls');
$assert(str_contains($css, '@media (max-width:760px) and (max-height:600px)') && str_contains($css, 'position:static!important') && str_contains($css, 'never cover the answer/actions'), 'Short 320x455-style viewport keeps nav/actions in normal flow instead of covering controls');
$assert(str_contains($practice, 'wf176LessonSearch') && str_contains($js, 'setupPracticeSearch'), 'Practice Materials search filters the real lesson selector');
$assert(str_contains($js, 'setupSpokenFilters') && str_contains($js, 'data-wf176-mode-filter'), 'Spoken quick filters are functional, not decorative');
$assert(str_contains($js175, "(isMobile() || reduceMotion) ? 'auto' : 'smooth'"), 'Mobile page shortcut scrolling responds immediately');
$assert(str_contains($voice, "root.scrollIntoView({ block: 'start', behavior: 'auto' });"), 'Change Mode no longer waits for a smooth-scroll animation');
$assert(str_contains($sw, 'wellfare-spoken-static-v176') && str_contains($sw, 'phase176-mobile-reference-match.css') && str_contains($sw, 'phase176-mobile-reference-match.js'), 'Service Worker v176 includes the new mobile assets');
$assert(!is_file($root . '/poken-materials.php'), 'No fake poken-materials.php duplicate page was introduced');

foreach (['spoken-materials.php'=>$spoken,'free-ai-english-practice.php'=>$practice,'learning-roadmap.php'=>$roadmap] as $file=>$html) {
    preg_match_all('/\bid\s*=\s*["\']([^"\']+)["\']/', $html, $m);
    $ids = $m[1] ?? [];
    $dupes = array_diff_assoc($ids, array_unique($ids));
    $assert(!$dupes, $file . ' has no duplicate literal IDs');
}

$failed = array_filter($checks, static fn(array $row): bool => !$row[0]);
echo PHP_EOL . count($checks) . ' checks, ' . count($failed) . ' failed.' . PHP_EOL;
exit($failed ? 1 : 0);
