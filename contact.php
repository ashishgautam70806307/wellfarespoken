<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = app_setting('seo_contact_title', 'Contact | ' . app_setting('site_name', APP_NAME));
$meta_description = app_setting('seo_contact_description', 'Contact Well Fare English Spoken for admission, course details and counselling.');
$lightweight_layout = true;
$page_styles = ['assets/css/phase140-contact-page.css'];
require_once __DIR__ . '/includes/header.php';

$contactValue = static function (string $key, string $fallback): string {
    $value = trim((string)app_setting($key, ''));
    return $value !== '' ? $value : $fallback;
};

$siteName = app_setting('site_name', APP_NAME);
$phone = app_setting('phone', APP_PHONE);
$phoneClean = preg_replace('/[^0-9+]/', '', $phone);
$whatsapp = preg_replace('/\D+/', '', app_setting('whatsapp', APP_WHATSAPP));
$email = app_setting('email', APP_EMAIL);
$address = app_setting('address', APP_ADDRESS);
$officeTime = $contactValue('contact_office_time', 'Call or visit for admission guidance.');
$mapUrl = app_safe_href(app_setting('map_url', GOOGLE_MAP_URL), GOOGLE_MAP_URL, true);
$whatsappUrl = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode('Hello, I want spoken English course and batch details.');

