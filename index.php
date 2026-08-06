<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = app_setting('seo_home_title', app_setting('site_name', APP_NAME) . ' | Spoken English Classes');
$meta_description = app_setting('seo_home_description', 'Learn spoken English through live classes, daily practice, a clear roadmap and weekly tests.');
$page_styles = ['assets/css/phase126-home.css'];
$page_scripts = ['assets/js/phase126-home.js'];
$lightweight_layout = true;

require_once __DIR__ . '/includes/header.php';

$courses = fetch_courses(4);
$reviews = fetch_testimonials(6);
$batches = fetch_batch_timings(3);
$faqs = fetch_faqs(6);
$heroStats = fetch_content_blocks('hero_stat', 4);
$homeFeatures = fetch_content_blocks('home_feature', 4);
$onlineFeatures = fetch_content_blocks('online_class_feature', 3);
$dbHeroSlides = function_exists('fetch_hero_banners') ? fetch_hero_banners('home', 8) : [];
$currentStudent = function_exists('fetch_current_student') ? fetch_current_student() : null;

function wf_home_short(string $text, int $limit = 92): string
{
    $text = trim((string)preg_replace('/\s+/', ' ', strip_tags($text)));
    if ($text === '') return '';
    return mb_strimwidth($text, 0, $limit, '…', 'UTF-8');
}

function wf_home_course_icon(string $title): string
{
    $title = strtolower($title);
    if (str_contains($title, 'interview') || str_contains($title, 'career')) return 'fa-solid fa-briefcase';
    if (str_contains($title, 'grammar')) return 'fa-solid fa-spell-check';
    if (str_contains($title, 'kids') || str_contains($title, 'child')) return 'fa-solid fa-child-reaching';
    if (str_contains($title, 'advanced')) return 'fa-solid fa-arrow-trend-up';
    if (str_contains($title, 'basic') || str_contains($title, 'beginner')) return 'fa-solid fa-seedling';
    return 'fa-solid fa-comments';
}

function wf_home_slide_asset(?string $path): string
{
    $path = trim((string)$path);
    return $path === '' ? '' : site_asset_url($path);
}

$heroSlides = [];
foreach ($dbHeroSlides as $row) {
    $desktopImage = wf_home_slide_asset($row['desktop_image_url'] ?? $row['image_url'] ?? '');
    $mobileImage = wf_home_slide_asset($row['mobile_image_url'] ?? '');
    $fallbackImage = wf_home_slide_asset($row['image_url'] ?? '');
    if ($desktopImage === '') $desktopImage = $fallbackImage;
    if ($fallbackImage === '') $fallbackImage = $desktopImage;
    if ($desktopImage === '') continue;

    $rawPosition = strtolower(trim((string)($row['content_position'] ?? 'left')));
    $position = in_array($rawPosition, ['left', 'center', 'right'], true) ? $rawPosition : 'left';
    $overlay = max(15, min(85, (int)($row['overlay_strength'] ?? 58)));
    $showContent = ($row['show_content'] ?? 'Yes') !== 'No';

    $heroSlides[] = [
        'eyebrow' => trim((string)($row['eyebrow'] ?? '')),
        'title' => trim((string)($row['title'] ?? '')) ?: 'Learn spoken English with confidence',
        'subtitle' => trim((string)($row['subtitle'] ?? '')),
        'desktop_image_url' => $desktopImage,
        'mobile_image_url' => $mobileImage,
        'fallback_image_url' => $fallbackImage,
        'image_alt' => trim((string)($row['image_alt'] ?? '')) ?: trim((string)($row['title'] ?? 'Spoken English class banner')),
        'primary_text' => trim((string)($row['primary_text'] ?? '')),
        'primary_url' => trim((string)($row['primary_url'] ?? '')),
        'secondary_text' => trim((string)($row['secondary_text'] ?? '')),
        'secondary_url' => trim((string)($row['secondary_url'] ?? '')),
        'show_content' => $showContent,
        'content_position' => $position,
        'overlay_strength' => $overlay,
    ];
}

