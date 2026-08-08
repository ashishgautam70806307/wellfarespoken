<?php
$admin_page_final_styles = ['assets/css/phase147-student-accounts.css'];
require_once __DIR__ . '/_header.php';
ensure_schema_updates();
student_account_ensure_schema();
material_ensure_schema();
weekly_test_ensure_schema();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM students WHERE id=? AND status_deleted=0 LIMIT 1');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { flash('error','Student account not found.'); redirect('students.php'); }

$levels = ['Zero Level','Basic','Intermediate','Advanced'];
$languages = ['Hindi','English','Hindi + English'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security check failed. Refresh the page and try again.');
        redirect('student-view.php?id=' . $id);
    }
    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'save_profile') {
            $name = trim((string)($_POST['full_name'] ?? ''));
            $phone = clean_phone_digits((string)($_POST['phone'] ?? ''));
            if (strlen($phone) > 10 && str_starts_with($phone, '91')) $phone = substr($phone, -10);
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $level = trim((string)($_POST['current_level'] ?? 'Zero Level')) ?: 'Zero Level';
            $language = trim((string)($_POST['preferred_language'] ?? 'Hindi')) ?: 'Hindi';
            $minutes = max(5, min(180, (int)($_POST['daily_goal_minutes'] ?? 20)));
            $goal = mb_substr(trim((string)($_POST['target_goal'] ?? '')), 0, 180);
            $note = trim((string)($_POST['admin_note'] ?? ''));
            $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
            $identityStatus = column_exists('students','identity_status') && ($_POST['identity_status'] ?? 'Unverified') === 'Verified' ? 'Verified' : 'Unverified';
            $oldPhoneDigits = clean_phone_digits((string)($student['phone'] ?? ''));
            if (strlen($oldPhoneDigits) > 10 && str_starts_with($oldPhoneDigits, '91')) $oldPhoneDigits = substr($oldPhoneDigits, -10);
            $phoneChanged = $phone !== $oldPhoneDigits;
            $oldIdentityStatus = (string)($student['identity_status'] ?? 'Unverified');
            $verificationNote = mb_substr(trim((string)($_POST['identity_verification_note'] ?? '')), 0, 500);
            if ($phoneChanged && column_exists('students','identity_status')) $identityStatus = 'Unverified';
            if (!$phoneChanged && column_exists('students','identity_status') && $oldIdentityStatus !== 'Verified' && $identityStatus === 'Verified' && mb_strlen($verificationNote) < 3) throw new RuntimeException('Add a short verification note before marking the mobile Verified, for example: confirmed in person or called the registered number.');
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) throw new RuntimeException('Student name must contain 2 to 100 characters.');
            if (strlen($phone) !== 10) throw new RuntimeException('Please enter a valid 10 digit phone number.');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Please enter a valid email address.');
            if (!in_array($level, $levels, true)) throw new RuntimeException('Please select a valid learning level.');
            if (!in_array($language, $languages, true)) $language = 'Hindi';
            $duplicate = db()->prepare('SELECT id FROM students WHERE phone=? AND id<>? AND status_deleted=0 LIMIT 1');
            $duplicate->execute([$phone, $id]);
            if ($duplicate->fetchColumn()) throw new RuntimeException('This phone number already belongs to another student account.');
            $invalidate = (string)$student['published'] === 'Yes' && $published === 'No';
            if (column_exists('students','identity_status')) {
                db()->prepare('UPDATE students SET full_name=?, phone=?, email=?, current_level=?, preferred_language=?, daily_goal_minutes=?, target_goal=?, admin_note=?, published=?, identity_status=? WHERE id=? AND status_deleted=0')
                    ->execute([$name,$phone,$email ?: null,$level,$language,$minutes,$goal ?: null,$note ?: null,$published,$identityStatus,$id]);
            } else {
                db()->prepare('UPDATE students SET full_name=?, phone=?, email=?, current_level=?, preferred_language=?, daily_goal_minutes=?, target_goal=?, admin_note=?, published=? WHERE id=? AND status_deleted=0')
                    ->execute([$name,$phone,$email ?: null,$level,$language,$minutes,$goal ?: null,$note ?: null,$published,$id]);
            }
            if ($invalidate || $phoneChanged) student_account_invalidate_sessions($id);
            if (column_exists('students','identity_status') && $identityStatus === 'Verified') lifecycle_link_student_registration($id,$phone);
            if (column_exists('students','identity_status') && !$phoneChanged && $oldIdentityStatus !== 'Verified' && $identityStatus === 'Verified') {
                student_account_log($id, 'mobile_verified', 'Student mobile verified by institute', 'Verification note: ' . $verificationNote);
                admin_audit_log('student.mobile_verified','student',$id,'Verification note: ' . $verificationNote);
            }
            if (column_exists('students','identity_status') && $oldIdentityStatus === 'Verified' && $identityStatus !== 'Verified') {
                student_account_log($id, 'mobile_unverified', 'Student mobile marked unverified', $phoneChanged ? 'Verification reset because the mobile number changed.' : 'Institute removed the verified status.');
                admin_audit_log('student.mobile_unverified','student',$id,$phoneChanged ? 'Verification reset after mobile change.' : 'Verified status removed by administrator.');
            }
            student_account_log($id, 'profile_update', 'Student profile and account settings updated', 'Identity, contact, level, learning target, account status or admin note was updated.');
            $profileMessage = $invalidate ? 'Student profile saved. Account was deactivated and existing sessions were signed out.' : 'Student profile and account settings saved.';
            if ($phoneChanged && column_exists('students','identity_status')) $profileMessage .= ' Mobile verification was reset because the phone number changed.';
            flash('success', $profileMessage);
            redirect('student-view.php?id=' . $id);
        }
        if ($action === 'reset_password') {
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');
            $resetReason = mb_substr(trim((string)($_POST['reset_reason'] ?? '')), 0, 500);
            if (mb_strlen($resetReason) < 3) throw new RuntimeException('Add a short reset reason, for example: student confirmed in person or by phone.');
            $passwordError = student_password_error($newPassword);
            if ($passwordError !== '') throw new RuntimeException($passwordError);
            if (!hash_equals($newPassword, $confirmPassword)) throw new RuntimeException('New password and confirmation do not match.');
            db()->beginTransaction();
            try {
                if (!student_account_reset_password($id, password_hash($newPassword, PASSWORD_DEFAULT))) {
                    throw new RuntimeException('Student account was not updated.');
                }
                student_account_log($id, 'password_reset', 'Student password reset by admin', 'Reason: ' . $resetReason . ' All existing student sessions were invalidated. The password itself was not stored in logs.');
                admin_audit_log('student.password_reset', 'student', $id, 'Reason: ' . $resetReason . '. Password value was not logged.');
                db()->commit();
            } catch (Throwable $e) {
                if (db()->inTransaction()) db()->rollBack();
                throw $e;
            }
            flash('success', 'Password changed successfully. The student can now use the new password and all old sessions were signed out.');
            redirect('student-view.php?id=' . $id . '#password-control');
        }
        if ($action === 'force_logout') {
            student_account_invalidate_sessions($id);
            student_account_log($id, 'force_logout', 'Student signed out from all sessions', 'Admin invalidated all active sessions without changing the password.');
            flash('success', 'All current sessions for this student were invalidated.');
            redirect('student-view.php?id=' . $id . '#security-control');
        }
        if ($action === 'set_status') {
            $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
            db()->prepare('UPDATE students SET published=? WHERE id=? AND status_deleted=0')->execute([$published, $id]);
            if ($published === 'No') student_account_invalidate_sessions($id);
            student_account_log($id, $published === 'Yes' ? 'account_activated' : 'account_deactivated', $published === 'Yes' ? 'Student account activated' : 'Student account deactivated', $published === 'No' ? 'Existing sessions were invalidated.' : 'Student login access restored.');
            flash('success', $published === 'Yes' ? 'Student account activated.' : 'Student account deactivated and signed out.');
            redirect('student-view.php?id=' . $id);
        }
        if ($action === 'delete') {
            db()->prepare("UPDATE students SET status_deleted=1, published='No' WHERE id=?")->execute([$id]);
            student_account_invalidate_sessions($id);
            student_account_log($id, 'account_hidden', 'Student account hidden', 'Soft delete applied. Practice, roadmap and test history remain stored.');
            flash('success', 'Student account hidden safely. Related learning records were preserved.');
            redirect('students.php');
        }
    } catch (Throwable $e) {
        error_log('[admin-student-view] ' . $e->__toString());
        flash('error', ($e instanceof RuntimeException && !($e instanceof PDOException)) ? $e->getMessage() : 'Student account could not be updated.');
        redirect('student-view.php?id=' . $id);
    }
}

