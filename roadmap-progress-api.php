<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
header('Content-Type: application/json; charset=utf-8');

function roadmap_api_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!is_student()) {
    roadmap_api_response(['success' => false, 'message' => 'Student login required.'], 401);
}

$studentId = current_student_id();
$action = strtolower(trim((string)($_GET['action'] ?? $_POST['action'] ?? 'list')));

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    roadmap_api_response([
        'success' => true,
        'completed_ids' => roadmap_student_completed_ids($studentId),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    roadmap_api_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!csrf_validate((string)($_POST['csrf_token'] ?? ''))) {
    roadmap_api_response(['success' => false, 'message' => 'Security check failed.'], 419);
}

if (!security_rate_limit('roadmap-progress:' . $studentId, 90, 3600)) {
    roadmap_api_response(['success' => false, 'message' => 'Too many requests. Please wait.'], 429);
}

if ($action === 'complete') {
    $unitId = max(0, (int)($_POST['unit_id'] ?? 0));
    if ($unitId <= 0 || !roadmap_mark_student_complete($studentId, $unitId)) {
        roadmap_api_response(['success' => false, 'message' => 'Could not save progress.'], 422);
    }
    roadmap_api_response([
        'success' => true,
        'completed_ids' => roadmap_student_completed_ids($studentId),
    ]);
}

if ($action === 'reset') {
    if (!roadmap_reset_student_progress($studentId)) {
        roadmap_api_response(['success' => false, 'message' => 'Could not reset progress.'], 422);
    }
    roadmap_api_response(['success' => true, 'completed_ids' => []]);
}

roadmap_api_response(['success' => false, 'message' => 'Invalid action.'], 400);
