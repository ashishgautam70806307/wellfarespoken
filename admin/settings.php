<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$groups = [
    'Brand & Contact' => [
        'site_name' => ['Site Name', 'text'],
        'site_tagline' => ['Site Tagline', 'text'],
        'brand_short' => ['Brand Short Mark', 'text'],
        'brand_title' => ['Brand Main Text', 'text'],
        'brand_subtitle' => ['Brand Subtitle', 'text'],
        'brand_logo_alt' => ['Logo Alt Text', 'text'],
        'site_logo' => ['Logo Path', 'hidden'],
        'site_favicon' => ['Favicon Path', 'hidden'],
        'phone' => ['Phone Number', 'text'],
        'whatsapp' => ['WhatsApp Number with Country Code', 'text'],
        'email' => ['Email Address', 'email'],
        'address' => ['Institute Address', 'textarea'],
        'map_url' => ['Google Map URL', 'text'],
        'admission_marquee_text' => ['Topbar Moving Text', 'textarea']
    ],
    'Social Media Links' => [
        'facebook_url' => ['Facebook URL', 'url', 'https://facebook.com/your-page'],
        'instagram_url' => ['Instagram URL', 'url', 'https://instagram.com/your-profile'],
        'youtube_url' => ['YouTube Channel URL', 'url', 'https://youtube.com/@your-channel'],
        'twitter_url' => ['Twitter / X URL', 'url', 'https://x.com/your-profile'],
        'linkedin_url' => ['LinkedIn URL', 'url', 'https://linkedin.com/company/your-page']
    ],
    'Homepage Hero' => [
        'hero_eyebrow' => ['Hero Small Label', 'text'],
        'hero_headline' => ['Hero Headline', 'textarea'],
        'hero_subtitle' => ['Hero Subtitle', 'textarea'],
        'hero_primary_text' => ['Primary Button Text', 'text'],
        'hero_primary_url' => ['Primary Button URL', 'text'],
        'hero_secondary_text' => ['Secondary Button Text', 'text']
    ],
    'Homepage Section Text' => [
        'home_features_title' => ['Features Section Title', 'textarea'],
        'home_features_subtitle' => ['Features Section Subtitle', 'textarea'],
        'home_courses_title' => ['Courses Section Title', 'text'],
        'home_courses_subtitle' => ['Courses Section Subtitle', 'textarea'],
        'home_batches_eyebrow' => ['Batch Eyebrow', 'text'],
        'home_batches_title' => ['Batch Section Title', 'textarea'],
        'home_batches_subtitle' => ['Batch Section Subtitle', 'textarea'],
        'home_gallery_title' => ['Gallery Section Title', 'text'],
        'home_gallery_subtitle' => ['Gallery Section Subtitle', 'textarea'],
        'home_reviews_title' => ['Reviews Section Title', 'text'],
        'home_reviews_subtitle' => ['Reviews Section Subtitle', 'textarea'],
        'home_faculty_eyebrow' => ['Faculty Small Label', 'text'],
        'home_faculty_title' => ['Faculty Section Title', 'text'],
        'home_faculty_subtitle' => ['Faculty Section Subtitle', 'textarea'],
        'home_videos_title' => ['Videos Section Title', 'text'],
        'home_videos_subtitle' => ['Videos Section Subtitle', 'textarea'],
        'home_faq_eyebrow' => ['FAQ Eyebrow', 'text'],
        'home_faq_title' => ['FAQ Section Title', 'text'],
        'home_faq_subtitle' => ['FAQ Section Subtitle', 'textarea'],
        'home_cta_title' => ['Homepage CTA Title', 'textarea'],
        'admission_note' => ['Homepage CTA / Admission Note', 'textarea']
    ],
    'Inner Page Text' => [
        'about_eyebrow' => ['About Eyebrow', 'text'],
        'about_title' => ['About Page Title', 'textarea'],
        'about_subtitle' => ['About Page Subtitle', 'textarea'],
        'about_promise_title' => ['About Promise Title', 'text'],
        'about_promise_body' => ['About Promise Body', 'textarea'],
        'courses_page_title' => ['Courses Page Title', 'textarea'],
        'courses_page_subtitle' => ['Courses Page Subtitle', 'textarea'],
        'gallery_page_title' => ['Gallery Page Title', 'text'],
        'gallery_page_subtitle' => ['Gallery Page Subtitle', 'textarea'],
        'reviews_page_title' => ['Reviews Page Title', 'text'],
        'reviews_page_subtitle' => ['Reviews Page Subtitle', 'textarea'],
        'contact_page_title' => ['Contact Page Title', 'text'],
        'contact_page_subtitle' => ['Contact Page Subtitle', 'textarea'],
        'contact_office_time' => ['Contact Office Time / Support Note', 'textarea'],
        'contact_admission_help' => ['Contact Admission Help Text', 'textarea']
    ],
    'About Director' => [
        'director_name' => ['Director Name', 'text'],
        'director_designation' => ['Designation', 'text'],
        'director_experience' => ['Experience / Teaching Experience', 'text'],
        'director_qualification' => ['Qualification', 'text'],
        'director_speciality' => ['Speciality', 'text'],
        'director_message' => ['Director Message / Quote', 'textarea'],
        'director_bio' => ['Director Full Bio', 'textarea'],
        'director_photo' => ['Director Photo Path', 'hidden']
    ],
    'Admission Page Text' => [
        'admission_eyebrow' => ['Admission Eyebrow', 'text'],
        'admission_title' => ['Admission Page Title', 'textarea'],
        'admission_privacy_note' => ['Admission Privacy Note', 'text'],
        'admission_faq_title' => ['Admission FAQ Title', 'text'],
        'admission_faq_subtitle' => ['Admission FAQ Subtitle', 'textarea']
    ],
    'Footer' => [
        'footer_about' => ['Footer About Text', 'textarea'],
        'footer_copyright' => ['Copyright Text', 'text']
    ]
];
$flatKeys = [];
foreach ($groups as $fields) { foreach ($fields as $key => $meta) { $flatKeys[$key] = $meta; } }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $uploadError = '';
    $settingErrors = [];
    $newUploadedFiles = [];
    $mediaKeys = ['site_logo','site_favicon','director_photo'];
    $oldMedia = array_fill_keys($mediaKeys, '');
    try {
        $oldStmt = db()->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_logo','site_favicon','director_photo')");
        $oldStmt->execute();
        foreach ($oldStmt->fetchAll() as $row) {
            $key = (string)($row['setting_key'] ?? '');
            if (array_key_exists($key, $oldMedia)) $oldMedia[$key] = (string)($row['setting_value'] ?? '');
        }

        $socialKeys = ['facebook_url','instagram_url','youtube_url','twitter_url','linkedin_url'];
        $stmt = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        foreach ($flatKeys as $key => $meta) {
            $value = trim((string)($_POST[$key] ?? ''));
            if ($key === 'site_logo' && ($_POST['remove_site_logo'] ?? 'No') === 'Yes') $value = '';
            if ($key === 'site_favicon' && ($_POST['remove_site_favicon'] ?? 'No') === 'Yes') $value = '';
            if ($key === 'director_photo' && ($_POST['remove_director_photo'] ?? 'No') === 'Yes') $value = '';
            if (in_array($key, $socialKeys, true) && $value !== '') {
                if (!preg_match('#^https://#i', $value)) $value = 'https://' . preg_replace('#^http://#i', '', ltrim($value, '/'));
                if (app_safe_https_url($value, '') === '') {
                    $settingErrors[] = ($meta[0] ?? $key) . ' is not a valid URL.';
                    continue;
                }
            }
            $stmt->execute([$key, $value]);
        }

        if (!empty($_FILES['site_logo_file']['name'])) {
            $logoPath = upload_brand_asset($_FILES['site_logo_file'], 'logo');
            if ($logoPath) {
                $newUploadedFiles[] = $logoPath;
                $stmt->execute(['site_logo', $logoPath]);
            } else {
                $uploadError = 'Logo upload failed. Use JPG, JPEG, PNG or WEBP under 2 MB.';
            }
        }
        if (!empty($_FILES['site_favicon_file']['name'])) {
            $faviconPath = upload_brand_asset($_FILES['site_favicon_file'], 'favicon');
            if ($faviconPath) {
                $newUploadedFiles[] = $faviconPath;
                $stmt->execute(['site_favicon', $faviconPath]);
            } else {
                $uploadError = 'Favicon upload failed. Use JPG, JPEG, PNG or WEBP under 2 MB.';
            }
        }
        if (!empty($_FILES['director_photo_file']['name'])) {
            try {
                $directorPhotoPath = upload_gallery_image($_FILES['director_photo_file']);
                if ($directorPhotoPath) {
                    $newUploadedFiles[] = $directorPhotoPath;
                    $stmt->execute(['director_photo', $directorPhotoPath]);
                }
            } catch (Throwable $e) {
                error_log('[admin-settings-upload] ' . $e->__toString());
                $uploadError = 'Director photo upload failed. Use JPG, JPEG, PNG or WEBP under 2 MB.';
            }
        }

        $finalMedia = array_fill_keys($mediaKeys, '');
        $finalStmt = db()->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_logo','site_favicon','director_photo')");
        $finalStmt->execute();
        foreach ($finalStmt->fetchAll() as $row) {
            $key = (string)($row['setting_key'] ?? '');
            if (array_key_exists($key, $finalMedia)) $finalMedia[$key] = (string)($row['setting_value'] ?? '');
        }
        foreach ($mediaKeys as $key) {
            if ($oldMedia[$key] !== '' && $oldMedia[$key] !== $finalMedia[$key]) managed_upload_cleanup($oldMedia[$key]);
        }
    } catch (Throwable $e) {
        foreach ($newUploadedFiles as $path) managed_upload_cleanup($path);
        error_log('[admin-settings] ' . $e->__toString());
        $uploadError = 'Settings could not be saved completely. Please try again.';
    }

    if ($uploadError !== '' || $settingErrors) {
        $messages = array_values(array_filter(array_merge([$uploadError], $settingErrors)));
        flash('error', implode(' ', $messages));
    } else {
        flash('success', 'Dynamic site settings updated. Replaced managed images are cleaned up automatically.');
    }
    redirect('settings.php');
}
?>
<div class="admin-top"><div><h1>Site Settings</h1><p>Control brand, contact, about page, director profile, homepage, inner page and footer text from admin.</p></div><div class="admin-actions"><a class="btn btn-soft" href="../index.php" target="_blank">View Website</a></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
<form class="form-box" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
        <?php foreach ($groups as $groupTitle => $fields): ?>
            <div class="form-section-title"><span>⚙️</span><?= e($groupTitle) ?></div>
            <?php if ($groupTitle === 'Brand & Contact'): ?>
                <div class="field full">
                    <label>Current Logo / Favicon</label>
                    <div class="brand-preview-row">
                        <?php $currentLogo = site_asset_url(app_setting('site_logo', '')); $currentFavicon = site_asset_url(app_setting('site_favicon', '')); ?>
                        <div><?php if ($currentLogo): ?><img src="../<?= e($currentLogo) ?>" alt="Logo preview"><?php else: ?><span class="brand-mark"><?= e(app_setting('brand_short','WF')) ?></span><?php endif; ?><small>Logo</small></div>
                        <div><?php if ($currentFavicon): ?><img src="../<?= e($currentFavicon) ?>" alt="Favicon preview"><?php else: ?><span class="brand-mark">★</span><?php endif; ?><small>Favicon</small></div>
                    </div>
                </div>
                <div class="field"><label>Upload Logo</label><input type="file" name="site_logo_file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"><span class="help">Allowed: PNG, JPG, JPEG or WEBP, max 2 MB.</span><?php if (app_setting('site_logo','') !== ''): ?><label class="help"><input type="checkbox" name="remove_site_logo" value="Yes"> Remove current logo</label><?php endif; ?></div>
                <div class="field"><label>Upload Favicon</label><input type="file" name="site_favicon_file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"><span class="help">Allowed: square PNG, JPG, JPEG or WEBP, max 2 MB.</span><?php if (app_setting('site_favicon','') !== ''): ?><label class="help"><input type="checkbox" name="remove_site_favicon" value="Yes"> Remove current favicon</label><?php endif; ?></div>
                <div class="field full"><label>Logo Path</label><input name="site_logo" value="<?= e(app_setting('site_logo', '')) ?>" placeholder="assets/uploads/brand/logo.png"><span class="help">Upload a logo above or paste an existing path.</span></div>
                <div class="field full"><label>Favicon Path</label><input name="site_favicon" value="<?= e(app_setting('site_favicon', '')) ?>" placeholder="assets/uploads/brand/favicon.png"><span class="help">Upload a favicon above or paste an existing path.</span></div>
            <?php endif; ?>
            <?php if ($groupTitle === 'Social Media Links'): ?>
                <div class="field full">
                    <div class="wf139-social-admin-note">
                        <span><i class="fa-solid fa-share-nodes" aria-hidden="true"></i></span>
                        <div><b>Dynamic footer social icons</b><small>Save a valid profile URL to show its icon in the website footer. Leave a field blank to hide that platform.</small></div>
                    </div>
                    <div class="wf139-social-admin-preview" aria-label="Configured social link status">
                        <?php foreach ([
                            'facebook_url' => ['Facebook','fa-brands fa-facebook-f'],
                            'instagram_url' => ['Instagram','fa-brands fa-instagram'],
                            'youtube_url' => ['YouTube','fa-brands fa-youtube'],
                            'twitter_url' => ['X','fa-brands fa-x-twitter'],
                            'linkedin_url' => ['LinkedIn','fa-brands fa-linkedin-in'],
                        ] as $socialKey => [$socialLabel, $socialIcon]): $socialActive = trim((string)app_setting($socialKey, '')) !== ''; ?>
                            <span class="<?= $socialActive ? 'is-active' : 'is-hidden' ?>"><i class="<?= e($socialIcon) ?>" aria-hidden="true"></i><b><?= e($socialLabel) ?></b><small><?= $socialActive ? 'Visible' : 'Hidden' ?></small></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($groupTitle === 'About Director'): ?>
                <div class="field full" id="director-settings">
                    <label>Current Director Photo</label>
                    <div class="brand-preview-row director-admin-preview">
                        <?php $currentDirectorPhoto = site_asset_url(app_setting('director_photo', '')); ?>
                        <div><?php if ($currentDirectorPhoto): ?><img src="../<?= e($currentDirectorPhoto) ?>" alt="Director photo preview"><?php else: ?><span class="brand-mark"><?= e(mb_substr(app_setting('director_name','D') ?: 'D',0,1)) ?></span><?php endif; ?><small>Director Photo</small></div>
                    </div>
                </div>
                <div class="field"><label>Choose Director Image</label><input type="file" name="director_photo_file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"><span class="help">Allowed: PNG, JPG, JPEG or WEBP. Best size: square or portrait image under 2 MB.</span><?php if (app_setting('director_photo','') !== ''): ?><label class="help"><input type="checkbox" name="remove_director_photo" value="Yes"> Remove current director photo</label><?php endif; ?></div>
                <div class="field"><label>Director Photo Path</label><input name="director_photo" value="<?= e(app_setting('director_photo', '')) ?>" placeholder="assets/uploads/gallery/director.png"><span class="help">Upload image above or paste existing image path.</span></div>
            <?php endif; ?>
            <?php foreach ($fields as $key => $meta): $label = $meta[0]; $type = $meta[1]; $placeholder = $meta[2] ?? ''; ?>
                <?php if ($type !== 'hidden'): ?>
                <div class="field <?= $type === 'textarea' ? 'full' : '' ?>">
                    <label><?= e($label) ?></label>
                    <?php if ($type === 'textarea'): ?><textarea name="<?= e($key) ?>" placeholder="<?= e($placeholder) ?>"><?= e(app_setting($key, '')) ?></textarea><?php else: ?><input type="<?= e($type) ?>" name="<?= e($key) ?>" value="<?= e(app_setting($key, '')) ?>" placeholder="<?= e($placeholder) ?>"><?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="field full"><button class="btn btn-primary">Save All Dynamic Settings</button></div>
    </div>
</form>
<?php require_once __DIR__ . '/_footer.php'; ?>