if (!$heroSlides) {
    $heroSlides = [
        [
            'eyebrow' => 'Live • Practice • Improve',
            'title' => 'Speak English with confidence.',
            'subtitle' => 'Daily speaking practice, teacher support and a clear learning path.',
            'desktop_image_url' => 'assets/uploads/banners/home-banner-speaking-desktop.webp',
            'mobile_image_url' => 'assets/uploads/banners/home-banner-speaking-mobile.webp',
            'fallback_image_url' => 'assets/uploads/banners/home-banner-speaking-desktop.webp',
            'image_alt' => 'Student practicing spoken English with a microphone',
            'primary_text' => 'Start Learning',
            'primary_url' => 'admission.php',
            'secondary_text' => 'View Roadmap',
            'secondary_url' => 'learning-roadmap.php',
            'show_content' => true,
            'content_position' => 'left',
            'overlay_strength' => 58,
        ],
        [
            'eyebrow' => 'Live Online Class',
            'title' => 'Learn live from mobile or laptop.',
            'subtitle' => 'Join the teacher, speak in class and get direct feedback.',
            'desktop_image_url' => 'assets/uploads/banners/home-banner-online-class-desktop.webp',
            'mobile_image_url' => 'assets/uploads/banners/home-banner-online-class-mobile.webp',
            'fallback_image_url' => 'assets/uploads/banners/home-banner-online-class-desktop.webp',
            'image_alt' => 'Students joining a live online English class',
            'primary_text' => 'Join Online Class',
            'primary_url' => 'admission.php?mode=online',
            'secondary_text' => 'View Class Flow',
            'secondary_url' => '#online-class',
            'show_content' => true,
            'content_position' => 'left',
            'overlay_strength' => 68,
        ],
    ];
}

$cleanStats = [];
foreach ($heroStats as $stat) {
    $title = trim((string)($stat['title'] ?? ''));
    if ($title === '') continue;
    $cleanStats[] = [
        'icon' => app_icon_class((string)($stat['icon'] ?? ''), 'fa-solid fa-circle-check'),
        'title' => wf_home_short($title, 28),
        'subtitle' => wf_home_short((string)($stat['subtitle'] ?? $stat['body'] ?? ''), 38),
    ];
    if (count($cleanStats) >= 4) break;
}
if (!$cleanStats) {
    $cleanStats = [
        ['icon' => 'fa-solid fa-person-chalkboard', 'title' => 'Live Classes', 'subtitle' => 'Teacher guidance'],
        ['icon' => 'fa-solid fa-microphone-lines', 'title' => 'Daily Practice', 'subtitle' => 'Speak every day'],
        ['icon' => 'fa-solid fa-route', 'title' => 'Clear Roadmap', 'subtitle' => 'Easy step-by-step'],
        ['icon' => 'fa-solid fa-clipboard-check', 'title' => 'Weekly Test', 'subtitle' => 'Track improvement'],
    ];
}

$cleanHomeFeatures = [];
foreach ($homeFeatures as $feature) {
    $title = trim((string)($feature['title'] ?? ''));
    if ($title === '') continue;
    $cleanHomeFeatures[] = [
        'icon' => app_icon_class((string)($feature['icon'] ?? ''), 'fa-solid fa-circle-check'),
        'title' => wf_home_short($title, 32),
        'subtitle' => wf_home_short((string)($feature['subtitle'] ?? $feature['body'] ?? ''), 62),
        'url' => trim((string)($feature['link_url'] ?? '')) ?: 'about.php',
    ];
    if (count($cleanHomeFeatures) >= 4) break;
}
if (!$cleanHomeFeatures) {
    $cleanHomeFeatures = [
        ['icon' => 'fa-solid fa-comments', 'title' => 'Real Conversation', 'subtitle' => 'Daily-use English practice.', 'url' => 'spoken-materials.php'],
        ['icon' => 'fa-solid fa-layer-group', 'title' => 'Basic to Advanced', 'subtitle' => 'Levels in the correct order.', 'url' => 'learning-roadmap.php'],
        ['icon' => 'fa-solid fa-user-check', 'title' => 'Teacher Feedback', 'subtitle' => 'Know what to improve next.', 'url' => 'contact.php'],
        ['icon' => 'fa-solid fa-mobile-screen-button', 'title' => 'Mobile Friendly', 'subtitle' => 'Learn from phone or laptop.', 'url' => 'student-auth.php'],
    ];
}

