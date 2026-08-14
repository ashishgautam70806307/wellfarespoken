<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
header('Content-Type: application/json; charset=utf-8');
function material_api_out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    material_ensure_schema();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        material_api_out(['success' => false, 'message' => 'Invalid request method.'], 405);
    }
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        material_api_out(['success' => false, 'message' => 'Session expired. Refresh once and try again.'], 419);
    }
    $pairId = max(0, (int)($_POST['pair_id'] ?? 0));
    $direction = ($_POST['direction'] ?? 'hindi_to_english') === 'english_to_hindi' ? 'english_to_hindi' : 'hindi_to_english';
    $answer = trim((string)($_POST['answer'] ?? ''));
    $studentId = current_student_id();
    $rateIdentity = $studentId > 0 ? ('student-' . $studentId) : ('session-' . session_id());
    // Hands-free practice can legitimately create many short answer checks. Keep abuse
    // protection, but do not interrupt a normal continuous learning session.
    $practiceLimit = $studentId > 0 ? 600 : 300;
    if (!security_rate_limit('material-practice:' . $rateIdentity, $practiceLimit, 600)) {
        material_api_out(['success' => false, 'message' => 'Practice is moving very fast. Wait a few seconds and it will continue.'], 429);
    }
    if ($pairId <= 0) {
        material_api_out(['success' => false, 'message' => 'Practice sentence is missing.'], 422);
    }
    if ($answer === '') {
        material_api_out(['success' => false, 'message' => 'Please write your answer first.'], 422);
    }
    if (mb_strlen($answer) > 2000) {
        material_api_out(['success' => false, 'message' => 'Answer is too long.'], 413);
    }
    $stmt = db()->prepare("SELECT * FROM translation_pairs WHERE id=? AND published='Yes' AND status_deleted=0 LIMIT 1");
    $stmt->execute([$pairId]);
    $pair = $stmt->fetch();
    if (!$pair) {
        material_api_out(['success' => false, 'message' => 'Practice sentence not found.'], 404);
    }
    $result = evaluate_translation_pair($pair, $answer, $direction);
    save_material_attempt($pairId, $direction, $answer, $result);
    material_api_out(['success' => true, 'result' => $result, 'summary' => material_attempt_summary()]);
} catch (Throwable $e) {
    error_log('[material-practice-api] ' . $e->__toString());
    material_api_out(['success' => false, 'message' => 'Could not check this material practice. Run Admin > System Check once.'], 500);
}
