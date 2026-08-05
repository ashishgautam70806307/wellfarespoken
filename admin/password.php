<?php require_once __DIR__ . '/_header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM admins WHERE id=? LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($current, $admin['password_hash'])) {
        flash('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 8) {
        flash('error', 'New password must be at least 8 characters.');
    } elseif ($new !== $confirm) {
        flash('error', 'New password and confirm password do not match.');
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $up = db()->prepare('UPDATE admins SET password_hash=? WHERE id=?');
        $up->execute([$hash, (int)$_SESSION['admin_id']]);
        flash('success', 'Admin password updated successfully.');
    }
    redirect('password.php');
}
?>
<div class="admin-top"><div><h1>Change Password</h1><p>Keep the admin panel secure by changing the default password before live use.</p></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<form class="form-box secure-form" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
        <div class="form-section-title"><span>🔐</span>Password Security</div>
        <div class="field full"><label>Current Password</label><input type="password" name="current_password" required></div>
        <div class="field"><label>New Password</label><input type="password" name="new_password" required><small class="help">Minimum 8 characters. Use a strong password.</small></div>
        <div class="field"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
        <div class="field full"><button class="btn btn-primary">Update Password</button></div>
    </div>
</form>
<?php require_once __DIR__ . '/_footer.php'; ?>
