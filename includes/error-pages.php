<?php
/**
 * Render a branded HTML error page without bootstrapping the database or the
 * normal site shell. Keep API/AJAX callers responsible for their own JSON.
 */
function wf_show_error_page(int $code): never
{
    $allowed = [400,401,403,404,405,408,409,410,413,414,415,419,422,429,500,501,502,503,504];
    if (!in_array($code, $allowed, true)) $code = 500;
    $file = dirname(__DIR__) . '/errors/' . $code . '.php';
    if (is_file($file)) {
        require $file;
    }
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Request could not be completed.';
    exit;
}
