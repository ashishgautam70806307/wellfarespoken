<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Student Reviews | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Student feedback and spoken English learning experiences.';
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
$reviews = fetch_testimonials(50);

wf_page_hero([
    'eyebrow' => 'Student Reviews',
    'title' => 'Progress built through regular practice.',
    'text' => 'Read how students improved speaking confidence, grammar clarity and interview communication.',
    'icon' => 'fa-solid fa-star',
    'actions' => [
        ['label' => 'Start Learning', 'url' => 'admission.php', 'icon' => 'fa-solid fa-arrow-right'],
        ['label' => 'View Roadmap', 'url' => 'learning-roadmap.php', 'icon' => 'fa-solid fa-route'],
    ],
    'compact' => true,
]);
?>
<section class="section">
    <div class="container">
        <?php wf_section_heading('All Reviews', 'What students say.', 'Short, clear feedback from institute learners.'); ?>
        <div class="wf127-review-page-grid">
            <?php foreach ($reviews as $review):
                $studentName = trim((string)($review['student_name'] ?? 'Student')) ?: 'Student';
                $studentImage = site_asset_url((string)($review['student_image'] ?? ''));
                $initial = strtoupper(mb_substr($studentName, 0, 1));
                $rating = max(1, min(5, (int)($review['rating'] ?? 5)));
            ?>
                <article class="wf127-review-card" data-reveal>
                    <header><span class="wf127-review-avatar"><?php if ($studentImage !== ''): ?><img src="<?= e($studentImage) ?>" loading="lazy" decoding="async" alt="<?= e($studentName) ?>"><?php else: ?><?= e($initial) ?><?php endif; ?></span><div><b><?= e($studentName) ?></b><small><?= e(str_repeat('★', $rating)) ?></small></div><i class="fa-solid fa-quote-right"></i></header>
                    <p><?= e(wf_text_limit((string)($review['message'] ?? ''), 260)) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (!$reviews): ?><div class="empty-state">Student reviews will appear here soon.</div><?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
