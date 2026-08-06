<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/ui-components.php';
ensure_schema_updates();

$pageSlug = preg_replace('/[^a-z0-9-]+/', '-', strtolower(pathinfo(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'), PATHINFO_FILENAME)));
$pageStyles = isset($page_styles) && is_array($page_styles) ? $page_styles : [];
$lightweightLayout = !empty($lightweight_layout);
$headerStudent = function_exists('fetch_current_student') ? fetch_current_student() : null;
$studentDisplayName = $headerStudent ? trim((string)($headerStudent['student_name'] ?? $headerStudent['full_name'] ?? $headerStudent['name'] ?? 'Student')) : '';
$studentCtaLabel = $headerStudent ? 'My Dashboard' : 'Student Login';
$studentCtaUrl = $headerStudent ? 'student-dashboard.php' : 'student-auth.php';
$navItems = wf_public_nav_items((bool)$headerStudent);

$extraHeaderMenus = function_exists('fetch_public_nav_menu') ? fetch_public_nav_menu('header') : [];
$defaultNavKeys = ['index.php','courses.php','course-detail.php','online-class.php','spoken-materials.php','learning-roadmap.php','roadmap-lesson.php','weekly-test.php','weekly-result.php','weekly-exam-room.php','student-revision.php','admission.php','student-auth.php','student-dashboard.php','about.php','gallery.php','reviews.php','contact.php'];
$extraChildren = [];
foreach ($extraHeaderMenus as $menu) {
    $label = trim((string)($menu['label'] ?? ''));
    $url = trim((string)($menu['url'] ?? ''));
    $key = function_exists('nav_url_key') ? nav_url_key($url) : strtolower($url);
    if ($label === '' || $url === '' || in_array($key, $defaultNavKeys, true) || nav_is_blocked_feature($url, $label)) continue;
    $extraChildren[] = ['label' => $label, 'icon' => 'fa-solid fa-link', 'url' => $url, 'text' => 'Open page'];
}
if ($extraChildren) {
    $navItems[] = [
        'label' => 'More',
        'icon' => 'fa-solid fa-ellipsis',
        'pages' => array_map(static fn(array $item): string => basename((string)$item['url']), $extraChildren),
        'children' => $extraChildren,
    ];
}

$topAnnouncement = trim(app_setting('admission_marquee_text', 'Admission open — start your spoken English journey today.'));
$siteLogo = site_asset_url(app_setting('site_logo', ''));
$siteName = app_setting('site_name', APP_NAME);
$phoneRaw = app_setting('phone', APP_PHONE);
$phoneClean = preg_replace('/[^0-9+]/', '', $phoneRaw);
$shortAddress = trim((string)app_setting('mobile_short_address', 'Mariahu, Jaunpur'));
if ($shortAddress === '') $shortAddress = 'Mariahu, Jaunpur';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? $siteName) ?></title>
    <meta name="description" content="<?= e($meta_description ?? app_setting('admission_note', 'Practical spoken English, grammar, interview confidence and personality development classes.')) ?>">
    <?php if (defined('APP_REMOTE_FONTS') && APP_REMOTE_FONTS): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <?php
    $headerFavicon = site_asset_url(app_setting('site_favicon', 'assets/uploads/brand/wf-favicon.ico'));
    $headerPwa32 = site_asset_url(app_setting('site_favicon_32', 'assets/uploads/brand/wf-pwa-icon-32.png'));
    $headerPwa192 = site_asset_url(app_setting('site_pwa_icon_192', 'assets/uploads/brand/wf-pwa-icon-192.png'));
    ?>
    <?php if ($headerFavicon !== ''): ?><link rel="icon" href="<?= e($headerFavicon) ?>"><?php endif; ?>
    <?php if ($headerPwa32 !== ''): ?><link rel="icon" type="image/png" sizes="32x32" href="<?= e($headerPwa32) ?>"><?php endif; ?>
    <?php if ($headerPwa192 !== ''): ?><link rel="icon" type="image/png" sizes="192x192" href="<?= e($headerPwa192) ?>"><?php endif; ?>
    <link rel="manifest" href="manifest.webmanifest">
    <meta name="theme-color" content="#071d3e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Well Fare">
    <?php $headerAppleIcon = site_asset_url(app_setting('site_pwa_icon_180', 'assets/uploads/brand/wf-pwa-icon-180.png')); if ($headerAppleIcon !== ''): ?><link rel="apple-touch-icon" href="<?= e($headerAppleIcon) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/wf-design-tokens.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path($lightweightLayout ? 'assets/css/phase123-shell.css' : 'assets/css/style.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase123-ui-core.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase130-design-system.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase130-public-pages.css'))) ?>">
    <?php foreach ($pageStyles as $pageStyle): ?>
        <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path((string)$pageStyle))) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase133-controlled-ui.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/wf-components.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase137-visual-repair.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase138-mobile-ux.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase139-mobile-learning.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase141-learning-pages-mobile.css'))) ?>">
    <link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase142-interaction-fixes.css'))) ?>">
