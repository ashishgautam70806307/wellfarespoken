<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$seoFields = [
    'home' => 'Home Page',
    'about' => 'About Page',
    'courses' => 'Courses Page',
    'admission' => 'Admission Page',
    'gallery' => 'Gallery Page',
    'reviews' => 'Reviews Page',
    'contact' => 'Contact Page'
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    foreach ($seoFields as $key => $label) {
        save_app_setting('seo_' . $key . '_title', trim($_POST['seo_' . $key . '_title'] ?? ''));
        save_app_setting('seo_' . $key . '_description', trim($_POST['seo_' . $key . '_description'] ?? ''));
    }
    flash('success', 'SEO settings updated.');
    redirect('seo.php');
}
?>
<div class="admin-top"><div><h1>SEO Meta Settings</h1><p>Control browser titles and search descriptions for important website pages.</p></div><a class="btn btn-soft" href="../index.php" target="_blank">View Website</a></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<form class="form-box seo-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-grid">
<div class="form-section-title"><span>🔎</span>Page SEO Details</div>
<?php foreach ($seoFields as $key => $label): ?>
<div class="field full seo-row"><h3><?= e($label) ?></h3><label>Meta Title</label><input name="seo_<?= e($key) ?>_title" value="<?= e(app_setting('seo_' . $key . '_title', '')) ?>" maxlength="180"><small class="help">Best length: 50 to 60 characters.</small><label>Meta Description</label><textarea name="seo_<?= e($key) ?>_description" maxlength="300"><?= e(app_setting('seo_' . $key . '_description', '')) ?></textarea><small class="help">Best length: 140 to 160 characters.</small></div>
<?php endforeach; ?>
<div class="field full"><button class="btn btn-primary">Save SEO Settings</button></div>
</div></form>
<?php require_once __DIR__ . '/_footer.php'; ?>
