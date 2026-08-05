<?php
require_once __DIR__ . '/includes/functions.php';
ensure_core_schema_columns();
$page_title = app_setting('seo_admission_title', 'Admission Enquiry | ' . app_setting('site_name', APP_NAME));
$meta_description = app_setting('seo_admission_description', 'Book free counselling for spoken English classes.');
$lightweight_layout = true;
$page_styles = ['assets/css/phase130-admission.css'];

$courses = fetch_courses();
$batches = fetch_batch_timings(20);
$faqs = fetch_faqs(8);
$levels = fetch_form_options('current_level');
$preferredTimes = fetch_form_options('preferred_time');
$benefits = fetch_content_blocks('admission_benefit', 6);

$mode = strtolower(trim((string)($_GET['mode'] ?? $_POST['mode'] ?? '')));
$requestedBatchId = max(0, (int)($_GET['batch_id'] ?? $_POST['batch_id'] ?? 0));
$requestedBatchValue = trim((string)($_GET['batch'] ?? $_POST['preferred_batch'] ?? ''));
$selectedBatch = null;
foreach ($batches as $batchRow) {
    $value = trim((string)$batchRow['batch_name'] . ' - ' . (string)($batchRow['timing'] ?? ''));
    if (($requestedBatchId > 0 && (int)$batchRow['id'] === $requestedBatchId) || ($requestedBatchValue !== '' && hash_equals($value, $requestedBatchValue))) {
        $selectedBatch = $batchRow;
        $requestedBatchId = (int)$batchRow['id'];
        $requestedBatchValue = $value;
        break;
    }
}
$requestedCourse = trim((string)($_GET['course'] ?? $_POST['course'] ?? ($selectedBatch['course_name'] ?? '')));
$sourceLabel = $mode === 'online' ? 'Online Class Admission' : trim((string)($_GET['source'] ?? $_POST['source'] ?? 'Website Admission Form'));
if ($sourceLabel === '') $sourceLabel = 'Website Admission Form';

