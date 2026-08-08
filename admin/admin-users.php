<?php
require_once __DIR__ . '/_header.php';
if (!admin_rbac_ready()) {
    ?>
    <div class="panel-card wf149-db-required">
        <h2>Administrator security setup is not installed yet.</h2>
        <p>Import <code>sql/phase148_critical_backend_hardening.sql</code>, then open System Check. Administrator management stays locked until RBAC is ready.</p>
        <div class="admin-actions"><a class="btn btn-primary" href="system-check.php">Open System Check</a><a class="btn btn-soft" href="dashboard.php">Back to Dashboard</a></div>
    </div>
    <?php require_once __DIR__ . '/_footer.php'; exit;
}
admin_assert_primary_owner();

$primaryOwnerId = admin_primary_owner_id();
$superRoleId = admin_super_role_id();
$rolesStmt = db()->query("SELECT * FROM admin_roles WHERE published='Yes' AND role_key<>'super_admin' ORDER BY is_system DESC,role_name");
$roles = $rolesStmt->fetchAll();

$edit = null;
$editingOwner = false;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT a.*,r.role_key,r.role_name FROM admins a LEFT JOIN admin_roles r ON r.id=a.role_id WHERE a.id=? LIMIT 1');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch() ?: null;
    $editingOwner = $edit && (int)$edit['id'] === $primaryOwnerId;
    if ($edit && ($edit['role_key'] ?? '') === 'super_admin' && !$editingOwner) {
        flash('error', 'A legacy extra Super Admin account was detected. Run the Phase 150 access-control migration before managing it.');
        redirect('admin-users.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security token expired. Refresh and try again.');
        redirect('admin-users.php');
    }

    $action = (string)($_POST['action'] ?? 'save');
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($action !== 'save') throw new RuntimeException('Unsupported administrator action.');

        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['new_password'] ?? '');
        $isOwnerTarget = $id > 0 && $id === $primaryOwnerId;
        $roleId = $isOwnerTarget ? $superRoleId : (int)($_POST['role_id'] ?? 0);
        $published = $isOwnerTarget ? 'Yes' : (($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes');

        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Name and a valid email are required.');
        if ($roleId <= 0) throw new RuntimeException('Choose a valid staff role.');

        $r = db()->prepare("SELECT id,role_key FROM admin_roles WHERE id=? AND published='Yes' LIMIT 1");
        $r->execute([$roleId]);
        $selectedRole = $r->fetch();
        if (!$selectedRole) throw new RuntimeException('Choose a valid active role.');

        // The Super Admin role is never assignable from the UI/API. Only first-run setup owns it.
        if (($selectedRole['role_key'] ?? '') === 'super_admin' && !$isOwnerTarget) {
            throw new RuntimeException('Super Admin is a protected owner role and cannot be assigned to another administrator.');
        }
        if ($isOwnerTarget && ($selectedRole['role_key'] ?? '') !== 'super_admin') {
            throw new RuntimeException('The primary owner role cannot be changed.');
        }
        if ($id === (int)$_SESSION['admin_id'] && $published === 'No') throw new RuntimeException('You cannot deactivate your own account.');

        if ($id > 0) {
            $targetStmt = db()->prepare('SELECT a.id,r.role_key FROM admins a LEFT JOIN admin_roles r ON r.id=a.role_id WHERE a.id=? LIMIT 1');
            $targetStmt->execute([$id]);
            $target = $targetStmt->fetch();
            if (!$target) throw new RuntimeException('Administrator account was not found.');
            if (($target['role_key'] ?? '') === 'super_admin' && $id !== $primaryOwnerId) {
                throw new RuntimeException('Legacy extra Super Admin detected. Run the Phase 150 migration first.');
            }

            db()->prepare('UPDATE admins SET name=?,email=?,role_id=?,published=? WHERE id=?')->execute([$name,$email,$roleId,$published,$id]);
            if ($password !== '') {
                $passwordError = admin_password_error($password);
                if ($passwordError !== '') throw new RuntimeException($passwordError);
                db()->prepare("UPDATE admins SET password_hash=?,auth_version=auth_version+1,must_change_password='Yes',password_changed_at=NOW() WHERE id=?")
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
            }
            if (isset($_POST['reset_mfa'])) {
                db()->prepare("UPDATE admins SET mfa_secret=NULL,mfa_enabled='No',auth_version=auth_version+1 WHERE id=?")->execute([$id]);
            }
            if ($id === (int)$_SESSION['admin_id']) {
                $f = db()->prepare('SELECT * FROM admins WHERE id=?');
                $f->execute([$id]);
                $fresh = $f->fetch();
                if ($fresh) admin_session_login($fresh);
            } else {
                admin_invalidate_sessions($id);
            }
            admin_audit_log('admin.updated', 'admin', $id, $isOwnerTarget ? 'Protected owner profile updated; role/status remained locked.' : 'Staff administrator updated.');
            flash('success', $isOwnerTarget ? 'Owner profile updated. Super Admin role remains protected.' : 'Administrator updated.');
        } else {
            if (($selectedRole['role_key'] ?? '') === 'super_admin') throw new RuntimeException('A second Super Admin cannot be created.');
            $passwordError = admin_password_error($password);
            if ($passwordError !== '') throw new RuntimeException($passwordError);
            $stmt = db()->prepare("INSERT INTO admins (role_id,name,email,password_hash,auth_version,must_change_password,mfa_enabled,published,created_at) VALUES (?,?,?,?,1,'Yes','No',?,NOW())");
            $stmt->execute([$roleId,$name,$email,password_hash($password,PASSWORD_DEFAULT),$published]);
            $id = (int)db()->lastInsertId();
            admin_audit_log('admin.created','admin',$id,'Staff administrator created by protected owner; Super Admin role unavailable.');
            flash('success','Staff administrator created. They must change the temporary password after login.');
        }
        redirect('admin-users.php');
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('admin-users.php' . ($id > 0 ? '?edit=' . $id : ''));
    }
}

