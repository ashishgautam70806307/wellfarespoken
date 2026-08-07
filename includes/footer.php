</main>
<?php
require_once __DIR__ . '/ui-components.php';
$wfPhoneRaw = app_setting('phone', APP_PHONE);
$wfPhoneClean = preg_replace('/[^0-9+]/', '', $wfPhoneRaw);
$wfWhatsapp = preg_replace('/\D+/', '', app_setting('whatsapp', APP_WHATSAPP));
$wfLogo = site_asset_url(app_setting('site_logo', ''));
$wfName = app_setting('site_name', APP_NAME);
$wfTagline = trim((string)app_setting('site_tagline', APP_TAGLINE));
$wfAddress = app_setting('address', APP_ADDRESS);
$wfEmail = app_setting('email', APP_EMAIL);
$wfAbout = wf_text_limit(app_setting('footer_about', app_setting('admission_note', 'Practical spoken English classes with a clear roadmap, daily speaking practice and weekly feedback.')), 190);
$wfOfficeTime = trim(app_setting('contact_office_time', 'Call or visit for admission guidance.'));
$wfCopyright = trim((string)app_setting('footer_copyright', 'All rights reserved.'));
$footerStudent = function_exists('fetch_current_student') ? fetch_current_student() : null;
$footerAccountUrl = $footerStudent ? 'student-dashboard.php' : 'student-auth.php';
$footerAccountLabel = $footerStudent ? 'My Dashboard' : 'Student Login';
$socialLinks = [
    ['key' => 'facebook_url', 'label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f', 'slug' => 'facebook'],
    ['key' => 'instagram_url', 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'slug' => 'instagram'],
    ['key' => 'youtube_url', 'label' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'slug' => 'youtube'],
    ['key' => 'linkedin_url', 'label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in', 'slug' => 'linkedin'],
    ['key' => 'twitter_url', 'label' => 'X', 'icon' => 'fa-brands fa-x-twitter', 'slug' => 'x'],
];
$activeSocials = [];
foreach ($socialLinks as $social) {
    $url = trim((string)app_setting($social['key'], ''));
    if ($url !== '') {
        if (!preg_match('#^https?://#i', $url)) $url = 'https://' . ltrim($url, '/');
        $social['url'] = $url;
        $activeSocials[] = $social;
    }
}
?>

<div class="wf127-contact-dock" data-contact-dock>
    <div class="wf127-contact-actions">
        <a href="tel:<?= e($wfPhoneClean) ?>"><span><i class="fa-solid fa-phone"></i></span><b>Call</b></a>
        <a href="https://wa.me/<?= e($wfWhatsapp) ?>?text=Hello,%20I%20want%20spoken%20English%20course%20details" target="_blank" rel="noopener"><span><i class="fa-brands fa-whatsapp"></i></span><b>WhatsApp</b></a>
        <button type="button" data-scroll-top><span><i class="fa-solid fa-arrow-up"></i></span><b>Top</b></button>
    </div>
    <button class="wf127-contact-toggle" type="button" aria-label="Open contact options" aria-expanded="false" data-contact-toggle><i class="fa-solid fa-headset"></i></button>
</div>

