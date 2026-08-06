<?php
require_once __DIR__ . '/_header.php';
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM students WHERE id=? AND status_deleted=0 LIMIT 1');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { flash('error','Student not found.'); redirect('students.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'set_status') {
            $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
            db()->prepare('UPDATE students SET published=? WHERE id=?')->execute([$published, $id]);
            flash('success', $published === 'Yes' ? 'Student approved.' : 'Student marked as not approved.');
            redirect('student-view.php?id=' . $id);
        }
        if ($action === 'password') {
            $newPassword = trim((string)($_POST['new_password'] ?? ''));
            if (strlen($newPassword) < 6) throw new RuntimeException('Password must be at least 6 characters.');
            db()->prepare('UPDATE students SET password_hash=? WHERE id=?')->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
            flash('success','Password changed successfully.');
            redirect('student-view.php?id=' . $id);
        }
        if ($action === 'note') {
            db()->prepare('UPDATE students SET admin_note=? WHERE id=?')->execute([trim((string)($_POST['admin_note'] ?? '')), $id]);
            flash('success','Admin note saved.');
            redirect('student-view.php?id=' . $id);
        }
    } catch (Throwable $e) { error_log('[admin-student-view] ' . $e->__toString()); flash('error', 'Student record could not be updated.'); redirect('student-view.php?id=' . $id); }
}

$summary = student_activity_summary($id);
$metrics = student_learning_metrics($id);
$activities = fetch_student_activity($id, 12);
$weeklyAttempts = weekly_test_fetch_attempts_for_student($id, 12);
$recentAttempts = student_recent_material_attempts($id, 10);
$wrongAttempts = student_wrong_material_attempts($id, 10);
$progress = student_level_progress_percent((string)$student['current_level'], $metrics);
function admin_student_initials(array $s): string { $n=trim((string)($s['full_name']??'S')); $p=preg_split('/\s+/', $n); $o=''; foreach(array_slice($p?:[],0,2) as $x){$o.=mb_substr($x,0,1);} return mb_strtoupper($o?:'S'); }
?>
<div class="admin-page-head student-detail-head">
  <div><span class="eyebrow">Student Dashboard View</span><h1><?= e($student['full_name']) ?></h1><p><?= e($student['phone']) ?><?= $student['email'] ? ' • '.e($student['email']) : '' ?> • Admin can review progress without student password.</p></div>
  <div class="head-actions"><a class="btn btn-soft" href="students.php">Back Students</a><a class="btn btn-primary" href="weekly-student-record.php?phone=<?= e(urlencode((string)$student['phone'])) ?>">Test Records</a></div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="student-detail-hero-card">
  <div class="student-detail-avatar"><?= e(admin_student_initials($student)) ?></div>
  <div><span class="badge <?= $student['published']==='Yes'?'badge-yes':'badge-no' ?>"><?= $student['published']==='Yes'?'Approved':'Not Approved' ?></span><h2><?= e($student['current_level'] ?: 'Zero Level') ?></h2><p><?= e($student['target_goal'] ?: 'No goal saved yet') ?></p></div>
  <div class="student-progress-ring admin-ring" style="--p:<?= e((string)$progress) ?>"><div><strong><?= e((string)$progress) ?>%</strong><span>Progress</span></div></div>
</div>

<div class="student-crm-stats detail-stats">
  <div><b><?= e((string)$metrics['practice_total']) ?></b><span>Practice Total</span></div>
  <div><b><?= e((string)$metrics['correct_rate']) ?>%</b><span>Correct Rate</span></div>
  <div><b><?= e((string)$metrics['wrong_total']) ?></b><span>Wrong Answers</span></div>
  <div><b><?= e((string)$metrics['weekly_attempts']) ?></b><span>Weekly Attempts</span></div>
  <div><b><?= e((string)$summary['avg_score']) ?></b><span>Avg Activity Score</span></div>
</div>

<div class="student-detail-grid">
  <section class="panel-card">
    <h2>Admin Controls</h2>
    <div class="student-control-row">
      <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_status"><input type="hidden" name="published" value="<?= $student['published']==='Yes'?'No':'Yes' ?>"><button class="btn <?= $student['published']==='Yes'?'btn-soft':'btn-green' ?>" type="submit"><?= $student['published']==='Yes'?'Mark Not Approved':'Approve Student' ?></button></form>
      <form method="post" class="student-password-mini"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="password"><input type="text" name="new_password" placeholder="New password" required><button class="btn btn-primary" type="submit">Change Password</button></form>
    </div>
    <form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="note"><label>Admin Note<textarea name="admin_note" rows="4"><?= e($student['admin_note'] ?? '') ?></textarea></label><button class="btn btn-soft" type="submit">Save Note</button></form>
  </section>
  <section class="panel-card">
    <h2>Latest Weekly Tests</h2>
    <div class="activity-list compact-attempt-list">
      <?php if(!$weeklyAttempts): ?><p class="muted">No test attempts yet.</p><?php endif; ?>
      <?php foreach($weeklyAttempts as $a): ?><div class="attempt-row"><strong><?= e($a['test_title']) ?></strong><span><?= e(weekly_test_status_badge((string)$a['status'])) ?> • Auto <?= e((string)($a['auto_score'] ?? '-')) ?> / Admin <?= e((string)($a['admin_score'] ?? '-')) ?></span><small><?= e(date('d M Y, h:i A', strtotime((string)($a['started_at'] ?? 'now')))) ?></small></div><?php endforeach; ?>
    </div>
  </section>
</div>

<div class="student-detail-grid">
  <section class="panel-card"><h2>Recent Practice</h2><div class="activity-list compact-attempt-list"><?php if(!$recentAttempts): ?><p class="muted">No practice attempts yet.</p><?php endif; ?><?php foreach($recentAttempts as $item): ?><div class="attempt-row <?= !empty($item['is_correct'])?'ok':'bad' ?>"><strong><?= !empty($item['is_correct'])?'Correct':'Needs Revision' ?> • <?= e((string)$item['score']) ?>/10</strong><span><?= e($item['correct_answer'] ?: $item['english_text']) ?></span><small><?= e(date('d M Y, h:i A', strtotime((string)$item['created_at']))) ?></small></div><?php endforeach; ?></div></section>
  <section class="panel-card"><h2>Wrong Answer Revision</h2><div class="activity-list compact-attempt-list"><?php if(!$wrongAttempts): ?><p class="muted">No wrong answers saved.</p><?php endif; ?><?php foreach($wrongAttempts as $item): ?><div class="attempt-row bad"><strong><?= e($item['english_text'] ?? 'Practice') ?></strong><span>Student: <?= e($item['user_answer'] ?? '') ?></span><small>Correct: <?= e($item['correct_answer'] ?? '') ?></small></div><?php endforeach; ?></div></section>
</div>

<section class="panel-card"><h2>Manual Activity Log</h2><div class="table-wrap"><table><thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Score</th><th>Note</th></tr></thead><tbody><?php foreach($activities as $a): ?><tr><td><?= e(date('d M Y', strtotime((string)$a['created_at']))) ?></td><td><?= e($a['activity_type']) ?></td><td><?= e($a['activity_title'] ?? '') ?></td><td><?= e((string)($a['score'] ?? '-')) ?></td><td><?= e($a['note'] ?? '') ?></td></tr><?php endforeach; ?><?php if(!$activities): ?><tr><td colspan="5" class="empty-state">No manual activities yet.</td></tr><?php endif; ?></tbody></table></div></section>
<?php require_once __DIR__ . '/_footer.php'; ?>
