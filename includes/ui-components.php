<?php

if (!function_exists('wf_current_page')) {
    function wf_current_page(): string
    {
        return strtolower(basename((string)($_SERVER['PHP_SELF'] ?? 'index.php')));
    }
}

if (!function_exists('wf_nav_active')) {
    function wf_nav_active(array|string $pages): bool
    {
        $pages = is_array($pages) ? $pages : [$pages];
        return in_array(wf_current_page(), array_map('strtolower', $pages), true);
    }
}

if (!function_exists('wf_public_nav_items')) {
    function wf_public_nav_items(bool $studentLoggedIn = false): array
    {
        return [
            [
                'label' => 'Home',
                'icon' => 'fa-solid fa-house',
                'url' => 'index.php',
                'pages' => ['index.php'],
            ],
            [
                'label' => 'Learn',
                'icon' => 'fa-solid fa-book-open-reader',
                'pages' => ['courses.php', 'course-detail.php', 'online-class.php', 'spoken-materials.php', 'learning-roadmap.php', 'roadmap-lesson.php', 'faculty-profile.php'],
                'children' => [
                    ['label' => 'Courses', 'icon' => 'fa-solid fa-book-open', 'url' => 'courses.php', 'text' => 'Choose your level'],
                    ['label' => 'Online Class', 'icon' => 'fa-solid fa-laptop-file', 'url' => 'online-class.php', 'text' => 'Join from mobile or laptop'],
                    ['label' => 'Practice Room', 'icon' => 'fa-solid fa-microphone-lines', 'url' => 'spoken-materials.php', 'text' => 'Speak and improve'],
                    ['label' => 'Learning Roadmap', 'icon' => 'fa-solid fa-route', 'url' => 'learning-roadmap.php', 'text' => 'Follow the correct path'],
                ],
            ],
            [
                'label' => 'Test & Practice',
                'icon' => 'fa-solid fa-clipboard-check',
                'pages' => ['weekly-test.php', 'weekly-result.php', 'weekly-exam-room.php', 'student-revision.php'],
                'children' => [
                    ['label' => 'Weekly Test', 'icon' => 'fa-solid fa-clipboard-check', 'url' => 'weekly-test.php', 'text' => 'Practice or official exam'],
                    ['label' => 'Revision', 'icon' => 'fa-solid fa-rotate-left', 'url' => 'student-revision.php', 'text' => 'Repeat wrong answers'],
                ],
            ],
            [
                'label' => 'Student',
                'icon' => 'fa-solid fa-user-graduate',
                'pages' => ['admission.php', 'student-auth.php', 'student-dashboard.php'],
                'children' => [
                    ['label' => 'Admission', 'icon' => 'fa-solid fa-user-plus', 'url' => 'admission.php', 'text' => 'Join a suitable batch'],
                    [
                        'label' => $studentLoggedIn ? 'My Dashboard' : 'Student Login',
                        'icon' => $studentLoggedIn ? 'fa-solid fa-gauge-high' : 'fa-solid fa-right-to-bracket',
                        'url' => $studentLoggedIn ? 'student-dashboard.php' : 'student-auth.php',
                        'text' => $studentLoggedIn ? 'Progress and results' : 'Save your progress',
                    ],
                ],
            ],
            [
                'label' => 'About',
                'icon' => 'fa-solid fa-circle-info',
                'pages' => ['about.php', 'gallery.php', 'reviews.php'],
                'children' => [
                    ['label' => 'About Institute', 'icon' => 'fa-solid fa-circle-info', 'url' => 'about.php', 'text' => 'Teaching approach and director'],
                    ['label' => 'Gallery', 'icon' => 'fa-solid fa-images', 'url' => 'gallery.php', 'text' => 'Classroom and activities'],
                    ['label' => 'Student Reviews', 'icon' => 'fa-solid fa-star', 'url' => 'reviews.php', 'text' => 'Learner experiences'],
                ],
            ],
            [
                'label' => 'Contact',
                'icon' => 'fa-solid fa-phone',
                'url' => 'contact.php',
                'pages' => ['contact.php'],
            ],
        ];
    }
}


if (!function_exists('wf_button')) {
    function wf_button(string $label, string $url = '', string $variant = 'primary', string $icon = '', array $attributes = []): string
    {
        $label = trim($label);
        if ($label === '') return '';
        $variant = in_array($variant, ['primary','secondary','success','danger'], true) ? $variant : 'primary';
        $class = 'wf-btn wf-btn-' . $variant;
        if (!empty($attributes['class'])) $class .= ' ' . preg_replace('/[^a-zA-Z0-9 _-]/', '', (string)$attributes['class']);
        $iconClass = $icon !== '' ? app_icon_class($icon, 'fa-solid fa-arrow-right') : '';
        $content = '<span class="wf-btn-label">' . ($iconClass !== '' ? '<i class="' . e($iconClass) . '" aria-hidden="true"></i>' : '') . e($label) . '</span>';
        if ($url !== '') {
            $target = !empty($attributes['target']) ? ' target="' . e((string)$attributes['target']) . '"' : '';
            $rel = !empty($attributes['rel']) ? ' rel="' . e((string)$attributes['rel']) . '"' : '';
            return '<a class="' . e($class) . '" href="' . e($url) . '"' . $target . $rel . '>' . $content . '</a>';
        }
        $type = in_array(($attributes['type'] ?? 'button'), ['button','submit','reset'], true) ? (string)$attributes['type'] : 'button';
        return '<button class="' . e($class) . '" type="' . e($type) . '">' . $content . '</button>';
    }
}

