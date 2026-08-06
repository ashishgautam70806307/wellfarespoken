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

        $allowedVariants = ['primary','secondary','gold','success','danger','ghost','link'];
        $variant = in_array($variant, $allowedVariants, true) ? $variant : 'primary';
        $size = strtolower(trim((string)($attributes['size'] ?? 'md')));
        $size = in_array($size, ['sm','md','lg'], true) ? $size : 'md';

        $classes = ['wf-btn', 'wf-btn--' . $variant, 'wf-btn-' . $variant];
        if ($size !== 'md') $classes[] = 'wf-btn--' . $size;
        if (!empty($attributes['block'])) $classes[] = 'wf-btn--block';
        if (!empty($attributes['icon_only'])) $classes[] = 'wf-btn--icon';
        if (!empty($attributes['class'])) {
            $extraClasses = preg_split('/\s+/', trim((string)$attributes['class'])) ?: [];
            foreach ($extraClasses as $extraClass) {
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $extraClass)) $classes[] = $extraClass;
            }
        }
        $classes = array_values(array_unique($classes));

        $iconClass = $icon !== '' ? app_icon_class($icon, 'fa-solid fa-arrow-right') : '';
        $content = '<span class="wf-btn-label">'
            . ($iconClass !== '' ? '<i class="' . e($iconClass) . '" aria-hidden="true"></i>' : '')
            . '<span>' . e($label) . '</span></span>';

        $common = ' class="' . e(implode(' ', $classes)) . '"';
        if (!empty($attributes['id']) && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', (string)$attributes['id'])) {
            $common .= ' id="' . e((string)$attributes['id']) . '"';
        }
        if (!empty($attributes['aria_label'])) $common .= ' aria-label="' . e((string)$attributes['aria_label']) . '"';
        if (!empty($attributes['title'])) $common .= ' title="' . e((string)$attributes['title']) . '"';
        foreach (($attributes['data'] ?? []) as $dataKey => $dataValue) {
            $dataKey = strtolower((string)$dataKey);
            if (preg_match('/^[a-z0-9_-]+$/', $dataKey)) $common .= ' data-' . e($dataKey) . '="' . e((string)$dataValue) . '"';
        }

        if ($url !== '') {
            $target = !empty($attributes['target']) ? ' target="' . e((string)$attributes['target']) . '"' : '';
            $relValue = trim((string)($attributes['rel'] ?? ''));
            if (($attributes['target'] ?? '') === '_blank' && $relValue === '') $relValue = 'noopener';
            $rel = $relValue !== '' ? ' rel="' . e($relValue) . '"' : '';
            $download = !empty($attributes['download']) ? ' download' : '';
            $disabled = !empty($attributes['disabled']) ? ' aria-disabled="true" tabindex="-1"' : '';
            return '<a' . $common . ' href="' . e(app_safe_href($url)) . '"' . $target . $rel . $download . $disabled . '>' . $content . '</a>';
        }

        $type = in_array(($attributes['type'] ?? 'button'), ['button','submit','reset'], true) ? (string)$attributes['type'] : 'button';
        $name = !empty($attributes['name']) && preg_match('/^[a-zA-Z0-9_-]+$/', (string)$attributes['name']) ? ' name="' . e((string)$attributes['name']) . '"' : '';
        $value = array_key_exists('value', $attributes) ? ' value="' . e((string)$attributes['value']) . '"' : '';
        $disabled = !empty($attributes['disabled']) ? ' disabled' : '';
        return '<button' . $common . ' type="' . e($type) . '"' . $name . $value . $disabled . '>' . $content . '</button>';
    }
}

