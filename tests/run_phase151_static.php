<?php
$root = dirname(__DIR__);
$fail = 0;
$ok = static function(bool $value, string $label) use (&$fail): void {
    echo ($value ? 'PASS' : 'FAIL') . ' - ' . $label . PHP_EOL;
    if (!$value) $fail++;
};

require_once $root . '/includes/functions.php';

$ok(secure_image_allowed_extensions() === ['jpg','jpeg','png','webp'], 'Server image allowlist is exactly JPG/JPEG/PNG/WEBP');
$ok(secure_image_filename_is_allowed('photo.jpg') && secure_image_filename_is_allowed('photo.JPEG') && secure_image_filename_is_allowed('photo.webp'), 'Normal approved image extensions pass');
$ok(!secure_image_filename_is_allowed('shell.php') && !secure_image_filename_is_allowed('shell.php.jpg') && !secure_image_filename_is_allowed('.htaccess') && !secure_image_filename_is_allowed('vector.svg') && !secure_image_filename_is_allowed('animation.gif') && !secure_image_filename_is_allowed('favicon.ico'), 'Executable/GIF/SVG/ICO upload names are rejected');
$ok(secure_image_extension_for_mime('image/jpeg') === 'jpg' && secure_image_extension_for_mime('image/png') === 'png' && secure_image_extension_for_mime('image/webp') === 'webp' && secure_image_extension_for_mime('application/x-httpd-php') === '', 'Server MIME allowlist rejects non-image executable MIME');

$functions = file_get_contents($root . '/includes/functions.php');
$faculty = file_get_contents($root . '/admin/faculty.php');
$hero = file_get_contents($root . '/admin/hero-banners.php');
$home = file_get_contents($root . '/index.php');
$mainJs = file_get_contents($root . '/assets/js/main.js');
$homeCss = file_get_contents($root . '/assets/css/phase126-home.css');
$sw = file_get_contents($root . '/sw.js');

$ok(strpos($functions, 'Options -Indexes -ExecCGI') !== false && strpos($functions, 'RemoveHandler .php') !== false, 'Public upload directories disable executable handlers');
$ok(strpos($functions, 'is_uploaded_file($tmp)') !== false && strpos($functions, 'getimagesize($tmp)') !== false && strpos($functions, 'secure_image_reencode') !== false, 'Image upload validates upload source, decoded dimensions and sanitizes pixels when GD is available');
$ok(strpos($faculty, "secure_image_upload(\$file, 'faculty'") !== false && strpos($faculty, 'move_uploaded_file') === false, 'Faculty upload uses the same central secure image pipeline');
$ok(strpos($mainJs, "['png','jpg','jpeg','webp']") !== false && strpos($mainJs, "'image/webp'") !== false, 'Client image picker matches the server allowlist');

$imageFormFiles = ['admin/admissions.php','admin/courses.php','admin/gallery.php','admin/testimonials.php','admin/faculty.php','admin/settings.php','admin/hero-banners.php'];
$badAccept = [];
foreach ($imageFormFiles as $file) {
    $text = file_get_contents($root . '/' . $file);
    if (preg_match('/type="file"[^>]*accept="[^"]*(?:gif|svg|ico|image\/\*)/i', $text)) $badAccept[] = $file;
}
$ok($badAccept === [], 'All Admin image pickers expose only JPG/JPEG/PNG/WEBP');

$ok(strpos($hero, 'max(0, min(85') !== false && strpos($hero, 'type="range" min="0" max="85"') !== false, 'Hero overlay supports true 0% darkness');
$ok(strpos($hero, 'No individual field is required') !== false && strpos($hero, 'Banner title is required') === false && strpos($hero, 'Please upload at least a desktop') === false, 'Hero supports image-only, text-only or combined content without an individually required field');
$ok(strpos($home, "'has_media' => \$hasMedia") !== false && strpos($home, "empty(\$slide['has_media']) ? 'is-text-only'") !== false && strpos($home, 'max(0, min(85') !== false, 'Homepage renderer supports text-only banners and 0% overlay');
$ok(strpos($homeCss, '.wf126-slide.is-text-only') !== false && strpos($homeCss, 'var(--overlay-strength)') !== false, 'Homepage CSS has a text-only visual and dynamic overlay variable');
$ok(strpos($sw, 'wellfare-spoken-static-v151') !== false, 'Service-worker cache version is Phase 151');

$phpFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) if ($f->isFile() && strtolower($f->getExtension()) === 'php') $phpFiles[] = $f->getPathname();
$suspicious = [];
foreach ($phpFiles as $file) {
    $text = file_get_contents($file);
    if (preg_match('/\b(?:eval|shell_exec|system|passthru|proc_open|popen)\s*\(/i', $text)) $suspicious[] = str_replace($root . '/', '', $file);
}
$ok($suspicious === [], 'No direct eval/shell/process execution primitive found in project PHP');

if ($fail) {
    echo PHP_EOL . $fail . ' Phase 151 static check(s) failed.' . PHP_EOL;
    exit(1);
}
echo PHP_EOL . 'All Phase 151 static checks passed.' . PHP_EOL;
