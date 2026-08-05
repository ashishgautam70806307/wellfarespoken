<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
function mpl_out(array $data, int $code = 200): void { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
try {
    material_ensure_schema();
    $collectionId = (int)($_GET['collection'] ?? 0);
    $default = material_default_practice_collection_id();
    if ($collectionId <= 0) $collectionId = $default;
    $unitId = (int)($_GET['unit'] ?? 0);
    $q = trim($_GET['q'] ?? '');
    $goal = $_GET['goal'] ?? 'speak';
    $direction = $_GET['direction'] ?? ($goal === 'english_to_hindi' ? 'english_to_hindi' : 'hindi_to_english');
    $limit = min(80, max(5, (int)($_GET['limit'] ?? 30)));
    $collections = fetch_material_practice_collections(300);
    $units = fetch_material_units($collectionId, 300);
    $pairs = fetch_translation_pairs($collectionId, $unitId, $q, $limit);
    if (!$pairs && $collectionId !== $default && $default > 0) {
        $collectionId = $default;
        $units = fetch_material_units($collectionId, 300);
        $pairs = fetch_translation_pairs($collectionId, 0, $q, $limit);
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
        ];
    }
    mpl_out(['success'=>true,'csrf'=>csrf_token(),'collection_id'=>$collectionId,'unit_id'=>$unitId,'goal'=>$goal,'direction'=>$direction,'collections'=>$collections,'units'=>$units,'items'=>$items,'count'=>count($items)]);
} catch (Throwable $e) {
    mpl_out(['success'=>false,'message'=>'Could not load practice records. Run Admin > System Check once.'],500);
}