</head>
<body class="page-<?= e($pageSlug) ?> wf130-site wf133-site wf138-mobile-ui wf-ui">
<div id="appLoader" class="app-loader" aria-hidden="true"><div class="app-loader-card"><span class="app-loader-spinner"></span><b>Loading...</b></div></div>

<div class="wf127-topbar">
    <div class="container wf127-topbar-inner">
        <a class="wf127-topbar-place" href="<?= e(app_safe_href(app_setting('map_url', GOOGLE_MAP_URL), '#', true)) ?>" target="_blank" rel="noopener">
            <i class="fa-solid fa-location-dot" aria-hidden="true"></i><span class="wf133-place-full"><?= e(app_setting('address', APP_ADDRESS)) ?></span><span class="wf133-place-short"><?= e($shortAddress) ?></span>
        </a>
        <div class="wf127-announcement" aria-label="Institute announcement">
            <div class="wf127-announcement-track"><span><i class="fa-solid fa-bullhorn" aria-hidden="true"></i><?= e($topAnnouncement) ?></span><span aria-hidden="true"><i class="fa-solid fa-bullhorn"></i><?= e($topAnnouncement) ?></span></div>
        </div>
        <div class="wf129-topbar-actions"><a class="wf129-institute-link" href="admin/login.php" aria-label="Institute Login"><i class="fa-solid fa-building-shield" aria-hidden="true"></i><span>Institute Login</span></a><a class="wf127-topbar-phone" href="tel:<?= e($phoneClean) ?>" aria-label="Call <?= e($phoneRaw) ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span><?= e($phoneRaw) ?></span></a></div>
    </div>
</div>