$stmt = db()->prepare('SELECT * FROM students WHERE id=? AND status_deleted=0 LIMIT 1');
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { flash('error','Student account not found.'); redirect('students.php'); }

$summary = student_activity_summary($id);
$metrics = student_learning_metrics($id);
$activities = fetch_student_activity($id, 12);
$weeklyAttempts = weekly_test_fetch_attempts_for_student($id, 12);
$recentAttempts = student_recent_material_attempts($id, 10);
$wrongAttempts = student_wrong_material_attempts($id, 10);
$accountEvents = student_account_events($id, 24);
$progress = student_level_progress_percent((string)$student['current_level'], $metrics);
function admin_student_initials(array $s): string { $n=trim((string)($s['full_name']??'S')); $p=preg_split('/\s+/', $n); $o=''; foreach(array_slice($p?:[],0,2) as $x){$o.=mb_substr($x,0,1);} return mb_strtoupper($o?:'S'); }
function admin_student_datetime(?string $value, string $fallback='Never'): string { if(!$value)return $fallback; $t=strtotime($value); return $t?date('d M Y, h:i A',$t):$fallback; }
?>
<div class="admin-page-head student-detail-head wf147-detail-head">
  <div><span class="eyebrow">Student Account Manager</span><h1><?= e($student['full_name']) ?></h1><p>Manage identity, access, password, learning settings, tests, practice and account history without knowing the current password.</p></div>
  <div class="head-actions"><a class="btn btn-soft" href="students.php"><i class="fa-solid fa-arrow-left"></i> All Accounts</a><a class="btn btn-primary" href="weekly-student-record.php?phone=<?= e(urlencode((string)$student['phone'])) ?>"><i class="fa-solid fa-clipboard-list"></i> Test Records</a></div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="student-detail-hero-card wf147-student-hero">
  <div class="student-detail-avatar"><?= e(admin_student_initials($student)) ?></div>
  <div class="wf147-student-identity"><span class="badge <?= $student['published']==='Yes'?'badge-yes':'badge-no' ?>"><?= $student['published']==='Yes'?'Active Account':'Inactive Account' ?></span><?php if(column_exists('students','identity_status')): ?><span class="badge <?= ($student['identity_status']??'Unverified')==='Verified'?'badge-yes':'badge-no' ?>"><?= e(($student['identity_status']??'Unverified')==='Verified'?'Mobile Verified by Institute':'Mobile Unverified') ?></span><?php endif; ?><h2><?= e($student['current_level'] ?: 'Zero Level') ?></h2><p><?= e($student['target_goal'] ?: 'No learning goal saved yet') ?></p><div class="wf147-identity-meta"><span><i class="fa-solid fa-phone"></i><?= e($student['phone']) ?></span><?php if($student['email']): ?><span><i class="fa-solid fa-envelope"></i><?= e($student['email']) ?></span><?php endif; ?></div></div>
  <div class="student-progress-ring admin-ring" style="--p:<?= e((string)$progress) ?>"><div><strong><?= e((string)$progress) ?>%</strong><span>Progress</span></div></div>
