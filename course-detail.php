<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$course = fetch_course($id);
if (!$course) {
    $page_title = 'Course Not Found | ' . app_setting('site_name', APP_NAME);
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><div class="empty-state">Course not found or not published. <a href="courses.php">Back to courses</a></div></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$variants = fetch_course_variants((int)$course['id']);
$page_title = $course['title'] . ' | ' . app_setting('site_name', APP_NAME);
$meta_description = $course['short_description'] ?: 'Spoken English course details.';
$lightweight_layout = true;
$courseImg = site_asset_url($course['course_image'] ?? '');
$payUrl = course_pay_url($course);
$outcomes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($course['outcomes'] ?? ''))));
$includes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($course['includes_text'] ?? ''))));
require_once __DIR__ . '/includes/header.php';
?>
<?php
wf_page_hero([
    'eyebrow' => 'Course Details',
    'title' => (string)$course['title'],
    'text' => wf_text_limit((string)($course['short_description'] ?? ''), 180),
    'icon' => 'fa-solid fa-graduation-cap',
    'actions' => [
        ['label' => 'Join Course', 'url' => $payUrl, 'icon' => 'fa-solid fa-arrow-right'],
        ['label' => 'Ask Details', 'url' => 'admission.php?course=' . urlencode((string)$course['title']), 'icon' => 'fa-solid fa-phone'],
    ],
    'steps' => ['Check level', 'Choose batch', 'Start lessons', 'Take tests'],
    'compact' => true,
]);
?>
<section class="section course-detail-clean">
    <div class="container">
        <div class="course-detail-shell">
            <div class="course-detail-copy" data-reveal>
                <span class="eyebrow">Course Price</span>
                <div class="course-detail-price"><?= e(course_money_label($course['price'] ?? 0)) ?></div>
                <p><?= e(wf_text_limit((string)($course['short_description'] ?? ''), 145)) ?></p>
                <div class="course-action-row"><a class="btn btn-primary" href="<?= e(app_safe_href($payUrl)) ?>">Join Course</a><a class="btn btn-soft" href="courses.php">Back to Courses</a></div>
            </div>
            <aside class="course-summary-card wf-surface-dark" data-wf-surface="dark" data-reveal>
                <div class="summary-icon"><i class="fa-solid fa-list-check"></i></div>
                <h3>Course Summary</h3>
                <div class="summary-list">
                    <div><b>Duration</b><span><?= e($course['duration'] ?: 'As per batch') ?></span></div>
                    <div><b>Level</b><span><?= e($course['level'] ?: 'All Levels') ?></span></div>
                    <div><b>Class Time</b><span><?= e($course['class_time'] ?: 'Flexible') ?></span></div>
                    <div><b>Days</b><span><?= e($course['class_days'] ?: 'Mon to Sat') ?></span></div>
                    <div><b>Tests</b><span><?= e((string)($course['total_tests'] ?: 0)) ?></span></div>
                    <div><b>Lessons</b><span><?= e((string)($course['lessons_count'] ?: 0)) ?></span></div>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="section course-info-section">
    <div class="container course-detail-layout clean-layout">
        <div class="course-main-copy card">
            <h2>What this course includes</h2>
            <p><?= nl2br(e($course['course_details'] ?: 'This course is designed for practical spoken English improvement through daily speaking, grammar clarity, sentence practice, correction, weekly tests and revision.')) ?></p>
            <div class="included-grid">
                <?php if ($includes): foreach ($includes as $item): ?>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span><?= e($item) ?></div>
                <?php endforeach; else: ?>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span>Daily speaking practice</div>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span>Hindi to English sentence practice</div>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span>Grammar with real usage</div>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span>Weekly test and revision</div>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span>Teacher-style correction</div>
                    <div><span><i class="fa-solid fa-check" aria-hidden="true"></i></span>Confidence building activities</div>
                <?php endif; ?>
            </div>
        </div>
        <aside class="course-visual-mini card">
            <?php if ($courseImg !== ''): ?>
                <img src="<?= e($courseImg) ?>" decoding="async" alt="<?= e($course['title']) ?>">
            <?php else: ?>
                <div class="course-image-fallback mini"><span>WF</span><small>English Spoken</small></div>
            <?php endif; ?>
            <h3><?= e($course['title']) ?></h3>
            <p>Best for <?= e($course['level'] ?: 'students') ?> learners who want daily speaking confidence.</p>
        </aside>
    </div>
</section>

<?php if ($outcomes): ?>
<section class="section section-soft compact-outcomes">
    <div class="container">
        <div class="section-title"><h2>Learning Outcomes</h2><p>After completing this course, students should be able to:</p></div>
        <div class="outcomes-grid <?= count($outcomes) === 1 ? 'single' : '' ?>">
            <?php foreach ($outcomes as $i => $item): ?>
                <div class="card outcome-mini"><span><?= e((string)($i+1)) ?></span><p><?= e($item) ?></p></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($variants): ?>
<section class="section">
    <div class="container">
        <div class="section-title"><h2>Available Batches / Variants</h2><p>Choose the batch or package that matches your schedule.</p></div>
        <div class="variants-grid <?= count($variants) === 1 ? 'single' : '' ?>">
            <?php foreach ($variants as $v): ?>
                <div class="card course-variant-card clean-variant">
                    <div class="variant-top-row">
                        <span class="variant-price"><?= e(course_money_label($v['price'] ?? 0)) ?></span>
                        <span class="variant-badge">Batch / Package</span>
                    </div>
                    <h3><?= e($v['variant_title']) ?></h3>
                    <p class="variant-text"><?= e($v['details'] ?? '') ?></p>
                    <ul class="mini-list variant-meta">
                        <li>Time: <?= e($v['class_time'] ?: 'As per schedule') ?></li>
                        <li>Days: <?= e($v['class_days'] ?: 'Flexible') ?></li>
                        <li>Tests: <?= e((string)($v['total_tests'] ?? 0)) ?></li>
                    </ul>
                    <div class="variant-actions">
                        <a class="btn btn-sm btn-primary" href="<?= e(app_safe_href($payUrl)) ?>">Pay Now</a>
                        <a class="btn btn-sm btn-light" href="admission.php?course=<?= e(urlencode($course['title'])) ?>">Ask Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
