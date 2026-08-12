<?php
$admin_page_final_styles = ['assets/css/phase161-upcoming-performance.css', 'assets/css/phase162-dashboard-performance.css'];
require_once __DIR__ . '/_header.php';
weekly_test_ensure_schema();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security check failed. Refresh the page and try again.');
        redirect('upcoming-test-performance.php');
    }
    if (($_POST['action'] ?? '') === 'save_gap') {
        $hours = max(0, min(168, (int)($_POST['gap_hours'] ?? 12)));
        save_app_setting('weekly_upcoming_min_gap_hours', (string)$hours);
        flash('success', $hours === 0
            ? 'Rapid-repeat lock disabled intentionally. Same-paper one-attempt security still remains active.'
            : 'Upcoming Test minimum gap saved: ' . $hours . ' hour' . ($hours === 1 ? '' : 's') . '.');
        $returnId = max(0, (int)($_POST['test_id'] ?? 0));
        redirect('upcoming-test-performance.php' . ($returnId > 0 ? '?test_id=' . $returnId : ''));
    }
}

$tests = weekly_test_fetch_tests('upcoming');
$batchOptions = [];
foreach ($tests as $test) {
    $batchId = max(0, (int)($test['batch_id'] ?? 0));
    $batchLabel = trim((string)(($test['batch_label'] ?? '') ?: ($test['batch_name'] ?? '') ?: 'Common / All Batches'));
    $batchKey = $batchId > 0 ? 'batch-' . $batchId : 'common';
    if (!isset($batchOptions[$batchKey])) {
        $batchOptions[$batchKey] = ['key'=>$batchKey, 'id'=>$batchId, 'label'=>$batchLabel, 'tests'=>[]];
    }
    $batchOptions[$batchKey]['tests'][] = $test;
}

$testId = max(0, (int)($_GET['test_id'] ?? 0));
$requestedBatch = trim((string)($_GET['batch'] ?? ''));
$selected = null;
if ($testId > 0) {
    foreach ($tests as $test) {
        if ((int)($test['id'] ?? 0) === $testId) { $selected = $test; break; }
    }
}
if (!$selected && $requestedBatch !== '' && isset($batchOptions[$requestedBatch])) {
    foreach ($batchOptions[$requestedBatch]['tests'] as $test) {
        if (strtolower((string)($test['status'] ?? '')) === 'active') { $selected = $test; break; }
    }
    if (!$selected) $selected = $batchOptions[$requestedBatch]['tests'][0] ?? null;
}
if (!$selected && $tests) {
    foreach ($tests as $test) {
        if (strtolower((string)($test['status'] ?? '')) === 'active') { $selected = $test; break; }
    }
    if (!$selected) $selected = $tests[0];
}
if ($selected) $testId = (int)($selected['id'] ?? 0);
$selectedBatchId = $selected ? max(0, (int)($selected['batch_id'] ?? 0)) : 0;
$selectedBatchKey = $selected ? ($selectedBatchId > 0 ? 'batch-' . $selectedBatchId : 'common') : ($requestedBatch !== '' ? $requestedBatch : '');
$selectedBatchLabel = $selected
    ? trim((string)(($selected['batch_label'] ?? '') ?: ($selected['batch_name'] ?? '') ?: 'Common / All Batches'))
    : (($selectedBatchKey !== '' && isset($batchOptions[$selectedBatchKey])) ? (string)$batchOptions[$selectedBatchKey]['label'] : 'No batch selected');
$visibleTests = ($selectedBatchKey !== '' && isset($batchOptions[$selectedBatchKey])) ? $batchOptions[$selectedBatchKey]['tests'] : $tests;

$gapHours = weekly_test_upcoming_gap_hours();
$stats = ['attempts'=>0,'started'=>0,'submitted'=>0,'checked'=>0,'avg_score'=>0,'high_score'=>0,'total_marks'=>0];
$standings = [];
$officialWinners = [];
$topThree = [];
$scoreCounts = array_fill(0, 11, 0);
$scoreAboveTen = 0;

