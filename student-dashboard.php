<?php
require_once __DIR__ . '/includes/functions.php';
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();
require_student();

$student = fetch_current_student();
if (!$student) {
    unset($_SESSION['student_id'], $_SESSION['student_name']);
    redirect('student-auth.php');
}

$studentId = (int)$student['id'];
$metrics = student_learning_metrics($studentId);
$weeklyAttempts = weekly_test_fetch_attempts_for_student($studentId, 20);
$dashboardWinners = weekly_test_active_winners_for_phone((string)($student['phone'] ?? ''));
if (!$dashboardWinners) {
    $dashboardWinners = weekly_test_active_winners(null);
}
$levelPercent = student_level_progress_percent((string)$student['current_level'], $metrics);

$page_title = 'Student Dashboard | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Student dashboard for spoken English practice, learning roadmap and weekly test results.';
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
?>
<section class="section student-dash-section pro-student-dashboard">
    <div class="container">
        <?php $successMessage = flash('success'); if ($successMessage): ?>
            <div class="alert alert-success"><p><?= e($successMessage) ?></p></div>
        <?php endif; ?>

        <div class="student-dashboard-hero wf-surface-dark" data-wf-surface="dark">
            <div class="student-welcome-copy">
                <span class="eyebrow">Student Dashboard</span>
                <h1>Welcome, <?= e($student['full_name']) ?></h1>
                <p>Continue your roadmap, practise one sentence at a time and keep every weekly-test result in one focused dashboard.</p>
                <div class="student-hero-actions">
                    <a class="btn btn-primary" href="spoken-materials.php"><i class="fa-solid fa-microphone-lines" aria-hidden="true"></i> Start Practice</a>
                    <a class="btn btn-light" href="learning-roadmap.php"><i class="fa-solid fa-route" aria-hidden="true"></i> Open Roadmap</a>
                    <a class="btn btn-soft wf145-dashboard-test" href="weekly-test.php"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Weekly Test</a>
                    <a class="btn wf145-dashboard-logout" href="student-logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Logout</a>
                </div>
            </div>
            <div class="student-progress-ring" style="--p:<?= e((string)$levelPercent) ?>">
                <div><strong><?= e((string)$levelPercent) ?>%</strong><span><?= e($student['current_level']) ?> Progress</span></div>
            </div>
        </div>

        <?php if (!empty($dashboardWinners)): ?>
            <div class="card student-winner-dashboard flower-dashboard">
                <div class="section-between">
                    <div><span class="eyebrow">Latest Batch Winners</span><h2>Top performers this week</h2><p class="muted">Recently completed batch tests ke leading students.</p></div>
                    <a class="btn btn-sm btn-primary" href="weekly-test.php">Open Weekly Test</a>
                </div>
                <div class="weekly-winner-grid student-winner-grid">
                    <?php foreach (array_slice($dashboardWinners, 0, 3) as $winner): ?>
                        <article class="weekly-winner-card rank-<?= e((string)$winner['rank_no']) ?>">
                            <b>#<?= e((string)$winner['rank_no']) ?> <?= e($winner['student_name'] ?: 'Student') ?></b>
                            <span><?= e((string)$winner['score']) ?> / <?= e((string)$winner['total_marks']) ?> marks</span>
                            <small><?= e($winner['test_title']) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="student-stat-grid progress-stat-grid wf145-dashboard-stats">
            <a class="stat card clickable-stat" href="spoken-materials.php"><strong><?= e((string)$metrics['practice_today']) ?></strong><span>Today Practice</span><small><?= e((string)$metrics['correct_today']) ?> correct today</small></a>
            <a class="stat card clickable-stat" href="weekly-test.php"><strong><?= e((string)$metrics['weekly_attempts']) ?></strong><span>Weekly Tests</span><small><?= e((string)$metrics['weekly_pending']) ?> pending result</small></a>
            <a class="stat card clickable-stat" href="learning-roadmap.php"><strong><?= e((string)$levelPercent) ?>%</strong><span>Roadmap Progress</span><small><?= e((string)$student['current_level']) ?> level</small></a>
            <div class="stat card"><strong><?= e((string)$metrics['streak_days']) ?></strong><span>Day Streak</span><small>Keep practising daily</small></div>
        </div>

        <section class="card student-weekly-results wf145-history-section" aria-labelledby="dashboardTestHistoryTitle">
            <div class="section-between wf145-history-head">
                <div><span class="eyebrow">Saved attempts</span><h2 id="dashboardTestHistoryTitle">Weekly Test Result History</h2><p class="muted">Every saved attempt with date, time, score and review status.</p></div>
                <div class="wf145-history-head-actions"><span><?= e((string)count($weeklyAttempts)) ?> attempts</span><a class="btn btn-sm btn-primary" href="weekly-test.php">Open Test Center</a></div>
            </div>

            <?php if (!$weeklyAttempts): ?>
                <div class="wf145-history-empty"><i class="fa-solid fa-chart-line" aria-hidden="true"></i><h3>No weekly-test history yet</h3><p>Complete a basic or weekly test. Your date, time, marks and answer review will appear here.</p><a class="btn btn-primary" href="weekly-test.php">Give First Test</a></div>
            <?php else: ?>
                <div class="wf145-history-grid">
                    <?php foreach ($weeklyAttempts as $index => $attempt):
                        $attemptStatus = strtolower((string)($attempt['status'] ?? ''));
                        $score = $attempt['admin_score'] !== null ? $attempt['admin_score'] : $attempt['auto_score'];
                        $totalMarks = (float)($attempt['total_marks'] ?? 0);
                        $percent = ($score !== null && $totalMarks > 0) ? max(0, min(100, (int)round(((float)$score / $totalMarks) * 100))) : null;
                        $attemptDateValue = trim((string)((($attempt['submitted_at'] ?? '') ?: ($attempt['started_at'] ?? '') ?: ($attempt['created_at'] ?? ''))));
                        $attemptTimestamp = $attemptDateValue !== '' ? strtotime($attemptDateValue) : false;
                        $attemptUrl = in_array($attemptStatus, ['submitted', 'checked'], true)
                            ? weekly_test_result_url($attempt)
                            : ('weekly-exam-room.php?attempt_id=' . (int)$attempt['id'] . '&token=' . rawurlencode((string)($attempt['access_token'] ?? '')));
                        $statusClass = $attemptStatus === 'checked' ? 'is-checked' : ($attemptStatus === 'started' ? 'is-progress' : 'is-pending');
                    ?>
                        <article class="wf145-history-card <?= e($statusClass) ?>">
                            <div class="wf145-history-card-top">
                                <span class="wf145-history-index">#<?= e((string)(count($weeklyAttempts) - $index)) ?></span>
                                <span class="wf145-history-type"><?= e(ucfirst((string)($attempt['test_type'] ?? 'test'))) ?> Test</span>
                                <span class="wf145-history-status"><?= e(weekly_test_status_badge((string)($attempt['status'] ?? ''))) ?></span>
                            </div>
                            <h3><?= e((string)($attempt['test_title'] ?? 'Weekly Test')) ?></h3>
                            <div class="wf145-history-time">
                                <span><i class="fa-regular fa-calendar" aria-hidden="true"></i><?= $attemptTimestamp ? e(date('d M Y', $attemptTimestamp)) : 'Date unavailable' ?></span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i><?= $attemptTimestamp ? e(date('h:i A', $attemptTimestamp)) : 'Time unavailable' ?></span>
                            </div>
                            <div class="wf145-history-score-row">
                                <div><small>Score</small><strong><?= $score !== null ? e((string)$score) : '—' ?><span>/<?= e((string)($attempt['total_marks'] ?? '—')) ?></span></strong></div>
                                <div class="wf145-history-percent" style="--score:<?= e((string)($percent ?? 0)) ?>"><span><?= $percent !== null ? e((string)$percent) . '%' : '—' ?></span></div>
                            </div>
                            <a class="wf145-history-action" href="<?= e($attemptUrl) ?>"><span><?= $attemptStatus === 'started' ? 'Resume Test' : 'View Answer Review' ?></span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