$formValues = [
    'name' => trim((string)($_POST['name'] ?? '')),
    'phone' => preg_replace('/\D+/', '', (string)($_POST['phone'] ?? '')),
    'course' => $requestedCourse,
    'current_level' => trim((string)($_POST['current_level'] ?? '')),
    'preferred_batch' => $requestedBatchValue,
    'message' => trim((string)($_POST['message'] ?? '')),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security token expired. Please refresh and try again.');
    } elseif ($formValues['name'] === '' || $formValues['phone'] === '') {
        flash('error', 'Student name and mobile number are required.');
    } elseif (!preg_match('/^[0-9]{10}$/', $formValues['phone'])) {
        flash('error', 'Please enter a valid 10 digit mobile number.');
    } else {
        $stmt = db()->prepare('INSERT INTO enquiries (name, phone, course_interest, current_level, preferred_batch, lead_source, message, enquiry_status, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $formValues['name'], $formValues['phone'], $formValues['course'], $formValues['current_level'],
            $formValues['preferred_batch'], mb_substr($sourceLabel, 0, 100), $formValues['message'], 'New', $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
        flash('success', 'Thank you! Your counselling request has been submitted.');
        $return = 'admission.php?submitted=1';
        if ($mode === 'online') $return .= '&mode=online';
        redirect($return);
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="wf129-admission-hero">
    <div class="container wf129-admission-hero-inner">
        <div>
            <span class="wf-page-kicker"><i class="fa-solid <?= $mode === 'online' ? 'fa-laptop-file' : 'fa-user-plus' ?>"></i><?= $mode === 'online' ? 'Online Class Admission' : 'Free Counselling' ?></span>
            <h1><?= $mode === 'online' ? 'Choose your online batch and request admission.' : 'Get the right course and batch guidance.' ?></h1>
            <p><?= $mode === 'online' ? 'The batch selected on the Online Class page is already filled below. Add your details to continue.' : 'Complete one short form. The institute team will confirm your level, timing and next step.' ?></p>
        </div>
        <div class="wf129-admission-flow" aria-label="Admission process">
            <?php foreach (['Send details','Counselling call','Batch confirmation','Start class'] as $i => $step): ?>
                <span><b><?= $i + 1 ?></b><?= e($step) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="wf129-admission-section">
    <div class="container wf129-admission-layout">
        <aside class="wf129-admission-info" data-reveal>
            <div class="wf129-admission-info-head">
                <span class="wf-section-kicker"><?= $mode === 'online' ? 'Selected Online Class' : 'Why students join' ?></span>
                <h2><?= $mode === 'online' && $selectedBatch ? e((string)$selectedBatch['batch_name']) : 'Simple guidance before you start.' ?></h2>
                <?php if ($mode === 'online' && $selectedBatch): ?>
                    <div class="wf129-selected-batch"><i class="fa-regular fa-clock"></i><span><b><?= e((string)($selectedBatch['timing'] ?? 'Timing to be confirmed')) ?></b><small><?= e((string)($selectedBatch['days'] ?? '')) ?></small></span></div>
                <?php else: ?>
                    <p><?= e(wf_text_limit(app_setting('admission_note', 'Share your current level and preferred timing. The institute team will suggest the correct learning path.'), 170)) ?></p>
                <?php endif; ?>
            </div>

            <div class="wf129-admission-benefits">
                <?php
                $fallbackBenefits = [
                    ['icon'=>'fa-solid fa-seedling','title'=>'Beginner Friendly','subtitle'=>'Start from basic daily-use speaking.'],
                    ['icon'=>'fa-solid fa-microphone-lines','title'=>'Practical Speaking','subtitle'=>'Speak, repeat and receive correction.'],
                    ['icon'=>'fa-solid fa-calendar-check','title'=>'Suitable Timing','subtitle'=>'Choose a batch around your schedule.'],
                ];
                $benefitList = $benefits ?: $fallbackBenefits;
                $seen = [];
                foreach ($benefitList as $benefit):
                    $title = trim((string)($benefit['title'] ?? ''));
                    $sub = trim((string)(($benefit['subtitle'] ?? '') ?: ($benefit['body'] ?? '')));
                    $key = strtolower($title . '|' . $sub);
                    if ($title === '' || isset($seen[$key])) continue;
                    $seen[$key] = true;
                    if (count($seen) > 3) break;
                ?>
                    <article><span><?= app_icon_html((string)($benefit['icon'] ?? ''), 'fa-solid fa-circle-check') ?></span><div><b><?= e($title) ?></b><small><?= e(wf_text_limit($sub, 82)) ?></small></div></article>
                <?php endforeach; ?>
            </div>

            <?php if ($batches): ?>
                <div class="wf129-admission-batches">
                    <div><h3>Available timings</h3><a href="online-class.php">View online classes</a></div>
                    <?php foreach (array_slice($batches, 0, 4) as $batch): ?>
                        <a href="admission.php?mode=online&batch_id=<?= e((string)$batch['id']) ?>">
                            <span><b><?= e((string)$batch['batch_name']) ?></b><small><?= e(trim((string)($batch['days'] ?? ''))) ?></small></span>
                            <em><?= e((string)($batch['timing'] ?? 'Ask timing')) ?></em><i class="fa-solid fa-arrow-right"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="wf129-admission-contact">
                <?= wf_button('WhatsApp', 'https://wa.me/' . preg_replace('/\D+/', '', app_setting('whatsapp', APP_WHATSAPP)) . '?text=Hello,%20I%20want%20admission%20details', 'success', 'fa-brands fa-whatsapp', ['target' => '_blank', 'rel' => 'noopener']) ?>
                <?= wf_button('Call Now', 'tel:' . str_replace(' ', '', app_setting('phone', APP_PHONE)), 'secondary', 'fa-solid fa-phone') ?>
            </div>
        </aside>

        <form class="wf129-admission-form" method="post" data-reveal>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="mode" value="<?= e($mode) ?>">
            <input type="hidden" name="batch_id" value="<?= e((string)$requestedBatchId) ?>">
            <input type="hidden" name="source" value="<?= e($sourceLabel) ?>">
            <header><span class="wf-section-kicker">Admission Form</span><h2>Start with the right batch.</h2><p>Fill the details below. The institute team will confirm course, level and timing.</p></header>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
            <div class="form-grid">
                <div class="field"><label for="admissionName">Student Name *</label><div class="wf129-input-icon"><i class="fa-solid fa-user"></i><input id="admissionName" name="name" required maxlength="100" value="<?= e($formValues['name']) ?>" placeholder="Enter student name"></div></div>
                <div class="field"><label for="admissionPhone">Mobile Number *</label><div class="wf129-input-icon"><i class="fa-solid fa-mobile-screen-button"></i><input id="admissionPhone" name="phone" required maxlength="10" inputmode="numeric" pattern="[0-9]{10}" value="<?= e($formValues['phone']) ?>" placeholder="10 digit mobile"></div></div>
                <div class="field"><label for="admissionCourse">Course Interest</label><select id="admissionCourse" name="course"><option value="">Select course</option><?php foreach ($courses as $course): ?><option value="<?= e((string)$course['title']) ?>" <?= $formValues['course'] === (string)$course['title'] ? 'selected' : '' ?>><?= e((string)$course['title']) ?></option><?php endforeach; ?></select></div>
                <div class="field"><label for="admissionLevel">Current English Level</label><select id="admissionLevel" name="current_level"><option value="">Select level</option><?php foreach ($levels as $option): $value=(string)($option['option_value'] ?: $option['option_label']); ?><option value="<?= e($value) ?>" <?= $formValues['current_level'] === $value ? 'selected' : '' ?>><?= e((string)$option['option_label']) ?></option><?php endforeach; ?></select></div>
                <div class="field full"><label for="admissionBatch">Preferred Batch Time</label><select id="admissionBatch" name="preferred_batch"><option value="">Select preferred timing</option><?php foreach ($batches as $batch): $value=trim((string)$batch['batch_name'].' - '.(string)($batch['timing'] ?? '')); ?><option value="<?= e($value) ?>" <?= $formValues['preferred_batch'] === $value ? 'selected' : '' ?>><?= e((string)$batch['batch_name']) ?><?= !empty($batch['timing']) ? ' · '.e((string)$batch['timing']) : '' ?><?= !empty($batch['days']) ? ' · '.e((string)$batch['days']) : '' ?></option><?php endforeach; ?><?php foreach ($preferredTimes as $option): $value=(string)($option['option_value'] ?: $option['option_label']); ?><option value="<?= e($value) ?>" <?= $formValues['preferred_batch'] === $value ? 'selected' : '' ?>><?= e((string)$option['option_label']) ?></option><?php endforeach; ?></select></div>
                <div class="field full"><label for="admissionGoal">Learning Goal</label><textarea id="admissionGoal" name="message" rows="3" maxlength="500" placeholder="Interview, daily conversation, school English..."><?= e($formValues['message']) ?></textarea></div>
            </div>
            <footer><button class="wf-btn wf-btn-primary" type="submit"><span class="wf-btn-label"><i class="fa-solid fa-paper-plane"></i>Submit Enquiry</span></button><span><i class="fa-solid fa-shield-halved"></i><?= e(app_setting('admission_privacy_note', 'Your details are safe with us.')) ?></span></footer>
        </form>
    </div>
</section>

<?php wf_faq_split($faqs, [
    'eyebrow' => 'Admission Help',
    'title' => app_setting('admission_faq_title', 'Admission FAQs'),
    'text' => 'Tap the question related to course, fee, batch or admission process.',
    'icon' => 'fa-solid fa-user-check',
]); ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
