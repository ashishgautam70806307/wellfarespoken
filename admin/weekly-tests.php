<?php
$admin_page_final_styles = ['assets/css/phase159-admin-weekly-papers.css'];
require_once __DIR__ . '/_header.php';
weekly_test_ensure_schema();


function weekly_admin_is_ajax_request(): bool {
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}
function weekly_admin_post_reply(bool $success, string $message, array $extra = []): void {
    if (weekly_admin_is_ajax_request()) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(['success'=>$success,'message'=>$message], $extra), JSON_UNESCAPED_UNICODE);
        exit;
    }
    flash($success ? 'success' : 'error', $message);
    $testId = (int)($extra['test_id'] ?? ($_POST['test_id'] ?? $_POST['id'] ?? 0));
    $type = $_POST['test_type'] ?? ($_GET['type'] ?? '');
    $q=[]; if ($type) $q[]='type='.urlencode((string)$type); if ($testId>0) $q[]='test_id='.$testId;
    redirect('weekly-tests.php'.($q?'?'.implode('&',$q):'').'#setup');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['action'] ?? ''), ['save_test','publish_test_now','set_test_pending','upload_questions','clear_questions','save_question','delete_question','grade_attempt','reset_attempt'], true)) {
    try {
        if (!csrf_validate($_POST['csrf_token'] ?? '')) weekly_admin_post_reply(false, 'Security check failed. Refresh page once.');
        $action = $_POST['action'] ?? '';
        if ($action === 'save_test') {
            $id=(int)($_POST['id']??0);
            $title=trim((string)($_POST['title']??''));
            if($title==='') weekly_admin_post_reply(false, 'Test title required');
            $type=in_array($_POST['test_type']??'basic',['basic','previous','upcoming'],true)?$_POST['test_type']:'basic';
            $status=in_array($_POST['status']??'draft',['draft','active','archived'],true)?$_POST['status']:'draft';
            $login=($_POST['requires_login']??'No')==='Yes'?'Yes':'No';
            $duration=max(1,min(240,(int)($_POST['duration_minutes']??30)));
            $questions=max(1,min(200,(int)($_POST['total_questions']??30)));
            $marks=max(1,(int)($_POST['total_marks']??30));
            $shuffleQ=($_POST['shuffle_questions']??'Yes')==='No'?'No':'Yes';
            $shuffleO=($_POST['shuffle_options']??'Yes')==='No'?'No':'Yes';
            $warningLimit=max(1,min(20,(int)($_POST['warning_limit']??3)));
            $penaltyAfter=($_POST['penalty_after_warnings']??'Yes')==='No'?'No':'Yes';
            $penaltyPer=max(0,min(20,(float)($_POST['penalty_per_warning']??1)));
            $strictMode=($_POST['strict_exam_mode']??'Yes')==='No'?'No':'Yes';
            $autoSubmitWarn=($_POST['auto_submit_on_warning_limit']??'Yes')==='No'?'No':'Yes';
            $allowJump=($_POST['allow_question_jump']??'Yes')==='No'?'No':'Yes';
            $batchId=max(0,(int)($_POST['batch_id']??0));
            $batchLabel=trim((string)($_POST['batch_label']??''));
            $inst=trim((string)($_POST['instructions']??''));
            $starts=trim((string)($_POST['starts_at']??''));
            $ends=trim((string)($_POST['ends_at']??''));
            $starts=$starts!==''?str_replace('T',' ',$starts).':00':null;
            $ends=$ends!==''?str_replace('T',' ',$ends).':00':null;
            if($id>0){
                db()->prepare("UPDATE weekly_tests SET title=?,test_type=?,status=?,requires_login=?,duration_minutes=?,total_questions=?,total_marks=?,shuffle_questions=?,shuffle_options=?,warning_limit=?,penalty_after_warnings=?,penalty_per_warning=?,strict_exam_mode=?,auto_submit_on_warning_limit=?,allow_question_jump=?,batch_id=?,batch_label=?,instructions=?,starts_at=?,ends_at=? WHERE id=?")
                   ->execute([$title,$type,$status,$login,$duration,$questions,$marks,$shuffleQ,$shuffleO,$warningLimit,$penaltyAfter,$penaltyPer,$strictMode,$autoSubmitWarn,$allowJump,($batchId?:null),$batchLabel,$inst,$starts,$ends,$id]);
            } else {
                db()->prepare("INSERT INTO weekly_tests (title,test_type,status,requires_login,duration_minutes,total_questions,total_marks,shuffle_questions,shuffle_options,warning_limit,penalty_after_warnings,penalty_per_warning,strict_exam_mode,auto_submit_on_warning_limit,allow_question_jump,batch_id,batch_label,instructions,starts_at,ends_at,published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'Yes')")
                   ->execute([$title,$type,$status,$login,$duration,$questions,$marks,$shuffleQ,$shuffleO,$warningLimit,$penaltyAfter,$penaltyPer,$strictMode,$autoSubmitWarn,$allowJump,($batchId?:null),$batchLabel,$inst,$starts,$ends]);
                $id=(int)db()->lastInsertId();
            }
            if($status==='active') weekly_test_set_single_active_by_type($id, false);
            weekly_admin_post_reply(true, 'Test saved successfully. '.($status==='active'?'Only this '.$type.' paper is active now. Other '.$type.' papers moved to Pending.':'Status: '.ucfirst($status).'.'), ['test_id'=>$id, 'type'=>$type]);
        }
        if ($action === 'publish_test_now') {
            $testId=(int)($_POST['test_id'] ?? $_POST['id'] ?? 0);
            if($testId<=0) weekly_admin_post_reply(false, 'Select a test paper first.');
            weekly_test_publish_now($testId, true, true);
            $tstmt=db()->prepare("SELECT test_type FROM weekly_tests WHERE id=? LIMIT 1");
            $tstmt->execute([$testId]);
            $tt=(string)($tstmt->fetchColumn() ?: 'basic');
            weekly_admin_post_reply(true, 'Published for students. Test status is Active and all questions are Active.', ['test_id'=>$testId, 'type'=>$tt]);
        }
        if ($action === 'set_test_pending') {
            $testId=(int)($_POST['test_id'] ?? $_POST['id'] ?? 0);
            if($testId<=0) weekly_admin_post_reply(false, 'Select a test paper first.');
            db()->prepare("UPDATE weekly_tests SET status='draft', updated_at=NOW() WHERE id=? AND status_deleted=0")->execute([$testId]);
            $tstmt=db()->prepare("SELECT test_type FROM weekly_tests WHERE id=? LIMIT 1");
            $tstmt->execute([$testId]);
            $tt=(string)($tstmt->fetchColumn() ?: 'basic');
            weekly_admin_post_reply(true, 'Set to Pending/Draft. Students cannot start this paper now.', ['test_id'=>$testId, 'type'=>$tt]);
        }
        if ($action === 'upload_questions') {
            $testId=(int)($_POST['test_id']??0);
            if($testId<=0) weekly_admin_post_reply(false, 'Select test first');
            if(empty($_FILES['file']['tmp_name'])) weekly_admin_post_reply(false, 'Upload CSV or XLSX file');
            $fileName=(string)($_FILES['file']['name'] ?? '');
            $ext=strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if(!in_array($ext, ['csv','txt','xlsx'], true)) weekly_admin_post_reply(false, 'Please upload CSV or XLSX only. If your file is XLS, Save As CSV or XLSX.');
            $rows=weekly_test_parse_upload($_FILES['file']['tmp_name'], $fileName);
            if(!$rows) weekly_admin_post_reply(false, 'No rows found. Check columns: question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d');
            $added=weekly_test_import_rows($testId,$rows);
            if($added<=0) weekly_admin_post_reply(false, 'File read ho gayi, but no valid question imported. question_text and expected_answer columns check karein.');
            weekly_admin_post_reply(true, $added.' question(s) imported successfully', ['test_id'=>$testId]);
        }
        if ($action === 'clear_questions') {
            $testId=(int)($_POST['test_id']??0);
            if($testId<=0) weekly_admin_post_reply(false, 'Select test first');
            db()->prepare("UPDATE weekly_test_questions SET status_deleted=1, deleted_at=NOW() WHERE test_id=?")->execute([$testId]);
            weekly_admin_post_reply(true, 'Questions cleared', ['test_id'=>$testId]);
        }
        if ($action === 'save_question') {
            $id=(int)($_POST['id']??0); $testId=(int)($_POST['test_id']??0);
            $question=trim((string)($_POST['question_text']??''));
            if($testId<=0 || $question==='') weekly_admin_post_reply(false, 'Select test and write question');
            $type=trim((string)($_POST['question_type']??'hindi_to_english')) ?: 'hindi_to_english';
            $topic=trim((string)($_POST['topic_name']??''));
            $level=trim((string)($_POST['level']??'Beginner')) ?: 'Beginner';
            $expected=trim((string)($_POST['expected_answer']??''));
            $marks=max(0.25,(float)($_POST['marks']??1));
            $order=max(0,(int)($_POST['sort_order']??0));
            $opts=[trim((string)($_POST['option_a']??'')),trim((string)($_POST['option_b']??'')),trim((string)($_POST['option_c']??'')),trim((string)($_POST['option_d']??''))];
            $published=($_POST['published']??'Yes')==='No'?'No':'Yes';
            if($id>0){
                db()->prepare("UPDATE weekly_test_questions SET test_id=?,question_type=?,topic_name=?,level=?,question_text=?,expected_answer=?,option_a=?,option_b=?,option_c=?,option_d=?,marks=?,sort_order=?,published=? WHERE id=?")
                  ->execute([$testId,$type,$topic,$level,$question,$expected,$opts[0],$opts[1],$opts[2],$opts[3],$marks,$order,$published,$id]);
            } else {
                if($order<=0) $order=(int)db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM weekly_test_questions WHERE test_id=".$testId)->fetchColumn();
                db()->prepare("INSERT INTO weekly_test_questions (test_id,question_type,topic_name,level,question_text,expected_answer,option_a,option_b,option_c,option_d,marks,sort_order,published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
                  ->execute([$testId,$type,$topic,$level,$question,$expected,$opts[0],$opts[1],$opts[2],$opts[3],$marks,$order,$published]);
            }
            weekly_admin_post_reply(true, 'Question saved', ['test_id'=>$testId]);
        }
        if ($action === 'delete_question') {
            $id=(int)($_POST['id']??0); $testId=(int)($_POST['test_id']??0);
            db()->prepare("UPDATE weekly_test_questions SET status_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
            weekly_admin_post_reply(true, 'Question deleted', ['test_id'=>$testId]);
        }
        if ($action === 'grade_attempt') {
            $attemptId=(int)($_POST['attempt_id']??0);
            if($attemptId<=0) weekly_admin_post_reply(false, 'Invalid attempt');
            $scores=is_array($_POST['marks']??null)?$_POST['marks']:[];
            $notes=is_array($_POST['notes']??null)?$_POST['notes']:[];
            $total=0.0;
            $check=db()->prepare("SELECT ans.id, q.marks FROM weekly_test_answers ans JOIN weekly_test_questions q ON q.id=ans.question_id WHERE ans.id=? AND ans.attempt_id=? LIMIT 1");
            $upd=db()->prepare("UPDATE weekly_test_answers SET marks_awarded=?, is_correct=?, admin_note=? WHERE id=? AND attempt_id=?");
            foreach($scores as $answerId=>$mark){
                $answerId=(int)$answerId; if($answerId<=0) continue;
                $check->execute([$answerId,$attemptId]); $row=$check->fetch(); if(!$row) continue;
                $max=(float)($row['marks'] ?? 1); $mark=max(0,min($max,(float)$mark)); $total += $mark;
                $status = $mark <= 0 ? 'No' : ($mark >= $max ? 'Yes' : 'Review');
                $upd->execute([$mark,$status,(string)($notes[$answerId]??''),$answerId,$attemptId]);
            }
            $overall=trim((string)($_POST['admin_note']??''));
            db()->prepare("UPDATE weekly_test_attempts SET admin_score=?, status='checked', admin_note=? WHERE id=?")->execute([round($total,2),$overall,$attemptId]);
            weekly_admin_post_reply(true, 'Marks saved and result published: '.round($total,2), ['attempt_id'=>$attemptId]);
        }
        if ($action === 'reset_attempt') {
            $attemptId=(int)($_POST['attempt_id']??0);
            if($attemptId<=0) weekly_admin_post_reply(false, 'Invalid attempt');
            db()->prepare("UPDATE weekly_test_attempts SET status_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$attemptId]);
            weekly_admin_post_reply(true, 'Attempt hidden for admin. It will be permanently cleaned after 15 days.', ['attempt_id'=>$attemptId]);
        }
    } catch (Throwable $e) { error_log('[admin-weekly-tests] ' . $e->__toString()); weekly_admin_post_reply(false, 'Weekly Test change could not be saved. Check Admin > System Check.'); }
}

$typeLabel = ['basic'=>'Basic Test','previous'=>'Previous Test','upcoming'=>'Upcoming Test'];
$typeDesc = [
    'basic'=>'Open practice test for visitors. Warning only, no marks penalty.',
    'previous'=>'Missed weekly paper. Can run with mobile number and records are saved.',
    'upcoming'=>'Scheduled exam. Login/strict mode can be enabled.'
];

function weekly_admin_redirect_with_selected(int $testId = 0, string $extra = ''): void {
    $url = 'weekly-tests.php';
    $q = [];
    if ($testId > 0) $q[] = 'test_id=' . $testId;
    if ($extra !== '') $q[] = ltrim($extra, '&?');
    if ($q) $url .= '?' . implode('&', $q);
    redirect($url);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'bulk_question_action') {
        $ids = array_values(array_filter(array_map('intval', $_POST['ids'] ?? [])));
        $testIdReturn = (int)($_POST['test_id'] ?? 0);
        $bulkAction = $_POST['bulk_action'] ?? '';
        if (!$ids) {
            flash('error', 'Please select at least one question.');
            weekly_admin_redirect_with_selected($testIdReturn);
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($bulkAction === 'delete') {
            db()->prepare("UPDATE weekly_test_questions SET status_deleted=1, deleted_at=NOW() WHERE id IN ($in)")->execute($ids);
            flash('success', count($ids) . ' question(s) deleted.');
        } elseif ($bulkAction === 'activate') {
            db()->prepare("UPDATE weekly_test_questions SET published='Yes' WHERE id IN ($in)")->execute($ids);
            flash('success', count($ids) . ' question(s) activated.');
        } elseif ($bulkAction === 'deactivate') {
            db()->prepare("UPDATE weekly_test_questions SET published='No' WHERE id IN ($in)")->execute($ids);
            flash('success', count($ids) . ' question(s) deactivated.');
        } else {
            flash('error', 'Choose a valid bulk action.');
        }
        weekly_admin_redirect_with_selected($testIdReturn);
    }

    if ($action === 'bulk_delete_attempts') {
        $ids = array_values(array_filter(array_map('intval', $_POST['ids'] ?? [])));
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            db()->prepare("UPDATE weekly_test_attempts SET status_deleted=1, deleted_at=NOW() WHERE id IN ($in)")->execute($ids);
            flash('success', count($ids) . ' submission(s) hidden. It will be permanently cleaned after 15 days.');
        } else {
            flash('error', 'Please select at least one submission.');
        }
        redirect('weekly-tests.php');
    }
}

$tests = weekly_test_fetch_tests();
$testsByType = ['basic'=>[], 'previous'=>[], 'upcoming'=>[]];
foreach ($tests as $t) {
    $testsByType[$t['test_type']][] = $t;
}
$weeklyBatches = weekly_test_get_batches();

$selectedType = $_GET['type'] ?? '';
if (!in_array($selectedType, ['basic','previous','upcoming'], true)) $selectedType = '';
$selectedTestId = (int)($_GET['test_id'] ?? 0);
if ($selectedTestId <= 0) {
    $preferred = $selectedType ?: 'basic';
    $pool = $testsByType[$preferred] ?? [];
    if (!$pool) $pool = $tests;
    foreach ($pool as $t) {
        if (($t['status'] ?? '') === 'active') { $selectedTestId = (int)$t['id']; break; }
    }
    if ($selectedTestId <= 0 && $pool) $selectedTestId = (int)$pool[0]['id'];
}
$selectedTest = null;
foreach ($tests as $t) {
    if ((int)$t['id'] === $selectedTestId) { $selectedTest = $t; $selectedType = $t['test_type']; break; }
}
if ($selectedType === '') $selectedType = 'basic';

$editQ = null;
$editQId = (int)($_GET['edit_q'] ?? 0);
if ($editQId > 0) {
    $stmt = db()->prepare("SELECT * FROM weekly_test_questions WHERE id=? AND status_deleted=0 LIMIT 1");
    $stmt->execute([$editQId]);
    $editQ = $stmt->fetch();
    if ($editQ) $selectedTestId = (int)$editQ['test_id'];
}

$qSearch = trim((string)($_GET['q'] ?? ''));
$qStatus = $_GET['qstatus'] ?? 'all';
$qPage = max(1, (int)($_GET['qpage'] ?? 1));
$qPerPage = 15;
$qOffset = ($qPage - 1) * $qPerPage;
$qWhere = ["test_id=?", "status_deleted=0"];
$qParams = [$selectedTestId];
if ($qSearch !== '') {
    $qWhere[] = "(question_text LIKE ? OR expected_answer LIKE ? OR topic_name LIKE ? OR question_type LIKE ?)";
    $like = '%' . $qSearch . '%';
    array_push($qParams, $like, $like, $like, $like);
}
if ($qStatus === 'active') $qWhere[] = "published='Yes'";
if ($qStatus === 'inactive') $qWhere[] = "published='No'";
$questions = [];
$qTotal = 0;
if ($selectedTestId > 0) {
    $cnt = db()->prepare("SELECT COUNT(*) FROM weekly_test_questions WHERE " . implode(' AND ', $qWhere));
    $cnt->execute($qParams);
    $qTotal = (int)$cnt->fetchColumn();
    $stmtQ = db()->prepare("SELECT * FROM weekly_test_questions WHERE " . implode(' AND ', $qWhere) . " ORDER BY sort_order ASC, id ASC LIMIT {$qPerPage} OFFSET {$qOffset}");
    $stmtQ->execute($qParams);
    $questions = $stmtQ->fetchAll();
}
$qPages = max(1, (int)ceil($qTotal / $qPerPage));

$reviewId = (int)($_GET['review'] ?? 0);
$review = null; $reviewAnswers = [];
if ($reviewId > 0) {
    $review = weekly_test_attempt_detail($reviewId);
    $reviewAnswers = $review['answers'] ?? [];
}

$aSearch = trim((string)($_GET['aq'] ?? ''));
$aType = $_GET['atype'] ?? ($selectedType ?: 'all');
if (!in_array($aType, ['all','basic','previous','upcoming'], true)) $aType = $selectedType ?: 'all';
$aPage = max(1, (int)($_GET['apage'] ?? 1));
$aPerPage = 12;
$aOffset = ($aPage - 1) * $aPerPage;
$studentWhere = ["COALESCE(a.status_deleted,0)=0"];
$studentParams = [];
if ($selectedTestId > 0 && in_array($aType, ['basic','previous','upcoming'], true)) {
    $studentWhere[] = "a.test_id=?";
    $studentParams[] = $selectedTestId;
}
if ($aSearch !== '') {
    $studentWhere[] = "(a.guest_name LIKE ? OR a.guest_phone LIKE ? OR s.full_name LIKE ? OR s.phone LIKE ? OR t.title LIKE ?)";
    $like = '%' . $aSearch . '%';
    array_push($studentParams, $like, $like, $like, $like, $like);
}
if (in_array($aType, ['basic','previous','upcoming'], true)) {
    $studentWhere[] = "t.test_type=?";
    $studentParams[] = $aType;
}
$studentWhereSql = $studentWhere ? (' WHERE ' . implode(' AND ', $studentWhere)) : '';
$rawPhoneExpr = "COALESCE(NULLIF(s.phone,''), NULLIF(a.guest_phone,''), '')";
$cleanPhoneSql = "COALESCE(NULLIF(a.canonical_phone,''), RIGHT(REPLACE(REPLACE(REPLACE(REPLACE({$rawPhoneExpr},' ',''),'-',''),'+91',''),'+',''),10))";
$groupExpr = "CASE WHEN {$cleanPhoneSql}<>'' THEN {$cleanPhoneSql} WHEN a.student_id IS NOT NULL THEN CONCAT('student-',a.student_id) ELSE CONCAT('guest-',MIN(a.id)) END";
$nameExpr = "COALESCE(NULLIF(s.full_name,''), NULLIF(a.guest_name,''), 'Guest Student')";
$phoneExpr = $cleanPhoneSql;

$countSql = "SELECT COUNT(*) FROM (SELECT CASE WHEN {$cleanPhoneSql}<>'' THEN {$cleanPhoneSql} WHEN a.student_id IS NOT NULL THEN CONCAT('student-',a.student_id) ELSE CONCAT('guest-',a.id) END phone_key FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id LEFT JOIN students s ON s.id=a.student_id {$studentWhereSql} GROUP BY phone_key) x";
$cnt = db()->prepare($countSql);
$cnt->execute($studentParams);
$studentTotal = (int)$cnt->fetchColumn();
$studentPages = max(1, (int)ceil($studentTotal / $aPerPage));

$studentSql = "SELECT 
    CASE WHEN {$cleanPhoneSql}<>'' THEN {$cleanPhoneSql} WHEN a.student_id IS NOT NULL THEN CONCAT('student-',a.student_id) ELSE CONCAT('guest-',a.id) END phone_key,
    SUBSTRING_INDEX(GROUP_CONCAT({$nameExpr} ORDER BY a.id DESC SEPARATOR '||'),'||',1) display_name,
    SUBSTRING_INDEX(GROUP_CONCAT({$phoneExpr} ORDER BY a.id DESC SEPARATOR '||'),'||',1) phone_display,
    COUNT(*) total_attempts,
    SUM(CASE WHEN t.test_type='basic' THEN 1 ELSE 0 END) basic_count,
    SUM(CASE WHEN t.test_type='previous' THEN 1 ELSE 0 END) previous_count,
    SUM(CASE WHEN t.test_type='upcoming' THEN 1 ELSE 0 END) upcoming_count,
    SUM(CASE WHEN a.status='checked' THEN 1 ELSE 0 END) checked_count,
    SUM(CASE WHEN a.status='submitted' THEN 1 ELSE 0 END) pending_count,
    SUM(COALESCE(a.warning_count,0)) warning_total,
    MAX(COALESCE(a.submitted_at,a.started_at)) last_activity,
    MAX(a.id) last_attempt_id
    FROM weekly_test_attempts a
    JOIN weekly_tests t ON t.id=a.test_id
    LEFT JOIN students s ON s.id=a.student_id
    {$studentWhereSql}
    GROUP BY phone_key
    ORDER BY last_activity DESC, last_attempt_id DESC
    LIMIT {$aPerPage} OFFSET {$aOffset}";
$stmt = db()->prepare($studentSql);
$stmt->execute($studentParams);
$studentCards = $stmt->fetchAll();

$weeklyDash = [
    'tests' => count($tests),
    'active_tests' => 0,
    'questions' => 0,
    'active_questions' => 0,
    'copies' => $studentTotal,
    'pending' => 0,
    'checked' => 0,
    'warnings' => 0,
    'today' => 0,
];
foreach (($testsByType[$selectedType] ?? []) as $t) {
    if (($t['status'] ?? '') === 'active') $weeklyDash['active_tests']++;
}
$weeklyDash['tests'] = count($testsByType[$selectedType] ?? []);
try {
    $stmtDash = db()->prepare("SELECT COUNT(*) FROM weekly_test_questions q JOIN weekly_tests t ON t.id=q.test_id WHERE q.status_deleted=0 AND t.test_type=?");
    $stmtDash->execute([$selectedType]);
    $weeklyDash['questions'] = (int)($stmtDash->fetchColumn() ?: 0);
    $stmtDash = db()->prepare("SELECT COUNT(*) FROM weekly_test_questions q JOIN weekly_tests t ON t.id=q.test_id WHERE q.status_deleted=0 AND q.published='Yes' AND t.test_type=?");
    $stmtDash->execute([$selectedType]);
    $weeklyDash['active_questions'] = (int)($stmtDash->fetchColumn() ?: 0);
    $stmtDash = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id WHERE COALESCE(a.status_deleted,0)=0 AND a.status='submitted' AND t.test_type=?");
    $stmtDash->execute([$selectedType]);
    $weeklyDash['pending'] = (int)($stmtDash->fetchColumn() ?: 0);
    $stmtDash = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id WHERE COALESCE(a.status_deleted,0)=0 AND a.status='checked' AND t.test_type=?");
    $stmtDash->execute([$selectedType]);
    $weeklyDash['checked'] = (int)($stmtDash->fetchColumn() ?: 0);
    $stmtDash = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id WHERE COALESCE(a.status_deleted,0)=0 AND COALESCE(a.warning_count,0)>0 AND t.test_type=?");
    $stmtDash->execute([$selectedType]);
    $weeklyDash['warnings'] = (int)($stmtDash->fetchColumn() ?: 0);
    $stmtDash = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id WHERE COALESCE(a.status_deleted,0)=0 AND DATE(COALESCE(a.started_at,a.submitted_at))=CURDATE() AND t.test_type=?");
    $stmtDash->execute([$selectedType]);
    $weeklyDash['today'] = (int)($stmtDash->fetchColumn() ?: 0);
} catch (Throwable $e) {}

$paperCards = $testsByType[$selectedType] ?? [];
$paperAttemptStats = [];
$paperWinnerStats = [];
if ($paperCards) {
    $ids = array_map(fn($t)=>(int)$t['id'], $paperCards);
    $in = implode(',', array_fill(0, count($ids), '?'));
    try {
        $st = db()->prepare("SELECT test_id, COUNT(*) attempts, SUM(CASE WHEN status='submitted' THEN 1 ELSE 0 END) pending, SUM(CASE WHEN status='checked' THEN 1 ELSE 0 END) checked, SUM(COALESCE(warning_count,0)) warnings FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0 AND test_id IN ($in) GROUP BY test_id");
        $st->execute($ids);
        foreach($st->fetchAll() as $r){ $paperAttemptStats[(int)$r['test_id']] = $r; }
    } catch(Throwable $e) {}
    try {
        $st = db()->prepare("SELECT test_id, COUNT(*) winners, MAX(created_at) completed_at FROM weekly_test_winners WHERE test_id IN ($in) AND (published_until IS NULL OR published_until>=NOW()) GROUP BY test_id");
        $st->execute($ids);
        foreach($st->fetchAll() as $r){ $paperWinnerStats[(int)$r['test_id']] = $r; }
    } catch(Throwable $e) {}
}

function weekly_admin_paper_schedule_label(array $t): string {
    $from = !empty($t['starts_at']) ? date('d M, h:i A', strtotime((string)$t['starts_at'])) : '';
    $until = !empty($t['ends_at']) ? date('d M, h:i A', strtotime((string)$t['ends_at'])) : '';
    if ($from && $until) return $from . ' - ' . $until;
    if ($from) return 'From ' . $from;
    if ($until) return 'Until ' . $until;
    return 'Open now when published';
}

function weekly_admin_sample_link(string $type): string {
    return '../assets/downloads/weekly_test_sample_' . $type . '.csv';
}
?>
<div class="admin-page-head weekly-pro-head">
  <div>
    <span class="eyebrow">Exam CMS</span>
    <h1>Weekly Test Control</h1>
    <p>Manage Basic, Previous and Upcoming tests separately. Upload answer sheets, review copies and publish marks from one clean panel.</p>
    <div class="weekly-scope-chip">Current scope: <b><?= e($typeLabel[$selectedType] ?? 'Weekly Test') ?></b></div>
  </div>
  <div class="head-actions">
    <a class="btn btn-soft" href="students.php">Students</a>
    <?php if($selectedType==='upcoming'): ?><a class="btn btn-soft" href="upcoming-test-performance.php<?= $selectedTestId>0 ? '?test_id='.e((string)$selectedTestId) : '' ?>"><i class="fa-solid fa-ranking-star"></i> Performance</a><?php endif; ?>
    <a class="btn btn-primary" href="../weekly-test.php" target="_blank">Open Test Page</a>
  </div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-auto-toast="success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger" data-auto-toast="error"><?= e($msg) ?></div><?php endif; ?>
<script>document.body.classList.add('weekly-scope-<?= e($selectedType) ?>');</script>


<div class="weekly-flow-cards">
  <?php foreach(['basic','previous','upcoming'] as $type): $pool=$testsByType[$type] ?? []; $activeCount=0; $questionCount=0; foreach($pool as $t){ if(($t['status'] ?? '')==='active') $activeCount++; $questionCount += (int)($t['question_count'] ?? 0); } ?>
    <a class="weekly-flow-card <?= $selectedType===$type?'active':'' ?>" href="weekly-tests.php?type=<?= e($type) ?><?= $pool ? '&test_id='.e((string)$pool[0]['id']) : '' ?>">
      <span><?= e($typeLabel[$type]) ?></span>
      <b><?= e((string)count($pool)) ?> test<?= count($pool)===1?'':'s' ?></b>
      <small><?= e($typeDesc[$type]) ?></small>
      <em><?= e((string)$questionCount) ?> questions • <?= e((string)$activeCount) ?> active</em>
    </a>
  <?php endforeach; ?>
</div>

<div class="weekly-dashboard-grid" id="weekly-dashboard">
  <a class="weekly-dash-card blue" href="#setup"><i>1</i><strong><?= e((string)$weeklyDash['active_tests']) ?></strong><span>Active tests from <?= e((string)$weeklyDash['tests']) ?> total</span></a>
  <a class="weekly-dash-card" href="#question-bank"><i>Q</i><strong><?= e((string)$weeklyDash['active_questions']) ?></strong><span>Active questions / answer sheet rows</span></a>
  <a class="weekly-dash-card warn" href="#student-copies"><i>!</i><strong><?= e((string)$weeklyDash['pending']) ?></strong><span>Submitted copies pending checking</span></a>
  <a class="weekly-dash-card ok" href="#student-copies"><i>✓</i><strong><?= e((string)$weeklyDash['checked']) ?></strong><span>Checked and published copies</span></a>
  <a class="weekly-dash-card warn" href="#student-copies"><i>W</i><strong><?= e((string)$weeklyDash['warnings']) ?></strong><span>Attempts with activity warnings</span></a>
  <a class="weekly-dash-card blue" href="#student-copies"><i>S</i><strong><?= e((string)$weeklyDash['copies']) ?></strong><span>Student/mobile grouped records</span></a>
</div>

<div class="weekly-test-hub">
  <a class="weekly-hub-card" href="#setup"><span>01</span><div><b>Test Setup</b><small>Create Basic, Previous, Upcoming tests.</small></div></a>
  <a class="weekly-hub-card" href="#answer-sheet"><span>02</span><div><b>Answer Sheet</b><small>Upload CSV/XLSX master answers.</small></div></a>
  <a class="weekly-hub-card" href="#question-bank"><span>03</span><div><b>Question Bank</b><small>Edit, activate, deactivate, delete.</small></div></a>
  <a class="weekly-hub-card" href="#student-copies"><span>04</span><div><b>Student Copies</b><small>Open mobile-wise answer records.</small></div></a>
</div>

<div class="admin-card weekly-paper-board" id="paper-board">
  <div class="section-between paper-board-head">
    <div>
      <h2><?= e($typeLabel[$selectedType] ?? 'Test') ?> Batch / Test Papers</h2>
      <p class="muted small">Har batch/test paper card me status, schedule, questions aur copies ka short summary rahega. Published card light green rahega. Open/Edit se selected paper ka setup, upload, questions aur student copies scoped open honge.</p>
    </div>
    <div class="wf159-board-head-actions"><a class="btn btn-soft btn-sm" href="#setup">Create / Edit Paper</a><?php if($selectedType==='upcoming' && $selectedTestId>0): ?><a class="btn btn-primary btn-sm" target="_blank" rel="noopener" href="weekly-test-offline-paper.php?id=<?= e((string)$selectedTestId) ?>&mode=paper&autoprint=1"><i class="fa-solid fa-file-pdf"></i> Student PDF</a><a class="btn btn-soft btn-sm" target="_blank" rel="noopener" href="weekly-test-offline-paper.php?id=<?= e((string)$selectedTestId) ?>&mode=answer-key"><i class="fa-solid fa-key"></i> Answer Key</a><?php endif; ?></div>
  </div>
  <div class="paper-card-grid">
    <?php if(!$paperCards): ?><p class="muted">No paper yet. Create a test paper from Test Setup.</p><?php endif; ?>
    <?php foreach($paperCards as $pt):
      $pid=(int)$pt['id'];
      $ready=weekly_test_ready_reason($pt);
      $stat=$paperAttemptStats[$pid] ?? ['attempts'=>0,'pending'=>0,'checked'=>0,'warnings'=>0];
      $winner=$paperWinnerStats[$pid] ?? null;
      $cardClass=$ready==='ready'?'published':($ready==='scheduled_later'?'scheduled':($ready==='expired'?'closed':'pending'));
    ?>
    <div class="paper-batch-card <?= e($cardClass) ?> <?= $selectedTestId===$pid?'selected':'' ?>">
      <a class="paper-batch-main" href="weekly-test-paper.php?id=<?= e((string)$pid) ?>">
        <span class="paper-badge"><?= e($ready==='ready'?'Published':($ready==='scheduled_later'?'Scheduled':($ready==='expired'?'Closed':'Pending')) ) ?></span>
        <h3><?= e($pt['title']) ?></h3>
        <p><?= e($pt['batch_label'] ?: ($pt['batch_name'] ?? 'Common paper')) ?></p>
        <div class="paper-mini-stats">
          <em><?= e((string)($pt['question_count'] ?? 0)) ?> Q</em>
          <em><?= e((string)($pt['duration_minutes'] ?? 30)) ?> min</em>
          <em><?= e((string)($stat['attempts'] ?? 0)) ?> copies</em>
          <em><?= e((string)($stat['pending'] ?? 0)) ?> pending</em>
        </div>
        <small><?= e(weekly_admin_paper_schedule_label($pt)) ?></small>
        <?php if($winner): ?><strong class="winner-mini">Winner published • <?= e((string)$winner['winners']) ?> rank(s)</strong><?php endif; ?>
      </a>
      <div class="paper-card-actions paper-actions-grid">
        <a class="btn btn-soft btn-sm" href="weekly-tests.php?type=<?= e($selectedType) ?>&test_id=<?= e((string)$pid) ?>#setup">Edit</a>
        <a class="btn btn-soft btn-sm" href="weekly-tests.php?type=<?= e($selectedType) ?>&test_id=<?= e((string)$pid) ?>#question-bank">Questions</a>
        <?php if(($pt['test_type'] ?? '')==='upcoming'): ?>
          <a class="btn btn-soft btn-sm" target="_blank" rel="noopener" href="weekly-test-offline-paper.php?id=<?= e((string)$pid) ?>&mode=paper&autoprint=1"><i class="fa-solid fa-file-pdf"></i><span>Student Paper / PDF</span></a>
          <a class="btn btn-soft btn-sm" target="_blank" rel="noopener" href="weekly-test-offline-paper.php?id=<?= e((string)$pid) ?>&mode=answer-key"><i class="fa-solid fa-key"></i><span>Answer Key</span></a>
        <?php endif; ?>
        <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="publish_test_now"><input type="hidden" name="test_id" value="<?= e((string)$pid) ?>"><button class="btn btn-primary btn-sm" type="submit">Publish</button><span class="ajax-msg"></span></form>
        <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_test_pending"><input type="hidden" name="test_id" value="<?= e((string)$pid) ?>"><button class="btn btn-soft btn-sm" type="submit">Pending</button><span class="ajax-msg"></span></form>
        <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post" data-confirm="<?= e((($pt['test_type'] ?? '')==='upcoming') ? 'Complete this upcoming batch test and freeze 1st, 2nd and 3rd positions? All submitted copies should be checked first.' : 'Complete this batch test and publish top 3 winners for 2 days?') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="complete_batch_test"><input type="hidden" name="test_id" value="<?= e((string)$pid) ?>"><button class="btn btn-green btn-sm" type="submit"><?= (($pt['test_type'] ?? '')==='upcoming') ? 'Complete + Rank' : 'Complete' ?></button><span class="ajax-msg"></span></form>
        <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post" data-confirm="Hide/archive this batch paper? Records will remain in database for 15 days."><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="archive_test_paper"><input type="hidden" name="test_id" value="<?= e((string)$pid) ?>"><button class="btn btn-red btn-sm" type="submit">Delete</button><span class="ajax-msg"></span></form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<details class="admin-card weekly-guide" open>
  <summary>How to manage weekly tests in 3 simple steps</summary>
  <div class="weekly-guide-grid">
    <div><b>1. Create Test</b><span>Select Basic / Previous / Upcoming and set status active.</span></div>
    <div><b>2. Upload Answer Sheet</b><span>Use the CSV columns below. Expected Answer is the master answer sheet used for auto-checking.</span></div>
    <div><b>3. Review Copies</b><span>Student records appear as cards by mobile number. Open a card to see date-wise attempts.</span></div>
  </div>
  <div class="excel-format-box weekly-format">
    <b>CSV / Excel columns:</b>
    <code>question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d</code>
    <p>Multiple accepted answers: use new line, <b>||</b> or <b>;</b>. Example: <code>I cannot go || I can't go</code></p>
    <div class="sample-links">
      <a class="btn btn-soft btn-sm" href="<?= e(weekly_admin_sample_link('basic')) ?>" download>Download Basic Sample</a>
      <a class="btn btn-soft btn-sm" href="<?= e(weekly_admin_sample_link('previous')) ?>" download>Download Previous Sample</a>
      <a class="btn btn-soft btn-sm" href="<?= e(weekly_admin_sample_link('upcoming')) ?>" download>Download Upcoming Sample</a>
    </div>
  </div>
</details>

<div id="setup" class="admin-grid two-cols weekly-admin-grid weekly-compact-grid">
  <div class="admin-card">
    <h2>Test Setup</h2>
    <p class="muted small">Use this section to create or edit the selected test type.</p>
    <?php
      $selectedQuestionCount = (int)($selectedTest['question_count'] ?? 0);
      $selectedStatus = strtolower((string)($selectedTest['status'] ?? 'draft'));
      $selectedReadyReason = $selectedTest ? weekly_test_ready_reason($selectedTest) : 'pending';
      $selectedReadyLabel = $selectedReadyReason === 'ready' ? 'Published / Student Visible' : ($selectedReadyReason === 'no_questions' ? 'Pending: No Active Questions' : ($selectedReadyReason === 'scheduled_later' ? 'Scheduled: Not Open Yet' : ($selectedReadyReason === 'expired' ? 'Closed: Time Over' : 'Pending / Draft')));
    ?>
    <div class="paper-publish-panel <?= $selectedReadyReason === 'ready' ? 'ready' : 'pending' ?>">
      <div><span>Selected Paper</span><b><?= e($selectedTest['title'] ?? 'No paper selected') ?></b><small><?= e($typeLabel[$selectedType] ?? 'Test') ?> • <?= e((string)$selectedQuestionCount) ?> active question(s)</small></div>
      <div><span>Student Status</span><b><?= e($selectedReadyLabel) ?></b><small>Paper + questions dono Published hone chahiye. Publish Now se schedule block clear hota hai.</small></div>
      <form class="ajax-admin-form inline-paper-action" action="weekly-test-ajax.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="publish_test_now"><input type="hidden" name="test_id" value="<?= e((string)$selectedTestId) ?>">
        <button class="btn btn-primary" type="submit">Publish Now</button><span class="ajax-msg"></span>
      </form>
      <form class="ajax-admin-form inline-paper-action" action="weekly-test-ajax.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_test_pending"><input type="hidden" name="test_id" value="<?= e((string)$selectedTestId) ?>">
        <button class="btn btn-soft" type="submit">Set Pending</button><span class="ajax-msg"></span>
      </form>
    </div>
    <div class="weekly-test-active-helper">Delete button paper ko 15 din ke liye hide/archive karega, direct permanent delete nahi karega.<br>Frontend start ke liye <b>Paper Published / Active</b>, <b>Active Questions</b> aur <b>schedule open</b> required hai. Publish Now selected paper ko turant student visible bana deta hai aur same type ke other papers ko Pending kar deta hai.</div>
    <form class="ajax-admin-form form-grid compact-admin-form" action="weekly-test-ajax.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="save_test">
      <label>Existing Test
        <select name="id" id="weeklyEditSelect">
          <option value="0">Create new test</option>
          <?php foreach(($testsByType[$selectedType] ?? []) as $t): ?>
            <option value="<?= e((string)$t['id']) ?>" <?= (int)$t['id']===$selectedTestId?'selected':'' ?> data-title="<?= e($t['title']) ?>" data-type="<?= e($t['test_type']) ?>" data-status="<?= e($t['status']) ?>" data-login="<?= e($t['requires_login']) ?>" data-duration="<?= e((string)$t['duration_minutes']) ?>" data-questions="<?= e((string)$t['total_questions']) ?>" data-marks="<?= e((string)$t['total_marks']) ?>" data-shuffle-q="<?= e($t['shuffle_questions'] ?? 'Yes') ?>" data-shuffle-o="<?= e($t['shuffle_options'] ?? 'Yes') ?>" data-warning-limit="<?= e((string)($t['warning_limit'] ?? 3)) ?>" data-penalty-after="<?= e($t['penalty_after_warnings'] ?? 'Yes') ?>" data-penalty-per="<?= e((string)($t['penalty_per_warning'] ?? 1)) ?>" data-strict-mode="<?= e($t['strict_exam_mode'] ?? 'Yes') ?>" data-auto-submit-warn="<?= e($t['auto_submit_on_warning_limit'] ?? 'Yes') ?>" data-allow-jump="<?= e($t['allow_question_jump'] ?? 'Yes') ?>" data-starts-at="<?= e(!empty($t['starts_at']) ? date('Y-m-d\TH:i', strtotime($t['starts_at'])) : '') ?>" data-ends-at="<?= e(!empty($t['ends_at']) ? date('Y-m-d\TH:i', strtotime($t['ends_at'])) : '') ?>" data-batch-id="<?= e((string)($t['batch_id'] ?? 0)) ?>" data-batch-label="<?= e($t['batch_label'] ?? '') ?>" data-instructions="<?= e($t['instructions'] ?? '') ?>"><?= e($t['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Title <input name="title" id="testTitle" value="<?= e($selectedTest['title'] ?? '') ?>" placeholder="Example: Week 5 - Can Could Practice" required></label>
      <label>Type <select name="test_type" id="testType"><option value="basic" <?= $selectedType==='basic'?'selected':'' ?>>Basic Test</option><option value="previous" <?= $selectedType==='previous'?'selected':'' ?>>Previous Test</option><option value="upcoming" <?= $selectedType==='upcoming'?'selected':'' ?>>Upcoming Test</option></select></label>
      <label>Batch / Group <select name="batch_id" id="testBatchId"><option value="0">All batches / common test</option><?php foreach($weeklyBatches as $b): ?><option value="<?= e((string)$b['id']) ?>"><?= e($b['batch_name']) ?><?= !empty($b['timing']) ? ' • '.e($b['timing']) : '' ?></option><?php endforeach; ?></select></label>
      <label>Batch Label <input name="batch_label" id="testBatchLabel" placeholder="Example: Morning Batch / Batch A"></label>
      <label>Paper Status <select name="status" id="testStatus"><option value="draft">Pending / Draft</option><option value="active">Published / Active</option><option value="archived">Archived</option></select></label>
      <label>Available From <input name="starts_at" id="testStartsAt" type="datetime-local"></label>
      <label>Available Until <input name="ends_at" id="testEndsAt" type="datetime-local"></label>
      <label>Login Required <select name="requires_login" id="testLogin"><option>No</option><option>Yes</option></select></label>
      <label>Duration <input name="duration_minutes" id="testDuration" type="number" value="10" min="1" max="240"></label>
      <label>Total Questions <input name="total_questions" id="testQuestions" type="number" value="10" min="1" max="200"></label>
      <label>Total Marks <input name="total_marks" id="testMarks" type="number" value="10" min="1"></label>
      <label>Shuffle Questions <select name="shuffle_questions" id="testShuffleQ"><option value="Yes">Yes</option><option value="No">No</option></select></label>
      <label>Shuffle Options <select name="shuffle_options" id="testShuffleO"><option value="Yes">Yes</option><option value="No">No</option></select></label>
      <label>Warning Limit <input name="warning_limit" id="testWarningLimit" type="number" value="3" min="1" max="20"></label>
      <label>Penalty After Warnings <select name="penalty_after_warnings" id="testPenaltyAfter"><option value="Yes">Yes</option><option value="No">No</option></select></label>
      <label>Penalty Per Warning <input name="penalty_per_warning" id="testPenaltyPer" type="number" step="0.25" value="1" min="0" max="20"></label>
      <label>Strict Browser Mode <select name="strict_exam_mode" id="testStrictMode"><option value="Yes">Yes</option><option value="No">No</option></select></label>
      <label>Auto Submit At Warning Limit <select name="auto_submit_on_warning_limit" id="testAutoSubmitWarn"><option value="Yes">Yes</option><option value="No">No</option></select></label>
      <label>Allow Question Jump <select name="allow_question_jump" id="testAllowJump"><option value="Yes">Yes</option><option value="No">No</option></select></label>
      <label class="full">Instructions <textarea name="instructions" id="testInstructions" rows="2" placeholder="Write short rules for students..."><?= e($selectedTest['instructions'] ?? '') ?></textarea></label>
      <button class="btn btn-primary" type="submit">Save Test</button><span class="ajax-msg"></span>
    </form>
  </div>

  <div class="admin-card" id="answer-sheet">
    <h2>Upload Answer Sheet / Questions</h2>
    <p class="muted small">Select exact test, upload CSV/XLSX. The <b>expected_answer</b> column is the answer sheet.</p>
    <form class="ajax-admin-form form-stack compact-upload-form" action="weekly-test-ajax.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="upload_questions">
      <label>Select Test <select name="test_id" required><?php foreach(($testsByType[$selectedType] ?? []) as $t): ?><option value="<?= e((string)$t['id']) ?>" <?= (int)$t['id']===$selectedTestId?'selected':'' ?>><?= e($t['title']) ?></option><?php endforeach; ?></select></label>
      <label>CSV / XLSX File <input type="file" name="file" accept=".csv,.xlsx,.txt" required></label>
      <div class="sample-links">
        <a href="<?= e(weekly_admin_sample_link('basic')) ?>" download>Basic sample</a>
        <a href="<?= e(weekly_admin_sample_link('previous')) ?>" download>Previous sample</a>
        <a href="<?= e(weekly_admin_sample_link('upcoming')) ?>" download>Upcoming sample</a>
      </div>
      <button class="btn btn-primary" type="submit">Upload Questions</button><span class="ajax-msg"></span>
    </form>
    <?php if($selectedTestId): ?>
      <form class="ajax-admin-form clear-form" action="weekly-test-ajax.php" method="post" data-confirm="Clear all questions from selected test?">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="clear_questions"><input type="hidden" name="test_id" value="<?= e((string)$selectedTestId) ?>">
        <button class="btn btn-danger btn-sm" type="submit">Clear Selected Test Questions</button><span class="ajax-msg"></span>
      </form>
    <?php endif; ?>
    <?php if(in_array($selectedType, ['previous','upcoming'], true)): ?>
      <form class="ajax-admin-form demo-test-form" action="weekly-test-ajax.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="create_demo_batch_tests">
        <input type="hidden" name="test_type" value="<?= e($selectedType) ?>">
        <button class="btn btn-soft btn-sm" type="submit">Create 2 Demo Batch Papers</button>
        <small>Testing ke liye Morning/Evening batch paper with 30 active questions ban jayega.</small>
        <span class="ajax-msg"></span>
      </form>
    <?php endif; ?>
  </div>
</div>

<div id="question-bank" class="admin-card">
  <div class="section-between">
    <div>
      <h2>Question Bank<?= $selectedTest ? ': '.e($selectedTest['title']) : '' ?></h2>
      <p class="muted small">This is the answer sheet. Activate/deactivate, edit, delete and paginate questions here.</p>
    </div>
    <div class="head-actions">
      <?php foreach(['basic','previous','upcoming'] as $type): if(!empty($testsByType[$type])): ?>
        <a class="btn btn-soft btn-sm <?= $selectedType===$type?'active':'' ?>" href="weekly-tests.php?type=<?= e($type) ?>&test_id=<?= e((string)$testsByType[$type][0]['id']) ?>"><?= e($typeLabel[$type]) ?></a>
      <?php endif; endforeach; ?>
    </div>
  </div>
  <?php if($selectedTestId<=0): ?><p class="muted">Create/select test first.</p><?php else: ?>
  <details class="question-editor" <?= $editQ ? 'open' : '' ?>>
    <summary><?= $editQ ? 'Edit selected question' : 'Add one question manually' ?></summary>
    <form class="ajax-admin-form form-grid compact-admin-form" action="weekly-test-ajax.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_question"><input type="hidden" name="id" value="<?= e((string)($editQ['id'] ?? 0)) ?>">
      <label>Test <select name="test_id"><?php foreach(($testsByType[$selectedType] ?? []) as $t): ?><option value="<?= e((string)$t['id']) ?>" <?= (int)$t['id']===(int)($editQ['test_id'] ?? $selectedTestId)?'selected':'' ?>><?= e($t['title']) ?></option><?php endforeach; ?></select></label>
      <label>Question Type <select name="question_type"><?php foreach(['hindi_to_english'=>'Hindi to English','english_to_hindi'=>'English to Hindi','correction'=>'Correction','short_answer'=>'Short Answer','mcq'=>'MCQ'] as $k=>$v): ?><option value="<?= e($k) ?>" <?= (($editQ['question_type'] ?? '')===$k)?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
      <label>Topic / Use <input name="topic_name" value="<?= e($editQ['topic_name'] ?? '') ?>" placeholder="is/am/are, can, present simple..."></label>
      <label>Level <select name="level"><?php foreach(['Beginner','Basic','Intermediate','Advanced'] as $lvl): ?><option <?= (($editQ['level'] ?? 'Beginner')===$lvl)?'selected':'' ?>><?= e($lvl) ?></option><?php endforeach; ?></select></label>
      <label>Marks <input name="marks" type="number" step="0.25" value="<?= e((string)($editQ['marks'] ?? 1)) ?>"></label>
      <label>Sort <input name="sort_order" type="number" value="<?= e((string)($editQ['sort_order'] ?? 0)) ?>"></label>
      <label>Status <select name="published"><option value="Yes" <?= (($editQ['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Active</option><option value="No" <?= (($editQ['published'] ?? 'Yes')==='No')?'selected':'' ?>>Inactive</option></select></label>
      <label class="full">Question <textarea name="question_text" rows="2" required placeholder="मैं रोज अंग्रेजी बोलता हूँ।"><?= e($editQ['question_text'] ?? '') ?></textarea></label>
      <label class="full">Expected / Accepted Answers <textarea name="expected_answer" rows="2" placeholder="I speak English every day. || I speak English daily."><?= e($editQ['expected_answer'] ?? '') ?></textarea></label>
      <label>Option A <input name="option_a" value="<?= e($editQ['option_a'] ?? '') ?>"></label><label>Option B <input name="option_b" value="<?= e($editQ['option_b'] ?? '') ?>"></label><label>Option C <input name="option_c" value="<?= e($editQ['option_c'] ?? '') ?>"></label><label>Option D <input name="option_d" value="<?= e($editQ['option_d'] ?? '') ?>"></label>
      <button class="btn btn-primary" type="submit"><?= $editQ ? 'Update Question' : 'Save Question' ?></button><?php if($editQ): ?><a class="btn btn-soft" href="weekly-tests.php?test_id=<?= e((string)$selectedTestId) ?>">Cancel Edit</a><?php endif; ?><span class="ajax-msg"></span>
    </form>
  </details>

  <div class="weekly-bank-tools">
    <form method="get" class="weekly-search-form">
      <input type="hidden" name="type" value="<?= e($selectedType) ?>"><input type="hidden" name="test_id" value="<?= e((string)$selectedTestId) ?>">
      <input type="search" name="q" value="<?= e($qSearch) ?>" placeholder="Search question, answer, topic...">
      <select name="qstatus"><option value="all" <?= $qStatus==='all'?'selected':'' ?>>All Status</option><option value="active" <?= $qStatus==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $qStatus==='inactive'?'selected':'' ?>>Inactive</option></select>
      <button class="btn btn-soft" type="submit">Search</button>
    </form>
    <div class="question-top-pager"><span><?= e((string)$qTotal) ?> question(s)</span><?php if($qPages > 1): ?><nav class="admin-pagination inline-pager"><?php $base='type='.urlencode($selectedType).'&test_id='.urlencode((string)$selectedTestId).'&q='.urlencode($qSearch).'&qstatus='.urlencode($qStatus); if($qPage>1): ?><a href="?<?= $base ?>&qpage=<?= e((string)($qPage-1)) ?>#question-bank">‹</a><?php endif; for($p=max(1,$qPage-1);$p<=min($qPages,$qPage+1);$p++): ?><a class="<?= $p===$qPage?'active':'' ?>" href="?<?= $base ?>&qpage=<?= e((string)$p) ?>#question-bank"><?= e((string)$p) ?></a><?php endfor; if($qPage<$qPages): ?><a href="?<?= $base ?>&qpage=<?= e((string)($qPage+1)) ?>#question-bank">›</a><?php endif; ?></nav><?php endif; ?></div>
  </div>

  <form method="post" class="weekly-bulk-form" data-confirm="Apply selected action?">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="bulk_question_action"><input type="hidden" name="test_id" value="<?= e((string)$selectedTestId) ?>">
  <div class="bulk-action-row"><select name="bulk_action" required><option value="">Bulk Action</option><option value="activate">Activate</option><option value="deactivate">Deactivate</option><option value="delete">Delete</option></select><button class="btn btn-soft btn-sm" type="submit">Apply</button></div>
  <div class="table-responsive"><table class="admin-table compact-table weekly-question-table"><thead><tr><th class="check-col"><input class="tiny-check" type="checkbox" onclick="document.querySelectorAll('.qRowCheck').forEach(c=>c.checked=this.checked)"></th><th>#</th><th>Type / Topic</th><th>Question</th><th>Answer Sheet</th><th>Status</th><th>Action</th></tr></thead><tbody>
    <?php if(!$questions): ?><tr><td colspan="7">No questions found. Upload CSV/Excel or add one question.</td></tr><?php endif; ?>
    <?php foreach($questions as $i=>$q): ?><tr>
      <td class="check-col"><input class="qRowCheck tiny-check" type="checkbox" name="ids[]" value="<?= e((string)$q['id']) ?>"></td>
      <td><?= e((string)($qOffset+$i+1)) ?></td>
      <td><b><?= e($q['question_type']) ?></b><small><?= e($q['topic_name'] ?: '-') ?> • <?= e((string)$q['marks']) ?> mark</small></td>
      <td><?= e($q['question_text']) ?></td>
      <td><?= nl2br(e($q['expected_answer'] ?: '-')) ?><small>Multiple answers supported.</small></td>
      <td><span class="status-pill <?= ($q['published'] ?? 'Yes')==='Yes'?'yes':'no' ?>"><?= ($q['published'] ?? 'Yes')==='Yes'?'Active':'Inactive' ?></span></td>
      <td><a class="btn btn-sm btn-soft" href="weekly-tests.php?test_id=<?= e((string)$selectedTestId) ?>&edit_q=<?= e((string)$q['id']) ?>">Edit</a></td>
    </tr><?php endforeach; ?>
  </tbody></table></div>
  </form>
  <?php endif; ?>
</div>

<div id="student-copies" class="admin-card">
  <div class="section-between">
    <div><h2><?= e($typeLabel[$aType] ?? 'All') ?> Student Answer Copies</h2><p class="muted small">Only selected test / batch records are shown here. Same mobile number is merged into one card even if the name changes.</p></div>
    <form method="get" class="weekly-search-form">
      <input type="hidden" name="type" value="<?= e($selectedType) ?>"><input type="hidden" name="test_id" value="<?= e((string)$selectedTestId) ?>">
      <input type="search" name="aq" value="<?= e($aSearch) ?>" placeholder="Search name, mobile, test...">
      <select name="atype"><option value="all">All Types</option><?php foreach($typeLabel as $k=>$v): ?><option value="<?= e($k) ?>" <?= $aType===$k?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select>
      <button class="btn btn-soft" type="submit">Search</button>
    </form>
  </div>
  <div class="student-copy-grid">
    <?php if(!$studentCards): ?><p class="muted">No student submissions yet.</p><?php endif; ?>
    <?php foreach($studentCards as $s): $phone=(string)($s['phone_display'] ?? ''); $key=(string)($s['phone_key'] ?? ''); ?>
      <a class="student-copy-card" href="weekly-student-record.php?<?= $phone!=='' ? 'phone='.urlencode($phone) : 'key='.urlencode($key) ?>&type=<?= e(urlencode($aType === 'all' ? '' : $aType)) ?>">
        <div class="student-copy-head"><span><?= e(strtoupper(mb_substr($s['display_name'] ?? 'G',0,1))) ?></span><div><b><?= e($s['display_name']) ?></b><small><?= e($phone ?: $key) ?></small></div></div>
        <div class="student-copy-stats <?= $aType !== 'all' ? 'single-scope' : '' ?>">
          <?php if($aType !== 'all'): ?>
            <em><?= e($typeLabel[$aType] ?? 'Selected') ?> <?= e((string)$s['total_attempts']) ?></em>
            <em>Pending <?= e((string)$s['pending_count']) ?></em>
            <em>Checked <?= e((string)$s['checked_count']) ?></em>
            <em>Warnings <?= e((string)$s['warning_total']) ?></em>
          <?php else: ?>
            <em>Total <?= e((string)$s['total_attempts']) ?></em>
            <em>Basic <?= e((string)$s['basic_count']) ?></em>
            <em>Previous <?= e((string)$s['previous_count']) ?></em>
            <em>Upcoming <?= e((string)$s['upcoming_count']) ?></em>
          <?php endif; ?>
        </div>
        <p>Last activity: <?= e((string)$s['last_activity']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
  <?php if($studentPages > 1): ?><nav class="admin-pagination"><?php $base='type='.urlencode($selectedType).'&test_id='.urlencode((string)$selectedTestId).'&aq='.urlencode($aSearch).'&atype='.urlencode($aType); if($aPage>1): ?><a href="?<?= $base ?>&apage=<?= e((string)($aPage-1)) ?>">Prev</a><?php endif; for($p=max(1,$aPage-2);$p<=min($studentPages,$aPage+2);$p++): ?><a class="<?= $p===$aPage?'active':'' ?>" href="?<?= $base ?>&apage=<?= e((string)$p) ?>"><?= e((string)$p) ?></a><?php endfor; if($aPage<$studentPages): ?><a href="?<?= $base ?>&apage=<?= e((string)($aPage+1)) ?>">Next</a><?php endif; ?></nav><?php endif; ?>
</div>

<?php if($review): ?>
<div class="admin-card review-panel weekly-review-panel" id="review">
  <div class="section-between"><div><h2>Review: <?= e($review['test_title']) ?></h2><p class="muted small"><?= e($review['student_name'] ?: $review['guest_name'] ?: 'Guest') ?> • <?= e($review['student_phone'] ?: $review['guest_phone'] ?: '') ?></p></div><a class="btn btn-soft" href="weekly-student-record.php?phone=<?= urlencode((string)($review['student_phone'] ?: $review['guest_phone'] ?: '')) ?>">Full Record</a></div>
  <div class="security-report-grid">
    <div><b>Status</b><span><?= e(weekly_test_status_badge($review['status'])) ?></span></div>
    <div><b>Auto Score</b><span><?= e((string)($review['auto_score'] ?? '-')) ?></span></div>
    <div><b>Admin Score</b><span><?= e((string)($review['admin_score'] ?? '-')) ?></span></div>
    <div><b>Warnings</b><span><?= e((string)($review['warning_count'] ?? 0)) ?></span></div>
    <div><b>Penalty</b><span><?= e((string)($review['penalty_marks'] ?? 0)) ?></span></div>
  </div>
  <?php if(!empty($review['activity_log'])): ?><details class="cheat-log"><summary>Activity warnings</summary><pre><?= e($review['activity_log']) ?></pre></details><?php endif; ?>
  <?php if(!empty($review['timing_log'])): ?><details class="cheat-log"><summary>Question time log</summary><pre><?= e($review['timing_log']) ?></pre></details><?php endif; ?>
  <form class="ajax-admin-form" action="weekly-test-ajax.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="grade_attempt"><input type="hidden" name="attempt_id" value="<?= e((string)$review['id']) ?>">
    <div class="review-accordion">
      <?php foreach($reviewAnswers as $idx=>$ans): ?>
      <details class="review-answer-card">
        <summary><b>Q<?= $idx+1 ?>. <?= e($ans['question_type']) ?><?= $ans['topic_name']?' • '.e($ans['topic_name']):'' ?></b><span><?= e((string)$ans['marks']) ?> mark</span></summary>
        <p><strong>Question:</strong> <?= e($ans['question_text']) ?></p>
        <p><strong>Student Answer:</strong> <?= e($ans['answer_text'] ?: 'No answer') ?></p>
        <p><strong>Expected:</strong> <?= nl2br(e($ans['expected_answer'])) ?></p>
        <div class="form-grid compact-admin-form">
          <label>Marks out of <?= e((string)$ans['marks']) ?><input type="number" step="0.25" min="0" max="<?= e((string)$ans['marks']) ?>" name="marks[<?= e((string)$ans['id']) ?>]" value="<?= e((string)($ans['marks_awarded'] ?? 0)) ?>"></label>
          <label>Teacher Note <input name="notes[<?= e((string)$ans['id']) ?>]" value="<?= e($ans['admin_note'] ?? '') ?>" placeholder="Why wrong/correct?"></label>
        </div>
      </details>
      <?php endforeach; ?>
    </div>
    <label>Overall Note <textarea name="admin_note" rows="2" placeholder="Final feedback for student"><?= e($review['admin_note'] ?? '') ?></textarea></label>
    <button class="btn btn-primary">Save Marks & Publish Result</button><span class="ajax-msg"></span>
  </form>
</div>
<?php endif; ?>

<script>
(function(){
 const sel=document.getElementById('weeklyEditSelect');
 function fill(o){
   if(!o) return;
   document.getElementById('testTitle').value=o.dataset.title||'';
   document.getElementById('testType').value=o.dataset.type||'basic';
   document.getElementById('testStatus').value=o.dataset.status||'draft';
   document.getElementById('testLogin').value=o.dataset.login||'No';
   document.getElementById('testDuration').value=o.dataset.duration||10;
   document.getElementById('testQuestions').value=o.dataset.questions||10;
   document.getElementById('testMarks').value=o.dataset.marks||10;
   ['testShuffleQ','testShuffleO','testPenaltyAfter','testStrictMode','testAutoSubmitWarn','testAllowJump'].forEach(id=>{const el=document.getElementById(id); if(el){ const key=id.replace('test','').replace(/[A-Z]/g,m=>'-'+m.toLowerCase()).replace(/^-/, ''); }});
   if(document.getElementById('testShuffleQ')) document.getElementById('testShuffleQ').value=o.dataset.shuffleQ||'Yes';
   if(document.getElementById('testShuffleO')) document.getElementById('testShuffleO').value=o.dataset.shuffleO||'Yes';
   if(document.getElementById('testWarningLimit')) document.getElementById('testWarningLimit').value=o.dataset.warningLimit||3;
   if(document.getElementById('testPenaltyAfter')) document.getElementById('testPenaltyAfter').value=o.dataset.penaltyAfter||'Yes';
   if(document.getElementById('testPenaltyPer')) document.getElementById('testPenaltyPer').value=o.dataset.penaltyPer||1;
   if(document.getElementById('testStrictMode')) document.getElementById('testStrictMode').value=o.dataset.strictMode||'Yes';
   if(document.getElementById('testAutoSubmitWarn')) document.getElementById('testAutoSubmitWarn').value=o.dataset.autoSubmitWarn||'Yes';
   if(document.getElementById('testAllowJump')) document.getElementById('testAllowJump').value=o.dataset.allowJump||'Yes';
   if(document.getElementById('testStartsAt')) document.getElementById('testStartsAt').value=o.dataset.startsAt||'';
   if(document.getElementById('testEndsAt')) document.getElementById('testEndsAt').value=o.dataset.endsAt||'';
   if(document.getElementById('testBatchId')) document.getElementById('testBatchId').value=o.dataset.batchId||'0';
   if(document.getElementById('testBatchLabel')) document.getElementById('testBatchLabel').value=o.dataset.batchLabel||'';
   document.getElementById('testInstructions').value=o.dataset.instructions||'';
 }
 if(sel){ sel.addEventListener('change',()=>{const o=sel.selectedOptions[0]; if(o&&o.value!=='0') fill(o);}); if(sel.selectedOptions[0] && sel.selectedOptions[0].value!=='0') fill(sel.selectedOptions[0]); }
 document.querySelectorAll('.ajax-admin-form').forEach(form=>{
   form.addEventListener('submit',e=>{
     e.preventDefault();
     const msg=form.querySelector('.ajax-msg'); if(msg){msg.textContent='Saving...'; msg.className='ajax-msg';}
     fetch((form.getAttribute('action')||'weekly-test-ajax.php'),{method:'POST',body:new FormData(form),cache:'no-store',headers:{'X-Requested-With':'XMLHttpRequest'}})
       .then(async r=>{const t=await r.text(); try{return JSON.parse(t)}catch(e){throw new Error((t.indexOf('<!DOCTYPE')>=0 || t.indexOf('<html')>=0) ? 'Admin save endpoint returned HTML. Please use the new ZIP files together; ajax endpoint should be admin/weekly-test-ajax.php.' : (t.slice(0,180)||'Server returned invalid response'));}})
       .then(d=>{if(msg){msg.textContent=d.message||''; msg.classList.toggle('ok',!!d.success); msg.classList.toggle('bad',!d.success);} if(window.AppUI){window.AppUI.toast(d.success?'success':'error', d.message||'Done');} if(d.success){setTimeout(()=>location.reload(),800)}})
       .catch(err=>{if(msg){msg.textContent='Server error: '+err.message; msg.classList.add('bad');} if(window.AppUI){window.AppUI.toast('error', 'Server error: '+err.message);} });
   });
 });
})();
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