if (!$onlineFeatures) {
    $onlineFeatures = [
        ['icon' => 'fa-solid fa-video', 'title' => 'Live Teacher', 'subtitle' => 'Ask questions directly.'],
        ['icon' => 'fa-solid fa-microphone-lines', 'title' => 'Speaking Room', 'subtitle' => 'Practice in every class.'],
        ['icon' => 'fa-solid fa-chart-line', 'title' => 'Weekly Feedback', 'subtitle' => 'See your improvement.'],
    ];
}
$onlineFeatures = array_slice($onlineFeatures, 0, 3);

$quickActions = [
    ['icon' => 'fa-solid fa-microphone-lines', 'title' => 'Practice', 'meta' => 'Speak now', 'url' => 'spoken-materials.php'],
    ['icon' => 'fa-solid fa-route', 'title' => 'Roadmap', 'meta' => 'Next lesson', 'url' => 'learning-roadmap.php'],
    ['icon' => 'fa-solid fa-clipboard-check', 'title' => 'Weekly Test', 'meta' => 'Check level', 'url' => 'weekly-test.php'],
    ['icon' => 'fa-solid fa-gauge-high', 'title' => $currentStudent ? 'Dashboard' : 'Login', 'meta' => $currentStudent ? 'View progress' : 'Student access', 'url' => $currentStudent ? 'student-dashboard.php' : 'student-auth.php'],
];

$practiceTools = [
    ['icon' => 'fa-solid fa-language', 'title' => 'Word Meaning', 'meta' => 'Build vocabulary', 'url' => 'spoken-materials.php'],
    ['icon' => 'fa-solid fa-spell-check', 'title' => 'Grammar Uses', 'meta' => 'Learn patterns', 'url' => 'spoken-materials.php'],
    ['icon' => 'fa-solid fa-volume-high', 'title' => 'Listen & Speak', 'meta' => 'Improve fluency', 'url' => 'spoken-materials.php'],
    ['icon' => 'fa-solid fa-pen-to-square', 'title' => 'Quick Practice', 'meta' => 'Try exercises', 'url' => 'spoken-materials.php'],
];
?>

<section class="wf126-hero" data-home-slider aria-label="Homepage banners">
    <div class="wf126-slides">
        <?php foreach ($heroSlides as $index => $slide): ?>
            <article class="wf126-slide <?= $index === 0 ? 'is-active' : '' ?> wf126-position-<?= e($slide['content_position']) ?> <?= $slide['show_content'] ? '' : 'is-image-only' ?>" data-home-slide aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>" style="--overlay-strength:<?= e(number_format($slide['overlay_strength'] / 100, 2, '.', '')) ?>">
                <picture class="wf126-slide-media">
                    <?php if ($slide['mobile_image_url'] !== ''): ?><source media="(max-width: 767px)" srcset="<?= e($slide['mobile_image_url']) ?>"><?php endif; ?>
                    <source media="(min-width: 768px)" srcset="<?= e($slide['desktop_image_url']) ?>">
                    <img src="<?= e($slide['fallback_image_url']) ?>" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async" alt="<?= e($slide['image_alt']) ?>">
                </picture>
                <div class="wf126-slide-overlay" aria-hidden="true"></div>

                <?php if ($slide['show_content']): ?>
                    <div class="container wf126-slide-inner">
                        <div class="wf126-slide-copy">
                            <?php if ($slide['eyebrow'] !== ''): ?><span class="wf126-kicker"><i class="fa-solid fa-star"></i><?= e(wf_home_short($slide['eyebrow'], 48)) ?></span><?php endif; ?>
                            <?php if ($index === 0): ?>
                                <h1><?= e(wf_home_short($slide['title'], 58)) ?></h1>
                            <?php else: ?>
                                <h2><?= e(wf_home_short($slide['title'], 58)) ?></h2>
                            <?php endif; ?>
                            <?php if ($slide['subtitle'] !== ''): ?><p><?= e(wf_home_short($slide['subtitle'], 118)) ?></p><?php endif; ?>
                            <?php if (($slide['primary_text'] !== '' && $slide['primary_url'] !== '') || ($slide['secondary_text'] !== '' && $slide['secondary_url'] !== '')): ?>
                                <div class="wf126-hero-actions">
                                    <?php if ($slide['primary_text'] !== '' && $slide['primary_url'] !== ''): ?><a class="wf126-btn wf126-btn-primary" href="<?= e(app_safe_href($slide['primary_url'])) ?>"><?= e(wf_home_short($slide['primary_text'], 26)) ?><i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
                                    <?php if ($slide['secondary_text'] !== '' && $slide['secondary_url'] !== ''): ?><a class="wf126-btn wf126-btn-ghost" href="<?= e(app_safe_href($slide['secondary_url'])) ?>"><?= e(wf_home_short($slide['secondary_text'], 26)) ?></a><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (count($heroSlides) > 1): ?>
        <button class="wf126-slider-arrow wf126-slider-prev" type="button" data-home-prev aria-label="Previous banner"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="wf126-slider-arrow wf126-slider-next" type="button" data-home-next aria-label="Next banner"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="wf126-slider-controls">
            <div class="wf126-slider-dots" role="tablist" aria-label="Choose banner">
                <?php foreach ($heroSlides as $index => $_): ?><button type="button" class="<?= $index === 0 ? 'is-active' : '' ?>" data-home-dot="<?= e((string)$index) ?>" aria-label="Show banner <?= e((string)($index + 1)) ?>"></button><?php endforeach; ?>
            </div>
            <button class="wf126-slider-toggle" type="button" data-home-toggle aria-label="Pause banner autoplay" aria-pressed="false"><i class="fa-solid fa-pause"></i></button>
        </div>
        <span class="wf126-slider-progress" data-home-progress aria-hidden="true"></span>
    <?php endif; ?>

    <div class="wf126-hero-wave" aria-hidden="true"><svg viewBox="0 0 1440 100" preserveAspectRatio="none"><path d="M0,55 C240,95 430,8 720,52 C1000,94 1190,22 1440,58 L1440,100 L0,100 Z"></path></svg></div>
