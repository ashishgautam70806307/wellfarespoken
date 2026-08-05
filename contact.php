<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = app_setting('seo_contact_title', 'Contact | ' . app_setting('site_name', APP_NAME));
$meta_description = app_setting('seo_contact_description', 'Contact Well Fare English Spoken for admission, course details and counselling.');
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';

function contact_dynamic_value(string $key, string $fallback): string {
    $value = trim(app_setting($key, ''));
    return $value !== '' ? $value : $fallback;
}

$phone = app_setting('phone', APP_PHONE);
$phoneClean = preg_replace('/[^0-9+]/', '', $phone);
$whatsapp = preg_replace('/\D+/', '', app_setting('whatsapp', APP_WHATSAPP));
$email = app_setting('email', APP_EMAIL);
$address = app_setting('address', APP_ADDRESS);
$mapUrl = trim(app_setting('map_url', GOOGLE_MAP_URL));

wf_page_hero([
    'eyebrow' => 'Contact Institute',
    'title' => contact_dynamic_value('contact_page_title', 'Talk to the admission team.'),
    'text' => contact_dynamic_value('contact_page_subtitle', 'Get course, batch timing and admission guidance through call, WhatsApp or an institute visit.'),
    'icon' => 'fa-solid fa-headset',
    'actions' => [
        ['label' => 'Call Now', 'url' => 'tel:' . $phoneClean, 'icon' => 'fa-solid fa-phone'],
        ['label' => 'Admission Form', 'url' => 'admission.php', 'icon' => 'fa-solid fa-user-plus'],
    ],
    'steps' => ['Share your goal', 'Choose level', 'Confirm timing', 'Start learning'],
]);
?>

<section class="section contact-details-v2">
    <div class="container">
        <?php wf_section_heading('Quick Contact', 'Choose the easiest way to reach us.', 'Students and parents can contact the institute without reading long instructions.'); ?>
        <div class="contact-card-grid-v2">
            <article class="contact-mini-card-v2" data-reveal>
                <span><i class="fa-solid fa-phone"></i></span><h3>Call</h3><p><?= e($phone) ?></p>
                <?= wf_button('Call Now', 'tel:' . $phoneClean, 'primary', 'fa-solid fa-phone') ?>
            </article>
            <article class="contact-mini-card-v2" data-reveal>
                <span><i class="fa-brands fa-whatsapp"></i></span><h3>WhatsApp</h3><p>Ask for course and batch details.</p>
                <?= wf_button('Open Chat', 'https://wa.me/' . $whatsapp . '?text=Hello,%20I%20want%20spoken%20English%20course%20details', 'success', 'fa-brands fa-whatsapp', ['target' => '_blank', 'rel' => 'noopener']) ?>
            </article>
            <article class="contact-mini-card-v2" data-reveal>
                <span><i class="fa-solid fa-location-dot"></i></span><h3>Visit Institute</h3><p><?= e(wf_text_limit($address, 95)) ?></p>
                <?= wf_button('Get Direction', $mapUrl, 'secondary', 'fa-solid fa-location-arrow', ['target' => '_blank', 'rel' => 'noopener']) ?>
            </article>
        </div>
    </div>
</section>

<section class="section section-soft contact-map-section-v2">
    <div class="container contact-map-grid-v2">
        <a class="contact-map-card-v2" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener" data-reveal>
            <i class="fa-solid fa-map-location-dot"></i>
            <h2>Open Google Map</h2>
            <p><?= e($address) ?></p>
            <span class="wf-btn wf-btn-primary"><span class="wf-btn-label"><i class="fa-solid fa-location-arrow"></i>Get Direction</span></span>
        </a>
        <div class="contact-cta-card-v2" data-reveal>
            <span class="eyebrow">Need Guidance?</span>
            <h2>Book a free counselling call.</h2>
            <p>Submit your name, mobile number and learning goal. Our team will suggest the suitable level and batch.</p>
            <div class="contact-info-panel-v2">
                <a href="mailto:<?= e($email) ?>"><i class="fa-solid fa-envelope"></i><span><b>Email</b><?= e($email) ?></span></a>
                <div><i class="fa-solid fa-clock"></i><span><b>Support</b><?= e(contact_dynamic_value('contact_office_time', 'Call or visit for admission guidance.')) ?></span></div>
            </div>
            <?= wf_button('Submit Admission Enquiry', 'admission.php', 'primary', 'fa-solid fa-paper-plane') ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