if (!function_exists('wf_card_start')) {
    function wf_card_start(array $config = []): void
    {
        $variant = strtolower(trim((string)($config['variant'] ?? 'default')));
        $allowedVariants = ['default','soft','gold','navy','flat'];
        $variant = in_array($variant, $allowedVariants, true) ? $variant : 'default';
        $size = strtolower(trim((string)($config['size'] ?? 'md')));
        $size = in_array($size, ['sm','md','lg'], true) ? $size : 'md';
        $classes = ['wf-card'];
        if ($variant !== 'default') $classes[] = 'wf-card--' . $variant;
        if ($size !== 'md') $classes[] = 'wf-card--' . $size;
        if (!empty($config['interactive'])) $classes[] = 'wf-card--interactive';
        if (!empty($config['class'])) {
            foreach (preg_split('/\s+/', trim((string)$config['class'])) ?: [] as $extraClass) {
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $extraClass)) $classes[] = $extraClass;
            }
        }
        $id = !empty($config['id']) && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', (string)$config['id']) ? ' id="' . e((string)$config['id']) . '"' : '';
        echo '<section class="' . e(implode(' ', array_unique($classes))) . '"' . $id . '>';
        if (!empty($config['title']) || !empty($config['text']) || !empty($config['action'])) {
            echo '<header class="wf-card__header"><div>';
            if (!empty($config['eyebrow'])) echo '<span class="wf-section-kicker">' . e((string)$config['eyebrow']) . '</span>';
            if (!empty($config['title'])) echo '<h3>' . e((string)$config['title']) . '</h3>';
            if (!empty($config['text'])) echo '<p class="wf-muted">' . e((string)$config['text']) . '</p>';
            echo '</div>';
            if (is_array($config['action'] ?? null) && !empty($config['action']['label'])) {
                echo wf_button(
                    (string)$config['action']['label'],
                    (string)($config['action']['url'] ?? ''),
                    (string)($config['action']['variant'] ?? 'secondary'),
                    (string)($config['action']['icon'] ?? ''),
                    ['size' => 'sm']
                );
            }
            echo '</header>';
        }
        echo '<div class="wf-card__body">';
    }
}

if (!function_exists('wf_card_end')) {
    function wf_card_end(array $footerActions = []): void
    {
        echo '</div>';
        if ($footerActions) {
            echo '<footer class="wf-card__footer">';
            foreach ($footerActions as $action) {
                if (!is_array($action) || empty($action['label'])) continue;
                echo wf_button(
                    (string)$action['label'],
                    (string)($action['url'] ?? ''),
                    (string)($action['variant'] ?? 'secondary'),
                    (string)($action['icon'] ?? ''),
                    ['size' => (string)($action['size'] ?? 'sm'), 'type' => (string)($action['type'] ?? 'button')]
                );
            }
            echo '</footer>';
        }
        echo '</section>';
    }
}

if (!function_exists('wf_badge')) {
    function wf_badge(string $label, string $variant = 'neutral', string $icon = ''): string
    {
        $label = trim($label);
        if ($label === '') return '';
        $allowed = ['neutral','success','danger','warning','info','gold'];
        $variant = in_array($variant, $allowed, true) ? $variant : 'neutral';
        $iconClass = $icon !== '' ? app_icon_class($icon, 'fa-solid fa-circle') : '';
        return '<span class="wf-badge wf-badge--' . e($variant) . '">'
            . ($iconClass !== '' ? '<i class="' . e($iconClass) . '" aria-hidden="true"></i>' : '')
            . e($label) . '</span>';
    }
}

if (!function_exists('wf_alert')) {
    function wf_alert(string $title, string $message, string $variant = 'info', string $icon = ''): string
    {
        $allowed = ['info','success','warning','danger'];
        $variant = in_array($variant, $allowed, true) ? $variant : 'info';
        $fallbackIcons = [
            'info' => 'fa-solid fa-circle-info',
            'success' => 'fa-solid fa-circle-check',
            'warning' => 'fa-solid fa-triangle-exclamation',
            'danger' => 'fa-solid fa-circle-exclamation',
        ];
        $iconClass = app_icon_class($icon, $fallbackIcons[$variant]);
        return '<div class="wf-alert wf-alert--' . e($variant) . '" role="' . ($variant === 'danger' ? 'alert' : 'status') . '">'
            . '<span class="wf-alert__icon"><i class="' . e($iconClass) . '" aria-hidden="true"></i></span>'
            . '<div><h3>' . e($title) . '</h3><p>' . e($message) . '</p></div></div>';
    }
}