</section>

<section class="wf126-quick-actions" aria-label="Student quick actions">
    <div class="container wf126-quick-grid">
        <?php foreach ($quickActions as $action): ?>
            <a class="wf126-quick-card" href="<?= e(app_safe_href($action['url'])) ?>">
                <span class="wf126-quick-icon"><i class="<?= e($action['icon']) ?>"></i></span>
                <span><b><?= e($action['title']) ?></b><small><?= e($action['meta']) ?></small></span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="wf126-trust" aria-label="Institute learning benefits">
    <div class="container wf126-trust-grid">
        <?php foreach ($cleanStats as $stat): ?>
            <article><i class="<?= e($stat['icon']) ?>"></i><span><b><?= e($stat['title']) ?></b><small><?= e($stat['subtitle']) ?></small></span></article>
        <?php endforeach; ?>
    </div>
</section>

<section class="wf126-section wf126-why">
    <div class="container">
        <header class="wf126-section-head" data-reveal>
            <div><span class="wf126-label">Why Well Fare</span><h2>Everything needed to start speaking.</h2></div>
            <a href="about.php">About institute<i class="fa-solid fa-arrow-right"></i></a>
        </header>
        <div class="wf126-feature-grid">
            <?php foreach ($cleanHomeFeatures as $feature): ?>
                <a class="wf126-feature-card" href="<?= e(app_safe_href($feature['url'])) ?>" data-reveal>
                    <span><i class="<?= e($feature['icon']) ?>"></i></span>
                    <div><h3><?= e($feature['title']) ?></h3><p><?= e($feature['subtitle']) ?></p></div>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="wf126-section wf126-online" id="online-class">
    <div class="container wf126-online-grid">
        <div class="wf126-online-visual" data-reveal>
            <div class="wf126-online-window">
                <div class="wf126-window-top"><span><i></i><i></i><i></i></span><b>Live English Class</b><small><i class="fa-solid fa-shield-halved"></i> Secure</small></div>
                <div class="wf126-online-screen">
                    <span class="wf126-live"><i></i> LIVE</span>
                    <div class="wf126-teacher"><i class="fa-solid fa-person-chalkboard"></i></div>
                    <strong>Speak with the teacher</strong>
                    <small>Listen • Answer • Improve</small>
                    <div class="wf126-waveform" aria-hidden="true"><?php for ($i = 0; $i < 14; $i++): ?><i></i><?php endfor; ?></div>
                    <div class="wf126-call-controls" aria-hidden="true"><i class="fa-solid fa-microphone"></i><i class="fa-solid fa-video"></i><i class="fa-solid fa-phone-slash"></i></div>
                </div>
            </div>
            <span class="wf126-float-note wf126-float-one"><i class="fa-solid fa-comment-dots"></i> Ask live</span>
            <span class="wf126-float-note wf126-float-two"><i class="fa-solid fa-circle-check"></i> Feedback</span>
        </div>

        <div class="wf126-online-copy" data-reveal>
            <span class="wf126-label"><i class="fa-solid fa-wifi"></i> Online Class</span>
            <h2>Join from mobile or laptop.</h2>
            <p>Live teacher guidance with direct speaking practice.</p>
            <div class="wf126-online-features">
                <?php foreach ($onlineFeatures as $feature): ?>
                    <article><span><?= app_icon_html((string)($feature['icon'] ?? ''), 'fa-solid fa-circle-check') ?></span><div><b><?= e(wf_home_short((string)($feature['title'] ?? 'Online Learning'), 28)) ?></b><small><?= e(wf_home_short((string)(($feature['subtitle'] ?? '') ?: ($feature['body'] ?? '')), 48)) ?></small></div></article>
                <?php endforeach; ?>
            </div>
            <?php if ($batches): ?>
                <div class="wf126-class-times">
                    <?php foreach (array_slice($batches, 0, 2) as $batch): ?><span><i class="fa-regular fa-clock"></i><?= e(wf_home_short((string)($batch['timing'] ?: $batch['batch_name']), 30)) ?></span><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="wf126-inline-actions"><a class="wf126-btn wf126-btn-dark" href="admission.php?mode=online">Join Online Class<i class="fa-solid fa-arrow-right"></i></a><a href="contact.php"><i class="fa-solid fa-headset"></i> Ask Details</a></div>
        </div>
    </div>
