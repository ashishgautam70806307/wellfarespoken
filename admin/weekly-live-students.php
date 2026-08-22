<?php
$admin_page_final_styles = ['assets/css/phase182-live-test-control.css'];
require_once __DIR__ . '/_header.php';
weekly_test_ensure_schema();
// Keep the "Live Now" list truthful by finalizing only attempts whose server timer genuinely expired.
weekly_test_finalize_started_attempts(0, false);

function weekly_live_redirect_query(array $source): string {
    $pairs = [];
    foreach (['batch','test_id','result_status','q','page'] as $key) {
        $value = trim((string)($source[$key] ?? ''));
        if ($value !== '') $pairs[$key] = $value;
    }
    return $pairs ? ('?' . http_build_query($pairs)) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security check failed. Refresh the page and try again.');
        redirect('weekly-live-students.php' . weekly_live_redirect_query($_POST));
    }
    if (($_POST['action'] ?? '') === 'reopen_attempt') {
        $attemptId = max(0, (int)($_POST['attempt_id'] ?? 0));
        $reason = trim((string)($_POST['reason'] ?? ''));
        $timeMode = trim((string)($_POST['time_mode'] ?? 'remaining'));
        $result = weekly_test_reopen_attempt_for_admin($attemptId, $reason, $timeMode);
        flash(!empty($result['success']) ? 'success' : 'error', (string)($result['message'] ?? 'Reopen Access could not be applied.'));
        redirect('weekly-live-students.php' . weekly_live_redirect_query($_POST));
    }
}

$tests = weekly_test_fetch_tests('upcoming');

// Batch filter is based on the student's effective test batch, not only on the paper assignment.
// Assigned papers use weekly_tests.batch_id; common papers use the student's latest Active batch membership.
$batchOptions = ['all'=>['key'=>'all','id'=>-1,'label'=>'All Batches']];
if (table_exists('batch_timings')) {
    try {
        $batchRows = db()->query("SELECT id,batch_name,timing,published FROM batch_timings ORDER BY (published='Yes') DESC, sort_order ASC, id DESC LIMIT 500")->fetchAll();
        foreach ($batchRows as $batchRow) {
            $bid = max(0, (int)($batchRow['id'] ?? 0));
            if ($bid <= 0) continue;
            $label = trim((string)($batchRow['batch_name'] ?? 'Batch'));
            $timing = trim((string)($batchRow['timing'] ?? ''));
            if ($timing !== '') $label .= ' - ' . $timing;
            if (strtolower((string)($batchRow['published'] ?? 'yes')) !== 'yes') $label .= ' (Inactive)';
            $batchOptions['batch-'.$bid] = ['key'=>'batch-'.$bid,'id'=>$bid,'label'=>$label];
        }
    } catch (Throwable $e) {
        error_log('[weekly-live] batch filter load failed: ' . $e->getMessage());
    }
}
$batchOptions['common'] = ['key'=>'common','id'=>0,'label'=>'Common / Unmapped'];

$requestedBatch = trim((string)($_GET['batch'] ?? 'all'));
if (!isset($batchOptions[$requestedBatch])) $requestedBatch = 'all';
$requestedBatchId = (int)($batchOptions[$requestedBatch]['id'] ?? -1);
$requestedTestId = max(0, (int)($_GET['test_id'] ?? 0));

// When one batch is selected, show papers assigned to that batch plus Common/All-Batch papers.
$visibleTests = $tests;
if ($requestedBatchId > 0) {
    $visibleTests = array_values(array_filter($tests, static function(array $test) use ($requestedBatchId): bool {
        $paperBatchId = max(0, (int)($test['batch_id'] ?? 0));
        return $paperBatchId === 0 || $paperBatchId === $requestedBatchId;
    }));
} elseif ($requestedBatch === 'common') {
    $visibleTests = array_values(array_filter($tests, static fn(array $test): bool => max(0, (int)($test['batch_id'] ?? 0)) === 0));
}
$visibleTestIds = array_values(array_filter(array_map(static fn($t)=>(int)($t['id'] ?? 0), $visibleTests), static fn($v)=>$v>0));
if ($requestedTestId > 0 && !in_array($requestedTestId, $visibleTestIds, true)) $requestedTestId = 0;
$filterTestIds = $requestedTestId > 0 ? [$requestedTestId] : $visibleTestIds;

