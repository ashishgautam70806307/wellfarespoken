<?php
require_once __DIR__ . '/_header.php';
weekly_test_ensure_schema();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    if (($_POST['action'] ?? '') === 'hide_attempt') {
        $id = (int)($_POST['attempt_id'] ?? 0);
        if ($id > 0) {
            db()->prepare("UPDATE weekly_test_attempts SET status_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
            flash('success', 'Attempt hidden. It will be permanently cleaned after 15 days.');
        }
        redirect($_SERVER['REQUEST_URI'] ?? 'weekly-tests.php#student-copies');
    }
}

$phone = trim((string)($_GET['phone'] ?? ''));
$key = trim((string)($_GET['key'] ?? ''));
$attemptId = (int)($_GET['attempt_id'] ?? 0);
$filterType = $_GET['type'] ?? '';
if (!in_array($filterType, ['basic','previous','upcoming'], true)) $filterType = '';

$where = [];
$params = [];
$titleName = 'Student Record';
$titlePhone = $phone ?: $key;

if ($phone !== '') {
    $digits = preg_replace('/\D+/', '', $phone);
    $where[] = "(COALESCE(NULLIF(a.canonical_phone,''), RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(NULLIF(s.phone,''),NULLIF(a.guest_phone,''),''),' ',''),'-',''),'+91',''),'+',''),10))=?)";
    $params[] = weekly_test_clean_phone($digits);
} elseif ($key !== '') {
    $where[] = "COALESCE(NULLIF(s.phone,''), NULLIF(a.guest_phone,''), CONCAT('student-',a.student_id), CONCAT('guest-',a.id))=?";
    $params[] = $key;
} elseif ($attemptId > 0) {
    $where[] = "a.id=?";
    $params[] = $attemptId;
} else {
    flash('error','Student record not found.');
    redirect('weekly-tests.php#student-copies');
}
if ($filterType !== '') {
    $where[] = "t.test_type=?";
    $params[] = $filterType;
}

$sql = "SELECT a.*, t.title test_title, t.test_type, t.duration_minutes, t.total_marks, s.full_name student_name, s.phone student_phone
        FROM weekly_test_attempts a
        JOIN weekly_tests t ON t.id=a.test_id
        LEFT JOIN students s ON s.id=a.student_id
        WHERE COALESCE(a.status_deleted,0)=0 AND " . implode(' AND ', $where) . "
        ORDER BY COALESCE(a.submitted_at,a.started_at) DESC, a.id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$attempts = $stmt->fetchAll();

if ($attempts) {
    $first = $attempts[0];
    $titleName = $first['student_name'] ?: $first['guest_name'] ?: 'Guest Student';
    $titlePhone = $first['student_phone'] ?: $first['guest_phone'] ?: $titlePhone;
}

$typeLabel = ['basic'=>'Basic Test','previous'=>'Previous Test','upcoming'=>'Upcoming Test'];
$typeNote = [
    'basic'=>'Practice records only. No penalty needed.',
    'previous'=>'Missed paper records. Review and publish marks.',
    'upcoming'=>'Scheduled exam records with strict mode.'
];
$byType = ['basic'=>[], 'previous'=>[], 'upcoming'=>[]];
foreach ($attempts as $a) {
    $type = $a['test_type'] ?? 'basic';
    if (!isset($byType[$type])) $byType[$type] = [];
    $dateKey = date('d M Y', strtotime((string)($a['submitted_at'] ?: $a['started_at'] ?: 'now')));
    $byType[$type][$dateKey][] = $a;
}

$summary = [
    'total'=>count($attempts),
    'basic'=>0,
    'previous'=>0,
    'upcoming'=>0,
    'pending'=>0,
    'checked'=>0,
    'warnings'=>0,
];
foreach ($attempts as $a) {
    $t = $a['test_type'] ?? 'basic';
    if (isset($summary[$t])) $summary[$t]++;
    if (($a['status'] ?? '') === 'submitted') $summary['pending']++;
    if (($a['status'] ?? '') === 'checked') $summary['checked']++;
    $summary['warnings'] += (int)($a['warning_count'] ?? 0);
}

function weekly_record_answers(int $attemptId): array {
    $stmt = db()->prepare("SELECT ans.*, q.question_text, q.expected_answer, q.question_type, q.topic_name, q.marks FROM weekly_test_answers ans JOIN weekly_test_questions q ON q.id=ans.question_id WHERE ans.attempt_id=? ORDER BY q.sort_order ASC, q.id ASC");
    $stmt->execute([$attemptId]);
    return $stmt->fetchAll();
}
function weekly_record_status_text(string $status): string {
    return [
        'started'=>'In Progress',
        'submitted'=>'Pending Check',
        'checked'=>'Checked',
        'cancelled'=>'Cancelled',
    ][$status] ?? ucfirst($status);
}
function weekly_record_score($auto, $admin): string {
    $a = ($auto === null || $auto === '') ? '-' : (string)$auto;
    $m = ($admin === null || $admin === '') ? '-' : (string)$admin;
    return 'Auto ' . $a . ' / Admin ' . $m;
}
?>
<div class="admin-page-head weekly-pro-head">
  <div>
    <span class="eyebrow">Student Copy Record</span>
    <h1><?= e($titleName) ?></h1>
    <p><?= e($titlePhone ?: 'No mobile saved') ?> • <?= $filterType ? e($typeLabel[$filterType]) . ' date-wise activity' : 'Date-wise Basic, Previous and Upcoming test activity' ?>.</p>
  </div>
  <div class="head-actions">
    <a class="btn btn-soft" href="weekly-tests.php?<?= $filterType ? 'type='.urlencode($filterType).'&atype='.urlencode($filterType) : '' ?>#student-copies">Back to Copies</a>
    <a class="btn btn-primary" href="weekly-tests.php">Weekly Dashboard</a>
  </div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="record-type-tabs">
  <a class="<?= $filterType===''?'active':'' ?>" href="weekly-student-record.php?<?= $phone!=='' ? 'phone='.urlencode($phone) : ($key!=='' ? 'key='.urlencode($key) : 'attempt_id='.urlencode((string)$attemptId)) ?>">All Records</a>
  <?php foreach($typeLabel as $k=>$v): ?>
    <a class="<?= $filterType===$k?'active':'' ?>" href="weekly-student-record.php?<?= $phone!=='' ? 'phone='.urlencode($phone) : ($key!=='' ? 'key='.urlencode($key) : 'attempt_id='.urlencode((string)$attemptId)) ?>&type=<?= e($k) ?>"><?= e($v) ?></a>
  <?php endforeach; ?>