if ($selected && $testId > 0) {
    $summary = db()->prepare("SELECT COUNT(*) attempts,
        SUM(status='started') started,
        SUM(status='submitted') submitted,
        SUM(status='checked') checked,
        COALESCE(AVG(CASE WHEN status='checked' THEN COALESCE(admin_score,auto_score,0) END),0) avg_score,
        COALESCE(MAX(CASE WHEN status='checked' THEN COALESCE(admin_score,auto_score,0) END),0) high_score,
        COALESCE(MAX(total_marks),0) total_marks
        FROM weekly_test_attempts
        WHERE COALESCE(status_deleted,0)=0 AND test_id=?");
    $summary->execute([$testId]);
    $stats = array_merge($stats, $summary->fetch() ?: []);

    $rankStmt = db()->prepare("SELECT a.id, a.student_id,
        COALESCE(NULLIF(s.full_name,''),NULLIF(a.guest_name,''),'Student') student_name,
        COALESCE(NULLIF(s.phone,''),NULLIF(a.guest_phone,''),'') student_phone,
        COALESCE(a.admin_score,a.auto_score,0) final_score,
        COALESCE(a.total_marks,0) total_marks,
        a.submitted_at, a.warning_count
        FROM weekly_test_attempts a
        LEFT JOIN students s ON s.id=a.student_id
        WHERE COALESCE(a.status_deleted,0)=0 AND a.test_id=? AND a.status='checked'
        ORDER BY final_score DESC, COALESCE(a.submitted_at,a.started_at) ASC, a.id ASC
        LIMIT 1000");
    $rankStmt->execute([$testId]);
    $checkedRows = $rankStmt->fetchAll();
    $standings = array_slice($checkedRows, 0, 10);
    foreach ($checkedRows as $row) {
        $score = max(0, (float)($row['final_score'] ?? 0));
        $rounded = (int)round($score);
        if ($rounded <= 10) $scoreCounts[max(0, $rounded)]++;
        else $scoreAboveTen++;
    }

    $winnerStmt = db()->prepare("SELECT w.* FROM weekly_test_winners w WHERE w.test_id=? ORDER BY w.rank_no ASC, w.id ASC LIMIT 3");
    $winnerStmt->execute([$testId]);
    $officialWinners = $winnerStmt->fetchAll();
    if ($officialWinners) {
        $topThree = $officialWinners;
    } else {
        foreach (array_slice($standings, 0, 3) as $index => $row) {
            $topThree[] = [
                'rank_no'=>$index + 1,
                'student_name'=>$row['student_name'],
                'student_phone'=>$row['student_phone'],
                'score'=>$row['final_score'],
                'total_marks'=>$row['total_marks'],
            ];
        }
    }
}

$readyReason = $selected ? weekly_test_ready_reason($selected) : 'pending';
$statusLabel = $readyReason === 'ready' ? 'Open now' : ($readyReason === 'scheduled_later' ? 'Scheduled' : ($readyReason === 'expired' ? 'Closed' : ucfirst((string)($selected['status'] ?? 'Pending'))));
$totalMarks = max(0, (float)($stats['total_marks'] ?? ($selected['total_marks'] ?? 0)));
?>
<div class="admin-top wf161-admin-top">
    <div>
        <span class="eyebrow">Upcoming Test Intelligence</span>
        <h1>Performance & Top 10</h1>
        <p>See checked marks, Top 3, score distribution and the anti-repeat security window for every Upcoming Test.</p>
    </div>
    <div class="admin-actions">
        <a class="btn btn-soft" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        <a class="btn btn-primary" href="weekly-tests.php?type=upcoming<?= $testId > 0 ? '&test_id=' . e((string)$testId) : '' ?>#paper-board"><i class="fa-solid fa-clipboard-check"></i> Manage Test</a>
    </div>
</div>

<?php if (!$tests): ?>
<section class="admin-card wf161-empty">
    <i class="fa-solid fa-calendar-plus"></i>
    <div><h2>No Upcoming Test yet</h2><p>Create an Upcoming Test, upload questions and publish it. Performance will appear here after students submit and Admin checks their copies.</p></div>
    <a class="btn btn-primary" href="weekly-tests.php?type=upcoming#setup">Create Upcoming Test</a>
</section>
<?php else: ?>
<section class="admin-card wf161-selector-card wf162-batch-test-selector">
    <div class="wf162-selector-stack">
        <form method="get" class="wf161-test-selector">
            <label>1. Choose Batch
                <select name="batch" onchange="this.form.submit()">
                    <?php foreach ($batchOptions as $option): ?>
                    <option value="<?= e((string)$option['key']) ?>" <?= (string)$option['key'] === $selectedBatchKey ? 'selected' : '' ?>><?= e((string)$option['label']) ?> • <?= e((string)count($option['tests'])) ?> test<?= count($option['tests'])===1?'':'s' ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <noscript><button class="btn btn-soft" type="submit">Open Batch</button></noscript>
        </form>
        <form method="get" class="wf161-test-selector">
            <input type="hidden" name="batch" value="<?= e($selectedBatchKey) ?>">
            <label>2. Choose Test
                <select name="test_id" onchange="this.form.submit()">
                    <?php foreach ($visibleTests as $test): ?>
                    <option value="<?= e((string)$test['id']) ?>" <?= (int)$test['id'] === $testId ? 'selected' : '' ?>><?= e((string)$test['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <noscript><button class="btn btn-soft" type="submit">Open Test</button></noscript>
        </form>
    </div>
    <div class="wf161-selected-status wf162-selected-batch-status">
        <span class="wf161-status-dot"></span>
        <div><b><?= e($selectedBatchLabel) ?></b><small><?= e($statusLabel) ?> • <?= e((string)($selected['starts_at'] ?? 'No fixed start')) ?> → <?= e((string)($selected['ends_at'] ?? 'No fixed end')) ?></small></div>
    </div>
</section>

<section class="wf161-metrics" aria-label="Upcoming test performance summary">
    <article><span>Students</span><b><?= e((string)(int)$stats['attempts']) ?></b><small>Total attempts</small></article>
    <article><span>Checked</span><b><?= e((string)(int)$stats['checked']) ?></b><small>Ranking-ready copies</small></article>
    <article><span>Pending</span><b><?= e((string)(int)$stats['submitted']) ?></b><small>Teacher review needed</small></article>
    <article><span>In Test</span><b><?= e((string)(int)$stats['started']) ?></b><small>Currently started</small></article>
    <article><span>Average</span><b><?= e(number_format((float)$stats['avg_score'], 1)) ?></b><small>Checked marks</small></article>
    <article><span>Highest</span><b><?= e(number_format((float)$stats['high_score'], 1)) ?></b><small>Out of <?= e(number_format($totalMarks, 1)) ?></small></article>
</section>

<section class="admin-card wf161-podium-shell" id="winner-cards">
    <div class="section-between wf161-section-head">
        <div><span class="dash-mini">Separate winner cards • <?= e($selectedBatchLabel) ?></span><h2><?= $officialWinners ? 'Official 1st – 3rd Winners' : 'Provisional 1st – 3rd Winners' ?></h2><p><?= $officialWinners ? 'These three positions are finalized for this batch test.' : 'Based only on Admin-checked copies for this selected batch test. Finalize the paper before treating these positions as official.' ?></p></div>
        <?php if (!$officialWinners): ?><span class="wf161-provisional"><i class="fa-solid fa-clock"></i> Waiting for final ranking</span><?php endif; ?>
    </div>
    <?php if (!$topThree): ?>
        <div class="wf161-no-rank"><i class="fa-solid fa-medal"></i><span>No checked student yet. Check submitted copies first.</span></div>
    <?php else: ?>
    <div class="wf161-podium" aria-label="Top three students">
        <?php foreach ($topThree as $winner): $rank=(int)($winner['rank_no'] ?? 0); ?>
        <article class="wf161-winner rank-<?= e((string)$rank) ?>">
            <span class="wf161-medal"><i class="fa-solid <?= $rank===1?'fa-crown':'fa-medal' ?>"></i></span>
            <small>#<?= e((string)$rank) ?></small>
            <h3><?= e((string)($winner['student_name'] ?? 'Student')) ?></h3>
            <p><?= e(number_format((float)($winner['score'] ?? 0), 1)) ?> / <?= e(number_format((float)($winner['total_marks'] ?? $totalMarks), 1)) ?></p>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<div class="wf161-two-col">
<section class="admin-card wf161-top10-card">
    <div class="wf161-section-head"><span class="dash-mini">Batch-wise checked result</span><h2>Top 10 — <?= e($selectedBatchLabel) ?></h2><p><?= e((string)($selected['title'] ?? 'Upcoming Test')) ?> • Tie-breaker: higher marks first, then earlier submission.</p></div>
    <?php if (!$standings): ?><p class="muted">No checked result available yet.</p><?php else: ?>
    <div class="wf161-leaderboard">
        <?php foreach ($standings as $index => $row): $rank=$index+1; $pct=((float)($row['total_marks']??0)>0)?(((float)$row['final_score']/(float)$row['total_marks'])*100):0; ?>
        <article>
            <span class="wf161-rank <?= $rank<=3?'is-top':'' ?>"><?= e((string)$rank) ?></span>
            <div class="wf161-student"><b><?= e((string)$row['student_name']) ?></b><small><?= e((string)($row['student_phone'] ?: 'Student account')) ?></small></div>
            <div class="wf161-score"><b><?= e(number_format((float)$row['final_score'],1)) ?></b><small><?= e(number_format($pct,0)) ?>%</small></div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="admin-card wf161-distribution-card">
    <div class="wf161-section-head"><span class="dash-mini">Marks 0–10</span><h2>Low-score Distribution</h2><p>Quickly see how many checked students received each mark from 0 to 10. Scores above 10 are grouped separately.</p></div>
    <?php $maxBucket=max(1,max($scoreCounts ?: [0]),$scoreAboveTen); ?>
    <div class="wf161-bars">
        <?php foreach ($scoreCounts as $mark=>$count): ?>
        <div><span><?= e((string)$mark) ?></span><i style="--bar:<?= e((string)max(6,(int)round(($count/$maxBucket)*100))) ?>%"></i><b><?= e((string)$count) ?></b></div>
        <?php endforeach; ?>
        <div class="is-more"><span>11+</span><i style="--bar:<?= e((string)max(6,(int)round(($scoreAboveTen/$maxBucket)*100))) ?>%"></i><b><?= e((string)$scoreAboveTen) ?></b></div>
    </div>
</section>
</div>

<section class="admin-card wf161-security-card">
    <div class="wf161-security-copy">
        <span class="dash-mini">Flexible schedule + anti-cheat</span>
        <h2>No fixed 7-day rule.</h2>
        <p>Admin can schedule an Upcoming Test for tomorrow, two days later, or any date using <b>Available From</b> and <b>Available Until</b>. Every student still gets only one attempt per paper. This extra lock prevents the same student from immediately entering a different new Upcoming Test after finishing one.</p>
        <ul>
            <li><i class="fa-solid fa-check"></i> Same paper: one official attempt only.</li>
            <li><i class="fa-solid fa-check"></i> Different paper: minimum gap checked from the previous submitted Upcoming Test.</li>
            <li><i class="fa-solid fa-check"></i> A student cannot keep two Upcoming Tests open at the same time.</li>
            <li><i class="fa-solid fa-check"></i> Start requests are serialized against the student record to stop double-click/race starts.</li>
        </ul>
    </div>
    <form method="post" class="wf161-gap-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save_gap">
        <input type="hidden" name="test_id" value="<?= e((string)$testId) ?>">
        <label>Minimum gap between different Upcoming Tests
            <div><input type="number" name="gap_hours" min="0" max="168" value="<?= e((string)$gapHours) ?>"><span>hours</span></div>
        </label>
        <small>Recommended: 12 hours. Set 0 only when Admin intentionally wants to disable the cross-paper gap for a special retest.</small>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-shield-halved"></i> Save Security Gap</button>
    </form>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