if (!function_exists('wf_page_hero')) {
    function wf_page_hero(array $config): void
    {
        $eyebrow = trim((string)($config['eyebrow'] ?? ''));
        $title = trim((string)($config['title'] ?? ''));
        $text = trim((string)($config['text'] ?? ''));
        $icon = app_icon_class((string)($config['icon'] ?? ''), 'fa-solid fa-graduation-cap');
        $theme = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($config['theme'] ?? 'navy')));
        $actions = is_array($config['actions'] ?? null) ? $config['actions'] : [];
        $steps = is_array($config['steps'] ?? null) ? $config['steps'] : [];
        $compact = !empty($config['compact']);
        $class = 'wf-page-hero wf-page-hero-' . ($theme ?: 'navy') . ($compact ? ' is-compact' : '');
        ?>
        <section class="<?= e($class) ?>">
            <div class="wf-page-hero-orb wf-page-hero-orb-one" aria-hidden="true"></div>
            <div class="wf-page-hero-orb wf-page-hero-orb-two" aria-hidden="true"></div>
            <div class="container wf-page-hero-inner">
                <div class="wf-page-hero-copy">
                    <?php if ($eyebrow !== ''): ?><span class="wf-page-kicker"><i class="<?= e($icon) ?>" aria-hidden="true"></i><?= e($eyebrow) ?></span><?php endif; ?>
                    <?php if ($title !== ''): ?><h1><?= e($title) ?></h1><?php endif; ?>
                    <?php if ($text !== ''): ?><p><?= e($text) ?></p><?php endif; ?>
                    <?php if ($actions): ?>
                        <div class="wf-page-hero-actions">
                            <?php foreach ($actions as $index => $action):
                                $label = trim((string)($action['label'] ?? ''));
                                $url = trim((string)($action['url'] ?? '#'));
                                if ($label === '') continue;
                                $actionIcon = app_icon_class((string)($action['icon'] ?? ''), $index === 0 ? 'fa-solid fa-arrow-right' : 'fa-solid fa-circle-info');
                            ?>
                                <?= wf_button($label, $url, $index === 0 ? 'primary' : 'secondary', $actionIcon) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="wf-page-hero-visual" aria-hidden="true">
                    <span class="wf-page-hero-icon"><i class="<?= e($icon) ?>"></i></span>
                    <span class="wf-page-hero-line"></span>
                    <span class="wf-page-hero-dot dot-one"></span>
                    <span class="wf-page-hero-dot dot-two"></span>
                    <span class="wf-page-hero-dot dot-three"></span>
                </div>
            </div>
            <?php if ($steps): ?>
                <div class="container wf-page-mini-flow" aria-label="Page process">
                    <?php foreach ($steps as $index => $step): ?>
                        <span><b><?= e((string)($index + 1)) ?></b><?= e((string)$step) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }
}

if (!function_exists('wf_section_heading')) {
    function wf_section_heading(string $eyebrow, string $title, string $text = '', array $action = []): void
    {
        ?>
        <header class="wf-section-heading">
            <div>
                <?php if ($eyebrow !== ''): ?><span class="wf-section-kicker"><?= e($eyebrow) ?></span><?php endif; ?>
                <h2><?= e($title) ?></h2>
                <?php if ($text !== ''): ?><p><?= e($text) ?></p><?php endif; ?>
            </div>
            <?php if (!empty($action['label']) && !empty($action['url'])): ?>
                <a href="<?= e((string)$action['url']) ?>"><?= e((string)$action['label']) ?><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <?php endif; ?>
        </header>
        <?php
    }
}

if (!function_exists('wf_text_limit')) {
    function wf_text_limit(string $text, int $limit = 110): string
    {
        $text = trim((string)preg_replace('/\s+/', ' ', strip_tags($text)));
        if ($text === '') return '';
        return mb_strimwidth($text, 0, $limit, '…', 'UTF-8');
    }
}


if (!function_exists('wf_faq_split')) {
    function wf_faq_split(array $faqs, array $config = []): void
    {
        if (!$faqs) return;
        $eyebrow = trim((string)($config['eyebrow'] ?? 'Questions'));
        $title = trim((string)($config['title'] ?? 'Frequently asked questions'));
        $text = trim((string)($config['text'] ?? 'Open a question to view the answer.'));
        $icon = app_icon_class((string)($config['icon'] ?? ''), 'fa-solid fa-circle-question');
        ?>
        <section class="wf129-faq-section">
            <div class="container wf129-faq-layout">
                <aside class="wf129-faq-visual" data-reveal>
                    <span class="wf129-faq-icon"><i class="<?= e($icon) ?>" aria-hidden="true"></i></span>
                    <?php if ($eyebrow !== ''): ?><span class="wf-section-kicker"><?= e($eyebrow) ?></span><?php endif; ?>
                    <h2><?= e($title) ?></h2>
                    <?php if ($text !== ''): ?><p><?= e($text) ?></p><?php endif; ?>
                    <div class="wf129-faq-count"><b><?= e((string)count($faqs)) ?></b><span>answers available</span></div>
                </aside>
                <div class="wf129-faq-list" data-faq-list>
                    <?php foreach ($faqs as $index => $faq): ?>
                        <details <?= $index === 0 ? 'open' : '' ?>>
                            <summary><span><?= e(str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT)) ?></span><b><?= e((string)$faq['question']) ?></b><i class="fa-solid fa-plus" aria-hidden="true"></i></summary>
                            <div><p><?= nl2br(e((string)$faq['answer'])) ?></p></div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