</section>

<?php if ($courses): ?>
<section class="wf126-section wf126-courses">
    <div class="container">
        <header class="wf126-section-head" data-reveal>
            <div><span class="wf126-label">Courses</span><h2>Choose the right learning level.</h2></div>
            <a href="courses.php">View all courses<i class="fa-solid fa-arrow-right"></i></a>
        </header>
        <div class="wf126-course-grid">
            <?php foreach ($courses as $course): ?>
                <article class="wf126-course-card" data-reveal>
                    <div class="wf126-course-top"><span><i class="<?= e(wf_home_course_icon((string)$course['title'])) ?>"></i></span><small><?= e((string)($course['level'] ?: 'All Levels')) ?></small></div>
                    <h3><?= e(wf_home_short((string)$course['title'], 46)) ?></h3>
                    <p><?= e(wf_home_short((string)$course['short_description'], 84)) ?></p>
                    <footer><span><i class="fa-regular fa-clock"></i><?= e(wf_home_short((string)($course['duration'] ?: 'Flexible'), 24)) ?></span><a href="course-detail.php?id=<?= e((string)$course['id']) ?>" aria-label="View <?= e((string)$course['title']) ?>"><i class="fa-solid fa-arrow-right"></i></a></footer>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="wf126-section wf126-journey">
    <div class="container">
        <div class="wf126-journey-card wf-surface-dark" data-reveal>
            <div class="wf126-journey-copy"><span class="wf126-label wf126-label-light"><i class="fa-solid fa-route"></i> Learning Path</span><h2>One clear action at every step.</h2><p>Complete the current level to unlock the next one.</p><a href="learning-roadmap.php">Open full roadmap<i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="wf126-journey-steps" aria-label="Learning process">
                <article><span>1</span><i class="fa-solid fa-book-open-reader"></i><b>Learn</b></article>
                <article><span>2</span><i class="fa-solid fa-microphone-lines"></i><b>Practice</b></article>
                <article><span>3</span><i class="fa-solid fa-clipboard-check"></i><b>Test</b></article>
                <article><span>4</span><i class="fa-solid fa-lock-open"></i><b>Unlock</b></article>
            </div>
            <div class="wf126-journey-wave" aria-hidden="true"></div>
        </div>
    </div>
</section>

<section class="wf126-section wf126-practice-tools">
    <div class="container">
        <header class="wf126-section-head" data-reveal><div><span class="wf126-label">Daily Practice</span><h2>Open a tool and start.</h2></div><a href="spoken-materials.php">All materials<i class="fa-solid fa-arrow-right"></i></a></header>
        <div class="wf126-tool-grid">
            <?php foreach ($practiceTools as $tool): ?><a href="<?= e(app_safe_href($tool['url'])) ?>" data-reveal><span><i class="<?= e($tool['icon']) ?>"></i></span><div><b><?= e($tool['title']) ?></b><small><?= e($tool['meta']) ?></small></div><i class="fa-solid fa-arrow-right"></i></a><?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($batches): ?>
