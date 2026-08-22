<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$add = static function (string $name, bool $ok, string $detail = '') use (&$checks): void {
    $checks[] = [$name, $ok, $detail];
};

$expectedHashes = [
    'includes/header.php' => '629aa565b26afa711c9fc09217cf0496c22e3a6a18884fb81913abfaf17493d4',
    'includes/footer.php' => 'f88794a390616502263af7b11e5c6c90f445bcf1d30bfacc54fd5fdc0fa378bb',
    'assets/js/phase170-spoken-practice.js' => 'c11e38a04e7bd6e79320ddb61e2e8c4960cbb797ca15550c72cffa1bb22b7b1b',
];
foreach ($expectedHashes as $file => $hash) {
    $path = $root . '/' . $file;
    $add('Preserved original: ' . $file, is_file($path) && hash_file('sha256', $path) === $hash);
}

$pages = [
    'spoken-materials.php',
    'free-ai-english-practice.php',
    'learning-roadmap.php',
];
foreach ($pages as $file) {
    $content = (string)file_get_contents($root . '/' . $file);
    $add('Phase178 stylesheet attached: ' . $file, str_contains($content, 'phase178-mobile-learning-premium.css'));
    $add('No Phase175/176 alternate shell in ' . $file,
        !str_contains($content, 'wf175-') && !str_contains($content, 'wf176-') && !str_contains($content, 'page_ultimate_styles'));
}

$spoken = (string)file_get_contents($root . '/spoken-materials.php');
foreach ([
    'data-goal="speak"',
    'data-goal="hindi_to_english"',
    'data-goal="english_to_hindi"',
    'data-goal="revision"',
    'id="practiceHandsfree"',
    'id="practiceListen"',
    'id="practiceSpeak"',
    'id="practiceStop"',
    'id="practiceAnswer"',
    'id="practiceCheck"',
    'id="practiceClear"',
    'id="practicePrevious"',
    'id="practiceNext"',
    'id="practiceChangeMode"',
] as $marker) {
    $add('Spoken feature preserved: ' . $marker, str_contains($spoken, $marker));
}

$practice = (string)file_get_contents($root . '/free-ai-english-practice.php');
foreach ([
    'id="quickPracticeForm"',
    'value="sentence_correction"',
    'value="hindi_to_english"',
    'value="english_to_hindi"',
    'id="quickMicBtn"',
    'class="lesson-pick-btn"',
    'id="appVoiceBtn"',
    'id="studentAnswer"',
    'id="prevQuestionBtn"',
    'id="checkAnswerBtn"',
    'id="nextQuestionBtn"',
] as $marker) {
    $add('Practice feature preserved: ' . $marker, str_contains($practice, $marker));
}
$add('Practice request watchdog added', str_contains($practice, 'function fetchWithTimeout') && substr_count($practice, 'fetchWithTimeout(') >= 4 && str_contains($practice, '12000'));

$roadmap = (string)file_get_contents($root . '/learning-roadmap.php');
foreach ([
    'id="roadmapContinueBtn"',
    'class="rm126-summary"',
    'class="rm126-stages"',
    'class="rm126-level-card"',
    'class="rm126-open"',
    'class="rm126-locked"',
    'id="roadmapResetProgress"',
    'admin/roadmap.php',
] as $marker) {
    $add('Roadmap feature preserved: ' . $marker, str_contains($roadmap, $marker));
}

$cssPath = $root . '/assets/css/phase178-mobile-learning-premium.css';
$css = is_file($cssPath) ? (string)file_get_contents($cssPath) : '';
$add('Phase178 CSS mobile-only scoped', str_contains($css, '@media (max-width: 760px)'));
$add('Phase178 uses project navy token', str_contains($css, '--wf-color-navy-950') && str_contains($css, '#04162f'));
$add('Phase178 uses project blue token', str_contains($css, '--wf-color-blue-700') && str_contains($css, '#174e8f'));
$add('Phase178 uses project gold token', str_contains($css, '--wf-color-gold-500') && str_contains($css, '#d8a62d'));
$add('Phase178 uses project soft surface', str_contains($css, '--wf-color-soft') && str_contains($css, '#f4f7fb'));
$add('Mobile touch targets are 46-48px', str_contains($css, 'min-height:48px') && str_contains($css, 'min-height:46px'));
$add('No unreadable sub-11px typography', !preg_match('/font-size\s*:\s*(?:[0-9](?:\.[0-9]+)?|10(?:\.[0-9]+)?)px/i', $css));
$add('Spoken original control families styled', str_contains($css, '.wf143-mode-card') && str_contains($css, '.wf143-audio-actions') && str_contains($css, '.wf143-answer-actions') && str_contains($css, '.wf143-navigation'));
$add('Practice original feature families styled', str_contains($css, '.ai-simple-tool') && str_contains($css, '.lesson-pick-btn') && str_contains($css, '.practice-workspace') && str_contains($css, '.practice-controls'));
$add('Roadmap original feature families styled', str_contains($css, '.rm126-progress-card') && str_contains($css, '.rm126-level-card') && str_contains($css, '.rm126-tools'));
$add('Contact dock retained without floating overlap', str_contains($css, '.wf127-contact-dock') && str_contains($css, 'position: relative !important'));
$add('Short-height controls avoid sticky overlap', str_contains($css, '(max-height: 600px)') && str_contains($css, 'position:relative !important'));
$add('Clear action text is not hidden', !str_contains($css, '#practiceClear span { display:none'));
$add('Roadmap stage count remains visible', !str_contains($css, '.rm126-stage-count { display:none'));

$sw = (string)file_get_contents($root . '/sw.js');
$add('Service Worker v178', str_contains($sw, "wellfare-spoken-static-v178"));
$add('Phase178 stylesheet precached', str_contains($sw, './assets/css/phase178-mobile-learning-premium.css'));
$add('Phase175/176 alternate assets not precached', !str_contains($sw, 'phase175-mobile-learning') && !str_contains($sw, 'phase176-mobile-reference-match'));

$failed = 0;
foreach ($checks as [$name, $ok, $detail]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . ($detail !== '' ? ' - ' . $detail : '') . PHP_EOL;
    if (!$ok) $failed++;
}
echo PHP_EOL . (count($checks) - $failed) . '/' . count($checks) . ' checks passed.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
