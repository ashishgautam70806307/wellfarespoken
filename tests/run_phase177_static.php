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
    $add('Original preserved: ' . $file, is_file($path) && hash_file('sha256', $path) === $hash);
}

$pageHashes = [
    'spoken-materials.php' => '84e6986ed82fd1dc5dc974286943e051ee03de3ef3f6c4c96b4f9bd0a9503501',
    'free-ai-english-practice.php' => '848b9e6da9c6d27ef0d21656cce81b6af5b51ec0b1a4042c738d89018741c152',
    'learning-roadmap.php' => 'a1343730ea14b4aee52e87bfd04056a0cca90c234443dc1267583df4b297aa67',
];
foreach ($pageHashes as $file => $hash) {
    $path = $root . '/' . $file;
    $content = is_file($path) ? (string)file_get_contents($path) : '';
    $normalized = str_replace("'assets/css/phase177-mobile-learning-ui.css', ", '', $content);
    $normalized = str_replace(", 'assets/css/phase177-mobile-learning-ui.css'", '', $normalized);
    $normalized = str_replace("\n\$page_final_styles = ['assets/css/phase177-mobile-learning-ui.css'];", '', $normalized);
    $add('Feature source restored: ' . $file, hash('sha256', $normalized) === $hash);
    $add('Phase177 CSS attached: ' . $file, str_contains($content, 'phase177-mobile-learning-ui.css'));
}

$combined = '';
foreach (['spoken-materials.php','free-ai-english-practice.php','learning-roadmap.php','includes/header.php','includes/footer.php'] as $file) {
    $combined .= "\n" . (string)file_get_contents($root . '/' . $file);
}
$add('No Phase175 mobile app markup remains', !str_contains($combined, 'wf175-') && !str_contains($combined, 'phase175-mobile-learning'));
$add('No Phase176 alternate app header/nav remains', !str_contains($combined, 'wf176-') && !str_contains($combined, 'mobile_learning_nav') && !str_contains($combined, 'page_ultimate_styles'));

$cssPath = $root . '/assets/css/phase177-mobile-learning-ui.css';
$css = is_file($cssPath) ? (string)file_get_contents($cssPath) : '';
$add('Phase177 is mobile scoped', str_contains($css, '@media (max-width: 760px)'));
$add('Shared header is styled, not replaced', str_contains($css, '.wf127-header') && str_contains($css, '.wf127-menu-toggle'));
$add('Shared mobile bottom nav retained', str_contains($css, '.wf127-mobile-bottom'));
$add('Contact dock kept without content overlap', str_contains($css, '.wf127-contact-dock') && str_contains($css, 'position: relative !important'));
$add('Spoken original controls styled', str_contains($css, '.wf143-audio-actions') && str_contains($css, '#practiceAnswer') && str_contains($css, '.wf143-navigation'));
$add('Practice original lesson controls styled', str_contains($css, '.practice-lesson-panel') && str_contains($css, '.lesson-pick-btn') && str_contains($css, '.practice-controls'));
$add('Roadmap original feature cards styled', str_contains($css, '.rm126-level-card') && str_contains($css, '.rm126-tools'));
$add('Readable answer input', str_contains($css, 'font-size: 16px !important'));
$add('Short-height nav avoids overlap', str_contains($css, '(max-height: 600px)') && str_contains($css, 'position: relative !important'));

$sw = (string)file_get_contents($root . '/sw.js');
$add('Service Worker v177', str_contains($sw, "wellfare-spoken-static-v177"));
$add('Phase177 CSS precached', str_contains($sw, './assets/css/phase177-mobile-learning-ui.css'));
$add('Removed Phase175/176 assets not precached', !str_contains($sw, 'phase175-mobile-learning') && !str_contains($sw, 'phase176-mobile-reference-match'));

$failed = 0;
foreach ($checks as [$name, $ok, $detail]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . ($detail !== '' ? ' - ' . $detail : '') . PHP_EOL;
    if (!$ok) $failed++;
}
echo PHP_EOL . (count($checks) - $failed) . '/' . count($checks) . ' checks passed.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
