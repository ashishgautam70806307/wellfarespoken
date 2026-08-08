<?php
require_once __DIR__ . '/../includes/functions.php';
private_no_store();
if (!admin_setup_needed()) redirect('login.php');
$pageError='';
$liveNeedsKey = defined('APP_RUNTIME_ENV') && APP_RUNTIME_ENV === 'live';
if (!admin_rbac_ready()) $pageError='Phase 148 database migration is required before first administrator setup.';
if ($liveNeedsKey && (!defined('ADMIN_SETUP_KEY') || trim(ADMIN_SETUP_KEY)==='')) $pageError='ADMIN_SETUP_KEY is missing from the server .env file. Configure a long random setup key before creating the first administrator.';
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name=trim((string)($_POST['name']??'')); $email=strtolower(trim((string)($_POST['email']??''))); $password=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm_password']??''); $setupKey=(string)($_POST['setup_key']??'');
    if($pageError!=='')$errors[]=$pageError;
    elseif(!csrf_validate($_POST['csrf_token']??''))$errors[]='Security token expired. Refresh and try again.';
    elseif(!security_rate_limit('admin-setup:'.$email,5,1800))$errors[]='Too many setup attempts. Please wait before trying again.';
    elseif(mb_strlen($name)<2||mb_strlen($name)>100)$errors[]='Enter a valid administrator name.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid administrator email.';
    elseif(($passwordError=admin_password_error($password))!=='')$errors[]=$passwordError;
    elseif(!hash_equals($password,$confirm))$errors[]='Password confirmation does not match.';
    elseif($liveNeedsKey&&!hash_equals((string)ADMIN_SETUP_KEY,$setupKey))$errors[]='The setup key is incorrect.';
    elseif(!admin_setup_needed())$errors[]='An administrator already exists. Setup is closed.';
    else{
        try{
            $role=(int)(db()->query("SELECT id FROM admin_roles WHERE role_key='super_admin' LIMIT 1")->fetchColumn()?:0); if($role<=0)throw new RuntimeException('Super Admin role is missing.');
            $stmt=db()->prepare("INSERT INTO admins (role_id,name,email,password_hash,auth_version,must_change_password,mfa_enabled,password_changed_at,published,created_at) VALUES (?,?,?,?,1,'No','No',NOW(),'Yes',NOW())");
            $stmt->execute([$role,$name,$email,password_hash($password,PASSWORD_DEFAULT)]); $id=(int)db()->lastInsertId();
            $a=db()->prepare('SELECT * FROM admins WHERE id=?');$a->execute([$id]);$admin=$a->fetch(); admin_session_login($admin); security_rate_limit_clear('admin-setup:'.$email); admin_audit_log('admin.first_setup','admin',$id,'First Super Admin created securely.'); redirect('dashboard.php');
        }catch(Throwable $e){error_log('[admin-setup] '.$e->__toString());$errors[]='Administrator account could not be created. Check the database migration and try again.';}
    }
}
$siteName=app_setting('site_name',APP_NAME);$siteLogo=site_asset_url(app_setting('site_logo',''));
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>First Admin Setup | <?=e($siteName)?></title><link rel="stylesheet" href="../<?=e(app_asset_versioned(app_css_asset_path('assets/css/wf-design-tokens.css')))?>"><link rel="stylesheet" href="../<?=e(app_asset_versioned(app_css_asset_path('assets/css/phase146-admin-login.css')))?>"></head>
<body class="admin-login-page"><main class="admin-login-shell"><section class="admin-login-card" style="max-width:920px"><aside class="admin-login-brand-panel"><div class="admin-login-brand-top"><span class="admin-login-logo"><?php if($siteLogo):?><img src="../<?=e($siteLogo)?>" alt="<?=e($siteName)?>"><?php else:?><span>WF</span><?php endif;?></span><div class="admin-login-brand-name"><b><?=e($siteName)?></b><small>Secure first-run setup</small></div></div><div class="admin-login-brand-copy"><span class="admin-login-brand-kicker">One-time setup</span><h2>Create the institute owner account.</h2><p>No default administrator is shipped in the project. This page closes automatically after the first account is created.</p></div><div class="admin-login-brand-foot"><span>Use a unique 12+ character password and enable Authenticator MFA after login.</span></div></aside>
<form class="admin-login-form-panel" method="post" autocomplete="off"><header class="admin-login-form-head"><span class="admin-login-secure-badge">Secure setup</span><h1>Create Super Admin</h1><p>This account can manage roles, students, payments and system settings.</p></header><?php if($pageError):?><div class="admin-login-alert" role="alert"><span><?=e($pageError)?></span></div><?php endif;?><?php foreach($errors as $err):?><div class="admin-login-alert" role="alert"><span><?=e($err)?></span></div><?php endforeach;?><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label class="admin-login-field"><span>Full name</span><input name="name" required maxlength="100" value="<?=e($_POST['name']??'')?>"></label><label class="admin-login-field"><span>Email address</span><input type="email" name="email" required autocomplete="username" value="<?=e($_POST['email']??'')?>"></label><label class="admin-login-field"><span>Password</span><input type="password" name="password" required minlength="12" maxlength="128" autocomplete="new-password"></label><label class="admin-login-field"><span>Confirm password</span><input type="password" name="confirm_password" required minlength="12" maxlength="128" autocomplete="new-password"></label><?php if($liveNeedsKey):?><label class="admin-login-field"><span>Server setup key</span><input type="password" name="setup_key" required autocomplete="off"></label><?php endif;?><button class="admin-login-submit" type="submit" <?=$pageError!==''?'disabled':''?>><span>Create secure admin</span></button><div class="admin-login-note"><span>After setup, rotate/remove ADMIN_SETUP_KEY from the server environment.</span></div></form></section></main></body></html>
