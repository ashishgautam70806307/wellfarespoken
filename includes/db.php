<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/error-pages.php';

function db_fatal_response(string $message): never
{
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $script = strtolower(basename((string)($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '')));
    $isMachineRequest = str_contains($accept, 'application/json')
        || $requestedWith === 'xmlhttprequest'
        || str_contains($script, '-api.php')
        || str_contains($script, 'ajax.php');

    if ($isMachineRequest) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    wf_show_error_page(500);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (APP_RUNTIME_ENV === 'live' && (trim(DB_NAME) === '' || trim(DB_USER) === '')) {
        error_log('[database] Missing live DB credentials. Configure DB_LIVE_NAME, DB_LIVE_USER and DB_LIVE_PASS in .env.');
        db_fatal_response('Database service is not configured.');
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('[database] Connection failed for ' . DB_HOST . ':' . DB_PORT . '/' . DB_NAME . ' as ' . DB_USER . ' - ' . $e->getMessage());
        db_fatal_response('Database service is temporarily unavailable.');
    }

    return $pdo;
}
?>
