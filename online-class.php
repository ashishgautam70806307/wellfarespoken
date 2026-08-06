<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Online Spoken English Class | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Join live online spoken English classes from mobile or laptop with speaking practice, teacher correction and weekly feedback.';
$lightweight_layout = true;
$page_styles = ['assets/css/phase130-online-class.css'];
require_once __DIR__ . '/includes/header.php';

$features = fetch_content_blocks('online_class_feature', 6);
if (!$features) {
    $features = [
        ['icon' => 'fa-solid fa-video', 'title' => 'Live Teacher', 'subtitle' => 'Learn and ask questions in the live class.'],
        ['icon' => 'fa-solid fa-microphone-lines', 'title' => 'Speaking Practice', 'subtitle' => 'Speak during class instead of only watching.'],
        ['icon' => 'fa-solid fa-comments', 'title' => 'Direct Correction', 'subtitle' => 'Get simple correction and repeat correctly.'],
        ['icon' => 'fa-solid fa-chart-line', 'title' => 'Weekly Feedback', 'subtitle' => 'Track practice, tests and improvement.'],
    ];
}
$batches = fetch_batch_timings(6);
$whatsapp = preg_replace('/\D+/', '', app_setting('whatsapp', APP_WHATSAPP));

wf_page_hero([
    'eyebrow' => 'Online Class',
    'title' => 'Join live English classes from mobile or laptop.',
    'text' => 'Attend the teacher-led class, speak regularly and receive direct feedback.',
    'icon' => 'fa-solid fa-laptop-file',
    'actions' => [
        ['label' => 'Join a Batch', 'url' => '#online-batches', 'icon' => 'fa-solid fa-arrow-right'],
        ['label' => 'Ask on WhatsApp', 'url' => 'https://wa.me/' . $whatsapp . '?text=Hello,%20I%20want%20online%20spoken%20English%20class%20details', 'icon' => 'fa-brands fa-whatsapp'],
    ],
    'steps' => ['Choose batch', 'Join live', 'Speak in class', 'Get feedback'],
]);
?>

<section class="section wf128-online-class-page">
    <div class="container">
        <?php wf_section_heading('Class Experience', 'Learn by speaking, not only watching.', 'Every class gives students a clear action and practice opportunity.'); ?>
        <div class="wf128-online-feature-grid">
            <?php foreach (array_slice($features, 0, 6) as $feature): ?>
                <article data-reveal>
                    <span><?= app_icon_html((string)($feature['icon'] ?? ''), 'fa-solid fa-circle-check') ?></span>
                    <div><h3><?= e(wf_text_limit((string)($feature['title'] ?? 'Online Class'), 42)) ?></h3><p><?= e(wf_text_limit((string)(($feature['subtitle'] ?? '') ?: ($feature['body'] ?? '')), 95)) ?></p></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-soft wf128-online-process">
    <div class="container">
        <?php wf_section_heading('Simple Process', 'Four steps from joining to improvement.'); ?>
        <div class="wf128-process-grid">
            <?php foreach ([
                ['fa-solid fa-calendar-check','Choose Batch','Select a suitable class time.'],
                ['fa-solid fa-link','Open Class Link','Join from your phone or laptop.'],
                ['fa-solid fa-microphone-lines','Speak & Repeat','Participate in guided speaking.'],
                ['fa-solid fa-chart-simple','View Feedback','Use feedback for the next practice.'],
            ] as $index => $step): ?>
                <article data-reveal><b><?= e(str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT)) ?></b><span><i class="<?= e($step[0]) ?>"></i></span><h3><?= e($step[1]) ?></h3><p><?= e($step[2]) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($batches): ?>
<section class="section wf128-online-batches" id="online-batches">
    <div class="container">
        <?php wf_section_heading('Available Batches', 'Choose a suitable class time.', 'Tap a batch to continue with the timing already selected.'); ?>
        <div class="wf128-batch-grid">
            <?php foreach ($batches as $batch): ?>
                <article data-reveal><span><i class="fa-regular fa-clock"></i></span><div><h3><?= e(wf_text_limit((string)($batch['batch_name'] ?? 'Online Spoken English'), 42)) ?></h3><p><?= e(wf_text_limit(trim((string)($batch['timing'] ?? 'Flexible timing') . ' · ' . (string)($batch['days'] ?? '')), 70)) ?></p></div><a href="admission.php?mode=online&batch_id=<?= e((string)$batch['id']) ?>" aria-label="Select <?= e((string)($batch['batch_name'] ?? 'this batch')) ?>"><i class="fa-solid fa-arrow-right"></i></a></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section-soft">
    <div class="container wf128-online-cta wf-surface-dark" data-reveal>
        <div><span class="eyebrow">Admission Open</span><h2>Start with the suitable online batch.</h2><p>Share your current level and preferred timing.</p></div>
        <a class="btn btn-primary" href="admission.php?mode=online">Join Online Class</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