</div>

<div class="student-crm-stats detail-stats wf147-detail-stats">
  <div><b><?= e((string)$metrics['practice_total']) ?></b><span>Practice Total</span></div>
  <div><b><?= e((string)$metrics['correct_rate']) ?>%</b><span>Correct Rate</span></div>
  <div><b><?= e((string)$metrics['weekly_attempts']) ?></b><span>Weekly Attempts</span></div>
  <div><b><?= e(admin_student_datetime((string)($student['last_login_at'] ?? ''))) ?></b><span>Last Login</span></div>
  <div><b><?= e(admin_student_datetime((string)($student['password_changed_at'] ?? ''), 'Not reset')) ?></b><span>Password Changed</span></div>
</div>

<div class="wf147-account-control-grid">
  <section class="panel-card wf147-profile-control">
    <div class="wf147-card-heading"><span><i class="fa-solid fa-id-card"></i></span><div><h2>Profile & Learning Settings</h2><p>Update the student identity, contact and learning configuration.</p></div></div>
    <form method="post" class="wf147-profile-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_profile">
      <label><span>Full Name</span><input name="full_name" value="<?= e($student['full_name']) ?>" maxlength="100" required></label>
      <label><span>Phone Number</span><input name="phone" value="<?= e($student['phone']) ?>" inputmode="numeric" maxlength="15" required></label>
      <label><span>Email Address</span><input type="email" name="email" value="<?= e($student['email'] ?? '') ?>"></label>
      <label><span>Learning Level</span><select name="current_level"><?php foreach($levels as $lv): ?><option value="<?= e($lv) ?>" <?= $student['current_level']===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select></label>
      <label><span>Preferred Language</span><select name="preferred_language"><?php foreach($languages as $lang): ?><option value="<?= e($lang) ?>" <?= ($student['preferred_language']??'Hindi')===$lang?'selected':'' ?>><?= e($lang) ?></option><?php endforeach; ?></select></label>
      <label><span>Daily Goal (minutes)</span><input type="number" min="5" max="180" name="daily_goal_minutes" value="<?= e((string)($student['daily_goal_minutes'] ?? 20)) ?>"></label>
      <label class="full"><span>Target Goal</span><input name="target_goal" value="<?= e($student['target_goal'] ?? '') ?>" maxlength="180" placeholder="Interview, fluency, grammar, confidence..."></label>
      <label><span>Account Access</span><select name="published"><option value="Yes" <?= $student['published']==='Yes'?'selected':'' ?>>Active</option><option value="No" <?= $student['published']==='No'?'selected':'' ?>>Inactive</option></select></label>
      <?php if(column_exists('students','identity_status')): ?><label><span>Mobile Identity</span><select name="identity_status"><option value="Unverified" <?= ($student['identity_status']??'Unverified')==='Unverified'?'selected':'' ?>>Unverified</option><option value="Verified" <?= ($student['identity_status']??'Unverified')==='Verified'?'selected':'' ?>>Verified by Institute</option></select><small>Use Verified only after staff confirms the number belongs to the student/guardian. Changing the phone resets verification.</small></label><label><span>Verification Note</span><input name="identity_verification_note" maxlength="500" placeholder="Required when first marking Verified"><small>Example: confirmed in person / called registered mobile. Saved to the account audit timeline.</small></label><?php endif; ?>
      <label class="full"><span>Private Admin Note</span><textarea name="admin_note" rows="4" placeholder="Internal note visible only to admin"><?= e($student['admin_note'] ?? '') ?></textarea></label>
      <div class="full wf147-form-actions"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Student Account</button></div>
    </form>
  </section>

  <aside class="wf147-security-column">
    <section class="panel-card wf147-security-card" id="password-control">
      <div class="wf147-card-heading"><span><i class="fa-solid fa-key"></i></span><div><h2>Reset Forgotten Password</h2><p>Set a new password chosen by the institute. The old password is not required.</p></div></div>
      <form method="post" class="wf147-password-form" data-confirm="Change this password and sign the student out from all existing sessions?">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="reset_password">
        <label><span>New Password</span><div class="wf147-password-field"><input type="password" id="studentNewPassword" name="new_password" minlength="8" maxlength="128" autocomplete="new-password" required><button type="button" class="btn btn-soft" id="generateStudentPassword">Generate</button></div></label>
        <label><span>Confirm Password</span><input type="password" id="studentConfirmPassword" name="confirm_password" minlength="8" maxlength="128" autocomplete="new-password" required></label>
        <label><span>Reset Reason</span><input name="reset_reason" maxlength="500" required placeholder="Example: student confirmed in person / on registered phone"></label>
        <?php if(column_exists('students','identity_status') && ($student['identity_status']??'Unverified')!=='Verified'): ?><div class="alert alert-warning">This mobile number is still <strong>Unverified</strong>. Confirm the student/guardian identity manually before sharing the new password.</div><?php endif; ?>
        <div class="wf147-password-tools"><button type="button" class="btn btn-soft" id="copyStudentPassword"><i class="fa-regular fa-copy"></i> Copy Password</button><small>Minimum 8 characters. Share it privately with the student.</small></div>
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-rotate"></i> Change Password</button>
      </form>
    </section>

    <section class="panel-card wf147-security-card" id="security-control">
      <div class="wf147-card-heading"><span><i class="fa-solid fa-shield-halved"></i></span><div><h2>Account Access Control</h2><p>Security actions affect active sessions immediately.</p></div></div>
      <div class="wf147-security-actions">
        <form method="post" data-confirm="Sign this student out from every current session without changing the password?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="force_logout"><button class="btn btn-soft" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Force Sign Out</button></form>
        <form method="post" data-confirm="<?= $student['published']==='Yes'?'Deactivate this account and sign the student out?':'Activate this account so the student can login?' ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="set_status"><input type="hidden" name="published" value="<?= $student['published']==='Yes'?'No':'Yes' ?>"><button class="btn <?= $student['published']==='Yes'?'btn-soft':'btn-green' ?>" type="submit"><i class="fa-solid <?= $student['published']==='Yes'?'fa-user-lock':'fa-user-check' ?>"></i> <?= $student['published']==='Yes'?'Deactivate Account':'Activate Account' ?></button></form>
        <form method="post" data-confirm="Hide this student account? Learning, practice and test records will remain saved."><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-danger" type="submit"><i class="fa-solid fa-trash-can"></i> Hide Account</button></form>
      </div>
      <div class="wf147-security-facts"><span>Auth version: <?= e((string)($student['auth_version'] ?? 1)) ?></span><span>Joined: <?= e(admin_student_datetime((string)$student['created_at'])) ?></span></div>
    </section>
  </aside>