$rows = db()->query("SELECT a.*,r.role_name,r.role_key FROM admins a LEFT JOIN admin_roles r ON r.id=a.role_id ORDER BY CASE WHEN a.id=".(int)$primaryOwnerId." THEN 0 ELSE 1 END,a.id")->fetchAll();
?>
<div class="admin-top">
    <div><h1>Administrator Accounts</h1><p>Only the protected institute owner can create staff accounts. The Super Admin role cannot be assigned from this screen.</p></div>
    <a class="btn btn-soft" href="roles.php">Manage Roles</a>
</div>
<?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif;?>
<?php if($m=flash('error')):?><div class="alert alert-danger"><?=e($m)?></div><?php endif;?>
<div class="alert alert-info"><strong>Owner lock:</strong> only one Super Admin is allowed. New administrator accounts can receive staff roles only.</div>
<form class="form-box" method="post">
    <input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?=e((string)($edit['id']??0))?>">
    <div class="form-grid">
        <div class="form-section-title"><span>👤</span><?=$edit?'Edit Administrator':'Add Staff Administrator'?></div>
        <div class="field"><label>Name</label><input name="name" required value="<?=e($edit['name']??'')?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" required value="<?=e($edit['email']??'')?>"></div>
        <?php if ($editingOwner): ?>
            <input type="hidden" name="role_id" value="<?=e((string)$superRoleId)?>">
            <div class="field"><label>Role</label><input value="Super Admin · Protected Owner" readonly></div>
            <input type="hidden" name="published" value="Yes">
            <div class="field"><label>Account Status</label><input value="Active · Protected" readonly></div>
        <?php else: ?>
            <div class="field"><label>Role</label><select name="role_id" required><option value="">Select staff role</option><?php foreach($roles as $r):?><option value="<?=e((string)$r['id'])?>" <?=(int)($edit['role_id']??0)===(int)$r['id']?'selected':''?>><?=e($r['role_name'])?></option><?php endforeach;?></select></div>
            <div class="field"><label>Account Status</label><select name="published"><option value="Yes" <?=($edit['published']??'Yes')==='Yes'?'selected':''?>>Active</option><option value="No" <?=($edit['published']??'')==='No'?'selected':''?>>Inactive</option></select></div>
        <?php endif; ?>
        <div class="field full"><label><?=$edit?'Reset Password (optional)':'Temporary Password'?></label><input type="password" name="new_password" minlength="12" maxlength="128" <?=$edit?'':'required'?>><small class="help">12–128 characters, including a letter and number. Staff must change a temporary password after first login.</small></div>
        <?php if($edit&&($edit['mfa_enabled']??'No')==='Yes'):?><div class="field full"><label><input type="checkbox" name="reset_mfa" value="1"> Reset this administrator's Authenticator MFA</label></div><?php endif;?>
        <div class="field full"><button class="btn btn-primary"><?=$edit?'Update Administrator':'Create Staff Administrator'?></button><?php if($edit):?><a class="btn btn-soft" href="admin-users.php">Cancel</a><?php endif;?></div>
    </div>
</form><br>
<div class="panel-card"><div class="table-wrap"><table><thead><tr><th>Administrator</th><th>Role</th><th>MFA</th><th>Status</th><th>Last Login</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r): $isOwnerRow=(int)$r['id']===$primaryOwnerId; ?>
<tr><td data-label="Administrator"><strong><?=e($r['name'])?></strong><?php if($isOwnerRow):?> <span class="badge badge-yes">Protected Owner</span><?php endif;?><br><span class="help"><?=e($r['email'])?></span></td><td data-label="Role"><?=e($r['role_name']??'Unassigned')?></td><td data-label="MFA"><span class="badge <?=($r['mfa_enabled']??'No')==='Yes'?'badge-yes':'badge-muted'?>"><?=e($r['mfa_enabled']??'No')?></span></td><td data-label="Status"><span class="badge <?=$r['published']==='Yes'?'badge-yes':'badge-no'?>"><?=$r['published']==='Yes'?'Active':'Inactive'?></span></td><td data-label="Last Login"><?=e($r['last_login_at']??'Never')?></td><td data-label="Action"><a class="btn btn-sm btn-soft" href="admin-users.php?edit=<?=e((string)$r['id'])?>"><?=$isOwnerRow?'Profile':'Manage'?></a></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