<header class="wf127-header" data-site-header>
    <div class="container wf127-header-row">
        <a href="index.php" class="wf127-brand" aria-label="<?= e($siteName) ?> home">
            <?php if ($siteLogo !== ''): ?>
                <img src="<?= e($siteLogo) ?>" fetchpriority="high" decoding="async" alt="<?= e(app_setting('brand_logo_alt', $siteName)) ?>">
            <?php else: ?>
                <span class="wf127-brand-fallback"><?= e(app_setting('brand_short', 'WF')) ?></span>
            <?php endif; ?>
            <span class="wf127-brand-copy"><b><?= e(app_setting('brand_mobile_title', 'Well Fare')) ?></b><small>English Spoken</small></span>
        </a>

        <nav class="wf127-desktop-nav" aria-label="Main navigation">
            <?php foreach ($navItems as $navIndex => $item):
                $hasChildren = !empty($item['children']);
                $active = wf_nav_active($item['pages'] ?? ($item['url'] ?? []));
                $desktopPanelId = 'wfDesktopMenu' . $navIndex;
            ?>
                <?php if ($hasChildren): ?>
                    <div class="wf127-nav-group <?= $active ? 'is-active' : '' ?>">
                        <button type="button" class="wf127-nav-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="<?= e($desktopPanelId) ?>">
                            <?= e((string)$item['label']) ?><i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="wf127-mega-panel" id="<?= e($desktopPanelId) ?>">
                            <?php foreach ($item['children'] as $child): ?>
                                <a href="<?= e(app_safe_href((string)$child['url'])) ?>">
                                    <span><i class="<?= e((string)$child['icon']) ?>" aria-hidden="true"></i></span>
                                    <div><b><?= e((string)$child['label']) ?></b><small><?= e((string)($child['text'] ?? '')) ?></small></div>
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="wf127-nav-link <?= $active ? 'is-active' : '' ?>" href="<?= e(app_safe_href((string)$item['url'])) ?>"><?= e((string)$item['label']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="wf127-header-actions">
            <a class="wf127-account-btn" href="<?= e($studentCtaUrl) ?>"><span><i class="<?= $headerStudent ? 'fa-solid fa-gauge-high' : 'fa-solid fa-user-graduate' ?>" aria-hidden="true"></i><?= e($studentCtaLabel) ?></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <button class="wf127-menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-drawer-open><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
        </div>
    </div>
</header>

<div class="wf127-drawer-backdrop" data-drawer-close aria-hidden="true"></div>
<aside class="wf127-mobile-drawer" aria-label="Mobile navigation" aria-hidden="true" data-mobile-drawer>
    <div class="wf127-drawer-head">
        <a href="index.php" class="wf127-drawer-brand">
            <?php if ($siteLogo !== ''): ?><img src="<?= e($siteLogo) ?>" alt=""><?php endif; ?>
            <span><b><?= e(app_setting('brand_mobile_title', 'Well Fare')) ?></b><small>English Spoken</small></span>
        </a>
        <button type="button" aria-label="Close navigation" data-drawer-close><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="wf127-drawer-scroll">
        <nav class="wf127-drawer-nav">
            <?php foreach ($navItems as $index => $item):
                $hasChildren = !empty($item['children']);
                $active = wf_nav_active($item['pages'] ?? ($item['url'] ?? []));
                $drawerId = 'wfDrawerGroup' . $index;
            ?>
                <?php if ($hasChildren): ?>
                    <div class="wf127-drawer-group <?= $active ? 'is-active is-open' : '' ?>">
                        <button type="button" aria-expanded="<?= $active ? 'true' : 'false' ?>" aria-controls="<?= e($drawerId) ?>" data-drawer-group>
                            <span><i class="<?= e((string)$item['icon']) ?>" aria-hidden="true"></i><?= e((string)$item['label']) ?></span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div id="<?= e($drawerId) ?>" class="wf127-drawer-children" <?= $active ? '' : 'hidden' ?>>
                            <?php foreach ($item['children'] as $child): ?><a href="<?= e(app_safe_href((string)$child['url'])) ?>"><i class="<?= e((string)$child['icon']) ?>" aria-hidden="true"></i><span><b><?= e((string)$child['label']) ?></b><small><?= e((string)($child['text'] ?? '')) ?></small></span></a><?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="wf127-drawer-link <?= $active ? 'is-active' : '' ?>" href="<?= e(app_safe_href((string)$item['url'])) ?>"><i class="<?= e((string)$item['icon']) ?>" aria-hidden="true"></i><span><?= e((string)$item['label']) ?></span></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>
    <div class="wf127-drawer-actions">
        <div class="wf133-drawer-primary-actions">
            <a class="wf133-drawer-student" href="<?= e($studentCtaUrl) ?>"><span><i class="fa-solid fa-user-graduate" aria-hidden="true"></i><?= e($studentCtaLabel) ?></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <a class="wf133-drawer-admission" href="admission.php"><span><i class="fa-solid fa-user-plus" aria-hidden="true"></i>Admission</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <a class="wf138-drawer-call" href="tel:<?= e($phoneClean) ?>"><span><i class="fa-solid fa-phone" aria-hidden="true"></i>Call Now</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <a class="wf129-drawer-institute" href="admin/login.php"><i class="fa-solid fa-building-shield" aria-hidden="true"></i><span>Institute Login</span></a>
    </div>
</aside>
<main id="mainContent">