$resultStatus = strtolower(trim((string)($_GET['result_status'] ?? 'all')));
if (!in_array($resultStatus, ['all','submitted','checked'], true)) $resultStatus = 'all';
$search = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 40;

$liveRows = [];
$resultRows = [];
$resultTotal = 0;
$winnerTests = [];
$liveBatchCounts = [];
$liveTotalAll = 0;
$hasMembershipBatch = table_exists('student_batch_memberships') && table_exists('batch_timings');
$batchExpr = "CASE WHEN COALESCE(t.batch_id,0)>0 THEN COALESCE(NULLIF(t.batch_label,''),NULLIF(bt.batch_name,''),'Assigned Batch') ELSE 'Common / All Batches' END";
$batchIdExpr = "COALESCE(t.batch_id,0)";
if ($hasMembershipBatch) {
    $batchExpr = "CASE WHEN COALESCE(t.batch_id,0)>0 THEN COALESCE(NULLIF(t.batch_label,''),NULLIF(bt.batch_name,''),'Assigned Batch') ELSE COALESCE((SELECT NULLIF(b2.batch_name,'') FROM student_batch_memberships sbm2 JOIN batch_timings b2 ON b2.id=sbm2.batch_id WHERE sbm2.student_id=a.student_id AND sbm2.membership_status='Active' ORDER BY sbm2.id DESC LIMIT 1),'Common / All Batches') END";
    $batchIdExpr = "CASE WHEN COALESCE(t.batch_id,0)>0 THEN t.batch_id ELSE COALESCE((SELECT sbm2.batch_id FROM student_batch_memberships sbm2 WHERE sbm2.student_id=a.student_id AND sbm2.membership_status='Active' ORDER BY sbm2.id DESC LIMIT 1),0) END";
}

// Always show a truthful batch-wise Live Now summary across all Upcoming papers.
$allUpcomingIds = array_values(array_filter(array_map(static fn($t)=>(int)($t['id'] ?? 0), $tests), static fn($v)=>$v>0));
if ($allUpcomingIds) {
    $allIn = implode(',', array_fill(0, count($allUpcomingIds), '?'));
    $summarySql = "SELECT {$batchIdExpr} effective_batch_id, {$batchExpr} display_batch, COUNT(*) live_count
                   FROM weekly_test_attempts a
                   JOIN weekly_tests t ON t.id=a.test_id
                   LEFT JOIN batch_timings bt ON bt.id=t.batch_id
                   WHERE COALESCE(a.status_deleted,0)=0 AND a.status='started' AND a.test_id IN ($allIn)
                   GROUP BY effective_batch_id, display_batch ORDER BY live_count DESC, display_batch ASC";
    $summaryStmt = db()->prepare($summarySql);
    $summaryStmt->execute($allUpcomingIds);
    foreach ($summaryStmt->fetchAll() as $summaryRow) {
        $count = max(0, (int)($summaryRow['live_count'] ?? 0));
        $bid = max(0, (int)($summaryRow['effective_batch_id'] ?? 0));
        $liveTotalAll += $count;
        $liveBatchCounts[] = ['batch_id'=>$bid,'label'=>(string)($summaryRow['display_batch'] ?? 'Common / Unmapped'),'count'=>$count];
    }
}

