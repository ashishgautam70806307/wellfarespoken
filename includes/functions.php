<?php
require_once __DIR__ . '/db.php';

require_once __DIR__ . '/security.php';

function safe_local_redirect(string $path, string $default = 'student-dashboard.php'): string
{
    $path = trim(str_replace(["\r", "\n", "\0"], '', html_entity_decode($path, ENT_QUOTES, 'UTF-8')));
    if ($path === '' || str_starts_with($path, '//') || str_contains($path, '\\')) return $default;

    $parts = parse_url($path);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
        return $default;
    }

    $cleanPath = ltrim((string)($parts['path'] ?? ''), '/');
    if ($cleanPath === '' || preg_match('#(^|/)\.\.(/|$)#', $cleanPath)) return $default;
    if (!preg_match('/^[A-Za-z0-9_\-\.\/]+$/', $cleanPath)) return $default;

    $query = (string)($parts['query'] ?? '');
    if ($query !== '' && (strlen($query) > 1500 || preg_match('/[\x00-\x1F\x7F]/', $query))) return $default;

    $fragment = (string)($parts['fragment'] ?? '');
    if ($fragment !== '' && !preg_match('/^[A-Za-z0-9_\-:.]+$/', $fragment)) return $default;

    return $cleanPath
        . ($query !== '' ? '?' . $query : '')
        . ($fragment !== '' ? '#' . $fragment : '');
}

function csv_safe_cell(mixed $value): string
{
    $value = (string)$value;
    if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) return "'" . $value;
    return $value;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function app_safe_href(?string $url, string $default = '#', bool $webOnly = false): string
{
    $url = trim(str_replace(["\r", "\n", "\0"], '', html_entity_decode((string)$url, ENT_QUOTES, 'UTF-8')));
    if ($url === '' || str_starts_with($url, '//') || str_contains($url, '\\')) return $default;
    if (preg_match('/[\x00-\x1F\x7F]/', $url)) return $default;

    if (str_starts_with($url, '#')) {
        return preg_match('/^#[A-Za-z0-9_\-:.]+$/', $url) ? $url : $default;
    }

    $scheme = strtolower((string)(parse_url($url, PHP_URL_SCHEME) ?? ''));
    if ($scheme !== '') {
        $allowed = $webOnly ? ['http', 'https'] : ['http', 'https', 'mailto', 'tel'];
        return in_array($scheme, $allowed, true) ? $url : $default;
    }

    if ($webOnly) return $default;
    $path = (string)(parse_url($url, PHP_URL_PATH) ?? '');
    if ($path === '' || preg_match('#(^|/)\.\.(/|$)#', $path)) return $default;
    return $url;
}

function app_icon_class(?string $value, string $fallback = 'fa-solid fa-circle-check'): string
{
    $value = trim((string)$value);
    if ($value !== '' && preg_match('/^(?:fa-(?:solid|regular|brands)\s+)?fa-[a-z0-9-]+$/i', $value)) {
        return str_starts_with(strtolower($value), 'fa-') && !preg_match('/^fa-(?:solid|regular|brands)\s/i', $value)
            ? 'fa-solid ' . strtolower($value)
            : strtolower($value);
    }

    $map = [
        '✅' => 'fa-solid fa-circle-check', '✓' => 'fa-solid fa-check',
        '🎯' => 'fa-solid fa-bullseye', '📞' => 'fa-solid fa-phone', '☎' => 'fa-solid fa-phone',
        '👤' => 'fa-solid fa-user', '📚' => 'fa-solid fa-book-open', '📘' => 'fa-solid fa-book',
        '⏰' => 'fa-solid fa-clock', '⏱' => 'fa-solid fa-stopwatch', '🎙️' => 'fa-solid fa-microphone',
        '🎙' => 'fa-solid fa-microphone', '🎤' => 'fa-solid fa-microphone', '🔁' => 'fa-solid fa-arrows-rotate',
        '⭐' => 'fa-solid fa-star', '★' => 'fa-solid fa-star', '🎧' => 'fa-solid fa-headphones',
        '✍️' => 'fa-solid fa-pen', '✍' => 'fa-solid fa-pen', '🧠' => 'fa-solid fa-brain',
        '🏆' => 'fa-solid fa-trophy', '🎓' => 'fa-solid fa-graduation-cap', '💬' => 'fa-solid fa-comments',
        '💼' => 'fa-solid fa-briefcase', '🏫' => 'fa-solid fa-school', '👨‍🏫' => 'fa-solid fa-chalkboard-user',
        '🚀' => 'fa-solid fa-rocket', '🔊' => 'fa-solid fa-volume-high', '🧩' => 'fa-solid fa-puzzle-piece',
        '⚡' => 'fa-solid fa-bolt', '👉' => 'fa-solid fa-hand-point-right', '✨' => 'fa-solid fa-wand-magic-sparkles',
        '🎉' => 'fa-solid fa-star', '🌸' => 'fa-solid fa-spa', '🌼' => 'fa-solid fa-seedling',
        '🔐' => 'fa-solid fa-lock', '🖼' => 'fa-solid fa-image', '🔎' => 'fa-solid fa-magnifying-glass',
        '🧭' => 'fa-solid fa-compass', '⚙️' => 'fa-solid fa-gear', '⚙' => 'fa-solid fa-gear',
    ];
    if (isset($map[$value])) return $map[$value];

    $search = mb_strtolower($value);
    $keywordMap = [
        'conversation' => 'fa-solid fa-comments', 'grammar' => 'fa-solid fa-brain',
        'confidence' => 'fa-solid fa-bullseye', 'interview' => 'fa-solid fa-briefcase',
        'teacher' => 'fa-solid fa-chalkboard-user', 'course' => 'fa-solid fa-book-open',
        'practice' => 'fa-solid fa-pen-to-square', 'test' => 'fa-solid fa-clipboard-check',
        'phone' => 'fa-solid fa-phone', 'call' => 'fa-solid fa-phone', 'student' => 'fa-solid fa-user-graduate',
    ];
    foreach ($keywordMap as $keyword => $class) {
        if ($search !== '' && str_contains($search, $keyword)) return $class;
    }
    return $fallback;
}