<footer class="wf137-footer wf-surface-dark" data-wf-surface="dark">
    <div class="wf137-footer-accent" aria-hidden="true"></div>

    <div class="container wf137-footer-cta-wrap">
        <div class="wf137-footer-cta">
            <div class="wf137-footer-cta-copy">
                <span class="wf137-footer-cta-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                <div>
                    <span class="wf137-footer-kicker">Free course guidance</span>
                    <h2>Choose the right English level and batch before admission.</h2>
                </div>
            </div>
            <div class="wf137-footer-cta-actions">
                <?= wf_button('Call Institute', 'tel:' . $wfPhoneClean, 'secondary', 'fa-solid fa-phone') ?>
                <?= wf_button('Book Counselling', 'admission.php', 'gold', 'fa-solid fa-arrow-right') ?>
            </div>
        </div>
    </div>

    <div class="container wf137-footer-main">
        <section class="wf137-footer-brand">
            <a href="index.php" class="wf137-footer-logo" aria-label="<?= e($wfName) ?> home">
                <?php if ($wfLogo !== ''): ?><img src="<?= e($wfLogo) ?>" loading="lazy" decoding="async" alt="<?= e($wfName) ?>"><?php else: ?><span>WF</span><?php endif; ?>
            </a>
            <h3><?= e($wfName) ?></h3>
            <?php if ($wfTagline !== ''): ?><strong class="wf137-footer-tagline"><?= e($wfTagline) ?></strong><?php endif; ?>
            <p><?= e($wfAbout) ?></p>
            <div class="wf137-footer-trust" aria-label="Institute learning highlights">
                <span><i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i>Live Classes</span>
                <span><i class="fa-solid fa-route" aria-hidden="true"></i>Clear Roadmap</span>
                <span><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>Weekly Tests</span>
            </div>
            <?php if ($activeSocials): ?>
                <span class="wf137-footer-social-title">Connect with us</span>
                <div class="wf137-footer-socials" aria-label="Social media links">
                    <?php foreach ($activeSocials as $social): ?>
                        <a href="<?= e(app_safe_href($social['url'], '#', true)) ?>" target="_blank" rel="noopener" data-social="<?= e($social['slug'] ?? '') ?>" aria-label="Open <?= e($social['label']) ?>">
                            <i class="<?= e($social['icon']) ?>" aria-hidden="true"></i><span><?= e($social['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="wf137-footer-nav">
            <nav aria-label="Learning links"><h3>Learn</h3><a href="courses.php">Courses</a><a href="online-class.php">Online Class</a><a href="learning-roadmap.php">Learning Roadmap</a><a href="spoken-materials.php">Practice Room</a></nav>
            <nav aria-label="Test links"><h3>Practice &amp; Test</h3><a href="weekly-test.php?type=basic">Basic Test</a><a href="weekly-test.php?type=previous">Previous Test</a><a href="weekly-test.php?type=upcoming">Upcoming Test</a></nav>
            <nav aria-label="Institute links"><h3>Institute</h3><a href="about.php">About Us</a><a href="gallery.php">Gallery</a><a href="reviews.php">Student Reviews</a><a href="contact.php">Contact</a></nav>
            <nav aria-label="Student links"><h3>Student</h3><a href="<?= e($footerAccountUrl) ?>"><?= e($footerAccountLabel) ?></a><a href="admission.php">Admission</a><a href="admin/login.php">Institute Login</a><a href="index.php">Home</a></nav>
        </div>

        <section class="wf137-footer-contact">
            <h3>Contact Institute</h3>
            <div class="wf137-footer-contact-list">
                <a class="wf137-footer-contact-item" href="tel:<?= e($wfPhoneClean) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span><small>Phone</small><b><?= e($wfPhoneRaw) ?></b></span></a>
                <a class="wf137-footer-contact-item" href="mailto:<?= e($wfEmail) ?>"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span><small>Email</small><b><?= e($wfEmail) ?></b></span></a>
                <a class="wf137-footer-contact-item" href="<?= e(app_safe_href(app_setting('map_url', GOOGLE_MAP_URL), '#', true)) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><small>Address</small><b><?= e($wfAddress) ?></b></span></a>
                <div class="wf137-footer-contact-item"><i class="fa-solid fa-clock" aria-hidden="true"></i><span><small>Guidance Time</small><b><?= e($wfOfficeTime) ?></b></span></div>
            </div>
            <?php if ($wfWhatsapp !== ''): ?>
                <?= wf_button('Chat on WhatsApp', 'https://wa.me/' . $wfWhatsapp . '?text=Hello,%20I%20want%20spoken%20English%20course%20details', 'success', 'fa-brands fa-whatsapp', ['target' => '_blank', 'rel' => 'noopener', 'class' => 'wf137-footer-whatsapp']) ?>
            <?php endif; ?>
        </section>
    </div>

    <div class="container wf137-footer-bottom">
        <span>© <?= date('Y') ?> <strong><?= e($wfName) ?></strong>. <?= e($wfCopyright !== '' ? $wfCopyright : 'All rights reserved.') ?></span>
        <span>Speak • Learn • Practise • Grow</span>
    </div>
</footer>

<nav class="wf127-mobile-bottom" aria-label="Mobile quick navigation">
    <a class="<?= wf_nav_active('index.php') ? 'is-active' : '' ?>" href="index.php"><i class="fa-solid fa-house"></i><span>Home</span></a>
    <a class="<?= wf_nav_active(['learning-roadmap.php','roadmap-lesson.php']) ? 'is-active' : '' ?>" href="learning-roadmap.php"><i class="fa-solid fa-route"></i><span>Roadmap</span></a>
    <a class="<?= wf_nav_active('spoken-materials.php') ? 'is-active' : '' ?>" href="spoken-materials.php"><i class="fa-solid fa-microphone-lines"></i><span>Practice</span></a>
    <a class="<?= wf_nav_active(['weekly-test.php','weekly-result.php','weekly-exam-room.php']) ? 'is-active' : '' ?>" href="weekly-test.php"><i class="fa-solid fa-clipboard-check"></i><span>Test</span></a>
    <a class="<?= wf_nav_active(['student-auth.php','student-dashboard.php']) ? 'is-active' : '' ?>" href="<?= e($footerAccountUrl) ?>"><i class="fa-solid fa-user-graduate"></i><span>Account</span></a>
</nav>

<?php $wfPageScripts = isset($page_scripts) && is_array($page_scripts) ? $page_scripts : []; ?>
<script src="<?= e(app_asset_versioned('assets/js/phase130-ui.js')) ?>" defer></script>
<script src="<?= e(app_asset_versioned('assets/js/phase133-controlled-ui.js')) ?>" defer></script>
<?php if (empty($skip_phase139_mobile_learning_script)): ?><script src="<?= e(app_asset_versioned('assets/js/phase139-mobile-learning.js')) ?>" defer></script><?php endif; ?>
<?php foreach ($wfPageScripts as $wfPageScript): ?><script src="<?= e(app_asset_versioned((string)$wfPageScript)) ?>" defer></script><?php endforeach; ?>
<script src="<?= e(app_asset_versioned('assets/js/main.js')) ?>" defer></script>
<script>
if ('serviceWorker' in navigator && window.isSecureContext) {
  window.addEventListener('load', function () { navigator.serviceWorker.register('./sw.js', { scope: './' }).then(function (reg) { if (reg && reg.update) reg.update(); }).catch(function () {}); });
}
(function(){function hideLoader(){var loader=document.getElementById('appLoader');if(loader){loader.classList.add('hide');loader.setAttribute('aria-hidden','true');}}if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',hideLoader,{once:true});}else{hideLoader();}setTimeout(hideLoader,900);})();
</script>
</body>
</html>