$socialDefinitions = [
    ['key' => 'facebook_url', 'label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f'],
    ['key' => 'instagram_url', 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram'],
    ['key' => 'youtube_url', 'label' => 'YouTube', 'icon' => 'fa-brands fa-youtube'],
    ['key' => 'linkedin_url', 'label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in'],
    ['key' => 'twitter_url', 'label' => 'X', 'icon' => 'fa-brands fa-x-twitter'],
];
$activeSocials = [];
foreach ($socialDefinitions as $social) {
    $url = trim((string)app_setting($social['key'], ''));
    if ($url === '') continue;
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . ltrim($url, '/');
    $safeUrl = app_safe_href($url, '', true);
    if ($safeUrl === '') continue;
    $social['url'] = $safeUrl;
    $activeSocials[] = $social;
}
?>

<main class="wf140-contact-page">
    <section class="wf140-contact-hero wf-surface-dark" data-wf-surface="dark">
        <div class="wf140-contact-orb wf140-contact-orb-one" aria-hidden="true"></div>
        <div class="wf140-contact-orb wf140-contact-orb-two" aria-hidden="true"></div>
        <div class="container wf140-contact-hero-grid">
            <div class="wf140-contact-copy" data-reveal>
                <span class="wf140-contact-kicker"><i class="fa-solid fa-headset" aria-hidden="true"></i> Personal course guidance</span>
                <h1><?= e($contactValue('contact_page_title', 'Let’s plan your English journey.')) ?></h1>
                <p><?= e($contactValue('contact_page_subtitle', 'Talk directly with our admission team for the right course, level and batch—without confusing forms or long instructions.')) ?></p>

                <div class="wf140-contact-actions" aria-label="Primary contact actions">
                    <a class="wf140-contact-action is-gold" href="tel:<?= e($phoneClean) ?>">
                        <span><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                        <b>Call Institute</b>
                        <small><?= e($phone) ?></small>
                    </a>
                    <a class="wf140-contact-action is-whatsapp" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener">
                        <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
                        <b>Chat on WhatsApp</b>
                        <small>Quick course &amp; batch help</small>
                    </a>
                </div>

                <div class="wf140-contact-assurance" aria-label="Contact support highlights">
                    <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Clear level guidance</span>
                    <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Batch timing help</span>
                    <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> No counselling fee</span>
                </div>
            </div>

            <aside class="wf140-contact-console" aria-label="Institute contact details" data-reveal>
                <div class="wf140-console-head">
                    <div>
                        <span>Contact desk</span>
                        <h2>Reach us your way</h2>
                    </div>
                    <span class="wf140-console-status"><i aria-hidden="true"></i> Guidance available</span>
                </div>

                <div class="wf140-console-list">
                    <a href="tel:<?= e($phoneClean) ?>" class="wf140-console-item">
                        <span class="wf140-console-icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                        <span><small>Call</small><b><?= e($phone) ?></b></span>
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                    <a href="mailto:<?= e($email) ?>" class="wf140-console-item">
                        <span class="wf140-console-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                        <span><small>Email</small><b><?= e($email) ?></b></span>
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                    <a href="<?= e($mapUrl) ?>" target="_blank" rel="noopener" class="wf140-console-item">
                        <span class="wf140-console-icon"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                        <span><small>Visit</small><b><?= e($address) ?></b></span>
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="wf140-console-foot">
                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
                    <div><small>Guidance timing</small><b><?= e($officeTime) ?></b></div>
                </div>
            </aside>
        </div>
    </section>

    <section class="wf140-contact-purpose">
        <div class="container">
            <header class="wf140-section-head" data-reveal>
                <span>Choose your next step</span>
                <h2>What do you need help with?</h2>
                <p>Pick one clear path. We will take you to the right place immediately.</p>
            </header>

            <div class="wf140-purpose-grid">
                <article class="wf140-purpose-card is-course" data-reveal>
                    <span class="wf140-purpose-number">01</span>
                    <span class="wf140-purpose-icon"><i class="fa-solid fa-compass" aria-hidden="true"></i></span>
                    <h3>I am choosing a course</h3>
                    <p>Get help selecting Basic, Interview, News Commentary or Advance Grammar.</p>
                    <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener">Ask course advisor <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </article>

                <article class="wf140-purpose-card is-admission" data-reveal>
                    <span class="wf140-purpose-number">02</span>
                    <span class="wf140-purpose-icon"><i class="fa-solid fa-user-check" aria-hidden="true"></i></span>
                    <h3>I am ready for admission</h3>
                    <p>Share your details, preferred mode and batch timing through the admission form.</p>
                    <a href="admission.php">Open admission form <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </article>

                <article class="wf140-purpose-card is-student" data-reveal>
                    <span class="wf140-purpose-number">03</span>
                    <span class="wf140-purpose-icon"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></span>
                    <h3>I am an existing student</h3>
                    <p>Open your dashboard for roadmap, practice, weekly tests and learning progress.</p>
                    <a href="student-auth.php">Open student portal <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </article>
            </div>
        </div>
    </section>

    <section class="wf140-contact-visit">
        <div class="container wf140-visit-grid">
            <a class="wf140-map-card wf-surface-dark" data-wf-surface="dark" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener" data-reveal aria-label="Open institute location in Google Maps">
                <div class="wf140-map-pattern" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>
                <span class="wf140-map-pin"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                <div class="wf140-map-copy">
                    <span>Visit the institute</span>
                    <h2><?= e($address) ?></h2>
                    <p>Meet the team, understand your level and choose a suitable class timing.</p>
                </div>
                <span class="wf140-map-link">Open Google Maps <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
            </a>

            <div class="wf140-guidance-card" data-reveal>
                <span class="wf140-guidance-kicker">Simple counselling process</span>
                <h2>From question to class in four clear steps.</h2>
                <ol class="wf140-guidance-steps">
                    <li><span>1</span><div><b>Tell us your goal</b><small>Speaking, grammar, interview or confidence.</small></div></li>
                    <li><span>2</span><div><b>Know your level</b><small>We suggest the right starting point.</small></div></li>
                    <li><span>3</span><div><b>Choose a batch</b><small>Select a practical class timing.</small></div></li>
                    <li><span>4</span><div><b>Start learning</b><small>Join with a clear roadmap and practice plan.</small></div></li>
                </ol>
                <div class="wf140-guidance-actions">
                    <a href="tel:<?= e($phoneClean) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> Talk now</a>
                    <a href="admission.php"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send enquiry</a>
                </div>
            </div>
        </div>
    </section>

    <section class="wf140-contact-final">
        <div class="container wf140-final-panel" data-reveal>
            <div>
                <span>Still unsure?</span>
                <h2>One short conversation can give you a clear starting plan.</h2>
                <p>Call or message <?= e($siteName) ?> and tell us what you want to improve.</p>
            </div>
            <div class="wf140-final-actions">
                <a href="tel:<?= e($phoneClean) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i> <?= e($phone) ?></a>
                <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
            </div>
            <?php if ($activeSocials): ?>
                <div class="wf140-socials" aria-label="Institute social media links">
                    <span>Follow</span>
                    <?php foreach ($activeSocials as $social): ?>
                        <a href="<?= e($social['url']) ?>" target="_blank" rel="noopener" aria-label="Open <?= e($social['label']) ?>"><i class="<?= e($social['icon']) ?>" aria-hidden="true"></i></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