</div>

<div class="student-detail-grid wf147-learning-grid">
  <section class="panel-card"><div class="wf147-card-heading"><span><i class="fa-solid fa-clipboard-check"></i></span><div><h2>Latest Weekly Tests</h2><p>Most recent saved test attempts.</p></div></div><div class="activity-list compact-attempt-list"><?php if(!$weeklyAttempts): ?><p class="muted">No test attempts yet.</p><?php endif; ?><?php foreach($weeklyAttempts as $a): ?><div class="attempt-row"><strong><?= e($a['test_title']) ?></strong><span><?= e(weekly_test_status_badge((string)$a['status'])) ?> • Auto <?= e((string)($a['auto_score'] ?? '-')) ?> / Admin <?= e((string)($a['admin_score'] ?? '-')) ?></span><small><?= e(admin_student_datetime((string)($a['started_at'] ?? ''))) ?></small></div><?php endforeach; ?></div></section>
  <section class="panel-card"><div class="wf147-card-heading"><span><i class="fa-solid fa-clock-rotate-left"></i></span><div><h2>Account Change History</h2><p>Password resets, status changes and security actions.</p></div></div><div class="wf147-account-timeline"><?php if(!$accountEvents): ?><p class="muted">No admin account events recorded yet.</p><?php endif; ?><?php foreach($accountEvents as $event): ?><article><i class="fa-solid <?= $event['event_type']==='password_reset'?'fa-key':($event['event_type']==='force_logout'?'fa-right-from-bracket':'fa-shield-halved') ?>"></i><div><strong><?= e($event['event_title']) ?></strong><span><?= e($event['event_note'] ?? '') ?></span><small><?= e(admin_student_datetime((string)$event['created_at'])) ?> • <?= e($event['admin_name'] ?: 'System') ?></small></div></article><?php endforeach; ?></div></section>
