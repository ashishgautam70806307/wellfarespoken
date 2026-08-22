<?php
require_once __DIR__ . '/_header.php';
if (!admin_rbac_ready()) {
    ?><div class="panel-card wf149-db-required"><h2>Roles & Permissions need the Phase 148 database migration.</h2><p>Import <code>sql/phase148_critical_backend_hardening.sql</code> first.</p><div class="admin-actions"><a class="btn btn-primary" href="system-check.php">Open System Check</a><a class="btn btn-soft" href="dashboard.php">Back to Dashboard</a></div></div><?php
    require_once __DIR__ . '/_footer.php'; exit;
}
admin_assert_primary_owner();

$permissions = db()->query('SELECT * FROM admin_permissions ORDER BY permission_group,permission_label')->fetchAll();
$permissionById = [];
foreach ($permissions as $permission) $permissionById[(int)$permission['id']] = (string)$permission['permission_key'];
$ownerOnlyKeys = ['admins.manage'];

$edit = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT * FROM admin_roles WHERE id=?');
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch() ?: null;
    if ($edit && ($edit['role_key'] ?? '') === 'super_admin') {
        flash('error', 'Super Admin is a protected system owner role. Its permissions cannot be edited.');
        redirect('roles.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error','Security token expired. Refresh and try again.');
        redirect('roles.php');
    }
    $id = (int)($_POST['id'] ?? 0);
    try {
        $name = trim((string)($_POST['role_name'] ?? ''));
        $key = strtolower(trim((string)($_POST['role_key'] ?? '')));
        $key = preg_replace('/[^a-z0-9_]+/', '_', $key) ?: '';
        $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
        if (mb_strlen($name) < 2) throw new RuntimeException('Role name is required.');

        if ($id > 0) {
            $q = db()->prepare('SELECT * FROM admin_roles WHERE id=?');
            $q->execute([$id]);
            $role = $q->fetch();
            if (!$role) throw new RuntimeException('Role not found.');
            $key = (string)$role['role_key'];
            if ($key === 'super_admin') throw new RuntimeException('Super Admin is a protected owner role and cannot be edited.');
            db()->prepare('UPDATE admin_roles SET role_name=?,published=? WHERE id=?')->execute([$name,$published,$id]);
        } else {
            if ($key === '' || in_array($key, ['super_admin','owner','root','master_admin'], true)) throw new RuntimeException('Choose a unique staff role key. Protected owner role names are reserved.');
            db()->prepare("INSERT INTO admin_roles(role_name,role_key,is_system,published) VALUES (?,?,'No',?)")->execute([$name,$key,$published]);
            $id = (int)db()->lastInsertId();
        }

        $allowed = array_values(array_unique(array_map('intval', (array)($_POST['permission_ids'] ?? []))));
        $allowed = array_values(array_filter($allowed, static function(int $pid) use ($permissionById,$ownerOnlyKeys): bool {
            if ($pid <= 0 || !isset($permissionById[$pid])) return false;
            return !in_array($permissionById[$pid], $ownerOnlyKeys, true);
        }));

        db()->beginTransaction();
        try {
            db()->prepare('DELETE FROM admin_role_permissions WHERE role_id=?')->execute([$id]);
            $ins = db()->prepare('INSERT IGNORE INTO admin_role_permissions(role_id,permission_id) VALUES (?,?)');
            foreach ($allowed as $pid) $ins->execute([$id,$pid]);
            db()->commit();
        } catch (Throwable $e) {
            if (db()->inTransaction()) db()->rollBack();
            throw $e;
        }

        // Role/permission changes invalidate affected staff sessions immediately.
        $affected = db()->prepare('SELECT id FROM admins WHERE role_id=?');
        $affected->execute([$id]);
        foreach ($affected->fetchAll(PDO::FETCH_COLUMN) as $adminId) admin_invalidate_sessions((int)$adminId);
        admin_audit_log('role.saved','admin_role',$id,'Staff role permissions updated by protected owner. Owner-only permissions were excluded.');
        flash('success','Role permissions saved. Existing sessions for this role were signed out for security.');
        redirect('roles.php');
    } catch (Throwable $e) {
        error_log('[admin-roles] '.$e->__toString());
        flash('error', ($e instanceof RuntimeException && !($e instanceof PDOException)) ? $e->getMessage() : 'Role settings could not be saved. Check System Check and try again.');
        redirect('roles.php'.($id>0?'?edit='.$id:''));
    }
}

