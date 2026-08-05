<?php
/**
 * Environment-aware application configuration.
 * Copy .env.example to .env on the server and update the values.
 */
if (!function_exists('app_load_env')) {
    function app_load_env(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '' || getenv($key) !== false) continue;
            if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
app_load_env(dirname(__DIR__) . '/.env');

if (!function_exists('app_env')) {
    function app_env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) $value = $_ENV[$key] ?? $default;
        return $value;
    }
}
if (!function_exists('app_env_bool')) {
    function app_env_bool(string $key, bool $default = false): bool
    {
        $value = app_env($key, $default ? 'true' : 'false');
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
if (!function_exists('app_request_is_https')) {
    function app_request_is_https(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
        if ((string)($_SERVER['SERVER_PORT'] ?? '') === '443') return true;
        if (app_env_bool('TRUST_PROXY_HEADERS', false)) {
            return strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0] ?? '')) === 'https';
        }
        return false;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    $secureCookie = app_env_bool('SESSION_SECURE_COOKIE', app_request_is_https());
    session_name((string)app_env('SESSION_NAME', 'wellfare_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => (string)app_env('SESSION_SAMESITE', 'Lax'),
    ]);
    session_start();
}

define('APP_ENV', (string)app_env('APP_ENV', 'production'));
define('APP_DEBUG', app_env_bool('APP_DEBUG', false));
define('APP_REMOTE_FONTS', app_env_bool('APP_REMOTE_FONTS', true));
define('APP_ALLOW_SCHEMA_UPDATES', app_env_bool('APP_ALLOW_SCHEMA_UPDATES', false));
define('APP_AI_TEACHER_ENABLED', app_env_bool('APP_AI_TEACHER_ENABLED', false));
define('TRUST_PROXY_HEADERS', app_env_bool('TRUST_PROXY_HEADERS', false));
define('APP_NAME', (string)app_env('APP_NAME', 'Well Fare English Spoken'));
define('APP_TAGLINE', (string)app_env('APP_TAGLINE', 'Speak English With Confidence'));
define('APP_URL', rtrim((string)app_env('APP_URL', ''), '/'));
define('APP_PHONE', (string)app_env('APP_PHONE', '+91 9506617831'));
define('APP_WHATSAPP', (string)app_env('APP_WHATSAPP', '919506617831'));
define('APP_EMAIL', (string)app_env('APP_EMAIL', 'info@example.com'));
define('APP_ADDRESS', (string)app_env('APP_ADDRESS', 'Station Road, Mariahu, Jaunpur, Uttar Pradesh'));
define('GOOGLE_MAP_URL', (string)app_env('GOOGLE_MAP_URL', 'https://www.google.com/maps/search/?api=1&query=Well+Fare+English+Spoken+Station+Road+Mariahu+Jaunpur'));

define('DB_HOST', (string)app_env('DB_HOST', 'localhost'));
define('DB_PORT', (string)app_env('DB_PORT', '3306'));
define('DB_NAME', (string)app_env('DB_NAME', 'wellfare_english'));
define('DB_USER', (string)app_env('DB_USER', 'root'));
define('DB_PASS', (string)app_env('DB_PASS', ''));
?>