if ($filterTestIds) {
    $in = implode(',', array_fill(0, count($filterTestIds), '?'));

    $liveSql = "SELECT a.*, t.title test_title, t.duration_minutes, t.status test_status, t.ends_at test_ends_at,
                       COALESCE(NULLIF(s.full_name,''),NULLIF(a.guest_name,''),'Student') student_name,
                       COALESCE(NULLIF(s.phone,''),NULLIF(a.guest_phone,''),'') student_phone,
                       {$batchExpr} display_batch
                FROM weekly_test_attempts a
                JOIN weekly_tests t ON t.id=a.test_id
                LEFT JOIN students s ON s.id=a.student_id
                LEFT JOIN batch_timings bt ON bt.id=t.batch_id
                WHERE COALESCE(a.status_deleted,0)=0 AND a.status='started' AND a.test_id IN ($in)";
    $liveParams = $filterTestIds;
    if ($requestedBatchId >= 0) {
        $liveSql .= " AND ({$batchIdExpr})=?";
        $liveParams[] = $requestedBatchId;
    }
    if ($search !== '') {
        $liveSql .= " AND (COALESCE(s.full_name,a.guest_name,'') LIKE ? OR COALESCE(s.phone,a.guest_phone,'') LIKE ? OR t.title LIKE ?)";
        $like = '%'.$search.'%';
        array_push($liveParams,$like,$like,$like);
    }
    $liveSql .= " ORDER BY a.started_at ASC, a.id ASC LIMIT 250";
    $stmt = db()->prepare($liveSql); $stmt->execute($liveParams); $liveRows = $stmt->fetchAll();

    $where = ["COALESCE(a.status_deleted,0)=0", "a.status IN ('submitted','checked')", "a.test_id IN ($in)"];
    $params = $filterTestIds;
    if ($requestedBatchId >= 0) { $where[] = "({$batchIdExpr})=?"; $params[] = $requestedBatchId; }
    if ($resultStatus !== 'all') { $where[] = 'a.status=?'; $params[] = $resultStatus; }
    if ($search !== '') {
        $where[] = "(COALESCE(s.full_name,a.guest_name,'') LIKE ? OR COALESCE(s.phone,a.guest_phone,'') LIKE ? OR t.title LIKE ?)";
        $like = '%'.$search.'%'; array_push($params,$like,$like,$like);
    }
    $count = db()->prepare("SELECT COUNT(*) FROM weekly_test_attempts a JOIN weekly_tests t ON t.id=a.test_id LEFT JOIN students s ON s.id=a.student_id WHERE ".implode(' AND ',$where));
    $count->execute($params); $resultTotal = (int)$count->fetchColumn();
    $pages = max(1, (int)ceil($resultTotal/$perPage)); $page = min($page,$pages); $offset = ($page-1)*$perPage;
    $resultSql = "SELECT a.*, t.title test_title, t.duration_minutes, t.status test_status, t.ends_at test_ends_at,
                         COALESCE(NULLIF(s.full_name,''),NULLIF(a.guest_name,''),'Student') student_name,
                         COALESCE(NULLIF(s.phone,''),NULLIF(a.guest_phone,''),'') student_phone,
                         {$batchExpr} display_batch
                  FROM weekly_test_attempts a
                  JOIN weekly_tests t ON t.id=a.test_id
                  LEFT JOIN students s ON s.id=a.student_id
                  LEFT JOIN batch_timings bt ON bt.id=t.batch_id
                  WHERE ".implode(' AND ',$where)."
                  ORDER BY COALESCE(a.submitted_at,a.started_at) DESC,a.id DESC LIMIT {$perPage} OFFSET {$offset}";
    $stmt = db()->prepare($resultSql); $stmt->execute($params); $resultRows = $stmt->fetchAll();

    $winnerStmt = db()->prepare("SELECT DISTINCT test_id FROM weekly_test_winners WHERE test_id IN ($in)");
    $winnerStmt->execute($filterTestIds);
    foreach ($winnerStmt->fetchAll(PDO::FETCH_COLUMN) as $wid) $winnerTests[(int)$wid] = true;
} else {
    $pages = 1;
}

function weekly_live_hms(int $seconds): string {
    $seconds=max(0,$seconds); $m=(int)floor($seconds/60); $s=$seconds%60;
    return $m.'m '.str_pad((string)$s,2,'0',STR_PAD_LEFT).'s';
}
function weekly_live_reopen_ui(array $row, array $winnerTests): array {
    if (($row['status'] ?? '') !== 'submitted') return ['ok'=>false,'why'=>'Already checked'];
    if (empty($row['student_id'])) return ['ok'=>false,'why'=>'Login account required'];
    if (trim((string)($row['submission_reason'] ?? 'manual_submit')) !== 'manual_submit') return ['ok'=>false,'why'=>'System/admin final submit'];
    if ((int)($row['reopen_count'] ?? 0) >= 1) return ['ok'=>false,'why'=>'Reopened once already'];
    $testId=(int)($row['test_id']??0);
    if (!empty($winnerTests[$testId])) return ['ok'=>false,'why'=>'Top 3 finalized'];
    if (weekly_test_answers_manually_released($testId)) return ['ok'=>false,'why'=>'Answer key released'];
    $status=strtolower((string)($row['test_status']??''));
    if (in_array($status,['archived','closed','completed'],true)) return ['ok'=>false,'why'=>'Test finalized'];
    $end=!empty($row['test_ends_at'])?strtotime((string)$row['test_ends_at']):false;
    if ($end!==false && $end<=time()) return ['ok'=>false,'why'=>'Test window ended'];
    return ['ok'=>true,'why'=>''];
}

