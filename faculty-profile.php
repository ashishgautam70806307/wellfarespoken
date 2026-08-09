<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/error-pages.php';
ensure_schema_updates();
$id = (int)($_GET['id'] ?? 0);
$teacher = fetch_faculty_member($id);
if (!$teacher) { wf_show_error_page(404); }
$page_title = $teacher['faculty_name'] . ' | Faculty Profile';
$meta_description = $teacher['short_bio'] ?: ($teacher['designation'] . ' at ' . app_setting('site_name', APP_NAME));
require_once __DIR__ . '/includes/header.php';
$img = site_asset_url($teacher['image_url'] ?? '');
$expertise = array_filter(array_map('trim', preg_split('/[,|\n]+/', (string)($teacher['expertise'] ?? ''))));
?>
<section class="faculty-profile-hero">
    <div class="container faculty-profile-grid">
        <div class="faculty-profile-photo">
            <?php if ($img !== ''): ?><img src="<?= e($img) ?>" decoding="async" alt="<?= e($teacher['faculty_name']) ?>"><?php else: ?><span><?= e(strtoupper(mb_substr($teacher['faculty_name'],0,1))) ?></span><?php endif; ?>
        </div>
        <div class="faculty-profile-copy">
            <span class="eyebrow">Faculty Profile</span>
            <h1><?= e($teacher['faculty_name']) ?></h1>
            <p class="faculty-designation"><?= e($teacher['designation'] ?: 'Spoken English Faculty') ?></p>
            <div class="faculty-profile-pills">
                <?php if (!empty($teacher['experience'])): ?><span><i class="fa-regular fa-clock"></i> <?= e($teacher['experience']) ?></span><?php endif; ?>
                <?php if (!empty($teacher['qualification'])): ?><span><i class="fa-solid fa-graduation-cap"></i> <?= e($teacher['qualification']) ?></span><?php endif; ?>
            </div>
            <p><?= e($teacher['full_bio'] ?: ($teacher['short_bio'] ?: 'Experienced faculty member focused on practical spoken English learning.')) ?></p>
            <div class="faculty-profile-actions">
                <a class="btn btn-primary" href="admission.php">Join Batch</a>
                <a class="btn btn-soft" href="index.php#faculty">Back to Faculty</a>
            </div>
        </div>
    </div>
</section>
<?php if ($expertise): ?>
<section class="section section-compact">
    <div class="container">
        <div class="section-title">
            <h2>Teaching Expertise</h2>
            <p>Core areas handled by <?= e($teacher['faculty_name']) ?>.</p>
        </div>
        <div class="expertise-grid">
            <?php foreach ($expertise as $item): ?><div class="expertise-chip"><?= e($item) ?></div><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
