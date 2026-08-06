<?php
require_once __DIR__ . '/_header.php';
ensure_schema_updates();
material_ensure_schema();
weekly_test_ensure_schema();

$errors = [];
$levels = ['Zero Level','Basic','Intermediate','Advanced'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        try {
            if ($action === 'save' && $id > 0) {
                $level = trim((string)($_POST['current_level'] ?? 'Zero Level')) ?: 'Zero Level';
                $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
                $goal = trim((string)($_POST['target_goal'] ?? ''));
                $minutes = max(5, min(180, (int)($_POST['daily_goal_minutes'] ?? 20)));
                $note = trim((string)($_POST['admin_note'] ?? ''));
                $newPassword = trim((string)($_POST['new_password'] ?? ''));
                if ($newPassword !== '') {
                    if (strlen($newPassword) < 6) {
                        throw new RuntimeException('Password must be at least 6 characters.');
                    }
                    db()->prepare('UPDATE students SET current_level=?, target_goal=?, daily_goal_minutes=?, published=?, admin_note=?, password_hash=? WHERE id=?')
                        ->execute([$level, $goal, $minutes, $published, $note, password_hash($newPassword, PASSWORD_DEFAULT), $id]);
                    flash('success', 'Student updated and password changed successfully.');
                } else {
                    db()->prepare('UPDATE students SET current_level=?, target_goal=?, daily_goal_minutes=?, published=?, admin_note=? WHERE id=?')
                        ->execute([$level, $goal, $minutes, $published, $note, $id]);
                    flash('success', 'Student updated successfully.');
                }
                redirect('students.php');
            }
            if ($action === 'set_status' && $id > 0) {
                $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
                db()->prepare('UPDATE students SET published=? WHERE id=?')->execute([$published, $id]);
                flash('success', $published === 'Yes' ? 'Student approved successfully.' : 'Student marked as not approved.');
                redirect('students.php');
            }
            if ($action === 'delete' && $id > 0) {
                db()->prepare('UPDATE students SET status_deleted=1 WHERE id=?')->execute([$id]);
                flash('success', 'Student safely hidden from normal list. Records remain saved.');
                redirect('students.php');
            }
            if ($action === 'bulk') {
                $ids = array_values(array_filter(array_map('intval', $_POST['student_ids'] ?? [])));
                $bulkAction = $_POST['bulk_action'] ?? '';
                if (!$ids) throw new RuntimeException('Please select at least one student.');
                $in = implode(',', array_fill(0, count($ids), '?'));
                if ($bulkAction === 'approve') {
                    db()->prepare("UPDATE students SET published='Yes' WHERE id IN ($in)")->execute($ids);
                    flash('success', count($ids) . ' student(s) approved.');
                } elseif ($bulkAction === 'not_approve') {
                    db()->prepare("UPDATE students SET published='No' WHERE id IN ($in)")->execute($ids);
                    flash('success', count($ids) . ' student(s) marked as not approved.');
                } elseif ($bulkAction === 'delete') {
                    db()->prepare("UPDATE students SET status_deleted=1 WHERE id IN ($in)")->execute($ids);
                    flash('success', count($ids) . ' student(s) safely hidden.');
                } else {
                    throw new RuntimeException('Please choose a valid bulk action.');
                }
                redirect('students.php');
            }
        } catch (Throwable $e) {
            error_log('[admin-students] ' . $e->__toString());
            $errors[] = ($e instanceof RuntimeException && !($e instanceof PDOException))
                ? $e->getMessage()
                : 'Student change could not be saved. Check Admin > System Check.';
        }
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$levelFilter = trim((string)($_GET['level'] ?? ''));
$where = ['s.status_deleted=0'];
$params = [];
if ($q !== '') {
    $where[] = '(s.full_name LIKE ? OR s.phone LIKE ? OR s.email LIKE ? OR s.target_goal LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if ($status === 'Yes' || $status === 'No') { $where[] = 's.published=?'; $params[] = $status; }
if ($levelFilter !== '') { $where[] = 's.current_level=?'; $params[] = $levelFilter; }
$sqlWhere = implode(' AND ', $where);
$stmt = db()->prepare("SELECT s.*, (SELECT COUNT(*) FROM student_activity_logs a WHERE a.student_id=s.id) activity_count, (SELECT COUNT(*) FROM weekly_test_attempts w WHERE w.student_id=s.id AND COALESCE(w.status_deleted,0)=0) weekly_count FROM students s WHERE $sqlWhere ORDER BY s.id DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

$stats = ['total'=>0,'approved'=>0,'not_approved'=>0,'today'=>0,'tests'=>0];
try {
    $stats['total'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0")->fetchColumn();
    $stats['approved'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND published='Yes'")->fetchColumn();
    $stats['not_approved'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND published='No'")->fetchColumn();
    $stats['today'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND DATE(created_at)=CURDATE()")->fetchColumn();
    $stats['tests'] = (int)db()->query("SELECT COUNT(*) FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0")->fetchColumn();
} catch (Throwable $e) {}

function student_status_badge_class(string $status): string { return $status === 'Yes' ? 'badge-yes' : 'badge-no'; }
function student_initials(array $s): string {
    $name = trim((string)($s['full_name'] ?? 'Student'));
    $parts = preg_split('/\s+/', $name);
    $letters = '';
    foreach (array_slice($parts ?: [], 0, 2) as $part) { $letters .= mb_substr($part, 0, 1); }
    return mb_strtoupper($letters ?: 'S');
}
?>
<div class="admin-page-head student-admin-head">
  <div><span class="eyebrow">Student CRM</span><h1>Students</h1><p>Approve/not approve, update profile, change password, open test records and view every student dashboard from one clean page.</p></div>
  <div class="head-actions"><a class="btn btn-primary" href="../student-auth.php?mode=register" target="_blank">Open Register</a><a class="btn btn-soft" href="weekly-tests.php#student-copies">Test Copies</a></div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>

<div class="student-crm-stats">
  <a href="students.php"><b><?= e((string)$stats['total']) ?></b><span>Total Students</span></a>
  <a href="students.php?status=Yes"><b><?= e((string)$stats['approved']) ?></b><span>Approved</span></a>
  <a href="students.php?status=No"><b><?= e((string)$stats['not_approved']) ?></b><span>Not Approved</span></a>
  <a href="students.php"><b><?= e((string)$stats['today']) ?></b><span>Joined Today</span></a>
  <a href="weekly-tests.php#student-copies"><b><?= e((string)$stats['tests']) ?></b><span>Test Attempts</span></a>
</div>

<div class="panel-card student-filter-panel">
  <form method="get" class="student-filter-form">
    <input name="q" value="<?= e($q) ?>" placeholder="Search name, phone, email or goal">
    <select name="status"><option value="">All Status</option><option value="Yes" <?= $status==='Yes'?'selected':'' ?>>Approved</option><option value="No" <?= $status==='No'?'selected':'' ?>>Not Approved</option></select>
    <select name="level"><option value="">All Levels</option><?php foreach($levels as $lv): ?><option value="<?= e($lv) ?>" <?= $levelFilter===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select>
    <button class="btn btn-dark" type="submit">Filter</button>
    <a class="btn btn-soft" href="students.php">Reset</a>
  </form>
</div>

<form method="post" id="studentBulkForm" class="student-bulk-bar" data-confirm="Apply selected bulk action to students?">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="bulk">
  <label class="checkline"><input type="checkbox" id="selectAllStudents"> Select all visible</label>
  <select name="bulk_action" required><option value="">Bulk action</option><option value="approve">Approve</option><option value="not_approve">Not Approve</option><option value="delete">Delete / Hide</option></select>
  <button class="btn btn-soft" type="submit">Apply</button>
</form>
<div class="student-card-grid">
    <?php if (!$students): ?><div class="admin-card empty-state">No students found for selected filter.</div><?php endif; ?>
    <?php foreach ($students as $student): $phoneDigits = preg_replace('/\D+/', '', (string)$student['phone']); ?>
    <article class="student-manage-card <?= $student['published']==='Yes'?'approved':'blocked' ?>">
      <div class="student-card-top">
        <label class="student-card-check"><input type="checkbox" name="student_ids[]" form="studentBulkForm" value="<?= e((string)$student['id']) ?>"></label>
        <div class="student-avatar-admin"><?= e(student_initials($student)) ?></div>
        <div class="student-main-info"><h2><?= e($student['full_name']) ?></h2><p><?= e($student['phone']) ?> <?= $student['email'] ? '• '.e($student['email']) : '' ?></p></div>
        <span class="badge <?= e(student_status_badge_class((string)$student['published'])) ?>"><?= $student['published']==='Yes'?'Approved':'Not Approved' ?></span>
      </div>
      <div class="student-mini-metrics">
        <div><b><?= e($student['current_level'] ?: 'Zero Level') ?></b><span>Level</span></div>
        <div><b><?= e((string)($student['activity_count'] ?? 0)) ?></b><span>Practice</span></div>
        <div><b><?= e((string)($student['weekly_count'] ?? 0)) ?></b><span>Tests</span></div>
        <div><b><?= e(date('d M', strtotime((string)$student['created_at']))) ?></b><span>Joined</span></div>
      </div>
      <p class="student-goal-line"><?= e($student['target_goal'] ?: 'No learning goal saved yet.') ?></p>
      <div class="student-card-actions">
        <a class="btn btn-sm btn-primary" href="student-view.php?id=<?= e((string)$student['id']) ?>">View</a>
        <a class="btn btn-sm btn-soft" href="weekly-student-record.php?phone=<?= e(urlencode((string)$student['phone'])) ?>">Tests</a>
        <?php if ($phoneDigits): ?><a class="btn btn-sm btn-green" href="https://wa.me/<?= e($phoneDigits) ?>" target="_blank">WhatsApp</a><?php endif; ?>
        <form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_status"><input type="hidden" name="id" value="<?= e((string)$student['id']) ?>"><input type="hidden" name="published" value="<?= $student['published']==='Yes'?'No':'Yes' ?>"><button class="btn btn-sm <?= $student['published']==='Yes'?'btn-soft':'btn-green' ?>" type="submit"><?= $student['published']==='Yes'?'Not Approve':'Approve' ?></button></form>
      </div>
      <details class="student-edit-box">
        <summary>Edit / Password</summary>
        <form method="post" class="student-edit-form">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string)$student['id']) ?>"><input type="hidden" name="action" value="save">
          <label>Level<select name="current_level"><?php foreach($levels as $lv): ?><option <?= $student['current_level']===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select></label>
          <label>Status<select name="published"><option value="Yes" <?= $student['published']==='Yes'?'selected':'' ?>>Approved</option><option value="No" <?= $student['published']==='No'?'selected':'' ?>>Not Approved</option></select></label>
          <label>Daily Goal<input type="number" min="5" max="180" name="daily_goal_minutes" value="<?= e((string)($student['daily_goal_minutes'] ?? 20)) ?>"></label>
          <label>Target Goal<input name="target_goal" value="<?= e($student['target_goal'] ?? '') ?>" placeholder="Interview / daily speaking / grammar"></label>
          <label>New Password<input type="text" name="new_password" placeholder="Admin can set new password"></label>
          <label class="full">Admin Note<textarea name="admin_note" rows="2" placeholder="Internal note for this student"><?= e($student['admin_note'] ?? '') ?></textarea></label>
          <button class="btn btn-primary btn-sm" type="submit">Save</button>
        </form>
        <form method="post" data-confirm="Delete/hide this student from normal list? Test and practice records will remain saved.">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string)$student['id']) ?>"><input type="hidden" name="action" value="delete">
          <button class="btn btn-danger btn-sm" type="submit">Delete</button>
        </form>
      </details>
    </article>
    <?php endforeach; ?>
</div>
<script>
document.getElementById('selectAllStudents')?.addEventListener('change', function(){
  document.querySelectorAll('.student-card-check input[type="checkbox"]').forEach(cb => { const card = cb.closest('.student-manage-card'); if(!card || card.style.display !== 'none') cb.checked = this.checked; });
});
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
