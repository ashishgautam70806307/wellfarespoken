<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
material_ensure_schema();
header('Content-Type: application/json; charset=utf-8');

function mat_json(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function mat_clean(string $v): string { return trim(preg_replace('/\s+/u', ' ', $v)); }
function mat_slug(string $v): string { $s = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $v), '-')); return $s ?: 'spoken-practice'; }
function mat_get_or_create_collection(string $title = 'Spoken Practice Lessons', string $level = 'Beginner to Advanced'): int {
    $title = mat_clean($title) ?: 'Spoken Practice Lessons';
    $slug = mat_slug($title);
    $stmt = db()->prepare('SELECT id FROM material_collections WHERE slug=? AND status_deleted=0 LIMIT 1');
    $stmt->execute([$slug]);
    $id = (int)($stmt->fetchColumn() ?: 0);
    if ($id) return $id;
    $stmt = db()->prepare('INSERT INTO material_collections (title, slug, category, level, description, sort_order, published) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$title, $slug, 'Spoken Practice', $level, 'Client-friendly spoken English practice content.', 1, 'Yes']);
    return (int)db()->lastInsertId();
}
function mat_get_or_create_unit(int $collectionId, string $topic, string $level = 'Beginner', string $instructions = ''): int {
    $topic = mat_clean($topic) ?: 'Daily Speaking Practice';
    $stmt = db()->prepare('SELECT id FROM material_units WHERE collection_id=? AND title=? AND status_deleted=0 LIMIT 1');
    $stmt->execute([$collectionId, $topic]);
    $id = (int)($stmt->fetchColumn() ?: 0);
    if ($id) return $id;
    $stmt = db()->prepare('INSERT INTO material_units (collection_id, title, unit_type, tense_name, level, instructions, sort_order, published) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$collectionId, $topic, 'spoken_practice', $topic, $level, $instructions ?: 'Listen, speak, write and check one sentence at a time.', 1, 'Yes']);
    return (int)db()->lastInsertId();
}
function mat_parse_csv_text(string $text): array {
    $rows = [];
    $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
    foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (str_contains($line, '|')) $parts = array_map('trim', explode('|', $line));
        else $parts = str_getcsv($line);
        $rows[] = array_map('trim', $parts);
    }
    return $rows;
}
function mat_xlsx_cells(string $path): array {
    if (!class_exists('ZipArchive')) return [];
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) return [];
    $shared = [];
    $ss = $zip->getFromName('xl/sharedStrings.xml');
    if ($ss !== false) {
        $xml = @simplexml_load_string($ss);
        if ($xml) {
            foreach ($xml->si as $si) {
                $txt = '';
                if (isset($si->t)) $txt .= (string)$si->t;
                if (isset($si->r)) foreach ($si->r as $r) $txt .= (string)$r->t;
                $shared[] = $txt;
            }
        }
    }
    $sheetName = 'xl/worksheets/sheet1.xml';
    $sheet = $zip->getFromName($sheetName);
    if ($sheet === false) { $zip->close(); return []; }
    $xml = @simplexml_load_string($sheet);
    $rows = [];
    if ($xml) {
        foreach ($xml->sheetData->row as $row) {
            $out = [];
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                preg_match('/([A-Z]+)/', $ref, $m);
                $colLetters = $m[1] ?? 'A';
                $col = 0;
                for ($i=0; $i<strlen($colLetters); $i++) $col = $col * 26 + (ord($colLetters[$i]) - 64);
                $col--;
                $type = (string)$c['t'];
                $val = isset($c->v) ? (string)$c->v : '';
                if ($type === 's') $val = $shared[(int)$val] ?? '';
                if ($type === 'inlineStr' && isset($c->is->t)) $val = (string)$c->is->t;
                $out[$col] = trim($val);
            }
            if ($out) {
                ksort($out); $max = max(array_keys($out));
                $line = [];
                for ($i=0; $i<=$max; $i++) $line[] = $out[$i] ?? '';
                if (implode('', $line) !== '') $rows[] = $line;
            }
        }
    }
    $zip->close();
    return $rows;
}
function mat_rows_to_records(array $rows, int $collectionId, int $defaultUnitId, string $defaultTopic, string $defaultLevel): array {
    $records = [];
    if (!$rows) return $records;
    $first = array_map(fn($x)=>strtolower(trim((string)$x)), $rows[0]);
    $hasHeader = false;
    foreach ($first as $h) {
        if (in_array($h, ['hindi','hindi sentence','hindi_text','english','english sentence','english_text','topic','tense','level'], true)) { $hasHeader = true; break; }
    }
    $map = [];
    if ($hasHeader) {
        foreach ($first as $i=>$h) {
            if (str_contains($h, 'hindi')) $map['hindi'] = $i;
            elseif (str_contains($h, 'english')) $map['english'] = $i;
            elseif (str_contains($h, 'roman')) $map['roman'] = $i;
            elseif (str_contains($h, 'topic') || str_contains($h, 'tense') || str_contains($h, 'grammar') || str_contains($h, 'use')) $map['topic'] = $i;
            elseif (str_contains($h, 'situation') || str_contains($h, 'tag')) $map['tag'] = $i;
            elseif (str_contains($h, 'level')) $map['level'] = $i;
            elseif (str_contains($h, 'accepted') && str_contains($h, 'hindi')) $map['accepted_hi'] = $i;
            elseif (str_contains($h, 'accepted')) $map['accepted'] = $i;
            elseif (str_contains($h, 'type') || str_contains($h, 'pattern')) $map['sentence_type'] = $i;
            elseif (str_contains($h, 'mistake')) $map['mistakes'] = $i;
            elseif (str_contains($h, 'hint')) $map['hint'] = $i;
            elseif (str_contains($h, 'mode')) $map['mode'] = $i;
            elseif (str_contains($h, 'explanation') || str_contains($h, 'note')) $map['explanation'] = $i;
        }
        array_shift($rows);
    }
    foreach ($rows as $r) {
        $hindi = $hasHeader ? ($r[$map['hindi'] ?? -1] ?? '') : ($r[0] ?? '');
        $english = $hasHeader ? ($r[$map['english'] ?? -1] ?? '') : ($r[1] ?? '');
        $roman = $hasHeader ? ($r[$map['roman'] ?? -1] ?? '') : ($r[2] ?? '');
        $topic = $hasHeader ? ($r[$map['topic'] ?? -1] ?? '') : ($r[3] ?? $defaultTopic);
        $tag = $hasHeader ? ($r[$map['tag'] ?? -1] ?? '') : ($r[4] ?? 'Daily Speaking');
        $level = $hasHeader ? ($r[$map['level'] ?? -1] ?? '') : ($r[5] ?? $defaultLevel);
        $accepted = $hasHeader ? ($r[$map['accepted'] ?? -1] ?? '') : ($r[6] ?? '');
        $acceptedHi = $hasHeader ? ($r[$map['accepted_hi'] ?? -1] ?? '') : '';
        $sentenceType = $hasHeader ? ($r[$map['sentence_type'] ?? -1] ?? '') : ($r[8] ?? 'Simple');
        $mistakes = $hasHeader ? ($r[$map['mistakes'] ?? -1] ?? '') : ($r[9] ?? '');
        $hint = $hasHeader ? ($r[$map['hint'] ?? -1] ?? '') : ($r[10] ?? '');
        $mode = $hasHeader ? ($r[$map['mode'] ?? -1] ?? '') : ($r[11] ?? 'smart');
        $explanation = $hasHeader ? ($r[$map['explanation'] ?? -1] ?? '') : ($r[7] ?? '');
        $hindi = mat_clean((string)$hindi); $english = mat_clean((string)$english);
        if ($hindi === '' || $english === '') continue;
        $topic = mat_clean((string)$topic) ?: $defaultTopic;
        $unitId = ($topic && $topic !== $defaultTopic) ? mat_get_or_create_unit($collectionId, $topic, $level ?: $defaultLevel) : $defaultUnitId;
        $records[] = compact('hindi','english','roman','topic','tag','level','accepted','acceptedHi','sentenceType','mistakes','hint','mode','explanation','unitId');
    }
    return $records;
}
function mat_render_sentence_rows(array $pairs): string {
    ob_start();
    if (!$pairs) {
        echo '<tr><td colspan="7" class="muted-text">No sentence found. Add one sentence or import Excel/CSV.</td></tr>';
    } else {
        foreach ($pairs as $p) {
            echo '<tr data-row="'.e((string)$p['id']).'">';
            echo '<td class="select-col"><input type="checkbox" class="sentence-select" value="'.e((string)$p['id']).'" aria-label="Select record"></td>';
            echo '<td><b>'.e($p['hindi_text']).'</b><small>'.e($p['roman_text'] ?? '').'</small></td>';
            echo '<td><b>'.e($p['english_text']).'</b><small>'.e($p['explanation'] ?? '').'</small></td>';
            echo '<td><span class="mini-chip">'.e($p['tense_name'] ?: 'Mixed').'</span><small>'.e($p['sentence_type'] ?? '').'</small></td>';
            echo '<td>'.e($p['situation_tag'] ?: 'Practice').'<small>'.e($p['teacher_hint'] ?? '').'</small></td>';
            echo '<td>'.e($p['level'] ?: 'Beginner').'<small>'.e($p['answer_match_mode'] ?? 'smart').'</small></td>';
            echo '<td class="material-action-cell"><div class="table-actions material-row-actions"><button type="button" class="btn btn-sm btn-soft admin-icon-action material-icon-action" title="Edit sentence" aria-label="Edit sentence" data-edit-sentence data-pair=\''.e(json_encode($p, JSON_UNESCAPED_UNICODE)).'\'><span>✎</span></button><button type="button" class="btn btn-sm btn-danger admin-icon-action material-icon-action" title="Delete sentence" aria-label="Delete sentence" data-delete-sentence="'.e((string)$p['id']).'"><span>🗑</span></button></div></td>';
            echo '</tr>';
        }
    }
    return ob_get_clean();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') mat_json(['success'=>false,'message'=>'Invalid request.'],405);
    if (!csrf_validate($_POST['csrf_token'] ?? '')) mat_json(['success'=>false,'message'=>'Session expired. Refresh and try again.'],419);
    $action = $_POST['action'] ?? '';

    if ($action === 'seed_use_library') {
        $count = material_seed_use_practice_library(true);
        mat_json(['success'=>true,'message'=>($count > 0 ? $count.' ready practice sentence(s) loaded.' : 'Practice library already loaded. No duplicate records added.'),'count'=>$count]);
    }

    if ($action === 'create_lesson') {
        $collectionId = mat_get_or_create_collection($_POST['collection_title'] ?? 'Spoken Practice Lessons', $_POST['level'] ?? 'Beginner to Advanced');
        $unitId = mat_get_or_create_unit($collectionId, $_POST['topic_name'] ?? 'Daily Speaking Practice', $_POST['level'] ?? 'Beginner', $_POST['instructions'] ?? '');
        mat_json(['success'=>true,'message'=>'Lesson/topic created successfully.','collection_id'=>$collectionId,'unit_id'=>$unitId]);
    }

    if ($action === 'save_sentence') {
        $id = (int)($_POST['id'] ?? 0);
        $collectionId = (int)($_POST['collection_id'] ?? 0) ?: mat_get_or_create_collection($_POST['collection_title'] ?? 'Spoken Practice Lessons');
        $unitId = (int)($_POST['unit_id'] ?? 0);
        $topic = mat_clean($_POST['tense_name'] ?? $_POST['topic_name'] ?? 'Daily Speaking Practice');
        if (!$unitId) $unitId = mat_get_or_create_unit($collectionId, $topic, $_POST['level'] ?? 'Beginner');
        $hindi = mat_clean($_POST['hindi_text'] ?? '');
        $english = mat_clean($_POST['english_text'] ?? '');
        if ($hindi === '' || $english === '') mat_json(['success'=>false,'message'=>'Hindi and English sentence both are required.'],422);
        $data = [$collectionId,$unitId,$hindi,$english,trim($_POST['roman_text'] ?? ''),$topic,trim($_POST['situation_tag'] ?? 'Daily Speaking'),trim($_POST['level'] ?? 'Beginner'),trim($_POST['explanation'] ?? ''),trim($_POST['accepted_english_answers'] ?? ''),trim($_POST['accepted_hindi_answers'] ?? ''),trim($_POST['sentence_type'] ?? 'Simple'),trim($_POST['common_mistakes'] ?? ''),trim($_POST['teacher_hint'] ?? ''),trim($_POST['answer_match_mode'] ?? 'smart'),'Yes'];
        if ($id) {
            $stmt = db()->prepare('UPDATE translation_pairs SET collection_id=?, unit_id=?, hindi_text=?, english_text=?, roman_text=?, tense_name=?, situation_tag=?, level=?, explanation=?, accepted_english_answers=?, accepted_hindi_answers=?, sentence_type=?, common_mistakes=?, teacher_hint=?, answer_match_mode=?, published=? WHERE id=?');
            $data[] = $id;
            $stmt->execute($data);
            $msg = 'Sentence updated.';
        } else {
            $stmt = db()->prepare('INSERT INTO translation_pairs (collection_id, unit_id, hindi_text, english_text, roman_text, tense_name, situation_tag, level, explanation, accepted_english_answers, accepted_hindi_answers, sentence_type, common_mistakes, teacher_hint, answer_match_mode, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute($data);
            $msg = 'Sentence added.';
        }
        mat_json(['success'=>true,'message'=>$msg]);
    }

    if ($action === 'delete_sentence') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) { $stmt = db()->prepare('UPDATE translation_pairs SET status_deleted=1 WHERE id=?'); $stmt->execute([$id]); }
        mat_json(['success'=>true,'message'=>'Sentence deleted safely.']);
    }

    if ($action === 'bulk_delete_sentences') {
        $ids = array_values(array_filter(array_map('intval', explode(',', (string)($_POST['ids'] ?? '')))));
        if (!$ids) mat_json(['success'=>false,'message'=>'No records selected.'],422);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare('UPDATE translation_pairs SET status_deleted=1 WHERE id IN (' . $placeholders . ')');
        $stmt->execute($ids);
        mat_json(['success'=>true,'message'=>count($ids).' record(s) deleted safely.']);
    }

    if ($action === 'import_sentences') {
        $collectionId = (int)($_POST['collection_id'] ?? 0) ?: mat_get_or_create_collection($_POST['collection_title'] ?? 'Spoken Practice Lessons');
        $topic = mat_clean($_POST['topic_name'] ?? $_POST['tense_name'] ?? 'Daily Speaking Practice');
        $level = mat_clean($_POST['level'] ?? 'Beginner');
        $unitId = (int)($_POST['unit_id'] ?? 0) ?: mat_get_or_create_unit($collectionId, $topic, $level);
        $rows = [];
        $pasted = trim($_POST['bulk_text'] ?? '');
        if ($pasted !== '') $rows = array_merge($rows, mat_parse_csv_text($pasted));
        if (!empty($_FILES['sentence_file']['tmp_name']) && is_uploaded_file($_FILES['sentence_file']['tmp_name'])) {
            $name = strtolower($_FILES['sentence_file']['name'] ?? '');
            if (str_ends_with($name, '.xlsx')) $rows = array_merge($rows, mat_xlsx_cells($_FILES['sentence_file']['tmp_name']));
            else $rows = array_merge($rows, mat_parse_csv_text((string)file_get_contents($_FILES['sentence_file']['tmp_name'])));
        }
        $records = mat_rows_to_records($rows, $collectionId, $unitId, $topic, $level);
        if (!$records) mat_json(['success'=>false,'message'=>'No valid rows found. Required: Hindi Sentence and English Sentence.'],422);
        $stmt = db()->prepare('INSERT INTO translation_pairs (collection_id, unit_id, hindi_text, english_text, roman_text, tense_name, situation_tag, level, explanation, accepted_english_answers, accepted_hindi_answers, sentence_type, common_mistakes, teacher_hint, answer_match_mode, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $count = 0;
        foreach ($records as $r) {
            $stmt->execute([$collectionId,$r['unitId'],$r['hindi'],$r['english'],$r['roman'],$r['topic'],$r['tag'] ?: 'Daily Speaking',$r['level'] ?: $level,$r['explanation'],$r['accepted'],$r['acceptedHi'] ?? '',$r['sentenceType'] ?? 'Simple',$r['mistakes'] ?? '',$r['hint'] ?? '',$r['mode'] ?? 'smart','Yes']);
            $count++;
        }
        mat_json(['success'=>true,'message'=>$count.' sentence(s) imported successfully.','count'=>$count]);
    }

    if ($action === 'list_sentences') {
        $collectionId = (int)($_POST['collection_id'] ?? 0);
        $unitId = (int)($_POST['unit_id'] ?? 0);
        $q = trim($_POST['q'] ?? '');
        $page = max(1, (int)($_POST['page'] ?? 1));
        $perPage = 25;
        $where = ['tp.status_deleted=0'];
        $params = [];
        if ($collectionId > 0) { $where[] = 'tp.collection_id=?'; $params[] = $collectionId; }
        if ($unitId > 0) { $where[] = 'tp.unit_id=?'; $params[] = $unitId; }
        if ($q !== '') {
            $where[] = '(tp.hindi_text LIKE ? OR tp.english_text LIKE ? OR tp.tense_name LIKE ? OR tp.situation_tag LIKE ? OR tp.level LIKE ?)';
            $like = '%'.$q.'%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        $whereSql = implode(' AND ', $where);
        $stmt = db()->prepare('SELECT COUNT(*) FROM translation_pairs tp WHERE '.$whereSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;
        $stmt = db()->prepare('SELECT tp.* FROM translation_pairs tp WHERE '.$whereSql.' ORDER BY tp.id DESC LIMIT '.$perPage.' OFFSET '.$offset);
        $stmt->execute($params);
        $pairs = $stmt->fetchAll();
        mat_json(['success'=>true,'html'=>mat_render_sentence_rows($pairs),'count'=>$total,'page'=>$page,'pages'=>$pages,'per_page'=>$perPage]);
    }

    if ($action === 'get_units') {
        $collectionId = (int)($_POST['collection_id'] ?? 0);
        $units = fetch_material_units($collectionId, 500);
        mat_json(['success'=>true,'units'=>$units]);
    }

    mat_json(['success'=>false,'message'=>'Unknown action.'],400);
} catch (Throwable $e) {
    error_log('[admin-materials-ajax] ' . $e->__toString());
    mat_json(['success'=>false,'message'=>'The materials request could not be completed. Check Admin > System Check.'],500);
}
