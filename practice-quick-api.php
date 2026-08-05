<?php
ob_start();
ini_set('display_errors', '0');
require_once __DIR__ . '/includes/functions.php';
while (ob_get_level() > 1) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');

try {
    if (!security_rate_limit('quick_practice_' . ($_SERVER['REMOTE_ADDR'] ?? 'local'), 45, 60)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many practice requests. Please wait a minute and try again.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        http_response_code(419);
        echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh once and try again.']);
        exit;
    }

    $mode = trim($_POST['quick_mode'] ?? 'sentence_correction');
    $allowedModes = ['sentence_correction', 'hindi_to_english', 'english_to_hindi'];
    if (!in_array($mode, $allowedModes, true)) {
        $mode = 'sentence_correction';
    }

    $input = trim($_POST['quick_input'] ?? '');
    $result = free_ai_local_tool($mode, $input);
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['success' => true, 'result' => $result], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if (ob_get_length()) { ob_clean(); }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Practice tool could not process this request. Please run Admin > System Check once.'
    ], JSON_UNESCAPED_UNICODE);
}
