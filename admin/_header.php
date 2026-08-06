<?php
if (!ob_get_level()) {
    ob_start(); // admin output buffer allows safe redirects/flash after shared header is loaded
}
require_once __DIR__ . '/../includes/functions.php'; require_admin(); ensure_schema_updates();
$adminPageSlug = preg_replace('/[^a-z0-9-]+/i', '-', pathinfo(basename($_SERVER['PHP_SELF'] ?? 'admin'), PATHINFO_FILENAME));
$adminPageStyles = isset($admin_page_styles) && is_array($admin_page_styles) ? $admin_page_styles : [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#071529">
    <title>Admin | <?= e(app_setting('site_name', APP_NAME)) ?></title>
    <?php $adminFavicon = site_asset_url(app_setting('site_favicon', app_setting('site_logo', ''))); if ($adminFavicon !== ''): ?>
    <link rel="icon" href="../<?= e($adminFavicon) ?>">
    <?php endif; ?>
    <?php if (defined('APP_REMOTE_FONTS') && APP_REMOTE_FONTS): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/wf-design-tokens.css'))) ?>">
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/style.css'))) ?>">
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase123-ui-core.css'))) ?>">
    <?php foreach ($adminPageStyles as $adminPageStyle): ?>
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path((string)$adminPageStyle))) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/wf-components.css'))) ?>">
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase138-mobile-ux.css'))) ?>">
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase139-mobile-learning.css'))) ?>">
</head>
<body class="admin-body page-admin-<?= e($adminPageSlug) ?> wf138-admin-mobile wf-ui">
<div id="appLoader" class="app-loader" aria-hidden="true"><div class="app-loader-card"><span class="app-loader-spinner"></span><b>Loading...</b></div></div>
<?= admin_toast_html() ?>
<div class="admin-shell">
    <aside class="admin-side" id="adminSide">
        <div class="admin-side-top">
            <a href="dashboard.php" class="brand admin-brand admin-logo-only" aria-label="Admin dashboard">
                <?php $adminLogo = site_asset_url(app_setting('site_logo', '')); ?>
                <?php if ($adminLogo !== ''): ?><span class="brand-logo-wrap"><img src="../<?= e($adminLogo) ?>" decoding="async" alt="<?= e(app_setting('brand_logo_alt', 'Logo')) ?>"></span><?php else: ?><span class="brand-mark"><?= e(app_setting('brand_short', 'WF')) ?></span><?php endif; ?>
            </a>
            <button class="admin-menu-close" type="button" data-admin-menu-close>×</button>
        </div>
        <div class="admin-menu-scroll">
            <div class="admin-menu-title">Main</div>
            <a class="<?= active_nav('dashboard.php') ?>" href="dashboard.php"><span class="menu-ico"><i class="fa-solid fa-gauge-high"></i></span><span>Dashboard</span></a>
            <a class="<?= active_nav('enquiries.php') ?>" href="enquiries.php"><span class="menu-ico"><i class="fa-solid fa-phone-volume"></i></span><span>Enquiries</span></a>
            <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['admissions.php','admission-view.php'], true) ? 'active' : '' ?>" href="admissions.php"><span class="menu-ico"><i class="fa-solid fa-user-plus"></i></span><span>Admissions</span></a>
            <a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['students.php','student-view.php'], true) ? 'active' : '' ?>" href="students.php"><span class="menu-ico"><i class="fa-solid fa-user-graduate"></i></span><span>Students</span></a>
            <a class="<?= active_nav('courses.php') ?>" href="courses.php"><span class="menu-ico"><i class="fa-solid fa-book-open"></i></span><span>Courses</span></a>
            <a class="<?= active_nav('testimonials.php') ?>" href="testimonials.php"><span class="menu-ico"><i class="fa-solid fa-star"></i></span><span>Testimonials</span></a>
            <a class="<?= active_nav('faculty.php') ?>" href="faculty.php"><span class="menu-ico"><i class="fa-solid fa-chalkboard-user"></i></span><span>Faculty</span></a>
            <a class="<?= active_nav('videos.php') ?>" href="videos.php"><span class="menu-ico"><i class="fa-solid fa-circle-play"></i></span><span>Videos</span></a>
            <a class="<?= active_nav('gallery.php') ?>" href="gallery.php"><span class="menu-ico"><i class="fa-solid fa-images"></i></span><span>Gallery</span></a>

            <div class="admin-menu-title">Learning CMS</div>
            <a class="<?= active_nav('materials.php') ?>" href="materials.php"><span class="menu-ico"><i class="fa-solid fa-folder-open"></i></span><span>Study Materials</span></a>
            <a class="<?= active_nav('roadmap.php') ?>" href="roadmap.php"><span class="menu-ico"><i class="fa-solid fa-route"></i></span><span>Learning Roadmap</span></a>
            <a class="<?= active_nav('weekly-tests.php') ?>" href="weekly-tests.php"><span class="menu-ico"><i class="fa-solid fa-clipboard-check"></i></span><span>Weekly Tests</span></a>
            <a class="<?= active_nav('batches.php') ?>" href="batches.php"><span class="menu-ico"><i class="fa-solid fa-clock"></i></span><span>Batches</span></a>
            <a class="<?= active_nav('faqs.php') ?>" href="faqs.php"><span class="menu-ico"><i class="fa-solid fa-circle-question"></i></span><span>FAQs</span></a>

            <div class="admin-menu-title">Website Control</div>
            <a class="<?= active_nav('content.php') ?>" href="content.php"><span class="menu-ico"><i class="fa-solid fa-table-cells-large"></i></span><span>Content Blocks</span></a>
            <a class="<?= active_nav('hero-banners.php') ?>" href="hero-banners.php"><span class="menu-ico"><i class="fa-solid fa-image"></i></span><span>Hero Banners</span></a>
            <a class="<?= active_nav('form-options.php') ?>" href="form-options.php"><span class="menu-ico"><i class="fa-solid fa-square-check"></i></span><span>Form Options</span></a>
            <a class="<?= active_nav('nav-menus.php') ?>" href="nav-menus.php"><span class="menu-ico"><i class="fa-solid fa-bars"></i></span><span>Navigation</span></a>
            <a class="<?= active_nav('seo.php') ?>" href="seo.php"><span class="menu-ico"><i class="fa-solid fa-magnifying-glass"></i></span><span>SEO</span></a>
            <a class="<?= active_nav('settings.php') ?>" href="settings.php"><span class="menu-ico"><i class="fa-solid fa-gear"></i></span><span>Site Settings</span></a>

            <div class="admin-menu-title">System</div>
            <a class="<?= active_nav('ui-library.php') ?>" href="ui-library.php"><span class="menu-ico"><i class="fa-solid fa-swatchbook"></i></span><span>UI Library</span></a>
            <a class="<?= active_nav('password.php') ?>" href="password.php"><span class="menu-ico"><i class="fa-solid fa-lock"></i></span><span>Password</span></a>
            <a class="<?= active_nav('system-check.php') ?>" href="system-check.php"><span class="menu-ico"><i class="fa-solid fa-screwdriver-wrench"></i></span><span>System Check</span></a>
            <a href="../index.php" target="_blank"><span class="menu-ico"><i class="fa-solid fa-globe"></i></span><span>View Website</span></a>
            <a href="logout.php"><span class="menu-ico"><i class="fa-solid fa-right-from-bracket"></i></span><span>Logout</span></a>
        </div>
        <div class="admin-side-footer">
            <b>Premium CMS</b>
            <span>Optimized panel for institute management</span>
        </div>
    </aside>
    <main class="admin-main">
        <?php
        $adminQuickLinks = [
            ['Dashboard', 'dashboard.php', 'Main', 'fa-solid fa-gauge-high'],
            ['Enquiries', 'enquiries.php', 'Main', 'fa-solid fa-phone-volume'],
            ['Admissions', 'admissions.php', 'Main', 'fa-solid fa-user-plus'],
            ['Students', 'students.php', 'Main', 'fa-solid fa-user-graduate'],
            ['Courses', 'courses.php', 'Main', 'fa-solid fa-book-open'],
            ['Testimonials', 'testimonials.php', 'Main', 'fa-solid fa-star'],
            ['Videos', 'videos.php', 'Main', 'fa-solid fa-circle-play'],
            ['Gallery', 'gallery.php', 'Main', 'fa-solid fa-images'],
            ['Study Materials', 'materials.php', 'Learning CMS', 'fa-solid fa-folder-open'],
            ['Learning Roadmap', 'roadmap.php', 'Learning CMS', 'fa-solid fa-route'],
            ['Weekly Tests', 'weekly-tests.php', 'Learning CMS', 'fa-solid fa-clipboard-check'],
            ['Batches', 'batches.php', 'Learning CMS', 'fa-solid fa-clock'],
            ['FAQs', 'faqs.php', 'Learning CMS', 'fa-solid fa-circle-question'],
            ['Content Blocks', 'content.php', 'Website Control', 'fa-solid fa-table-cells-large'],
            ['Hero Banners', 'hero-banners.php', 'Website Control', 'fa-solid fa-image'],
            ['Form Options', 'form-options.php', 'Website Control', 'fa-solid fa-square-check'],
            ['Navigation', 'nav-menus.php', 'Website Control', 'fa-solid fa-bars'],
            ['SEO', 'seo.php', 'Website Control', 'fa-solid fa-magnifying-glass'],
            ['Site Settings', 'settings.php', 'Website Control', 'fa-solid fa-gear'],
            ['UI Library', 'ui-library.php', 'System', 'fa-solid fa-swatchbook'],
            ['Password', 'password.php', 'System', 'fa-solid fa-lock'],
            ['System Check', 'system-check.php', 'System', 'fa-solid fa-screwdriver-wrench'],
        ];
        ?>
        <div class="admin-topbar">
            <button class="admin-drawer-btn" type="button" data-admin-menu-open aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
            <div class="admin-search-wrap">
                <span class="admin-search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="search" id="adminMenuSearch" placeholder="Search menu, page or module..." autocomplete="off">
                <div class="admin-search-results" id="adminMenuResults" aria-live="polite">
                    <?php foreach ($adminQuickLinks as $link): ?>
                        <a href="<?= e($link[1]) ?>" data-menu-search-item data-search-text="<?= e(strtolower($link[0] . ' ' . $link[2] . ' ' . $link[1])) ?>">
                            <span><i class="<?= e($link[3]) ?>"></i></span>
                            <b><?= e($link[0]) ?></b>
                            <small><?= e($link[2]) ?> → <?= e($link[1]) ?></small>
                        </a>
                    <?php endforeach; ?>
                    <div class="admin-search-empty" data-menu-search-empty>No matching admin link found.</div>
                </div>
            </div>
            <div class="admin-topbar-actions">
                <a class="btn btn-sm btn-soft" href="../index.php" target="_blank"><i class="fa-solid fa-globe"></i> View</a>
                <a class="btn btn-sm btn-dark" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
        <div class="admin-mobile-bar">
            <button class="btn btn-icon" type="button" data-admin-menu-open aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
            <strong><?= e(app_setting('brand_title', 'Admin')) ?></strong>
            <a class="btn btn-sm btn-soft" href="../index.php" target="_blank">View</a>
        </div>
