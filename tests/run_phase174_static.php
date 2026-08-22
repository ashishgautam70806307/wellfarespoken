<?php
$root = dirname(__DIR__);
$read = static fn(string $path): string => (string)file_get_contents($root . '/' . $path);
$checks = [];
$check = static function(bool $ok, string $label) use (&$checks): void {
    $checks[] = [$ok, $label];
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};

$config = $read('includes/config.php');
$backend = $read('includes/phase148_backend.php');
$functions = $read('includes/functions.php');
$practice = $read('admin/practice-lab.php');
$password = $read('admin/password.php');
$login = $read('admin/login.php');
$practiceApi = $read('practice-session-api.php');
$online = $read('admin/online-classes.php');
$ht = $read('.htaccess');
$sw = $read('sw.js');
$env = $read('.env.example');

$check(strpos($config, "'name' => '',\n    'user' => '',\n    'pass' => '',") !== false, 'Live DB source-code fallback is blank');
$check(strpos($config, "ini_set('display_errors', '0')") !== false && strpos($config, "ini_set('log_errors', '1')") !== false, 'Production PHP error display is disabled and logging remains enabled');
$check(strpos($backend, "'practice-lab.php'=>'materials.manage'") !== false && strpos($backend, "'online-classes.php'=>'content.manage'") !== false, 'Previously unmapped Admin pages now have server-side permissions');
$check(strpos($practice, "\$_GET['delete']") === false && strpos($practice, "action\" value=\"delete_item") !== false, 'Practice Lab destructive delete is POST based and no longer uses GET tokens');
$check(strpos($practice, "admin_require_permission('settings.manage')") !== false, 'Practice Lab sensitive settings require settings.manage');
$check(strpos($practice, 'name="openai_api_key"') === false && strpos($practice, 'name="openai_endpoint"') === false, 'AI secret and endpoint are not editable from Admin HTML');
$check(strpos($functions, 'function practice_ai_api_key') !== false && strpos($functions, 'OPENAI_API_KEY') !== false, 'AI API key is read from server environment');
$check(strpos($functions, 'function practice_ai_endpoint') !== false && strpos($functions, 'OPENAI_ALLOWED_HOSTS') !== false && strpos($functions, "CURLPROTO_HTTPS") !== false, 'AI endpoint is HTTPS-only and host allowlisted');
$check(strpos($backend, 'function app_encrypt_secret') !== false && strpos($backend, "aes-256-gcm") !== false, 'Sensitive MFA values support authenticated encryption at rest');
$check(strpos($password, 'app_encrypt_secret($secret)') !== false && strpos($login, 'admin_mfa_secret_plain') !== false, 'MFA storage encrypts new secrets and login decrypts safely');
$check(strpos($functions, 'admin_mfa_gate_active') !== false && strpos($functions, "password.php?mfa_required=1") !== false, 'Protected owner MFA gate is enforced before Admin modules');
$check(strpos($practiceApi, "security_rate_limit('practice-session-check:'") !== false, 'Legacy practice answer checking is rate limited');
$check(strpos($functions, 'function app_safe_https_url') !== false && strpos($online, 'app_safe_https_url') !== false, 'External class links use centralized HTTPS URL validation');
$check(strpos($ht, '^(?:tests|tools)') !== false && strpos($config, 'Strict-Transport-Security') !== false, 'Development directories are web-denied and HTTPS HSTS is configured');
$check(strpos($ht, 'student-[A-Za-z0-9-]+') !== false, 'Old static student prototype pages are blocked from public direct access');
$swPhase = 0; if (preg_match('/wellfare-spoken-static-v(\d+)/', $sw, $m)) $swPhase = (int)$m[1];
$check($swPhase >= 174, 'Service Worker cache namespace preserves Phase 174 or newer');
$check(strpos($env, 'APP_SECRET_KEY=') !== false && strpos($env, 'OPENAI_API_KEY=') !== false && strpos($env, 'ADMIN_REQUIRE_OWNER_MFA=true') !== false, 'Environment template documents new security settings without real secrets');
$check(!is_file($root . '/spoken_phase167_replace_only.zip') && !is_file($root . '/spoken_phase121_materials_icon_table_ui_fix.zip'), 'Historical nested source ZIPs are removed from the project');
$check(!is_file($root . '/storage/private/.app-secret.key'), 'No generated application encryption key is packaged in source');

$fail = count(array_filter($checks, static fn(array $row): bool => !$row[0]));
echo PHP_EOL . (count($checks)-$fail) . ' passed, ' . $fail . ' failed' . PHP_EOL;
exit($fail ? 1 : 0);
