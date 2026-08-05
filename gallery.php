<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = app_setting('seo_gallery_title', 'Gallery | ' . app_setting('site_name', APP_NAME));
$meta_description = app_setting('seo_gallery_description', 'Classroom, activity and student photos from Well Fare English Spoken.');
$page_styles = ['assets/css/phase129-gallery.css'];
$page_scripts = ['assets/js/phase129-gallery.js'];
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
$items = fetch_gallery();
$galleryItems = [];
foreach ($items as $item) {
    $imageUrl = site_asset_url($item['image_url'] ?? $item['image'] ?? '');
    if ($imageUrl === '') continue;
    $galleryItems[] = [
        'image' => $imageUrl,
        'title' => trim((string)($item['title'] ?? 'Institute activity')) ?: 'Institute activity',
        'description' => trim((string)($item['description'] ?? '')),
    ];
}

wf_page_hero([
    'eyebrow' => 'Institute Gallery',
    'title' => app_setting('gallery_page_title', 'See learning in action.'),
    'text' => 'Classroom activities, speaking practice and student learning moments from the institute.',
    'icon' => 'fa-solid fa-images',
    'actions' => [
        ['label' => 'Join a Batch', 'url' => 'admission.php', 'icon' => 'fa-solid fa-user-plus'],
        ['label' => 'View Courses', 'url' => 'courses.php', 'icon' => 'fa-solid fa-book-open'],
    ],
    'steps' => ['Learn', 'Speak', 'Practise', 'Improve'],
    'compact' => true,
]);
?>
<section class="wf129-gallery-section">
    <div class="container">
        <?php wf_section_heading('Photo Gallery', 'Open any photo in full view.', 'Use next, previous, zoom or swipe controls on mobile.'); ?>
        <?php if ($galleryItems): ?>
            <div class="wf129-gallery-grid" data-gallery-grid>
                <?php foreach ($galleryItems as $index => $item): ?>
                    <button type="button" class="wf129-gallery-card" data-gallery-open="<?= e((string)$index) ?>" data-reveal aria-label="Open <?= e($item['title']) ?>">
                        <span class="wf129-gallery-image"><img src="<?= e($item['image']) ?>" loading="lazy" decoding="async" alt="<?= e($item['title']) ?>"><i class="fa-solid fa-expand"></i></span>
                        <span class="wf129-gallery-caption"><b><?= e($item['title']) ?></b><?php if ($item['description'] !== ''): ?><small><?= e(wf_text_limit($item['description'], 82)) ?></small><?php endif; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="wf129-gallery-empty"><i class="fa-solid fa-images"></i><h2>Photos will appear here</h2><p>Gallery images can be uploaded from the institute admin panel.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php if ($galleryItems): ?>
<dialog class="wf129-gallery-lightbox" id="wfGalleryLightbox" aria-labelledby="wfGalleryTitle">
    <div class="wf129-gallery-dialog">
        <header>
            <div><small id="wfGalleryCounter">1 / <?= e((string)count($galleryItems)) ?></small><h2 id="wfGalleryTitle">Gallery photo</h2></div>
            <div class="wf129-gallery-tools">
                <button type="button" data-gallery-zoom aria-label="Zoom photo"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                <button type="button" data-gallery-close aria-label="Close gallery"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </header>
        <div class="wf129-gallery-stage" data-gallery-stage>
            <button type="button" class="wf129-gallery-nav is-prev" data-gallery-prev aria-label="Previous photo"><i class="fa-solid fa-chevron-left"></i></button>
            <figure><div class="wf129-gallery-photo-wrap" data-gallery-photo-wrap><img id="wfGalleryImage" src="" alt=""></div><figcaption id="wfGalleryDescription"></figcaption></figure>
            <button type="button" class="wf129-gallery-nav is-next" data-gallery-next aria-label="Next photo"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <footer><span><i class="fa-solid fa-hand-pointer"></i> Swipe or use arrow keys</span><button type="button" data-gallery-close>Close</button></footer>
    </div>
</dialog>
<script id="wfGalleryData" type="application/json"><?= json_encode($galleryItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
