<?php
require_once __DIR__ . '/_header.php';
$adminId=(int)$_SESSION['admin_id'];
$stmt=db()->prepare('SELECT * FROM admins WHERE id=? LIMIT 1');$stmt->execute([$adminId]);$admin=$stmt->fetch();
if(!$admin){admin_session_logout();redirect('login.php');}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!csrf_validate($_POST['csrf_token']??'')){flash('error','Security token expired.');redirect('password.php');}
    $action=(string)($_POST['action']??'change_password');
    $current=(string)($_POST['current_password']??'');
    if(!password_verify($current,(string)$admin['password_hash'])){flash('error','Current password is incorrect.');redirect('password.php');}
    try{
        if($action==='change_password'){
            $new=(string)($_POST['new_password']??'');$confirm=(string)($_POST['confirm_password']??'');
            $passwordError=admin_password_error($new); if($passwordError!=='') throw new RuntimeException($passwordError);
            if(!hash_equals($new,$confirm))throw new RuntimeException('New password and confirm password do not match.');
            $hash=password_hash($new,PASSWORD_DEFAULT);
            db()->prepare("UPDATE admins SET password_hash=?,auth_version=auth_version+1,must_change_password='No',password_changed_at=NOW() WHERE id=?")->execute([$hash,$adminId]);
            $fresh=db()->prepare('SELECT * FROM admins WHERE id=?');$fresh->execute([$adminId]);admin_session_login($fresh->fetch());admin_audit_log('admin.password_changed','admin',$adminId,'Administrator changed own password.');flash('success','Password updated. Existing sessions were invalidated.');redirect(isset($_GET['required']) ? 'dashboard.php' : 'password.php');
        }
        if($action==='mfa_prepare'){
            $secret=admin_mfa_generate_secret();$_SESSION['admin_mfa_setup_secret']=$secret;flash('success','Authenticator setup key generated. Add it to your authenticator app, then verify one code below.');redirect('password.php#mfa');
        }
        if($action==='mfa_enable'){
            $secret=(string)($_SESSION['admin_mfa_setup_secret']??'');$code=(string)($_POST['mfa_code']??'');if($secret===''||!admin_mfa_verify($secret,$code))throw new RuntimeException('Authenticator code is invalid. Generate/setup the key again if needed.');
            db()->prepare("UPDATE admins SET mfa_secret=?,mfa_enabled='Yes',auth_version=auth_version+1 WHERE id=?")->execute([app_encrypt_secret($secret),$adminId]);unset($_SESSION['admin_mfa_setup_secret']);$fresh=db()->prepare('SELECT * FROM admins WHERE id=?');$fresh->execute([$adminId]);admin_session_login($fresh->fetch());admin_audit_log('admin.mfa_enabled','admin',$adminId,'TOTP MFA enabled.');flash('success','Authenticator MFA is now enabled.');redirect('password.php#mfa');
        }
        if($action==='mfa_disable'){
            if (admin_mfa_is_required_for_owner($adminId)) throw new RuntimeException('Authenticator MFA is required for the protected Super Admin on this environment.');
            $code=(string)($_POST['mfa_code']??'');$storedMfa=admin_mfa_secret_plain((string)($admin['mfa_secret']??''));if(($admin['mfa_enabled']??'No')!=='Yes'||$storedMfa===''||!admin_mfa_verify($storedMfa,$code))throw new RuntimeException('Enter a valid current authenticator code to disable MFA.');
            db()->prepare("UPDATE admins SET mfa_secret=NULL,mfa_enabled='No',auth_version=auth_version+1 WHERE id=?")->execute([$adminId]);$fresh=db()->prepare('SELECT * FROM admins WHERE id=?');$fresh->execute([$adminId]);admin_session_login($fresh->fetch());admin_audit_log('admin.mfa_disabled','admin',$adminId,'TOTP MFA disabled.');flash('success','Authenticator MFA disabled.');redirect('password.php#mfa');
        }
    }catch(Throwable $e){error_log('[admin-account-security] '.$e->__toString());flash('error', ($e instanceof RuntimeException && !($e instanceof PDOException)) ? $e->getMessage() : 'Account security change could not be completed. Please try again.');redirect('password.php'.(str_starts_with($action,'mfa_')?'#mfa':''));}
}
$setupSecret=(string)($_SESSION['admin_mfa_setup_secret']??'');
?>
<div class="admin-top"><div><h1>Account Security</h1><p>Use a unique administrator password and free Authenticator MFA for sensitive institute access.</p></div></div>
<?php if(isset($_GET['required'])):?><div class="alert alert-warning">Your administrator account requires a password change before other modules can be used.</div><?php endif;?>
<?php if(isset($_GET['mfa_required']) || admin_mfa_gate_active()):?><div class="alert alert-warning">For production security, the protected Super Admin must enable Authenticator MFA before other modules can be used.</div><?php endif;?>
<?php if($msg=flash('success')):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?><?php if($msg=flash('error')):?><div class="alert alert-danger"><?=e($msg)?></div><?php endif;?>
<div class="grid-2">
<form class="form-box secure-form" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="change_password"><div class="form-grid"><div class="form-section-title"><span>🔐</span>Change Password</div><div class="field full"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"></div><div class="field"><label>New Password</label><input type="password" name="new_password" minlength="12" maxlength="128" required autocomplete="new-password"><small class="help">12–128 characters; use at least one letter and one number.</small></div><div class="field"><label>Confirm New Password</label><input type="password" name="confirm_password" minlength="12" maxlength="128" required autocomplete="new-password"></div><div class="field full"><button class="btn btn-primary">Update Password & Sign Out Other Sessions</button></div></div></form>
<section class="form-box" id="mfa"><div class="form-grid"><div class="form-section-title"><span>🛡️</span>Authenticator MFA</div><div class="field full"><p class="help">Status: <strong><?=($admin['mfa_enabled']??'No')==='Yes'?'Enabled':'Not enabled'?></strong>. This uses a free authenticator app; no SMS/OTP provider fee is required.</p></div>
<?php if(($admin['mfa_enabled']??'No')==='Yes'):?>
<?php if(admin_mfa_is_required_for_owner($adminId)):?><div class="field full"><div class="alert alert-info">MFA is required for the protected Super Admin and cannot be disabled while ADMIN_REQUIRE_OWNER_MFA is enabled.</div></div><?php else:?><form method="post" class="field full"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="mfa_disable"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"><label>Current 6-digit code</label><input name="mfa_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required><button class="btn btn-danger" type="submit">Disable MFA</button></form><?php endif;?>
<?php else:?>
<?php if($setupSecret===''):?><form method="post" class="field full"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="mfa_prepare"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"><button class="btn btn-soft" type="submit">Generate Authenticator Setup Key</button></form>
<?php else:?><div class="field full"><label>Authenticator setup key</label><input value="<?=e($setupSecret)?>" readonly><small class="help">Add this key manually in Google Authenticator, Microsoft Authenticator, 1Password or another TOTP app.</small><details><summary>Advanced otpauth URI</summary><code style="word-break:break-all"><?=e(admin_mfa_uri((string)$admin['email'],$setupSecret))?></code></details></div><form method="post" class="field full"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="mfa_enable"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"><label>6-digit code from the app</label><input name="mfa_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required><button class="btn btn-primary" type="submit">Verify & Enable MFA</button></form><?php endif;?>
<?php endif;?></div></section>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
