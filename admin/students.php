<?php
$admin_page_final_styles = ['assets/css/phase147-student-accounts.css'];
require_once __DIR__ . '/_header.php';
ensure_schema_updates();
student_account_ensure_schema();
material_ensure_schema();
weekly_test_ensure_schema();

$errors = [];
$levels = ['Zero Level','Basic','Intermediate','Advanced'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security check failed. Please refresh and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        $id = (int)($_POST['id'] ?? 0);
        try {
            if ($action === 'save' && $id > 0) {
                $level = trim((string)($_POST['current_level'] ?? 'Zero Level')) ?: 'Zero Level';
                if (!in_array($level, $levels, true)) throw new RuntimeException('Please select a valid level.');
                $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
                $goal = mb_substr(trim((string)($_POST['target_goal'] ?? '')), 0, 180);
                $minutes = max(5, min(180, (int)($_POST['daily_goal_minutes'] ?? 20)));
                $note = trim((string)($_POST['admin_note'] ?? ''));
                $old = db()->prepare('SELECT published FROM students WHERE id=? AND status_deleted=0 LIMIT 1');
                $old->execute([$id]);
                $oldStatus = (string)($old->fetchColumn() ?: 'No');
                $invalidate = $oldStatus === 'Yes' && $published === 'No';
                db()->prepare('UPDATE students SET current_level=?, target_goal=?, daily_goal_minutes=?, published=?, admin_note=? WHERE id=? AND status_deleted=0')
                    ->execute([$level, $goal ?: null, $minutes, $published, $note ?: null, $id]);
                if ($invalidate) student_account_invalidate_sessions($id);
                student_account_log($id, 'profile_update', 'Student account settings updated', 'Level, goal, daily target, status or note was updated from the account list.');
                flash('success', 'Student account updated successfully.');
                redirect('students.php');
            }
            if ($action === 'set_status' && $id > 0) {
                $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
                db()->prepare('UPDATE students SET published=? WHERE id=? AND status_deleted=0')->execute([$published, $id]);
                if ($published === 'No') student_account_invalidate_sessions($id);
                student_account_log($id, $published === 'Yes' ? 'account_activated' : 'account_deactivated', $published === 'Yes' ? 'Student account activated' : 'Student account deactivated', $published === 'No' ? 'Existing student sessions were invalidated.' : 'Student can login again.');
                flash('success', $published === 'Yes' ? 'Student account activated.' : 'Student account deactivated and signed out.');
                redirect('students.php');
            }
            if ($action === 'delete' && $id > 0) {
                db()->prepare("UPDATE students SET status_deleted=1, published='No' WHERE id=?")->execute([$id]);
                student_account_invalidate_sessions($id);
                student_account_log($id, 'account_hidden', 'Student account hidden', 'The account was soft deleted; learning and test records were preserved.');
                flash('success', 'Student hidden safely. Existing sessions were invalidated and records remain saved.');
                redirect('students.php');
            }
            if ($action === 'bulk') {
                $ids = array_values(array_unique(array_filter(array_map('intval', $_POST['student_ids'] ?? []))));
                $bulkAction = (string)($_POST['bulk_action'] ?? '');
                if (!$ids) throw new RuntimeException('Please select at least one student.');
                if (count($ids) > 250) throw new RuntimeException('Please update no more than 250 students at once.');
                $in = implode(',', array_fill(0, count($ids), '?'));
                if ($bulkAction === 'approve') {
                    db()->prepare("UPDATE students SET published='Yes' WHERE id IN ($in) AND status_deleted=0")->execute($ids);
                    $eventType = 'account_activated'; $eventTitle = 'Student account activated in bulk';
                    flash('success', count($ids) . ' student account(s) activated.');
                } elseif ($bulkAction === 'not_approve') {
                    db()->prepare("UPDATE students SET published='No' WHERE id IN ($in) AND status_deleted=0")->execute($ids);
                    foreach ($ids as $studentId) student_account_invalidate_sessions($studentId);
                    $eventType = 'account_deactivated'; $eventTitle = 'Student account deactivated in bulk';
                    flash('success', count($ids) . ' account(s) deactivated and signed out.');
                } elseif ($bulkAction === 'unverify_mobile' && column_exists('students','identity_status')) {
                    db()->prepare("UPDATE students SET identity_status='Unverified' WHERE id IN ($in) AND status_deleted=0")->execute($ids);
                    $eventType = 'mobile_unverified'; $eventTitle = 'Student mobile marked unverified by institute';
                    flash('success', count($ids) . ' student mobile number(s) marked Unverified.');
                } elseif ($bulkAction === 'delete') {
                    db()->prepare("UPDATE students SET status_deleted=1, published='No' WHERE id IN ($in)")->execute($ids);
                    foreach ($ids as $studentId) student_account_invalidate_sessions($studentId);
                    $eventType = 'account_hidden'; $eventTitle = 'Student account hidden in bulk';
                    flash('success', count($ids) . ' account(s) hidden safely.');
                } else {
                    throw new RuntimeException('Please choose a valid bulk action.');
                }
                foreach ($ids as $studentId) student_account_log($studentId, $eventType, $eventTitle);
                redirect('students.php');
            }
        } catch (Throwable $e) {
            error_log('[admin-students] ' . $e->__toString());
            $errors[] = ($e instanceof RuntimeException && !($e instanceof PDOException))
                ? $e->getMessage()
                : 'Student account change could not be saved. Check Admin > System Check.';
        }
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$levelFilter = trim((string)($_GET['level'] ?? ''));
$loginFilter = trim((string)($_GET['login'] ?? ''));
$identityFilter = trim((string)($_GET['identity'] ?? ''));
$hasIdentityStatus = column_exists('students','identity_status');
$where = ['s.status_deleted=0'];
$params = [];
if ($q !== '') {
    $where[] = '(s.full_name LIKE ? OR s.phone LIKE ? OR s.email LIKE ? OR s.target_goal LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if ($status === 'Yes' || $status === 'No') { $where[] = 's.published=?'; $params[] = $status; }
if (in_array($levelFilter, $levels, true)) { $where[] = 's.current_level=?'; $params[] = $levelFilter; }
if ($loginFilter === 'never') $where[] = 's.last_login_at IS NULL';
if ($loginFilter === 'recent') $where[] = 's.last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
if ($loginFilter === 'inactive') $where[] = '(s.last_login_at IS NULL OR s.last_login_at < DATE_SUB(NOW(), INTERVAL 30 DAY))';
if ($hasIdentityStatus && in_array($identityFilter, ['Verified','Unverified'], true)) { $where[] = 's.identity_status=?'; $params[] = $identityFilter; }
$sqlWhere = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM students s WHERE $sqlWhere");
$countStmt->execute($params);
$studentPager = admin_pagination_state((int)$countStmt->fetchColumn(), 24);
$stmt = db()->prepare("SELECT s.*,
    (SELECT COUNT(*) FROM student_activity_logs a WHERE a.student_id=s.id) activity_count,
    (SELECT COUNT(*) FROM weekly_test_attempts w WHERE w.student_id=s.id AND COALESCE(w.status_deleted,0)=0) weekly_count
    FROM students s WHERE $sqlWhere ORDER BY s.id DESC LIMIT {$studentPager['per_page']} OFFSET {$studentPager['offset']}");
$stmt->execute($params);
$students = $stmt->fetchAll();

$stats = ['total'=>0,'active'=>0,'inactive'=>0,'never_login'=>0,'recent'=>0,'tests'=>0,'unverified'=>0];
try {
    $stats['total'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0")->fetchColumn();
    $stats['active'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND published='Yes'")->fetchColumn();
    $stats['inactive'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND published='No'")->fetchColumn();
    $stats['never_login'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND last_login_at IS NULL")->fetchColumn();
    $stats['recent'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND last_login_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    $stats['tests'] = (int)db()->query("SELECT COUNT(*) FROM weekly_test_attempts WHERE COALESCE(status_deleted,0)=0")->fetchColumn();
    if ($hasIdentityStatus) $stats['unverified'] = (int)db()->query("SELECT COUNT(*) FROM students WHERE status_deleted=0 AND identity_status='Unverified'")->fetchColumn();
} catch (Throwable $e) {}

function student_status_badge_class(string $status): string { return $status === 'Yes' ? 'badge-yes' : 'badge-no'; }
function student_initials(array $s): string {
    $name = trim((string)($s['full_name'] ?? 'Student'));
    $parts = preg_split('/\s+/', $name);
    $letters = '';
    foreach (array_slice($parts ?: [], 0, 2) as $part) $letters .= mb_substr($part, 0, 1);
    return mb_strtoupper($letters ?: 'S');
}
function student_admin_date(?string $value, string $fallback = 'Never'): string {
    if (!$value) return $fallback;
    $time = strtotime($value);
    return $time ? date('d M Y, h:i A', $time) : $fallback;
}
?>
<div class="admin-page-head student-admin-head wf147-account-head">
  <div><span class="eyebrow">Student Account Control</span><h1>Student Accounts</h1><p>Search every student, activate or suspend access, manage learning settings, reset forgotten passwords and open complete practice/test history.</p></div>
  <div class="head-actions"><a class="btn btn-primary" href="../student-auth.php?mode=register" target="_blank"><i class="fa-solid fa-user-plus"></i> Open Registration</a><a class="btn btn-soft" href="weekly-tests.php#student-copies"><i class="fa-solid fa-file-circle-check"></i> Test Copies</a></div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>

<div class="student-crm-stats wf147-account-stats">
  <a href="students.php"><i class="fa-solid fa-users"></i><b><?= e((string)$stats['total']) ?></b><span>Total Accounts</span></a>
  <a href="students.php?status=Yes"><i class="fa-solid fa-circle-check"></i><b><?= e((string)$stats['active']) ?></b><span>Active</span></a>
  <a href="students.php?status=No"><i class="fa-solid fa-user-lock"></i><b><?= e((string)$stats['inactive']) ?></b><span>Inactive</span></a>
  <?php if($hasIdentityStatus): ?><a href="students.php?identity=Unverified"><i class="fa-solid fa-mobile-screen-button"></i><b><?= e((string)$stats['unverified']) ?></b><span>Mobile Unverified</span></a><?php endif; ?>
  <a href="students.php?login=never"><i class="fa-solid fa-user-clock"></i><b><?= e((string)$stats['never_login']) ?></b><span>Never Logged In</span></a>
  <a href="students.php?login=recent"><i class="fa-solid fa-arrow-trend-up"></i><b><?= e((string)$stats['recent']) ?></b><span>Active in 7 Days</span></a>
  <a href="weekly-tests.php#student-copies"><i class="fa-solid fa-clipboard-check"></i><b><?= e((string)$stats['tests']) ?></b><span>Test Attempts</span></a>
</div>

<div class="panel-card student-filter-panel wf147-filter-panel">
  <form method="get" class="student-filter-form wf147-student-filter">
    <label><span>Search student</span><input name="q" value="<?= e($q) ?>" placeholder="Name, phone, email or goal"></label>
    <label><span>Account status</span><select name="status"><option value="">All Status</option><option value="Yes" <?= $status==='Yes'?'selected':'' ?>>Active</option><option value="No" <?= $status==='No'?'selected':'' ?>>Inactive</option></select></label>
    <?php if($hasIdentityStatus): ?><label><span>Mobile identity</span><select name="identity"><option value="">All Identity</option><option value="Unverified" <?= $identityFilter==='Unverified'?'selected':'' ?>>Unverified</option><option value="Verified" <?= $identityFilter==='Verified'?'selected':'' ?>>Verified by Institute</option></select></label><?php endif; ?>
    <label><span>Learning level</span><select name="level"><option value="">All Levels</option><?php foreach($levels as $lv): ?><option value="<?= e($lv) ?>" <?= $levelFilter===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select></label>
    <label><span>Login activity</span><select name="login"><option value="">All Login Activity</option><option value="recent" <?= $loginFilter==='recent'?'selected':'' ?>>Last 7 days</option><option value="never" <?= $loginFilter==='never'?'selected':'' ?>>Never logged in</option><option value="inactive" <?= $loginFilter==='inactive'?'selected':'' ?>>Inactive 30+ days</option></select></label>
    <div class="wf147-filter-actions"><button class="btn btn-dark" type="submit">Apply Filters</button><a class="btn btn-soft" href="students.php">Reset</a></div>
  </form>
</div>

<form method="post" id="studentBulkForm" class="student-bulk-bar wf147-bulk-bar" data-confirm="Apply selected account action? Deactivate and hide actions will sign students out.">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="bulk">
  <label class="checkline"><input type="checkbox" id="selectAllStudents"> Select visible students</label>
  <select name="bulk_action" required><option value="">Choose bulk action</option><option value="approve">Activate accounts</option><option value="not_approve">Deactivate + sign out</option><?php if($hasIdentityStatus): ?><option value="unverify_mobile">Mark mobile Unverified</option><?php endif; ?><option value="delete">Hide accounts safely</option></select>
  <button class="btn btn-soft" type="submit">Apply</button>
</form>

<div class="student-card-grid wf147-account-grid">
  <?php if (!$students): ?><div class="admin-card empty-state">No student accounts match the selected filters.</div><?php endif; ?>
  <?php foreach ($students as $student): $phoneDigits = preg_replace('/\D+/', '', (string)$student['phone']); ?>
  <article class="student-manage-card wf147-account-card <?= $student['published']==='Yes'?'approved':'blocked' ?>">
    <div class="student-card-top">
      <label class="student-card-check" title="Select student"><input type="checkbox" name="student_ids[]" form="studentBulkForm" value="<?= e((string)$student['id']) ?>"></label>
      <div class="student-avatar-admin"><?= e(student_initials($student)) ?></div>
      <div class="student-main-info"><h2><?= e($student['full_name']) ?></h2><p><?= e($student['phone']) ?><?= $student['email'] ? ' • '.e($student['email']) : '' ?></p></div>
      <span class="badge <?= e(student_status_badge_class((string)$student['published'])) ?>"><?= $student['published']==='Yes'?'Active':'Inactive' ?></span>
      <?php if($hasIdentityStatus): ?><span class="badge <?= ($student['identity_status']??'Unverified')==='Verified'?'badge-yes':'badge-no' ?>"><?= e($student['identity_status']??'Unverified') ?></span><?php endif; ?>
    </div>
    <div class="student-mini-metrics wf147-account-metrics">
      <div><b><?= e($student['current_level'] ?: 'Zero Level') ?></b><span>Level</span></div>
      <div><b><?= e((string)($student['activity_count'] ?? 0)) ?></b><span>Practice</span></div>
      <div><b><?= e((string)($student['weekly_count'] ?? 0)) ?></b><span>Tests</span></div>
      <div><b><?= e($student['last_login_at'] ? date('d M', strtotime((string)$student['last_login_at'])) : 'Never') ?></b><span>Last Login</span></div>
    </div>
    <div class="wf147-account-facts">
      <span><i class="fa-solid fa-calendar-plus"></i> Joined <?= e(student_admin_date((string)$student['created_at'], '-')) ?></span>
      <span><i class="fa-solid fa-key"></i> Password <?= e(!empty($student['password_changed_at']) ? 'changed '.student_admin_date((string)$student['password_changed_at']) : 'not reset by admin') ?></span>
    </div>
    <p class="student-goal-line"><?= e($student['target_goal'] ?: 'No learning goal saved yet.') ?></p>
    <div class="student-card-actions wf147-card-actions">
      <a class="btn btn-sm btn-primary" href="student-view.php?id=<?= e((string)$student['id']) ?>"><i class="fa-solid fa-user-gear"></i> Manage Account</a>
      <a class="btn btn-sm btn-soft" href="weekly-student-record.php?phone=<?= e(urlencode((string)$student['phone'])) ?>"><i class="fa-solid fa-clipboard-list"></i> Tests</a>
      <?php if ($phoneDigits): ?><a class="btn btn-sm btn-green" href="https://wa.me/<?= e($phoneDigits) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
      <form method="post" class="inline-form" data-confirm="<?= $student['published']==='Yes'?'Deactivate this account and sign the student out?':'Activate this student account?' ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_status"><input type="hidden" name="id" value="<?= e((string)$student['id']) ?>"><input type="hidden" name="published" value="<?= $student['published']==='Yes'?'No':'Yes' ?>"><button class="btn btn-sm <?= $student['published']==='Yes'?'btn-soft':'btn-green' ?>" type="submit"><?= $student['published']==='Yes'?'Deactivate':'Activate' ?></button></form>
    </div>
    <details class="student-edit-box wf147-quick-edit">
      <summary><i class="fa-solid fa-sliders"></i> Quick Learning Settings</summary>
      <form method="post" class="student-edit-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string)$student['id']) ?>"><input type="hidden" name="action" value="save">
        <label>Level<select name="current_level"><?php foreach($levels as $lv): ?><option <?= $student['current_level']===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select></label>
        <label>Status<select name="published"><option value="Yes" <?= $student['published']==='Yes'?'selected':'' ?>>Active</option><option value="No" <?= $student['published']==='No'?'selected':'' ?>>Inactive</option></select></label>
        <label>Daily Goal<input type="number" min="5" max="180" name="daily_goal_minutes" value="<?= e((string)($student['daily_goal_minutes'] ?? 20)) ?>"></label>
        <label>Target Goal<input name="target_goal" value="<?= e($student['target_goal'] ?? '') ?>" placeholder="Interview / daily speaking / grammar"></label>
        <label class="full">Admin Note<textarea name="admin_note" rows="2" placeholder="Internal account note"><?= e($student['admin_note'] ?? '') ?></textarea></label>
        <button class="btn btn-primary btn-sm" type="submit">Save Settings</button>
      </form>
      <p class="wf147-password-hint"><i class="fa-solid fa-shield-halved"></i> Password reset is available inside <b>Manage Account</b> with session invalidation and an audit record.</p>
    </details>
  </article>
  <?php endforeach; ?>
</div>
<?= admin_pagination_html($studentPager) ?>
<script>
document.getElementById('selectAllStudents')?.addEventListener('change', function(){
  document.querySelectorAll('.student-card-check input[type="checkbox"]').forEach(cb => { cb.checked = this.checked; });
});
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
