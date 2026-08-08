<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();

// Learning documents are private institute content. A logged-in student or
// authenticated administrator may request a published asset through this gate.
if (!is_student() && !is_admin()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Please sign in to access this learning file.');
}

if (is_student()) {
    require_student();
} else {
    require_admin();
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('File not found.');
}

try {
    $stmt = db()->prepare("SELECT id,file_path,original_name,file_type,published,status_deleted FROM material_assets WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $asset = $stmt->fetch();
    if (!$asset || ($asset['published'] ?? 'No') !== 'Yes' || (int)($asset['status_deleted'] ?? 1) !== 0) {
        http_response_code(404);
        exit('File not found.');
    }

    $path = material_asset_absolute_path((string)$asset['file_path']);
    if ($path === null || !is_file($path) || !is_readable($path)) {
        http_response_code(404);
        exit('File not found.');
    }

    $mime = function_exists('mime_content_type') ? (string)(mime_content_type($path) ?: '') : '';
    $allowedMimes = [
        'image/jpeg','image/png','image/webp','application/pdf','text/plain','text/csv','application/csv','application/vnd.ms-excel'
    ];
    if (!in_array($mime, $allowedMimes, true)) {
        http_response_code(415);
        exit('Unsupported file type.');
    }

    $download = isset($_GET['download']) && $_GET['download'] === '1';

    // Best-effort append-only access audit. File delivery must not fail only because
    // an audit insert is temporarily unavailable.
    if (table_exists('material_access_logs')) {
        try {
            $log = db()->prepare('INSERT INTO material_access_logs (asset_id,student_id,admin_id,access_type,ip_address,user_agent,created_at) VALUES (?,?,?,?,?,?,NOW())');
            $log->execute([
                (int)$asset['id'],
                is_student() ? current_student_id() : null,
                is_admin() ? (int)($_SESSION['admin_id'] ?? 0) ?: null : null,
                $download ? 'Download' : 'View',
                function_exists('client_ip') ? client_ip() : (string)($_SERVER['REMOTE_ADDR'] ?? ''),
                mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            ]);
        } catch (Throwable $auditError) {
            error_log('[material-file:audit] ' . $auditError->getMessage());
        }
    }

    $original = trim((string)($asset['original_name'] ?? ''));
    $filename = $original !== '' ? basename($original) : basename($path);
    $disposition = $download ? 'attachment' : 'inline';

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string)filesize($path));
    header("Content-Disposition: {$disposition}; filename*=UTF-8''" . rawurlencode($filename));
    header('Cache-Control: private, no-store, max-age=0');
    readfile($path);
    exit;
} catch (Throwable $e) {
    error_log('[material-file] ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to open this learning file.');
}
