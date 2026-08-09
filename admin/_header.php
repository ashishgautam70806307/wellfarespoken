<?php
if (!ob_get_level()) {
    ob_start(); // admin output buffer allows safe redirects/flash after shared header is loaded
}
require_once __DIR__ . '/../includes/functions.php'; require_admin(); ensure_schema_updates();
$adminPageSlug = preg_replace('/[^a-z0-9-]+/i', '-', pathinfo(basename($_SERVER['PHP_SELF'] ?? 'admin'), PATHINFO_FILENAME));
$adminPageStyles = isset($admin_page_styles) && is_array($admin_page_styles) ? $admin_page_styles : [];
$adminPageFinalStyles = isset($admin_page_final_styles) && is_array($admin_page_final_styles) ? $admin_page_final_styles : [];
$canMainMenu = admin_can('dashboard.view') || admin_can('enquiries.manage') || admin_can('admissions.manage') || admin_can('students.manage') || admin_can('courses.manage') || admin_can('content.manage');
$canLearningMenu = admin_can('materials.manage') || admin_can('roadmap.manage') || admin_can('tests.manage') || admin_can('batches.manage') || admin_can('content.manage');
$canWebsiteMenu = admin_can('content.manage') || admin_can('settings.manage');
$phase148SchemaReady = function_exists('phase148_schema_ready') ? phase148_schema_ready() : true;
$adminPasswordGate = function_exists('admin_password_gate_active') ? admin_password_gate_active() : false;
if ($adminPasswordGate) { $canMainMenu = false; $canLearningMenu = false; $canWebsiteMenu = false; }
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
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase149-admin-resilience.css'))) ?>">
    <?php foreach ($adminPageFinalStyles as $adminPageFinalStyle): ?>
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path((string)$adminPageFinalStyle))) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase150-security-ui.css'))) ?>">
</head>
<body class="admin-body page-admin-<?= e($adminPageSlug) ?> wf138-admin-mobile wf-ui">
<div id="appLoader" class="app-loader" aria-hidden="true"><div class="app-loader-card"><span class="app-loader-spinner"></span><b>Loading...</b></div></div>
<?= admin_toast_html() ?>
<div class="admin-shell">
    <aside class="admin-side" id="adminSide">
        <div class="admin-side-top">
            <a href="<?= $adminPasswordGate ? 'password.php?required=1' : 'dashboard.php' ?>" class="brand admin-brand admin-logo-only" aria-label="<?= $adminPasswordGate ? 'Account security' : 'Admin dashboard' ?>">
                <?php $adminLogo = site_asset_url(app_setting('site_logo', '')); ?>
                <?php if ($adminLogo !== ''): ?><span class="brand-logo-wrap"><img src="../<?= e($adminLogo) ?>" decoding="async" alt="<?= e(app_setting('brand_logo_alt', 'Logo')) ?>"></span><?php else: ?><span class="brand-mark"><?= e(app_setting('brand_short', 'WF')) ?></span><?php endif; ?>
            </a>
            <button class="admin-menu-close" type="button" data-admin-menu-close>×</button>
        </div>
        <div class="admin-menu-scroll">
            <?php if ($adminPasswordGate): ?>
            <div class="admin-menu-title">Account Setup</div>
            <a class="<?= active_nav('password.php') ?>" href="password.php?required=1"><span class="menu-ico"><i class="fa-solid fa-lock"></i></span><span>Password & MFA</span></a>
            <a href="logout.php"><span class="menu-ico"><i class="fa-solid fa-right-from-bracket"></i></span><span>Logout</span></a>
            <div class="wf153-password-gate-note"><i class="fa-solid fa-shield-halved"></i><span>Finish the temporary password change to unlock assigned modules.</span></div>
            <?php else: ?>
            <?php if ($canMainMenu): ?><div class="admin-menu-title">Main</div><?php endif; ?>
            <?php if (admin_can('dashboard.view')): ?><a class="<?= active_nav('dashboard.php') ?>" href="dashboard.php"><span class="menu-ico"><i class="fa-solid fa-gauge-high"></i></span><span>Dashboard</span></a><?php endif; ?>
            <?php if (admin_can('enquiries.manage')): ?><a class="<?= active_nav('enquiries.php') ?>" href="enquiries.php"><span class="menu-ico"><i class="fa-solid fa-phone-volume"></i></span><span>Enquiries</span></a><?php endif; ?>
            <?php if (admin_can('admissions.manage')): ?><a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['admissions.php','admission-view.php'], true) ? 'active' : '' ?>" href="admissions.php"><span class="menu-ico"><i class="fa-solid fa-user-plus"></i></span><span>Admissions</span></a><?php endif; ?>
            <?php if (admin_can('students.manage')): ?><a class="<?= in_array(basename($_SERVER['PHP_SELF']), ['students.php','student-view.php'], true) ? 'active' : '' ?>" href="students.php"><span class="menu-ico"><i class="fa-solid fa-user-graduate"></i></span><span>Student Accounts</span></a><?php endif; ?>
            <?php if (admin_can('courses.manage')): ?><a class="<?= active_nav('courses.php') ?>" href="courses.php"><span class="menu-ico"><i class="fa-solid fa-book-open"></i></span><span>Courses</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('testimonials.php') ?>" href="testimonials.php"><span class="menu-ico"><i class="fa-solid fa-star"></i></span><span>Testimonials</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('faculty.php') ?>" href="faculty.php"><span class="menu-ico"><i class="fa-solid fa-chalkboard-user"></i></span><span>Faculty</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('videos.php') ?>" href="videos.php"><span class="menu-ico"><i class="fa-solid fa-circle-play"></i></span><span>Videos</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('gallery.php') ?>" href="gallery.php"><span class="menu-ico"><i class="fa-solid fa-images"></i></span><span>Gallery</span></a><?php endif; ?>

            <?php if ($canLearningMenu): ?><div class="admin-menu-title">Learning CMS</div><?php endif; ?>
            <?php if (admin_can('materials.manage')): ?><a class="<?= active_nav('materials.php') ?>" href="materials.php"><span class="menu-ico"><i class="fa-solid fa-folder-open"></i></span><span>Study Materials</span></a><?php endif; ?>
            <?php if (admin_can('roadmap.manage')): ?><a class="<?= active_nav('roadmap.php') ?>" href="roadmap.php"><span class="menu-ico"><i class="fa-solid fa-route"></i></span><span>Learning Roadmap</span></a><?php endif; ?>
            <?php if (admin_can('tests.manage')): ?><a class="<?= active_nav('weekly-tests.php') ?>" href="weekly-tests.php"><span class="menu-ico"><i class="fa-solid fa-clipboard-check"></i></span><span>Weekly Tests</span></a><?php endif; ?>
            <?php if (admin_can('batches.manage')): ?><a class="<?= active_nav('batches.php') ?>" href="batches.php"><span class="menu-ico"><i class="fa-solid fa-clock"></i></span><span>Batches</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('faqs.php') ?>" href="faqs.php"><span class="menu-ico"><i class="fa-solid fa-circle-question"></i></span><span>FAQs</span></a><?php endif; ?>

            <?php if ($canWebsiteMenu): ?><div class="admin-menu-title">Website Control</div><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('content.php') ?>" href="content.php"><span class="menu-ico"><i class="fa-solid fa-table-cells-large"></i></span><span>Content Blocks</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('hero-banners.php') ?>" href="hero-banners.php"><span class="menu-ico"><i class="fa-solid fa-image"></i></span><span>Hero Banners</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('form-options.php') ?>" href="form-options.php"><span class="menu-ico"><i class="fa-solid fa-square-check"></i></span><span>Form Options</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('nav-menus.php') ?>" href="nav-menus.php"><span class="menu-ico"><i class="fa-solid fa-bars"></i></span><span>Navigation</span></a><?php endif; ?>
            <?php if (admin_can('content.manage')): ?><a class="<?= active_nav('seo.php') ?>" href="seo.php"><span class="menu-ico"><i class="fa-solid fa-magnifying-glass"></i></span><span>SEO</span></a><?php endif; ?>
            <?php if (admin_can('settings.manage')): ?><a class="<?= active_nav('settings.php') ?>" href="settings.php"><span class="menu-ico"><i class="fa-solid fa-gear"></i></span><span>Site Settings</span></a><?php endif; ?>

            <div class="admin-menu-title">System</div>
            <?php if ($phase148SchemaReady && admin_can('admins.manage')): ?>
            <a class="<?= active_nav('admin-users.php') ?>" href="admin-users.php"><span class="menu-ico"><i class="fa-solid fa-user-shield"></i></span><span>Admin Users</span></a>
            <?php if (admin_role_key() === 'super_admin'): ?><a class="<?= active_nav('roles.php') ?>" href="roles.php"><span class="menu-ico"><i class="fa-solid fa-shield-halved"></i></span><span>Roles & Permissions</span></a><?php endif; ?>
            <a class="<?= active_nav('audit-log.php') ?>" href="audit-log.php"><span class="menu-ico"><i class="fa-solid fa-clock-rotate-left"></i></span><span>Admin Audit Log</span></a>
            <?php endif; ?>
            <?php if (admin_can('system.manage')): ?>
            <a class="<?= active_nav('system-check.php') ?>" href="system-check.php"><span class="menu-ico"><i class="fa-solid fa-screwdriver-wrench"></i></span><span>System Check</span></a>
            <?php endif; ?>
            <a class="<?= active_nav('password.php') ?>" href="password.php"><span class="menu-ico"><i class="fa-solid fa-lock"></i></span><span>Password & MFA</span></a>
            <a href="../index.php" target="_blank"><span class="menu-ico"><i class="fa-solid fa-globe"></i></span><span>View Website</span></a>
            <a href="logout.php"><span class="menu-ico"><i class="fa-solid fa-right-from-bracket"></i></span><span>Logout</span></a>
            <?php endif; ?>
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
            ['Student Accounts', 'students.php', 'Main', 'fa-solid fa-user-graduate'],
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
            ['Admin Users', 'admin-users.php', 'System', 'fa-solid fa-user-shield'],
            ['Roles & Permissions', 'roles.php', 'System', 'fa-solid fa-shield-halved'],
            ['Admin Audit Log', 'audit-log.php', 'System', 'fa-solid fa-clock-rotate-left'],
            ['Password & MFA', 'password.php', 'System', 'fa-solid fa-lock'],
            ['System Check', 'system-check.php', 'System', 'fa-solid fa-screwdriver-wrench'],
        ];
        if ($adminPasswordGate) {
            $adminQuickLinks = array_values(array_filter($adminQuickLinks, static fn(array $link): bool => (string)$link[1] === 'password.php'));
        } else {
            $adminQuickLinks = array_values(array_filter($adminQuickLinks, static function(array $link) use ($phase148SchemaReady): bool {
                if (!$phase148SchemaReady && in_array((string)$link[1], ['admin-users.php','roles.php','audit-log.php'], true)) return false;
                if ((string)$link[1] === 'roles.php' && admin_role_key() !== 'super_admin') return false;
                $permission = admin_page_permission((string)$link[1]);
                return $permission === null || admin_can($permission);
            }));
        }
        ?>
        <div class="admin-topbar">
            <button class="admin-drawer-btn" type="button" data-admin-menu-open aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
            <div class="admin-search-wrap <?= $adminPasswordGate ? 'is-password-gated' : '' ?>">
                <span class="admin-search-icon"><i class="fa-solid <?= $adminPasswordGate ? 'fa-lock' : 'fa-magnifying-glass' ?>"></i></span>
                <input type="search" id="adminMenuSearch" placeholder="<?= $adminPasswordGate ? 'Complete password setup to unlock modules' : 'Search menu, page or module...' ?>" autocomplete="off" <?= $adminPasswordGate ? 'disabled' : '' ?>>
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
        <?php if (!$phase148SchemaReady && !in_array(basename((string)($_SERVER['PHP_SELF'] ?? '')), ['system-check.php','setup.php'], true)): ?>
        <div class="wf149-schema-warning" role="alert">
            <i class="fa-solid fa-database" aria-hidden="true"></i>
            <div><b>Database upgrade is incomplete.</b><span>Phase 148 security tables/columns are missing. Import <code>sql/phase148_critical_backend_hardening.sql</code> before using Admin Users, Roles, Admissions lifecycle or other new backend controls.</span></div>
            <a class="btn btn-sm btn-dark" href="system-check.php">Open System Check</a>
        </div>
        <?php endif; ?>