</div>

<div class="student-detail-grid wf147-learning-grid">
  <section class="panel-card"><h2>Recent Practice</h2><div class="activity-list compact-attempt-list"><?php if(!$recentAttempts): ?><p class="muted">No practice attempts yet.</p><?php endif; ?><?php foreach($recentAttempts as $item): ?><div class="attempt-row <?= !empty($item['is_correct'])?'ok':'bad' ?>"><strong><?= !empty($item['is_correct'])?'Correct':'Needs Revision' ?> • <?= e((string)$item['score']) ?>/10</strong><span><?= e($item['correct_answer'] ?: $item['english_text']) ?></span><small><?= e(admin_student_datetime((string)$item['created_at'])) ?></small></div><?php endforeach; ?></div></section>
  <section class="panel-card"><h2>Wrong Answer Review</h2><div class="activity-list compact-attempt-list"><?php if(!$wrongAttempts): ?><p class="muted">No wrong answers saved.</p><?php endif; ?><?php foreach($wrongAttempts as $item): ?><div class="attempt-row bad"><strong><?= e($item['english_text'] ?? 'Practice') ?></strong><span>Student: <?= e($item['user_answer'] ?? '') ?></span><small>Correct: <?= e($item['correct_answer'] ?? '') ?></small></div><?php endforeach; ?></div></section>