$currentBatchLabel = (string)($batchOptions[$requestedBatch]['label'] ?? 'All Batches');
$selectedTestLabel = 'All Upcoming Tests in this batch';
if ($requestedTestId > 0) foreach ($visibleTests as $t) if ((int)$t['id']===$requestedTestId) { $selectedTestLabel=(string)$t['title']; break; }
$queryBase = ['batch'=>$requestedBatch,'test_id'=>$requestedTestId ?: '', 'result_status'=>$resultStatus,'q'=>$search];
?>
<div class="admin-page-head wf182-head">
  <div>
    <span class="eyebrow">Upcoming Test Live Control</span>
    <h1>Live Students & Reopen Access</h1>
    <p>Live section shows only students currently inside an Upcoming Test. Finished copies stay separately filtered below.</p>
  </div>
  <div class="head-actions">
    <a class="btn btn-soft" href="weekly-tests.php?type=upcoming"><i class="fa-solid fa-arrow-left"></i> Weekly Tests</a>
    <a class="btn btn-soft" href="upcoming-test-performance.php"><i class="fa-solid fa-ranking-star"></i> Performance</a>
    <a class="btn btn-primary" href="weekly-live-students.php?<?= e(http_build_query($queryBase)) ?>"><i class="fa-solid fa-rotate"></i> Refresh Live</a>
  </div>
</div>
<?php if ($msg=flash('success')): ?><div class="alert alert-success" data-auto-toast="success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg=flash('error')): ?><div class="alert alert-danger" data-auto-toast="error"><?= e($msg) ?></div><?php endif; ?>

