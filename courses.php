<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = app_setting('seo_courses_title', 'Courses | ' . app_setting('site_name', APP_NAME));
$meta_description = app_setting('seo_courses_description', 'Practical spoken English, grammar, interview preparation and personality development courses.');
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
$courses = fetch_courses();

wf_page_hero([
    'eyebrow' => 'Course Catalogue',
    'title' => app_setting('courses_page_title', 'Choose the right spoken English course.'),
    'text' => 'Start at your current level and move step by step through speaking, grammar, practice and weekly tests.',
    'icon' => 'fa-solid fa-book-open-reader',
    'actions' => [
        ['label' => 'View Roadmap', 'url' => 'learning-roadmap.php', 'icon' => 'fa-solid fa-route'],
        ['label' => 'Admission Help', 'url' => 'admission.php', 'icon' => 'fa-solid fa-user-plus'],
    ],
    'steps' => ['Choose level', 'Join batch', 'Practice daily', 'Track progress'],
]);
?>
<section class="section courses-page-section">
    <div class="container">
        <?php wf_section_heading('Available Courses', 'Pick one clear learning path.', 'Course cards show only the information students need before opening full details.'); ?>
        <div class="course-card-grid">
            <?php foreach ($courses as $course):
                $courseImg = site_asset_url($course['course_image'] ?? '');
                $payUrl = course_pay_url($course);
            ?>
                <article class="card course-card premium-course live-course-card" data-reveal>
                    <div class="course-image-box">
                        <?php if ($courseImg !== ''): ?><img src="<?= e($courseImg) ?>" loading="lazy" decoding="async" alt="<?= e($course['title']) ?>"><?php else: ?><div class="course-image-fallback"><span>WF</span><small>English Spoken</small></div><?php endif; ?>
                        <span class="course-price-badge"><?= e(course_money_label($course['price'] ?? 0)) ?></span>
                    </div>
                    <div class="course-card-body">
                        <div class="course-top"><span class="pill"><i class="fa-solid fa-bullseye"></i><?= e($course['level'] ?: 'All Levels') ?></span><span class="pill"><i class="fa-solid fa-stopwatch"></i><?= e($course['duration'] ?: 'Flexible') ?></span></div>
                        <h3><?= e($course['title']) ?></h3>
                        <p class="course-desc"><?= e(wf_text_limit((string)($course['short_description'] ?? ''), 110)) ?></p>
                        <ul class="mini-list course-mini-points">
                            <?php if (!empty($course['class_time'])): ?><li>Class Time: <?= e($course['class_time']) ?></li><?php endif; ?>
                            <?php if (!empty($course['class_days'])): ?><li>Days: <?= e($course['class_days']) ?></li><?php endif; ?>
                            <?php if (!empty($course['total_tests'])): ?><li><?= e((string)$course['total_tests']) ?> course tests included</li><?php endif; ?>
                        </ul>
                        <div class="course-actions"><?= wf_button('View Details', 'course-detail.php?id=' . (int)$course['id'], 'secondary', 'fa-solid fa-eye', ['class' => 'btn-sm']) ?><?= wf_button('Join Course', $payUrl, 'primary', 'fa-solid fa-arrow-right', ['class' => 'btn-sm']) ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$courses): ?><div class="empty-state">Courses will be added soon.</div><?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
