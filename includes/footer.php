</main>
<?php
require_once __DIR__ . '/ui-components.php';
$wfPhoneRaw = app_setting('phone', APP_PHONE);
$wfPhoneClean = preg_replace('/[^0-9+]/', '', $wfPhoneRaw);
$wfWhatsapp = preg_replace('/\D+/', '', app_setting('whatsapp', APP_WHATSAPP));
$wfLogo = site_asset_url(app_setting('site_logo', ''));
$wfName = app_setting('site_name', APP_NAME);
$wfAddress = app_setting('address', APP_ADDRESS);
$wfEmail = app_setting('email', APP_EMAIL);
$wfAbout = wf_text_limit(app_setting('footer_about', app_setting('admission_note', 'Practical spoken English classes with a clear roadmap, daily speaking practice and weekly feedback.')), 190);
$wfOfficeTime = trim(app_setting('contact_office_time', 'Call or visit for admission guidance.'));
$footerStudent = function_exists('fetch_current_student') ? fetch_current_student() : null;
$footerAccountUrl = $footerStudent ? 'student-dashboard.php' : 'student-auth.php';
$footerAccountLabel = $footerStudent ? 'My Dashboard' : 'Student Login';
$socialLinks = [
    ['key' => 'facebook_url', 'label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f'],
    ['key' => 'instagram_url', 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram'],
    ['key' => 'youtube_url', 'label' => 'YouTube', 'icon' => 'fa-brands fa-youtube'],
    ['key' => 'linkedin_url', 'label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in'],
];
$activeSocials = [];
foreach ($socialLinks as $social) {
    $url = trim((string)app_setting($social['key'], ''));
    if ($url !== '') { $social['url'] = $url; $activeSocials[] = $social; }
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

<footer class="wf131-footer">
    <div class="container wf131-footer-cta">
        <div class="wf131-footer-cta-copy"><span><i class="fa-solid fa-comments"></i></span><div><small>Free course guidance</small><h2>Choose the right level and batch before admission.</h2></div></div>
        <div class="wf131-footer-cta-actions"><?= wf_button('Call Institute', 'tel:' . $wfPhoneClean, 'secondary', 'fa-solid fa-phone') ?><?= wf_button('Book Counselling', 'admission.php', 'primary', 'fa-solid fa-arrow-right') ?></div>
    </div>

    <div class="container wf131-footer-main">
        <section class="wf131-footer-brand">
            <a href="index.php" class="wf131-footer-logo" aria-label="<?= e($wfName) ?> home"><?php if ($wfLogo !== ''): ?><img src="<?= e($wfLogo) ?>" loading="lazy" decoding="async" alt="<?= e($wfName) ?>"><?php else: ?><span>WF</span><?php endif; ?></a>
            <h3><?= e($wfName) ?></h3>
            <p><?= e($wfAbout) ?></p>
            <div class="wf131-footer-badges"><span><i class="fa-solid fa-person-chalkboard"></i>Live Classes</span><span><i class="fa-solid fa-route"></i>Clear Roadmap</span><span><i class="fa-solid fa-clipboard-check"></i>Weekly Tests</span></div>
            <?php if ($activeSocials): ?><div class="wf131-footer-socials" aria-label="Social media links"><?php foreach ($activeSocials as $social): ?><a href="<?= e($social['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($social['label']) ?>"><i class="<?= e($social['icon']) ?>"></i><span><?= e($social['label']) ?></span></a><?php endforeach; ?></div><?php endif; ?>
        </section>

        <div class="wf131-footer-links">
            <nav aria-label="Learning links"><h3>Learn</h3><a href="courses.php">Courses</a><a href="online-class.php">Online Class</a><a href="learning-roadmap.php">Learning Roadmap</a><a href="spoken-materials.php">Practice Room</a></nav>
            <nav aria-label="Test links"><h3>Practice & Test</h3><a href="weekly-test.php?type=basic">Basic Test</a><a href="weekly-test.php?type=previous">Previous Test</a><a href="weekly-test.php?type=upcoming">Upcoming Test</a><a href="student-revision.php">Revision</a></nav>
            <nav aria-label="Institute links"><h3>Institute</h3><a href="about.php">About Us</a><a href="gallery.php">Gallery</a><a href="reviews.php">Student Reviews</a><a href="contact.php">Contact</a></nav>
            <nav aria-label="Student links"><h3>Student</h3><a href="<?= e($footerAccountUrl) ?>"><?= e($footerAccountLabel) ?></a><a href="admission.php">Admission</a><a href="admin/login.php">Institute Login</a><a href="index.php">Home</a></nav>
        </div>

        <section class="wf131-footer-contact">
            <h3>Contact Institute</h3>
            <a href="tel:<?= e($wfPhoneClean) ?>"><i class="fa-solid fa-phone"></i><span><small>Phone</small><b><?= e($wfPhoneRaw) ?></b></span></a>
            <a href="mailto:<?= e($wfEmail) ?>"><i class="fa-solid fa-envelope"></i><span><small>Email</small><b><?= e($wfEmail) ?></b></span></a>
            <a href="<?= e(app_setting('map_url', GOOGLE_MAP_URL)) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i><span><small>Address</small><b><?= e($wfAddress) ?></b></span></a>
            <div><i class="fa-solid fa-clock"></i><span><small>Guidance Time</small><b><?= e($wfOfficeTime) ?></b></span></div>
        </section>
    </div>

    <div class="container wf131-footer-bottom"><span>© <?= date('Y') ?> <?= e($wfName) ?>. All rights reserved.</span><span>Learn • Practice • Test • Improve</span></div>
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
