<?php
require_once __DIR__ . '/_header.php';
ensure_schema_updates();

$responsiveColumnsReady = column_exists('hero_banners', 'desktop_image_url')
    && column_exists('hero_banners', 'mobile_image_url')
    && column_exists('hero_banners', 'show_content')
    && column_exists('hero_banners', 'content_position')
    && column_exists('hero_banners', 'overlay_strength');

if (isset($_GET['delete']) && csrf_validate($_GET['token'] ?? '')) {
    $id = (int)$_GET['delete'];
    db()->prepare("UPDATE hero_banners SET published='No' WHERE id=?")->execute([$id]);
    flash('success', 'Hero banner unpublished successfully.');
    redirect('hero-banners.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $pageKey = trim($_POST['page_key'] ?? 'home') ?: 'home';
    $showContent = ($_POST['show_content'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
    $title = trim($_POST['title'] ?? '');
    if ($title === '' && $showContent === 'No') {
        $title = 'Image Banner';
    }

    $fallbackImage = trim($_POST['existing_image_url'] ?? '');
    $desktopImage = trim($_POST['existing_desktop_image_url'] ?? '');
    $mobileImage = trim($_POST['existing_mobile_image_url'] ?? '');

    try {
        $uploadedFallback = upload_hero_banner_image($_FILES['banner_image'] ?? [], 'general');
        $uploadedDesktop = upload_hero_banner_image($_FILES['desktop_banner_image'] ?? [], 'desktop');
        $uploadedMobile = upload_hero_banner_image($_FILES['mobile_banner_image'] ?? [], 'mobile');
        if ($uploadedFallback) $fallbackImage = $uploadedFallback;
        if ($uploadedDesktop) $desktopImage = $uploadedDesktop;
        if ($uploadedMobile) $mobileImage = $uploadedMobile;

        if ($desktopImage === '') $desktopImage = $fallbackImage;
        if ($fallbackImage === '') $fallbackImage = $desktopImage;

        if ($title === '') {
            flash('error', 'Banner title is required when overlay content is enabled.');
        } elseif ($fallbackImage === '' && $desktopImage === '') {
            flash('error', 'Please upload at least a desktop or fallback banner image.');
        } else {
            $baseData = [
                $pageKey,
                trim($_POST['eyebrow'] ?? ''),
                $title,
                trim($_POST['subtitle'] ?? ''),
                $fallbackImage,
                trim($_POST['image_alt'] ?? ''),
                trim($_POST['badge_one'] ?? ''),
                trim($_POST['badge_two'] ?? ''),
                trim($_POST['stat_one_label'] ?? ''),
                trim($_POST['stat_one_value'] ?? ''),
                trim($_POST['stat_two_label'] ?? ''),
                trim($_POST['stat_two_value'] ?? ''),
                trim($_POST['primary_text'] ?? ''),
                trim($_POST['primary_url'] ?? ''),
                trim($_POST['secondary_text'] ?? ''),
                trim($_POST['secondary_url'] ?? ''),
                (int)($_POST['sort_order'] ?? 0),
                ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes',
            ];

            if ($responsiveColumnsReady) {
                $rawContentPosition = strtolower(trim((string)($_POST['content_position'] ?? 'left')));
                $contentPosition = in_array($rawContentPosition, ['left', 'center', 'right'], true)
                    ? $rawContentPosition
                    : 'left';
                $overlayStrength = max(15, min(85, (int)($_POST['overlay_strength'] ?? 58)));

                $responsiveData = [
                    $pageKey,
                    trim($_POST['eyebrow'] ?? ''),
                    $title,
                    trim($_POST['subtitle'] ?? ''),
                    $fallbackImage,
                    $desktopImage,
                    $mobileImage,
                    trim($_POST['image_alt'] ?? ''),
                    $showContent,
                    $contentPosition,
                    $overlayStrength,
                    trim($_POST['badge_one'] ?? ''),
                    trim($_POST['badge_two'] ?? ''),
                    trim($_POST['stat_one_label'] ?? ''),
                    trim($_POST['stat_one_value'] ?? ''),
                    trim($_POST['stat_two_label'] ?? ''),
                    trim($_POST['stat_two_value'] ?? ''),
                    trim($_POST['primary_text'] ?? ''),
                    trim($_POST['primary_url'] ?? ''),
                    trim($_POST['secondary_text'] ?? ''),
                    trim($_POST['secondary_url'] ?? ''),
                    (int)($_POST['sort_order'] ?? 0),
                    ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes',
                ];

                if ($id) {
                    $responsiveData[] = $id;
                    db()->prepare('UPDATE hero_banners SET page_key=?, eyebrow=?, title=?, subtitle=?, image_url=?, desktop_image_url=?, mobile_image_url=?, image_alt=?, show_content=?, content_position=?, overlay_strength=?, badge_one=?, badge_two=?, stat_one_label=?, stat_one_value=?, stat_two_label=?, stat_two_value=?, primary_text=?, primary_url=?, secondary_text=?, secondary_url=?, sort_order=?, published=? WHERE id=?')->execute($responsiveData);
                } else {
                    db()->prepare('INSERT INTO hero_banners (page_key, eyebrow, title, subtitle, image_url, desktop_image_url, mobile_image_url, image_alt, show_content, content_position, overlay_strength, badge_one, badge_two, stat_one_label, stat_one_value, stat_two_label, stat_two_value, primary_text, primary_url, secondary_text, secondary_url, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($responsiveData);
                }
            } else {
                if ($id) {
                    $baseData[] = $id;
                    db()->prepare('UPDATE hero_banners SET page_key=?, eyebrow=?, title=?, subtitle=?, image_url=?, image_alt=?, badge_one=?, badge_two=?, stat_one_label=?, stat_one_value=?, stat_two_label=?, stat_two_value=?, primary_text=?, primary_url=?, secondary_text=?, secondary_url=?, sort_order=?, published=? WHERE id=?')->execute($baseData);
                } else {
                    db()->prepare('INSERT INTO hero_banners (page_key, eyebrow, title, subtitle, image_url, image_alt, badge_one, badge_two, stat_one_label, stat_one_value, stat_two_label, stat_two_value, primary_text, primary_url, secondary_text, secondary_url, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($baseData);
                }
            }

            flash('success', 'Responsive hero banner saved successfully.');
            redirect('hero-banners.php');
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM hero_banners WHERE id=? LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$banners = db()->query('SELECT * FROM hero_banners ORDER BY page_key ASC, sort_order ASC, id DESC')->fetchAll();
$pageOptions = [
    'home' => 'Home Hero',
    'practice' => 'Practice Room Hero',
    'about' => 'About Page',
    'courses' => 'Courses Page',
    'admission' => 'Admission Page',
    'gallery' => 'Gallery Page',
    'reviews' => 'Reviews Page',
    'contact' => 'Contact Page',
];
?>
<div class="admin-top">
    <div>
        <h1>Responsive Hero Banners</h1>
        <p>Upload separate desktop and mobile artwork so the banner stays clear on every device.</p>
    </div>
    <a class="btn btn-primary" href="../index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Website</a>
</div>

<?php if (!$responsiveColumnsReady): ?>
    <div class="alert alert-error">
        Responsive banner columns are not installed yet. Run <code>sql/phase126_home_roadmap_upgrade.sql</code> once, or temporarily enable schema updates and open System Check.
    </div>
<?php endif; ?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<div class="grid-2 hero-admin-grid-v126">
    <div class="panel-card">
        <div class="toolbar">
            <div><h2><?= $edit ? 'Edit' : 'Add' ?> Hero Banner</h2><p>Keep banner text short. Use image-only mode when artwork already contains text.</p></div>
        </div>
        <form method="post" enctype="multipart/form-data" class="form-grid" id="responsiveBannerForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>">
            <input type="hidden" name="existing_image_url" value="<?= e($edit['image_url'] ?? '') ?>">
            <input type="hidden" name="existing_desktop_image_url" value="<?= e($edit['desktop_image_url'] ?? '') ?>">
            <input type="hidden" name="existing_mobile_image_url" value="<?= e($edit['mobile_image_url'] ?? '') ?>">

            <label>Page / Placement
                <select name="page_key"><?php foreach ($pageOptions as $key => $label): ?><option value="<?= e($key) ?>" <?= (($edit['page_key'] ?? 'home') === $key) ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
            </label>
            <label>Published
                <select name="published"><option <?= (($edit['published'] ?? 'Yes') === 'Yes') ? 'selected' : '' ?>>Yes</option><option <?= (($edit['published'] ?? '') === 'No') ? 'selected' : '' ?>>No</option></select>
            </label>

            <div class="full hero-upload-guide-v126">
                <article><i class="fa-solid fa-display"></i><div><b>Desktop banner</b><span>Recommended 1920×720 or 1600×600</span></div></article>
                <article><i class="fa-solid fa-mobile-screen-button"></i><div><b>Mobile banner</b><span>Recommended 900×1200 or 750×1000</span></div></article>
            </div>

            <label class="full">Desktop / Laptop Image
                <input type="file" name="desktop_banner_image" accept="image/jpeg,image/png,image/webp" data-preview-target="desktopBannerPreview">
            </label>
            <div class="full image-preview hero-preview-v126 hero-preview-desktop-v126" id="desktopBannerPreview">
                <?php $desktopPreview = $edit['desktop_image_url'] ?? $edit['image_url'] ?? ''; ?>
                <?php if ($desktopPreview): ?><img src="../<?= e($desktopPreview) ?>" alt="Desktop preview"><?php else: ?><span><i class="fa-solid fa-display"></i> Desktop preview</span><?php endif; ?>
            </div>

            <label class="full">Mobile Image
                <input type="file" name="mobile_banner_image" accept="image/jpeg,image/png,image/webp" data-preview-target="mobileBannerPreview">
            </label>
            <div class="full image-preview hero-preview-v126 hero-preview-mobile-v126" id="mobileBannerPreview">
                <?php $mobilePreview = $edit['mobile_image_url'] ?? ''; ?>
                <?php if ($mobilePreview): ?><img src="../<?= e($mobilePreview) ?>" alt="Mobile preview"><?php else: ?><span><i class="fa-solid fa-mobile-screen-button"></i> Mobile preview; desktop image will be used as fallback</span><?php endif; ?>
            </div>

            <details class="full hero-fallback-v126">
                <summary><i class="fa-solid fa-image"></i> Legacy fallback image</summary>
                <label>Fallback Image<input type="file" name="banner_image" accept="image/jpeg,image/png,image/webp" data-preview-target="fallbackBannerPreview"></label>
                <div class="image-preview" id="fallbackBannerPreview"><?php if (!empty($edit['image_url'])): ?><img src="../<?= e($edit['image_url']) ?>" alt="Fallback preview"><?php else: ?>Optional fallback preview<?php endif; ?></div>
            </details>

            <?php if ($responsiveColumnsReady): ?>
                <label>Show text over banner?
                    <select name="show_content" id="showBannerContent"><option value="Yes" <?= (($edit['show_content'] ?? 'Yes') === 'Yes') ? 'selected' : '' ?>>Yes</option><option value="No" <?= (($edit['show_content'] ?? '') === 'No') ? 'selected' : '' ?>>No — image only</option></select>
                </label>
                <label>Text position
                    <select name="content_position"><option value="left" <?= (($edit['content_position'] ?? 'left') === 'left') ? 'selected' : '' ?>>Left</option><option value="center" <?= (($edit['content_position'] ?? '') === 'center') ? 'selected' : '' ?>>Center</option><option value="right" <?= (($edit['content_position'] ?? '') === 'right') ? 'selected' : '' ?>>Right</option></select>
                </label>
                <label class="full">Overlay darkness <span id="overlayStrengthValue"><?= e((string)($edit['overlay_strength'] ?? 58)) ?>%</span>
                    <input type="range" min="15" max="85" step="1" name="overlay_strength" value="<?= e((string)($edit['overlay_strength'] ?? 58)) ?>" id="overlayStrengthRange">
                </label>
            <?php else: ?>
                <input type="hidden" name="show_content" value="Yes">
                <input type="hidden" name="content_position" value="left">
                <input type="hidden" name="overlay_strength" value="58">
            <?php endif; ?>

            <div class="full banner-copy-fields-v126" id="bannerCopyFields">
                <div class="form-grid">
                    <label>Small Label<input name="eyebrow" value="<?= e($edit['eyebrow'] ?? '') ?>" maxlength="52" placeholder="Live Spoken English"></label>
                    <label>Image Alt Text<input name="image_alt" value="<?= e($edit['image_alt'] ?? '') ?>" maxlength="180"></label>
                    <label class="full">Hero Title<input name="title" value="<?= e($edit['title'] ?? '') ?>" maxlength="76" placeholder="Speak English with confidence"></label>
                    <label class="full">Short Subtitle<textarea name="subtitle" rows="2" maxlength="160"><?= e($edit['subtitle'] ?? '') ?></textarea></label>
                    <label>Primary Button Text<input name="primary_text" value="<?= e($edit['primary_text'] ?? '') ?>" maxlength="30"></label>
                    <label>Primary Button URL<input name="primary_url" value="<?= e($edit['primary_url'] ?? '') ?>"></label>
                    <label>Secondary Button Text<input name="secondary_text" value="<?= e($edit['secondary_text'] ?? '') ?>" maxlength="30"></label>
                    <label>Secondary Button URL<input name="secondary_url" value="<?= e($edit['secondary_url'] ?? '') ?>"></label>
                </div>
            </div>

            <input type="hidden" name="badge_one" value="<?= e($edit['badge_one'] ?? '') ?>">
            <input type="hidden" name="badge_two" value="<?= e($edit['badge_two'] ?? '') ?>">
            <input type="hidden" name="stat_one_label" value="<?= e($edit['stat_one_label'] ?? '') ?>">
            <input type="hidden" name="stat_one_value" value="<?= e($edit['stat_one_value'] ?? '') ?>">
            <input type="hidden" name="stat_two_label" value="<?= e($edit['stat_two_label'] ?? '') ?>">
            <input type="hidden" name="stat_two_value" value="<?= e($edit['stat_two_value'] ?? '') ?>">

            <label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></label>
            <div class="full admin-actions">
                <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Banner</button>
                <?php if ($edit): ?><a class="btn btn-soft" href="hero-banners.php">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="panel-card table-wrap">
        <div class="toolbar"><div><h2>Saved Banners</h2><p>Published Home banners slide automatically. Sort order controls the sequence.</p></div></div>
        <table>
            <thead><tr><th>Page</th><th>Responsive Preview</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($banners as $row): ?>
                <?php
                    $desktopThumb = $row['desktop_image_url'] ?? $row['image_url'] ?? '';
                    $mobileThumb = $row['mobile_image_url'] ?? '';
                ?>
                <tr>
                    <td><?= e($pageOptions[$row['page_key']] ?? $row['page_key']) ?><br><small>Order <?= e((string)$row['sort_order']) ?></small></td>
                    <td>
                        <strong><?= e($row['title']) ?></strong><br>
                        <div class="hero-table-previews-v126">
                            <?php if ($desktopThumb): ?><span><img src="../<?= e($desktopThumb) ?>" loading="lazy" decoding="async" alt=""><small>Desktop</small></span><?php endif; ?>
                            <?php if ($mobileThumb): ?><span class="is-mobile"><img src="../<?= e($mobileThumb) ?>" loading="lazy" decoding="async" alt=""><small>Mobile</small></span><?php endif; ?>
                        </div>
                    </td>
                    <td><span class="badge <?= $row['published'] === 'Yes' ? 'badge-green' : 'badge-gray' ?>"><?= e($row['published']) ?></span></td>
                    <td><div class="table-actions"><a class="btn btn-sm btn-soft" href="hero-banners.php?edit=<?= e((string)$row['id']) ?>">Edit</a><a class="btn btn-sm btn-danger confirm-action" href="hero-banners.php?delete=<?= e((string)$row['id']) ?>&token=<?= e(csrf_token()) ?>">Unpublish</a></div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$banners): ?><tr><td colspan="4" class="empty-state">No hero banners added yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.hero-admin-grid-v126{align-items:start}.hero-upload-guide-v126{display:grid;grid-template-columns:1fr 1fr;gap:12px}.hero-upload-guide-v126 article{display:flex;align-items:center;gap:12px;padding:14px;border:1px solid #dfe7f0;border-radius:14px;background:#f8fbff}.hero-upload-guide-v126 i{width:42px;height:42px;display:grid;place-items:center;border-radius:12px;background:#eaf1fb;color:#123b73}.hero-upload-guide-v126 b,.hero-upload-guide-v126 span{display:block}.hero-upload-guide-v126 span{margin-top:3px;color:#65758a;font-size:12px}.hero-preview-v126{min-height:130px;display:grid;place-items:center;overflow:hidden}.hero-preview-v126 img{width:100%;height:100%;object-fit:cover}.hero-preview-desktop-v126{aspect-ratio:16/6}.hero-preview-mobile-v126{width:min(100%,260px);aspect-ratio:3/4}.hero-preview-v126>span{display:grid;place-items:center;gap:8px;color:#65758a}.hero-fallback-v126{padding:12px;border:1px solid #dfe7f0;border-radius:14px;background:#fbfcfe}.hero-fallback-v126 summary{cursor:pointer;font-weight:800;color:#123b73}.banner-copy-fields-v126.is-hidden{display:none}.hero-table-previews-v126{display:flex;align-items:end;gap:8px;margin-top:8px}.hero-table-previews-v126 span{display:grid;gap:3px}.hero-table-previews-v126 img{width:105px;aspect-ratio:16/6;object-fit:cover;border-radius:8px;border:1px solid #dfe7f0}.hero-table-previews-v126 .is-mobile img{width:42px;aspect-ratio:3/4}.hero-table-previews-v126 small{color:#65758a;font-size:9px;text-align:center}@media(max-width:700px){.hero-upload-guide-v126{grid-template-columns:1fr}.hero-preview-mobile-v126{width:180px}}
</style>
<script>
(function(){
    document.querySelectorAll('[data-preview-target]').forEach(function(input){
        input.addEventListener('change', function(){
            var target = document.getElementById(input.dataset.previewTarget || '');
            var file = input.files && input.files[0];
            if (!target || !file) return;
            var reader = new FileReader();
            reader.onload = function(event){ target.innerHTML = '<img src="' + event.target.result + '" alt="Preview">'; };
            reader.readAsDataURL(file);
        });
    });
    var show = document.getElementById('showBannerContent');
    var copy = document.getElementById('bannerCopyFields');
    function syncCopy(){ if (show && copy) copy.classList.toggle('is-hidden', show.value === 'No'); }
    show && show.addEventListener('change', syncCopy); syncCopy();
    var range = document.getElementById('overlayStrengthRange');
    var value = document.getElementById('overlayStrengthValue');
    range && range.addEventListener('input', function(){ if (value) value.textContent = range.value + '%'; });
})();
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
