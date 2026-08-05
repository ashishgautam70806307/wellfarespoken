<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = app_setting('seo_about_title', 'About | ' . app_setting('site_name', APP_NAME));
$meta_description = app_setting('seo_about_description', 'About Well Fare English Spoken institute.');
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';

function about_dynamic_value(string $key, string $fallback): string {
    $value = trim(app_setting($key, ''));
    return $value !== '' ? $value : $fallback;
}

$siteName = app_setting('site_name', APP_NAME);
$logo = site_asset_url(app_setting('site_logo', ''));
$directorName = about_dynamic_value('director_name', 'Institute Director');
$directorDesignation = about_dynamic_value('director_designation', 'Director & Spoken English Mentor');
$directorExperience = about_dynamic_value('director_experience', 'Practical spoken English training');
$directorQualification = about_dynamic_value('director_qualification', 'English communication and grammar guidance');
$directorSpeciality = about_dynamic_value('director_speciality', 'Hindi to English speaking confidence');
$directorMessage = about_dynamic_value('director_message', 'Our goal is simple: every student should speak English with confidence, clarity and correct practice.');
$directorBio = about_dynamic_value('director_bio', 'We focus on practical spoken English, grammar clarity, daily-use sentences, interview preparation and regular practice so students can improve step by step.');
$directorPhoto = site_asset_url(app_setting('director_photo', ''));
$highlights = fetch_content_blocks('about_highlight', 6);
$highlightsToShow = $highlights ?: [
    ['icon' => 'fa-solid fa-microphone-lines', 'title' => 'Practical Speaking', 'subtitle' => 'Daily-use sentences and real speaking habits.'],
    ['icon' => 'fa-solid fa-brain', 'title' => 'Simple Grammar', 'subtitle' => 'Tense, modal verbs and correction with examples.'],
    ['icon' => 'fa-solid fa-trophy', 'title' => 'Confidence Building', 'subtitle' => 'Tests, feedback and interview communication practice.'],
];

wf_page_hero([
    'eyebrow' => about_dynamic_value('about_eyebrow', 'About Institute'),
    'title' => about_dynamic_value('about_title', 'A practical institute for confident English speaking.'),
    'text' => about_dynamic_value('about_subtitle', 'Students learn through a clear roadmap, daily speaking practice, correction and regular feedback.'),
    'icon' => 'fa-solid fa-graduation-cap',
    'actions' => [
        ['label' => 'View Courses', 'url' => 'courses.php', 'icon' => 'fa-solid fa-book-open'],
        ['label' => 'Book Counselling', 'url' => 'admission.php', 'icon' => 'fa-solid fa-user-plus'],
    ],
    'steps' => ['Learn clearly', 'Practice daily', 'Get correction', 'Build confidence'],
]);
?>

<section class="section about-highlight-section-v2">
    <div class="container">
        <?php wf_section_heading('Why Well Fare', 'Learning designed around student actions.', 'Students see what to learn, what to practise and what to do next.'); ?>
        <div class="about-highlight-grid-v2">
            <?php foreach (array_slice($highlightsToShow, 0, 6) as $item): ?>
                <article class="about-mini-card-v2" data-reveal>
                    <span class="about-mini-icon-v2"><?= app_icon_html($item['icon'] ?? '', 'fa-solid fa-circle-check') ?></span>
                    <h3><?= e($item['title'] ?? '') ?></h3>
                    <p><?= e(wf_text_limit((string)(($item['subtitle'] ?? '') ?: ($item['body'] ?? '')), 105)) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-soft about-director-section-v2">
    <div class="container director-detail-grid-v2">
        <div class="director-large-photo-v2" data-reveal>
            <?php if ($directorPhoto !== ''): ?>
                <img src="<?= e($directorPhoto) ?>" loading="lazy" decoding="async" alt="<?= e($directorName) ?>">
            <?php elseif ($logo !== ''): ?>
                <img src="<?= e($logo) ?>" loading="lazy" decoding="async" alt="<?= e($siteName) ?>">
            <?php else: ?>
                <span><?= e(mb_substr($directorName, 0, 1)) ?></span>
            <?php endif; ?>
        </div>
        <div class="director-detail-copy-v2" data-reveal>
            <span class="eyebrow">Director Profile</span>
            <h2><?= e($directorName) ?></h2>
            <p class="director-designation-v2"><?= e($directorDesignation) ?></p>
            <div class="director-info-list-v2">
                <div><b>Experience</b><span><?= e($directorExperience) ?></span></div>
                <div><b>Qualification</b><span><?= e($directorQualification) ?></span></div>
                <div><b>Speciality</b><span><?= e($directorSpeciality) ?></span></div>
            </div>
            <blockquote>“<?= e($directorMessage) ?>”</blockquote>
            <p><?= nl2br(e($directorBio)) ?></p>
        </div>
    </div>
</section>

<section class="section about-promise-v2">
    <div class="container promise-card-v2" data-reveal>
        <div>
            <span class="eyebrow">Teaching Promise</span>
            <h2><?= e(about_dynamic_value('about_promise_title', 'One clear next step for every student.')) ?></h2>
            <p><?= e(about_dynamic_value('about_promise_body', 'Theory stays short. Speaking, practice, correction and weekly improvement remain the main focus.')) ?></p>
        </div>
        <div class="promise-points-v2">
            <span>Daily speaking practice</span>
            <span>Grammar with examples</span>
            <span>Weekly tests and review</span>
            <span>Interview confidence</span>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
