<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
ensure_schema_updates();
weekly_test_ensure_schema();

$attemptId = (int)($_GET['attempt_id'] ?? 0);
$token = trim((string)($_GET['token'] ?? ''));
if ($attemptId <= 0 || $token === '') {
    http_response_code(403);
    echo 'Invalid exam link.';
    exit;
}

$stmt = db()->prepare("SELECT a.*, t.title, t.test_type, t.duration_minutes, t.instructions, t.total_questions, t.shuffle_options, t.warning_limit, t.penalty_after_warnings, t.penalty_per_warning, t.strict_exam_mode, t.auto_submit_on_warning_limit, t.allow_question_jump
                       FROM weekly_test_attempts a
                       JOIN weekly_tests t ON t.id=a.test_id
                       WHERE a.id=? AND a.access_token=? AND COALESCE(a.status_deleted,0)=0 LIMIT 1");
$stmt->execute([$attemptId, $token]);
$attempt = $stmt->fetch();
if (!$attempt) {
    http_response_code(403);
    echo 'Exam access denied.';
    exit;
}
if (!empty($attempt['student_id'])) {
    require_student();
    if ((int)$attempt['student_id'] !== current_student_id()) {
        http_response_code(403);
        exit('Exam access denied.');
    }
}

$resultToken = trim((string)($attempt['result_token'] ?? ''));
if ($resultToken === '') {
    $resultToken = bin2hex(random_bytes(32));
    db()->prepare("UPDATE weekly_test_attempts SET result_token=? WHERE id=? AND access_token=?")
        ->execute([$resultToken, $attemptId, $token]);
    $attempt['result_token'] = $resultToken;
}
$resultUrl = weekly_test_result_url($attempt);

if (($attempt['status'] ?? '') !== 'started') {
    header('Location: ' . $resultUrl);
    exit;
}

$remaining = weekly_attempt_remaining_seconds($attempt);
if ($remaining <= 0) {
    $finalized = weekly_test_finalize_attempt($attemptId, $token, [], 'timer_expired');
    if (!empty($finalized['result_url'])) $resultUrl = (string)$finalized['result_url'];
    if (empty($finalized['success'])) {
        http_response_code(500);
        echo 'Your exam time is over, but the saved answers could not be submitted safely. Please reopen My Results or contact the institute.';
        exit;
    }
    header('Location: ' . $resultUrl);
    exit;
}

$snapshot = weekly_test_attempt_snapshot($attempt);
if (!$snapshot) {
    http_response_code(500);
    echo 'Question paper is unavailable. Please contact the institute.';
    exit;
}
$safe = array_map(static function(array $question): array {
    unset($question['expected']);
    return $question;
}, $snapshot);

$savedAnswers = [];
$answerStmt = db()->prepare("SELECT question_id, answer_text FROM weekly_test_answers WHERE attempt_id=?");
$answerStmt->execute([$attemptId]);
foreach ($answerStmt->fetchAll() as $answerRow) {
    $savedAnswers[(int)$answerRow['question_id']] = (string)($answerRow['answer_text'] ?? '');
}
$csrf = csrf_token();
$candidateName = trim((string)($attempt['guest_name'] ?? ''));
$candidatePhone = trim((string)($attempt['guest_phone'] ?? ''));
if (($attempt['student_id'] ?? null)) {
    try {
        $st = db()->prepare("SELECT full_name, phone FROM students WHERE id=? LIMIT 1");
        $st->execute([(int)$attempt['student_id']]);
        $student = $st->fetch();
        if ($student) {
            $candidateName = trim((string)($student['full_name'] ?? $candidateName));
            $candidatePhone = trim((string)($student['phone'] ?? $candidatePhone));
        }
    } catch (Throwable $e) {}
}
$examLogo = site_asset_url(app_setting('site_logo', ''));
if ($examLogo === '') { $examLogo = site_asset_url(app_setting('site_pwa_icon_192', 'assets/uploads/brand/wf-pwa-icon-192.png')); }
$isBasicWeeklyTest = (($attempt['test_type'] ?? 'basic') === 'basic');
$strictExamMode = (!$isBasicWeeklyTest && (($attempt['strict_exam_mode'] ?? 'Yes') === 'Yes'));
$autoSubmitOnLimit = (!$isBasicWeeklyTest && (($attempt['auto_submit_on_warning_limit'] ?? 'Yes') === 'Yes'));
$allowQuestionJump = (($attempt['allow_question_jump'] ?? 'Yes') === 'Yes');
$entryIntroText = $isBasicWeeklyTest ? 'Practice test: answer one question at a time. Your answers save automatically.' : 'Official weekly exam: check the simple rules, then start when you are ready.';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($attempt['title']) ?> | Weekly Test</title>
<?php $examFavicon = site_asset_url(app_setting('site_favicon', 'assets/uploads/brand/wf-favicon.ico')); ?>
<?php if ($examFavicon !== ''): ?><link rel="icon" href="<?= e($examFavicon) ?>"><?php endif; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/style.css'))) ?>">
<link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase138-mobile-ux.css'))) ?>">
<style>
:root{--exam-navy:#071529;--exam-blue:#1a3565;--exam-gold:#f4b63f;--exam-green:#16a34a;--exam-red:#dc2626;--exam-soft:#f4f7fb;--exam-border:#dce5ef;--exam-muted:#60708a}*{box-sizing:border-box}html,body{margin:0;background:#eef3f9;color:var(--exam-navy);font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif;overflow-x:hidden}body.exam-active{user-select:none;-webkit-user-select:none}body.exam-active textarea,body.exam-active input,body.exam-active label{user-select:text;-webkit-user-select:text}.exam-entry-overlay{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:22px;background:linear-gradient(120deg,rgba(7,21,41,.96),rgba(26,53,101,.94))}.exam-entry-card{width:min(920px,100%);background:#fff;border-radius:28px;box-shadow:0 35px 100px rgba(0,0,0,.36);overflow:hidden;border:1px solid rgba(255,255,255,.25)}.exam-entry-head{display:flex;justify-content:space-between;align-items:center;gap:18px;padding:22px 26px;background:#071529;color:#fff}.exam-entry-brand{display:flex;align-items:center;gap:12px;min-width:0}.exam-entry-brand img{width:52px;height:52px;object-fit:contain;background:#fff;border-radius:16px;padding:5px}.exam-entry-brand b{display:block;font-size:19px;line-height:1.1}.exam-entry-brand small{display:block;color:#cbd7ea;font-weight:600;margin-top:3px}.exam-entry-code{display:grid;text-align:right;gap:3px;font-size:13px;color:#cbd7ea}.exam-entry-code b{font-size:18px;color:#f4b63f}.exam-entry-body{display:grid;grid-template-columns:1.1fr .9fr;gap:22px;padding:28px}.exam-entry-title span{display:inline-flex;padding:8px 14px;border-radius:999px;background:#fff1c9;border:1px solid #f3cf7b;color:#7a4c00;font-weight:900;font-size:13px}.exam-entry-title h1{margin:14px 0 10px;font-size:clamp(34px,4vw,52px);line-height:1.05;letter-spacing:-.018em;color:#071529}.exam-entry-title p{margin:0;color:#60708a;font-size:16px;line-height:1.55}.exam-summary-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:20px}.exam-summary-grid article{padding:15px;border-radius:18px;background:#f8fbff;border:1px solid #dce5ef}.exam-summary-grid small{display:block;color:#60708a;font-weight:700;margin-bottom:5px}.exam-summary-grid b{display:block;color:#071529;font-size:17px}.exam-rule-panel{padding:18px;border-radius:22px;background:#f8fbff;border:1px solid #dce5ef}.exam-rule-panel h2{margin:0 0 12px;font-size:22px}.exam-rule-list{display:grid;gap:10px;margin:0;padding:0;list-style:none}.exam-rule-list li{display:grid;grid-template-columns:26px 1fr;gap:10px;align-items:start;color:#26344a;font-size:14px;line-height:1.45}.exam-rule-list li:before{content:"✓";width:26px;height:26px;display:grid;place-items:center;border-radius:50%;background:#dcfce7;color:#166534;font-weight:900}.exam-entry-actions{display:flex;gap:12px;padding:0 28px 28px}.exam-entry-actions .btn{flex:1;justify-content:center;min-height:48px}.exam-app{min-height:100vh;display:grid;grid-template-rows:auto 1fr}.exam-topbar{position:sticky;top:0;z-index:20;display:flex;justify-content:space-between;align-items:center;gap:18px;padding:12px 20px;background:#071529;color:#fff;border-bottom:3px solid #f4b63f}.exam-brand-line{display:flex;align-items:center;gap:10px;min-width:0}.exam-brand-line img{width:42px;height:42px;object-fit:contain;background:#fff;border-radius:12px;padding:4px}.exam-brand-line b{display:block;line-height:1.1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px}.exam-brand-line small{display:block;color:#cbd7ea;font-weight:600}.exam-timer-box{display:flex;align-items:center;gap:12px;padding:8px 14px;border-radius:16px;background:#10294f;min-width:150px;justify-content:center}.exam-timer-box span{display:block;color:#cbd7ea;font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:800}.exam-timer-box b{display:block;font-size:30px;line-height:1;color:#f4b63f}.exam-timer-box.danger b{color:#ff6b6b}.exam-layout{width:min(1320px,100%);margin:0 auto;padding:18px;display:grid;grid-template-columns:minmax(0,1fr) 310px;gap:18px}.exam-paper{background:#fff;border:1px solid #dce5ef;border-radius:24px;overflow:hidden;box-shadow:0 16px 45px rgba(7,21,41,.08)}.exam-paper-head{padding:15px 18px;display:flex;justify-content:space-between;gap:12px;align-items:center;border-bottom:1px solid #e5edf6;background:#fbfdff}.exam-paper-head h2{margin:0;font-size:20px;letter-spacing:-.006em}.exam-paper-head p{margin:3px 0 0;color:#60708a;font-size:13px;font-weight:700}.exam-progress{height:9px;background:#e8eef6}.exam-progress span{display:block;height:100%;width:0;background:#16a34a;transition:width .25s ease}.exam-question-area{padding:24px;min-height:440px}.exam-q-meta{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}.exam-q-meta span,.exam-q-meta em{font-style:normal;display:inline-flex;padding:9px 13px;border-radius:999px;font-size:13px;font-weight:900}.exam-q-meta span{background:#edf4ff;color:#1a3565}.exam-q-meta em{background:#fff1c9;color:#7a4c00}.exam-question-area h1{margin:0 0 22px;font-size:clamp(28px,3.4vw,44px);line-height:1.2;letter-spacing:-.01em;color:#071529}.exam-answer textarea{width:100%;min-height:190px;border:1px solid #dce5ef;border-radius:20px;padding:18px;font:inherit;font-size:18px;line-height:1.55;resize:vertical;outline:none}.exam-answer textarea:focus{border-color:#f4b63f;box-shadow:0 0 0 4px rgba(244,182,63,.16)}.exam-option{display:flex;align-items:center;gap:14px;border:1px solid #dce5ef;border-radius:18px;padding:15px 16px;margin:10px 0;background:#fff;cursor:pointer;font-size:17px;font-weight:700}.exam-option:hover{border-color:#f4b63f;background:#fffaf0}.exam-option input{width:20px;height:20px}.exam-actions{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:16px 18px;border-top:1px solid #e5edf6;background:#fbfdff}.exam-actions .group{display:flex;gap:10px;flex-wrap:wrap}.exam-side{display:grid;gap:14px;align-content:start}.exam-card{background:#fff;border:1px solid #dce5ef;border-radius:22px;padding:17px;box-shadow:0 16px 45px rgba(7,21,41,.07)}.exam-card h3{margin:0 0 12px;font-size:18px}.candidate-table{display:grid;gap:9px}.candidate-table div{display:flex;justify-content:space-between;gap:12px;padding-bottom:9px;border-bottom:1px solid #edf2f8}.candidate-table small{color:#60708a;font-weight:800}.candidate-table b{text-align:right}.question-palette{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}.question-palette button{height:40px;border:1px solid #dce5ef;background:#f8fbff;border-radius:12px;font-weight:900;cursor:pointer}.question-palette button.current{background:#1a3565;color:#fff;border-color:#1a3565}.question-palette button.answered{background:#dcfce7;color:#166534;border-color:#86efac}.question-palette button.unanswered{background:#fff}.legend{display:grid;gap:8px;margin-top:12px}.legend span{display:flex;align-items:center;gap:8px;color:#60708a;font-size:13px;font-weight:700}.legend i{width:15px;height:15px;border-radius:5px;display:inline-block;border:1px solid #dce5ef}.legend .l-current{background:#1a3565}.legend .l-answered{background:#dcfce7;border-color:#86efac}.legend .l-unanswered{background:#fff}.warning-card{display:none;background:#fff8e8;color:#8a5a00;border:1px solid #f3cf7b;border-radius:18px;padding:13px;font-weight:800;line-height:1.45}.exam-submit-modal{position:fixed;inset:0;display:none;place-items:center;z-index:9998;background:rgba(7,21,41,.72);padding:20px}.exam-submit-card{width:min(460px,100%);background:#fff;border-radius:24px;padding:24px;text-align:center;box-shadow:0 30px 90px rgba(0,0,0,.3)}.exam-submit-card h2{margin:0 0 8px;font-size:28px}.exam-submit-card p{color:#60708a;line-height:1.55}.exam-submit-card .group{display:flex;gap:10px;margin-top:18px}.exam-submit-card .group .btn{flex:1;justify-content:center}@media(max-width:980px){.exam-layout{grid-template-columns:1fr}.exam-side{order:-1}.question-palette{grid-template-columns:repeat(10,1fr)}}@media(max-width:640px){.exam-entry-body{grid-template-columns:1fr;padding:20px}.exam-entry-head{align-items:flex-start}.exam-entry-code{display:none}.exam-summary-grid{grid-template-columns:1fr}.exam-entry-actions{padding:0 20px 20px;display:grid}.exam-topbar{padding:10px 12px}.exam-brand-line b{max-width:180px}.exam-timer-box{min-width:112px;padding:7px 10px}.exam-timer-box b{font-size:23px}.exam-layout{padding:10px;gap:10px}.exam-question-area{padding:18px;min-height:360px}.exam-question-area h1{font-size:30px}.question-palette{grid-template-columns:repeat(6,1fr)}.exam-actions{display:grid}.exam-actions .group{display:grid;grid-template-columns:1fr 1fr}}

/* Phase 99: compact CBT layout + reusable exam modal */
.exam-topbar{
  min-height:68px!important;
  padding:10px 18px!important;
}
.exam-brand-line img{
  width:38px!important;
  height:38px!important;
}
.exam-brand-line b{
  font-size:15px!important;
}
.exam-brand-line small{
  font-size:12px!important;
}
.exam-timer-box{
  min-width:168px!important;
  padding:8px 13px!important;
  border-radius:14px!important;
}
.exam-timer-box span{
  font-size:10px!important;
}
.exam-timer-box b{
  font-size:24px!important;
}
.exam-layout{
  padding:14px!important;
  gap:14px!important;
  grid-template-columns:minmax(0,1fr) 300px!important;
}
.exam-paper{
  border-radius:20px!important;
}
.exam-paper-head{
  padding:12px 16px!important;
}
.exam-paper-head h2{
  font-size:18px!important;
}
.exam-paper-head p{
  font-size:12px!important;
}
.exam-question-area{
  padding:20px!important;
  min-height:365px!important;
}
.exam-q-meta{
  margin-bottom:12px!important;
}
.exam-q-meta span,
.exam-q-meta em{
  padding:7px 11px!important;
  font-size:12px!important;
}
.exam-question-area h1{
  font-size:clamp(24px,2.8vw,34px)!important;
  line-height:1.18!important;
  margin-bottom:16px!important;
  max-width:980px!important;
}
.exam-answer textarea{
  min-height:125px!important;
  max-height:230px!important;
  font-size:16px!important;
  line-height:1.45!important;
  padding:14px 16px!important;
  border-radius:16px!important;
}
.exam-actions{
  padding:12px 16px!important;
}
.exam-actions .submit-near-answer{
  display:none!important;
}
.exam-side{
  gap:12px!important;
}
.exam-card{
  padding:15px!important;
  border-radius:18px!important;
}
.exam-card h3{
  font-size:16px!important;
  margin-bottom:10px!important;
}
.candidate-table div{
  padding-bottom:7px!important;
  font-size:13px!important;
}
.question-palette{
  grid-template-columns:repeat(5,1fr)!important;
  gap:7px!important;
}
.question-palette button{
  height:36px!important;
  border-radius:10px!important;
  font-size:13px!important;
}
.exam-side-submit{
  display:grid;
  gap:10px;
}
.exam-side-submit .btn{
  width:100%;
  justify-content:center;
  min-height:48px;
  font-size:15px;
  box-shadow:0 12px 26px rgba(244,182,63,.25);
}
.exam-side-submit{
  position:sticky;
  bottom:14px;
}
.exam-side-submit small{
  display:block;
  color:#60708a;
  line-height:1.4;
  font-weight:700;
}
.exam-modal{
  position:fixed;
  inset:0;
  display:none;
  place-items:center;
  z-index:99999;
  background:rgba(7,21,41,.72);
  padding:20px;
}
.exam-modal.active{
  display:grid;
}
.exam-modal-card{
  width:min(460px,100%);
  background:#fff;
  border-radius:24px;
  padding:24px;
  text-align:left;
  box-shadow:0 30px 90px rgba(0,0,0,.32);
}
.exam-modal-icon{
  width:54px;
  height:54px;
  display:grid;
  place-items:center;
  border-radius:18px;
  background:#fff1c9;
  color:#7a4c00;
  font-size:26px;
  margin-bottom:14px;
}
.exam-modal-card h2{
  margin:0 0 8px!important;
  font-size:26px!important;
  line-height:1.15!important;
}
.exam-modal-card p{
  color:#60708a;
  line-height:1.55;
  margin:0;
}
.exam-modal-actions{
  display:flex;
  gap:10px;
  margin-top:20px;
}
.exam-modal-actions .btn{
  flex:1;
  justify-content:center;
}
.exam-entry-card{
  max-height:92vh;
  overflow:auto;
}
.exam-entry-body{
  padding:24px!important;
}
.exam-entry-title h1{
  font-size:clamp(30px,3.5vw,44px)!important;
}
@media(max-width:980px){
  .exam-layout{
    grid-template-columns:1fr!important;
  }
  .exam-side-submit{
    grid-template-columns:1fr 1fr;
  }
}
@media(max-width:640px){
  .exam-layout{
    padding:8px!important;
  }
  .exam-question-area{
    padding:16px!important;
    min-height:310px!important;
  }
  .exam-question-area h1{
    font-size:25px!important;
  }
  .exam-answer textarea{
    min-height:115px!important;
  }
  .exam-side-submit{
    grid-template-columns:1fr;
  }
  .exam-modal-actions{
    display:grid;
  }
}


/* Phase 100 final CBT corrections */
.exam-question-area{min-height:330px!important}
.exam-answer textarea{min-height:105px!important}
.exam-question-area h1{font-size:clamp(23px,2.5vw,32px)!important}
.question-palette button.not-answered::after,
.question-palette button.visited-empty::after{content:"";display:block;width:5px;height:5px;border-radius:50%;margin:-5px auto 0;background:currentColor}
.exam-side-submit .btn{display:flex!important;visibility:visible!important;opacity:1!important}
.exam-paper .submit-near-answer{display:none!important}
@media(max-width:980px){.exam-side-submit{position:static}}
.legend .l-notanswered{background:#fee2e2;border-color:#fca5a5}.legend .l-visited{background:#ffedd5;border-color:#fdba74}.question-palette button.not-answered{background:#fee2e2!important;color:#991b1b!important;border-color:#fca5a5!important}.question-palette button.visited-empty{background:#ffedd5!important;color:#9a3412!important;border-color:#fdba74!important}.question-palette button.not-visited{background:#f1f5f9!important;color:#475569!important;border-color:#cbd5e1!important}
/* Phase 101: compact final exam corrections */
.exam-topbar{min-height:58px!important;padding:8px 14px!important}
.exam-brand-line img{width:34px!important;height:34px!important}
.exam-brand-line b{font-size:14px!important}
.exam-brand-line small{font-size:11px!important}
.exam-timer-box{min-width:148px!important;padding:7px 11px!important}
.exam-timer-box b{font-size:22px!important}
.exam-layout{padding:10px!important;gap:12px!important}
.exam-paper-head{padding:10px 14px!important}
.exam-paper-head h2{font-size:16px!important}
.exam-question-area{padding:16px!important;min-height:285px!important}
.exam-question-area h1{font-size:clamp(20px,2.2vw,28px)!important;line-height:1.18!important;margin-bottom:14px!important}
.exam-answer textarea{min-height:88px!important;max-height:160px!important;font-size:15px!important;padding:12px 14px!important}
.exam-actions{padding:10px 14px!important}
.exam-card{padding:13px!important}
.exam-card h3{font-size:15px!important}
.question-palette button{height:34px!important;font-size:12px!important}
.warning-card{display:none}
.exam-side-submit .btn{min-height:46px!important}
.exam-cancel-btn{background:#fff!important;color:#991b1b!important;border:1px solid #fecaca!important}
.question-palette button.not-answered{background:#fee2e2!important;color:#991b1b!important;border-color:#fca5a5!important}
.question-palette button.not-visited{background:#f1f5f9!important;color:#475569!important;border-color:#cbd5e1!important}
.question-palette button.answered{background:#dcfce7!important;color:#166534!important;border-color:#86efac!important}
.question-palette button.current{background:#1a3565!important;color:#fff!important;border-color:#1a3565!important}
.legend{grid-template-columns:1fr!important}



/* Phase 106: fix start screen summary overflow and Basic mode copy */
.exam-entry-card{width:min(980px,calc(100vw - 36px))!important;max-height:calc(100vh - 42px);overflow:auto!important}
.exam-entry-body{grid-template-columns:minmax(0,1fr) minmax(320px,.9fr)!important;gap:22px!important}
.exam-entry-title{min-width:0!important}.exam-entry-title h1{font-size:clamp(26px,3vw,38px)!important;line-height:1.12!important;margin:12px 0 8px!important}.exam-entry-title p{font-size:15px!important;line-height:1.5!important;max-width:620px}
.clean-start-summary{display:grid!important;grid-template-columns:repeat(6,minmax(0,1fr))!important;gap:10px!important;margin-top:18px!important}
.clean-start-summary article{min-width:0!important;padding:12px 13px!important;border-radius:16px!important;background:#f8fbff!important;border:1px solid #dce5ef!important;overflow:hidden!important}
.clean-start-summary article.summary-wide{grid-column:span 3!important}
.clean-start-summary article:not(.summary-wide){grid-column:span 2!important}
.clean-start-summary small{font-size:12px!important;line-height:1.2!important;margin-bottom:7px!important;color:#60708a!important;font-weight:700!important}
.clean-start-summary b{font-size:15px!important;line-height:1.25!important;font-weight:800!important;display:block!important;color:#071529!important;white-space:normal!important;word-break:break-word!important;overflow-wrap:anywhere!important}
.clean-start-summary .summary-wide b{font-size:16px!important;max-height:42px;overflow:hidden!important;display:-webkit-box!important;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.exam-rule-panel{padding:18px!important;border-radius:20px!important}.exam-rule-panel h2{font-size:1.55rem!important;margin-bottom:12px!important}.exam-rule-list{gap:9px!important}.exam-rule-list li{font-size:13.5px!important;line-height:1.42!important;grid-template-columns:24px 1fr!important}.exam-rule-list li:before{width:24px!important;height:24px!important}
.exam-entry-actions{padding:0 28px 24px!important}.exam-entry-actions .btn{min-height:44px!important;border-radius:14px!important}
@media(max-width:880px){.exam-entry-body{grid-template-columns:1fr!important}.clean-start-summary{grid-template-columns:repeat(2,minmax(0,1fr))!important}.clean-start-summary article,.clean-start-summary article.summary-wide{grid-column:span 1!important}.exam-rule-panel h2{font-size:1.35rem!important}}
@media(max-width:520px){.exam-entry-overlay{padding:12px!important}.exam-entry-card{width:100%!important;max-height:calc(100vh - 20px);border-radius:22px!important}.exam-entry-head{padding:16px!important}.exam-entry-body{padding:18px!important}.exam-entry-title h1{font-size:1.55rem!important}.clean-start-summary{grid-template-columns:1fr!important}.exam-entry-actions{padding:0 18px 18px!important;grid-template-columns:1fr!important}}

</style>
<link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase139-mobile-learning.css'))) ?>">
<link rel="stylesheet" href="<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase154-exam-mobile.css'))) ?>">
</head>
<body class="wf138-exam-mobile">
<div class="exam-entry-overlay" id="entryOverlay"><section class="exam-entry-card"><header class="exam-entry-head"><div class="exam-entry-brand"><img src="<?= e($examLogo) ?>" alt="Logo"><div><b><?= e(app_setting('site_name','Well Fare English Spoken')) ?></b><small>Online Weekly Test Portal</small></div></div><div class="exam-entry-code"><span>Attempt ID</span><b>#<?= e((string)$attemptId) ?></b></div></header><div class="exam-entry-body"><div class="exam-entry-title"><span>Before You Start</span><h1><?= e($attempt['title']) ?></h1><p><?= e($entryIntroText) ?></p><div class="exam-summary-grid clean-start-summary"><article class="summary-wide"><small>Candidate</small><b title="<?= e($candidateName ?: 'Guest Student') ?>"><?= e($candidateName ?: 'Guest Student') ?></b></article><article class="summary-wide"><small>Mobile</small><b><?= e($candidatePhone ?: '-') ?></b></article><article><small>Questions</small><b><?= e((string)count($safe)) ?></b></article><article><small>Time</small><b><?= e((string)$attempt['duration_minutes']) ?> Min</b></article><article><small>Mode</small><b><?= $strictExamMode ? 'Strict' : 'Practice' ?></b></article></div></div><aside class="exam-rule-panel"><h2>Test Rules</h2><ul class="exam-rule-list"><li>One question will appear at a time.</li><li>Your answer saves automatically after every change.</li><li>Use Previous and Next to review answers.</li><li><?= $isBasicWeeklyTest ? 'Practice mode has no warning penalty.' : 'Stay on the exam screen; unusual activity is recorded for teacher review.' ?></li><li>Submit before the timer ends.</li></ul></aside></div><div class="exam-entry-actions"><button class="btn btn-primary" id="enterExamBtn">Start Test Now</button><a class="btn btn-soft" href="weekly-test.php">Cancel</a></div></section></div>

<div class="exam-modal" id="examModal" aria-hidden="true">
  <div class="exam-modal-card" role="dialog" aria-modal="true" aria-labelledby="examModalTitle">
    <div class="exam-modal-icon" id="examModalIcon">!</div>
    <h2 id="examModalTitle">Confirm Action</h2>
    <p id="examModalText">Please confirm to continue.</p>
    <div class="exam-modal-actions">
      <button class="btn btn-soft" id="examModalCancel" type="button">Cancel</button>
      <button class="btn btn-primary" id="examModalOk" type="button">Confirm</button>
    </div>
  </div>
</div>

<main class="exam-app"><header class="exam-topbar"><div class="exam-brand-line"><img src="<?= e($examLogo) ?>" alt="Logo"><div><b><?= e($attempt['title']) ?></b><small><?= e(app_setting('site_name','Well Fare English Spoken')) ?></small></div></div><div class="exam-timer-box" id="timerBox"><div><span>Time Left</span><b id="timer">00:00</b></div></div></header><section class="exam-layout"><div class="exam-paper"><div class="exam-paper-head"><div><h2>Question Paper</h2><p id="paperMeta">Question 1 of <?= count($safe) ?></p></div><div id="saveState" class="admin-mini-pill">Ready</div></div><div class="exam-progress"><span id="progress"></span></div><div class="exam-question-area" id="questionBox"></div><div class="exam-actions"><div class="group"><button class="btn btn-soft" id="prevBtn" type="button"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Previous</span></button><button class="btn btn-soft" id="nextBtn" type="button"><span>Next</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></div><div class="group"><button class="btn btn-soft" id="reportBtn" type="button"><i class="fa-regular fa-flag" aria-hidden="true"></i><span>Report</span></button><button class="btn btn-primary submit-near-answer" id="submitBtn" type="button"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i><span>Final Submit</span></button></div></div></div><aside class="exam-side"><div class="exam-card"><h3>Candidate Details</h3><div class="candidate-table"><div><small>Name</small><b><?= e($candidateName ?: 'Guest Student') ?></b></div><div><small>Mobile</small><b><?= e($candidatePhone ?: '-') ?></b></div><div><small>Attempt</small><b>#<?= e((string)$attemptId) ?></b></div><div><small>Warnings</small><b id="warnCount">0</b></div></div></div><div class="exam-card"><h3>Question Navigator</h3><div class="question-palette" id="palette"></div><div class="legend"><span><i class="l-current"></i> Current</span><span><i class="l-answered"></i> Answered</span><span><i class="l-notanswered"></i> Not Answered</span><span><i class="l-visited"></i> Visited, Not Answered</span><span><i class="l-unanswered"></i> Not Visited</span></div></div><div class="warning-card" id="warningBox"></div><div class="exam-card exam-side-submit"><button class="btn btn-primary" id="sideSubmitBtn" type="button">Submit Test</button><button class="btn btn-soft exam-cancel-btn" id="cancelExamBtn" type="button">Cancel Test</button><small>Submit only after reviewing answered and unanswered questions.</small></div></aside></section></main>
<script>
(function(){
 const csrf=<?= json_encode($csrf) ?>, attemptId=<?= (int)$attemptId ?>, token=<?= json_encode($token) ?>, resultUrl=<?= json_encode($resultUrl) ?>, strictMode=<?= $strictExamMode ? 'true' : 'false' ?>, autoSubmitOnLimit=<?= $autoSubmitOnLimit ? 'true' : 'false' ?>, allowQuestionJump=<?= $allowQuestionJump ? 'true' : 'false' ?>;
 const questions=<?= json_encode($safe, JSON_UNESCAPED_UNICODE) ?>;
 let remaining=<?= (int)$remaining ?>, current=0, answers=<?= json_encode($savedAnswers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, warnings=<?= (int)($attempt['warning_count'] ?? 0) ?>, active=false, timerInt=null, saveInt=null, visited={}, qStart=Date.now(), lastWarningAt=0, lastWarningMessage='';
 const $=id=>document.getElementById(id);
 const esc=v=>String(v||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

 function showModal(opts, onOk){
   $('examModalIcon').textContent = opts.icon || '!';
   $('examModalTitle').textContent = opts.title || 'Notice';
   $('examModalText').textContent = opts.text || '';
   $('examModalOk').textContent = opts.okText || 'OK';
   $('examModalCancel').textContent = opts.cancelText || 'Cancel';
   $('examModalCancel').style.display = opts.hideCancel ? 'none' : '';
   $('examModal').classList.add('active');
   $('examModal').setAttribute('aria-hidden','false');
   const ok=$('examModalOk'), cancel=$('examModalCancel');
   const close=()=>{$('examModal').classList.remove('active');$('examModal').setAttribute('aria-hidden','true');ok.onclick=null;cancel.onclick=null;};
   ok.onclick=()=>{close(); if(onOk) onOk();};
   cancel.onclick=close;
 }

 async function enterStrictMode(){
   if(!strictMode) return;
   try{
     if(document.documentElement.requestFullscreen && !document.fullscreenElement){
       await document.documentElement.requestFullscreen();
     }
   }catch(e){ markWarning('Fullscreen permission denied'); }
   try{
     if(navigator.keyboard && navigator.keyboard.lock){
       await navigator.keyboard.lock(['Escape','AltLeft','AltRight','Tab','MetaLeft','MetaRight','ControlLeft','ControlRight']);
     }
   }catch(e){}
 }

 function focusShieldText(){
   return 'This is a controlled exam screen. Stay on this page until submission. System keys like Win+D cannot be blocked by a normal browser, but leaving this screen is detected and recorded.';
 }

 function warningText(count, penaltyActive, penaltyPreview){
   if (penaltyActive && count > 1) {
     return 'Security warning '+count+' recorded. Repeated suspicious activity may deduct marks from your correct answers. Current estimated deduction: '+penaltyPreview+' mark(s). Please stay on the test screen.';
   }
   return 'Security warning '+count+' recorded. Please stay on the test screen. Repeated suspicious activity will be visible to admin during review.';
 }

 function markWarning(msg){
   if(!active) return;
   const now=Date.now();
   if((msg===lastWarningMessage && now-lastWarningAt<10000) || now-lastWarningAt<4000) return;
   lastWarningAt=now; lastWarningMessage=msg;
   const fd=new FormData();
   fd.append('csrf_token',csrf); fd.append('action','warning'); fd.append('attempt_id',attemptId); fd.append('access_token',token); fd.append('message',msg);
   fetch('weekly-test-api.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
       warnings = Number(d.warning_count || (warnings+1));
       $('warnCount').textContent=warnings;
       $('warningBox').style.display='block';
       $('warningBox').textContent='Warning '+warnings+': '+msg;
       showModal({icon:'!',title:'Security Warning',text:warningText(warnings,!!d.penalty_active,d.penalty_preview||0),okText:'I Understand',hideCancel:true}); if(d.should_auto_submit && autoSubmitOnLimit){setTimeout(()=>{submitFinal();},900);}
    }).catch(()=>{
       warnings++; $('warnCount').textContent=warnings;
       showModal({icon:'!',title:'Security Warning',text:'Suspicious activity was detected. Please stay on the test screen.',okText:'I Understand',hideCancel:true});
    });
 }

 function currentAnswer(){
   const q=questions[current]; if(!q) return;
   const checked=document.querySelector('[name="q'+q.id+'"]:checked');
   const text=document.querySelector('textarea[name="q'+q.id+'"]');
   answers[q.id]=checked?checked.value:(text?text.value:'');
 }
 function isAnswered(qid){return String(answers[qid]||'').trim()!=='';}
 function markVisited(){const q=questions[current]; if(q) visited[q.id]=true;}

 function pushTiming(nextIndex){
   const q=questions[current]; if(!q || !active) return;
   const spent=Math.max(0,Math.round((Date.now()-qStart)/1000));
   const fd=new FormData();
   fd.append('csrf_token',csrf); fd.append('action','timing'); fd.append('attempt_id',attemptId); fd.append('access_token',token);
   fd.append('timing_json',JSON.stringify({question_id:q.id,question_no:current+1,seconds:spent,answered:isAnswered(q.id),next_question:nextIndex+1}));
   fetch('weekly-test-api.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).catch(()=>{});
   qStart=Date.now();
 }

 function renderPalette(){
   const box=$('palette'); box.innerHTML='';
   questions.forEach((q,i)=>{
     const btn=document.createElement('button'); btn.type='button'; btn.textContent=i+1;
     let statusClass='not-visited';
     if (isAnswered(q.id)) statusClass='answered';
     else if (visited[q.id]) statusClass='not-answered';
     if (i===current) statusClass='current';
     btn.className=statusClass;
     btn.onclick=()=>{if(!allowQuestionJump && i!==current){showModal({icon:'!',title:'One-by-one mode',text:'Please answer questions in sequence using Previous and Next.',okText:'OK',hideCancel:true});return;}currentAnswer(); if(i!==current){pushTiming(i); current=i; render();}};
     box.appendChild(btn);
   });
 }

 function render(){
   const q=questions[current]; if(!q) return;
   markVisited();
   $('paperMeta').textContent='Question '+(current+1)+' of '+questions.length;
   $('progress').style.width=((current+1)/Math.max(1,questions.length)*100)+'%';
   const saved=answers[q.id]||'';
   const opts=(q.options||[]).map(o=>'<label class="exam-option"><input type="radio" name="q'+q.id+'" value="'+esc(o)+'" '+(saved===o?'checked':'')+'><span>'+esc(o)+'</span></label>').join('');
   const ans=opts?opts:'<div class="exam-answer"><textarea name="q'+q.id+'" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="Type your answer here...">'+esc(saved)+'</textarea></div>';
   $('questionBox').innerHTML='<div class="exam-q-meta"><span>Question '+(current+1)+'</span><em>'+esc(q.marks)+' mark</em></div><h1>'+esc(q.question)+'</h1>'+ans;
   $('prevBtn').disabled=current===0; $('nextBtn').disabled=current>=questions.length-1;
   renderPalette(); save();
 }

 function save(){
   if(!active) return;
   currentAnswer();
   const fd=new FormData(); fd.append('csrf_token',csrf); fd.append('action','autosave'); fd.append('attempt_id',attemptId); fd.append('access_token',token); fd.append('answers_json',JSON.stringify(answers));
   fetch('weekly-test-api.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{if(d.expired&&d.result_url){active=false;location.href=d.result_url;return;} $('saveState').textContent=d.success?'Saved':'Save pending';})
    .catch(()=>{$('saveState').textContent='Offline pending';});
 }

 function submitFinal(){
   currentAnswer(); pushTiming(current);
   const fd=new FormData(); fd.append('csrf_token',csrf); fd.append('action','submit'); fd.append('attempt_id',attemptId); fd.append('access_token',token); fd.append('answers_json',JSON.stringify(answers));
   fetch('weekly-test-api.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{if(!d.success) throw new Error(d.message||'Submit failed'); active=false; location.href=d.result_url||resultUrl;})
    .catch(e=>showModal({icon:'!',title:'Submit Failed',text:e.message,okText:'OK',hideCancel:true}));
 }

 function submitWithModal(){
   currentAnswer();
   const answeredCount=questions.filter(q=>isAnswered(q.id)).length;
   const unanswered=Math.max(0,questions.length-answeredCount);
   showModal({icon:'✓',title:'Submit final test?',text:'Answered: '+answeredCount+' / '+questions.length+'. Unanswered: '+unanswered+'. You can submit even if some answers are blank. After final submission, answers cannot be changed.',okText:'Final Submit',cancelText:'Review Again'}, submitFinal);
 }

 function cancelTest(){
   showModal({icon:'×',title:'Cancel this test?',text:'Your current test will be closed and marked as cancelled. Submitted answers will not be counted as final result.',okText:'Yes, Cancel Test',cancelText:'Continue Test'}, ()=>{
     const fd=new FormData(); fd.append('csrf_token',csrf); fd.append('action','cancel_attempt'); fd.append('attempt_id',attemptId); fd.append('access_token',token);
     fetch('weekly-test-api.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(d=>{active=false; location.href='weekly-test.php';})
      .catch(()=>{active=false; location.href='weekly-test.php';});
   });
 }

 function tick(){
   remaining--;
   if(remaining<0){submitFinal();return;}
   const m=Math.floor(remaining/60), s=remaining%60;
   $('timer').innerHTML=String(m).padStart(2,'0')+'<small style="font-size:10px;color:#cbd7ea;margin:0 4px">min</small>'+String(s).padStart(2,'0')+'<small style="font-size:10px;color:#cbd7ea;margin-left:4px">sec</small>';
   $('timerBox').classList.toggle('danger',remaining<300);
 }

 $('enterExamBtn').addEventListener('click',()=>{
   $('entryOverlay').style.display='none';
   $('warnCount').textContent=warnings;
   active=true;
   document.body.classList.add('exam-active');
   enterStrictMode();
   render(); tick(); timerInt=setInterval(tick,1000); saveInt=setInterval(save,15000);
 });
 $('nextBtn').onclick=()=>{currentAnswer(); if(current<questions.length-1){pushTiming(current+1); current++; render();}};
 $('prevBtn').onclick=()=>{currentAnswer(); if(current>0){pushTiming(current-1); current--; render();}};
 if($('submitBtn')) $('submitBtn').onclick=submitWithModal;
 if($('sideSubmitBtn')) $('sideSubmitBtn').onclick=submitWithModal;
 if($('cancelExamBtn')) $('cancelExamBtn').onclick=cancelTest;
 $('reportBtn').onclick=()=>showModal({icon:'?',title:'Report exam issue?',text:'This will notify admin and save a note with your attempt.',okText:'Report Issue',cancelText:'Cancel'},()=>markWarning('Candidate reported an exam issue'));
 document.addEventListener('visibilitychange',()=>{if(active&&document.hidden)markWarning('Tab or app switch detected');});
 document.addEventListener('fullscreenchange',()=>{if(active&&strictMode&&!document.fullscreenElement)markWarning('Fullscreen exited');});
 window.addEventListener('blur',()=>{if(active)markWarning('Window focus changed');});
 ['copy','cut','contextmenu','dragstart'].forEach(ev=>document.addEventListener(ev,e=>{if(active)e.preventDefault();}));
 document.addEventListener('paste',e=>{if(active){e.preventDefault();markWarning('Keyboard shortcut blocked');}});
 document.addEventListener('keydown',e=>{if(!active)return;const k=(e.key||'').toLowerCase();if(e.ctrlKey||e.metaKey||e.altKey||['f12','printscreen'].includes(k)){e.preventDefault();markWarning('Keyboard shortcut blocked');}});
 window.addEventListener('beforeunload',e=>{if(active){e.preventDefault();e.returnValue='Test is running';}});
})();
</script>
<script src="<?= e(app_asset_versioned('assets/js/phase139-mobile-learning.js')) ?>" defer></script>
</body>
</html>
