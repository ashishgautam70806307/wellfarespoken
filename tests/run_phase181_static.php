<?php
require_once __DIR__ . '/../includes/functions.php';

$passed = 0;
$failed = 0;
$check = function (bool $ok, string $label) use (&$passed, &$failed): void {
    if ($ok) { $passed++; echo "PASS: {$label}\n"; }
    else { $failed++; echo "FAIL: {$label}\n"; }
};

$check(managed_upload_normalize_path('assets/uploads/gallery/gallery-20260818-190000-abcdef1234567890.jpg') === 'assets/uploads/gallery/gallery-20260818-190000-abcdef1234567890.jpg', 'Managed public upload path normalizes');
$check(managed_upload_normalize_path('https://example.com/photo.jpg') === '', 'Remote URL is never a managed local deletion target');
$check(managed_upload_normalize_path('../assets/uploads/gallery/x.jpg') === '', 'Traversal path is rejected');
$check(managed_upload_filename_is_generated('assets/uploads/gallery/gallery-20260818-190000-abcdef1234567890.jpg'), 'Current generated upload filename is recognized');
$check(managed_upload_filename_is_generated('assets/uploads/brand/logo_20260708_164300_66b228d8.png'), 'Legacy generated upload filename is recognized');
$check(!managed_upload_filename_is_generated('assets/uploads/banners/home-banner-speaking-desktop.webp'), 'Bundled/static upload-style filename is not auto-deletable');
$staticRefs = managed_upload_static_reference_set();
$check(isset($staticRefs['assets/uploads/brand/logo_20260708_164300_66b228d8.png']), 'Hard-coded runtime/seed upload is protected as a static reference');

$files = [
    'courses' => file_get_contents(__DIR__ . '/../admin/courses.php'),
    'faculty' => file_get_contents(__DIR__ . '/../admin/faculty.php'),
    'gallery' => file_get_contents(__DIR__ . '/../admin/gallery.php'),
    'testimonials' => file_get_contents(__DIR__ . '/../admin/testimonials.php'),
    'settings' => file_get_contents(__DIR__ . '/../admin/settings.php'),
    'hero' => file_get_contents(__DIR__ . '/../admin/hero-banners.php'),
    'admissions' => file_get_contents(__DIR__ . '/../admin/admissions.php'),
    'system' => file_get_contents(__DIR__ . '/../admin/system-check.php'),
];

$check(str_contains($files['courses'], 'managed_upload_cleanup($oldImage)') && str_contains($files['courses'], 'remove_course_image'), 'Course replace/delete lifecycle cleans old image and supports explicit removal');
$check(str_contains($files['faculty'], 'managed_upload_cleanup_many($oldImages') && str_contains($files['faculty'], 'remove_faculty_photo'), 'Faculty single/bulk delete and replace lifecycle is covered');
$check(str_contains($files['gallery'], 'managed_upload_cleanup($oldImage)') && str_contains($files['gallery'], 'remove_gallery_image'), 'Gallery delete/replace/removal lifecycle is covered');
$check(str_contains($files['testimonials'], 'managed_upload_cleanup($oldImage)') && str_contains($files['testimonials'], 'remove_student_image'), 'Testimonial delete/replace/removal lifecycle is covered');
$check(str_contains($files['settings'], "['site_logo','site_favicon','director_photo']") && str_contains($files['settings'], 'remove_site_logo') && str_contains($files['settings'], 'managed_upload_cleanup($oldMedia[$key])'), 'Logo/favicon/director replace/removal lifecycle is covered');
$check(str_contains($files['hero'], 'managed_upload_cleanup_many(array_values($oldImages))') && str_contains($files['hero'], 'remove_mobile_image') && str_contains($files['hero'], 'remove_fallback_image'), 'Hero fallback/desktop/mobile replacement and removal lifecycle is covered');
$check(str_contains($files['admissions'], 'managed_upload_cleanup($oldPhoto)') && str_contains($files['admissions'], 'remove_student_photo'), 'Admission photo replacement/removal lifecycle is covered');
$deletePos = strpos($files['admissions'], "if (\$action === 'delete')");
$savePos = strpos($files['admissions'], "if (\$action === 'save')");
$deleteBlock = ($deletePos !== false && $savePos !== false) ? substr($files['admissions'], $deletePos, $savePos - $deletePos) : '';
$check($deleteBlock !== '' && !str_contains($deleteBlock, 'managed_upload_cleanup'), 'Admission soft-delete intentionally retains historical student photo');
$check(str_contains($files['system'], 'cleanup_orphan_uploads') && str_contains($files['system'], 'managed_upload_cleanup_orphans(48 * 3600, 500)'), 'System Check provides guarded stale orphan cleanup');

$functions = file_get_contents(__DIR__ . '/../includes/functions.php');
$check(str_contains($functions, 'managed_upload_reference_count') && str_contains($functions, "['hero_banners', 'desktop_image_url']") && str_contains($functions, "['material_assets', 'file_path']"), 'Central reference checker covers all persistent current image/file columns');
$check(str_contains($functions, 'if (is_file($target)) @unlink($target);') && str_contains($functions, 'A failed encoder may leave a partial target behind'), 'Failed image re-encode cannot leave partial upload targets');
$check(str_contains($functions, 'if (managed_upload_reference_count($normalized) > 0) return false;'), 'Physical unlink is blocked while any DB/static reference remains');

printf("Phase 181 static checks: %d passed, %d failed\n", $passed, $failed);
exit($failed === 0 ? 0 : 1);
