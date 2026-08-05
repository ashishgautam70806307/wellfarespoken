<?php
ob_start();
ini_set('display_errors', '0');
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
if (!defined('APP_AI_TEACHER_ENABLED') || !APP_AI_TEACHER_ENABLED) {
    if (ob_get_length()) ob_clean();
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'This feature is currently unavailable.'], JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    if (!security_rate_limit('ai_teacher_' . ($_SERVER['REMOTE_ADDR'] ?? 'local'), 40, 60)) {
        http_response_code(429);
        if (ob_get_length()) ob_clean();
        echo json_encode(['success'=>false,'message'=>'Too many teacher requests. Please wait a minute.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        if (ob_get_length()) ob_clean();
        echo json_encode(['success'=>false,'message'=>'Invalid request.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        http_response_code(419);
        if (ob_get_length()) ob_clean();
        echo json_encode(['success'=>false,'message'=>'Session expired. Refresh once and try again.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $q = trim((string)($_POST['message'] ?? ''));
    if ($q === '') {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success'=>true,'reply'=>'Please ask me anything about English, translation, grammar, words, verbs, or spoken practice.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $norm = practice_normalize_text($q);
    $reply = practice_ai_teacher_answer($q);
    if (!$reply) {
        if (practice_has_hindi($q)) {
            $tr = practice_auto_translate_local('hindi_to_english', $q);
            if (($tr['confidence'] ?? '') === 'High') {
                $reply = "English: " . $tr['answer'] . "\n\nPractice: Say it 3 times slowly, then make one more sentence using the same pattern.";
            } else {
                $reply = "This sentence needs teacher review or an approved translation service. A weak word-by-word guess is not shown.";
            }
        } elseif (str_contains($norm, 'translate') || str_contains($norm, 'hindi')) {
            $clean = trim(preg_replace('/\b(translate|this sentence|in hindi|to hindi|meaning|ka matlab|hindi meaning)\b/i', ' ', $q));
            $tr = practice_auto_translate_local('english_to_hindi', $clean ?: $q);
            if (($tr['confidence'] ?? '') === 'High') {
                $reply = "Hindi: " . $tr['answer'] . "\n\nTip: Understand the full sentence, not only word meaning.";
            } else {
                $reply = "This sentence needs teacher review or an approved translation service. A weak word-by-word guess is not shown.";
            }
        } elseif (str_contains($norm, 'correct') || str_contains($norm, 'grammar') || preg_match('/\bi\s+(goes|has|is go|am go)\b/i', $q)) {
            $c = practice_auto_correct_local($q);
            $reply = "Correct sentence: " . $c['answer'] . "\n\nWhy: " . $c['note'];
        } elseif (str_contains($norm, 'verb')) {
            $reply = "Verb practice: learn Verb 1, Verb 2, Verb 3, and -ing form. Example: go - went - gone - going. Make 3 sentences: I go, I went, I have gone.";
        } elseif (str_contains($norm, 'tense')) {
            $reply = "Tense shortcut: Present Simple is used for habits. Example: I speak English every day. Present Continuous is used for now. Example: I am speaking English now.";
        } elseif (str_contains($norm, 'introduce') || str_contains($norm, 'introduction')) {
            $reply = "Self introduction sample: My name is ____. I am from ____. I am learning English to improve my communication and confidence. My goal is to speak English clearly in daily life.";
        } else {
            $c = practice_auto_correct_local($q);
            $reply = "Teacher reply: " . $c['answer'] . "\n\nNow speak this sentence aloud once.";
        }
    }
    if (ob_get_length()) ob_clean();
    echo json_encode(['success'=>true,'reply'=>$reply], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'AI teacher could not answer right now. Please try again.'], JSON_UNESCAPED_UNICODE);
}