<section class="wf126-section wf126-batches">
    <div class="container wf126-batch-wrap wf-surface-dark" data-reveal>
        <div class="wf126-batch-copy"><span class="wf126-label wf126-label-light"><i class="fa-solid fa-calendar-check"></i> New Batches</span><h2>Choose a suitable class time.</h2><a href="admission.php">Reserve your seat<i class="fa-solid fa-arrow-right"></i></a></div>
        <div class="wf126-batch-list">
            <?php foreach ($batches as $index => $batch): ?>
                <article><span><?= e(str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT)) ?></span><div><b><?= e(wf_home_short((string)($batch['batch_name'] ?? 'Spoken English Batch'), 38)) ?></b><small><i class="fa-regular fa-clock"></i><?= e(wf_home_short((string)($batch['timing'] ?? 'Flexible timing'), 34)) ?></small></div><a href="admission.php"><i class="fa-solid fa-arrow-right"></i></a></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($reviews):
    $reviewPool = array_values(array_slice($reviews, 0, 8));
    // Use the same number of cards in both rows so the two-way marquee loops smoothly.
    $reviewRows = [$reviewPool, array_reverse($reviewPool)];
?>
<section class="wf126-section wf126-reviews" id="reviews">
    <div class="container">
        <?php wf_section_heading('Student Reviews', 'Real confidence. Real progress.', 'Students share how regular speaking practice helped them improve.', ['label' => 'View All Reviews', 'url' => 'reviews.php']); ?>
    </div>
    <div class="wf127-review-marquee" aria-label="Student reviews moving slider">
        <?php foreach ($reviewRows as $rowIndex => $row): ?>
            <div class="wf127-review-row <?= $rowIndex === 1 ? 'is-reverse' : '' ?>">
                <?php for ($setIndex = 0; $setIndex < 2; $setIndex++): ?>
                    <div class="wf127-review-set" <?= $setIndex === 1 ? 'aria-hidden="true"' : '' ?>>
                        <?php foreach ($row as $review):
                            $studentName = trim((string)($review['student_name'] ?? 'Student')) ?: 'Student';
                            $studentImage = site_asset_url((string)($review['student_image'] ?? ''));
                            $initial = strtoupper(mb_substr($studentName, 0, 1));
                            $rating = max(1, min(5, (int)($review['rating'] ?? 5)));
                        ?>
                            <article class="wf127-review-card">
                                <header>
                                    <span class="wf127-review-avatar"><?php if ($studentImage !== ''): ?><img src="<?= e($studentImage) ?>" loading="lazy" decoding="async" alt="<?= e($studentName) ?>"><?php else: ?><?= e($initial) ?><?php endif; ?></span>
                                    <div><b><?= e($studentName) ?></b><small><?= e(str_repeat('★', $rating)) ?></small></div>
                                    <i class="fa-solid fa-quote-right" aria-hidden="true"></i>
                                </header>
                                <p><?= e(wf_home_short((string)($review['message'] ?? ''), 190)) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php wf_faq_split($faqs, [
    'eyebrow' => app_setting('home_faq_eyebrow', 'Common Questions'),
    'title' => app_setting('home_faq_title', 'Before you join'),
    'text' => app_setting('home_faq_subtitle', 'Open a question to see the answer.'),
    'icon' => 'fa-solid fa-circle-question',
]); ?>

<section class="wf126-section wf126-final">
    <div class="container">
        <div class="wf126-final-card wf-surface-dark" data-reveal><div><span>Admission Open</span><h2>Start speaking English today.</h2></div><div><a class="wf126-btn wf126-btn-primary" href="admission.php">Join Now<i class="fa-solid fa-arrow-right"></i></a><a class="wf126-btn wf126-btn-ghost" href="tel:<?= e(str_replace(' ', '', app_setting('phone', APP_PHONE))) ?>"><i class="fa-solid fa-phone"></i>Call</a></div><i class="fa-solid fa-comments wf126-final-icon" aria-hidden="true"></i></div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
