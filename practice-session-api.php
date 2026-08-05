<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

function api_out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    ensure_core_schema_columns();
    ensure_schema_updates();
    material_ensure_schema();

    $action = trim($_REQUEST['action'] ?? '');

    if ($action === 'lesson') {
        $lessonId = (int)($_GET['lesson_id'] ?? 0);
        $lesson = $lessonId ? fetch_practice_lesson($lessonId) : null;
        if (!$lesson) {
            api_out(['success' => false, 'message' => 'Lesson not found or not published.'], 404);
        }
        $questions = fetch_practice_questions((int)$lesson['id'], 50);
        $safeQuestions = [];
        foreach ($questions as $q) {
            $opts = [];
            foreach (['option_a','option_b','option_c','option_d'] as $opt) {
                if (!empty($q[$opt])) $opts[] = $q[$opt];
            }
            $safeQuestions[] = [
                'id' => (int)$q['id'],
                'question_type' => $q['question_type'],
                'question_text' => $q['question_text'],
                'level' => $q['level'],
                'tense_name' => $q['tense_name'],
                'options' => $opts,
                'has_voice' => practice_setting('browser_voice_enabled', 'Yes') === 'Yes'
            ];
        }
        api_out([
            'success' => true,
            'lesson' => [
                'id' => (int)$lesson['id'],
                'title' => $lesson['lesson_title'],
                'level' => $lesson['level'],
                'tense_name' => $lesson['tense_name'],
                'instructions' => $lesson['instructions'] ?: $lesson['short_description']
            ],
            'questions' => $safeQuestions,
            'summary' => practice_attempt_summary()
        ]);
    }

    if ($action === 'check') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            api_out(['success' => false, 'message' => 'Invalid request method.'], 405);
        }
        if (!csrf_validate($_POST['csrf_token'] ?? '')) {
            api_out(['success' => false, 'message' => 'Session expired. Refresh once and try again.'], 419);
        }
        $questionId = (int)($_POST['question_id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');
        if ($questionId <= 0) {
            api_out(['success' => false, 'message' => 'Question missing. Please choose a lesson again.'], 422);
        }
        if ($answer === '') {
            api_out(['success' => false, 'message' => 'Please write or speak your answer first.'], 422);
        }
        $stmt = db()->prepare("SELECT * FROM practice_questions WHERE id = ? AND published='Yes' AND status_deleted=0 LIMIT 1");
        $stmt->execute([$questionId]);
        $question = $stmt->fetch();
        if (!$question) {
            api_out(['success' => false, 'message' => 'Question not found or unpublished.'], 404);
        }
        $localResult = evaluate_practice_answer($question, $answer);
        $aiResult = practice_ai_feedback($question, $answer, $localResult);
        $result = merge_ai_practice_result($localResult, $aiResult);
        save_practice_attempt($questionId, $answer, $result);
        api_out([
            'success' => true,
            'result' => [
                'is_correct' => (bool)$result['is_correct'],
                'score' => (int)($result['score'] ?? 0),
                'feedback' => $result['feedback'] ?? '',
                'correct_answer' => $result['sample_answer'] ?? '',
                'corrected_answer' => $result['corrected_answer'] ?? ($result['sample_answer'] ?? ''),
                'natural_answer' => $result['natural_answer'] ?? ($result['sample_answer'] ?? ''),
                'explanation' => $result['explanation'] ?? '',
                'next_step' => $result['next_step'] ?? '',
                'ai_status' => $result['ai_status'] ?? 'off',
                'match_type' => $result['match_type'] ?? '',
                'accepted_answers' => $result['accepted_answers'] ?? [],
                'answer_help' => $result['answer_help'] ?? '',
                'ai_feedback' => $result['ai_feedback'] ?? '',
                'ai_model' => $result['ai_model'] ?? ''
            ],
            'summary' => practice_attempt_summary()
        ]);
    }

    api_out(['success' => false, 'message' => 'Unknown practice action.'], 400);
} catch (Throwable $e) {
    api_out(['success' => false, 'message' => 'Practice system error. Run Admin > System Check once, then try again.'], 500);
}
