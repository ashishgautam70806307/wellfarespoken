<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
header('Content-Type: application/json; charset=utf-8');
function mpl_out(array $data, int $code = 200): void { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
try {
    material_ensure_schema();
    $collectionId = (int)($_GET['collection'] ?? 0);
    $default = material_default_practice_collection_id();
    if ($collectionId <= 0) $collectionId = $default;
    $unitId = (int)($_GET['unit'] ?? 0);
    $q = trim((string)($_GET['q'] ?? ''));
    if (mb_strlen($q) > 120) $q = mb_substr($q, 0, 120);
    $goal = strtolower(trim((string)($_GET['goal'] ?? 'speak')));
    $allowedGoals = ['speak','revision','hindi_to_english','english_to_hindi'];
    if (!in_array($goal, $allowedGoals, true)) $goal = 'speak';
    $direction = strtolower(trim((string)($_GET['direction'] ?? ($goal === 'english_to_hindi' ? 'english_to_hindi' : 'hindi_to_english'))));
    if (!in_array($direction, ['hindi_to_english','english_to_hindi'], true)) $direction = 'hindi_to_english';
    $limit = min(80, max(5, (int)($_GET['limit'] ?? 30)));
    $collections = fetch_material_practice_collections(300);
    $units = fetch_material_units($collectionId, 300);
    $pairs = [];

    $requiresLogin = false;
    if ($goal === 'revision') {
        $studentId = current_student_id();
        if ($studentId <= 0) {
            $requiresLogin = true;
        } elseif (column_exists('material_practice_attempts', 'student_id') && column_exists('material_practice_attempts', 'is_correct')) {
            $deletedFilter = column_exists('material_practice_attempts', 'status_deleted') ? ' AND COALESCE(a.status_deleted,0)=0' : '';
            $revisionSql = "SELECT p.*, latest.practice_direction
                FROM material_practice_attempts latest
                JOIN (
                    SELECT a.pair_id, MAX(a.id) AS latest_id
                    FROM material_practice_attempts a
                    WHERE a.student_id=?" . $deletedFilter . "
                    GROUP BY a.pair_id
                ) recent ON recent.latest_id=latest.id
                JOIN translation_pairs p ON p.id=recent.pair_id
                WHERE latest.is_correct=0 AND p.published='Yes' AND p.status_deleted=0
                ORDER BY latest.id DESC
                LIMIT " . (int)$limit;
            $revisionStmt = db()->prepare($revisionSql);
            $revisionStmt->execute([$studentId]);
            $pairs = $revisionStmt->fetchAll();
        }
    } else {
        $pairs = fetch_translation_pairs($collectionId, $unitId, $q, $limit);
        if (!$pairs && $collectionId !== $default && $default > 0) {
            $collectionId = $default;
            $units = fetch_material_units($collectionId, 300);
            $pairs = fetch_translation_pairs($collectionId, 0, $q, $limit);
        }
    }

    $items = [];
    foreach ($pairs as $p) {
        $items[] = [
            'id'=>(int)$p['id'],
            'hindi'=>(string)$p['hindi_text'],
            'english'=>(string)$p['english_text'],
            'roman'=>(string)($p['roman_text'] ?? ''),
            'topic'=>(string)($p['tense_name'] ?: 'Spoken Practice'),
            'tag'=>(string)($p['situation_tag'] ?: 'Practice'),
            'level'=>(string)($p['level'] ?: 'Beginner'),
            'explanation'=>(string)($p['explanation'] ?? ''),
            'sentence_type'=>(string)($p['sentence_type'] ?? 'Simple'),
            'teacher_hint'=>(string)($p['teacher_hint'] ?? ''),
            'direction'=>(string)($p['practice_direction'] ?? $direction),
        ];
    }
    mpl_out(['success'=>true,'csrf'=>csrf_token(),'collection_id'=>$collectionId,'unit_id'=>$unitId,'goal'=>$goal,'direction'=>$direction,'requires_login'=>$requiresLogin,'collections'=>$collections,'units'=>$units,'items'=>$items,'count'=>count($items)]);
} catch (Throwable $e) {
    error_log('[material-practice-list-api] ' . $e->__toString());
    mpl_out(['success'=>false,'message'=>'Could not load practice records. Run Admin > System Check once.'],500);
}