if (!function_exists('wf_form_field')) {
    function wf_form_field(array $config): string
    {
        $name = trim((string)($config['name'] ?? ''));
        if ($name === '' || !preg_match('/^[a-zA-Z0-9_\[\]-]+$/', $name)) return '';
        $type = strtolower(trim((string)($config['type'] ?? 'text')));
        $allowedTypes = ['text','email','tel','number','date','time','password','url','search','file','textarea','select'];
        $type = in_array($type, $allowedTypes, true) ? $type : 'text';
        $label = trim((string)($config['label'] ?? ''));
        $value = (string)($config['value'] ?? '');
        $required = !empty($config['required']);
        $fieldClasses = ['wf-field'];
        if (!empty($config['full'])) $fieldClasses[] = 'wf-field--full';
        if (!empty($config['class'])) {
            foreach (preg_split('/\s+/', trim((string)$config['class'])) ?: [] as $extraClass) {
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $extraClass)) $fieldClasses[] = $extraClass;
            }
        }
        $id = trim((string)($config['id'] ?? preg_replace('/[^a-zA-Z0-9_-]/', '-', $name)));
        if ($id === '' || !preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $id)) $id = 'wf-field-' . substr(hash('sha256', $name), 0, 10);

        $attributes = ' id="' . e($id) . '" name="' . e($name) . '" class="wf-control"';
        if ($required) $attributes .= ' required';
        if (!empty($config['disabled'])) $attributes .= ' disabled';
        if (!empty($config['readonly'])) $attributes .= ' readonly';
        if (!empty($config['multiple'])) $attributes .= ' multiple';
        foreach (['placeholder','autocomplete','accept','min','max','step','pattern','inputmode','rows'] as $attributeName) {
            if (array_key_exists($attributeName, $config) && (string)$config[$attributeName] !== '') {
                $attributes .= ' ' . $attributeName . '="' . e((string)$config[$attributeName]) . '"';
            }
        }
        if (!empty($config['error'])) $attributes .= ' aria-invalid="true" aria-describedby="' . e($id . '-error') . '"';
        elseif (!empty($config['help'])) $attributes .= ' aria-describedby="' . e($id . '-help') . '"';

        $html = '<div class="' . e(implode(' ', array_unique($fieldClasses))) . '">';
        if ($label !== '') {
            $html .= '<label class="wf-label" for="' . e($id) . '">' . e($label)
                . ($required ? ' <span class="wf-label__required" aria-hidden="true">*</span>' : '') . '</label>';
        }
        if ($type === 'textarea') {
            $html .= '<textarea' . $attributes . '>' . e($value) . '</textarea>';
        } elseif ($type === 'select') {
            $html .= '<select' . $attributes . '>';
            foreach (($config['options'] ?? []) as $optionValue => $optionLabel) {
                if (is_int($optionValue)) $optionValue = (string)$optionLabel;
                $selected = (string)$optionValue === $value ? ' selected' : '';
                $html .= '<option value="' . e((string)$optionValue) . '"' . $selected . '>' . e((string)$optionLabel) . '</option>';
            }
            $html .= '</select>';
        } else {
            $valueAttribute = $type === 'file' ? '' : ' value="' . e($value) . '"';
            $html .= '<input type="' . e($type) . '"' . $attributes . $valueAttribute . '>';
        }
        if (!empty($config['help'])) $html .= '<p class="wf-help" id="' . e($id . '-help') . '">' . e((string)$config['help']) . '</p>';
        if (!empty($config['error'])) $html .= '<p class="wf-error" id="' . e($id . '-error') . '">' . e((string)$config['error']) . '</p>';
        return $html . '</div>';
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
        $class = 'wf-page-hero wf-surface-dark wf-page-hero-' . ($theme ?: 'navy') . ($compact ? ' is-compact' : '');
        ?>
        <section class="<?= e($class) ?>" data-wf-surface="dark">
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
                <?= wf_button((string)$action['label'], (string)$action['url'], 'secondary', 'fa-solid fa-arrow-right', ['class' => 'btn-sm wf-section-action']) ?>
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
