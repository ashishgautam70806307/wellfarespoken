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
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'profile') {
            $goal = trim((string)($_POST['target_goal'] ?? ''));
            if (mb_strlen($goal) > 180) $goal = mb_substr($goal, 0, 180);
            $minutes = max(5, min(180, (int)($_POST['daily_goal_minutes'] ?? 20)));
            // Official curriculum level is assessment/admin controlled; students may update only their goal and daily target.
            db()->prepare('UPDATE students SET target_goal=?, daily_goal_minutes=? WHERE id=?')->execute([$goal ?: null, $minutes, (int)$student['id']]);
            flash('success', 'Learning profile updated. Your current level remains assessment-controlled.');
            redirect('student-dashboard.php');
        }
        if ($action === 'activity') {
            $type = trim((string)($_POST['activity_type'] ?? 'practice')) ?: 'practice';
            $title = trim((string)($_POST['activity_title'] ?? 'Daily Practice')) ?: 'Daily Practice';
            // Self-reported activity must never change official score/progress analytics.
            $score = null;
            $note = trim((string)($_POST['note'] ?? ''));
            db()->prepare('INSERT INTO student_activity_logs (student_id, activity_type, activity_title, score, note) VALUES (?, ?, ?, ?, ?)')->execute([(int)$student['id'], $type, $title, $score, $note]);
            flash('success', 'Practice activity saved.');
            redirect('student-dashboard.php');
        }
    }
}
$student = fetch_current_student();
$studentId = (int)$student['id'];
$summary = student_activity_summary($studentId);
$metrics = student_learning_metrics($studentId);
$activities = fetch_student_activity($studentId, 8);
$modules = student_recommended_modules((string)$student['current_level']);
$weeklyAttempts = weekly_test_fetch_attempts_for_student($studentId, 8);
$dashboardWinners = weekly_test_active_winners_for_phone((string)($student['phone'] ?? ''));
if (!$dashboardWinners) { $dashboardWinners = weekly_test_active_winners(null); }
$wrongAttempts = student_wrong_material_attempts($studentId, 8);
$recentAttempts = student_recent_material_attempts($studentId, 8);
$levelPercent = student_level_progress_percent((string)$student['current_level'], $metrics);
$todayTarget = max(5, (int)$student['daily_goal_minutes']);
$todayDone = min(100, (int)round((($metrics['practice_today'] ?? 0) / max(1, ceil($todayTarget / 5))) * 100));
$page_title = 'Student Dashboard | ' . app_setting('site_name', APP_NAME);
$meta_description = 'Student dashboard for spoken English practice progress, weekly tests, revision and learning history.';
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
?>
<section class="section student-dash-section pro-student-dashboard">
    <div class="container">
        <?php $successMessage = flash('success'); if ($successMessage): ?><div class="alert alert-success"><p><?= e($successMessage) ?></p></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>

        <div class="student-dashboard-hero wf-surface-dark">
            <div class="student-welcome-copy">
                <span class="eyebrow">Student Dashboard</span>
                <h1>Welcome, <?= e($student['full_name']) ?></h1>
                <p>Today ka focus clear hai: listen, speak loudly, type answer, check correction, then repeat. Daily small practice se confidence build hota hai.</p>
                <div class="student-hero-actions">
                    <a class="btn btn-primary" href="spoken-materials.php">Start Today’s Practice</a>
                    <a class="btn btn-light" href="weekly-test.php">Weekly Test</a>
                </div>
            </div>
            <div class="student-progress-ring" style="--p:<?= e((string)$levelPercent) ?>">
                <div><strong><?= e((string)$levelPercent) ?>%</strong><span><?= e($student['current_level']) ?> Progress</span></div>
            </div>
        </div>


        <?php if(!empty($dashboardWinners)): ?>
        <div class="card student-winner-dashboard flower-dashboard">
            <div class="section-between"><div><span class="eyebrow">Latest Batch Winners</span><h2>Top performers this week</h2><p class="muted">Completed batch test ke top students yahan 2 days ke liye show honge.</p></div><a class="btn btn-sm btn-primary" href="weekly-test.php">Open Weekly Test</a></div>
            <div class="weekly-winner-grid student-winner-grid">
                <?php foreach(array_slice($dashboardWinners,0,3) as $w): ?>
                <article class="weekly-winner-card rank-<?= e((string)$w['rank_no']) ?>">
                    <b>#<?= e((string)$w['rank_no']) ?> <?= e($w['student_name'] ?: 'Student') ?></b>
                    <span><?= e((string)$w['score']) ?> / <?= e((string)$w['total_marks']) ?> marks</span>
                    <small><?= e($w['test_title']) ?></small>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="student-stat-grid progress-stat-grid">
            <a class="stat card clickable-stat" href="spoken-materials.php"><strong><?= e((string)$metrics['practice_today']) ?></strong><span>Today Practice</span><small><?= e((string)$metrics['correct_today']) ?> correct today</small></a>
            <a class="stat card clickable-stat" href="student-revision.php"><strong><?= e((string)$metrics['wrong_total']) ?></strong><span>Wrong Answers</span><small>Revise and repeat</small></a>
            <a class="stat card clickable-stat" href="weekly-test.php"><strong><?= e((string)$metrics['weekly_attempts']) ?></strong><span>Weekly Tests</span><small><?= e((string)$metrics['weekly_pending']) ?> pending result</small></a>
            <div class="stat card"><strong><?= e((string)$metrics['streak_days']) ?></strong><span>Day Streak</span><small>Practise daily</small></div>
        </div>

        <div class="student-plan-grid">
            <div class="card today-plan-card">
                <div class="section-between"><div><span class="eyebrow">Today Plan</span><h2>Complete this simple routine</h2></div><strong><?= e((string)$todayDone) ?>%</strong></div>
                <div class="tiny-progress"><span style="width:<?= e((string)$todayDone) ?>%"></span></div>
                <div class="student-checklist">
                    <a href="spoken-materials.php"><span>1</span><b>10 sentences</b><small>Hindi → English / Speak Daily</small></a>
                    <a href="spoken-materials.php?goal=revision"><span>2</span><b>Revise mistakes</b><small>Repeat wrong answers loudly</small></a>
                    <a href="weekly-test.php"><span>3</span><b>Test practice</b><small>Attempt basic or weekly test</small></a>
                    <a href="courses.php"><span>4</span><b>Join class</b><small>Check available spoken English courses</small></a>
                </div>
            </div>
            <div class="card dashboard-profile-card">
                <h2>Learning Profile</h2>
                <form method="post" class="form-stack compact-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="profile">
                    <label>Current Level<select disabled aria-describedby="levelControlHelp"><option selected><?= e((string)$student['current_level']) ?></option></select><small id="levelControlHelp">Level is updated through assessment or by the institute.</small></label>
                    <label>Target Goal<input type="text" name="target_goal" value="<?= e($student['target_goal'] ?? '') ?>" placeholder="Example: Job interview English"></label>
                    <label>Daily Goal Minutes<input type="number" min="5" max="180" name="daily_goal_minutes" value="<?= e((string)$student['daily_goal_minutes']) ?>"></label>
                    <button class="btn btn-primary" type="submit">Save Profile</button>
                </form>
            </div>
        </div>

        <div class="dash-two-col student-dash-cards">
            <div class="card">
                <div class="section-between"><div><h2>Recommended Modules</h2><p class="muted">Based on your current level.</p></div><a class="btn btn-sm btn-soft" href="learning-roadmap.php">Roadmap</a></div>
                <div class="module-check-list">
                    <?php foreach ($modules as $module): ?><div><span>✓</span><strong><?= e($module) ?></strong></div><?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <div class="section-between"><div><h2>Recent Practice</h2><p class="muted">Latest sentence attempts from Practice Room.</p></div></div>
                <div class="activity-list compact-attempt-list">
                    <?php if (!$recentAttempts): ?><p class="muted">No spoken practice yet. Start from Practice Room.</p><?php endif; ?>
                    <?php foreach ($recentAttempts as $item): ?><div class="attempt-row <?= !empty($item['is_correct']) ? 'ok' : 'bad' ?>"><strong><?= !empty($item['is_correct']) ? 'Correct' : 'Needs Revision' ?> • <?= e((string)$item['score']) ?>/10</strong><span><?= e($item['correct_answer'] ?: $item['english_text']) ?></span><small><?= e(date('d M Y, h:i A', strtotime($item['created_at']))) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="dash-two-col student-dash-cards">
            <div class="card mistake-card">
                <div class="section-between"><div><h2>Wrong Answers</h2><p class="muted">These are your latest mistakes. Repeat the correct answer loudly.</p></div></div>
                <div class="wrong-answer-list">
                    <?php if (!$wrongAttempts): ?><p class="muted">Great. No wrong spoken answer saved yet.</p><?php endif; ?>
                    <?php foreach ($wrongAttempts as $item): ?>
                        <div>
                            <b><?= e($item['english_text']) ?></b>
                            <span>Your answer: <?= e($item['user_answer'] ?: '-') ?></span>
                            <small><?= e($item['feedback'] ?: 'Repeat the correct answer once.') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <h2>Save Extra Practice Note</h2>
                <form method="post" class="form-stack compact-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="activity">
                    <label>Practice Type<select name="activity_type"><option>Hindi to English</option><option>Vocabulary</option><option>Verb Forms</option><option>Grammar</option><option>Speaking</option><option>Test</option></select></label>
                    <label>Title<input type="text" name="activity_title" placeholder="Example: 20 daily sentences"></label>
                    <label>Note<textarea name="note" rows="3" placeholder="What did you practise today?"></textarea></label>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Practice Note</button>
                </form>
            </div>
        </div>

        <div class="card student-weekly-results">
            <div class="section-between"><div><h2>Weekly Test Result History</h2><p class="muted">Login-based weekly tests, teacher marks and answer review.</p></div><a class="btn btn-sm btn-primary" href="weekly-test.php">Open Weekly Test</a></div>
            <div class="table-responsive"><table class="admin-table compact-table"><thead><tr><th>Test</th><th>Status</th><th>Auto</th><th>Teacher Marks</th><th>Date</th><th>Review</th></tr></thead><tbody>
            <?php if (!$weeklyAttempts): ?><tr><td colspan="6">No weekly test record yet.</td></tr><?php endif; ?>
            <?php foreach ($weeklyAttempts as $wa): ?><tr><td><b><?= e($wa['test_title']) ?></b><small><?= e($wa['test_type']) ?></small></td><td><?= e(weekly_test_status_badge($wa['status'])) ?></td><td><?= e((string)($wa['auto_score'] ?? '-')) ?></td><td><?= e($wa['admin_score'] !== null ? (string)$wa['admin_score'].' / '.(string)$wa['total_marks'] : 'Pending') ?></td><td><?= e($wa['submitted_at'] ?: $wa['started_at']) ?></td><td><a class="btn btn-sm btn-soft" href="weekly-result.php?attempt_id=<?= e((string)$wa['id']) ?>">View</a></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