<section class="admin-card wf182-filter-card">
  <form method="get" class="wf182-filter-grid">
    <label>Batch
      <select name="batch" onchange="this.form.test_id.value='';this.form.submit()">
        <?php foreach($batchOptions as $key=>$opt): ?><option value="<?= e($key) ?>" <?= $requestedBatch===$key?'selected':'' ?>><?= e((string)$opt['label']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Test Paper
      <select name="test_id" onchange="this.form.submit()">
        <option value="">All tests in selected batch</option>
        <?php foreach($visibleTests as $t): ?><option value="<?= e((string)$t['id']) ?>" <?= $requestedTestId===(int)$t['id']?'selected':'' ?>><?= e((string)$t['title']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Result Status
      <select name="result_status" onchange="this.form.submit()">
        <option value="all" <?= $resultStatus==='all'?'selected':'' ?>>All finished copies</option>
        <option value="submitted" <?= $resultStatus==='submitted'?'selected':'' ?>>Pending Check</option>
        <option value="checked" <?= $resultStatus==='checked'?'selected':'' ?>>Checked</option>
      </select>
    </label>
    <label>Search Student
      <div class="wf182-search"><input type="search" name="q" value="<?= e($search) ?>" placeholder="Name / mobile / test"><button class="btn btn-soft" type="submit">Search</button></div>
    </label>
  </form>
  <div class="wf182-scope-note"><i class="fa-solid fa-filter"></i><span><b><?= e($currentBatchLabel) ?></b> • <?= e($selectedTestLabel) ?></span></div>
</section>

<section class="admin-card wf182-batch-live-card">
  <div class="wf182-batch-live-head"><div><span class="dash-mini">Batch-wise current test count</span><h2>Live Now by Batch</h2></div><strong><?= e((string)$liveTotalAll) ?> student<?= $liveTotalAll===1?'':'s' ?> live</strong></div>
  <div class="wf182-batch-chips">
    <?php $allQ=$queryBase; $allQ['batch']='all'; $allQ['test_id']=''; $allQ['page']=''; ?>
    <a class="wf182-batch-chip <?= $requestedBatch==='all'?'active':'' ?>" href="weekly-live-students.php?<?= e(http_build_query(array_filter($allQ,static fn($v)=>$v!==''))) ?>"><span>All Batches</span><b><?= e((string)$liveTotalAll) ?></b></a>
    <?php foreach($liveBatchCounts as $batchCount): $batchKey=((int)$batchCount['batch_id']>0?'batch-'.(int)$batchCount['batch_id']:'common'); $bq=$queryBase; $bq['batch']=$batchKey; $bq['test_id']=''; $bq['page']=''; ?>
      <a class="wf182-batch-chip <?= $requestedBatch===$batchKey?'active':'' ?>" href="weekly-live-students.php?<?= e(http_build_query(array_filter($bq,static fn($v)=>$v!==''))) ?>"><span><?= e((string)$batchCount['label']) ?></span><b><?= e((string)$batchCount['count']) ?></b></a>
    <?php endforeach; ?>
    <?php if(!$liveBatchCounts): ?><span class="wf182-no-live">No student is taking an Upcoming Test right now.</span><?php endif; ?>
  </div>
</section>

<section class="admin-card wf182-live-card">
  <div class="wf182-section-head">
    <div><span class="dash-mini">Only status = In Progress</span><h2><i class="fa-solid fa-circle wf182-live-dot"></i> Live Test Students</h2><p>Expired timers are auto-finalized before this list loads, so this area contains only genuine current attempts.</p></div>
    <strong class="wf182-live-count"><?= e((string)count($liveRows)) ?> Live</strong>
  </div>
  <?php if(!$filterTestIds): ?>
    <div class="wf182-empty"><i class="fa-solid fa-clipboard-question"></i><p>No Upcoming Test paper is available for this filter.</p></div>
  <?php elseif(!$liveRows): ?>
    <div class="wf182-empty"><i class="fa-solid fa-user-clock"></i><p>No student is currently taking the selected Upcoming Test<?= $requestedBatch==='all'?'':' for this batch' ?>.</p></div>
  <?php else: ?>
    <div class="wf182-table-wrap"><table class="wf182-table"><thead><tr><th>Student</th><th>Batch / Test</th><th>Started</th><th>Time Left</th><th>Warnings</th><th>Action</th></tr></thead><tbody>
    <?php foreach($liveRows as $row): $remaining=weekly_attempt_remaining_seconds($row); ?>
      <tr>
        <td><b><?= e((string)$row['student_name']) ?></b><small><?= e((string)($row['student_phone']?:'Student login')) ?></small></td>
        <td><b><?= e((string)$row['display_batch']) ?></b><small><?= e((string)$row['test_title']) ?></small></td>
        <td><?= !empty($row['started_at'])?e(date('d M, h:i A',strtotime((string)$row['started_at']))):'-' ?></td>
        <td><span class="wf182-timer"><?= e(weekly_live_hms($remaining)) ?></span><small>server timer</small></td>
        <td><span class="wf182-warning <?= (int)($row['warning_count']??0)>0?'has-warning':'' ?>"><?= e((string)(int)($row['warning_count']??0)) ?></span></td>
        <td><a class="btn btn-soft btn-sm" href="weekly-student-record.php?attempt_id=<?= e((string)$row['id']) ?>&type=upcoming">View Attempt</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</section>

<section class="admin-card wf182-results-card">
  <div class="wf182-section-head">
    <div><span class="dash-mini">Separate from Live Students</span><h2>Submitted / Checked Results</h2><p>Use batch, paper, status and student filters above. Reopen is available only for a safe accidental Final Submit.</p></div>
    <strong><?= e((string)$resultTotal) ?> result<?= $resultTotal===1?'':'s' ?></strong>
  </div>
  <?php if(!$resultRows): ?>
    <div class="wf182-empty"><i class="fa-solid fa-inbox"></i><p>No finished result matches this filter.</p></div>
  <?php else: ?>
  <div class="wf182-result-list">
    <?php foreach($resultRows as $row): $reopen=weekly_live_reopen_ui($row,$winnerTests); $status=(string)($row['status']??''); $score=$row['admin_score']!==null?$row['admin_score']:$row['auto_score']; ?>
    <article class="wf182-result-row <?= $status==='checked'?'is-checked':'is-pending' ?>">
      <div class="wf182-result-main">
        <div class="wf182-student"><span class="wf182-avatar"><?= e(mb_strtoupper(mb_substr((string)$row['student_name'],0,1))) ?></span><div><h3><?= e((string)$row['student_name']) ?></h3><p><?= e((string)($row['student_phone']?:'Student login')) ?></p></div></div>
        <div class="wf182-meta"><span><i class="fa-solid fa-users"></i><?= e((string)$row['display_batch']) ?></span><span><i class="fa-solid fa-file-lines"></i><?= e((string)$row['test_title']) ?></span><span><i class="fa-regular fa-clock"></i><?= !empty($row['submitted_at'])?e(date('d M, h:i A',strtotime((string)$row['submitted_at']))):'-' ?></span></div>
      </div>
      <div class="wf182-result-side">
        <span class="wf182-status <?= $status==='checked'?'is-checked':'is-pending' ?>"><?= e(weekly_test_status_badge($status)) ?></span>
        <div class="wf182-score"><small>Score</small><b><?= $score!==null?e((string)$score):'—' ?> / <?= e((string)($row['total_marks']??'—')) ?></b></div>
        <?php if((int)($row['reopen_count']??0)>0): ?><span class="wf182-reopened"><i class="fa-solid fa-rotate-left"></i> Reopened once</span><?php endif; ?>
      </div>
      <div class="wf182-actions">
        <a class="btn btn-soft btn-sm" href="weekly-student-record.php?attempt_id=<?= e((string)$row['id']) ?>&type=upcoming">Open Copy</a>
        <?php if($reopen['ok']): ?>
        <details class="wf182-reopen-box">
          <summary class="btn btn-primary btn-sm"><i class="fa-solid fa-unlock-keyhole"></i> Reopen Test Access</summary>
          <form method="post" onsubmit="return confirm('Reopen this submitted attempt for the SAME student login? Saved answers stay, score resets, and the student can edit answers until the new timer ends.');">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="reopen_attempt">
            <input type="hidden" name="attempt_id" value="<?= e((string)$row['id']) ?>">
            <input type="hidden" name="batch" value="<?= e($requestedBatch) ?>">
            <input type="hidden" name="test_id" value="<?= e($requestedTestId>0?(string)$requestedTestId:'') ?>">
            <input type="hidden" name="result_status" value="<?= e($resultStatus) ?>">
            <input type="hidden" name="q" value="<?= e($search) ?>">
            <input type="hidden" name="page" value="<?= e((string)$page) ?>">
            <label>Reason <input type="text" name="reason" value="Accidental Final Submit" maxlength="240" required></label>
            <label>Time after reopen
              <select name="time_mode"><option value="remaining">Restore remaining time (recommended)</option><option value="full">Give full test duration (Admin override)</option></select>
            </label>
            <small>Same attempt + same question snapshot + same saved answers. Reopen is allowed only once.</small>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-unlock"></i> Confirm Reopen</button>
          </form>
        </details>
        <?php else: ?><span class="wf182-reopen-blocked" title="Reopen protected"><i class="fa-solid fa-lock"></i><?= e((string)$reopen['why']) ?></span><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if($pages>1): ?><nav class="wf182-pages" aria-label="Result pages"><?php for($i=1;$i<=$pages;$i++): $pq=$queryBase; $pq['page']=$i; ?><a class="<?= $i===$page?'active':'' ?>" href="weekly-live-students.php?<?= e(http_build_query(array_filter($pq,static fn($v)=>$v!==''))) ?>"><?= e((string)$i) ?></a><?php endfor; ?></nav><?php endif; ?>
  <?php endif; ?>
</section>

<section class="admin-card wf182-rule-card">
  <h2>Reopen Safety Rules</h2>
  <div class="wf182-rules"><span><i class="fa-solid fa-check"></i> Same student login</span><span><i class="fa-solid fa-check"></i> Same attempt & question snapshot</span><span><i class="fa-solid fa-check"></i> Saved answers preserved</span><span><i class="fa-solid fa-shield-halved"></i> Only manual Final Submit</span><span><i class="fa-solid fa-lock"></i> No reopen after answer release / timer end / checking / Top 3</span><span><i class="fa-solid fa-clock-rotate-left"></i> One reopen maximum</span></div>
</section>
<?php require_once __DIR__ . '/_footer.php'; ?>