</div>

<?php if(!$attempts): ?>
  <div class="admin-card"><p>No test records found for this student/mobile.</p></div>
<?php else: ?>
<div class="student-record-dashboard">
  <div><b><?= e((string)$summary['total']) ?></b><span>Total Attempts</span></div>
  <div><b><?= e((string)$summary['pending']) ?></b><span>Pending Check</span></div>
  <div><b><?= e((string)$summary['checked']) ?></b><span>Checked</span></div>
  <div><b><?= e((string)$summary['warnings']) ?></b><span>Total Warnings</span></div>
  <div><b><?= e((string)$summary['upcoming']) ?></b><span>Upcoming Exams</span></div>
</div>

<?php foreach(($filterType ? [$filterType] : ['basic','previous','upcoming']) as $type): ?>
  <section class="record-type-card">
    <div class="record-type-head">
      <div><h2><?= e($typeLabel[$type]) ?></h2><p><?= e($typeNote[$type]) ?></p></div>
      <span class="record-pill"><?= e((string)$summary[$type]) ?> record<?= $summary[$type]===1?'':'s' ?></span>
    </div>
    <?php if(empty($byType[$type])): ?>
      <div class="record-date-group"><p class="muted">No <?= e($typeLabel[$type]) ?> records yet.</p></div>
    <?php endif; ?>
    <?php foreach($byType[$type] as $date=>$items): ?>
      <div class="record-date-group">
        <div class="record-date-title"><span><?= e($date) ?></span><small><?= e((string)count($items)) ?> attempt<?= count($items)===1?'':'s' ?></small></div>
        <div class="record-attempt-list">
          <?php foreach($items as $a): $answers = weekly_record_answers((int)$a['id']); ?>
            <details class="student-attempt-card">
              <summary>
                <div><b><?= e($a['test_title']) ?></b><small><?= e(date('h:i A', strtotime((string)($a['submitted_at'] ?: $a['started_at'] ?: 'now')))) ?> • Attempt #<?= e((string)$a['id']) ?></small></div>
                <span><?= e(weekly_record_status_text((string)$a['status'])) ?></span>
                <em><?= e(weekly_record_score($a['auto_score'] ?? null, $a['admin_score'] ?? null)) ?></em>
                <a class="btn btn-soft btn-sm" href="weekly-tests.php?type=<?= e($type) ?>&review=<?= e((string)$a['id']) ?>#review">Review</a>
              </summary>
              <div class="record-mini-grid">
                <div><b>Warnings</b><span><?= e((string)($a['warning_count'] ?? 0)) ?></span></div>
                <div><b>Penalty</b><span><?= e((string)($a['penalty_marks'] ?? 0)) ?></span></div>
                <div><b>Total Marks</b><span><?= e((string)($a['total_marks'] ?? '-')) ?></span></div>
                <div><b>Questions</b><span><?= e((string)count($answers)) ?></span></div>
              </div>
              <form method="post" class="record-hide-form" data-confirm="Hide this attempt from admin records? It will be permanently cleaned after 15 days.">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="hide_attempt"><input type="hidden" name="attempt_id" value="<?= e((string)$a['id']) ?>">
                <button class="btn btn-danger btn-sm" type="submit">Hide Attempt</button>
              </form>
              <?php if(!empty($a['activity_log'])): ?>
                <details class="cheat-log"><summary>Show security/activity log</summary><pre><?= e($a['activity_log']) ?></pre></details>
              <?php endif; ?>
              <details class="record-answer-wrapper">
                <summary class="record-answer-main-summary">Show question answers</summary>
                <div class="record-answer-list">
                  <?php foreach($answers as $idx=>$ans): ?>
                    <details class="record-answer">
                      <summary>Q<?= $idx+1 ?>. <?= e($ans['question_type']) ?><?= $ans['topic_name']?' • '.e($ans['topic_name']):'' ?> <span><?= e((string)($ans['marks_awarded'] ?? 0)) ?>/<?= e((string)$ans['marks']) ?></span></summary>
                      <p><b>Question:</b> <?= e($ans['question_text']) ?></p>
                      <p><b>Student:</b> <?= e($ans['answer_text'] ?: 'No answer') ?></p>
                      <p><b>Expected:</b> <?= nl2br(e($ans['expected_answer'])) ?></p>
                      <?php if(!empty($ans['admin_note'])): ?><p><b>Note:</b> <?= e($ans['admin_note']) ?></p><?php endif; ?>
                    </details>
                  <?php endforeach; ?>
                  <?php if(!$answers): ?><p class="muted small">No answer rows saved for this attempt.</p><?php endif; ?>
                </div>
              </details>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
<?php endforeach; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
