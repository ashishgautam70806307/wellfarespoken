<?php
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (APP_RUNTIME_ENV === 'live' && (trim(DB_NAME) === '' || trim(DB_USER) === '')) {
        error_log('[database] Missing live DB credentials. Configure DB_LIVE_NAME, DB_LIVE_USER and DB_LIVE_PASS in .env.');
        http_response_code(500);
        exit('Production database is not configured. Please set the live database credentials in the server .env file.');
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
        http_response_code(500);
        exit('Database connection failed. Please check configuration.');
    }

    return $pdo;
}
?>