</div>

<section class="panel-card"><h2>Manual Activity Log</h2><div class="table-wrap"><table><thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Score</th><th>Note</th></tr></thead><tbody><?php foreach($activities as $a): ?><tr><td data-label="Date"><?= e(admin_student_datetime((string)$a['created_at'])) ?></td><td data-label="Type"><?= e($a['activity_type']) ?></td><td data-label="Title"><?= e($a['activity_title'] ?? '') ?></td><td data-label="Score"><?= e((string)($a['score'] ?? '-')) ?></td><td data-label="Note"><?= e($a['note'] ?? '') ?></td></tr><?php endforeach; ?><?php if(!$activities): ?><tr><td colspan="5" class="empty-state">No manual activities yet.</td></tr><?php endif; ?></tbody></table></div></section>

<script>
(() => {
  const password = document.getElementById('studentNewPassword');
  const confirm = document.getElementById('studentConfirmPassword');
  document.getElementById('generateStudentPassword')?.addEventListener('click', () => {
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    const bytes = new Uint32Array(14);
    crypto.getRandomValues(bytes);
    const value = Array.from(bytes, n => alphabet[n % alphabet.length]).join('');
    password.value = value;
    confirm.value = value;
    password.focus();
    password.select();
  });
  document.getElementById('copyStudentPassword')?.addEventListener('click', async () => {
    if (!password?.value) return;
    try { await navigator.clipboard.writeText(password.value); } catch (_) { password.select(); document.execCommand('copy'); }
  });
})();
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
