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

if (!function_exists('app_runtime_host')) {
    function app_runtime_host(): string
    {
        $host = strtolower(trim((string)($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '')));
        if (str_starts_with($host, '[')) {
            $closingBracket = strpos($host, ']');
            return $closingBracket === false ? trim($host, '[]') : substr($host, 1, $closingBracket - 1);
        }
        if (substr_count($host, ':') === 1) {
            $host = explode(':', $host, 2)[0];
        }
        return rtrim($host, '.');
    }
}
if (!function_exists('app_runtime_is_local')) {
    function app_runtime_is_local(): bool
    {
        $localHosts = ['localhost', '127.0.0.1', '::1', 'host.docker.internal'];
        $host = app_runtime_host();
        if ($host !== '') {
            return in_array($host, $localHosts, true)
                || str_ends_with($host, '.localhost')
                || str_ends_with($host, '.test');
        }

        $appUrl = trim((string)app_env('APP_URL', ''));
        if ($appUrl !== '') {
            $appHost = strtolower((string)(parse_url($appUrl, PHP_URL_HOST) ?? ''));
            if ($appHost !== '') {
                return in_array($appHost, $localHosts, true)
                    || str_ends_with($appHost, '.localhost')
                    || str_ends_with($appHost, '.test');
            }
        }

        if (PHP_SAPI === 'cli-server') return true;

        $serverAddress = strtolower(trim((string)($_SERVER['SERVER_ADDR'] ?? '')));
        $remoteAddress = strtolower(trim((string)($_SERVER['REMOTE_ADDR'] ?? '')));
        if (in_array($serverAddress, ['127.0.0.1', '::1'], true)
            && ($remoteAddress === '' || in_array($remoteAddress, ['127.0.0.1', '::1'], true))) {
            return true;
        }

        // CLI has no reliable request host. APP_URL or APP_RUNTIME_MODE=live should be set on live cron/CLI jobs.
        return PHP_SAPI === 'cli';
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
    $sameSite = ucfirst(strtolower(trim((string)app_env('SESSION_SAMESITE', 'Lax'))));
    if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) $sameSite = 'Lax';
    if ($sameSite === 'None' && !$secureCookie) $sameSite = 'Lax';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => $sameSite,
    ]);
    session_start();
}

$runtimeMode = strtolower(trim((string)app_env('APP_RUNTIME_MODE', 'auto')));
if (!in_array($runtimeMode, ['auto', 'local', 'live'], true)) $runtimeMode = 'auto';
$appRuntimeIsLocal = $runtimeMode === 'local' || ($runtimeMode === 'auto' && app_runtime_is_local());
$dbConnectionMode = strtolower(trim((string)app_env('DB_CONNECTION_MODE', 'auto')));
if (!in_array($dbConnectionMode, ['auto', 'manual'], true)) $dbConnectionMode = 'auto';

define('APP_RUNTIME_MODE', $runtimeMode);
define('APP_RUNTIME_ENV', $appRuntimeIsLocal ? 'local' : 'live');
define('APP_ENV', (string)app_env('APP_ENV', $appRuntimeIsLocal ? 'local' : 'production'));
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
define('APP_EMAIL', (string)app_env('APP_EMAIL', 'wellfareenglishspoken@gmail.com'));
define('APP_ADDRESS', (string)app_env('APP_ADDRESS', 'Station Road, Mariahu, Jaunpur, Uttar Pradesh'));
define('GOOGLE_MAP_URL', (string)app_env('GOOGLE_MAP_URL', 'https://www.google.com/maps/search/?api=1&query=Well+Fare+English+Spoken+Station+Road+Mariahu+Jaunpur'));

define('STUDENT_REGISTRATION_MODE', strtolower(trim((string)app_env('STUDENT_REGISTRATION_MODE', 'open'))));
define('ADMIN_SETUP_KEY', (string)app_env('ADMIN_SETUP_KEY', ''));
define('ADMIN_MFA_ISSUER', (string)app_env('ADMIN_MFA_ISSUER', APP_NAME));
define('PRIVATE_STORAGE_PATH', rtrim((string)app_env('PRIVATE_STORAGE_PATH', dirname(__DIR__) . '/storage/private'), '/\\'));

$localDbDefaults = [
    'host' => 'localhost',
    'port' => '3306',
    'name' => 'wellfare_english',
    'user' => 'root',
    'pass' => '',
];
$liveDbDefaults = [
    // Production credentials must never live in source code. Configure DB_LIVE_* in .env.
    'host' => 'localhost',
    'port' => '3306',
    'name' => 'u790281974_wellfarespoken',
    'user' => 'u790281974_wellfarespoken',
    'pass' => '1yh3OewsWO=',
];

if ($dbConnectionMode === 'manual') {
    $dbDefaults = $appRuntimeIsLocal ? $localDbDefaults : $liveDbDefaults;
    $dbHost = (string)app_env('DB_HOST', $dbDefaults['host']);
    $dbPort = (string)app_env('DB_PORT', $dbDefaults['port']);
    $dbName = (string)app_env('DB_NAME', $dbDefaults['name']);
    $dbUser = (string)app_env('DB_USER', $dbDefaults['user']);
    $dbPass = (string)app_env('DB_PASS', $dbDefaults['pass']);
} elseif ($appRuntimeIsLocal) {
    // Auto mode uses the environment-specific profile only. Generic DB_* values are
    // intentionally ignored here so a local .env copied to live cannot select the
    // local database accidentally. Use DB_CONNECTION_MODE=manual for generic DB_*.
    $dbHost = (string)app_env('DB_LOCAL_HOST', $localDbDefaults['host']);
    $dbPort = (string)app_env('DB_LOCAL_PORT', $localDbDefaults['port']);
    $dbName = (string)app_env('DB_LOCAL_NAME', $localDbDefaults['name']);
    $dbUser = (string)app_env('DB_LOCAL_USER', $localDbDefaults['user']);
    $dbPass = (string)app_env('DB_LOCAL_PASS', $localDbDefaults['pass']);
} else {
    $dbHost = (string)app_env('DB_LIVE_HOST', $liveDbDefaults['host']);
    $dbPort = (string)app_env('DB_LIVE_PORT', $liveDbDefaults['port']);
    $dbName = (string)app_env('DB_LIVE_NAME', $liveDbDefaults['name']);
    $dbUser = (string)app_env('DB_LIVE_USER', $liveDbDefaults['user']);
    $dbPass = (string)app_env('DB_LIVE_PASS', $liveDbDefaults['pass']);
}

define('DB_CONNECTION_MODE', $dbConnectionMode);
define('DB_HOST', $dbHost);
define('DB_PORT', $dbPort);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
?>
