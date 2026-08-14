<?php
ob_start();
ini_set('display_errors','0');
require_once __DIR__ . '/../includes/functions.php';
require_admin();
weekly_test_ensure_schema();
while (ob_get_level() > 1) { @ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
function aj($arr,$code=200){ http_response_code($code); if(ob_get_length()) @ob_clean(); echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }
function weekly_ajax_demo_rows(): array {
    $base = [
        ['hindi_to_english','Present Simple','Beginner','मैं रोज अंग्रेजी बोलता हूँ।','I speak English every day. || I speak English daily.',1],
        ['hindi_to_english','is am are','Beginner','मैं तैयार हूँ।','I am ready.',1],
        ['english_to_hindi','can','Beginner','I can speak English.','मैं अंग्रेजी बोल सकता हूँ। || मैं अंग्रेजी बोल सकती हूँ।',1],
        ['correction','Present Simple','Beginner','Correct: She go to class every day.','She goes to class every day.',1],
        ['hindi_to_english','have to','Beginner','मुझे आज पढ़ना है।','I have to study today. || I have to read today.',1],
        ['english_to_hindi','should','Beginner','You should practise daily.','आपको रोज अभ्यास करना चाहिए।',1],
        ['hindi_to_english','was/were','Beginner','वह कल व्यस्त था।','He was busy yesterday.',1],
        ['hindi_to_english','has/have','Beginner','उसके पास बाइक है।','He has a bike.',1],
        ['hindi_to_english','can/could','Beginner','मैं आपकी मदद कर सकता हूँ।','I can help you.',1],
        ['english_to_hindi','must','Beginner','You must speak clearly.','आपको साफ बोलना चाहिए।',1],
    ];
    $rows=[];
    for($i=1;$i<=30;$i++){$r=$base[($i-1)%count($base)]; $rows[]=['question_type'=>$r[0],'topic_name'=>$r[1],'level'=>$r[2],'question_text'=>$r[3],'expected_answer'=>$r[4],'marks'=>$r[5],'sort_order'=>$i,'published'=>'Yes'];}
    return $rows;
}
function weekly_ajax_create_demo_questions(int $testId): void {
    $existing=(int)db()->query("SELECT COUNT(*) FROM weekly_test_questions WHERE test_id=".(int)$testId." AND status_deleted=0")->fetchColumn();
    if($existing>0) return;
    $ins=db()->prepare("INSERT INTO weekly_test_questions (test_id,question_type,topic_name,level,question_text,expected_answer,marks,sort_order,published) VALUES (?,?,?,?,?,?,?,?, 'Yes')");
    foreach(weekly_ajax_demo_rows() as $r){$ins->execute([$testId,$r['question_type'],$r['topic_name'],$r['level'],$r['question_text'],$r['expected_answer'],$r['marks'],$r['sort_order']]);}
}

try{
 if($_SERVER['REQUEST_METHOD']!=='POST') aj(['success'=>false,'message'=>'Invalid request'],405);
 if(!csrf_validate($_POST['csrf_token'] ?? '')) aj(['success'=>false,'message'=>'Security check failed. Refresh page once.'],419);
 $action=$_POST['action'] ?? '';

 if($action==='save_test'){
   $id=(int)($_POST['id']??0);
   $title=trim((string)($_POST['title']??''));
   if($title==='') aj(['success'=>false,'message'=>'Test title required']);
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
   aj(['success'=>true,'message'=>'Test saved successfully. '.($status==='active'?($type==='upcoming'?'This Upcoming paper is active for its selected batch. Other active Upcoming papers for the same batch moved to Pending.':'Only this '.$type.' paper is active now. Other '.$type.' papers moved to Pending.'):'Paper saved as Pending/Draft.'),'test_id'=>$id,'type'=>$type]);
 }


 if($action==='publish_test_now'){
   $testId=(int)($_POST['test_id'] ?? $_POST['id'] ?? 0);
   if($testId<=0) aj(['success'=>false,'message'=>'Select a test paper first.']);
   weekly_test_publish_now($testId,true,true);
   $q=(int)db()->query("SELECT COUNT(*) FROM weekly_test_questions WHERE test_id=".$testId." AND status_deleted=0 AND published='Yes'")->fetchColumn();
   $stmt=db()->prepare("SELECT test_type,title FROM weekly_tests WHERE id=? LIMIT 1");
   $stmt->execute([$testId]); $t=$stmt->fetch() ?: [];
   aj(['success'=>true,'message'=>'Published now. Paper is Active, schedule block cleared and '.$q.' question(s) are Active.','test_id'=>$testId,'type'=>($t['test_type'] ?? ''),'title'=>($t['title'] ?? '')]);
 }

 if($action==='set_test_pending'){
   $testId=(int)($_POST['test_id'] ?? $_POST['id'] ?? 0);
   if($testId<=0) aj(['success'=>false,'message'=>'Select a test paper first.']);
   weekly_test_close_entry($testId);
   $ts=db()->prepare("SELECT test_type FROM weekly_tests WHERE id=? LIMIT 1"); $ts->execute([$testId]); $closeType=(string)($ts->fetchColumn()?:'');
   aj(['success'=>true,'message'=>$closeType==='upcoming'?'Upcoming Test entry closed. No new student can start; students already inside keep their own exam timer. Review submitted copies, then Finalize Top 3.':'Set to Pending/Draft. Students cannot start this paper now.','test_id'=>$testId]);
 }

 if($action==='release_answer_key'){
   $testId=(int)($_POST['test_id'] ?? $_POST['id'] ?? 0);
   if($testId<=0) aj(['success'=>false,'message'=>'Select an Upcoming Test paper first.']);
   $res=weekly_test_release_answers_to_students($testId);
   aj($res, !empty($res['success']) ? 200 : 422);
 }

 if($action==='create_demo_batch_tests'){
   $kind=in_array($_POST['test_type']??'upcoming',['basic','previous','upcoming'],true)?$_POST['test_type']:'upcoming';
   $created=[];
   $papers = $kind==='previous' ? [
     ['Previous Demo Paper - Morning Batch','Morning Batch Demo','No','active'],
     ['Previous Demo Paper - Evening Batch','Evening Batch Demo','No','active']
   ] : [
     ['Upcoming Demo Paper - Morning Batch','Morning Batch Demo','No','active'],
     ['Upcoming Demo Paper - Evening Batch','Evening Batch Demo','No','active']
   ];
   foreach($papers as $paper){
     $chk=db()->prepare("SELECT id FROM weekly_tests WHERE test_type=? AND title=? AND status_deleted=0 LIMIT 1");
     $chk->execute([$kind,$paper[0]]);
     $id=(int)($chk->fetchColumn() ?: 0);
     if($id<=0){
       db()->prepare("INSERT INTO weekly_tests (title,test_type,status,requires_login,duration_minutes,total_questions,total_marks,shuffle_questions,shuffle_options,warning_limit,penalty_after_warnings,penalty_per_warning,strict_exam_mode,auto_submit_on_warning_limit,allow_question_jump,batch_label,instructions,starts_at,ends_at,published) VALUES (?,?,?,?,30,30,30,'Yes','Yes',3,'Yes',1,'Yes','Yes','Yes',?,? ,NULL,NULL,'Yes')")
         ->execute([$paper[0],$kind,$paper[3],$paper[2],$paper[1],'Demo paper for '.$paper[1].'. Replace questions anytime from Excel upload.']);
       $id=(int)db()->lastInsertId();
     } else {
       db()->prepare("UPDATE weekly_tests SET status='active', published='Yes', requires_login=?, batch_label=?, starts_at=NULL, ends_at=NULL, updated_at=NOW() WHERE id=?")->execute([$paper[2],$paper[1],$id]);
     }
     weekly_ajax_create_demo_questions($id);
     if(count($created)===0){ weekly_test_publish_now($id,true,true); } else { db()->prepare("UPDATE weekly_tests SET status='draft', published='Yes', updated_at=NOW() WHERE id=?")->execute([$id]); }
     $created[]=$paper[0];
   }
   aj(['success'=>true,'message'=>'Demo batch papers ready: '.implode(', ', $created),'type'=>$kind]);
 }


 if($action==='complete_batch_test'){
   $testId=(int)($_POST['test_id'] ?? 0);
   $res=weekly_test_complete_batch($testId);
   aj($res, !empty($res['success']) ? 200 : 422);
 }



 if($action==='archive_test_paper' || $action==='delete_test'){
   $id=(int)($_POST['test_id'] ?? $_POST['id'] ?? 0);
   if($id<=0) aj(['success'=>false,'message'=>'Select a batch/test paper first.']);
   db()->prepare("UPDATE weekly_tests SET status_deleted=1, deleted_at=NOW(), status='archived', published='No', updated_at=NOW() WHERE id=?")->execute([$id]);
   aj(['success'=>true,'message'=>'Batch/test paper hidden for admin. It will remain safely in database for 15 days before cleanup.']);
 }

 if($action==='clear_questions'){
   $testId=(int)($_POST['test_id']??0);
   if($testId<=0) aj(['success'=>false,'message'=>'Select test first']);
   db()->prepare("UPDATE weekly_test_questions SET status_deleted=1, deleted_at=NOW() WHERE test_id=?")->execute([$testId]);
   weekly_test_sync_question_totals($testId);
   aj(['success'=>true,'message'=>'Questions cleared']);
 }

 if($action==='upload_questions'){
   $testId=(int)($_POST['test_id']??0);
   if($testId<=0) aj(['success'=>false,'message'=>'Select test first']);
   $uploadError=(int)($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE);
   if($uploadError!==UPLOAD_ERR_OK || empty($_FILES['file']['tmp_name'])){
     $uploadMessage=$uploadError===UPLOAD_ERR_NO_FILE?'Choose an Excel (.xlsx) or CSV file first.':(($uploadError===UPLOAD_ERR_INI_SIZE || $uploadError===UPLOAD_ERR_FORM_SIZE)?'Question file is too large for this server. Try a smaller Excel/CSV file.':'Question file upload could not complete. Please select the file again.');
     aj(['success'=>false,'message'=>$uploadMessage]);
   }
   if((int)($_FILES['file']['size'] ?? 0) > 10*1024*1024) aj(['success'=>false,'message'=>'Question file is too large. Maximum allowed here is 10 MB.']);
   $fileName=(string)($_FILES['file']['name'] ?? '');
   $ext=strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
   if(!in_array($ext, ['csv','txt','xlsx'], true)) aj(['success'=>false,'message'=>'Please upload CSV or XLSX only. If your file is XLS, open it in Excel and Save As CSV or XLSX.']);
   $rows=weekly_test_parse_upload($_FILES['file']['tmp_name'], $fileName);
   if(!$rows) aj(['success'=>false,'message'=>'No rows found. Check sheet columns: question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d']);
   $added=weekly_test_import_rows($testId,$rows);
   if($added<=0) aj(['success'=>false,'message'=>'File read ho gayi, but no valid question imported. question_text is required. expected_answer should be filled for automatic checking/result answers.']);
   aj(['success'=>true,'message'=>$added.' question(s) imported successfully']);
 }

 if($action==='save_question'){
   $id=(int)($_POST['id']??0);
   $testId=(int)($_POST['test_id']??0);
   $question=trim((string)($_POST['question_text']??''));
   if($testId<=0 || $question==='') aj(['success'=>false,'message'=>'Select test and write question']);
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
   weekly_test_sync_question_totals($testId);
   aj(['success'=>true,'message'=>'Question saved']);
 }

 if($action==='delete_question'){
   $id=(int)($_POST['id']??0);
   $qs=db()->prepare("SELECT test_id FROM weekly_test_questions WHERE id=? LIMIT 1"); $qs->execute([$id]); $questionTestId=(int)($qs->fetchColumn()?:0);
   db()->prepare("UPDATE weekly_test_questions SET status_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
   if($questionTestId>0) weekly_test_sync_question_totals($questionTestId);
   aj(['success'=>true,'message'=>'Question deleted']);
 }

 if($action==='grade_attempt'){
   $attemptId=(int)($_POST['attempt_id']??0);
   if($attemptId<=0) aj(['success'=>false,'message'=>'Invalid attempt']);
   $scores=is_array($_POST['marks']??null)?$_POST['marks']:[];
   $notes=is_array($_POST['notes']??null)?$_POST['notes']:[];
   $total=0.0;
   $check=db()->prepare("SELECT ans.id, q.marks FROM weekly_test_answers ans JOIN weekly_test_questions q ON q.id=ans.question_id WHERE ans.id=? AND ans.attempt_id=? LIMIT 1");
   $upd=db()->prepare("UPDATE weekly_test_answers SET marks_awarded=?, is_correct=?, admin_note=? WHERE id=? AND attempt_id=?");
   foreach($scores as $answerId=>$mark){
     $answerId=(int)$answerId;
     if($answerId<=0) continue;
     $check->execute([$answerId,$attemptId]);
     $row=$check->fetch();
     if(!$row) continue;
     $max=(float)($row['marks'] ?? 1);
     $mark=max(0,min($max,(float)$mark));
     $total += $mark;
     $status = $mark <= 0 ? 'No' : ($mark >= $max ? 'Yes' : 'Review');
     $upd->execute([$mark,$status,(string)($notes[$answerId]??''),$answerId,$attemptId]);
   }
   $overall=trim((string)($_POST['admin_note']??''));
   db()->prepare("UPDATE weekly_test_attempts SET admin_score=?, status='checked', admin_note=? WHERE id=?")->execute([round($total,2),$overall,$attemptId]);
   aj(['success'=>true,'message'=>'Marks saved and result published: '.round($total,2)]);
 }

 if($action==='reset_attempt'){
   $attemptId=(int)($_POST['attempt_id']??0);
   if($attemptId<=0) aj(['success'=>false,'message'=>'Invalid attempt']);
   db()->prepare("UPDATE weekly_test_attempts SET status_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$attemptId]);
   aj(['success'=>true,'message'=>'Attempt hidden for admin. It will be permanently cleaned after 15 days.']);
 }

 aj(['success'=>false,'message'=>'Unknown action']);
} catch (Throwable $e) { error_log('[admin-weekly-test-ajax] ' . $e->__toString()); aj(['success'=>false,'message'=>'Weekly Test request failed. Check Admin > System Check.'],500); }