function app_icon_html(?string $value, string $fallback = 'fa-solid fa-circle-check', string $extraClass = ''): string
{
    $class = trim(app_icon_class($value, $fallback) . ' ' . $extraClass);
    return '<i class="' . e($class) . '" aria-hidden="true"></i>';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function redirect(string $path): void
{
    $path = str_replace(["\r", "\n"], '', $path);

    // If a page included the shared admin header before doing POST/redirect work,
    // buffered HTML may already exist. Clean buffered output before sending Location.
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    if (!headers_sent()) {
        header('Location: ' . $path);
        exit;
    }

    // Last-resort fallback: prevents "headers already sent" warnings and still moves the user.
    $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . $safePath . '"></head><body>';
    echo '<script>window.location.href=' . json_encode($path) . ';</script>';
    echo '<p>Redirecting...</p></body></html>';
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function admin_toast_html(): string
{
    $messages = [];
    foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type) {
        $msg = flash($key);
        if ($msg !== null && trim((string)$msg) !== '') {
            $messages[] = ['type' => $type, 'message' => $msg];
        }
    }
    if (!$messages) {
        return '';
    }
    $html = '<div class="admin-toast-zone" aria-live="polite" aria-atomic="true">';
    foreach ($messages as $item) {
        $icon = match ($item['type']) {
            'success' => 'fa-circle-check',
            'error' => 'fa-circle-exclamation',
            'warning' => 'fa-triangle-exclamation',
            default => 'fa-circle-info',
        };
        $html .= '<div class="app-toast toast-' . e($item['type']) . '" data-toast><span class="toast-icon"><i class="fa-solid ' . e($icon) . '" aria-hidden="true"></i></span><div><b>' . e(ucfirst($item['type'])) . '</b><p>' . e($item['message']) . '</p></div><button type="button" class="toast-close" aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>';
    }
    $html .= '</div>';
    return $html;
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function admin_session_logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_auth_signature'], $_SESSION['admin_last_activity'], $_SESSION['admin_mfa_pending_id'], $_SESSION['admin_mfa_pending_at'], $_SESSION['admin_password_change_required']);
    session_regenerate_id(true);
}

function require_admin(): void
{
    private_no_store();
    if (!is_admin()) redirect('login.php');
    $last = (int)($_SESSION['admin_last_activity'] ?? 0);
    if ($last > 0 && time() - $last > 3600) {
        admin_session_logout();
        redirect('login.php?expired=1');
    }
    try {
        $cols = ['id','name','email','password_hash','published'];
        foreach (['role_id','auth_version','must_change_password','mfa_enabled'] as $col) if (column_exists('admins',$col)) $cols[]=$col;
        $stmt = db()->prepare('SELECT ' . implode(',', $cols) . ' FROM admins WHERE id=? LIMIT 1');
        $stmt->execute([(int)$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        if (!$admin || ($admin['published'] ?? 'No') !== 'Yes') {
            admin_session_logout();
            redirect('login.php?inactive=1');
        }
        if (function_exists('admin_session_signature')) {
            $databaseSignature = admin_session_signature($admin);
            $sessionSignature = (string)($_SESSION['admin_auth_signature'] ?? '');
            if ($sessionSignature !== '' && !hash_equals($databaseSignature,$sessionSignature)) {
                admin_session_logout();
                redirect('login.php?reset=1');
            }
            if ($sessionSignature === '') $_SESSION['admin_auth_signature']=$databaseSignature;
        }
        if (function_exists('admin_clear_stale_owner_password_gate')) admin_clear_stale_owner_password_gate($admin);
        $_SESSION['admin_name'] = (string)$admin['name'];
        $_SESSION['admin_last_activity'] = time();
        $passwordGate = function_exists('admin_password_change_required') ? admin_password_change_required($admin) : (($admin['must_change_password'] ?? 'No') === 'Yes');
        $_SESSION['admin_password_change_required'] = $passwordGate;
        $page = basename((string)($_SERVER['PHP_SELF'] ?? ''));
        if ($passwordGate && !in_array($page,['password.php','logout.php'],true)) {
            redirect('password.php?required=1');
        }
        if (function_exists('admin_page_permission')) {
            $permission = admin_page_permission($page);
            if ($permission) admin_require_permission($permission);
        }
        if (function_exists('admin_request_audit_bootstrap')) admin_request_audit_bootstrap();
    } catch (Throwable $e) {
        error_log('[admin-session] ' . $e->getMessage());
        admin_session_logout();
        redirect('login.php');
    }
}

function active_nav(string $page): string
{
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}

function column_exists(string $table, string $column): bool
{
    try {
        $safeTable = preg_replace('/[^A-Za-z0-9_]/', '', $table);
        $safeColumn = preg_replace('/[^A-Za-z0-9_]/', '', $column);
        if ($safeTable === '' || $safeColumn === '') {
            return false;
        }
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$safeTable, $safeColumn]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}


function db_exec_safe(string $sql): bool
{
    try {
        db()->exec($sql);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function table_exists(string $table): bool
{
    try {
        $safeTable = preg_replace('/[^A-Za-z0-9_]/', '', $table);
        if ($safeTable === '') return false;
        $stmt = db()->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
        $stmt->execute([$safeTable]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function ensure_core_schema_columns(): void
{
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        db_exec_safe("CREATE TABLE IF NOT EXISTS enquiries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            course_interest VARCHAR(160) NULL,
            current_level VARCHAR(120) NULL,
            preferred_batch VARCHAR(120) NULL,
            lead_source VARCHAR(80) NULL,
            message TEXT NULL,
            enquiry_status VARCHAR(40) NOT NULL DEFAULT 'New',
            lead_priority VARCHAR(30) NOT NULL DEFAULT 'Normal',
            follow_up_date DATE NULL,
            last_contacted_at DATETIME NULL,
            admin_note TEXT NULL,
            ip_address VARCHAR(80) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $columns = [
            'course_interest' => "VARCHAR(160) NULL AFTER phone",
            'current_level' => "VARCHAR(120) NULL AFTER course_interest",
            'preferred_batch' => "VARCHAR(120) NULL AFTER current_level",
            'lead_source' => "VARCHAR(80) NULL AFTER preferred_batch",
            'message' => "TEXT NULL AFTER lead_source",
            'enquiry_status' => "VARCHAR(40) NOT NULL DEFAULT 'New' AFTER message",
            'lead_priority' => "VARCHAR(30) NOT NULL DEFAULT 'Normal' AFTER enquiry_status",
            'follow_up_date' => "DATE NULL AFTER lead_priority",
            'last_contacted_at' => "DATETIME NULL AFTER follow_up_date",
            'admin_note' => "TEXT NULL AFTER last_contacted_at",
            'ip_address' => "VARCHAR(80) NULL AFTER admin_note",
            'created_at' => "DATETIME DEFAULT CURRENT_TIMESTAMP AFTER ip_address",
            'updated_at' => "DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
        ];
        foreach ($columns as $column => $definition) {
            if (!column_exists('enquiries', $column)) {
                db_exec_safe("ALTER TABLE enquiries ADD `$column` $definition");
            }
        }

        db_exec_safe("CREATE TABLE IF NOT EXISTS site_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(80) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $brandDefaults = [
            'site_logo' => '',
            'site_favicon' => '',
            'brand_mark_mode' => 'text',
            'brand_logo_alt' => 'Institute logo',
            'practice_tool_label' => 'Free Smart English Practice Tool',
            'practice_tool_note' => 'Free local practice works for everyone. Optional OpenAI can be enabled from admin for advanced feedback.'
        ];
        $stmt = db()->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($brandDefaults as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    } catch (Throwable $e) {
        // Never block the public website because of upgrade helpers.
    }
}

function ensure_schema_updates(): void
{
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    static $phase83Done = false;
    if ($phase83Done) { return; }
    $phase83Done = true;

    /*
     * Performance fix:
     * Schema ALTER/CREATE checks are expensive on shared hosting. Run once per
     * project version, then skip on future page loads.
     */
    $phase83SchemaMarker = 'phase126_home_roadmap_v1';
    try {
        $phase83Stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
        $phase83Stmt->execute(['schema_marker']);
        $phase83CurrentMarker = (string)($phase83Stmt->fetchColumn() ?: '');
        if ($phase83CurrentMarker === $phase83SchemaMarker) { return; }
    } catch (Throwable $e) {
        // If marker check fails, continue with normal safe upgrade.
    }

    ensure_core_schema_columns();
    if (function_exists('roadmap_ensure_schema')) roadmap_ensure_schema();
    try {
        db_exec_safe("ALTER TABLE courses ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE courses ADD COLUMN pay_url VARCHAR(500) NULL");
        db_exec_safe("ALTER TABLE courses ADD COLUMN course_image VARCHAR(500) NULL");
        db_exec_safe("ALTER TABLE courses ADD COLUMN class_time VARCHAR(160) NULL");
        db_exec_safe("ALTER TABLE courses ADD COLUMN class_days VARCHAR(160) NULL");
        db_exec_safe("ALTER TABLE courses ADD COLUMN total_tests INT NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE courses ADD COLUMN lessons_count INT NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE courses ADD COLUMN course_details TEXT NULL");
        db_exec_safe("ALTER TABLE courses ADD COLUMN outcomes TEXT NULL");
        db_exec_safe("ALTER TABLE courses ADD COLUMN includes_text TEXT NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN student_image VARCHAR(500) NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN rating TINYINT NOT NULL DEFAULT 5");

        db_exec_safe("CREATE TABLE IF NOT EXISTS faculty_members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            faculty_name VARCHAR(180) NOT NULL,
            designation VARCHAR(180) NULL,
            experience VARCHAR(80) NULL,
            qualification VARCHAR(255) NULL,
            short_bio TEXT NULL,
            full_bio TEXT NULL,
            expertise TEXT NULL,
            image_url VARCHAR(500) NULL,
            phone VARCHAR(80) NULL,
            email VARCHAR(180) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_faculty_pub (published, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db_exec_safe("ALTER TABLE faculty_members ADD COLUMN sort_order INT NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE faculty_members ADD COLUMN published ENUM('Yes','No') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE faculty_members ADD COLUMN experience VARCHAR(80) NULL");
        db_exec_safe("ALTER TABLE faculty_members ADD COLUMN qualification VARCHAR(255) NULL");
        db_exec_safe("ALTER TABLE faculty_members ADD COLUMN expertise TEXT NULL");
        db_exec_safe("ALTER TABLE faculty_members ADD COLUMN full_bio TEXT NULL");
        $siteDefaults = [
            'admission_marquee_text' => 'Admission Open In Wellfare English Spoken Mariyahu Jaunpur',
            'twitter_url' => '',
            'linkedin_url' => '',
            'home_faculty_title' => 'Our Expert Faculty',
            'home_faculty_subtitle' => 'Learn from experienced teachers with practical spoken English guidance.',
            'home_faculty_eyebrow' => 'Meet Our Team'
        ];
        $stmtDefaults = db()->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($siteDefaults as $k => $v) { $stmtDefaults->execute([$k, $v]); }

        $fc = (int)db()->query("SELECT COUNT(*) FROM faculty_members")->fetchColumn();
        if ($fc === 0) {
            $seedFaculty = [
                ['Spoken English Trainer','Spoken English Faculty','7+ Years','MA, B.Ed','Conversation, grammar and confidence building.','Practical spoken English teacher focused on daily-use conversation, sentence correction and confidence practice.','Conversation, Grammar, Pronunciation','',0],
                ['Grammar Mentor','Grammar Faculty','5+ Years','BA, Diploma','Grammar made easy with examples.','Helps students understand tense, uses and daily English patterns with simple practice.','Tense, Uses, Translation','',1],
                ['Interview Coach','Interview & Personality Faculty','4+ Years','MBA, Communication Skills','Interview support and personality development.','Guides students for interview answers, speaking confidence and professional communication.','Interview, Personality, Speaking','',2],
            ];
            $sf = db()->prepare("INSERT INTO faculty_members (faculty_name, designation, experience, qualification, short_bio, full_bio, expertise, image_url, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?, 'Yes')");
            foreach ($seedFaculty as $f) { $sf->execute($f); }
        }


        db_exec_safe("CREATE TABLE IF NOT EXISTS course_variants (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            course_id INT UNSIGNED NOT NULL,
            variant_title VARCHAR(180) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            class_time VARCHAR(160) NULL,
            class_days VARCHAR(160) NULL,
            total_tests INT NOT NULL DEFAULT 0,
            details TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_course_variants_course (course_id, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS site_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(80) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS gallery_images (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(160) NOT NULL,
            category VARCHAR(100) NULL,
            image_url VARCHAR(500) NULL,
            image_alt VARCHAR(180) NULL,
            description TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS faqs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            question VARCHAR(220) NOT NULL,
            answer TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS batch_timings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            batch_name VARCHAR(160) NOT NULL,
            course_name VARCHAR(160) NULL,
            timing VARCHAR(120) NULL,
            days VARCHAR(120) NULL,
            seats_note VARCHAR(160) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS content_blocks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            block_type VARCHAR(80) NOT NULL,
            block_key VARCHAR(120) NULL,
            icon VARCHAR(40) NULL,
            eyebrow VARCHAR(160) NULL,
            title VARCHAR(220) NOT NULL,
            subtitle TEXT NULL,
            body TEXT NULL,
            link_text VARCHAR(120) NULL,
            link_url VARCHAR(255) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_blocks_type (block_type, published, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS form_options (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            option_group VARCHAR(80) NOT NULL,
            option_label VARCHAR(160) NOT NULL,
            option_value VARCHAR(160) NULL,
            helper_text VARCHAR(255) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_options_group (option_group, published, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS nav_menus (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            menu_area VARCHAR(40) NOT NULL DEFAULT 'header',
            label VARCHAR(120) NOT NULL,
            url VARCHAR(255) NOT NULL,
            is_cta ENUM('Yes','No') NOT NULL DEFAULT 'No',
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_nav_area (menu_area, published, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db_exec_safe("DELETE n1 FROM nav_menus n1 INNER JOIN nav_menus n2 ON n1.id > n2.id AND n1.menu_area = n2.menu_area AND LOWER(TRIM(n1.label)) = LOWER(TRIM(n2.label)) AND LOWER(TRIM(n1.url)) = LOWER(TRIM(n2.url))");
        db_exec_safe("DELETE n1 FROM nav_menus n1 INNER JOIN nav_menus n2 ON n1.id > n2.id AND n1.menu_area = n2.menu_area AND LOWER(TRIM(n1.label)) = LOWER(TRIM(n2.label))");
        db_exec_safe("DELETE n1 FROM nav_menus n1 INNER JOIN nav_menus n2 ON n1.id > n2.id AND n1.menu_area = n2.menu_area AND LOWER(TRIM(n1.url)) = LOWER(TRIM(n2.url))");


        db()->exec("CREATE TABLE IF NOT EXISTS hero_banners (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_key VARCHAR(80) NOT NULL DEFAULT 'home',
            eyebrow VARCHAR(160) NULL,
            title VARCHAR(220) NOT NULL,
            subtitle TEXT NULL,
            image_url VARCHAR(500) NULL,
            desktop_image_url VARCHAR(500) NULL,
            mobile_image_url VARCHAR(500) NULL,
            image_alt VARCHAR(180) NULL,
            show_content ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            content_position ENUM('left','center','right') NOT NULL DEFAULT 'left',
            overlay_strength TINYINT UNSIGNED NOT NULL DEFAULT 58,
            badge_one VARCHAR(120) NULL,
            badge_two VARCHAR(120) NULL,
            stat_one_label VARCHAR(120) NULL,
            stat_one_value VARCHAR(120) NULL,
            stat_two_label VARCHAR(120) NULL,
            stat_two_value VARCHAR(120) NULL,
            primary_text VARCHAR(120) NULL,
            primary_url VARCHAR(255) NULL,
            secondary_text VARCHAR(120) NULL,
            secondary_url VARCHAR(255) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_hero_page (page_key, published, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db_exec_safe("ALTER TABLE hero_banners ADD COLUMN desktop_image_url VARCHAR(500) NULL AFTER image_url");
        db_exec_safe("ALTER TABLE hero_banners ADD COLUMN mobile_image_url VARCHAR(500) NULL AFTER desktop_image_url");
        db_exec_safe("ALTER TABLE hero_banners ADD COLUMN show_content ENUM('Yes','No') NOT NULL DEFAULT 'Yes' AFTER image_alt");
        db_exec_safe("ALTER TABLE hero_banners ADD COLUMN content_position ENUM('left','center','right') NOT NULL DEFAULT 'left' AFTER show_content");
        db_exec_safe("ALTER TABLE hero_banners ADD COLUMN overlay_strength TINYINT UNSIGNED NOT NULL DEFAULT 58 AFTER content_position");


        db()->exec("CREATE TABLE IF NOT EXISTS practice_categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(160) NOT NULL,
            slug VARCHAR(180) NOT NULL UNIQUE,
            description TEXT NULL,
            icon VARCHAR(40) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_practice_cat (published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS practice_lessons (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NOT NULL DEFAULT 0,
            lesson_title VARCHAR(180) NOT NULL,
            lesson_type VARCHAR(80) NOT NULL DEFAULT 'tense',
            level VARCHAR(80) NULL,
            tense_name VARCHAR(120) NULL,
            short_description TEXT NULL,
            instructions TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_practice_lessons (category_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS practice_questions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NOT NULL DEFAULT 0,
            lesson_id INT UNSIGNED NOT NULL DEFAULT 0,
            question_type VARCHAR(60) NOT NULL DEFAULT 'fill_blank',
            question_text TEXT NOT NULL,
            option_a VARCHAR(255) NULL,
            option_b VARCHAR(255) NULL,
            option_c VARCHAR(255) NULL,
            option_d VARCHAR(255) NULL,
            correct_answer TEXT NULL,
            sample_answer TEXT NULL,
            explanation TEXT NULL,
            tense_name VARCHAR(120) NULL,
            level VARCHAR(80) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_practice_questions (lesson_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS practice_common_mistakes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            wrong_pattern VARCHAR(220) NOT NULL,
            correct_pattern VARCHAR(220) NOT NULL,
            explanation TEXT NULL,
            example_sentence TEXT NULL,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_mistakes (published, status_deleted)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS practice_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(120) NOT NULL,
            student_name VARCHAR(160) NULL,
            phone VARCHAR(40) NULL,
            question_id INT UNSIGNED NOT NULL DEFAULT 0,
            user_answer TEXT NULL,
            correct_answer TEXT NULL,
            score INT NOT NULL DEFAULT 0,
            local_feedback TEXT NULL,
            suggested_next_step VARCHAR(220) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_attempt_session (session_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS practice_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(80) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS students (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(160) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            email VARCHAR(160) NULL,
            password_hash VARCHAR(255) NOT NULL,
            current_level VARCHAR(80) NOT NULL DEFAULT 'Zero Level',
            target_goal VARCHAR(180) NULL,
            preferred_language VARCHAR(40) NOT NULL DEFAULT 'Hindi',
            daily_goal_minutes INT NOT NULL DEFAULT 20,
            admin_note TEXT NULL,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            last_login_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_students_phone (phone),
            KEY idx_students_active (published, status_deleted, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS student_activity_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL,
            activity_type VARCHAR(80) NOT NULL,
            activity_title VARCHAR(180) NULL,
            score INT NULL,
            note TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_student_activity (student_id, created_at),
            KEY idx_student_activity_type (activity_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS admissions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_photo VARCHAR(255) NULL,
            student_name VARCHAR(180) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            alt_phone VARCHAR(40) NULL,
            email VARCHAR(180) NULL,
            gender VARCHAR(30) NULL,
            dob DATE NULL,
            guardian_name VARCHAR(180) NULL,
            address TEXT NULL,
            course_interest VARCHAR(180) NULL,
            batch_preference VARCHAR(160) NULL,
            current_level VARCHAR(120) NULL,
            source_label VARCHAR(120) NULL,
            admission_status VARCHAR(40) NOT NULL DEFAULT 'New',
            fee_plan_name VARCHAR(180) NULL,
            total_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
            discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            payment_status VARCHAR(40) NOT NULL DEFAULT 'Unpaid',
            payment_mode VARCHAR(80) NULL,
            receipt_no VARCHAR(120) NULL,
            admission_date DATE NULL,
            due_date DATE NULL,
            next_follow_up DATE NULL,
            documents_received TEXT NULL,
            counselor_name VARCHAR(160) NULL,
            admin_note TEXT NULL,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            deleted_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_admissions_status (admission_status, payment_status, status_deleted),
            KEY idx_admissions_phone (phone),
            KEY idx_admissions_date (admission_date, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


        db()->exec("CREATE TABLE IF NOT EXISTS practice_ai_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(120) NOT NULL,
            question_id INT UNSIGNED NOT NULL DEFAULT 0,
            provider VARCHAR(60) NULL,
            model VARCHAR(120) NULL,
            request_type VARCHAR(80) NULL,
            prompt_chars INT NOT NULL DEFAULT 0,
            response_chars INT NOT NULL DEFAULT 0,
            status VARCHAR(40) NOT NULL DEFAULT 'skipped',
            error_message TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ai_logs_session (session_id, created_at),
            INDEX idx_ai_logs_date (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        foreach ([
            'practice_attempts' => [
                'is_correct' => "TINYINT(1) NOT NULL DEFAULT 0",
                'match_type' => "VARCHAR(80) NULL",
                'ai_feedback' => "TEXT NULL",
                'ai_status' => "VARCHAR(40) NULL",
                'ai_model' => "VARCHAR(120) NULL",
                'corrected_answer' => "TEXT NULL",
                'natural_answer' => "TEXT NULL"
            ],
            'practice_questions' => [
                'accepted_answers' => "TEXT NULL",
                'answer_match_mode' => "VARCHAR(40) NOT NULL DEFAULT 'smart'",
                'answer_help' => "TEXT NULL",
                'ai_prompt_hint' => "TEXT NULL"
            ]
        ] as $tableName => $columns) {
            foreach ($columns as $columnName => $definition) {
                if (!column_exists($tableName, $columnName)) {
                    db()->exec("ALTER TABLE `" . $tableName . "` ADD COLUMN `" . $columnName . "` " . $definition);
                }
            }
        }


        $defaults = [
            'site_name' => APP_NAME,
            'site_tagline' => APP_TAGLINE,
            'brand_short' => 'WF',
            'brand_title' => 'Well Fare',
            'brand_subtitle' => 'English Spoken',
            'site_logo' => '',
            'site_favicon' => '',
            'brand_mark_mode' => 'text',
            'brand_logo_alt' => 'Institute logo',
            'phone' => APP_PHONE,
            'whatsapp' => APP_WHATSAPP,
            'email' => APP_EMAIL,
            'address' => APP_ADDRESS,
            'map_url' => GOOGLE_MAP_URL,
            'facebook_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',
            'linkedin_url' => '',
            'twitter_url' => '',
            'footer_about' => 'Practical spoken English classes for students, job seekers and working professionals.',
            'footer_copyright' => 'All rights reserved.',
            'contact_office_time' => 'Call or visit for admission guidance.',
            'hero_eyebrow' => 'Trusted Spoken English Institute in Mariahu',
            'hero_headline' => 'Speak English confidently in daily life, interviews and career conversations.',
            'hero_subtitle' => 'Join practical spoken English classes designed for students, job seekers, working professionals and homemakers who want real speaking confidence.',
            'hero_primary_text' => 'Book Free Counselling',
            'hero_primary_url' => 'admission.php',
            'hero_secondary_text' => 'Call Now',
            'home_features_title' => 'Built for students who want real speaking confidence.',
            'home_features_subtitle' => 'Simple lessons, daily practice and guided correction make English easier for school students, college students, job seekers and working professionals.',
            'home_courses_title' => 'Popular Courses',
            'home_courses_subtitle' => 'Choose a course based on your current level, confidence and career goal.',
            'home_batches_eyebrow' => 'Batch Timings',
            'home_batches_title' => 'Choose a comfortable speaking practice batch.',
            'home_batches_subtitle' => 'Admin-managed batch timings help students quickly decide when to join.',
            'home_gallery_title' => 'Inside the institute',
            'home_gallery_subtitle' => 'Show real classroom trust with admin-managed gallery photos.',
            'home_reviews_title' => 'Student Reviews',
            'home_reviews_subtitle' => 'Real testimonials can be managed from the admin panel.',
            'home_videos_title' => 'Class Videos',
            'home_videos_subtitle' => 'Add YouTube links from admin and they will appear here.',
            'home_faq_eyebrow' => 'Common Questions',
            'home_faq_title' => 'Before you join',
            'home_faq_subtitle' => 'Answers to common admission and course questions.',
            'home_cta_title' => 'Admission open for spoken English batches.',
            'admission_note' => 'Admission open for spoken English, grammar, confidence and interview preparation batches.',
            'admission_eyebrow' => 'Admission Open',
            'admission_title' => 'Book your free spoken English counselling call.',
            'admission_privacy_note' => 'Your details are safe with us.',
            'admission_faq_title' => 'Admission FAQs',
            'admission_faq_subtitle' => 'Helpful answers managed from admin.',
            'about_eyebrow' => 'About Institute',
            'about_title' => 'About Well Fare English Spoken',
            'about_subtitle' => 'A student-friendly English speaking institute focused on practical learning and confidence building.',
            'about_promise_title' => 'Our teaching promise',
            'about_promise_body' => 'Students do not need only theory. They need habit, correction and practice. We make English simple, practical and confidence-focused.',
            'courses_page_title' => 'Choose the right spoken English course',
            'courses_page_subtitle' => 'Every course is designed to improve confidence, grammar clarity and practical communication.',
            'gallery_page_title' => 'Gallery',
            'gallery_page_subtitle' => 'Classroom moments, student practice and institute activities managed from admin.',
            'reviews_page_title' => 'Student Reviews',
            'reviews_page_subtitle' => 'Student feedback and success stories managed from admin.',
            'contact_page_title' => 'Contact the institute',
            'contact_page_subtitle' => 'Call, WhatsApp or visit for admission counselling and batch details.',
            'seo_home_title' => 'Well Fare English Spoken | Spoken English Institute in Mariahu Jaunpur',
            'seo_home_description' => 'Join practical spoken English, grammar, interview preparation and personality development classes at Well Fare English Spoken in Mariahu Jaunpur.',
            'seo_courses_title' => 'Spoken English Courses | Well Fare English Spoken',
            'seo_courses_description' => 'Explore beginner, advanced, grammar, interview and personality development English speaking courses.',
            'seo_admission_title' => 'Admission Enquiry | Well Fare English Spoken',
            'seo_admission_description' => 'Book free counselling for spoken English classes and get batch timing and course details.',
            'seo_contact_title' => 'Contact Well Fare English Spoken',
            'seo_contact_description' => 'Call, WhatsApp or visit Well Fare English Spoken for course details and admission counselling.',
            'seo_gallery_title' => 'Gallery | Well Fare English Spoken',
            'seo_gallery_description' => 'View classroom, activity and student practice photos from Well Fare English Spoken.',
            'seo_reviews_title' => 'Student Reviews | Well Fare English Spoken',
            'seo_reviews_description' => 'Read student reviews and feedback for spoken English classes.',
            'seo_about_title' => 'About Well Fare English Spoken',
            'seo_about_description' => 'Learn about Well Fare English Spoken, practical spoken English and confidence training institute.',
            'seo_practice_title' => 'Spoken English Practice Room | Listen, Speak and Improve',
            'seo_practice_description' => 'Practise English tenses, sentences, situations and speaking for free with a local AI-style practice lab.',
            'practice_page_title' => 'Spoken English Practice Room',
            'practice_page_subtitle' => 'Practise tenses, daily situations, sentence correction and speaking confidence without login. The free practice engine works even without paid AI API.',
            'practice_cta_title' => 'Want teacher guidance after practice?',
            'practice_cta_body' => 'Share your practice score with the counsellor and book a free demo class for personal spoken English correction.'
        ];
        $insert = db()->prepare('INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($defaults as $key => $value) {
            $insert->execute([$key, $value]);
        }

        $blockDefaults = [
            ['home_feature', 'conversation', '💬', '', 'Conversation Practice', 'Daily sentence speaking, question-answer and real-life conversation drills.', '', '', '', 1],
            ['home_feature', 'grammar', '🧠', '', 'Grammar Made Easy', 'Learn grammar in a practical way so students can use it while speaking.', '', '', '', 2],
            ['home_feature', 'confidence', '🎯', '', 'Confidence Training', 'Remove hesitation with classroom activities, presentation and correction.', '', '', '', 3],
            ['home_feature', 'interview', '💼', '', 'Interview Support', 'Prepare introduction, common questions, answers and professional communication.', '', '', '', 4],
            ['hero_stat', 'practice', '', '', 'Daily', 'Speaking Practice', '', '', '', 1],
            ['hero_stat', 'grammar', '', '', 'Basic+', 'Grammar to Fluency', '', '', '', 2],
            ['hero_stat', 'trust', '', '', 'Local', 'Trusted Institute', '', '', '', 3],
            ['about_highlight', 'trust', '🏫', '', 'Local Trust', 'Designed for students of Mariahu and nearby areas who want better English communication.', '', '', '', 1],
            ['about_highlight', 'teacher', '👨‍🏫', '', 'Teacher-Led Practice', 'Classroom guidance, correction and repeated speaking practice help students improve faster.', '', '', '', 2],
            ['about_highlight', 'goal', '🚀', '', 'Goal-Based Learning', 'Suitable for school, college, job interview, business and daily English speaking needs.', '', '', '', 3],
            ['admission_benefit', 'beginner', '✅', '', 'Beginner friendly classes', 'Start from basic sentences and daily-use speaking.', '', '', '', 1],
            ['admission_benefit', 'practice', '🎤', '', 'Practical speaking practice', 'Improve confidence with role-play, correction and conversation.', '', '', '', 2],
            ['admission_benefit', 'contact', '💬', '', 'Fast contact options', 'Call or WhatsApp directly for fee, timing and demo class details.', '', '', '', 3]
        ];
        $insertBlock = db()->prepare('INSERT INTO content_blocks (block_type, block_key, icon, eyebrow, title, subtitle, body, link_text, link_url, sort_order, published) SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM content_blocks WHERE block_type = ? AND block_key = ?)');
        foreach ($blockDefaults as $block) {
            $insertBlock->execute([$block[0], $block[1], $block[2], $block[3], $block[4], $block[5], $block[6], $block[7], $block[8], $block[9], 'Yes', $block[0], $block[1]]);
        }

        $optionDefaults = [
            ['current_level', 'Beginner', 'Beginner', 'New learner starting from basics', 1],
            ['current_level', 'Can understand but cannot speak', 'Can understand but cannot speak', 'Understands English but hesitates while speaking', 2],
            ['current_level', 'Basic speaking', 'Basic speaking', 'Can speak simple English and wants fluency', 3],
            ['current_level', 'Interview preparation', 'Interview preparation', 'Needs interview and professional communication practice', 4],
            ['enquiry_status', 'New', 'New', '', 1],
            ['enquiry_status', 'Contacted', 'Contacted', '', 2],
            ['enquiry_status', 'Converted', 'Converted', '', 3],
            ['enquiry_status', 'Not Interested', 'Not Interested', '', 4]
        ];
        $insertOption = db()->prepare('INSERT INTO form_options (option_group, option_label, option_value, helper_text, sort_order, published) SELECT ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM form_options WHERE option_group = ? AND option_label = ?)');
        foreach ($optionDefaults as $option) {
            $insertOption->execute([$option[0], $option[1], $option[2], $option[3], $option[4], 'Yes', $option[0], $option[1]]);
        }


        $heroBannerDefaults = [
            ['home', 'Free Counselling Open', 'Speak English confidently in daily life, interviews and career conversations.', 'A premium, admin-managed hero banner area. Upload institute photos or keep the elegant fallback visual.', '', 'Student practising spoken English', '🎤 Speak Daily', '📚 Easy to Advanced', 'Daily Practice', 'Yes', 'Interview Support', 'Included', 'Book Free Counselling', 'admission.php', 'Practice Room', 'spoken-materials.php', 1, 'Yes'],
            ['practice', 'Daily Practice Room', 'Speak, listen and practise English every day', 'Practise useful sentences, translation, listening and speaking in one student-friendly room.', '', 'Spoken English practice room', 'Daily Practice', 'Mobile Friendly', 'Sentence Practice', 'Available', 'Voice Input', 'Browser', 'Start Practice', 'spoken-materials.php', 'Book Free Demo', 'admission.php', 1, 'Yes']
        ];
        $insertHeroBanner = db()->prepare('INSERT INTO hero_banners (page_key, eyebrow, title, subtitle, image_url, image_alt, badge_one, badge_two, stat_one_label, stat_one_value, stat_two_label, stat_two_value, primary_text, primary_url, secondary_text, secondary_url, sort_order, published) SELECT ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? WHERE NOT EXISTS (SELECT 1 FROM hero_banners WHERE page_key = ?)');
        foreach ($heroBannerDefaults as $banner) {
            $insertHeroBanner->execute(array_merge($banner, [$banner[0]]));
        }

        $practiceSettingDefaults = [
            'practice_enabled' => 'Yes',
            'local_mode_enabled' => 'Yes',
            'browser_voice_enabled' => 'Yes',
            'ai_enabled' => 'No',
            'ai_correction_enabled' => 'Yes',
            'ai_fallback_enabled' => 'Yes',
            'ai_provider' => 'openai',
            'openai_api_key' => '',
            'openai_model' => 'gpt-4o-mini',
            'openai_endpoint' => 'https://api.openai.com/v1/chat/completions',
            'ai_daily_limit' => '10',
            'ai_timeout_seconds' => '18',
            'ai_temperature' => '0.2',
            'ai_system_prompt' => 'You are a friendly spoken English practice coach for Indian learners. Correct grammar, make answers natural, explain simply, and keep feedback short.',
            'free_daily_limit' => '20',
            'practice_intro_note' => 'Start with free local practice. AI enhancement can be enabled later from settings without breaking the core practice engine.'
        ];
        $insertPracticeSetting = db()->prepare('INSERT IGNORE INTO practice_settings (setting_key, setting_value) VALUES (?, ?)');
        foreach ($practiceSettingDefaults as $key => $value) {
            $insertPracticeSetting->execute([$key, $value]);
        }

        $practiceCategories = [
            ['Tense Practice', 'tense-practice', 'Practise all useful tenses with fill blanks, sentence making and conversion exercises.', '🧠', 1],
            ['Situation Practice', 'situation-practice', 'Learn what to say in school, office, interview, market, phone calls and daily life.', '💬', 2],
            ['Sentence Correction', 'sentence-correction', 'Type your sentence and get local correction tips using common mistake rules.', '✍️', 3],
            ['Voice Practice', 'voice-practice', 'Speak using browser voice input and compare your spoken sentence with the correct answer.', '🎤', 4]
        ];
        $insertPracticeCategory = db()->prepare('INSERT INTO practice_categories (category_name, slug, description, icon, sort_order, published) SELECT ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM practice_categories WHERE slug = ?)');
        foreach ($practiceCategories as $cat) {
            $insertPracticeCategory->execute([$cat[0], $cat[1], $cat[2], $cat[3], $cat[4], 'Yes', $cat[1]]);
        }

        $catMap = [];
        foreach (db()->query('SELECT id, slug FROM practice_categories')->fetchAll() as $catRow) {
            $catMap[$catRow['slug']] = (int)$catRow['id'];
        }
        $practiceLessons = [
            ['tense-practice', 'Present Simple Practice', 'tense', 'Beginner', 'Present Simple', 'Practise daily routine sentences using base verb and s/es.', 'Use base verb with I/You/We/They. Use s/es with He/She/It.', 1],
            ['tense-practice', 'Past Simple Practice', 'tense', 'Beginner', 'Past Simple', 'Practise completed actions using verb second form.', 'Use V2 for positive past sentences and did + base verb for negative/questions.', 2],
            ['tense-practice', 'Present Continuous Practice', 'tense', 'Beginner', 'Present Continuous', 'Practise actions happening now using am/is/are + verb-ing.', 'Use am/is/are + verb-ing for current actions.', 3],
            ['situation-practice', 'Daily Life Situations', 'situation', 'Beginner', '', 'Practise simple answers for real daily speaking situations.', 'Write a natural answer for the situation. Focus on clear and polite English.', 1],
            ['situation-practice', 'Interview Speaking', 'situation', 'Intermediate', '', 'Practise interview answers such as self introduction and strengths.', 'Write a short confident answer. Keep it natural and professional.', 2],
            ['sentence-correction', 'Correct My Sentence', 'correction', 'All Levels', '', 'Type or practise incorrect sentences and learn the correct version.', 'Compare your answer with the corrected sample and explanation.', 1],
            ['voice-practice', 'Speak and Compare', 'voice', 'All Levels', '', 'Use browser voice typing to practise English pronunciation and sentence flow.', 'Click Start Speaking, say your answer, then compare it with the correct answer.', 1]
        ];
        $insertPracticeLesson = db()->prepare('INSERT INTO practice_lessons (category_id, lesson_title, lesson_type, level, tense_name, short_description, instructions, sort_order, published) SELECT ?, ?, ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM practice_lessons WHERE lesson_title = ?)');
        foreach ($practiceLessons as $lesson) {
            $insertPracticeLesson->execute([$catMap[$lesson[0]] ?? 0, $lesson[1], $lesson[2], $lesson[3], $lesson[4], $lesson[5], $lesson[6], $lesson[7], 'Yes', $lesson[1]]);
        }

        $lessonMap = [];
        foreach (db()->query('SELECT id, lesson_title, category_id FROM practice_lessons')->fetchAll() as $lessonRow) {
            $lessonMap[$lessonRow['lesson_title']] = [(int)$lessonRow['id'], (int)$lessonRow['category_id']];
        }
        $practiceQuestions = [
            ['Present Simple Practice', 'fill_blank', 'I ___ tea every morning.', '', '', '', '', 'drink', 'I drink tea every morning.', 'With I, use base verb in Present Simple.', 'Present Simple', 'Beginner', 1],
            ['Present Simple Practice', 'fill_blank', 'She ___ English every day.', '', '', '', '', 'speaks', 'She speaks English every day.', 'With She/He/It, add s/es to the verb.', 'Present Simple', 'Beginner', 2],
            ['Past Simple Practice', 'fill_blank', 'I ___ to the market yesterday.', '', '', '', '', 'went', 'I went to the market yesterday.', 'Yesterday shows past time, so use went.', 'Past Simple', 'Beginner', 1],
            ['Past Simple Practice', 'conversion', 'Convert to negative: I watched the class video.', '', '', '', '', 'I did not watch the class video.', 'I did not watch the class video.', 'In Past Simple negative, use did not + base verb.', 'Past Simple', 'Beginner', 2],
            ['Present Continuous Practice', 'fill_blank', 'They ___ speaking English now.', '', '', '', '', 'are', 'They are speaking English now.', 'They uses are in Present Continuous.', 'Present Continuous', 'Beginner', 1],
            ['Daily Life Situations', 'situation', 'You are late for class. What will you say to your teacher?', '', '', '', '', 'Sorry, teacher. I am late because there was traffic.', 'Sorry, teacher. I am late because there was traffic. It will not happen again.', 'Use polite apology + clear reason + promise.', '', 'Beginner', 1],
            ['Daily Life Situations', 'situation', 'You want to ask someone for help in English. What will you say?', '', '', '', '', 'Could you please help me?', 'Excuse me, could you please help me with this?', 'Use could you please for polite requests.', '', 'Beginner', 2],
            ['Interview Speaking', 'situation', 'Answer this interview question: Tell me about yourself.', '', '', '', '', 'My name is Rahul. I am a hardworking student and I want to improve my communication skills.', 'My name is Rahul. I have completed my studies and I am improving my English communication to build a better career.', 'Keep your answer short, confident and relevant.', '', 'Intermediate', 1],
            ['Correct My Sentence', 'correction', 'Correct this sentence: I am go market yesterday.', '', '', '', '', 'I went to the market yesterday.', 'I went to the market yesterday.', 'Yesterday needs Past Simple. Use went, not am go.', '', 'Beginner', 1],
            ['Speak and Compare', 'voice', 'Speak this sentence clearly: I want to improve my English speaking confidence.', '', '', '', '', 'I want to improve my English speaking confidence.', 'I want to improve my English speaking confidence.', 'Speak slowly, clearly and repeat until the sentence feels natural.', '', 'All Levels', 1]
        ];
        $insertPracticeQuestion = db()->prepare('INSERT INTO practice_questions (category_id, lesson_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, sample_answer, explanation, tense_name, level, sort_order, published) SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM practice_questions WHERE lesson_id = ? AND question_text = ?)');
        foreach ($practiceQuestions as $question) {
            [$lessonId, $categoryId] = $lessonMap[$question[0]] ?? [0, 0];
            $insertPracticeQuestion->execute([$categoryId, $lessonId, $question[1], $question[2], $question[3], $question[4], $question[5], $question[6], $question[7], $question[8], $question[9], $question[10], $question[11], $question[12], 'Yes', $lessonId, $question[2]]);
        }

        $mistakes = [
            ['I am go', 'I am going / I went', 'Use am/is/are + verb-ing for now, or Past Simple for yesterday/past time.', 'I am going to the market. / I went to the market yesterday.'],
            ['He go', 'He goes', 'With He/She/It in Present Simple, add s/es.', 'He goes to class every day.'],
            ['I has', 'I have', 'Use have with I/You/We/They.', 'I have a notebook.'],
            ['did not went', 'did not go', 'After did/did not, use base verb.', 'I did not go to class yesterday.']
        ];
        $insertMistake = db()->prepare('INSERT INTO practice_common_mistakes (wrong_pattern, correct_pattern, explanation, example_sentence, published) SELECT ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM practice_common_mistakes WHERE wrong_pattern = ?)');
        foreach ($mistakes as $mistake) {
            $insertMistake->execute([$mistake[0], $mistake[1], $mistake[2], $mistake[3], 'Yes', $mistake[0]]);
        }

        $insertPracticeNav = db()->prepare('INSERT INTO nav_menus (menu_area, label, url, is_cta, sort_order, published) SELECT ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM nav_menus WHERE menu_area = ? AND url = ?)');
        $insertPracticeNav->execute(['header', 'Practice Room', 'spoken-materials.php', 'No', 6, 'Yes', 'header', 'spoken-materials.php']);
        $insertPracticeNav->execute(['footer', 'Practice Room', 'spoken-materials.php', 'No', 6, 'Yes', 'footer', 'spoken-materials.php']);
        $insertPracticeNav->execute(['header', 'Student Login', 'student-auth.php', 'No', 45, 'Yes', 'header', 'student-auth.php']);
        $insertPracticeNav->execute(['footer', 'Student Login', 'student-auth.php', 'No', 45, 'Yes', 'footer', 'student-auth.php']);
        $insertPracticeNav->execute(['header', 'Roadmap', 'learning-roadmap.php', 'No', 40, 'Yes', 'header', 'learning-roadmap.php']);
        $insertPracticeNav->execute(['footer', 'Learning Roadmap', 'learning-roadmap.php', 'No', 40, 'Yes', 'footer', 'learning-roadmap.php']);

        if (!column_exists('enquiries', 'current_level')) db()->exec("ALTER TABLE enquiries ADD current_level VARCHAR(120) NULL AFTER course_interest");
        if (!column_exists('enquiries', 'preferred_batch')) db()->exec("ALTER TABLE enquiries ADD preferred_batch VARCHAR(120) NULL AFTER current_level");
        if (!column_exists('enquiries', 'enquiry_status')) db()->exec("ALTER TABLE enquiries ADD enquiry_status VARCHAR(40) NOT NULL DEFAULT 'New' AFTER message");
        if (!column_exists('enquiries', 'follow_up_date')) db()->exec("ALTER TABLE enquiries ADD follow_up_date DATE NULL AFTER enquiry_status");
        if (!column_exists('enquiries', 'admin_note')) db()->exec("ALTER TABLE enquiries ADD admin_note TEXT NULL AFTER follow_up_date");
        if (!column_exists('enquiries', 'lead_source')) db()->exec("ALTER TABLE enquiries ADD lead_source VARCHAR(80) NULL AFTER preferred_batch");
        if (!column_exists('enquiries', 'lead_priority')) db()->exec("ALTER TABLE enquiries ADD lead_priority VARCHAR(30) NOT NULL DEFAULT 'Normal' AFTER enquiry_status");
        if (!column_exists('enquiries', 'last_contacted_at')) db()->exec("ALTER TABLE enquiries ADD last_contacted_at DATETIME NULL AFTER follow_up_date");
        if (!column_exists('enquiries', 'updated_at')) db()->exec("ALTER TABLE enquiries ADD updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        if (!column_exists('gallery_images', 'image_alt')) db()->exec("ALTER TABLE gallery_images ADD image_alt VARCHAR(180) NULL AFTER image_url");

        try {
            $phase83SaveMarker = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $phase83SaveMarker->execute(['schema_marker', $phase83SchemaMarker]);
        } catch (Throwable $e) {
            // Marker save failure should not block site.
        }
    } catch (Throwable $e) {
        // Keep the website running even if the DB user cannot ALTER/CREATE tables.
    }
}

function student_account_ensure_schema(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    try {
        ensure_schema_updates();
        if (!column_exists('students', 'auth_version')) {
            db_exec_safe("ALTER TABLE students ADD auth_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER password_hash");
        }
        if (!column_exists('students', 'password_changed_at')) {
            db_exec_safe("ALTER TABLE students ADD password_changed_at DATETIME NULL AFTER auth_version");
        }
        db_exec_safe("CREATE TABLE IF NOT EXISTS student_account_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL,
            admin_id INT UNSIGNED NULL,
            event_type VARCHAR(60) NOT NULL,
            event_title VARCHAR(180) NOT NULL,
            event_note TEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_student_account_event (student_id, created_at),
            KEY idx_student_account_type (event_type, created_at),
            KEY idx_student_account_admin (admin_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        error_log('[student-account-schema] ' . $e->__toString());
    }
}

function student_account_has_auth_version(): bool
{
    static $supported = null;
    if ($supported !== null) return $supported;
    student_account_ensure_schema();
    return $supported = column_exists('students', 'auth_version');
}

function student_account_has_password_changed_at(): bool
{
    static $supported = null;
    if ($supported !== null) return $supported;
    student_account_ensure_schema();
    return $supported = column_exists('students', 'password_changed_at');
}

function student_account_session_signature(array $student): string
{
    $sessionVersion = array_key_exists('auth_version', $student)
        ? (string)($student['auth_version'] ?? 1)
        : (string)($student['updated_at'] ?? '');
    return hash('sha256', implode('|', [
        (string)($student['password_hash'] ?? ''),
        $sessionVersion,
        (string)($student['published'] ?? 'No'),
        (string)($student['status_deleted'] ?? 0),
    ]));
}

function student_account_invalidate_sessions(int $studentId): void
{
    if ($studentId <= 0) return;
    if (student_account_has_auth_version()) {
        db()->prepare('UPDATE students SET auth_version=auth_version+1, updated_at=NOW() WHERE id=?')->execute([$studentId]);
    } else {
        db()->prepare('UPDATE students SET updated_at=DATE_ADD(COALESCE(updated_at,NOW()), INTERVAL 1 SECOND) WHERE id=?')->execute([$studentId]);
    }
}

function student_account_reset_password(int $studentId, string $passwordHash): bool
{
    if ($studentId <= 0 || $passwordHash === '') return false;
    $sets = ['password_hash=?', 'updated_at=NOW()'];
    if (student_account_has_auth_version()) $sets[] = 'auth_version=auth_version+1';
    if (student_account_has_password_changed_at()) $sets[] = 'password_changed_at=NOW()';
    $stmt = db()->prepare('UPDATE students SET ' . implode(', ', $sets) . ' WHERE id=? AND status_deleted=0');
    $stmt->execute([$passwordHash, $studentId]);
    return $stmt->rowCount() === 1;
}

function student_account_log(int $studentId, string $eventType, string $eventTitle, string $eventNote = ''): void
{
    if ($studentId <= 0) return;
    student_account_ensure_schema();
    $adminName = trim((string)($_SESSION['admin_name'] ?? 'System')) ?: 'System';
    try {
        if (table_exists('student_account_events')) {
            $stmt = db()->prepare('INSERT INTO student_account_events (student_id,admin_id,event_type,event_title,event_note,ip_address) VALUES (?,?,?,?,?,?)');
            $stmt->execute([
                $studentId,
                !empty($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null,
                mb_substr(trim($eventType) ?: 'account_update', 0, 60),
                mb_substr(trim($eventTitle) ?: 'Student account updated', 0, 180),
                trim($eventNote) ?: null,
                client_ip(),
            ]);
            return;
        }
        $stmt = db()->prepare('INSERT INTO student_activity_logs (student_id,activity_type,activity_title,note) VALUES (?,?,?,?)');
        $stmt->execute([
            $studentId,
            'account_' . mb_substr(preg_replace('/[^a-z0-9_]+/i', '_', trim($eventType)) ?: 'update', 0, 60),
            mb_substr(trim($eventTitle) ?: 'Student account updated', 0, 180),
            trim(($eventNote !== '' ? $eventNote . ' ' : '') . 'Admin: ' . $adminName),
        ]);
    } catch (Throwable $e) {
        error_log('[student-account-log] ' . $e->__toString());
    }
}

function student_account_events(int $studentId, int $limit = 20): array
{
    if ($studentId <= 0) return [];
    student_account_ensure_schema();
    $limit = max(1, min(100, $limit));
    try {
        if (table_exists('student_account_events')) {
            $stmt = db()->prepare('SELECT e.*, a.name admin_name FROM student_account_events e LEFT JOIN admins a ON a.id=e.admin_id WHERE e.student_id=? ORDER BY e.id DESC LIMIT ' . $limit);
            $stmt->execute([$studentId]);
            return $stmt->fetchAll();
        }
        $stmt = db()->prepare("SELECT id,student_id,NULL admin_id,REPLACE(activity_type,'account_','') event_type,activity_title event_title,note event_note,NULL ip_address,created_at,NULL admin_name FROM student_activity_logs WHERE student_id=? AND activity_type LIKE 'account_%' ORDER BY id DESC LIMIT " . $limit);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function student_password_error(string $password): string
{
    $length = strlen($password);
    if ($length < 8) return 'Password must be at least 8 characters so it works with student login.';
    if ($length > 128) return 'Password must not exceed 128 characters.';
    if (trim($password) === '') return 'Password cannot contain only spaces.';
    return '';
}

/**
 * Admin-assisted recovery deliberately has fewer composition rules than student
 * self-registration. The institute can set the exact temporary password agreed
 * with the student, while still rejecting blank/oversized values.
 */
function student_admin_password_error(string $password): string
{
    if ($password === '' || trim($password) === '') return 'Enter the new student password.';
    if (strlen($password) > 128) return 'Password must not exceed 128 characters.';
    return '';
}

/**
 * Return the student's effective/manual Weekly Test batch access for Admin UI.
 * Manual test access is stored in a learning-only enrollment so admission
 * memberships are never overwritten by this control.
 */
function student_weekly_test_batch_access(int $studentId): array
{
    $studentId = max(0, $studentId);
    $out = ['label'=>'Admission / Common only','manual'=>false,'manual_batch_id'=>0,'batch_ids'=>[]];
    if ($studentId <= 0 || !table_exists('student_batch_memberships')) return $out;
    try {
        $stmt = db()->prepare("SELECT sbm.batch_id, COALESCE(NULLIF(bt.batch_name,''), NULLIF(sbm.batch_name_snapshot,''), CONCAT('Batch #',sbm.batch_id)) batch_name, COALESCE(se.course_title_snapshot,'') enrollment_label, se.admission_id
            FROM student_batch_memberships sbm
            JOIN student_enrollments se ON se.id=sbm.enrollment_id
            LEFT JOIN batch_timings bt ON bt.id=sbm.batch_id
            WHERE sbm.student_id=? AND sbm.membership_status='Active' AND sbm.batch_id IS NOT NULL
              AND se.enrollment_status NOT IN ('Cancelled','Completed')
            ORDER BY (se.course_title_snapshot='Weekly Test Access') DESC, sbm.id DESC");
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll();
        $labels=[];
        foreach ($rows as $row) {
            $bid=(int)($row['batch_id']??0); if($bid<=0) continue;
            $out['batch_ids'][$bid]=true;
            $labels[]=(string)($row['batch_name']??('Batch #'.$bid));
            if ((string)($row['enrollment_label']??'') === 'Weekly Test Access') {
                $out['manual']=true;
                if ($out['manual_batch_id']<=0) $out['manual_batch_id']=$bid;
            }
        }
        if ($labels) $out['label']=implode(' · ', array_values(array_unique($labels)));
    } catch (Throwable $e) {
        error_log('[student-test-access-read] ' . $e->getMessage());
    }
    return $out;
}

function student_set_weekly_test_batch_access(int $studentId, int $batchId): void
{
    $studentId=max(0,$studentId); $batchId=max(0,$batchId);
    if($studentId<=0) throw new RuntimeException('Invalid student account.');
    if(!table_exists('student_enrollments') || !table_exists('student_batch_memberships')) throw new RuntimeException('Weekly Test batch-access tables are not ready. Run Admin > System Check and complete the backend database upgrade.');
    if($batchId>0){
        $b=db()->prepare("SELECT id,batch_name FROM batch_timings WHERE id=? AND published='Yes' LIMIT 1");
        $b->execute([$batchId]); $batch=$b->fetch();
        if(!$batch) throw new RuntimeException('Choose a valid active batch.');
    } else { $batch=null; }

    $pdo=db(); $owns=!$pdo->inTransaction();
    try {
        if($owns) $pdo->beginTransaction();
        $en=$pdo->prepare("SELECT id FROM student_enrollments WHERE student_id=? AND admission_id IS NULL AND course_title_snapshot='Weekly Test Access' AND enrollment_status IN ('Pending','Active','Completed') ORDER BY id DESC LIMIT 1 FOR UPDATE");
        $en->execute([$studentId]); $enrollmentId=(int)($en->fetchColumn()?:0);
        if($enrollmentId<=0 && $batchId>0){
            $pdo->prepare("INSERT INTO student_enrollments (student_id,admission_id,course_id,course_title_snapshot,enrollment_status,joined_at) VALUES (?,NULL,NULL,'Weekly Test Access','Active',NOW())")->execute([$studentId]);
            $enrollmentId=(int)$pdo->lastInsertId();
        }
        if($enrollmentId>0){
            $pdo->prepare("UPDATE student_batch_memberships SET membership_status='Left',left_at=NOW() WHERE enrollment_id=? AND membership_status='Active'")->execute([$enrollmentId]);
            if($batchId>0){
                $pdo->prepare("INSERT INTO student_batch_memberships (enrollment_id,student_id,batch_id,batch_name_snapshot,membership_status,joined_at) VALUES (?,?,?,?, 'Active',NOW())")
                    ->execute([$enrollmentId,$studentId,$batchId,(string)$batch['batch_name']]);
                $pdo->prepare("UPDATE student_enrollments SET enrollment_status='Active', joined_at=COALESCE(joined_at,NOW()), completed_at=NULL WHERE id=?")->execute([$enrollmentId]);
            } else {
                $pdo->prepare("UPDATE student_enrollments SET enrollment_status='Completed', completed_at=NOW() WHERE id=?")->execute([$enrollmentId]);
            }
        }
        if($owns) $pdo->commit();
    } catch(Throwable $e){
        if($owns && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function is_student(): bool
{
    return !empty($_SESSION['student_id']);
}

function current_student_id(): int
{
    return (int)($_SESSION['student_id'] ?? 0);
}

function student_session_login(array $student): void
{
    student_account_ensure_schema();
    session_regenerate_id(true);
    $_SESSION['student_id'] = (int)$student['id'];
    $_SESSION['student_name'] = (string)($student['full_name'] ?? 'Student');
    $_SESSION['student_auth_signature'] = student_account_session_signature($student);
    $_SESSION['student_last_activity'] = time();
}

function student_session_logout(): void
{
    unset($_SESSION['student_id'], $_SESSION['student_name'], $_SESSION['student_auth_signature'], $_SESSION['student_last_activity']);
    session_regenerate_id(true);
}

function require_student(): void
{
    private_no_store();
    if (!is_student()) redirect('student-auth.php');
    $last = (int)($_SESSION['student_last_activity'] ?? 0);
    if ($last > 0 && time() - $last > 7200) {
        student_session_logout();
        redirect('student-auth.php?expired=1');
    }
    try {
        student_account_ensure_schema();
        $stmt = db()->prepare("SELECT id, full_name, password_hash, published, status_deleted, updated_at" . (student_account_has_auth_version() ? ", auth_version" : "") . " FROM students WHERE id=? LIMIT 1");
        $stmt->execute([current_student_id()]);
        $student = $stmt->fetch();
        if (!$student || (int)($student['status_deleted'] ?? 0) !== 0 || ($student['published'] ?? 'No') !== 'Yes') {
            student_session_logout();
            redirect('student-auth.php?inactive=1');
        }
        $databaseSignature = student_account_session_signature($student);
        $sessionSignature = (string)($_SESSION['student_auth_signature'] ?? '');
        if ($sessionSignature !== '' && !hash_equals($databaseSignature, $sessionSignature)) {
            student_session_logout();
            redirect('student-auth.php?reset=1');
        }
        $_SESSION['student_auth_signature'] = $databaseSignature;
        $_SESSION['student_name'] = (string)$student['full_name'];
        $_SESSION['student_last_activity'] = time();
    } catch (Throwable $e) {
        student_session_logout();
        redirect('student-auth.php');
    }
}

function fetch_current_student(): ?array
{
    if (!is_student()) return null;
    try {
        student_account_ensure_schema();
        $stmt = db()->prepare("SELECT * FROM students WHERE id=? AND status_deleted=0 AND published='Yes' LIMIT 1");
        $stmt->execute([current_student_id()]);
        $student = $stmt->fetch();
        if (!$student) {
            unset($_SESSION['student_id'], $_SESSION['student_name'], $_SESSION['student_auth_signature'], $_SESSION['student_last_activity']);
            return null;
        }
        return $student;
    } catch (Throwable $e) {
        return null;
    }
}

function student_level_steps(): array
{
    return [
        ['Zero Level', 'Start from Hindi thinking, alphabet sounds, daily words and simple self-introduction.', 'Alphabet, pronunciation, 100 daily words, greetings, self introduction'],
        ['Basic', 'Build correct simple sentences and remove fear while speaking small English lines.', 'Is/am/are, has/have, do/does, basic verbs, questions and negatives'],
        ['Intermediate', 'Use tenses, paragraph translation and daily-life conversations with confidence.', 'All common tenses, situations, phone English, market/class/office conversation'],
        ['Advanced', 'Prepare for interviews, presentations, professional communication and fluent topic speaking.', 'Interview answers, email writing, group discussion, public speaking, advanced correction']
    ];
}

function student_recommended_modules(string $level): array
{
    $level = strtolower($level);
    if (strpos($level, 'zero') !== false) {
        return ['Daily-use words', 'Self introduction', 'Hindi to English short sentences', 'Verb 1 basics', 'Speaking 5 lines daily'];
    }
    if (strpos($level, 'basic') !== false) {
        return ['Is/am/are practice', 'Present Simple', 'Question making', 'Daily conversation', '20 translations daily'];
    }
    if (strpos($level, 'intermediate') !== false) {
        return ['All tenses revision', 'Paragraph translation', 'Situation speaking', 'Sentence correction', 'Interview basics'];
    }
    return ['Topic speaking', 'Interview fluency', 'Email/business English', 'Advanced grammar correction', 'Presentation practice'];
}

function fetch_student_activity(int $studentId, int $limit = 20): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM student_activity_logs WHERE student_id = ? ORDER BY id DESC LIMIT ' . (int)$limit);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function student_activity_summary(int $studentId): array
{
    $summary = ['total' => 0, 'today' => 0, 'avg_score' => 0, 'last_date' => ''];
    try {
        $stmt = db()->prepare("SELECT COUNT(*) total_count, SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) today_count, ROUND(AVG(score)) avg_score, MAX(created_at) last_date FROM student_activity_logs WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $row = $stmt->fetch() ?: [];
        $summary['total'] = (int)($row['total_count'] ?? 0);
        $summary['today'] = (int)($row['today_count'] ?? 0);
        $summary['avg_score'] = (int)($row['avg_score'] ?? 0);
        $summary['last_date'] = (string)($row['last_date'] ?? '');
    } catch (Throwable $e) {
        // Keep dashboard available even if summary fails.
    }
    return $summary;
}

function fetch_students_for_admin(int $limit = 200): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT s.*, (SELECT COUNT(*) FROM student_activity_logs a WHERE a.student_id=s.id) activity_count FROM students s WHERE s.status_deleted = 0 ORDER BY s.id DESC LIMIT ' . (int)$limit);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}



function fetch_public_nav_menu(string $area): array
{
    $area = in_array($area, ['header','footer'], true) ? $area : 'header';
    try {
        ensure_schema_updates();
        $stmt = db()->prepare("SELECT label, url, is_cta, sort_order FROM nav_menus WHERE menu_area=? AND published IN ('Yes','Y','1') ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$area]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function nav_url_key(string $url): string
{
    $url = strtolower(trim($url));
    $url = strtok($url, '?') ?: $url;
    return trim($url, '/');
}

function nav_is_blocked_feature(string $url, string $label = ''): bool
{
    $v = strtolower($url . ' ' . $label);
    foreach (['ai-teacher', 'practice-lab', 'free-ai-english-practice'] as $blocked) {
        if (strpos($v, $blocked) !== false) {
            return true;
        }
    }
    return false;
}

function app_setting(string $key, string $default = ''): string
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            ensure_schema_updates();
            $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = (string)$row['setting_value'];
            }
        } catch (Throwable $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

function save_app_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

function course_money_label($price): string
{
    $price = trim((string)$price);
    if ($price === '' || (float)$price <= 0) {
        return 'Fee on Enquiry';
    }
    return '₹' . number_format((float)$price, 0);
}

function course_pay_url(array $course): string
{
    $pay = trim((string)($course['pay_url'] ?? ''));
    if ($pay !== '') {
        return $pay;
    }
    return 'admission.php?course=' . rawurlencode((string)($course['title'] ?? 'Course'));
}

function secure_upload_directory(string $dir): void
{
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Upload directory could not be created.');
    }

    // Defence in depth for Apache/XAMPP/shared hosting. Uploaded files are also
    // renamed to a server-generated image extension, but the upload directory
    // itself must never execute server-side scripts.
    $rules = <<<'HTACCESS'
Options -Indexes -ExecCGI
<IfModule mod_mime.c>
  RemoveHandler .php .phtml .phar .php3 .php4 .php5 .php7 .php8 .cgi .pl .py .sh
  RemoveType .php .phtml .phar .php3 .php4 .php5 .php7 .php8 .cgi .pl .py .sh
</IfModule>
<FilesMatch "\.(php|phtml|phar|php[0-9]*|cgi|pl|py|sh|shtml|htaccess)$">
  Require all denied
</FilesMatch>
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set Content-Security-Policy "default-src 'none'; img-src 'self' data:; media-src 'self'; style-src 'none'; script-src 'none'; sandbox"
</IfModule>
HTACCESS;
    $rules .= "\n";
    @file_put_contents($dir . '/.htaccess', $rules, LOCK_EX);
    if (!is_file($dir . '/index.html')) @file_put_contents($dir . '/index.html', '', LOCK_EX);
}

function upload_detect_mime(string $tmp): string
{
    if ($tmp === '' || !is_file($tmp)) return '';
    try {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return strtolower((string)$finfo->file($tmp));
    } catch (Throwable $e) {
        return '';
    }
}

function secure_image_allowed_extensions(): array
{
    return ['jpg', 'jpeg', 'png', 'webp'];
}

function secure_image_extension_for_mime(string $mime): string
{
    return match (strtolower(trim($mime))) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => '',
    };
}

function secure_image_filename_is_allowed(string $originalName): bool
{
    $name = trim(str_replace(["\0", "\r", "\n"], '', basename($originalName)));
    if ($name === '') return false;
    if (preg_match('/\.(?:php[0-9]*|phtml|phar|cgi|pl|py|sh|jsp|asp|aspx|shtml|htaccess)(?:\.|$)/i', $name)) return false;
    $ext = strtolower((string)pathinfo($name, PATHINFO_EXTENSION));
    return in_array($ext, secure_image_allowed_extensions(), true);
}

function secure_image_reencode(string $source, string $target, string $mime): bool
{
    if (!function_exists('imagecreatefromstring')) return false;
    $bytes = @file_get_contents($source);
    if ($bytes === false || $bytes === '') return false;
    $image = @imagecreatefromstring($bytes);
    if (!$image) return false;

    try {
        if ($mime === 'image/jpeg' && function_exists('imagejpeg')) {
            return (bool)@imagejpeg($image, $target, 90);
        }
        if ($mime === 'image/png' && function_exists('imagepng')) {
            @imagealphablending($image, false);
            @imagesavealpha($image, true);
            return (bool)@imagepng($image, $target, 6);
        }
        if ($mime === 'image/webp' && function_exists('imagewebp')) {
            @imagealphablending($image, false);
            @imagesavealpha($image, true);
            return (bool)@imagewebp($image, $target, 88);
        }
        return false;
    } finally {
        if (is_resource($image) || is_object($image)) @imagedestroy($image);
    }
}

function secure_image_upload(array $file, string $folder, string $prefix, int $maxBytes = 2097152): ?string
{
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) return null;
    if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed. Please try again.');

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) throw new RuntimeException('Image size is not allowed.');

    $originalName = (string)($file['name'] ?? '');
    if (!secure_image_filename_is_allowed($originalName)) {
        throw new RuntimeException('Only JPG, JPEG, PNG and WEBP image files are allowed.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_file($tmp) || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Invalid upload source.');
    }

    $mime = upload_detect_mime($tmp);
    $serverExt = secure_image_extension_for_mime($mime);
    if ($serverExt === '') throw new RuntimeException('Only JPG, JPEG, PNG and WEBP image files are allowed.');

    $clientExt = strtolower((string)pathinfo($originalName, PATHINFO_EXTENSION));
    $extensionMatches = $mime === 'image/jpeg'
        ? in_array($clientExt, ['jpg', 'jpeg'], true)
        : $clientExt === $serverExt;
    if (!$extensionMatches) throw new RuntimeException('Image extension does not match the uploaded image content.');

    $info = @getimagesize($tmp);
    if (!is_array($info) || empty($info[0]) || empty($info[1])) throw new RuntimeException('Invalid or damaged image file.');
    $width = (int)$info[0];
    $height = (int)$info[1];
    if ($width < 1 || $height < 1 || $width > 12000 || $height > 12000 || ($width * $height) > 40000000) {
        throw new RuntimeException('Image dimensions are too large.');
    }

    if (function_exists('exif_imagetype')) {
        $type = @exif_imagetype($tmp);
        $expected = $mime === 'image/jpeg' ? IMAGETYPE_JPEG : ($mime === 'image/png' ? IMAGETYPE_PNG : (defined('IMAGETYPE_WEBP') ? IMAGETYPE_WEBP : 18));
        if ($type !== false && $type !== $expected) throw new RuntimeException('Image signature does not match its declared type.');
    }

    $dir = dirname(__DIR__) . '/assets/uploads/' . trim($folder, '/');
    secure_upload_directory($dir);
    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '', $prefix) ?: 'image';
    $name = $safePrefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $serverExt;
    $target = $dir . '/' . $name;

    // When GD is available, decoding and re-encoding strips trailing/polyglot
    // payload data instead of preserving attacker-controlled file bytes.
    $sanitized = secure_image_reencode($tmp, $target, $mime);
    if (!$sanitized && !move_uploaded_file($tmp, $target)) {
        throw new RuntimeException('Could not save uploaded image.');
    }
    @chmod($target, 0644);
    return 'assets/uploads/' . trim($folder, '/') . '/' . $name;
}

function upload_course_image(array $file): ?string
{
    return secure_image_upload($file, 'courses', 'course', 2 * 1024 * 1024);
}


function upload_admission_photo(array $file): ?string
{
    return secure_image_upload($file, 'admissions', 'admission', 2 * 1024 * 1024);
}

function fetch_courses(int $limit = 50): array
{
    ensure_schema_updates();
    $stmt = db()->prepare('SELECT * FROM courses WHERE published = ? ORDER BY sort_order ASC, id ASC LIMIT ' . max(200, (int)$limit));
    $stmt->execute(['Yes']);
    $rows = $stmt->fetchAll();
    $unique = [];
    $seen = [];
    foreach ($rows as $row) {
        $key = strtolower(trim((string)($row['title'] ?? '')));
        if ($key !== '' && isset($seen[$key])) {
            continue;
        }
        if ($key !== '') {
            $seen[$key] = true;
        }
        $unique[] = $row;
        if (count($unique) >= $limit) {
            break;
        }
    }
    return $unique;
}

function fetch_course(int $id): ?array
{
    ensure_schema_updates();
    $stmt = db()->prepare('SELECT * FROM courses WHERE id = ? AND published = ? LIMIT 1');
    $stmt->execute([$id, 'Yes']);
    $course = $stmt->fetch();
    return $course ?: null;
}

function fetch_course_variants(int $courseId): array
{
    ensure_schema_updates();
    $stmt = db()->prepare('SELECT * FROM course_variants WHERE course_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$courseId]);
    return $stmt->fetchAll();
}

function fetch_testimonials(int $limit = 6): array
{
    $limit = max(1, min(100, $limit));
    $stmt = db()->prepare('SELECT * FROM testimonials WHERE published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . $limit);
    $stmt->execute(['Yes']);
    return $stmt->fetchAll();
}

function fetch_videos(int $limit = 3): array
{
    $limit = max(1, min(100, $limit));
    $stmt = db()->prepare('SELECT * FROM videos WHERE published = ? ORDER BY id DESC LIMIT ' . $limit);
    $stmt->execute(['Yes']);
    return $stmt->fetchAll();
}

function fetch_gallery(int $limit = 30): array
{
    try {
        ensure_schema_updates();
        $limit = max(1, min(500, $limit));
        $stmt = db()->prepare('SELECT * FROM gallery_images WHERE published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . $limit);
        $stmt->execute(['Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_faqs(int $limit = 20): array
{
    try {
        ensure_schema_updates();
        $limit = max(1, min(200, $limit));
        $stmt = db()->prepare('SELECT * FROM faqs WHERE published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . $limit);
        $stmt->execute(['Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_batch_timings(int $limit = 20): array
{
    try {
        ensure_schema_updates();
        $limit = max(1, min(200, $limit));
        $stmt = db()->prepare('SELECT * FROM batch_timings WHERE published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . $limit);
        $stmt->execute(['Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_content_blocks(string $type, int $limit = 20): array
{
    try {
        ensure_schema_updates();
        $limit = max(1, min(500, $limit));
        $stmt = db()->prepare('SELECT * FROM content_blocks WHERE block_type = ? AND published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . $limit);
        $stmt->execute([$type, 'Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_form_options(string $group, int $limit = 50): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM form_options WHERE option_group = ? AND published = ? ORDER BY sort_order ASC, id ASC LIMIT ' . (int)$limit);
        $stmt->execute([$group, 'Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_nav_items(string $area = 'header', int $limit = 20): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM nav_menus WHERE menu_area = ? AND published = ? ORDER BY sort_order ASC, id ASC LIMIT ' . max(50, (int)$limit * 3));
        $stmt->execute([$area, 'Yes']);
        $rows = $stmt->fetchAll();
        $unique = [];
        $seen = [];
        foreach ($rows as $row) {
            $label = trim((string)($row['label'] ?? ''));
            $url = trim((string)($row['url'] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            $cleanUrl = strtolower(trim(preg_replace('/[?#].*$/', '', $url), '/'));
            $labelKey = strtolower(preg_replace('/\s+/', ' ', $label));
            $urlKey = $cleanUrl;
            if (isset($seen['label:' . $labelKey]) || isset($seen['url:' . $urlKey])) {
                continue;
            }
            $seen['label:' . $labelKey] = true;
            $seen['url:' . $urlKey] = true;
            $unique[] = $row;
            if (count($unique) >= $limit) {
                break;
            }
        }
        return $unique;
    } catch (Throwable $e) {
        return [];
    }
}

function safe_admin_table(string $table): ?string
{
    $allowed = ['courses','testimonials','videos','gallery_images','faqs','batch_timings','content_blocks','form_options','nav_menus','practice_categories','practice_lessons','practice_questions','practice_common_mistakes','practice_settings','hero_banners'];
    return in_array($table, $allowed, true) ? $table : null;
}

function badge_class_for_status(string $status): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $status));
    if ($slug === 'converted') return 'badge-converted';
    if ($slug === 'contacted') return 'badge-contacted';
    if ($slug === 'notinterested') return 'badge-notinterested';
    return 'badge-new';
}

function youtube_embed_url(string $url): string
{
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return $url;
}

function clean_phone_digits(string $phone): string
{
    return preg_replace('/\D+/', '', $phone);
}

function fetch_hero_banner(string $pageKey = 'home'): ?array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM hero_banners WHERE page_key = ? AND published = ? ORDER BY sort_order ASC, id DESC LIMIT 1');
        $stmt->execute([$pageKey, 'Yes']);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function fetch_hero_banners(string $pageKey = 'home', int $limit = 8): array
{
    try {
        ensure_schema_updates();
        $safeLimit = max(1, min(20, $limit));
        $stmt = db()->prepare('SELECT * FROM hero_banners WHERE page_key = ? AND published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . $safeLimit);
        $stmt->execute([$pageKey, 'Yes']);
        return $stmt->fetchAll() ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function upload_hero_banner_image(array $file, string $variant = 'general'): ?string
{
    $safeVariant = in_array($variant, ['desktop', 'mobile', 'general'], true) ? $variant : 'general';
    return secure_image_upload($file, 'banners', 'banner-' . $safeVariant, 3 * 1024 * 1024);
}

function practice_ai_status_label(): string
{
    if (practice_setting('ai_enabled', 'No') !== 'Yes') return 'AI Off - Local practice is active';
    if (trim(practice_setting('openai_api_key', '')) === '') return 'AI On but API key missing - local fallback active';
    if (!function_exists('curl_init')) return 'AI On but PHP cURL missing - local fallback active';
    $limit = max(0, (int)practice_setting('ai_daily_limit', '10'));
    if ($limit > 0 && practice_today_ai_used() >= $limit) return 'Daily AI limit reached - local fallback active';
    return 'AI Ready - optional correction active';
}


function upload_brand_asset(array $file, string $type = 'logo'): ?string
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    try {
        return secure_image_upload($file, 'brand', $type, 2 * 1024 * 1024);
    } catch (RuntimeException $e) {
        return null;
    }
}

function site_asset_url(string $path): string
{
    $path = trim(str_replace(["\r", "\n", "\0"], '', html_entity_decode($path, ENT_QUOTES, 'UTF-8')));
    if ($path === '' || str_starts_with($path, '//') || str_contains($path, '\\')) return '';
    if (preg_match('/[\x00-\x1F\x7F]/', $path)) return '';

    $scheme = strtolower((string)(parse_url($path, PHP_URL_SCHEME) ?? ''));
    if ($scheme !== '') return in_array($scheme, ['http', 'https'], true) ? $path : '';

    $cleanPath = ltrim((string)(parse_url($path, PHP_URL_PATH) ?? ''), '/');
    if ($cleanPath === '' || preg_match('#(^|/)\.\.(/|$)#', $cleanPath)) return '';
    return ltrim($path, '/');
}


function app_css_asset_path(string $path): string
{
    $path = ltrim(trim($path), '/');
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }
    if (defined('APP_ENV') && APP_ENV === 'production' && (!defined('APP_DEBUG') || !APP_DEBUG) && str_ends_with($path, '.css') && !str_ends_with($path, '.min.css')) {
        $minified = substr($path, 0, -4) . '.min.css';
        if (is_file(dirname(__DIR__) . '/' . $minified)) {
            return $minified;
        }
    }
    return $path;
}

function app_asset_versioned(string $path): string
{
    $path = ltrim(trim($path), '/');
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $file = dirname(__DIR__) . '/' . preg_replace('/[?#].*$/', '', $path);
    $version = is_file($file) ? (string)filemtime($file) : '123';
    return $path . (str_contains($path, '?') ? '&' : '?') . 'v=' . rawurlencode($version);
}

function upload_gallery_image(array $file): ?string
{
    return secure_image_upload($file, 'gallery', 'gallery', 2 * 1024 * 1024);
}

function login_blocked(): bool
{
    $until = $_SESSION['login_blocked_until'] ?? 0;
    return $until && time() < (int)$until;
}

function register_failed_login(): void
{
    $_SESSION['login_attempts'] = (int)($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) $_SESSION['login_blocked_until'] = time() + 900;
}

function clear_login_attempts(): void
{
    unset($_SESSION['login_attempts'], $_SESSION['login_blocked_until']);
}

function practice_setting(string $key, string $default = ''): string
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT setting_value FROM practice_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? (string)$row['setting_value'] : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function save_practice_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO practice_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

function fetch_practice_categories(int $limit = 20): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM practice_categories WHERE published = ? AND status_deleted = 0 ORDER BY sort_order ASC, id ASC LIMIT ' . (int)$limit);
        $stmt->execute(['Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_practice_lessons(int $categoryId = 0, int $limit = 40): array
{
    try {
        ensure_schema_updates();
        if ($categoryId > 0) {
            $stmt = db()->prepare('SELECT * FROM practice_lessons WHERE category_id = ? AND published = ? AND status_deleted = 0 ORDER BY sort_order ASC, id ASC LIMIT ' . (int)$limit);
            $stmt->execute([$categoryId, 'Yes']);
        } else {
            $stmt = db()->prepare('SELECT * FROM practice_lessons WHERE published = ? AND status_deleted = 0 ORDER BY sort_order ASC, id ASC LIMIT ' . (int)$limit);
            $stmt->execute(['Yes']);
        }
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_practice_lesson(int $id): ?array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM practice_lessons WHERE id = ? AND published = ? AND status_deleted = 0 LIMIT 1');
        $stmt->execute([$id, 'Yes']);
        $lesson = $stmt->fetch();
        return $lesson ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function fetch_practice_questions(int $lessonId, int $limit = 12): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM practice_questions WHERE lesson_id = ? AND published = ? AND status_deleted = 0 ORDER BY sort_order ASC, id ASC LIMIT ' . (int)$limit);
        $stmt->execute([$lessonId, 'Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function normalize_answer(string $value): string
{
    $value = trim($value);
    $value = str_replace(["’", "‘", "`", "´"], "'", $value);
    $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
    $value = mb_strtolower($value, 'UTF-8');
    $value = preg_replace('/[\p{P}\p{S}]+/u', ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value);
    return trim($value ?? '');
}

function expand_answer_contractions(string $value): string
{
    $map = [
        "isn't" => 'is not', "aren't" => 'are not', "wasn't" => 'was not', "weren't" => 'were not',
        "don't" => 'do not', "doesn't" => 'does not', "didn't" => 'did not', "can't" => 'cannot',
        "couldn't" => 'could not', "won't" => 'will not', "wouldn't" => 'would not', "shouldn't" => 'should not',
        "i'm" => 'i am', "you're" => 'you are', "he's" => 'he is', "she's" => 'she is', "it's" => 'it is',
        "that's" => 'that is', "what's" => 'what is', "i've" => 'i have', "i'll" => 'i will'
    ];
    return str_ireplace(array_keys($map), array_values($map), $value);
}

function answer_variants_from_question(array $question): array
{
    $raw = [];
    foreach (['correct_answer', 'sample_answer', 'accepted_answers'] as $field) {
        $text = trim((string)($question[$field] ?? ''));
        if ($text === '') continue;
        foreach (preg_split('/\r\n|\r|\n|\|\|/u', $text) as $piece) {
            $piece = trim($piece);
            if ($piece !== '') $raw[] = $piece;
        }
    }
    $variants = [];
    foreach (array_unique($raw) as $answer) {
        $variants[] = $answer;
        $expanded = expand_answer_contractions($answer);
        if ($expanded !== $answer) $variants[] = $expanded;
    }
    return array_values(array_unique(array_filter($variants)));
}

function answer_similarity_score(string $a, string $b): int
{
    $a = normalize_answer(expand_answer_contractions($a));
    $b = normalize_answer(expand_answer_contractions($b));
    if ($a === '' || $b === '') return 0;
    if ($a === $b) return 100;
    similar_text($a, $b, $percent);
    $aWords = array_values(array_filter(explode(' ', $a)));
    $bWords = array_values(array_filter(explode(' ', $b)));
    $overlap = count(array_intersect($aWords, $bWords));
    $wordScore = $bWords ? (int)round(($overlap / max(1, count($bWords))) * 100) : 0;
    return (int)max(round($percent), $wordScore);
}

function evaluate_practice_answer(array $question, string $answer): array
{
    $user = normalize_answer(expand_answer_contractions($answer));
    $answers = answer_variants_from_question($question);
    $sample = (string)($question['sample_answer'] ?: $question['correct_answer']);
    $mode = (string)($question['answer_match_mode'] ?? 'smart');
    $isCorrect = false;
    $score = 0;
    $matchType = 'no_match';
    $best = 0;

    foreach ($answers as $possible) {
        $norm = normalize_answer(expand_answer_contractions($possible));
        if ($norm !== '' && $user === $norm) {
            $isCorrect = true;
            $score = 10;
            $matchType = 'exact_or_accepted';
            break;
        }
        $best = max($best, answer_similarity_score($answer, $possible));
    }

    if (!$isCorrect && $mode === 'contains_keywords') {
        foreach ($answers as $possible) {
            $norm = normalize_answer(expand_answer_contractions($possible));
            $words = array_filter(explode(' ', $norm), fn($w) => mb_strlen($w, 'UTF-8') > 2);
            $hits = 0;
            foreach ($words as $w) { if (str_contains($user, $w)) $hits++; }
            if ($words && $hits >= max(2, (int)ceil(count($words) * 0.65))) {
                $isCorrect = true;
                $score = 8;
                $matchType = 'keyword_match';
                break;
            }
        }
    }

    if (!$isCorrect && $mode === 'smart' && $best >= 88) {
        $isCorrect = true;
        $score = 9;
        $matchType = 'smart_close_match';
    } elseif (!$isCorrect && $best >= 70) {
        $score = 5;
        $matchType = 'partial_match';
    }

    $feedback = $isCorrect ? 'Correct. Good job. This answer matches the teacher-approved answer set.' : 'Good try. Compare your answer with the teacher answer and practise again.';
    try {
        $stmt = db()->query("SELECT * FROM practice_common_mistakes WHERE published='Yes' AND status_deleted=0 ORDER BY id ASC");
        foreach ($stmt->fetchAll() as $mistake) {
            if (stripos($answer, $mistake['wrong_pattern']) !== false) {
                $feedback = 'Common mistake found: use "' . $mistake['correct_pattern'] . '". ' . $mistake['explanation'];
                break;
            }
        }
    } catch (Throwable $e) {}

    if (!$isCorrect && $question['question_type'] === 'situation' && strlen(trim($answer)) > 20 && $score < 5) {
        $score = 5;
        $matchType = 'situation_effort';
        $feedback = 'Your answer has effort. Now make it more natural using the teacher sample answer.';
    }

    return [
        'is_correct' => $isCorrect,
        'score' => $score,
        'match_type' => $matchType,
        'feedback' => $feedback,
        'sample_answer' => $sample,
        'accepted_answers' => $answers,
        'explanation' => (string)($question['explanation'] ?? ''),
        'answer_help' => (string)($question['answer_help'] ?? ''),
        'next_step' => $isCorrect ? 'Try the next question or speak this sentence three times.' : 'Read the correct answer, rewrite it once, and say it loudly three times.'
    ];
}

function practice_today_ai_used(): int
{
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $stmt = db()->prepare('SELECT COUNT(*) AS total FROM practice_ai_logs WHERE session_id = ? AND status = ? AND DATE(created_at) = CURDATE()');
        $stmt->execute([session_id(), 'success']);
        return (int)(($stmt->fetch()['total'] ?? 0));
    } catch (Throwable $e) {
        return 0;
    }
}

function practice_can_use_ai(): bool
{
    if (practice_setting('ai_enabled', 'No') !== 'Yes') return false;
    if (practice_setting('ai_correction_enabled', 'Yes') !== 'Yes') return false;
    if (trim(practice_setting('openai_api_key', '')) === '') return false;
    $limit = max(0, (int)practice_setting('ai_daily_limit', '10'));
    return $limit === 0 || practice_today_ai_used() < $limit;
}

function log_practice_ai(int $questionId, string $status, string $error = '', int $promptChars = 0, int $responseChars = 0): void
{
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $stmt = db()->prepare('INSERT INTO practice_ai_logs (session_id, question_id, provider, model, request_type, prompt_chars, response_chars, status, error_message) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            session_id(),
            $questionId,
            practice_setting('ai_provider', 'openai'),
            practice_setting('openai_model', 'gpt-4o-mini'),
            'practice_feedback',
            $promptChars,
            $responseChars,
            $status,
            $error
        ]);
    } catch (Throwable $e) {
        // Never break practice because AI logging failed.
    }
}

function extract_json_object(string $text): ?array
{
    $decoded = json_decode($text, true);
    if (is_array($decoded)) return $decoded;
    if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $m)) {
        $decoded = json_decode($m[0], true);
        return is_array($decoded) ? $decoded : null;
    }
    return null;
}

function practice_ai_feedback(array $question, string $answer, array $localResult): ?array
{
    if (!practice_can_use_ai()) {
        log_practice_ai((int)($question['id'] ?? 0), 'skipped', 'AI disabled, API key missing, or daily limit reached.');
        return null;
    }
    $apiKey = trim(practice_setting('openai_api_key', ''));
    $endpoint = trim(practice_setting('openai_endpoint', 'https://api.openai.com/v1/chat/completions'));
    $model = trim(practice_setting('openai_model', 'gpt-4o-mini')) ?: 'gpt-4o-mini';
    $timeout = max(8, min(45, (int)practice_setting('ai_timeout_seconds', '18')));
    $temperature = (float)practice_setting('ai_temperature', '0.2');
    $systemPrompt = trim(practice_setting('ai_system_prompt', '')) ?: 'You are a friendly spoken English practice coach. Keep feedback short and helpful.';
    $payloadPrompt = "Question type: " . ($question['question_type'] ?? '') . "\n" .
        "Question: " . ($question['question_text'] ?? '') . "\n" .
        "Expected answer: " . ($question['sample_answer'] ?: $question['correct_answer'] ?? '') . "\n" .
        "Learner answer: " . $answer . "\n" .
        "Local feedback: " . ($localResult['feedback'] ?? '') . "\n" .
        "Return ONLY JSON with keys: score, corrected_answer, natural_answer, mistake, simple_explanation, practice_tip, next_question_suggestion. Keep values short and beginner friendly.";
    $payload = [
        'model' => $model,
        'temperature' => $temperature,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $payloadPrompt]
        ]
    ];
    $jsonPayload = json_encode($payload);
    if (!function_exists('curl_init')) {
        log_practice_ai((int)($question['id'] ?? 0), 'error', 'PHP cURL extension is not enabled.', strlen($payloadPrompt), 0);
        return null;
    }
    try {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => $timeout
        ]);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            log_practice_ai((int)($question['id'] ?? 0), 'error', $curlError ?: ('OpenAI HTTP ' . $httpCode), strlen($payloadPrompt), is_string($response) ? strlen($response) : 0);
            return null;
        }
        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';
        $data = extract_json_object($content);
        if (!$data) {
            log_practice_ai((int)($question['id'] ?? 0), 'error', 'AI response was not valid JSON.', strlen($payloadPrompt), strlen($content));
            return null;
        }
        log_practice_ai((int)($question['id'] ?? 0), 'success', '', strlen($payloadPrompt), strlen($content));
        return [
            'score' => isset($data['score']) ? (int)$data['score'] : (int)($localResult['score'] ?? 0),
            'corrected_answer' => trim((string)($data['corrected_answer'] ?? $localResult['sample_answer'] ?? '')),
            'natural_answer' => trim((string)($data['natural_answer'] ?? $localResult['sample_answer'] ?? '')),
            'mistake' => trim((string)($data['mistake'] ?? '')),
            'simple_explanation' => trim((string)($data['simple_explanation'] ?? '')),
            'practice_tip' => trim((string)($data['practice_tip'] ?? '')),
            'next_question_suggestion' => trim((string)($data['next_question_suggestion'] ?? '')),
            'model' => $model
        ];
    } catch (Throwable $e) {
        log_practice_ai((int)($question['id'] ?? 0), 'error', $e->getMessage(), strlen($payloadPrompt), 0);
        return null;
    }
}

function merge_ai_practice_result(array $localResult, ?array $aiResult): array
{
    if (!$aiResult) {
        $localResult['ai_status'] = practice_setting('ai_enabled', 'No') === 'Yes' ? 'fallback' : 'off';
        $localResult['ai_feedback'] = '';
        $localResult['corrected_answer'] = $localResult['sample_answer'] ?? '';
        $localResult['natural_answer'] = $localResult['sample_answer'] ?? '';
        $localResult['ai_model'] = '';
        return $localResult;
    }
    $localResult['score'] = max((int)($localResult['score'] ?? 0), min(10, (int)($aiResult['score'] ?? 0)));
    $localResult['ai_status'] = 'success';
    $localResult['ai_model'] = $aiResult['model'] ?? '';
    $localResult['corrected_answer'] = $aiResult['corrected_answer'] ?: ($localResult['sample_answer'] ?? '');
    $localResult['natural_answer'] = $aiResult['natural_answer'] ?: ($localResult['sample_answer'] ?? '');
    $feedbackParts = array_filter([
        $aiResult['mistake'] ? 'Mistake: ' . $aiResult['mistake'] : '',
        $aiResult['simple_explanation'] ? 'Explanation: ' . $aiResult['simple_explanation'] : '',
        $aiResult['practice_tip'] ? 'Practice tip: ' . $aiResult['practice_tip'] : '',
        $aiResult['next_question_suggestion'] ? 'Next: ' . $aiResult['next_question_suggestion'] : ''
    ]);
    $localResult['ai_feedback'] = implode("\n", $feedbackParts);
    return $localResult;
}

function save_practice_attempt(int $questionId, string $answer, array $result): void
{
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $stmt = db()->prepare('INSERT INTO practice_attempts (session_id, question_id, user_answer, correct_answer, score, local_feedback, suggested_next_step, is_correct, match_type, ai_feedback, ai_status, ai_model, corrected_answer, natural_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            session_id(),
            $questionId,
            $answer,
            $result['sample_answer'] ?? '',
            (int)($result['score'] ?? 0),
            $result['feedback'] ?? '',
            $result['next_step'] ?? '',
            !empty($result['is_correct']) ? 1 : 0,
            $result['match_type'] ?? '',
            $result['ai_feedback'] ?? '',
            $result['ai_status'] ?? '',
            $result['ai_model'] ?? '',
            $result['corrected_answer'] ?? ($result['sample_answer'] ?? ''),
            $result['natural_answer'] ?? ($result['sample_answer'] ?? '')
        ]);
    } catch (Throwable $e) {
        // Practice should never fail for the user because logging failed.
    }
}

function practice_attempt_summary(): array
{
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $stmt = db()->prepare('SELECT COUNT(*) total, COALESCE(SUM(score),0) score FROM practice_attempts WHERE session_id = ? AND DATE(created_at)=CURDATE()');
        $stmt->execute([session_id()]);
        $row = $stmt->fetch() ?: ['total' => 0, 'score' => 0];
        return ['total' => (int)$row['total'], 'score' => (int)$row['score']];
    } catch (Throwable $e) {
        return ['total' => 0, 'score' => 0];
    }
}


function material_ensure_schema(): void
{
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    static $phase84MaterialDone = false;
    if ($phase84MaterialDone) { return; }
    $phase84MaterialDone = true;

    $phase84MaterialMarker = 'phase84_material_schema_v1';
    try {
        $phase84Stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
        $phase84Stmt->execute(['material_schema_marker']);
        if ((string)($phase84Stmt->fetchColumn() ?: '') === $phase84MaterialMarker) {
            return;
        }
    } catch (Throwable $e) {
        // Continue with schema creation if marker cannot be read.
    }
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS material_collections (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            slug VARCHAR(180) NULL,
            category VARCHAR(120) NULL,
            level VARCHAR(80) NULL,
            description TEXT NULL,
            cover_image VARCHAR(500) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_material_collection (published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS material_assets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            collection_id INT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(180) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            original_name VARCHAR(255) NULL,
            file_type VARCHAR(40) NULL,
            notes TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_material_assets (collection_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS material_units (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            collection_id INT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(180) NOT NULL,
            unit_type VARCHAR(80) NOT NULL DEFAULT 'lesson',
            tense_name VARCHAR(120) NULL,
            level VARCHAR(80) NULL,
            instructions TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_material_units (collection_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS translation_pairs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            collection_id INT UNSIGNED NOT NULL DEFAULT 0,
            unit_id INT UNSIGNED NOT NULL DEFAULT 0,
            hindi_text TEXT NOT NULL,
            english_text TEXT NOT NULL,
            roman_text TEXT NULL,
            tense_name VARCHAR(120) NULL,
            situation_tag VARCHAR(120) NULL,
            level VARCHAR(80) NULL,
            explanation TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_translation_pairs (collection_id, unit_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS material_practice_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(120) NOT NULL,
            pair_id INT UNSIGNED NOT NULL DEFAULT 0,
            practice_direction VARCHAR(40) NOT NULL DEFAULT 'hindi_to_english',
            user_answer TEXT NULL,
            correct_answer TEXT NULL,
            score INT NOT NULL DEFAULT 0,
            feedback TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_material_attempts (session_id, created_at),
            INDEX idx_material_pair (pair_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS material_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach ([
            'material_collections' => [
                'practice_priority' => "INT NOT NULL DEFAULT 0"
            ],
            'material_units' => [
                'practice_priority' => "INT NOT NULL DEFAULT 0"
            ],
            'material_assets' => [
                'practice_priority' => "INT NOT NULL DEFAULT 0"
            ],
            'translation_pairs' => [
                'accepted_english_answers' => "TEXT NULL",
                'accepted_hindi_answers' => "TEXT NULL",
                'answer_match_mode' => "VARCHAR(40) NOT NULL DEFAULT 'smart'",
                'sentence_type' => "VARCHAR(80) NULL",
                'difficulty_level' => "VARCHAR(80) NULL",
                'common_mistakes' => "TEXT NULL",
                'teacher_hint' => "TEXT NULL",
                'practice_priority' => "INT NOT NULL DEFAULT 0"
            ],
            'material_practice_attempts' => [
                'student_id' => "INT UNSIGNED NOT NULL DEFAULT 0 AFTER session_id",
                'is_correct' => "TINYINT(1) NOT NULL DEFAULT 0",
                'match_type' => "VARCHAR(80) NULL"
            ]
        ] as $tableName => $columns) {
            foreach ($columns as $columnName => $definition) {
                if (!column_exists($tableName, $columnName)) {
                    db_exec_safe("ALTER TABLE `" . $tableName . "` ADD COLUMN `" . $columnName . "` " . $definition);
                }
            }
        }


        db_exec_safe("ALTER TABLE material_practice_attempts ADD INDEX idx_material_attempt_student (student_id, created_at)");
        db_exec_safe("ALTER TABLE material_practice_attempts ADD INDEX idx_material_attempt_correct (student_id, is_correct, created_at)");

        db()->exec("INSERT INTO material_settings (setting_key, setting_value) VALUES
            ('material_library_enabled','Yes'),
            ('material_public_title','Spoken English Material & Hindi-English Practice'),
            ('material_public_subtitle','Learn from notes, practise Hindi to English and English to Hindi, and improve sentence making daily.'),
            ('material_upload_max_note','Recommended: upload notes in small batches. Images/PDF/TXT are supported; CSV/text import is best for very big sentence data.'),
            ('material_daily_practice_limit','50'),
            ('auto_translate_enabled','No'),
            ('auto_translate_provider','none'),
            ('auto_translate_note','Use teacher-approved material first. External translation requires a legal provider/API key and is optional.')
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        db()->exec("INSERT INTO material_collections (title, slug, category, level, description, cover_image, sort_order, published)
            SELECT 'Uploaded Spoken Notes', 'uploaded-spoken-notes', 'Notes', 'Beginner to Advanced', 'Your uploaded spoken English WhatsApp note images are stored here. Admin can add more images and convert important lines into Hindi-English practice pairs.', '', 1, 'Yes'
            WHERE NOT EXISTS (SELECT 1 FROM material_collections WHERE slug='uploaded-spoken-notes')");

        db()->exec("INSERT INTO material_collections (title, slug, category, level, description, cover_image, sort_order, published)
            SELECT 'Daily Hindi to English Sentences', 'daily-hindi-to-english', 'Translation Practice', 'Beginner', 'Daily-use Hindi sentences with simple spoken English answers for regular practice.', '', 2, 'Yes'
            WHERE NOT EXISTS (SELECT 1 FROM material_collections WHERE slug='daily-hindi-to-english')");

        $collectionId = (int)(db()->query("SELECT id FROM material_collections WHERE slug='daily-hindi-to-english' LIMIT 1")->fetchColumn() ?: 0);
        if ($collectionId) {
            db()->exec("INSERT INTO material_units (collection_id, title, unit_type, tense_name, level, instructions, sort_order, published)
                SELECT {$collectionId}, 'Daily Life Sentences', 'translation', 'Mixed Tenses', 'Beginner', 'Read Hindi, speak/write English, then compare with the natural answer.', 1, 'Yes'
                WHERE NOT EXISTS (SELECT 1 FROM material_units WHERE collection_id={$collectionId} AND title='Daily Life Sentences')");
            $unitId = (int)(db()->query("SELECT id FROM material_units WHERE collection_id={$collectionId} AND title='Daily Life Sentences' LIMIT 1")->fetchColumn() ?: 0);
            if ($unitId) {
                $seedPairs = [
                    ['मैं रोज अंग्रेजी बोलने की कोशिश करता हूँ।','I try to speak English every day.','Present Simple','Daily Practice','Beginner','Use try to + base verb for habit/practice.'],
                    ['मैंने कल अपना होमवर्क पूरा किया।','I completed my homework yesterday.','Past Simple','Daily Practice','Beginner','Yesterday shows past time, so use completed.'],
                    ['मैं अभी अंग्रेजी सीख रहा हूँ।','I am learning English right now.','Present Continuous','Daily Practice','Beginner','Right now shows action happening now: am/is/are + verb-ing.'],
                    ['क्या आप मेरी मदद कर सकते हैं?','Can you help me?','Modal Verb','Polite Speaking','Beginner','Can you is a simple polite request.'],
                    ['मुझे इंटरव्यू के लिए अंग्रेजी सुधारनी है।','I want to improve my English for an interview.','Present Simple','Interview','Beginner','Want to + verb is used for goals.'],
                    ['मैं पहले अंग्रेजी बोलने से डरता था।','I used to be afraid of speaking English.','Past Habit','Confidence','Intermediate','Used to describes a past habit or past condition.'],
                    ['कृपया इस वाक्य को दोहराइए।','Please repeat this sentence.','Imperative','Classroom','Beginner','Please makes the command polite.'],
                    ['मैं फोन पर स्पष्ट बोलना चाहता हूँ।','I want to speak clearly on the phone.','Present Simple','Phone English','Beginner','Clearly describes how you want to speak.']
                ];
                $stmt = db()->prepare("INSERT INTO translation_pairs (collection_id, unit_id, hindi_text, english_text, tense_name, situation_tag, level, explanation, sort_order, published)
                    SELECT ?,?,?,?,?,?,?,?,?, 'Yes' WHERE NOT EXISTS (SELECT 1 FROM translation_pairs WHERE hindi_text=? AND english_text=? LIMIT 1)");
                $i = 1;
                foreach ($seedPairs as $p) {
                    $stmt->execute([$collectionId, $unitId, $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $i++, $p[0], $p[1]]);
                }
            }
        }


        // Keep frontend/admin useful even when admin selects the uploaded-notes group.
        $uploadedId = (int)(db()->query("SELECT id FROM material_collections WHERE slug='uploaded-spoken-notes' LIMIT 1")->fetchColumn() ?: 0);
        if ($uploadedId) {
            $hasUploadedPairs = (int)(db()->query("SELECT COUNT(*) FROM translation_pairs WHERE collection_id={$uploadedId} AND status_deleted=0")->fetchColumn() ?: 0);
            if ($hasUploadedPairs === 0) {
                db()->exec("INSERT INTO material_units (collection_id, title, unit_type, tense_name, level, instructions, sort_order, published)
                    SELECT {$uploadedId}, 'Starter Daily Practice', 'translation', 'Mixed Tenses', 'Beginner', 'Starter sample records. Replace with your own class sentences anytime.', 1, 'Yes'
                    WHERE NOT EXISTS (SELECT 1 FROM material_units WHERE collection_id={$uploadedId} AND title='Starter Daily Practice')");
                $uploadedUnitId = (int)(db()->query("SELECT id FROM material_units WHERE collection_id={$uploadedId} AND title='Starter Daily Practice' LIMIT 1")->fetchColumn() ?: 0);
                if ($uploadedUnitId) {
                    $starterPairs = [
                        ['मैं रोज अंग्रेजी बोलने की कोशिश करता हूँ।','I try to speak English every day.','Present Simple','Daily Practice','Beginner','Use try to + base verb for habit/practice.'],
                        ['मैं अभी अंग्रेजी सीख रहा हूँ।','I am learning English right now.','Present Continuous','Daily Practice','Beginner','Right now shows action happening now.'],
                        ['क्या आप मेरी मदद कर सकते हैं?','Can you help me?','Modal Verb','Polite Speaking','Beginner','Can you is a polite request.'],
                        ['मैं बाजार जा रहा हूँ।','I am going to the market.','Present Continuous','Daily Speaking','Beginner','Use am going to for current/future movement.'],
                        ['मुझे अपना परिचय देना है।','I have to introduce myself.','Modal Phrase','Interview','Beginner','Have to means जरूरी है.']
                    ];
                    $starterStmt = db()->prepare("INSERT INTO translation_pairs (collection_id, unit_id, hindi_text, english_text, tense_name, situation_tag, level, explanation, sort_order, published)
                        SELECT ?,?,?,?,?,?,?,?,?, 'Yes' WHERE NOT EXISTS (SELECT 1 FROM translation_pairs WHERE collection_id=? AND hindi_text=? AND english_text=? LIMIT 1)");
                    $starterOrder = 1;
                    foreach ($starterPairs as $sp) {
                        $starterStmt->execute([$uploadedId, $uploadedUnitId, $sp[0], $sp[1], $sp[2], $sp[3], $sp[4], $sp[5], $starterOrder++, $uploadedId, $sp[0], $sp[1]]);
                    }
                }
            }
        }

        if (column_exists('nav_menus', 'menu_area')) {
            db()->exec("INSERT INTO nav_menus (menu_area, label, url, is_cta, sort_order, published)
                SELECT 'header', 'Study Material', 'spoken-materials.php', 'No', 35, 'Yes'
                WHERE NOT EXISTS (SELECT 1 FROM nav_menus WHERE menu_area='header' AND url='spoken-materials.php')");
            db()->exec("INSERT INTO nav_menus (menu_area, label, url, is_cta, sort_order, published)
                SELECT 'footer', 'Study Material', 'spoken-materials.php', 'No', 35, 'Yes'
                WHERE NOT EXISTS (SELECT 1 FROM nav_menus WHERE menu_area='footer' AND url='spoken-materials.php')");
        }
        seed_uploaded_note_assets();
        material_seed_use_practice_library(false);

        try {
            $phase84Save = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $phase84Save->execute(['material_schema_marker', $phase84MaterialMarker]);
        } catch (Throwable $e) {
            // Marker save failure should not block the page.
        }
    } catch (Throwable $e) {
        // Material library should not break the website if hosting permissions are limited.
    }
}


function material_seed_use_practice_library(bool $force = false): int
{
    try {
        $csvPath = __DIR__ . '/../sql/spoken_use_library_1000.csv';
        if (!is_file($csvPath)) return 0;
        $slug = 'spoken-use-tense-practice-library';
        $stmt = db()->prepare("SELECT id FROM material_collections WHERE slug=? AND status_deleted=0 LIMIT 1");
        $stmt->execute([$slug]);
        $collectionId = (int)($stmt->fetchColumn() ?: 0);
        if (!$collectionId) {
            $stmt = db()->prepare("INSERT INTO material_collections (title, slug, category, level, description, sort_order, published) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute(['Spoken Use & Tense Practice Library', $slug, 'Practice Library', 'Beginner to Advanced', '1000 ready Hindi-English spoken practice sentences by use, tense, question type and daily situation.', 0, 'Yes']);
            $collectionId = (int)db()->lastInsertId();
        } else {
            db()->prepare("UPDATE material_collections SET sort_order=0, published='Yes' WHERE id=?")->execute([$collectionId]);
        }
        $existing = (int)(db()->query("SELECT COUNT(*) FROM translation_pairs WHERE collection_id={$collectionId} AND status_deleted=0")->fetchColumn() ?: 0);
        if (!$force && $existing >= 950) return 0;
        $handle = fopen($csvPath, 'r');
        if (!$handle) return 0;
        $header = fgetcsv($handle);
        $unitCache = [];
        $inserted = 0;
        $sort = $existing + 1;
        $unitFind = db()->prepare("SELECT id FROM material_units WHERE collection_id=? AND title=? AND status_deleted=0 LIMIT 1");
        $unitInsert = db()->prepare("INSERT INTO material_units (collection_id, title, unit_type, tense_name, level, instructions, sort_order, published) VALUES (?,?,?,?,?,?,?,?)");
        $pairCheck = db()->prepare("SELECT id FROM translation_pairs WHERE collection_id=? AND hindi_text=? AND english_text=? AND status_deleted=0 LIMIT 1");
        $pairInsert = db()->prepare("INSERT INTO translation_pairs (collection_id, unit_id, hindi_text, english_text, roman_text, tense_name, situation_tag, level, accepted_english_answers, explanation, sort_order, published) VALUES (?,?,?,?,?,?,?,?,?,?,?, 'Yes')");
        while (($row = fgetcsv($handle)) !== false) {
            $hindi = trim((string)($row[0] ?? ''));
            $english = trim((string)($row[1] ?? ''));
            $roman = trim((string)($row[2] ?? ''));
            $topic = trim((string)($row[3] ?? 'Mixed Practice')) ?: 'Mixed Practice';
            $mode = trim((string)($row[4] ?? 'Practice')) ?: 'Practice';
            $level = trim((string)($row[5] ?? 'Beginner')) ?: 'Beginner';
            $accepted = trim((string)($row[6] ?? ''));
            $explanation = trim((string)($row[7] ?? ''));
            if ($hindi === '' || $english === '') continue;
            if (!isset($unitCache[$topic])) {
                $unitFind->execute([$collectionId, $topic]);
                $unitId = (int)($unitFind->fetchColumn() ?: 0);
                if (!$unitId) {
                    $unitInsert->execute([$collectionId, $topic, 'spoken_use_tense', $topic, $level, 'Practice this use/tense one sentence at a time: simple, negative, questions and WH questions.', count($unitCache) + 1, 'Yes']);
                    $unitId = (int)db()->lastInsertId();
                }
                $unitCache[$topic] = $unitId;
            }
            $pairCheck->execute([$collectionId, $hindi, $english]);
            if ($pairCheck->fetchColumn()) continue;
            $pairInsert->execute([$collectionId, $unitCache[$topic], $hindi, $english, $roman, $topic, $mode, $level, $accepted, $explanation, $sort++]);
            $inserted++;
        }
        fclose($handle);
        return $inserted;
    } catch (Throwable $e) {
        return 0;
    }
}

function material_setting(string $key, string $default = ''): string
{
    material_ensure_schema();
    try {
        $stmt = db()->prepare('SELECT setting_value FROM material_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string)$value : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function save_material_setting(string $key, string $value): void
{
    material_ensure_schema();
    $stmt = db()->prepare('INSERT INTO material_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}


function material_order_clause(string $table): string
{
    $parts = [];
    if (column_exists($table, 'practice_priority')) {
        $parts[] = 'practice_priority DESC';
    }
    if (column_exists($table, 'sort_order')) {
        $parts[] = 'sort_order ASC';
    }
    $parts[] = 'id DESC';
    return implode(', ', $parts);
}

function fetch_material_collections(int $limit = 50): array
{
    material_ensure_schema();
    $limit = max(1, min(500, (int)$limit));
    $order = material_order_clause('material_collections');
    $stmt = db()->prepare("SELECT * FROM material_collections WHERE published='Yes' AND status_deleted=0 ORDER BY {$order} LIMIT {$limit}");
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetch_material_practice_collections(int $limit = 50): array
{
    material_ensure_schema();
    $limit = max(1, min(500, $limit));
    $stmt = db()->prepare("SELECT c.* FROM material_collections c WHERE c.published='Yes' AND c.status_deleted=0 AND EXISTS (SELECT 1 FROM translation_pairs p WHERE p.collection_id=c.id AND p.published='Yes' AND p.status_deleted=0) ORDER BY c.sort_order ASC, c.id DESC LIMIT {$limit}");
    $stmt->execute();
    return $stmt->fetchAll();
}

function material_default_practice_collection_id(): int
{
    material_ensure_schema();
    $id = (int)(db()->query("SELECT c.id FROM material_collections c WHERE c.published='Yes' AND c.status_deleted=0 AND EXISTS (SELECT 1 FROM translation_pairs p WHERE p.collection_id=c.id AND p.published='Yes' AND p.status_deleted=0) ORDER BY c.sort_order ASC, c.id DESC LIMIT 1")->fetchColumn() ?: 0);
    return $id;
}

function fetch_material_collection(int $id): ?array
{
    material_ensure_schema();
    $stmt = db()->prepare("SELECT * FROM material_collections WHERE id=? AND status_deleted=0 LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function fetch_material_units(int $collectionId = 0, int $limit = 100): array
{
    material_ensure_schema();
    $limit = max(1, min(500, (int)$limit));
    $order = material_order_clause('material_units');
    if ($collectionId > 0) {
        $stmt = db()->prepare("SELECT * FROM material_units WHERE collection_id=? AND published='Yes' AND status_deleted=0 ORDER BY {$order} LIMIT {$limit}");
        $stmt->execute([$collectionId]);
    } else {
        $stmt = db()->prepare("SELECT * FROM material_units WHERE published='Yes' AND status_deleted=0 ORDER BY {$order} LIMIT {$limit}");
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function fetch_translation_pairs(int $collectionId = 0, int $unitId = 0, string $search = '', int $limit = 60): array
{
    material_ensure_schema();
    $where = ["published='Yes'", 'status_deleted=0'];
    $params = [];
    if ($collectionId > 0) { $where[] = 'collection_id=?'; $params[] = $collectionId; }
    if ($unitId > 0) { $where[] = 'unit_id=?'; $params[] = $unitId; }
    if ($search !== '') {
        $where[] = '(hindi_text LIKE ? OR english_text LIKE ? OR roman_text LIKE ? OR tense_name LIKE ? OR situation_tag LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like, $like);
    }
    $order = material_order_clause('translation_pairs');
    $sql = 'SELECT * FROM translation_pairs WHERE ' . implode(' AND ', $where) . " ORDER BY {$order} LIMIT {$limit}";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_material_assets(int $collectionId = 0, int $limit = 80): array
{
    material_ensure_schema();
    $limit = max(1, min(500, $limit));
    if ($collectionId > 0) {
        $stmt = db()->prepare("SELECT * FROM material_assets WHERE collection_id=? AND published='Yes' AND status_deleted=0 ORDER BY practice_priority DESC, sort_order ASC, id DESC LIMIT {$limit}");
        $stmt->execute([$collectionId]);
    } else {
        $stmt = db()->prepare("SELECT * FROM material_assets WHERE published='Yes' AND status_deleted=0 ORDER BY practice_priority DESC, sort_order ASC, id DESC LIMIT {$limit}");
        $stmt->execute();
    }
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        // Never expose the physical storage path to the browser. Files are delivered
        // only through material-file.php after an authenticated access check.
        $row['secure_url'] = 'material-file.php?id=' . (int)($row['id'] ?? 0);
    }
    unset($row);
    return $rows;
}

function upload_material_file(array $file): ?string
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Material upload failed.');
    if ((int)($file['size'] ?? 0) <= 0 || (int)$file['size'] > 8 * 1024 * 1024) throw new RuntimeException('Material file must be under 8 MB.');
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $mime = upload_detect_mime((string)$file['tmp_name']);
    $allowed = [
        'jpg'=>['image/jpeg'], 'jpeg'=>['image/jpeg'], 'png'=>['image/png'], 'webp'=>['image/webp'],
        'pdf'=>['application/pdf'], 'txt'=>['text/plain'],
        'csv'=>['text/csv','text/plain','application/csv','application/vnd.ms-excel']
    ];
    if (!isset($allowed[$ext]) || !in_array($mime, $allowed[$ext], true)) throw new RuntimeException('File type does not match the allowed material format.');

    $root = material_private_root();
    $dir = $root . '/materials';
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not prepare private material storage.');
    }
    $name = 'material-' . date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $target = $dir . '/' . $name;
    if (!move_uploaded_file((string)$file['tmp_name'], $target)) throw new RuntimeException('Could not save material file.');
    @chmod($target, 0640);
    return 'private/materials/' . $name;
}

function seed_uploaded_note_assets(): void
{
    try {
        $dir = __DIR__ . '/../assets/uploads/materials/notes';
        if (!is_dir($dir)) return;
        $collectionId = (int)(db()->query("SELECT id FROM material_collections WHERE slug='uploaded-spoken-notes' LIMIT 1")->fetchColumn() ?: 0);
        if (!$collectionId) return;
        $files = glob($dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
        sort($files);
        $stmt = db()->prepare("INSERT INTO material_assets (collection_id, title, file_path, original_name, file_type, notes, sort_order, published)
            SELECT ?,?,?,?,?,?,?, 'Yes' WHERE NOT EXISTS (SELECT 1 FROM material_assets WHERE file_path=? LIMIT 1)");
        $i = 1;
        foreach ($files as $file) {
            $path = 'assets/uploads/materials/notes/' . basename($file);
            $stmt->execute([$collectionId, 'Spoken Note Image ' . $i, $path, basename($file), 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', $i, $path]);
            $i++;
        }
    } catch (Throwable $e) {}
}

function normalize_material_answer(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $value = preg_replace('/[\p{P}\p{S}]+/u', ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value);
    return trim($value ?? '');
}

function material_answer_variants(array $pair, string $direction): array
{
    $fields = $direction === 'english_to_hindi'
        ? ['hindi_text', 'accepted_hindi_answers']
        : ['english_text', 'accepted_english_answers'];
    $raw = [];
    foreach ($fields as $field) {
        $text = trim((string)($pair[$field] ?? ''));
        if ($text === '') continue;
        foreach (preg_split('/\r\n|\r|\n|\|\|/u', $text) as $piece) {
            $piece = trim($piece);
            if ($piece !== '') $raw[] = $piece;
        }
    }
    $variants = [];
    foreach (array_unique($raw) as $answer) {
        $variants[] = $answer;
        if ($direction === 'hindi_to_english') {
            $expanded = expand_answer_contractions($answer);
            if ($expanded !== $answer) $variants[] = $expanded;
        }
    }
    return array_values(array_unique(array_filter($variants)));
}


function material_keyword_score(string $student, string $correct): int
{
    $a = normalize_material_answer($student);
    $b = normalize_material_answer($correct);
    if ($a === '' || $b === '') return 0;
    $aw = array_values(array_filter(explode(' ', $a)));
    $bw = array_values(array_filter(explode(' ', $b)));
    if (!$bw) return 0;
    $important = array_values(array_filter($bw, function($w){ return mb_strlen($w, 'UTF-8') > 2 && !in_array($w, ['the','and','are','you','with','for','this','that','hai','hain'], true); }));
    if (!$important) $important = $bw;
    $hit = 0;
    foreach ($important as $w) {
        if (in_array($w, $aw, true)) $hit++;
    }
    return (int)round(($hit / max(1, count($important))) * 100);
}

function material_teacher_feedback_rules(string $answer, string $correct, array $pair, string $direction): string
{
    $student = ' ' . normalize_material_answer($answer) . ' ';
    $topic = mb_strtolower((string)($pair['tense_name'] ?? ''), 'UTF-8');
    $hints = [];
    $adminMistakes = trim((string)($pair['common_mistakes'] ?? ''));
    $teacherHint = trim((string)($pair['teacher_hint'] ?? ''));
    if ($teacherHint !== '') $hints[] = $teacherHint;
    if ($adminMistakes !== '') $hints[] = 'Common mistake to avoid: ' . $adminMistakes;
    if ($direction !== 'english_to_hindi') {
        if (preg_match('/\bi\s+has\b/', $student)) $hints[] = 'With “I”, use “have”, not “has”.';
        if (preg_match('/\bhe\s+have\b|\bshe\s+have\b|\bit\s+have\b/', $student)) $hints[] = 'With he/she/it, use “has”, not “have”.';
        if (preg_match('/\bi\s+is\b/', $student)) $hints[] = 'With “I”, use “am”, not “is”.';
        if (preg_match('/\byou\s+is\b|\bwe\s+is\b|\bthey\s+is\b/', $student)) $hints[] = 'With you/we/they, use “are”, not “is”.';
        if (preg_match('/\bgo\s+market\b|\bgo\s+school\b|\bgo\s+office\b|\bgo\s+delhi\b/', $student)) $hints[] = 'Use “go to” before a place: go to market, go to school, go to Delhi.';
        if (str_contains($topic, 'present') && preg_match('/\byesterday\b|\blast night\b|\blast week\b/', $student)) $hints[] = 'Past time words usually need past tense.';
        if (str_contains($topic, 'past') && preg_match('/\btomorrow\b|\bnext week\b/', $student)) $hints[] = 'Future time words usually need “will” or “going to”.';
        if (str_contains($topic, 'question') && !preg_match('/\b(do|does|did|is|are|am|was|were|can|could|should|would|will|have|has)\b/', $student)) $hints[] = 'For questions, start with a helping verb or question word.';
    }
    if (!$hints) $hints[] = 'Compare your answer with the teacher answer and repeat it loudly once.';
    return implode(' ', array_unique($hints));
}


function material_answer_tokens(string $value): array
{
    $norm = normalize_material_answer($value);
    if ($norm === '') return [];
    return preg_split('/\s+/u', $norm) ?: [];
}

function material_sentence_form(array $tokens): string
{
    $first = $tokens[0] ?? '';
    $second = $tokens[1] ?? '';
    $questionStarts = ['am','is','are','was','were','do','does','did','can','could','should','would','will','have','has','had','may','might','shall'];
    $whStarts = ['what','why','when','where','who','whom','whose','which','how'];
    if (in_array($first, $whStarts, true)) return 'wh_question';
    if (in_array($first, $questionStarts, true)) return 'yes_no_question';
    if (($tokens[1] ?? '') === 'not' || in_array("n't", $tokens, true) || in_array('never', $tokens, true)) return 'negative';
    return 'statement';
}

function material_strict_structure_match(string $answer, string $possible, string $direction): bool
{
    if ($direction === 'english_to_hindi') {
        return true;
    }
    $a = material_answer_tokens($answer);
    $b = material_answer_tokens($possible);
    if (!$a || !$b) return false;

    if (material_sentence_form($a) !== material_sentence_form($b)) {
        return false;
    }

    $aux = ['am','is','are','was','were','do','does','did','can','could','should','would','will','have','has','had','may','might','shall','must'];
    $aFirstAux = in_array($a[0] ?? '', $aux, true);
    $bFirstAux = in_array($b[0] ?? '', $aux, true);
    if ($aFirstAux !== $bFirstAux) {
        return false;
    }

    $coreWords = array_values(array_filter($b, fn($w) => !in_array($w, ['a','an','the','to'], true)));
    if (count($coreWords) <= 5) {
        if (count($a) !== count($b)) {
            return false;
        }
        for ($i = 0; $i < min(count($a), count($b)); $i++) {
            if ($a[$i] !== $b[$i]) {
                return false;
            }
        }
    } else {
        $requiredPrefix = min(3, count($b));
        for ($i = 0; $i < $requiredPrefix; $i++) {
            if (($a[$i] ?? '') !== ($b[$i] ?? '')) {
                return false;
            }
        }
    }
    return true;
}


function material_strict_exact_normalize(string $value): string
{
    $v = strtolower(trim($value));
    $v = preg_replace('/[’`]/u', "'", $v);
    $v = preg_replace('/\b(i m)\b/u', "i am", $v);
    $v = preg_replace('/\b(im)\b/u', "i am", $v);
    $v = preg_replace('/\b(cant)\b/u', "cannot", $v);
    $v = preg_replace('/\b(dont)\b/u', "do not", $v);
    $v = preg_replace('/\b(doesnt)\b/u', "does not", $v);
    $v = preg_replace('/\b(didnt)\b/u', "did not", $v);
    $v = preg_replace('/\b(wont)\b/u', "will not", $v);
    $v = preg_replace('/\b(isnt)\b/u', "is not", $v);
    $v = preg_replace('/\b(arent)\b/u', "are not", $v);
    $v = preg_replace('/\b(wasnt)\b/u', "was not", $v);
    $v = preg_replace('/\b(werent)\b/u', "were not", $v);
    $v = preg_replace('/\b(hasnt)\b/u', "has not", $v);
    $v = preg_replace('/\b(havent)\b/u', "have not", $v);
    $v = preg_replace('/\b(hadnt)\b/u', "had not", $v);
    $v = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $v);
    $v = preg_replace('/\s+/u', ' ', $v);
    return trim($v);
}

function material_strict_tense_signature(string $value): string
{
    $v = ' ' . material_strict_exact_normalize($value) . ' ';
    if (preg_match('/\b(will|shall|going to)\b/u', $v)) return 'future';
    if (preg_match('/\b(was|were|did|had)\b/u', $v)) return 'past';
    if (preg_match('/\b(is|am|are|do|does|has|have)\b/u', $v)) return 'present';
    if (preg_match('/\b\w+ed\b/u', $v)) return 'past';
    return 'simple';
}

function material_strict_form_signature(string $value): string
{
    $tokens = material_answer_tokens($value);
    if (!$tokens) return 'blank';
    return material_sentence_form($tokens);
}

function material_is_exact_accepted_answer(string $answer, string $possible, string $direction): bool
{
    if ($direction === 'english_to_hindi') {
        return material_strict_exact_normalize($answer) === material_strict_exact_normalize($possible);
    }

    $a = material_strict_exact_normalize($answer);
    $p = material_strict_exact_normalize($possible);
    if ($a === '' || $p === '') return false;
    if ($a !== $p) return false;

    if (material_strict_tense_signature($answer) !== material_strict_tense_signature($possible)) return false;
    if (material_strict_form_signature($answer) !== material_strict_form_signature($possible)) return false;

    return true;
}

function evaluate_translation_pair(array $pair, string $answer, string $direction): array
{
    $correct = $direction === 'english_to_hindi' ? (string)$pair['hindi_text'] : (string)$pair['english_text'];
    $variants = material_answer_variants($pair, $direction);

    $isCorrect = false;
    $best = 0;
    $matchType = 'strict_no_match';

    foreach ($variants as $possible) {
        if (material_is_exact_accepted_answer($answer, $possible, $direction)) {
            $isCorrect = true;
            $best = 100;
            $matchType = 'exact_teacher_answer';
            break;
        }

        $score = answer_similarity_score($answer, $possible);
        $best = max($best, min(80, $score));
    }

    $score = $isCorrect ? 10 : ($best >= 80 ? 5 : ($best >= 55 ? 3 : 0));
    $ruleTip = material_teacher_feedback_rules($answer, $correct, $pair, $direction);

    if ($isCorrect) {
        $feedback = 'Correct.';
    } else {
        $feedback = 'Wrong answer. Use the same tense, same order and same sentence pattern. Speak with me: ' . $correct . ' ' . $ruleTip;
    }

    return [
        'is_correct' => $isCorrect,
        'score' => $score,
        'match_type' => $matchType,
        'correct_answer' => $correct,
        'accepted_answers' => $variants,
        'feedback' => $feedback,
        'explanation' => (string)($pair['explanation'] ?? ''),
        'next_step' => $direction === 'english_to_hindi' ? 'Repeat the correct Hindi meaning.' : 'Repeat the exact correct English sentence.'
    ];
}

function save_material_attempt(int $pairId, string $direction, string $answer, array $result): void
{
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $studentId = current_student_id();
        if (column_exists('material_practice_attempts', 'student_id') && column_exists('material_practice_attempts', 'is_correct')) {
            $stmt = db()->prepare('INSERT INTO material_practice_attempts (session_id, student_id, pair_id, practice_direction, user_answer, correct_answer, score, feedback, is_correct, match_type) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([session_id(), $studentId, $pairId, $direction, $answer, $result['correct_answer'] ?? '', (int)($result['score'] ?? 0), $result['feedback'] ?? '', !empty($result['is_correct']) ? 1 : 0, $result['match_type'] ?? '']);
        } elseif (column_exists('material_practice_attempts', 'is_correct')) {
            $stmt = db()->prepare('INSERT INTO material_practice_attempts (session_id, pair_id, practice_direction, user_answer, correct_answer, score, feedback, is_correct, match_type) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([session_id(), $pairId, $direction, $answer, $result['correct_answer'] ?? '', (int)($result['score'] ?? 0), $result['feedback'] ?? '', !empty($result['is_correct']) ? 1 : 0, $result['match_type'] ?? '']);
        } else {
            $stmt = db()->prepare('INSERT INTO material_practice_attempts (session_id, pair_id, practice_direction, user_answer, correct_answer, score, feedback) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([session_id(), $pairId, $direction, $answer, $result['correct_answer'] ?? '', (int)($result['score'] ?? 0), $result['feedback'] ?? '']);
        }
        if ($studentId > 0 && function_exists('student_progress_touch')) {
            student_progress_touch($studentId, 'practice', (int)($result['score'] ?? 0), !empty($result['is_correct']));
        }
    } catch (Throwable $e) {}
}

function material_attempt_summary(): array
{
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $studentId = current_student_id();
        if ($studentId > 0 && column_exists('material_practice_attempts', 'student_id')) {
            $stmt = db()->prepare('SELECT COUNT(*) total, COALESCE(SUM(score),0) score FROM material_practice_attempts WHERE student_id=? AND DATE(created_at)=CURDATE()');
            $stmt->execute([$studentId]);
        } else {
            $stmt = db()->prepare('SELECT COUNT(*) total, COALESCE(SUM(score),0) score FROM material_practice_attempts WHERE session_id=? AND DATE(created_at)=CURDATE()');
            $stmt->execute([session_id()]);
        }
        $row = $stmt->fetch() ?: ['total'=>0,'score'=>0];
        return ['total'=>(int)$row['total'], 'score'=>(int)$row['score']];
    } catch (Throwable $e) { return ['total'=>0, 'score'=>0]; }
}

function student_progress_touch(int $studentId, string $activityType = 'practice', int $score = 0, bool $isCorrect = false): void
{
    try {
        ensure_schema_updates();
        $title = $activityType === 'test' ? 'Weekly Test' : 'Auto practice session';
        // Log only meaningful attempts. Keep it lightweight, so dashboard can build streaks.
        $stmt = db()->prepare('INSERT INTO student_activity_logs (student_id, activity_type, activity_title, score, note) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$studentId, $activityType, $title, max(0, min(100, $score * 10)), $isCorrect ? 'Correct practice answer' : 'Needs revision']);
    } catch (Throwable $e) {}
}

function student_learning_metrics(int $studentId): array
{
    ensure_schema_updates();
    material_ensure_schema();
    weekly_test_ensure_schema();
    $out = [
        'practice_total' => 0,
        'practice_today' => 0,
        'correct_today' => 0,
        'wrong_total' => 0,
        'correct_rate' => 0,
        'streak_days' => 0,
        'weekly_attempts' => 0,
        'weekly_checked' => 0,
        'weekly_pending' => 0,
        'last_practice_date' => '',
    ];
    try {
        $useStudentColumn = column_exists('material_practice_attempts', 'student_id');
        if ($useStudentColumn) {
            $stmt = db()->prepare("SELECT COUNT(*) total, SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) today_count, SUM(CASE WHEN DATE(created_at)=CURDATE() AND is_correct=1 THEN 1 ELSE 0 END) correct_today, SUM(CASE WHEN is_correct=0 THEN 1 ELSE 0 END) wrong_total, SUM(CASE WHEN is_correct=1 THEN 1 ELSE 0 END) correct_total, MAX(created_at) last_date FROM material_practice_attempts WHERE student_id=?");
            $stmt->execute([$studentId]);
            $row = $stmt->fetch() ?: [];
            $out['practice_total'] = (int)($row['total'] ?? 0);
            $out['practice_today'] = (int)($row['today_count'] ?? 0);
            $out['correct_today'] = (int)($row['correct_today'] ?? 0);
            $out['wrong_total'] = (int)($row['wrong_total'] ?? 0);
            $correctTotal = (int)($row['correct_total'] ?? 0);
            $out['correct_rate'] = $out['practice_total'] > 0 ? (int)round(($correctTotal / $out['practice_total']) * 100) : 0;
            $out['last_practice_date'] = (string)($row['last_date'] ?? '');
            $stmt = db()->prepare("SELECT DATE(created_at) d FROM material_practice_attempts WHERE student_id=? GROUP BY DATE(created_at) ORDER BY d DESC LIMIT 45");
            $stmt->execute([$studentId]);
            $days = array_map(fn($r)=>(string)$r['d'], $stmt->fetchAll());
        } else {
            $days = [];
        }
        $today = new DateTimeImmutable('today');
        $streak = 0;
        for ($i=0; $i<45; $i++) {
            $d = $today->modify('-'.$i.' days')->format('Y-m-d');
            if (in_array($d, $days, true)) $streak++; else break;
        }
        $out['streak_days'] = $streak;
        $stmt = db()->prepare("SELECT COUNT(*) total, SUM(CASE WHEN status='checked' THEN 1 ELSE 0 END) checked_count, SUM(CASE WHEN status!='checked' THEN 1 ELSE 0 END) pending_count FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0 AND student_id=?");
        $stmt->execute([$studentId]);
        $wr = $stmt->fetch() ?: [];
        $out['weekly_attempts'] = (int)($wr['total'] ?? 0);
        $out['weekly_checked'] = (int)($wr['checked_count'] ?? 0);
        $out['weekly_pending'] = (int)($wr['pending_count'] ?? 0);
    } catch (Throwable $e) {}
    return $out;
}

function student_wrong_material_attempts(int $studentId, int $limit = 10): array
{
    try {
        material_ensure_schema();
        if (!column_exists('material_practice_attempts', 'student_id')) return [];
        $stmt = db()->prepare("SELECT a.*, p.hindi_text, p.english_text, p.tense_name, p.level, p.situation_tag FROM material_practice_attempts a JOIN translation_pairs p ON p.id=a.pair_id WHERE a.student_id=? AND a.is_correct=0 ORDER BY a.id DESC LIMIT " . (int)$limit);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) { return []; }
}

function student_recent_material_attempts(int $studentId, int $limit = 12): array
{
    try {
        material_ensure_schema();
        if (!column_exists('material_practice_attempts', 'student_id')) return [];
        $stmt = db()->prepare("SELECT a.*, p.hindi_text, p.english_text, p.tense_name, p.level FROM material_practice_attempts a JOIN translation_pairs p ON p.id=a.pair_id WHERE a.student_id=? AND COALESCE(a.status_deleted,0)=0 ORDER BY a.id DESC LIMIT " . (int)$limit);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) { return []; }
}

function student_level_progress_percent(string $level, array $metrics): int
{
    $base = ['Zero Level'=>5, 'Basic'=>25, 'Intermediate'=>55, 'Advanced'=>78];
    $b = $base[$level] ?? 10;
    $extra = min(20, (int)floor(($metrics['practice_total'] ?? 0) / 20));
    $rate = (int)round((($metrics['correct_rate'] ?? 0) / 100) * 10);
    return min(100, $b + $extra + $rate);
}

function practice_normalize_text(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
    $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return function_exists('mb_strtolower') ? mb_strtolower(trim($text), 'UTF-8') : strtolower(trim($text));
}

function find_translation_pair_for_practice(string $column, string $input): ?array
{
    $input = trim($input);
    if ($input === '' || !in_array($column, ['hindi_text', 'english_text'], true)) {
        return null;
    }

    $stmt = db()->prepare("SELECT * FROM translation_pairs WHERE published='Yes' AND status_deleted=0 AND {$column} = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$input]);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }

    $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $input) . '%';
    $stmt = db()->prepare("SELECT * FROM translation_pairs WHERE published='Yes' AND status_deleted=0 AND {$column} LIKE ? ORDER BY id DESC LIMIT 25");
    $stmt->execute([$like]);
    foreach ($stmt->fetchAll() as $row) {
        return $row;
    }

    $target = practice_normalize_text($input);
    if ($target === '') {
        return null;
    }
    $stmt = db()->query("SELECT * FROM translation_pairs WHERE published='Yes' AND status_deleted=0 ORDER BY id DESC LIMIT 1500");
    foreach ($stmt->fetchAll() as $row) {
        $candidate = practice_normalize_text((string)$row[$column]);
        if ($candidate === $target || str_contains($candidate, $target) || str_contains($target, $candidate)) {
            return $row;
        }
    }
    return null;
}

function practice_local_dictionary(): array
{
    return [
        'hindi_to_english_phrases' => [
            'मैं रोज अंग्रेजी बोलता हूँ' => 'I speak English every day.',
            'मैं रोज अंग्रेजी बोलती हूँ' => 'I speak English every day.',
            'मैं अंग्रेजी सीखना चाहता हूँ' => 'I want to learn English.',
            'मैं अंग्रेजी सीखना चाहती हूँ' => 'I want to learn English.',
            'मेरा नाम' => 'My name is',
            'आप कैसे हैं' => 'How are you?',
            'मैं ठीक हूँ' => 'I am fine.',
            'मुझे पानी चाहिए' => 'I want water.',
            'मुझे जाना है' => 'I have to go.',
            'मैं स्कूल जाता हूँ' => 'I go to school.',
            'मैं बाजार जाता हूँ' => 'I go to the market.',
            'वह स्कूल जाता है' => 'He goes to school.',
            'वह खुश है' => 'He is happy.',
            'तुम तैयार हो' => 'You are ready.',
            'मैं नहीं जानता' => 'I do not know.',
            'आज मौसम अच्छा है' => 'The weather is good today.',
            'मुझे आपकी मदद चाहिए' => 'I need your help.',
            'कृपया मेरी मदद करें' => 'Please help me.',
            'आपका नाम क्या है' => 'What is your name?',
            'आप कहाँ जा रहे हैं' => 'Where are you going?',
            'मैं घर जा रहा हूँ' => 'I am going home.',
            'मैं घर जा रही हूँ' => 'I am going home.',
            'मैं पढ़ रहा हूँ' => 'I am studying.',
            'मैं पढ़ रही हूँ' => 'I am studying.',
            'मुझे अंग्रेजी बोलनी है' => 'I have to speak English.',
            'आप कौन सी कार चला सकते हैं' => 'Which car can you drive?',
            'आप कौन सा काम कर सकते हैं' => 'Which work can you do?',
            'क्या आप कार चला सकते हैं' => 'Can you drive a car?',
            'क्या आप अंग्रेजी बोल सकते हैं' => 'Can you speak English?',
        ],
        'english_to_hindi_phrases' => [
            'i speak english every day' => 'मैं रोज अंग्रेजी बोलता/बोलती हूँ।',
            'i want to learn english' => 'मैं अंग्रेजी सीखना चाहता/चाहती हूँ।',
            'my name is' => 'मेरा नाम है।',
            'how are you' => 'आप कैसे हैं?',
            'i am fine' => 'मैं ठीक हूँ।',
            'i want water' => 'मुझे पानी चाहिए।',
            'i have to go' => 'मुझे जाना है।',
            'i go to school' => 'मैं स्कूल जाता/जाती हूँ।',
            'i go to the market' => 'मैं बाजार जाता/जाती हूँ।',
            'he goes to school' => 'वह स्कूल जाता है।',
            'she goes to school' => 'वह स्कूल जाती है।',
            'you are ready' => 'तुम तैयार हो।',
            'i do not know' => 'मैं नहीं जानता/जानती।',
            'please help me' => 'कृपया मेरी मदद करें।',
            'what is your name' => 'आपका नाम क्या है?',
            'where are you going' => 'आप कहाँ जा रहे हैं?',
            'i am going home' => 'मैं घर जा रहा/रही हूँ।',
            'i am going to the market' => 'मैं बाजार जा रहा/रही हूँ।',
            'am i going to the market' => 'क्या मैं बाजार जा रहा/रही हूँ?',
            'i can go to india' => 'मैं भारत जा सकता/सकती हूँ।',
            'i have to go to delhi' => 'मुझे दिल्ली जाना है।',
            'i am studying' => 'मैं पढ़ रहा/रही हूँ।',
            'which car can you drive' => 'आप कौन सी कार चला सकते हैं?',
            'can you drive a car' => 'क्या आप कार चला सकते हैं?',
            'can you speak english' => 'क्या आप अंग्रेजी बोल सकते हैं?',
            'which work can you do' => 'आप कौन सा काम कर सकते हैं?',
            'what can you do' => 'आप क्या कर सकते हैं?',
        ],
        'hi_words' => [
            'मैं'=>'I','मुझे'=>'I need','मेरा'=>'my','मेरी'=>'my','नाम'=>'name','हूँ'=>'am','है'=>'is','हो'=>'are','हैं'=>'are','आप'=>'you','तुम'=>'you','वह'=>'he/she','यह'=>'this','हम'=>'we','वे'=>'they','रोज'=>'daily','हर'=>'every','दिन'=>'day','सुबह'=>'morning','शाम'=>'evening','जल्दी'=>'early','देर'=>'late','अंग्रेजी'=>'English','बोलता'=>'speak','बोलती'=>'speak','बोल'=>'speak','बोलना'=>'speak','बोलनी'=>'speak','सकते'=>'can','सकती'=>'can','सकता'=>'can','सी'=>'which','सा'=>'which','कौन'=>'which','चला'=>'drive','चलाना'=>'drive','कार'=>'car','गाड़ी'=>'car','सीखना'=>'learn','चाहता'=>'want','चाहती'=>'want','चाहिए'=>'want','जाना'=>'go','जाता'=>'goes','जाती'=>'goes','जा'=>'go','रहा'=>'going','रही'=>'going','घर'=>'home','स्कूल'=>'school','बाजार'=>'market','पानी'=>'water','खाना'=>'food','मदद'=>'help','कृपया'=>'please','ठीक'=>'fine','खुश'=>'happy','तैयार'=>'ready','क्या'=>'what','कहाँ'=>'where','कब'=>'when','क्यों'=>'why','कैसे'=>'how','आज'=>'today','कल'=>'tomorrow/yesterday','अच्छा'=>'good','मौसम'=>'weather','पढ़'=>'study','पढ़ना'=>'study','काम'=>'work','करता'=>'do','करती'=>'do','करना'=>'do','कर'=>'do','नहीं'=>'not'
        ],
        'en_words' => [
            'i'=>'मैं','you'=>'आप/तुम','he'=>'वह','she'=>'वह','we'=>'हम','they'=>'वे','my'=>'मेरा/मेरी','name'=>'नाम','am'=>'हूँ','is'=>'है','are'=>'हैं','fine'=>'ठीक','happy'=>'खुश','ready'=>'तैयार','speak'=>'बोलना','speaks'=>'बोलता/बोलती है','english'=>'अंग्रेजी','learn'=>'सीखना','want'=>'चाहना','need'=>'जरूरत होना','go'=>'जाना','goes'=>'जाता/जाती है','going'=>'जा रहा/रही','home'=>'घर','school'=>'स्कूल','market'=>'बाजार','water'=>'पानी','food'=>'खाना','help'=>'मदद','please'=>'कृपया','what'=>'क्या','where'=>'कहाँ','when'=>'कब','why'=>'क्यों','how'=>'कैसे','today'=>'आज','tomorrow'=>'कल','good'=>'अच्छा','weather'=>'मौसम','study'=>'पढ़ना','work'=>'काम','daily'=>'रोज','every'=>'हर','day'=>'दिन','morning'=>'सुबह','early'=>'जल्दी','not'=>'नहीं','do'=>'करना','can'=>'सकना','which'=>'कौन सा/सी','car'=>'कार','drive'=>'चलाना','drives'=>'चलाता/चलाती है','driving'=>'चला रहा/रही','a'=>'एक','the'=>'वह/यह'
        ],
        'verb_hi' => [
            'drive'=>'चला','speak'=>'बोल','learn'=>'सीख','read'=>'पढ़','write'=>'लिख','go'=>'जा','come'=>'आ','eat'=>'खा','drink'=>'पी','do'=>'कर','make'=>'बना','help'=>'मदद कर','study'=>'पढ़','work'=>'काम कर','play'=>'खेल','buy'=>'खरीद','sell'=>'बेच','open'=>'खोल','close'=>'बंद कर','understand'=>'समझ','teach'=>'पढ़ा','call'=>'फोन कर','meet'=>'मिल'
        ],
        'noun_hi' => [
            'car'=>'कार','bike'=>'बाइक','school'=>'स्कूल','english'=>'अंग्रेजी','work'=>'काम','job'=>'नौकरी','book'=>'किताब','water'=>'पानी','food'=>'खाना','market'=>'बाजार','home'=>'घर','phone'=>'फोन','class'=>'क्लास','lesson'=>'लेसन','sentence'=>'वाक्य'
        ]
    ];
}

function practice_has_hindi(string $text): bool
{
    return (bool)preg_match('/\p{Devanagari}/u', $text);
}

function practice_clean_sentence(string $text): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    return trim($text, " \t\n\r\0\x0B।.!?");
}

function practice_fix_common_spelling(string $text): string
{
    $map = [
        'markat'=>'market','marget'=>'market','delhii'=>'Delhi','delhiii'=>'Delhi','delhi'=>'Delhi','india'=>'India','bharat'=>'India',
        'englis'=>'English','englesh'=>'English','englsh'=>'English','englishs'=>'English',
        'grammer'=>'grammar','sentance'=>'sentence','leran'=>'learn','lern'=>'learn','speek'=>'speak','spok'=>'spoke','drve'=>'drive',
        'studey'=>'study','techer'=>'teacher','recieve'=>'receive','becoz'=>'because','bcz'=>'because','pls'=>'please','plz'=>'please'
    ];
    return preg_replace_callback('/\b[a-z]+\b/i', function($m) use ($map) {
        $w = strtolower($m[0]);
        $fixed = $map[$w] ?? $m[0];
        if ($fixed === 'English' || $fixed === 'Delhi' || $fixed === 'India') return $fixed;
        return $fixed;
    }, $text);
}



function practice_apply_english_to_hindi_patterns(string $norm, array $dict): ?string
{
    $norm = trim(practice_normalize_text(practice_fix_common_spelling($norm)));
    $norm = preg_replace('/\s+/u', ' ', $norm);
    $norm = str_replace(['new delhi'], ['delhi'], $norm);
    $norm = preg_replace('/\b(go|come|travel|went|going)\s+(india|delhi|market|school|home)\b/i', '$1 to $2', $norm);
    $norm = preg_replace('/\b(to\s+home)\b/i', 'home', $norm);

    foreach ($dict['english_to_hindi_phrases'] as $en => $hi) {
        $key = practice_normalize_text($en);
        if ($norm === $key || str_contains($norm, $key)) return $hi;
    }

    $places = ['india'=>'भारत','delhi'=>'दिल्ली','market'=>'बाजार','school'=>'स्कूल','home'=>'घर','class'=>'क्लास','office'=>'ऑफिस','college'=>'कॉलेज'];
    $objects = array_merge($dict['noun_hi'], $places, ['english'=>'अंग्रेजी','hindi'=>'हिंदी']);
    $verbs = array_merge($dict['verb_hi'], ['go'=>'जा','come'=>'आ','visit'=>'जा','travel'=>'यात्रा कर','read'=>'पढ़','write'=>'लिख','study'=>'पढ़','watch'=>'देख','meet'=>'मिल','ask'=>'पूछ','answer'=>'जवाब दे']);
    $toObj = function(string $text) use ($objects, $dict): string {
        $text = trim(preg_replace('/\b(a|an|the|to|in|at|on)\b/i', ' ', $text));
        return practice_words_to_hindi($text, ['en_words'=>array_merge($dict['en_words'],$objects), 'noun_hi'=>$objects, 'verb_hi'=>$dict['verb_hi']], false);
    };

    if (preg_match('/^which\s+([a-z]+)\s+can\s+you\s+([a-z]+)$/i', $norm, $m)) {
        $noun = $objects[$m[1]] ?? $m[1];
        $verb = $verbs[$m[2]] ?? $m[2];
        $selector = in_array($m[1], ['car','bike','book','class'], true) ? 'कौन सी' : 'कौन सा';
        return 'आप ' . $selector . ' ' . $noun . ' ' . $verb . ' सकते हैं?';
    }
    if (preg_match('/^what\s+can\s+you\s+([a-z]+)$/i', $norm, $m)) {
        $verb = $verbs[$m[1]] ?? $m[1];
        return 'आप क्या ' . $verb . ' सकते हैं?';
    }
    if (preg_match('/^can\s+you\s+([a-z]+)(?:\s+(?:a|the|an|to))?\s*(.*)$/i', $norm, $m)) {
        $verb = $verbs[$m[1]] ?? $m[1];
        $obj = $toObj($m[2] ?? '');
        return 'क्या आप' . ($obj !== '' ? ' ' . $obj : '') . ' ' . $verb . ' सकते हैं?';
    }
    if (preg_match('/^am\s+i\s+going\s+(?:to\s+)?(.*)$/i', $norm, $m)) {
        $obj = $toObj($m[1] ?? '');
        return 'क्या मैं' . ($obj !== '' ? ' ' . $obj : '') . ' जा रहा/रही हूँ?';
    }
    if (preg_match('/^i\s+am\s+going\s+(?:to\s+)?(.*)$/i', $norm, $m)) {
        $obj = $toObj($m[1] ?? '');
        return 'मैं' . ($obj !== '' ? ' ' . $obj : '') . ' जा रहा/रही हूँ।';
    }
    if (preg_match('/^i\s+can\s+([a-z]+)(?:\s+(.*))?$/i', $norm, $m)) {
        $verb = $verbs[$m[1]] ?? $m[1];
        $obj = $toObj($m[2] ?? '');
        return 'मैं' . ($obj !== '' ? ' ' . $obj : '') . ' ' . $verb . ' सकता/सकती हूँ।';
    }
    if (preg_match('/^i\s+have\s+to\s+([a-z]+)(?:\s+(.*))?$/i', $norm, $m)) {
        $verb = $verbs[$m[1]] ?? $m[1];
        $obj = $toObj($m[2] ?? '');
        return 'मुझे' . ($obj !== '' ? ' ' . $obj : '') . ' ' . $verb . 'ना है।';
    }
    if (preg_match('/^i\s+want\s+to\s+([a-z]+)(?:\s+(.*))?$/i', $norm, $m)) {
        $verb = $verbs[$m[1]] ?? $m[1];
        $obj = $toObj($m[2] ?? '');
        return 'मैं' . ($obj !== '' ? ' ' . $obj : '') . ' ' . $verb . 'ना चाहता/चाहती हूँ।';
    }
    if (preg_match('/^i\s+(go|come|travel|study|speak|read|write|drive|work)(?:\s+(.*))?$/i', $norm, $m)) {
        $verbRoot = $verbs[$m[1]] ?? $m[1];
        $obj = $toObj($m[2] ?? '');
        if ($m[1] === 'go' || $m[1] === 'come' || $m[1] === 'travel') return 'मैं' . ($obj !== '' ? ' ' . $obj : '') . ' जाता/जाती हूँ।';
        if ($m[1] === 'speak') return 'मैं' . ($obj !== '' ? ' ' . $obj : ' अंग्रेजी') . ' बोलता/बोलती हूँ।';
        return 'मैं' . ($obj !== '' ? ' ' . $obj : '') . ' ' . $verbRoot . 'ता/ती हूँ।';
    }
    return null;
}



function practice_words_to_hindi(string $text, array $dict, bool $markUnknown = true): string
{
    $words = preg_split('/\s+/u', practice_normalize_text(practice_fix_common_spelling($text)), -1, PREG_SPLIT_NO_EMPTY);
    $skip = ['a','an','the','to'];
    $out = [];
    foreach ($words as $word) {
        $word = trim($word, ".,!?;:\"'()[]{} ");
        if ($word === '' || in_array($word, $skip, true)) continue;
        $out[] = $dict['noun_hi'][$word] ?? $dict['verb_hi'][$word] ?? $dict['en_words'][$word] ?? ($markUnknown ? '[' . $word . ']' : $word);
    }
    return trim(implode(' ', $out));
}

function practice_auto_translate_local(string $mode, string $input): array
{
    $dict = practice_local_dictionary();
    $clean = practice_clean_sentence($input);
    $clean = practice_fix_common_spelling($clean);
    $norm = practice_normalize_text($clean);

    if ($mode === 'hindi_to_english') {
        foreach ($dict['hindi_to_english_phrases'] as $hi => $en) {
            $needle = practice_normalize_text($hi);
            if ($needle !== '' && ($norm === $needle || str_contains($norm, $needle))) {
                return ['answer' => $en, 'confidence' => 'High', 'source' => 'local_phrase', 'natural' => true];
            }
        }
        if (preg_match('/^मैं\s+जा\s+सकता\s+हूँ$/u', $clean) || preg_match('/^मैं\s+जा\s+सकती\s+हूँ$/u', $clean)) {
            return ['answer'=>'I can go.','confidence'=>'High','source'=>'local_pattern','natural'=>true];
        }
        if (preg_match('/मैं\s+([\p{Devanagari}]+)\s+जा\s+सकता/u', $clean, $m) || preg_match('/मैं\s+([\p{Devanagari}]+)\s+जा\s+सकती/u', $clean, $m)) {
            $placeMap = ['भारत'=>'India','इंडिया'=>'India','दिल्ली'=>'Delhi','बाजार'=>'the market','स्कूल'=>'school','घर'=>'home'];
            $place = $placeMap[$m[1]] ?? ($dict['hi_words'][$m[1]] ?? $m[1]);
            return ['answer'=>'I can go to ' . $place . '.','confidence'=>'High','source'=>'local_pattern','natural'=>true];
        }
        if (preg_match('/आप\s+कौन\s+(सी|सा)\s+([\p{Devanagari}]+)\s+([\p{Devanagari}]+)\s+सकते\s+हैं/u', $clean, $m)) {
            $noun = array_search($m[2], $dict['noun_hi'], true) ?: ($dict['hi_words'][$m[2]] ?? $m[2]);
            $verb = array_search($m[3], $dict['verb_hi'], true) ?: ($dict['hi_words'][$m[3]] ?? $m[3]);
            return ['answer' => 'Which ' . $noun . ' can you ' . $verb . '?', 'confidence' => 'High', 'source' => 'local_pattern', 'natural' => true];
        }
        if (preg_match('/क्या\s+आप\s+([\p{Devanagari}]+)?\s*([\p{Devanagari}]+)\s+सकते\s+हैं/u', $clean, $m)) {
            $noun = trim($m[1] ?? '');
            $verb = trim($m[2] ?? '');
            $nounEn = $noun !== '' ? (array_search($noun, $dict['noun_hi'], true) ?: ($dict['hi_words'][$noun] ?? $noun)) . ' ' : '';
            $verbEn = array_search($verb, $dict['verb_hi'], true) ?: ($dict['hi_words'][$verb] ?? $verb);
            return ['answer' => 'Can you ' . $verbEn . ' ' . trim($nounEn) . '?', 'confidence' => 'High', 'source' => 'local_pattern', 'natural' => true];
        }
        $tokens = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($tokens as $token) {
            $key = trim($token, "।,.!?;:\"'()[]{} ");
            if ($key === '') continue;
            $out[] = $dict['hi_words'][$key] ?? '[' . $key . ']';
        }
        $answer = trim(implode(' ', $out));
        if ($answer !== '') {
            $answer = ucfirst($answer);
            if (!preg_match('/[.!?]$/', $answer)) $answer .= '.';
        }
        return ['answer' => $answer ?: 'Translation needs online API for this sentence.', 'confidence' => 'Review', 'source' => 'local_dictionary', 'natural' => false];
    }

    $pattern = practice_apply_english_to_hindi_patterns($norm, $dict);
    if ($pattern !== null) return ['answer' => $pattern, 'confidence' => 'High', 'source' => 'local_pattern', 'natural' => true];

    $answer = practice_words_to_hindi($norm, $dict, false);
    if ($answer !== '' && !preg_match('/[।!?]$/u', $answer)) $answer .= '।';
    return ['answer' => $answer ?: 'इस वाक्य के लिए सही अनुवाद के लिए Translation API जोड़ें।', 'confidence' => 'Review', 'source' => 'local_dictionary', 'natural' => false];
}



function practice_strip_teacher_command(string $text): string
{
    $text = trim($text);
    $text = preg_replace('/^\s*(correct\s+(this\s+)?sentence|please\s+correct|grammar\s+check)\s*[:\-]?\s*/iu', '', $text);
    $text = preg_replace('/^\s*(translate\s+this|translate)\s*[:\-]?\s*/iu', '', $text);
    return trim($text);
}

function practice_auto_correct_local(string $input): array
{
    $original = trim(practice_strip_teacher_command($input));
    $text = ' ' . strtolower(practice_fix_common_spelling($original)) . ' ';
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = preg_replace('/\bgo\s+(india|delhi|market|school|office|college)\b/i', 'go to $1', $text);
    $text = preg_replace('/\bgoing\s+(india|delhi|market|school|office|college)\b/i', 'going to $1', $text);
    $text = preg_replace('/\bwent\s+(india|delhi|market|school|office|college)\b/i', 'went to $1', $text);
    $replacements = [
        ' i goes ' => ' I go ', ' i go to market ' => ' I go to the market ', ' i go to india ' => ' I go to India ', ' i go to delhi ' => ' I go to Delhi ',
        ' i can go india ' => ' I can go to India ', ' i can go delhi ' => ' I can go to Delhi ', ' i have to go delhi ' => ' I have to go to Delhi ',
        ' am i going to market ' => ' Am I going to the market ',
        ' am i going market ' => ' Am I going to the market ',
        ' i am going market ' => ' I am going to the market ',
        ' i have to go india ' => ' I have to go to India ', ' i drive car ' => ' I drive a car ',
        ' i am go ' => ' I am going ', ' i am going to market ' => ' I am going to the market ',
        ' i has ' => ' I have ', ' i does ' => ' I do ', ' i did not went ' => ' I did not go ',
        ' he go ' => ' he goes ', ' she go ' => ' she goes ', ' it go ' => ' it goes ',
        ' he have ' => ' he has ', ' she have ' => ' she has ',
        ' you is ' => ' you are ', ' they is ' => ' they are ', ' we is ' => ' we are ',
        ' myself ' => ' my name is ', ' discuss about ' => ' discuss ',
        ' informations ' => ' information ', ' advices ' => ' advice ', ' equipments ' => ' equipment ',
    ];
    foreach ($replacements as $wrong => $right) $text = str_replace($wrong, $right, $text);
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    $text = preg_replace_callback('/(^|[.!?]\s+)([a-z])/', function($m){ return $m[1] . strtoupper($m[2]); }, $text);
    $text = preg_replace('/\bi\b/', 'I', $text);
    $text = preg_replace('/\benglish\b/i', 'English', $text);
    $text = preg_replace('/\bindia\b/i', 'India', $text);
    $text = preg_replace('/\bdelhi\b/i', 'Delhi', $text);
    $text = trim($text);
    if ($text !== '' && !preg_match('/[.!?]$/', $text)) $text .= (preg_match('/^(Which|What|Where|When|Why|How|Can|Do|Does|Did|Are|Is)\b/', $text) ? '?' : '.');
    $changed = practice_normalize_text($text) !== practice_normalize_text($original);
    $hint = $changed ? 'Corrected spelling, capitalization, article/preposition, or grammar pattern.' : 'No major grammar issue was detected. For a final review, ask your institute teacher.';
    return ['answer' => $text, 'changed' => $changed, 'note' => $hint];
}



function practice_ai_is_ready(): bool
{
    return practice_setting('ai_enabled', 'No') === 'Yes'
        && trim(practice_setting('openai_api_key', '')) !== ''
        && function_exists('curl_init');
}

function practice_ai_chat_text(string $systemPrompt, string $userPrompt, float $temperature = 0.15): ?string
{
    if (!practice_ai_is_ready()) return null;
    $apiKey = trim(practice_setting('openai_api_key', ''));
    $endpoint = trim(practice_setting('openai_endpoint', 'https://api.openai.com/v1/chat/completions'));
    $model = trim(practice_setting('openai_model', 'gpt-4o-mini')) ?: 'gpt-4o-mini';
    $timeout = max(8, min(45, (int)practice_setting('ai_timeout_seconds', '18')));
    $payload = [
        'model' => $model,
        'temperature' => $temperature,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ]
    ];
    try {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json','Authorization: Bearer ' . $apiKey],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $timeout
        ]);
        $response = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!is_string($response) || $http < 200 || $http >= 300) return null;
        $decoded = json_decode($response, true);
        $content = trim((string)($decoded['choices'][0]['message']['content'] ?? ''));
        return $content !== '' ? $content : null;
    } catch (Throwable $e) {
        return null;
    }
}

function practice_ai_quick_tool(string $mode, string $input): ?array
{
    $input = trim(practice_strip_teacher_command($input));
    if ($input === '') return null;
    $task = $mode === 'hindi_to_english' ? 'Translate the Hindi sentence into natural English.' : ($mode === 'english_to_hindi' ? 'Translate the English sentence into natural Hindi.' : 'Correct the English sentence and make it natural.');
    $system = 'You are a professional spoken English teacher for Indian learners. Return ONLY valid JSON. Never add markdown. Never translate word-by-word. If correcting English, keep the meaning and return natural correct English.';
    $prompt = $task . "\nInput: " . $input . "\nReturn JSON exactly with keys: title, answer, note, confidence. Confidence must be High if the answer is natural and safe. Note should be one short helpful line.";
    $raw = practice_ai_chat_text($system, $prompt, 0.1);
    if (!$raw) return null;
    $data = extract_json_object($raw);
    if (!is_array($data) || trim((string)($data['answer'] ?? '')) === '') return null;
    return [
        'title' => trim((string)($data['title'] ?? ($mode === 'sentence_correction' ? 'Sentence Correction' : ($mode === 'hindi_to_english' ? 'Hindi to English' : 'English to Hindi')))),
        'answer' => trim((string)$data['answer']),
        'note' => trim((string)($data['note'] ?? 'Checked by connected AI engine.')),
        'confidence' => trim((string)($data['confidence'] ?? 'High')),
        'source' => 'ai_api'
    ];
}

function practice_ai_teacher_answer(string $question): ?string
{
    $question = trim($question);
    if ($question === '') return null;
    $system = 'You are a friendly female spoken English teacher for Indian Hindi-speaking learners. Answer clearly and naturally. Correct wrong English when asked. For translation, give one natural translation only, not word-by-word. Keep answers short, practical, and student-friendly. Do not repeat command phrases like Correct this sentence.';
    $prompt = "Student question: " . $question . "\nGive the best teacher answer. If the student asks correction, provide: Correct sentence, Why, Practice. If translation, provide only natural translation plus one short tip.";
    return practice_ai_chat_text($system, $prompt, 0.2);
}


function free_ai_local_tool(string $mode, string $input): array
{
    material_ensure_schema();
    ensure_schema_updates();
    $input = trim($input);
    if ($input === '') {
        return ['title' => 'Write or speak something first', 'answer' => 'Please type or use the mic button, then click Practice Now.', 'note' => 'This tool now works with local auto logic and does not require admin-created sentence pairs.'];
    }

    try {
        $ai = practice_ai_quick_tool($mode, $input);
        if ($ai) return $ai;

        if ($mode === 'hindi_to_english') {
            $google = practice_google_translate_free('hindi_to_english', $input);
            if ($google) return $google;
            $local = practice_auto_translate_local('hindi_to_english', $input);
            if (($local['confidence'] ?? '') === 'High') {
                return [
                    'title' => 'Hindi to English',
                    'answer' => $local['answer'],
                    'note' => 'Matched by a safe built-in spoken-English pattern. Open sentences may require teacher review.', 'confidence' => $local['confidence'], 'source' => $local['source']
                ];
            }
            return ['title'=>'Teacher Review Required','answer'=>'This Hindi sentence needs teacher review or an approved translation service.','note'=>'To avoid wrong teaching, the app does not show weak dictionary guesses as a final translation.','confidence'=>'Needs review','source'=>'safe_block'];
        }
        if ($mode === 'english_to_hindi') {
            $google = practice_google_translate_free('english_to_hindi', $input);
            if ($google) return $google;
            $local = practice_auto_translate_local('english_to_hindi', $input);
            if (($local['confidence'] ?? '') === 'High') {
                return [
                    'title' => 'English to Hindi',
                    'answer' => $local['answer'],
                    'note' => 'Matched by a safe built-in spoken-English pattern. Open sentences may require teacher review.', 'confidence' => $local['confidence'], 'source' => $local['source']
                ];
            }
            return ['title'=>'Teacher Review Required','answer'=>'This English sentence needs teacher review or an approved translation service.','note'=>'To avoid wrong teaching, the app does not show weak word-by-word Hindi as a final answer.','confidence'=>'Needs review','source'=>'safe_block'];
        }

        $corrected = practice_auto_correct_local($input);
        if ($corrected['changed']) {
            return ['title' => 'Sentence Correction', 'answer' => $corrected['answer'], 'note' => $corrected['note']];
        }

        $normalized = practice_normalize_text($input);
        $stmt = db()->query("SELECT * FROM practice_common_mistakes WHERE published='Yes' AND status_deleted=0 ORDER BY id DESC LIMIT 300");
        foreach ($stmt->fetchAll() as $mistake) {
            $wrong = practice_normalize_text((string)$mistake['wrong_pattern']);
            if ($wrong !== '' && str_contains($normalized, $wrong)) {
                $fixed = str_ireplace($mistake['wrong_pattern'], $mistake['correct_pattern'], $input);
                $fixedLocal = practice_auto_correct_local($fixed);
                return ['title' => 'Sentence Correction', 'answer' => $fixedLocal['answer'], 'note' => trim(($mistake['explanation'] ?? '') . ' ' . ($mistake['example_sentence'] ?? ''))];
            }
        }
        return ['title' => 'Sentence Correction', 'answer' => $corrected['answer'], 'note' => $corrected['note']];
    } catch (Throwable $e) {
        if ($mode === 'hindi_to_english' || $mode === 'english_to_hindi') {
            $local = practice_auto_translate_local($mode, $input);
            return ['title' => $mode === 'hindi_to_english' ? 'Hindi to English' : 'English to Hindi', 'answer' => $local['answer'], 'note' => 'Local auto logic result. No database/admin dependency required.'];
        }
        $corrected = practice_auto_correct_local($input);
        return ['title' => 'Sentence Correction', 'answer' => $corrected['answer'], 'note' => 'Local auto correction result. No admin dependency required.'];
    }
}

function weekly_test_schema_status(): array
{
    $required = [
        'weekly_tests' => [
            'id','title','test_type','status','published','requires_login','duration_minutes','total_questions',
            'starts_at','ends_at','batch_id','instructions','shuffle_questions','shuffle_options','warning_limit',
            'penalty_after_warnings','penalty_per_warning','auto_submit_on_warning_limit'
        ],
        'weekly_test_questions' => [
            'id','test_id','question_type','question_text','expected_answer','option_a','option_b','option_c','option_d',
            'marks','sort_order','published','status_deleted'
        ],
        'weekly_test_attempts' => [
            'id','test_id','student_id','guest_name','guest_phone','canonical_phone','started_at','submitted_at','expires_at',
            'status','auto_score','admin_score','total_marks','penalty_marks','admin_note','warning_count','activity_log',
            'timing_log','suspicious_flag','access_token','result_token','question_snapshot','question_order',
            'submission_reason','last_saved_at','status_deleted'
        ],
        'weekly_test_answers' => ['id','attempt_id','question_id','answer_text','is_correct','marks_awarded','admin_note'],
    ];
    $missing = [];
    try {
        foreach ($required as $table => $columns) {
            if (!table_exists($table)) {
                $missing[] = $table;
                continue;
            }
            foreach ($columns as $column) {
                if (!column_exists($table, $column)) $missing[] = $table . '.' . $column;
            }
        }
    } catch (Throwable $e) {
        return ['ready' => false, 'missing' => ['database_connection'], 'message' => 'Database connection or schema check failed.'];
    }
    return [
        'ready' => $missing === [],
        'missing' => $missing,
        'message' => $missing === [] ? 'Weekly Test schema is ready.' : 'Weekly Test database upgrade is incomplete.',
    ];
}

function weekly_test_ensure_schema(): void
{
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    static $done = false;
    if ($done) return;
    $done = true;
    $weeklySchemaMarker = 'phase122_weekly_schema_v1';
    try {
        $markerStmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1');
        $markerStmt->execute(['weekly_schema_marker']);
        $markerReady = (string)($markerStmt->fetchColumn() ?: '') === $weeklySchemaMarker;
        $requiredColumns = ['access_token','result_token','question_snapshot','submission_reason','last_saved_at'];
        $columnsReady = true;
        foreach ($requiredColumns as $requiredColumn) {
            if (!column_exists('weekly_test_attempts', $requiredColumn)) { $columnsReady = false; break; }
        }
        if ($markerReady && $columnsReady) return;
    } catch (Throwable $e) {}
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS weekly_tests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            test_type VARCHAR(40) NOT NULL DEFAULT 'basic',
            instructions TEXT NULL,
            duration_minutes INT UNSIGNED NOT NULL DEFAULT 30,
            total_questions INT UNSIGNED NOT NULL DEFAULT 30,
            total_marks INT UNSIGNED NOT NULL DEFAULT 30,
            status VARCHAR(30) NOT NULL DEFAULT 'draft',
            requires_login ENUM('Yes','No') NOT NULL DEFAULT 'No',
            starts_at DATETIME NULL,
            ends_at DATETIME NULL,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_weekly_tests (test_type, status, published, status_deleted, starts_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS weekly_test_questions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            test_id INT UNSIGNED NOT NULL,
            question_type VARCHAR(60) NOT NULL DEFAULT 'hindi_to_english',
            topic_name VARCHAR(160) NULL,
            level VARCHAR(80) NULL,
            question_text TEXT NOT NULL,
            expected_answer TEXT NULL,
            option_a TEXT NULL,
            option_b TEXT NULL,
            option_c TEXT NULL,
            option_d TEXT NULL,
            marks DECIMAL(6,2) NOT NULL DEFAULT 1,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_weekly_questions (test_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS weekly_test_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            test_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NULL,
            guest_name VARCHAR(160) NULL,
            guest_phone VARCHAR(40) NULL,
            started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            submitted_at DATETIME NULL,
            expires_at DATETIME NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'started',
            auto_score DECIMAL(8,2) NULL,
            admin_score DECIMAL(8,2) NULL,
            total_marks DECIMAL(8,2) NULL,
            admin_note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_weekly_attempts (test_id, student_id, status, submitted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN warning_count INT UNSIGNED NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN activity_log TEXT NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN access_token VARCHAR(80) NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN result_token VARCHAR(80) NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN question_snapshot LONGTEXT NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN submission_reason VARCHAR(40) NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN last_saved_at DATETIME NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN question_order TEXT NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN timing_log MEDIUMTEXT NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN suspicious_flag ENUM('No','Yes') NOT NULL DEFAULT 'No'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN shuffle_questions ENUM('No','Yes') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN shuffle_options ENUM('No','Yes') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN warning_limit INT UNSIGNED NOT NULL DEFAULT 3");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN penalty_marks DECIMAL(8,2) NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN penalty_after_warnings ENUM('Yes','No') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN penalty_per_warning DECIMAL(6,2) NOT NULL DEFAULT 1");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN strict_exam_mode ENUM('No','Yes') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN auto_submit_on_warning_limit ENUM('No','Yes') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN allow_question_jump ENUM('No','Yes') NOT NULL DEFAULT 'Yes'");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN batch_id INT UNSIGNED NULL");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN batch_label VARCHAR(180) NULL");
        db_exec_safe("ALTER TABLE weekly_tests ADD COLUMN deleted_at DATETIME NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN status_deleted TINYINT(1) NOT NULL DEFAULT 0");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN deleted_at DATETIME NULL");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD COLUMN canonical_phone VARCHAR(20) NULL");
        db_exec_safe("ALTER TABLE weekly_test_questions ADD COLUMN deleted_at DATETIME NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN reviewer_role VARCHAR(160) NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN review_date VARCHAR(80) NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN source_label VARCHAR(120) NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN avatar_initials VARCHAR(8) NULL");
        db_exec_safe("ALTER TABLE testimonials ADD COLUMN sort_order INT NOT NULL DEFAULT 0");

        db_exec_safe("ALTER TABLE weekly_test_attempts ADD INDEX idx_weekly_guest_phone (guest_phone)");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD INDEX idx_weekly_access_token (access_token)");
        db_exec_safe("ALTER TABLE weekly_test_attempts ADD INDEX idx_weekly_result_token (result_token)");
        db()->exec("CREATE TABLE IF NOT EXISTS weekly_test_answers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            attempt_id INT UNSIGNED NOT NULL,
            question_id INT UNSIGNED NOT NULL,
            answer_text TEXT NULL,
            is_correct ENUM('Yes','No','Review') NOT NULL DEFAULT 'Review',
            marks_awarded DECIMAL(6,2) NULL,
            admin_note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_attempt_question (attempt_id, question_id),
            INDEX idx_weekly_answers (attempt_id, question_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->exec("CREATE TABLE IF NOT EXISTS weekly_test_winners (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            test_id INT UNSIGNED NOT NULL,
            attempt_id INT UNSIGNED NOT NULL,
            rank_no INT UNSIGNED NOT NULL DEFAULT 0,
            student_name VARCHAR(180) NULL,
            student_phone VARCHAR(30) NULL,
            score DECIMAL(8,2) NOT NULL DEFAULT 0,
            total_marks DECIMAL(8,2) NOT NULL DEFAULT 0,
            published_until DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_weekly_winner_attempt (test_id, attempt_id),
            INDEX idx_weekly_winners (test_id, rank_no, published_until)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        weekly_test_backfill_canonical_phones();
        // Permanent cleanup must be an explicit admin action, never a schema-upgrade side effect.
        weekly_test_seed_default();
        db()->prepare("INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
            ->execute(['weekly_schema_marker', $weeklySchemaMarker]);
    } catch (Throwable $e) {}
}


function weekly_test_clean_phone(?string $phone): string
{
    $digits = preg_replace('/\D+/', '', (string)$phone);
    if (strlen($digits) > 10 && substr($digits, 0, 2) === '91') $digits = substr($digits, -10);
    return substr($digits, -10);
}


function weekly_test_backfill_canonical_phones(): void
{
    try {
        $stmt = db()->query("SELECT a.id, COALESCE(NULLIF(s.phone,''), NULLIF(a.guest_phone,''), '') phone_raw
                            FROM weekly_test_attempts a
                            LEFT JOIN students s ON s.id=a.student_id
                            WHERE (a.canonical_phone IS NULL OR a.canonical_phone='')
                            LIMIT 1000");
        $upd = db()->prepare("UPDATE weekly_test_attempts SET canonical_phone=? WHERE id=?");
        foreach ($stmt->fetchAll() as $row) {
            $clean = weekly_test_clean_phone($row['phone_raw'] ?? '');
            if ($clean !== '') $upd->execute([$clean, (int)$row['id']]);
        }
    } catch (Throwable $e) {}
}

function weekly_test_cleanup_deleted_records(): void
{
    try {
        $ids = db()->query("SELECT id FROM weekly_test_attempts WHERE status_deleted=1 AND deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 15 DAY) LIMIT 500")->fetchAll(PDO::FETCH_COLUMN);
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            db()->prepare("DELETE FROM weekly_test_answers WHERE attempt_id IN ($in)")->execute($ids);
            db()->prepare("DELETE FROM weekly_test_attempts WHERE id IN ($in)")->execute($ids);
        }
        db()->exec("DELETE FROM weekly_test_questions WHERE status_deleted=1 AND deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 15 DAY) LIMIT 500");
        db()->exec("DELETE FROM weekly_tests WHERE status_deleted=1 AND deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 15 DAY) LIMIT 100");
    } catch (Throwable $e) {}
}

function weekly_test_get_batches(): array
{
    try {
        return db()->query("SELECT id, batch_name, course_name, timing, days FROM batch_timings WHERE published='Yes' ORDER BY sort_order ASC, id DESC")->fetchAll();
    } catch (Throwable $e) { return []; }
}

function weekly_test_seed_default(): void
{
    try {
        $count = (int)db()->query("SELECT COUNT(*) FROM weekly_tests WHERE status_deleted=0")->fetchColumn();
        if ($count > 0) return;
        $tests = [
            ['Basic Spoken Test', 'basic', 'No', 'active', 'Any visitor can try this basic spoken English test.'],
            ['Previous Weekly Test', 'previous', 'No', 'active', 'For students who missed the weekly test day.'],
            ['Upcoming Weekly Test', 'upcoming', 'Yes', 'draft', 'Login required. Admin can activate this when ready.']
        ];
        $ins = db()->prepare("INSERT INTO weekly_tests (title,test_type,requires_login,status,instructions,duration_minutes,total_questions,total_marks,published) VALUES (?,?,?,?,?,30,30,30,'Yes')");
        foreach ($tests as $t) $ins->execute([$t[0],$t[1],$t[2],$t[3],$t[4]]);
        $basicId = (int)db()->query("SELECT id FROM weekly_tests WHERE test_type='basic' LIMIT 1")->fetchColumn();
        $prevId = (int)db()->query("SELECT id FROM weekly_tests WHERE test_type='previous' LIMIT 1")->fetchColumn();
        $upId = (int)db()->query("SELECT id FROM weekly_tests WHERE test_type='upcoming' LIMIT 1")->fetchColumn();
        $samples = [];
        $base = [
            ['hindi_to_english','Present Simple','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day.'],
            ['hindi_to_english','is am are','मैं तैयार हूँ।','I am ready.'],
            ['english_to_hindi','can','I can speak English.','मैं अंग्रेजी बोल सकता/सकती हूँ।'],
            ['correction','Present Simple','Correct: She go to class every day.','She goes to class every day.'],
            ['hindi_to_english','have to','मुझे आज पढ़ना है।','I have to study today.'],
            ['english_to_hindi','should','You should practise daily.','आपको रोज अभ्यास करना चाहिए।']
        ];
        for ($i=1; $i<=30; $i++) { $samples[] = $base[($i-1)%count($base)]; }
        $qins = db()->prepare("INSERT INTO weekly_test_questions (test_id,question_type,topic_name,level,question_text,expected_answer,marks,sort_order,published) VALUES (?,?,?,?,?,?,?,?, 'Yes')");
        foreach ([$basicId,$prevId,$upId] as $tid) {
            $n=1; foreach ($samples as $s) { $qins->execute([$tid,$s[0],$s[1],'Beginner',$s[2],$s[3],1,$n++]); }
        }
    } catch (Throwable $e) {}
}


function weekly_test_create_snapshot(array $questions, array $test): array
{
    $snapshot = [];
    foreach ($questions as $q) {
        $options = array_values(array_filter([
            trim((string)($q['option_a'] ?? '')), trim((string)($q['option_b'] ?? '')),
            trim((string)($q['option_c'] ?? '')), trim((string)($q['option_d'] ?? '')),
        ], fn($v) => $v !== ''));
        if (($test['shuffle_options'] ?? 'Yes') === 'Yes' && count($options) > 1) shuffle($options);
        $snapshot[] = [
            'id'=>(int)$q['id'], 'type'=>(string)($q['question_type'] ?? ''),
            'topic'=>(string)($q['topic_name'] ?? ''), 'level'=>(string)($q['level'] ?? ''),
            'question'=>(string)($q['question_text'] ?? ''), 'expected'=>(string)($q['expected_answer'] ?? ''),
            'options'=>$options, 'marks'=>(float)($q['marks'] ?? 1),
        ];
    }
    return $snapshot;
}

function weekly_test_snapshot_questions(array $attempt, bool $includeExpected = false): array
{
    $rows = json_decode((string)($attempt['question_snapshot'] ?? ''), true);
    if (!is_array($rows)) return [];
    $safe = [];
    foreach ($rows as $row) {
        if (!is_array($row) || (int)($row['id'] ?? 0) <= 0) continue;
        $item = [
            'id'=>(int)$row['id'], 'type'=>(string)($row['type'] ?? ''), 'topic'=>(string)($row['topic'] ?? ''),
            'level'=>(string)($row['level'] ?? ''), 'question'=>(string)($row['question'] ?? ''),
            'options'=>array_values(array_map('strval', is_array($row['options'] ?? null) ? $row['options'] : [])),
            'marks'=>(float)($row['marks'] ?? 1),
        ];
        if ($includeExpected) $item['expected'] = (string)($row['expected'] ?? '');
        $safe[] = $item;
    }
    return $safe;
}

function weekly_test_result_url(array $attempt): string
{
    $url = 'weekly-result.php?attempt_id=' . (int)$attempt['id'];
    if (empty($attempt['student_id'])) {
        $token = trim((string)($attempt['result_token'] ?? $attempt['access_token'] ?? ''));
        if ($token !== '') $url .= '&token=' . rawurlencode($token);
    }
    return $url;
}

function weekly_attempt_remaining_seconds(array $attempt): int
{
    $duration = max(1, min(240, (int)($attempt['duration_minutes'] ?? 30))) * 60;
    $expires = strtotime((string)($attempt['expires_at'] ?? ''));
    if ($expires) {
        return max(0, min($duration, $expires - time()));
    }
    $started = strtotime((string)($attempt['started_at'] ?? ''));
    $remaining = $started ? (($started + $duration) - time()) : $duration;
    return max(0, min($duration, (int)$remaining));
}

function weekly_test_order_questions(array $questions, array $test, ?array $attempt = null): array
{
    $ids = [];
    if ($attempt && !empty($attempt['question_order'])) {
        $decoded = json_decode((string)$attempt['question_order'], true);
        if (is_array($decoded)) {
            $ids = array_values(array_filter(array_map('intval', $decoded)));
        }
    }

    $byId = [];
    foreach ($questions as $q) {
        $byId[(int)$q['id']] = $q;
    }

    $ordered = [];
    if ($ids) {
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
                unset($byId[$id]);
            }
        }
        foreach ($byId as $q) {
            $ordered[] = $q;
        }
        return $ordered;
    }

    if (($test['shuffle_questions'] ?? 'Yes') === 'Yes') {
        shuffle($questions);
    }
    return $questions;
}


function weekly_test_upcoming_gap_hours(): int
{
    $hours = (int)app_setting('weekly_upcoming_min_gap_hours', '12');
    return max(0, min(168, $hours));
}

function weekly_test_student_batch_eligibility(int $studentId, array $test): array
{
    $studentId = max(0, $studentId);
    $batchId = max(0, (int)($test['batch_id'] ?? 0));
    $batchLabel = trim((string)(($test['batch_label'] ?? '') ?: ($test['batch_name'] ?? '') ?: 'selected batch'));
    if ($batchId <= 0) {
        return ['allowed'=>true, 'message'=>'Common Upcoming Test is available to all active students.', 'batch_id'=>0, 'batch_label'=>'Common / All Batches', 'source'=>'common'];
    }
    if ($studentId <= 0) {
        return ['allowed'=>false, 'message'=>'Student login is required for this batch test.', 'batch_id'=>$batchId, 'batch_label'=>$batchLabel, 'source'=>'login_required'];
    }

    $checkAccess = static function(int $sid, int $bid): ?string {
        if (table_exists('student_batch_memberships')) {
            $sql = "SELECT sbm.id FROM student_batch_memberships sbm";
            if (table_exists('student_enrollments')) $sql .= " LEFT JOIN student_enrollments se ON se.id=sbm.enrollment_id";
            $sql .= " WHERE sbm.student_id=? AND sbm.batch_id=? AND sbm.membership_status='Active'";
            if (table_exists('student_enrollments')) $sql .= " AND (se.id IS NULL OR se.enrollment_status NOT IN ('Cancelled','Completed'))";
            $sql .= " LIMIT 1";
            $stmt=db()->prepare($sql); $stmt->execute([$sid,$bid]);
            if($stmt->fetchColumn()) return 'membership';
        }
        if (table_exists('admissions') && column_exists('admissions','student_id') && column_exists('admissions','batch_id')) {
            $where="student_id=? AND batch_id=?";
            if(column_exists('admissions','status_deleted')) $where.=" AND COALESCE(status_deleted,0)=0";
            if(column_exists('admissions','published')) $where.=" AND published='Yes'";
            if(column_exists('admissions','admission_status')) $where.=" AND COALESCE(admission_status,'') NOT IN ('Cancelled','Rejected')";
            $stmt=db()->prepare("SELECT id FROM admissions WHERE $where ORDER BY id DESC LIMIT 1"); $stmt->execute([$sid,$bid]);
            if($stmt->fetchColumn()) return 'admission';
        }
        return null;
    };

    try {
        $source=$checkAccess($studentId,$batchId);
        if($source){
            return ['allowed'=>true,'message'=>$source==='membership'?'Student belongs to this batch.':'Student admission belongs to this batch.','batch_id'=>$batchId,'batch_label'=>$batchLabel,'source'=>$source];
        }

        // Safely reconcile older verified student accounts whose admission existed before
        // the lifecycle tables were introduced. Unverified self-entered phone numbers are
        // never auto-linked here.
        if (function_exists('lifecycle_link_student_registration')) {
            $st=db()->prepare('SELECT phone' . (column_exists('students','identity_status') ? ',identity_status' : '') . ' FROM students WHERE id=? AND status_deleted=0 LIMIT 1');
            $st->execute([$studentId]); $studentRow=$st->fetch()?:[];
            $verified=!column_exists('students','identity_status') || (string)($studentRow['identity_status']??'Unverified')==='Verified';
            if($verified && !empty($studentRow['phone'])){
                lifecycle_link_student_registration($studentId,(string)$studentRow['phone']);
                $source=$checkAccess($studentId,$batchId);
                if($source){
                    return ['allowed'=>true,'message'=>'Student batch access was safely restored from the verified admission record.','batch_id'=>$batchId,'batch_label'=>$batchLabel,'source'=>'reconciled_'.$source];
                }
            }
        }

        return [
            'allowed'=>false,
            'message'=>'This Upcoming Test is for '.$batchLabel.'. Admin can open Student Accounts → this student → Upcoming Test Batch Access and grant the correct batch without changing the admission record.',
            'batch_id'=>$batchId,
            'batch_label'=>$batchLabel,
            'source'=>'not_assigned',
        ];
    } catch (Throwable $e) {
        error_log('[weekly-batch-eligibility] ' . $e->getMessage());
        return [
            'allowed'=>false,
            'message'=>'Your batch eligibility could not be verified safely. Ask the institute to check Student Account → Upcoming Test Batch Access.',
            'batch_id'=>$batchId,
            'batch_label'=>$batchLabel,
            'source'=>'error',
        ];
    }
}

function weekly_test_upcoming_eligibility(int $studentId, int $testId): array
{
    $studentId = max(0, $studentId);
    $testId = max(0, $testId);
    if ($studentId <= 0 || $testId <= 0) {
        return ['allowed'=>false, 'message'=>'Student login and a valid Upcoming Test are required.', 'wait_seconds'=>0, 'available_at'=>null];
    }

    try {
        $running = db()->prepare("SELECT a.id, t.title
            FROM weekly_test_attempts a
            JOIN weekly_tests t ON t.id=a.test_id
            WHERE COALESCE(a.status_deleted,0)=0 AND a.student_id=? AND a.test_id<>?
              AND a.status='started' AND (a.expires_at IS NULL OR a.expires_at>NOW())
              AND t.test_type='upcoming' AND COALESCE(t.status_deleted,0)=0
            ORDER BY a.started_at DESC, a.id DESC LIMIT 1");
        $running->execute([$studentId, $testId]);
        $openAttempt = $running->fetch();
        if ($openAttempt) {
            return [
                'allowed'=>false,
                'message'=>'Finish your current Upcoming Test before opening another official test.',
                'wait_seconds'=>0,
                'available_at'=>null,
                'previous_test'=>(string)($openAttempt['title'] ?? ''),
            ];
        }

        $gapHours = weekly_test_upcoming_gap_hours();
        if ($gapHours <= 0) {
            return ['allowed'=>true, 'message'=>'Eligible for this Upcoming Test.', 'wait_seconds'=>0, 'available_at'=>null, 'gap_hours'=>0];
        }

        $last = db()->prepare("SELECT a.id, COALESCE(a.submitted_at,a.started_at) completed_at, t.title
            FROM weekly_test_attempts a
            JOIN weekly_tests t ON t.id=a.test_id
            WHERE COALESCE(a.status_deleted,0)=0 AND a.student_id=? AND a.test_id<>?
              AND a.status IN ('submitted','checked') AND t.test_type='upcoming' AND COALESCE(t.status_deleted,0)=0
            ORDER BY COALESCE(a.submitted_at,a.started_at) DESC, a.id DESC LIMIT 1");
        $last->execute([$studentId, $testId]);
        $previous = $last->fetch();
        if (!$previous || empty($previous['completed_at'])) {
            return ['allowed'=>true, 'message'=>'Eligible for this Upcoming Test.', 'wait_seconds'=>0, 'available_at'=>null, 'gap_hours'=>$gapHours];
        }

        $previousTs = strtotime((string)$previous['completed_at']);
        if (!$previousTs) {
            return ['allowed'=>true, 'message'=>'Eligible for this Upcoming Test.', 'wait_seconds'=>0, 'available_at'=>null, 'gap_hours'=>$gapHours];
        }
        $availableTs = $previousTs + ($gapHours * 3600);
        $wait = max(0, $availableTs - time());
        if ($wait <= 0) {
            return ['allowed'=>true, 'message'=>'Eligible for this Upcoming Test.', 'wait_seconds'=>0, 'available_at'=>date('Y-m-d H:i:s', $availableTs), 'gap_hours'=>$gapHours];
        }

        $hoursLeft = max(1, (int)ceil($wait / 3600));
        return [
            'allowed'=>false,
            'message'=>'Upcoming Test security lock is active. You can start the next official test after '.date('d M Y, h:i A', $availableTs).' (about '.$hoursLeft.' hour'.($hoursLeft===1?'':'s').' remaining).',
            'wait_seconds'=>$wait,
            'available_at'=>date('Y-m-d H:i:s', $availableTs),
            'gap_hours'=>$gapHours,
            'previous_test'=>(string)($previous['title'] ?? ''),
        ];
    } catch (Throwable $e) {
        error_log('[weekly-upcoming-eligibility] ' . $e->getMessage());
        return ['allowed'=>false, 'message'=>'Upcoming Test eligibility could not be verified safely. Please try again.', 'wait_seconds'=>0, 'available_at'=>null];
    }
}

function weekly_test_ready_reason(array $test): string
{
    $status = strtolower((string)($test['status'] ?? 'draft'));
    $qCount = (int)($test['question_count'] ?? 0);
    if ($status !== 'active') return 'pending';
    if ($qCount <= 0) return 'no_questions';
    $now = time();
    if (!empty($test['starts_at']) && strtotime((string)$test['starts_at']) > $now) return 'scheduled_later';
    if (!empty($test['ends_at']) && strtotime((string)$test['ends_at']) < $now) return 'expired';
    return 'ready';
}


function weekly_test_set_single_active_by_type(int $testId, bool $clearSchedule = false): void
{
    weekly_test_ensure_schema();
    if ($testId <= 0) return;
    $stmt = db()->prepare("SELECT test_type, COALESCE(batch_id,0) batch_id FROM weekly_tests WHERE id=? AND status_deleted=0 LIMIT 1");
    $stmt->execute([$testId]);
    $paper = $stmt->fetch();
    $type = (string)($paper['test_type'] ?? '');
    $batchId = max(0, (int)($paper['batch_id'] ?? 0));
    if ($type === '') return;

    // Basic/Previous keep one active paper globally. Upcoming is scoped per batch so
    // different batches can have independent official tests at the same time.
    if ($type === 'upcoming') {
        if ($batchId > 0) {
            db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE test_type='upcoming' AND id<>? AND COALESCE(batch_id,0)=? AND status_deleted=0 AND LOWER(status)='active'")
                ->execute([$testId, $batchId]);
        } else {
            db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE test_type='upcoming' AND id<>? AND COALESCE(batch_id,0)=0 AND status_deleted=0 AND LOWER(status)='active'")
                ->execute([$testId]);
        }
    } else {
        db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE test_type=? AND id<>? AND status_deleted=0 AND LOWER(status)='active'")
            ->execute([$type, $testId]);
    }

    if ($clearSchedule) {
        db()->prepare("UPDATE weekly_tests SET status='active', published='Yes', starts_at=NULL, ends_at=NULL, updated_at=NOW() WHERE id=? AND status_deleted=0")->execute([$testId]);
    } else {
        db()->prepare("UPDATE weekly_tests SET status='active', published='Yes', updated_at=NOW() WHERE id=? AND status_deleted=0")->execute([$testId]);
    }
}

function weekly_test_publish_now(int $testId, bool $activateQuestions = true, bool $clearSchedule = true): bool
{
    weekly_test_ensure_schema();
    if ($testId <= 0) return false;
    weekly_test_set_single_active_by_type($testId, $clearSchedule);
    if ($activateQuestions) {
        db()->prepare("UPDATE weekly_test_questions SET published='Yes' WHERE test_id=? AND status_deleted=0")->execute([$testId]);
    }
    return true;
}

function weekly_test_close_entry(int $testId): bool
{
    weekly_test_ensure_schema();
    if ($testId <= 0) return false;
    $stmt=db()->prepare("SELECT test_type FROM weekly_tests WHERE id=? AND COALESCE(status_deleted,0)=0 LIMIT 1");
    $stmt->execute([$testId]);
    $type=strtolower((string)($stmt->fetchColumn()?:''));
    if($type==='') return false;
    if($type==='upcoming') {
        db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE id=? AND COALESCE(status_deleted,0)=0")->execute([$testId]);
    } else {
        db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE id=? AND COALESCE(status_deleted,0)=0")->execute([$testId]);
    }
    return true;
}

function weekly_test_fetch_tests(?string $type = null): array
{
    weekly_test_ensure_schema();
    $where = "WHERE t.status_deleted=0 AND t.published='Yes'"; $params=[];
    if ($type) { $where .= " AND t.test_type=?"; $params[]=$type; }
    $stmt = db()->prepare("SELECT t.*, bt.batch_name, bt.timing batch_timing, bt.days batch_days, (SELECT COUNT(*) FROM weekly_test_questions q WHERE q.test_id=t.id AND q.status_deleted=0 AND q.published='Yes') question_count, CASE WHEN LOWER(t.status)='active' AND (t.starts_at IS NULL OR t.starts_at<=NOW()) AND (t.ends_at IS NULL OR t.ends_at>=NOW()) AND (SELECT COUNT(*) FROM weekly_test_questions q2 WHERE q2.test_id=t.id AND q2.status_deleted=0 AND q2.published='Yes') > 0 THEN 1 ELSE 0 END ready_now FROM weekly_tests t LEFT JOIN batch_timings bt ON bt.id=t.batch_id $where ORDER BY FIELD(t.test_type,'basic','previous','upcoming'), ready_now DESC, CASE WHEN LOWER(t.status)='active' THEN 0 ELSE 1 END, COALESCE(t.starts_at, t.created_at) DESC, t.id DESC");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function weekly_test_fetch_questions(int $testId, int $limit = 30): array
{
    weekly_test_ensure_schema();
    $limit = max(1, min(500, $limit));
    $stmt = db()->prepare("SELECT * FROM weekly_test_questions WHERE test_id=? AND status_deleted=0 AND published='Yes' ORDER BY sort_order ASC, id ASC LIMIT " . $limit);
    $stmt->execute([$testId]);
    return $stmt->fetchAll();
}

function weekly_test_default_by_type(string $type): ?array
{
    $tests = weekly_test_fetch_tests($type);
    if (!$tests) return null;
    foreach ($tests as $t) if (strtolower((string)($t['status'] ?? '')) === 'active') return $t;
    return $tests[0];
}

function weekly_test_import_rows(int $testId, array $rows): int
{
    weekly_test_ensure_schema();
    $insert = db()->prepare("INSERT INTO weekly_test_questions (test_id,question_type,topic_name,level,question_text,expected_answer,option_a,option_b,option_c,option_d,marks,sort_order,published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'Yes')");
    $n = (int)db()->query("SELECT COALESCE(MAX(sort_order),0) FROM weekly_test_questions WHERE test_id=".(int)$testId)->fetchColumn();
    $added=0;
    foreach ($rows as $row) {
        $question = trim((string)($row['question_text'] ?? $row['question'] ?? $row[0] ?? ''));
        if ($question === '') continue;

        $answer = trim((string)($row['expected_answer'] ?? $row['answer'] ?? $row['correct_answer'] ?? $row[1] ?? ''));
        $type = trim((string)($row['question_type'] ?? $row['type'] ?? $row[2] ?? 'hindi_to_english')) ?: 'hindi_to_english';
        $topic = trim((string)($row['topic_name'] ?? $row['topic'] ?? $row[3] ?? ''));
        $level = trim((string)($row['level'] ?? $row[4] ?? 'Beginner')) ?: 'Beginner';
        $marks = (float)($row['marks'] ?? $row[5] ?? 1);
        if ($marks <= 0) $marks = 1;

        $opts = [
            trim((string)($row['option_a'] ?? $row['a'] ?? $row[6] ?? '')),
            trim((string)($row['option_b'] ?? $row['b'] ?? $row[7] ?? '')),
            trim((string)($row['option_c'] ?? $row['c'] ?? $row[8] ?? '')),
            trim((string)($row['option_d'] ?? $row['d'] ?? $row[9] ?? '')),
        ];

        $insert->execute([$testId,$type,$topic,$level,$question,$answer,$opts[0],$opts[1],$opts[2],$opts[3],$marks,++$n]);
        $added++;
    }
    return $added;
}

function csv_assoc_rows(string $path): array
{
    $rows=[]; $fh=fopen($path,'r'); if(!$fh) return $rows;
    $header = fgetcsv($fh); if(!$header) { fclose($fh); return $rows; }
    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    }
    $header = array_map(function($h){
        $h = strtolower(trim((string)$h));
        $h = preg_replace('/[^a-z0-9_]+/', '_', $h);
        return trim($h, '_');
    }, $header);
    while(($data=fgetcsv($fh))!==false){
        if ($data === [null] || count(array_filter($data, fn($v)=>trim((string)$v)!==''))===0) continue;
        $row=[]; foreach($header as $i=>$h){ if($h==='') continue; $row[$h] = $data[$i] ?? ''; }
        $rows[]=$row;
    }
    fclose($fh); return $rows;
}


function simple_zip_get_file(string $zipPath, string $entryName): ?string
{
    $data = @file_get_contents($zipPath);
    if ($data === false || strlen($data) < 22 || !function_exists('gzinflate')) return null;
    $pos = strrpos($data, "PK\x05\x06");
    if ($pos === false) return null;
    $eocd = substr($data, $pos, 22);
    $e = @unpack('vdisk/vcdisk/ventriesDisk/ventries/Vsize/Voffset/vcomment', $eocd);
    if (!$e) return null;
    $cdOffset = (int)$e['offset'];
    $cdSize = (int)$e['size'];
    $ptr = $cdOffset;
    $end = min(strlen($data), $cdOffset + $cdSize);
    while ($ptr + 46 <= $end && substr($data, $ptr, 4) === "PK\x01\x02") {
        $h = @unpack('Vsig/vverMade/vverNeed/vflag/vmethod/vtime/vdate/Vcrc/Vcsize/Vusize/vnlen/velen/vclen/vdisk/vintattr/Vextattr/Vlhoff', substr($data, $ptr, 46));
        if (!$h) break;
        $name = substr($data, $ptr + 46, (int)$h['nlen']);
        $ptr += 46 + (int)$h['nlen'] + (int)$h['elen'] + (int)$h['clen'];
        if ($name !== $entryName) continue;
        $lo = (int)$h['lhoff'];
        if (substr($data, $lo, 4) !== "PK\x03\x04") return null;
        $lh = @unpack('Vsig/vver/vflag/vmethod/vtime/vdate/Vcrc/Vcsize/Vusize/vnlen/velen', substr($data, $lo, 30));
        if (!$lh) return null;
        $start = $lo + 30 + (int)$lh['nlen'] + (int)$lh['elen'];
        $compressed = substr($data, $start, (int)$h['csize']);
        if ((int)$h['method'] === 0) return $compressed;
        if ((int)$h['method'] === 8) {
            $out = @gzinflate($compressed);
            return $out === false ? null : $out;
        }
        return null;
    }
    return null;
}

function xlsx_assoc_rows(string $path): array
{
    $get = function(string $name) use ($path): ?string {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($path) === true) {
                $xml = $zip->getFromName($name);
                $zip->close();
                return $xml === false ? null : $xml;
            }
        }
        return simple_zip_get_file($path, $name);
    };
    $shared=[]; $sxml=$get('xl/sharedStrings.xml');
    if ($sxml) {
        $sx=@simplexml_load_string($sxml);
        if ($sx) foreach($sx->si as $si){
            $txt='';
            if (isset($si->t)) $txt=(string)$si->t;
            elseif (isset($si->r)) foreach($si->r as $r){ $txt.=(string)($r->t ?? ''); }
            $shared[]=$txt;
        }
    }
    $xml=$get('xl/worksheets/sheet1.xml');
    if(!$xml) return [];
    $sx=@simplexml_load_string($xml); if(!$sx) return [];
    $grid=[];
    foreach($sx->sheetData->row as $row){
        $line=[];
        foreach($row->c as $c){
            $r=(string)$c['r']; preg_match('/([A-Z]+)/',$r,$m); $col=0; foreach(str_split($m[1] ?? 'A') as $ch){$col=$col*26+ord($ch)-64;} $col--;
            $type=(string)$c['t'];
            $v='';
            if($type==='s') { $idx=(int)($c->v ?? 0); $v=$shared[$idx] ?? ''; }
            elseif($type==='inlineStr') { $v=(string)($c->is->t ?? ''); if($v==='' && isset($c->is->r)) foreach($c->is->r as $rr){$v.=(string)($rr->t ?? '');} }
            else { $v=(string)($c->v ?? ''); }
            $line[$col]=$v;
        }
        if(count(array_filter($line, fn($v)=>trim((string)$v)!==''))>0) { ksort($line); $grid[]=array_values($line); }
    }
    if(!$grid) return [];
    $header=array_map(function($h){
        $h = strtolower(trim((string)$h));
        $h = preg_replace('/[^a-z0-9_]+/', '_', $h);
        return trim($h, '_');
    }, array_shift($grid));
    $rows=[]; foreach($grid as $data){ $row=[]; foreach($header as $i=>$h){ if($h==='') continue; $row[$h]=$data[$i]??'';} if($row) $rows[]=$row; }
    return $rows;
}

function weekly_test_parse_upload(string $path, string $name): array
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($ext === 'xlsx') return xlsx_assoc_rows($path);
    if (in_array($ext, ['csv','txt'], true)) return csv_assoc_rows($path);
    // Old .xls binary files are not safe to parse without a library. Save it as CSV or XLSX and upload again.
    return [];
}


function weekly_test_fetch_attempts_for_student(int $studentId, int $limit = 20): array
{
    weekly_test_ensure_schema();
    $stmt = db()->prepare("SELECT a.*, t.title test_title, t.test_type, t.duration_minutes, t.status test_status, t.starts_at test_starts_at, t.ends_at test_ends_at FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id WHERE a.student_id=? AND COALESCE(a.status_deleted,0)=0 ORDER BY a.id DESC LIMIT " . (int)$limit);
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

function weekly_test_attempt_detail(int $attemptId): ?array
{
    weekly_test_ensure_schema();
    $stmt = db()->prepare("SELECT a.*, t.title test_title, t.test_type, t.status test_status, t.starts_at test_starts_at, t.ends_at test_ends_at, t.instructions, t.penalty_after_warnings, t.penalty_per_warning, t.warning_limit, s.full_name student_name, s.phone student_phone FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id LEFT JOIN students s ON s.id=a.student_id WHERE a.id=? AND COALESCE(a.status_deleted,0)=0 LIMIT 1");
    $stmt->execute([$attemptId]);
    $attempt = $stmt->fetch();
    if (!$attempt) return null;

    $answerStmt = db()->prepare("SELECT * FROM weekly_test_answers WHERE attempt_id=? ORDER BY id ASC");
    $answerStmt->execute([$attemptId]);
    $answersByQuestion = [];
    foreach ($answerStmt->fetchAll() as $answer) $answersByQuestion[(int)$answer['question_id']] = $answer;

    $snapshot = weekly_test_snapshot_questions($attempt, true);
    if ($snapshot) {
        $attempt['answers'] = [];
        foreach ($snapshot as $q) {
            $answer = $answersByQuestion[(int)$q['id']] ?? [];
            $attempt['answers'][] = array_merge($answer, [
                'question_id'=>(int)$q['id'], 'question_text'=>$q['question'], 'expected_answer'=>$q['expected'] ?? '',
                'question_type'=>$q['type'], 'topic_name'=>$q['topic'], 'level'=>$q['level'], 'marks'=>$q['marks'],
                'answer_text'=>(string)($answer['answer_text'] ?? ''), 'is_correct'=>(string)($answer['is_correct'] ?? 'Review'),
                'marks_awarded'=>$answer['marks_awarded'] ?? 0, 'admin_note'=>$answer['admin_note'] ?? null,
            ]);
        }
        return $attempt;
    }

    $stmt = db()->prepare("SELECT ans.*, q.question_text, q.expected_answer, q.question_type, q.topic_name, q.level, q.marks FROM weekly_test_answers ans JOIN weekly_test_questions q ON q.id=ans.question_id WHERE ans.attempt_id=? ORDER BY q.sort_order ASC, q.id ASC");
    $stmt->execute([$attemptId]);
    $attempt['answers'] = $stmt->fetchAll();
    return $attempt;
}

function weekly_test_status_badge(string $status): string
{
    $status = strtolower(trim($status));
    if ($status === 'checked') return 'Checked';
    if ($status === 'submitted') return 'Submitted / Pending Check';
    if ($status === 'started') return 'In Progress';
    return ucwords($status ?: 'Draft');
}

/**
 * Decide when the uploaded master answer may be revealed to the student.
 * Basic/Previous are practice/revision flows, so the key can be shown after final submit.
 * Upcoming is exam-like: revealing the key while the paper is still open would let one
 * student share answers with others, so it unlocks only after the window closes or the
 * admin completes/archives the paper.
 */
function weekly_test_expected_answers_releasable(array $attempt): bool
{
    $attemptStatus = strtolower(trim((string)($attempt['status'] ?? '')));
    if (!in_array($attemptStatus, ['submitted', 'checked'], true)) return false;

    $type = strtolower(trim((string)($attempt['test_type'] ?? 'basic')));
    if ($type !== 'upcoming') return true;

    $testStatus = strtolower(trim((string)($attempt['test_status'] ?? '')));
    if (in_array($testStatus, ['archived', 'closed', 'completed'], true)) return true;

    $endsAt = trim((string)($attempt['test_ends_at'] ?? ''));
    if ($endsAt !== '') {
        $ts = strtotime($endsAt);
        if ($ts !== false && $ts <= time()) return true;
    }
    return false;
}

function weekly_test_answer_release_note(array $attempt): string
{
    if (weekly_test_expected_answers_releasable($attempt)) return '';
    if (strtolower((string)($attempt['test_type'] ?? '')) === 'upcoming') {
        return 'Your submitted answer is visible now. The master answer will unlock after the upcoming test closes or the admin completes the batch paper.';
    }
    return 'The master answer is not available for this attempt yet.';
}

function weekly_test_split_expected_answers(string $expected): array
{
    $parts = preg_split('/\r\n|\r|\n|\s*\|\|\s*|\s*;\s*/', trim($expected));
    $out = [];
    foreach ($parts ?: [] as $p) {
        $p = trim($p);
        if ($p !== '') $out[] = $p;
    }
    return $out ?: (trim($expected) !== '' ? [trim($expected)] : []);
}

function weekly_test_match_answer(string $answer, string $expected): array
{
    $answer = trim($answer);
    $expected = trim($expected);
    if ($answer === '' || $expected === '') return ['is_correct'=>'Review','marks_ratio'=>0,'note'=>'Needs teacher review'];

    $a = practice_normalize_text($answer);
    $bestPct = 0;
    $best = '';
    foreach (weekly_test_split_expected_answers($expected) as $exp) {
        $e = practice_normalize_text($exp);
        if ($e === '') continue;
        if ($a === $e) return ['is_correct'=>'Yes','marks_ratio'=>1,'note'=>'Accepted answer'];
        similar_text($a, $e, $pct);
        if ($pct > $bestPct) { $bestPct = $pct; $best = $exp; }
    }

    if ($bestPct >= 92) return ['is_correct'=>'Yes','marks_ratio'=>1,'note'=>'Accepted close spelling'];
    if ($bestPct >= 82) return ['is_correct'=>'Review','marks_ratio'=>0.75,'note'=>'Very close. Teacher should verify'];
    if ($bestPct >= 68) return ['is_correct'=>'Review','marks_ratio'=>0.45,'note'=>'Partially close. Teacher review required'];
    return ['is_correct'=>'Review','marks_ratio'=>0,'note'=>'Teacher review required'];
}



/**
 * Load one weekly-test attempt with all grading settings.
 * When $forUpdate is true the row is locked inside the caller's transaction.
 */
function weekly_test_fetch_attempt_record(int $attemptId, string $accessToken = '', bool $forUpdate = false): ?array
{
    if ($attemptId <= 0) return null;
    $sql = "SELECT a.*, t.title, t.test_type, t.duration_minutes, t.total_questions,
                   t.penalty_after_warnings, t.penalty_per_warning, t.warning_limit,
                   t.auto_submit_on_warning_limit, t.shuffle_options, t.shuffle_questions,
                   t.instructions
            FROM weekly_test_attempts a
            JOIN weekly_tests t ON t.id=a.test_id
            WHERE a.id=? AND COALESCE(a.status_deleted,0)=0";
    $params = [$attemptId];
    if ($accessToken !== '') {
        $sql .= " AND a.access_token=?";
        $params[] = $accessToken;
    }
    $sql .= " LIMIT 1";
    if ($forUpdate) $sql .= " FOR UPDATE";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Build and persist an immutable question snapshot for an attempt when missing. */
function weekly_test_attempt_snapshot(array &$attempt): array
{
    $snapshot = weekly_test_snapshot_questions($attempt, true);
    if ($snapshot) return $snapshot;

    $questions = weekly_test_fetch_questions((int)($attempt['test_id'] ?? 0), 500);
    $questions = weekly_test_order_questions($questions, $attempt, $attempt);
    $questions = array_slice($questions, 0, max(1, (int)($attempt['total_questions'] ?? 30)));
    if (!$questions) return [];

    $snapshot = weekly_test_create_snapshot($questions, $attempt);
    $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) return [];

    $params = [$json, (int)$attempt['id']];
    $sql = "UPDATE weekly_test_attempts SET question_snapshot=? WHERE id=?";
    $token = trim((string)($attempt['access_token'] ?? ''));
    if ($token !== '') {
        $sql .= " AND access_token=?";
        $params[] = $token;
    }
    db()->prepare($sql)->execute($params);
    $attempt['question_snapshot'] = $json;
    return $snapshot;
}

function weekly_test_saved_answer_map(int $attemptId): array
{
    if ($attemptId <= 0) return [];
    $stmt = db()->prepare('SELECT question_id, answer_text FROM weekly_test_answers WHERE attempt_id=?');
    $stmt->execute([$attemptId]);
    $answers = [];
    foreach ($stmt->fetchAll() as $row) {
        $answers[(int)$row['question_id']] = trim((string)($row['answer_text'] ?? ''));
    }
    return $answers;
}

/**
 * Finalize one attempt from submitted answers plus any previously autosaved answers.
 * The operation is row-locked, atomic and idempotent.
 */
function weekly_test_finalize_attempt(int $attemptId, string $accessToken, array $submittedAnswers = [], string $reason = 'manual_submit'): array
{
    $allowedReasons = ['manual_submit', 'timer_expired', 'warning_limit', 'admin_recovery'];
    if (!in_array($reason, $allowedReasons, true)) $reason = 'manual_submit';
    if ($attemptId <= 0 || strlen($accessToken) < 32) {
        return ['success'=>false, 'message'=>'Invalid test attempt.'];
    }

    $pdo = db();
    $startedTransaction = !$pdo->inTransaction();
    try {
        if ($startedTransaction) $pdo->beginTransaction();
        $attempt = weekly_test_fetch_attempt_record($attemptId, $accessToken, true);
        if (!$attempt) {
            if ($startedTransaction && $pdo->inTransaction()) $pdo->rollBack();
            return ['success'=>false, 'message'=>'Attempt not found.'];
        }

        if (($attempt['status'] ?? '') !== 'started') {
            if ($startedTransaction && $pdo->inTransaction()) $pdo->commit();
            return [
                'success'=>true,
                'already_closed'=>true,
                'message'=>'This test is already closed.',
                'auto_score'=>(float)($attempt['auto_score'] ?? 0),
                'penalty_marks'=>(float)($attempt['penalty_marks'] ?? 0),
                'result_url'=>weekly_test_result_url($attempt),
                'attempt'=>$attempt,
            ];
        }

        $snapshot = weekly_test_attempt_snapshot($attempt);
        if (!$snapshot) throw new RuntimeException('Question snapshot is unavailable.');

        $allowedIds = [];
        foreach ($snapshot as $question) $allowedIds[(int)$question['id']] = true;
        $answers = weekly_test_saved_answer_map($attemptId);
        foreach ($submittedAnswers as $questionId => $answer) {
            $questionId = (int)$questionId;
            if ($questionId <= 0 || !isset($allowedIds[$questionId])) continue;
            $answer = trim((string)$answer);
            if (mb_strlen($answer) > 5000) $answer = mb_substr($answer, 0, 5000);
            $answers[$questionId] = $answer;
        }

        $upsert = $pdo->prepare("INSERT INTO weekly_test_answers
            (attempt_id,question_id,answer_text,is_correct,marks_awarded,admin_note)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE answer_text=VALUES(answer_text), is_correct=VALUES(is_correct),
                                    marks_awarded=VALUES(marks_awarded), admin_note=VALUES(admin_note)");

        $autoScore = 0.0;
        $savedCount = 0;
        foreach ($snapshot as $question) {
            $questionId = (int)($question['id'] ?? 0);
            if ($questionId <= 0) continue;
            $answer = trim((string)($answers[$questionId] ?? ''));
            $expected = trim((string)($question['expected'] ?? ''));
            $match = weekly_test_match_answer($answer, $expected);
            $marks = ($expected !== '' && $answer !== '')
                ? round((float)($question['marks'] ?? 1) * (float)($match['marks_ratio'] ?? 0), 2)
                : 0.0;
            if (($match['is_correct'] ?? 'Review') === 'Yes') $autoScore += $marks;
            $upsert->execute([$attemptId, $questionId, $answer, $match['is_correct'], $marks, $match['note']]);
            $savedCount++;
        }

        $warningCount = (int)($attempt['warning_count'] ?? 0);
        $penalty = 0.0;
        if (($attempt['test_type'] ?? 'basic') !== 'basic' && (($attempt['penalty_after_warnings'] ?? 'Yes') === 'Yes')) {
            $penalty = max(0, $warningCount - 1) * max(0, (float)($attempt['penalty_per_warning'] ?? 1));
        }
        $finalScore = max(0, round($autoScore - $penalty, 2));
        $resultToken = trim((string)($attempt['result_token'] ?? '')) ?: bin2hex(random_bytes(32));
        $note = $penalty > 0 ? (' Security penalty applied: -' . $penalty . ' mark(s).') : '';

        $update = $pdo->prepare("UPDATE weekly_test_attempts
                                 SET submitted_at=NOW(), status='submitted', auto_score=?, penalty_marks=?,
                                     submission_reason=?, result_token=?, last_saved_at=NOW(),
                                     admin_note=CONCAT(COALESCE(admin_note,''), ?)
                                 WHERE id=? AND access_token=? AND status='started'");
        $update->execute([$finalScore, $penalty, $reason, $resultToken, $note, $attemptId, $accessToken]);
        if ($update->rowCount() !== 1) throw new RuntimeException('Attempt state changed before finalization.');

        $attempt['status'] = 'submitted';
        $attempt['auto_score'] = $finalScore;
        $attempt['penalty_marks'] = $penalty;
        $attempt['result_token'] = $resultToken;
        $attempt['submission_reason'] = $reason;

        if ($startedTransaction && $pdo->inTransaction()) $pdo->commit();
        return [
            'success'=>true,
            'already_closed'=>false,
            'message'=>$reason === 'timer_expired'
                ? 'Time ended. Your saved answers were submitted automatically.'
                : 'Test submitted successfully. Teacher/admin will review marks.',
            'auto_score'=>$finalScore,
            'penalty_marks'=>$penalty,
            'saved'=>$savedCount,
            'result_url'=>weekly_test_result_url($attempt),
            'attempt'=>$attempt,
        ];
    } catch (Throwable $e) {
        if ($startedTransaction && $pdo->inTransaction()) $pdo->rollBack();
        error_log('[weekly-finalize] ' . $e->__toString());
        return ['success'=>false, 'message'=>'The test could not be finalized safely. Please try again.'];
    }
}

function weekly_test_complete_batch(int $testId): array
{
    weekly_test_ensure_schema();
    $testId = max(0, $testId);
    if ($testId <= 0) return ['success'=>false,'message'=>'Invalid test paper.'];

    $testStmt = db()->prepare("SELECT id,test_type,title,status FROM weekly_tests WHERE id=? AND COALESCE(status_deleted,0)=0 LIMIT 1");
    $testStmt->execute([$testId]);
    $test = $testStmt->fetch();
    if (!$test) return ['success'=>false,'message'=>'Test paper not found.'];

    // Upcoming positions should be trustworthy. Do not freeze 1st/2nd/3rd while
    // the paper is still open, a student is still inside the exam, or submitted
    // copies are waiting for teacher review.
    if (($test['test_type'] ?? '') === 'upcoming') {
        $liveStmt = db()->prepare("SELECT status, ends_at FROM weekly_tests WHERE id=? LIMIT 1");
        $liveStmt->execute([$testId]);
        $live = $liveStmt->fetch() ?: [];
        $endsTs = !empty($live['ends_at']) ? strtotime((string)$live['ends_at']) : false;
        $entryClosedNow = false;
        if (strtolower((string)($live['status'] ?? '')) === 'active' && (!$endsTs || $endsTs > time())) {
            // Admin chose Complete/Rank: close new entry first so ranking can never race
            // with a new student starting the same paper. Existing started attempts keep
            // their own server-side expires_at and may finish safely.
            db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE id=? AND COALESCE(status_deleted,0)=0")
                ->execute([$testId]);
            $entryClosedNow = true;
        }
        $running = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0 AND test_id=? AND status='started'");
        $running->execute([$testId]);
        $runningCount = (int)$running->fetchColumn();
        if ($runningCount > 0) {
            return ['success'=>false,'paper_closed'=>true,'message'=>($entryClosedNow?'New entries are closed. ':'').$runningCount.' student attempt'.($runningCount===1?' is':'s are').' still in progress. Let those students finish or wait for their timer to end, then check submitted copies and click Finalize Top 3 again.'];
        }
        $pending = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0 AND test_id=? AND status='submitted'");
        $pending->execute([$testId]);
        $pendingCount = (int)$pending->fetchColumn();
        if ($pendingCount > 0) {
            return ['success'=>false,'paper_closed'=>true,'message'=>($entryClosedNow?'New entries are closed. ':'').'Review and publish marks for '.$pendingCount.' submitted upcoming-test cop'.($pendingCount===1?'y':'ies').'. After all copies are Checked, click Finalize Top 3 again.'];
        }
    }

    $stmt = db()->prepare("SELECT a.*, COALESCE(NULLIF(s.full_name,''), NULLIF(a.guest_name,''), 'Guest Student') student_name, COALESCE(NULLIF(s.phone,''), NULLIF(a.guest_phone,''), '') student_phone, COALESCE(a.admin_score,a.auto_score,0) final_score FROM weekly_test_attempts a LEFT JOIN students s ON s.id=a.student_id WHERE COALESCE(a.status_deleted,0)=0 AND a.test_id=? AND a.status IN ('submitted','checked') ORDER BY final_score DESC, COALESCE(a.submitted_at,a.started_at) ASC, a.id ASC LIMIT 3");
    $stmt->execute([$testId]);
    $rows = $stmt->fetchAll();
    if (!$rows) return ['success'=>false,'message'=>'No submitted copies found for this test yet.'];

    $pdo = db();
    $startedTransaction = !$pdo->inTransaction();
    try {
        if ($startedTransaction) $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM weekly_test_winners WHERE test_id=?")->execute([$testId]);
        $ins = $pdo->prepare("INSERT INTO weekly_test_winners (test_id,attempt_id,rank_no,student_name,student_phone,score,total_marks,published_until) VALUES (?,?,?,?,?,?,?,DATE_ADD(NOW(), INTERVAL 2 DAY))");
        $rank = 1;
        foreach ($rows as $r) {
            $ins->execute([$testId,(int)$r['id'],$rank,trim((string)$r['student_name']),weekly_test_clean_phone((string)$r['student_phone']),(float)$r['final_score'],(float)($r['total_marks'] ?? 0)]);
            $rank++;
        }
        $pdo->prepare("UPDATE weekly_tests SET status='archived', updated_at=NOW() WHERE id=?")->execute([$testId]);
        if ($startedTransaction && $pdo->inTransaction()) $pdo->commit();
    } catch (Throwable $e) {
        if ($startedTransaction && $pdo->inTransaction()) $pdo->rollBack();
        error_log('[weekly-complete] ' . $e->__toString());
        return ['success'=>false,'message'=>'The test could not be completed safely. Please try again.'];
    }

    $rankMessage = (($test['test_type'] ?? '') === 'upcoming')
        ? ' Top 3 positions are stored; winner dashboards use Gold, Purple and Parrot Green rank themes.'
        : '';
    return ['success'=>true,'message'=>'Batch test completed. Top '.count($rows).' winner(s) published for 2 days.'.$rankMessage];
}

function weekly_test_active_winners(?int $testId = null): array
{
    weekly_test_ensure_schema();
    try {
        if ($testId) {
            $stmt = db()->prepare("SELECT w.*, t.title test_title, t.test_type FROM weekly_test_winners w JOIN weekly_tests t ON t.id=w.test_id WHERE w.test_id=? AND (w.published_until IS NULL OR w.published_until>=NOW()) ORDER BY w.rank_no ASC, w.score DESC");
            $stmt->execute([$testId]);
        } else {
            $stmt = db()->query("SELECT w.*, t.title test_title, t.test_type FROM weekly_test_winners w JOIN weekly_tests t ON t.id=w.test_id WHERE (w.published_until IS NULL OR w.published_until>=NOW()) ORDER BY w.created_at DESC, w.rank_no ASC LIMIT 12");
        }
        return $stmt ? $stmt->fetchAll() : [];
    } catch (Throwable $e) { return []; }
}


function weekly_test_active_winners_for_phone(string $phone = ''): array
{
    weekly_test_ensure_schema();
    $phone = weekly_test_clean_phone($phone);
    try {
        if ($phone !== '') {
            $stmt = db()->prepare("SELECT w.*, t.title test_title, t.test_type FROM weekly_test_winners w JOIN weekly_tests t ON t.id=w.test_id WHERE w.student_phone=? AND (w.published_until IS NULL OR w.published_until>=NOW()) ORDER BY w.created_at DESC, w.rank_no ASC LIMIT 6");
            $stmt->execute([$phone]);
            return $stmt->fetchAll();
        }
    } catch (Throwable $e) {}
    return weekly_test_active_winners(null);
}

/** Return a rank only when the student's latest finalized Upcoming Test earned Top 3. */
function weekly_test_latest_upcoming_rank_for_student(int $studentId, string $phone = ''): ?array
{
    weekly_test_ensure_schema();
    if ($studentId <= 0) return null;
    try {
        $stmt = db()->prepare("SELECT w.*, t.title test_title, t.test_type, a.student_id, a.submitted_at
            FROM weekly_test_attempts a
            JOIN weekly_tests t ON t.id=a.test_id
            LEFT JOIN weekly_test_winners w ON w.attempt_id=a.id AND w.test_id=a.test_id
            WHERE a.student_id=? AND COALESCE(a.status_deleted,0)=0
              AND t.test_type='upcoming' AND a.status IN ('submitted','checked')
            ORDER BY COALESCE(a.submitted_at,a.started_at,a.created_at) DESC, a.id DESC
            LIMIT 1");
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        $rank = (int)($row['rank_no'] ?? 0);
        return ($row && in_array($rank, [1,2,3], true)) ? $row : null;
    } catch (Throwable $e) {
        return null;
    }
}

function weekly_test_fetch_attempts_by_phone(string $phone, int $limit = 30): array
{
    weekly_test_ensure_schema();
    $phone = preg_replace('/\D+/', '', $phone);
    if ($phone === '') return [];
    $stmt = db()->prepare("SELECT a.*, t.title test_title, t.test_type, t.duration_minutes FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id WHERE COALESCE(a.status_deleted,0)=0 AND REPLACE(REPLACE(REPLACE(a.guest_phone,' ',''),'-',''),'+91','')=? ORDER BY a.id DESC LIMIT " . (int)$limit);
    $stmt->execute([$phone]);
    return $stmt->fetchAll();
}

function practice_google_translate_free(string $mode, string $input): ?array
{
    $input = trim($input);
    if ($input === '' || !function_exists('curl_init')) return null;
    $sl = $mode === 'hindi_to_english' ? 'hi' : ($mode === 'english_to_hindi' ? 'en' : 'auto');
    $tl = $mode === 'hindi_to_english' ? 'en' : ($mode === 'english_to_hindi' ? 'hi' : 'en');
    if (!in_array($mode, ['hindi_to_english','english_to_hindi'], true)) return null;
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&dt=t&sl=' . rawurlencode($sl) . '&tl=' . rawurlencode($tl) . '&q=' . rawurlencode($input);
    try {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>8, CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_USERAGENT=>'Mozilla/5.0']);
        $res = curl_exec($ch); $http=(int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if (!is_string($res) || $http < 200 || $http >= 300) return null;
        $data = json_decode($res, true);
        $text = '';
        if (isset($data[0]) && is_array($data[0])) foreach ($data[0] as $part) $text .= (string)($part[0] ?? '');
        $text = trim($text);
        if ($text === '') return null;
        return ['title'=>$mode==='hindi_to_english'?'Hindi to English':'English to Hindi','answer'=>$text,'note'=>'Translated by online Google translation endpoint. Internet is required on the server. Teacher review is still recommended for exams.','confidence'=>'Online','source'=>'google_translate'];
    } catch (Throwable $e) { return null; }
}


/* Phase 74: Dynamic Learning Roadmap Engine */
function roadmap_ensure_schema(): void
{
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    static $done = false;
    if ($done) return;
    $done = true;
    $marker = 'phase122_roadmap_schema_v1';
    try {
        $stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key=? LIMIT 1');
        $stmt->execute(['roadmap_schema_marker']);
        if ((string)($stmt->fetchColumn() ?: '') === $marker) return;
    } catch (Throwable $e) {}
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS roadmap_groups (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(180) NOT NULL,
            subtitle VARCHAR(255) NULL,
            description TEXT NULL,
            icon VARCHAR(20) NULL,
            color VARCHAR(40) NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_roadmap_groups (published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS roadmap_units (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            group_id INT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(180) NOT NULL,
            subtitle VARCHAR(255) NULL,
            description TEXT NULL,
            unit_type VARCHAR(60) NOT NULL DEFAULT 'lesson',
            level VARCHAR(80) NULL,
            target_url VARCHAR(500) NULL,
            icon VARCHAR(20) NULL,
            reward_points INT UNSIGNED NOT NULL DEFAULT 10,
            unlock_after_unit_id INT UNSIGNED NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_roadmap_units (group_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS roadmap_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            unit_id INT UNSIGNED NOT NULL DEFAULT 0,
            item_key VARCHAR(120) NULL,
            col_1 VARCHAR(255) NULL,
            col_2 VARCHAR(255) NULL,
            col_3 VARCHAR(255) NULL,
            col_4 VARCHAR(255) NULL,
            col_5 VARCHAR(255) NULL,
            col_6 VARCHAR(255) NULL,
            example_text TEXT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            status_deleted TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_roadmap_items (unit_id, published, status_deleted, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        db()->exec("CREATE TABLE IF NOT EXISTS student_roadmap_progress (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL DEFAULT 0,
            unit_id INT UNSIGNED NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'started',
            score INT UNSIGNED NOT NULL DEFAULT 0,
            completed_at DATETIME NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_student_unit (student_id, unit_id),
            INDEX idx_progress (student_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        db()->prepare("INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
            ->execute(['roadmap_schema_marker', $marker]);
    } catch (Throwable $e) {}
}


function roadmap_student_completed_ids(int $studentId): array
{
    if ($studentId <= 0) return [];
    roadmap_ensure_schema();
    try {
        $stmt = db()->prepare("SELECT unit_id FROM student_roadmap_progress WHERE student_id=? AND status='completed' ORDER BY completed_at ASC, id ASC");
        $stmt->execute([$studentId]);
        return array_values(array_unique(array_map('intval', array_column($stmt->fetchAll(), 'unit_id'))));
    } catch (Throwable $e) {
        return [];
    }
}


/** Return whether a student may open/complete a roadmap unit. */
function roadmap_student_unit_access(int $studentId, int $unitId): array
{
    if ($studentId <= 0 || $unitId <= 0) {
        return ['allowed'=>false, 'reason'=>'invalid', 'prerequisite_id'=>0];
    }
    roadmap_ensure_schema();
    try {
        $stmt = db()->prepare("SELECT id, unlock_after_unit_id FROM roadmap_units WHERE id=? AND published='Yes' AND status_deleted=0 LIMIT 1");
        $stmt->execute([$unitId]);
        $unit = $stmt->fetch();
        if (!$unit) return ['allowed'=>false, 'reason'=>'missing', 'prerequisite_id'=>0];

        $prerequisiteId = max(0, (int)($unit['unlock_after_unit_id'] ?? 0));
        if ($prerequisiteId <= 0) $prerequisiteId = roadmap_previous_unit_id($unitId);
        if ($prerequisiteId <= 0) return ['allowed'=>true, 'reason'=>'first_unit', 'prerequisite_id'=>0];

        $check = db()->prepare("SELECT 1 FROM student_roadmap_progress WHERE student_id=? AND unit_id=? AND status='completed' LIMIT 1");
        $check->execute([$studentId, $prerequisiteId]);
        if ($check->fetchColumn()) return ['allowed'=>true, 'reason'=>'prerequisite_complete', 'prerequisite_id'=>$prerequisiteId];
        return ['allowed'=>false, 'reason'=>'prerequisite_incomplete', 'prerequisite_id'=>$prerequisiteId];
    } catch (Throwable $e) {
        error_log('[roadmap-access] ' . $e->__toString());
        return ['allowed'=>false, 'reason'=>'error', 'prerequisite_id'=>0];
    }
}

function roadmap_mark_student_complete(int $studentId, int $unitId): bool
{
    if ($studentId <= 0 || $unitId <= 0) return false;
    roadmap_ensure_schema();
    try {
        $access = roadmap_student_unit_access($studentId, $unitId);
        if (empty($access['allowed'])) return false;
        $stmt = db()->prepare("INSERT INTO student_roadmap_progress (student_id,unit_id,status,score,completed_at) VALUES (?,?,'completed',100,NOW()) ON DUPLICATE KEY UPDATE status='completed',score=GREATEST(score,100),completed_at=COALESCE(completed_at,NOW())");
        return $stmt->execute([$studentId, $unitId]);
    } catch (Throwable $e) {
        return false;
    }
}

function roadmap_reset_student_progress(int $studentId): bool
{
    if ($studentId <= 0) return false;
    roadmap_ensure_schema();
    try {
        $stmt = db()->prepare('DELETE FROM student_roadmap_progress WHERE student_id=?');
        return $stmt->execute([$studentId]);
    } catch (Throwable $e) {
        return false;
    }
}

function roadmap_seed_defaults(): void
{
    // Default records belong to the canonical SQL import or an explicit upgrade run,
    // never to a normal public page request.
    if (defined('APP_ALLOW_SCHEMA_UPDATES') && !APP_ALLOW_SCHEMA_UPDATES) return;
    roadmap_ensure_schema();
    try {
        $count = (int)db()->query("SELECT COUNT(*) FROM roadmap_groups WHERE status_deleted=0")->fetchColumn();
        if ($count > 0) return;

        $groups = [
            ['Foundation','Start with basic English building blocks','Words, pronouns, demonstratives and basic meanings.','📘','#1a3565',1],
            ['Verb Mastery','Learn action words with forms','V1, V2, V3 and Hindi meaning for spoken practice.','⚡','#0f766e',2],
            ['Use-Based English','Daily spoken English structure','Has/have, should, can, could, must and similar uses.','🧩','#9a5b00',3],
            ['Tense Mastery','Speak in correct time','Present, past and future tense step by step.','⏱','#6d28d9',4],
        ];
        $groupIds = [];
        $stmt = db()->prepare("INSERT INTO roadmap_groups (title, subtitle, description, icon, color, sort_order) VALUES (?,?,?,?,?,?)");
        foreach ($groups as $g) {
            $stmt->execute($g);
            $groupIds[$g[0]] = (int)db()->lastInsertId();
        }

        $units = [
            ['Foundation','Basic Pronouns + This/That','I, Me, My + This, That, These, Those','Understand pronouns and pointing words in one foundation lesson.','meaning','Beginner','spoken-materials.php?roadmap=pronouns','🔤',10,1],
            ['Foundation','Demonstrative Words','This, That, These, Those','Learn pointing words for near, far, singular and plural objects.','meaning','Beginner','spoken-materials.php?roadmap=demonstrative','👉',10,2],
            ['Foundation','Daily Word Meaning','Common daily words','Learn daily-use word meanings before sentence practice.','meaning','Beginner','spoken-materials.php?goal=revision','📖',15,3],
            ['Verb Mastery','Verb Forms V1 V2 V3','Go Went Gone','Learn verb forms with Hindi meaning and daily examples.','verb','Beginner','spoken-materials.php?goal=speak','⚡',20,4],
            ['Use-Based English','Use of Is / Am / Are','Present identity and state','Learn simple, negative and question forms.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=is%20am%20are','✅',20,5],
            ['Use-Based English','Use of Has / Have','Possession and relation','Learn has/have in daily spoken English.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=has%20have','🧩',20,6],
            ['Use-Based English','Use of Was / Were','Past state','Learn was/were in simple and question sentences.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=was%20were','🕰',20,7],
            ['Use-Based English','Use of Has To / Have To','Compulsion / duty','Learn duty sentences: मुझे जाना है, उसे पढ़ना है.','use','Beginner','spoken-materials.php?goal=hindi_to_english&q=have%20to','🎯',20,8],
            ['Use-Based English','Use of Should / Should Have','Advice and past advice','Learn should, should not and should have patterns.','use','Intermediate','spoken-materials.php?goal=hindi_to_english&q=should','💡',25,9],
            ['Use-Based English','Use of Can / Could / Must','Ability, polite request, compulsion','Learn practical modal verbs for daily speaking.','use','Intermediate','spoken-materials.php?goal=hindi_to_english&q=can%20could%20must','🚀',25,10],
            ['Tense Mastery','Present Simple','Daily habits','Learn present simple with simple, negative and questions.','tense','Beginner','spoken-materials.php?goal=hindi_to_english&q=present%20simple','🌱',30,11],
            ['Tense Mastery','Present Continuous','Right now actions','Learn is/am/are + verb ing.','tense','Beginner','spoken-materials.php?goal=hindi_to_english&q=present%20continuous','🏃',30,12],
            ['Tense Mastery','Past Simple','Completed actions','Learn past daily sentences and questions.','tense','Intermediate','spoken-materials.php?goal=hindi_to_english&q=past%20simple','📌',30,13],
            ['Tense Mastery','Future Simple','Will / future actions','Learn future sentences for daily speaking.','tense','Intermediate','spoken-materials.php?goal=hindi_to_english&q=future','🔮',30,14],
        ];
        $stmt = db()->prepare("INSERT INTO roadmap_units (group_id, title, subtitle, description, unit_type, level, target_url, icon, reward_points, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $unitIds = [];
        foreach ($units as $u) {
            $gid = $groupIds[$u[0]] ?? 0;
            $stmt->execute([$gid,$u[1],$u[2],$u[3],$u[4],$u[5],$u[6],$u[7],$u[8],$u[9]]);
            $unitIds[$u[1]] = (int)db()->lastInsertId();
        }

        $pronouns = [
            ['I','I (मैं)','Me (मुझे)','My (मेरा/मेरी)','Mine (मेरा ही)','Myself (मैं खुद)'],
            ['We','We (हम)','Us (हमें)','Our (हमारा/हमारी)','Ours (हमारा ही)','Ourselves (हम खुद)'],
            ['You','You (तुम/आप)','You (तुम्हें)','Your (तुम्हारा/आपका)','Yours (तुम्हारा ही)','Yourself (तुम खुद)'],
            ['He','He (वह - Male)','Him (उसे)','His (उसका/उसकी)','His (उसका ही)','Himself (वह खुद)'],
            ['She','She (वह - Female)','Her (उसे)','Her (उसका/उसकी)','Hers (उसका ही)','Herself (वह खुद)'],
            ['It','It (यह - निर्जीव)','It (इसे)','Its (इसका/इसकी)','Not used','Itself (यह खुद)'],
            ['They','They (वे/उन्होंने)','Them (उन्हें)','Their (उनका/उनकी)','Theirs (उनका ही)','Themselves (वे खुद)'],
        ];
        $stmtItem = db()->prepare("INSERT INTO roadmap_items (unit_id, item_key, col_1, col_2, col_3, col_4, col_5, col_6, example_text, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $sort=1;
        foreach ($pronouns as $p) {
            $stmtItem->execute([$unitIds['Basic Pronouns + This/That'] ?? 0,$p[0],$p[1],$p[2],$p[3],$p[4],$p[5],'pronoun',"Subject | Object | Possessive Adjective | Possessive Pronoun | Reflexive",$sort++]);
        }
        $demo = [
            ['This','This','यह - पास के लिए','This is a book.','यह एक किताब है।','',''],
            ['That','That','वह - दूर के लिए','That is a boy.','वह एक लड़का है।','',''],
            ['These','These','ये - पास में एक से ज़्यादा','These are books.','ये किताबें हैं।','',''],
            ['Those','Those','वे - दूर में एक से ज़्यादा','Those are people.','वे लोग हैं।','',''],
        ];
        $sort=1;
        foreach ($demo as $d) {
            $stmtItem->execute([$unitIds['Demonstrative Words'] ?? 0,$d[0],$d[1],$d[2],$d[3],$d[4],$d[5],$d[6],"Word | Meaning | English Example | Hindi Example",$sort++]);
            $stmtItem->execute([$unitIds['Basic Pronouns + This/That'] ?? 0,$d[0],$d[1],$d[2],$d[3],$d[4],$d[5],'demonstrative',"Word | Meaning | English Example | Hindi Example",100+$sort]);
        }
    } catch (Throwable $e) {}
}

function roadmap_fetch_groups_with_units(): array
{
    roadmap_seed_defaults();
    $groups = [];
    try {
        $stmt = db()->prepare("SELECT * FROM roadmap_groups WHERE published='Yes' AND status_deleted=0 ORDER BY sort_order ASC, id ASC");
        $stmt->execute();
        $groups = $stmt->fetchAll();
        if (!$groups) {
            return [];
        }

        $groupIds = array_values(array_filter(array_map(static fn(array $group): int => (int)($group['id'] ?? 0), $groups)));
        $unitsByGroup = [];
        if ($groupIds) {
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $unitStmt = db()->prepare("SELECT * FROM roadmap_units WHERE group_id IN ($placeholders) AND published='Yes' AND status_deleted=0 ORDER BY group_id ASC, sort_order ASC, id ASC");
            $unitStmt->execute($groupIds);
            foreach ($unitStmt->fetchAll() as $unit) {
                $unitsByGroup[(int)$unit['group_id']][] = $unit;
            }
        }

        foreach ($groups as &$group) {
            $group['units'] = $unitsByGroup[(int)$group['id']] ?? [];
        }
        unset($group);
    } catch (Throwable $e) {}
    return $groups;
}

function roadmap_fetch_item_counts(array $unitIds): array
{
    roadmap_ensure_schema();
    $unitIds = array_values(array_unique(array_filter(array_map('intval', $unitIds), static fn(int $id): bool => $id > 0)));
    if (!$unitIds) {
        return [];
    }

    try {
        $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
        $stmt = db()->prepare("SELECT unit_id, COUNT(*) AS item_count FROM roadmap_items WHERE unit_id IN ($placeholders) AND published='Yes' AND status_deleted=0 GROUP BY unit_id");
        $stmt->execute($unitIds);
        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[(int)$row['unit_id']] = (int)$row['item_count'];
        }
        return $counts;
    } catch (Throwable $e) {
        return [];
    }
}

function roadmap_fetch_unit_items(int $unitId): array
{
    roadmap_ensure_schema();
    try {
        $stmt = db()->prepare("SELECT * FROM roadmap_items WHERE unit_id=? AND published='Yes' AND status_deleted=0 ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$unitId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function roadmap_normalize_title_key(string $value): string
{
    $value = strtolower(trim($value));
    $value = str_replace(['/', '\\', '&', '+', '-', '_'], ' ', $value);
    $value = preg_replace('/[^a-z0-9\s]+/i', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
}

function roadmap_fetch_unit_items_smart(array $unit): array
{
    $unitId = (int)($unit['id'] ?? 0);
    $items = $unitId > 0 ? roadmap_fetch_unit_items($unitId) : [];
    if ($items) {
        return $items;
    }

    /*
     * Fast duplicate-topic fallback.
     * Example:
     * frontend id=16 "Use of Has/Have"
     * admin data unit_id=6 "Use of Has / Have".
     */
    $title = trim((string)($unit['title'] ?? ''));
    $type = trim((string)($unit['unit_type'] ?? ''));
    if ($title === '') {
        return [];
    }

    try {
        $titleKey = roadmap_normalize_title_key($title);
        $words = array_values(array_filter(explode(' ', $titleKey), function ($w) {
            return strlen($w) >= 2 && !in_array($w, ['use','uses','of','the','and','for','with','practice','lesson','english','basic'], true);
        }));

        $unitWhere = [
            "status_deleted=0",
            "(published IS NULL OR published='' OR published IN ('Yes','Y','1'))"
        ];
        $unitParams = [];
        if ($type !== '') {
            $unitWhere[] = "unit_type=?";
            $unitParams[] = $type;
        }

        $titleParts = [
            "LOWER(REPLACE(REPLACE(REPLACE(title,'/',' '),'-',' '),'  ',' ')) = ?",
            "LOWER(title)=LOWER(?)"
        ];
        $titleParams = [$titleKey, $title];

        // Require meaningful words in title; this avoids loading unrelated units and keeps query fast.
        foreach (array_slice($words, 0, 4) as $word) {
            $titleParts[] = "LOWER(title) LIKE ?";
            $titleParams[] = '%' . strtolower($word) . '%';
        }

        $unitWhere[] = '(' . implode(' OR ', $titleParts) . ')';
        $unitParams = array_merge($unitParams, $titleParams);

        $unitSql = "SELECT id, title
                    FROM roadmap_units
                    WHERE " . implode(' AND ', $unitWhere) . "
                    ORDER BY CASE WHEN LOWER(title)=LOWER(?) THEN 0 ELSE 1 END, sort_order ASC, id ASC
                    LIMIT 5";
        $unitParams[] = $title;
        $uStmt = db()->prepare($unitSql);
        $uStmt->execute($unitParams);
        $matchedUnits = $uStmt->fetchAll();

        if (!$matchedUnits) {
            return [];
        }

        $ids = array_map(fn($u) => (int)$u['id'], $matchedUnits);
        $in = implode(',', array_fill(0, count($ids), '?'));

        $itemSql = "SELECT i.*, u.title AS source_unit_title, u.id AS source_unit_id
                    FROM roadmap_items i
                    INNER JOIN roadmap_units u ON u.id=i.unit_id
                    WHERE i.status_deleted=0
                      AND (i.published IS NULL OR i.published='' OR i.published IN ('Yes','Y','1'))
                      AND i.unit_id IN ($in)
                    ORDER BY u.sort_order ASC, i.sort_order ASC, i.id ASC
                    LIMIT 300";
        $iStmt = db()->prepare($itemSql);
        $iStmt->execute($ids);
        $rows = $iStmt->fetchAll();

        if ($rows) {
            return $rows;
        }
    } catch (Throwable $e) {
        return [];
    }

    return [];
}

function roadmap_admin_groups(): array
{
    roadmap_seed_defaults();
    try {
        return db()->query("SELECT * FROM roadmap_groups WHERE status_deleted=0 ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Throwable $e) { return []; }
}

function roadmap_admin_units(int $groupId = 0): array
{
    roadmap_seed_defaults();
    try {
        if ($groupId > 0) {
            $stmt = db()->prepare("SELECT u.*, g.title AS group_title FROM roadmap_units u LEFT JOIN roadmap_groups g ON g.id=u.group_id WHERE u.status_deleted=0 AND u.group_id=? ORDER BY u.sort_order ASC, u.id ASC");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll();
        }
        return db()->query("SELECT u.*, g.title AS group_title FROM roadmap_units u LEFT JOIN roadmap_groups g ON g.id=u.group_id WHERE u.status_deleted=0 ORDER BY g.sort_order ASC, u.sort_order ASC, u.id ASC")->fetchAll();
    } catch (Throwable $e) { return []; }
}


function roadmap_fetch_unit(int $unitId): ?array
{
    roadmap_seed_defaults();
    try {
        $stmt = db()->prepare("SELECT u.*, g.title AS group_title, g.icon AS group_icon, g.color AS group_color FROM roadmap_units u LEFT JOIN roadmap_groups g ON g.id=u.group_id WHERE u.id=? AND u.status_deleted=0 LIMIT 1");
        $stmt->execute([$unitId]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function roadmap_admin_items(int $unitId = 0): array
{
    roadmap_seed_defaults();
    try {
        if ($unitId > 0) {
            $stmt = db()->prepare("SELECT i.*, u.title AS unit_title FROM roadmap_items i LEFT JOIN roadmap_units u ON u.id=i.unit_id WHERE i.status_deleted=0 AND i.unit_id=? ORDER BY i.sort_order ASC, i.id ASC");
            $stmt->execute([$unitId]);
            return $stmt->fetchAll();
        }
        return db()->query("SELECT i.*, u.title AS unit_title FROM roadmap_items i LEFT JOIN roadmap_units u ON u.id=i.unit_id WHERE i.status_deleted=0 ORDER BY u.sort_order ASC, i.sort_order ASC, i.id ASC LIMIT 200")->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}


function roadmap_fetch_all_units_flat(): array
{
    roadmap_seed_defaults();
    try {
        return db()->query("SELECT u.*, g.title AS group_title, g.icon AS group_icon, g.color AS group_color
            FROM roadmap_units u
            LEFT JOIN roadmap_groups g ON g.id=u.group_id
            WHERE u.published='Yes' AND u.status_deleted=0
            ORDER BY g.sort_order ASC, u.sort_order ASC, u.id ASC")->fetchAll();
    } catch (Throwable $e) { return []; }
}

function roadmap_next_unit_id(int $currentId): int
{
    $units = roadmap_fetch_all_units_flat();
    $ids = array_map(fn($u) => (int)$u['id'], $units);
    $pos = array_search($currentId, $ids, true);
    if ($pos === false) return 0;
    return (int)($ids[$pos + 1] ?? 0);
}

function roadmap_previous_unit_id(int $currentId): int
{
    $units = roadmap_fetch_all_units_flat();
    $ids = array_map(fn($u) => (int)$u['id'], $units);
    $pos = array_search($currentId, $ids, true);
    if ($pos === false || $pos <= 0) return 0;
    return (int)($ids[$pos - 1] ?? 0);
}


function fetch_faculty_members(int $limit = 12): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM faculty_members WHERE published = ? ORDER BY sort_order ASC, id DESC LIMIT ' . (int)$limit);
        $stmt->execute(['Yes']);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetch_faculty_member(int $id): ?array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM faculty_members WHERE id = ? AND published = ? LIMIT 1');
        $stmt->execute([$id, 'Yes']);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function admin_fetch_faculty(int $limit = 200): array
{
    try {
        ensure_schema_updates();
        $stmt = db()->prepare('SELECT * FROM faculty_members ORDER BY sort_order ASC, id DESC LIMIT ' . (int)$limit);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

require_once __DIR__ . "/phase148_backend.php";
