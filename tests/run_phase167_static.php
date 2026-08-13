<?php
$root = dirname(__DIR__);
$checks = [];
function p167(bool $ok, string $label): void {
    global $checks;
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
}
$header = file_get_contents($root . '/includes/header.php') ?: '';
$css = file_get_contents($root . '/assets/css/phase167-mobile-layout-safety.css') ?: '';
$sw = file_get_contents($root . '/sw.js') ?: '';
$weekly = file_get_contents($root . '/weekly-test.php') ?: '';
$dashboard = file_get_contents($root . '/student-dashboard.php') ?: '';

p167(str_contains($header, 'phase167-mobile-layout-safety.css'), 'Final mobile safety stylesheet loads globally');
p167(str_contains($css, 'body.page-weekly-test .wf145-history-grid') && str_contains($css, 'grid-template-columns: minmax(0, 1fr) !important'), 'Weekly Test history becomes one readable mobile column');
p167(str_contains($css, 'body.page-student-dashboard .wf145-history-grid'), 'Student Dashboard history uses the same safe mobile layout');
p167(str_contains($css, 'body.page-index .wf126-review-card') && str_contains($css, 'flex: 0 0 84% !important'), 'Home review slider has old-browser flex fallback');
p167(str_contains($weekly, 'wf145-history-grid') && str_contains($dashboard, 'wf145-history-grid'), 'Shared history markup is still present on both student pages');
p167(!preg_match('/flex\s*:\s*0\s+0\s+min\s*\(/i', implode("\n", array_map(static fn($f) => @file_get_contents($f) ?: '', glob($root . '/assets/css/*.css') ?: []))), 'No source CSS still uses min() inside flex-basis');
p167(str_contains($sw, "wellfare-spoken-static-v167"), 'Service Worker cache bumped to v167');
p167(str_contains($sw, "phase167-mobile-layout-safety.min.css"), 'Service Worker pre-caches Phase 167 CSS');
p167(is_file($root . '/assets/css/phase167-mobile-layout-safety.min.css'), 'Production minified Phase 167 CSS exists');

exit(in_array(false, $checks, true) ? 1 : 0);