$roles = db()->query("SELECT r.*,COUNT(DISTINCT a.id) admin_count,COUNT(DISTINCT rp.permission_id) permission_count FROM admin_roles r LEFT JOIN admins a ON a.role_id=r.id LEFT JOIN admin_role_permissions rp ON rp.role_id=r.id GROUP BY r.id ORDER BY CASE WHEN r.role_key='super_admin' THEN 0 ELSE 1 END,r.is_system DESC,r.role_name")->fetchAll();
$selected=[];
if($edit){$s=db()->prepare('SELECT permission_id FROM admin_role_permissions WHERE role_id=?');$s->execute([(int)$edit['id']]);$selected=array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));}
?>
<div class="admin-top"><div><h1>Roles & Permissions</h1><p>Staff permissions follow least privilege. Super Admin is a single protected owner and is not editable here.</p></div><a class="btn btn-soft" href="admin-users.php">Administrator Accounts</a></div>
<?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif;?><?php if($m=flash('error')):?><div class="alert alert-danger"><?=e($m)?></div><?php endif;?>
<div class="alert alert-info"><strong>Security rule:</strong> <code>admins.manage</code> is owner-only and cannot be granted to staff/custom roles, even by a forged POST request.</div>
<form class="form-box" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=e((string)($edit['id']??0))?>"><div class="form-grid"><div class="form-section-title"><span>🛡️</span><?=$edit?'Edit Staff Role':'Create Staff Role'?></div><div class="field"><label>Role Name</label><input name="role_name" required value="<?=e($edit['role_name']??'')?>"></div><div class="field"><label>Role Key</label><input name="role_key" <?=$edit?'readonly':''?> required value="<?=e($edit['role_key']??'')?>" placeholder="front_desk"></div><div class="field"><label>Status</label><select name="published"><option value="Yes">Active</option><option value="No" <?=($edit['published']??'Yes')==='No'?'selected':''?>>Inactive</option></select></div><div class="field full"><label>Permissions</label><div class="grid-3">
<?php foreach($permissions as $p): $ownerOnly=in_array((string)$p['permission_key'],$ownerOnlyKeys,true); ?>
<label class="panel-card" style="padding:12px;<?=$ownerOnly?'opacity:.65':''?>"><input type="checkbox" name="permission_ids[]" value="<?=e((string)$p['id'])?>" <?=in_array((int)$p['id'],$selected,true)?'checked':''?> <?=$ownerOnly?'disabled':''?>><strong><?=e($p['permission_label'])?></strong><small class="help"><?=e($p['permission_group'])?> · <?=e($p['permission_key'])?><?=$ownerOnly?' · Owner only':''?></small></label>
<?php endforeach; ?>
</div></div><div class="field full"><button class="btn btn-primary">Save Staff Role</button><?php if($edit):?><a class="btn btn-soft" href="roles.php">Cancel</a><?php endif;?></div></div></form><br>
<div class="panel-card"><div class="table-wrap"><table><thead><tr><th>Role</th><th>Permissions</th><th>Admins</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($roles as $r): $locked=($r['role_key']??'')==='super_admin'; ?><tr><td data-label="Role"><strong><?=e($r['role_name'])?></strong><?php if($locked):?> <span class="badge badge-yes">Protected Owner</span><?php endif;?><br><span class="help"><?=e($r['role_key'])?></span></td><td data-label="Permissions"><?=$locked?'All (locked)':e((string)$r['permission_count'])?></td><td data-label="Admins"><?=e((string)$r['admin_count'])?></td><td data-label="Status"><?=e($r['published'])?></td><td data-label="Action"><?=$locked?'<span class="badge badge-muted">Locked</span>':'<a class="btn btn-sm btn-soft" href="roles.php?edit='.e((string)$r['id']).'">Edit</a>'?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
