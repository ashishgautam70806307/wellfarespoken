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
$footer = $read('includes/footer.php');
$header = $read('includes/header.php');
$css = $read('assets/css/phase175-mobile-learning.css');
$js = $read('assets/js/phase175-mobile-learning.js');
$sw = $read('sw.js');

$assert(str_contains($spoken, 'phase175-mobile-learning.css') && str_contains($spoken, 'phase175-mobile-learning.js'), 'Spoken Materials loads Phase 175 mobile assets');
$assert(str_contains($spoken, 'wf175ModeSearch') && str_contains($spoken, 'wf175-step-strip'), 'Spoken Materials has mobile search and simple step flow');
$assert(str_contains($practice, 'Practice Materials') && str_contains($practice, 'wf175LessonSelect'), 'Practice page has approved mobile title and one-tap lesson selector');
$assert(str_contains($practice, 'fetchWithTimeout') && substr_count($practice, 'fetchWithTimeout(') >= 4, 'Practice actions use responsive request watchdogs');
$assert(str_contains($practice, 'data-wf175-toggle="#quick-tool"'), 'Quick Help is optional and mobile-toggleable');
$assert(str_contains($roadmap, 'wf175-roadmap-tabs') && str_contains($roadmap, 'wf175RoadmapStickyContinue'), 'Roadmap has progress tabs and thumb-reachable continue action');
$assert(str_contains($footer, 'wf175-learning-bottom') && str_contains($footer, 'free-ai-english-practice.php'), 'Approved five-item mobile learning navigation is opt-in');
$assert(str_contains($header, '$pageUltimateStyles'), 'Page-scoped ultimate stylesheet hook exists after global compatibility CSS');
$assert(str_contains($css, '@media (max-width:760px)') && str_contains($css, 'min-height:48px') && str_contains($css, 'touch-action:manipulation'), 'Mobile CSS enforces large fast touch controls');
$assert(str_contains($css, 'page-spoken-materials') && str_contains($css, 'page-free-ai-english-practice') && str_contains($css, 'page-learning-roadmap'), 'Phase 175 CSS is scoped to the three requested pages');
$assert(str_contains($js, 'setupSpokenMaterials') && str_contains($js, 'setupPracticeMaterials') && str_contains($js, 'setupRoadmap'), 'Phase 175 JS covers all three requested page flows');
preg_match('/wellfare-spoken-static-v(\d+)/', $sw, $p175SwMatch);
$p175SwVersion = isset($p175SwMatch[1]) ? (int)$p175SwMatch[1] : 0;
$assert($p175SwVersion >= 175 && str_contains($sw, 'phase175-mobile-learning.css') && str_contains($sw, 'phase175-mobile-learning.js'), 'Service Worker cache v175 or newer includes Phase 175 assets');

$failed = array_filter($checks, static fn(array $row): bool => !$row[0]);
echo PHP_EOL . count($checks) . ' checks, ' . count($failed) . ' failed.' . PHP_EOL;
exit($failed ? 1 : 0);
