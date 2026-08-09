<?php
require_once __DIR__ . '/../includes/functions.php';
private_no_store();
$setupNeeded = admin_setup_needed();
if (is_admin()) { redirect('dashboard.php'); }

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Robots-Tag: noindex, nofollow');
}

$siteName = app_setting('site_name', APP_NAME);
$siteLogo = site_asset_url(app_setting('site_logo', ''));
$siteFavicon = site_asset_url(app_setting('site_favicon', app_setting('site_logo', '')));
$brandShort = app_setting('brand_short', 'WF');
$loginSubtitle = app_setting('admin_login_subtitle', 'Manage enquiries, courses, learning roadmap, weekly tests, reviews and website settings from one clean panel.');
$failedLeft = max(0, 7 - (int)($_SESSION['login_attempts'] ?? 0));
$mfaPendingId = (int)($_SESSION['admin_mfa_pending_id'] ?? 0);
$mfaMode = $mfaPendingId > 0 && (time() - (int)($_SESSION['admin_mfa_pending_at'] ?? 0)) <= 300;
if (isset($_GET['cancel_mfa'])) { unset($_SESSION['admin_mfa_pending_id'], $_SESSION['admin_mfa_pending_at']); redirect('login.php'); }
if (!$mfaMode && $mfaPendingId > 0) unset($_SESSION['admin_mfa_pending_id'], $_SESSION['admin_mfa_pending_at']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($setupNeeded) {
        flash('error', 'No institute administrator account exists yet. Complete the one-time secure owner setup, then return to Institute Login.');
        redirect('login.php');
    }
    $action = (string)($_POST['action'] ?? 'login');
    if ($action === 'verify_mfa' && $mfaMode) {
        $code = trim((string)($_POST['mfa_code'] ?? ''));
        $rateKey = 'admin-mfa:' . $mfaPendingId;
        if (!csrf_validate($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid security token. Refresh the page and try again.');
        } elseif (!security_rate_limit($rateKey, 8, 600)) {
            flash('error', 'Too many verification attempts. Please wait and login again.');
            unset($_SESSION['admin_mfa_pending_id'], $_SESSION['admin_mfa_pending_at']);
        } else {
            $stmt = db()->prepare("SELECT * FROM admins WHERE id=? AND published='Yes' LIMIT 1");
            $stmt->execute([$mfaPendingId]);
            $admin = $stmt->fetch();
            if ($admin && ($admin['mfa_enabled'] ?? 'No') === 'Yes' && !empty($admin['mfa_secret']) && admin_mfa_verify((string)$admin['mfa_secret'], $code)) {
                if (admin_rbac_ready()) {
                    $loginRole = admin_role_key((int)$admin['id']);
                    if (($loginRole === 'super_admin' && !admin_is_primary_owner((int)$admin['id'])) || $loginRole === 'legacy_admin' || $loginRole === '') {
                        admin_audit_log('admin.login_blocked_invalid_role','admin',(int)$admin['id'],'MFA passed but role assignment was invalid/protected.');
                        unset($_SESSION['admin_mfa_pending_id'], $_SESSION['admin_mfa_pending_at']);
                        flash('error','This administrator account has an invalid role assignment. Ask the institute owner to review access control.');
                        redirect('login.php');
                    }
                }
                if (function_exists('admin_clear_stale_owner_password_gate')) admin_clear_stale_owner_password_gate($admin);
                security_rate_limit_clear($rateKey);
                clear_login_attempts();
                admin_session_login($admin);
                db()->prepare('UPDATE admins SET last_login_at=NOW() WHERE id=?')->execute([(int)$admin['id']]);
                admin_audit_log('admin.login_mfa','admin',(int)$admin['id'],'Administrator signed in with password + TOTP.');
                redirect(admin_password_change_required($admin) ? 'password.php?required=1' : 'dashboard.php');
            }
            flash('error', 'The authenticator code is invalid or expired.');
        }
        redirect('login.php?mfa=1');
    }

    $trap = trim((string)($_POST['website'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $rateKey = 'admin-login:' . $email;
    if ($trap !== '') {
        flash('error', 'Invalid login request.');
    } elseif (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token. Refresh the page and try again.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || (string)($_POST['password'] ?? '') === '') {
        flash('error', 'Please enter a valid email address and password.');
    } elseif (!security_rate_limit($rateKey, 7, 900)) {
        flash('error', 'Too many failed attempts. Please try again after 15 minutes.');
    } else {
        $password = (string)$_POST['password'];
        $stmt = db()->prepare('SELECT * FROM admins WHERE email=? AND published=? LIMIT 1');
        $stmt->execute([$email, 'Yes']);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, (string)$admin['password_hash'])) {
            if (admin_rbac_ready()) {
                $loginRole = admin_role_key((int)$admin['id']);
                if ($loginRole === 'super_admin' && !admin_is_primary_owner((int)$admin['id'])) {
                    admin_audit_log('admin.login_blocked_duplicate_super','admin',(int)$admin['id'],'Blocked legacy/duplicate Super Admin login; run Phase 150 owner-lock migration.');
                    flash('error','This administrator account has an invalid privileged role. Ask the institute owner to run the access-control migration.');
                    redirect('login.php');
                }
                if ($loginRole === 'legacy_admin' || $loginRole === '') {
                    flash('error','This administrator account has no active role. Ask the institute owner to review Roles & Permissions.');
                    redirect('login.php');
                }
            }
            if (password_needs_rehash((string)$admin['password_hash'], PASSWORD_DEFAULT)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('UPDATE admins SET password_hash=?, auth_version=auth_version+1 WHERE id=?')->execute([$hash,(int)$admin['id']]);
                $admin['password_hash']=$hash; $admin['auth_version']=(int)($admin['auth_version']??1)+1;
            }
            if (function_exists('admin_clear_stale_owner_password_gate')) admin_clear_stale_owner_password_gate($admin);
            security_rate_limit_clear($rateKey);
            if (($admin['mfa_enabled'] ?? 'No') === 'Yes' && !empty($admin['mfa_secret'])) {
                $_SESSION['admin_mfa_pending_id']=(int)$admin['id']; $_SESSION['admin_mfa_pending_at']=time();
                redirect('login.php?mfa=1');
            }
            clear_login_attempts();
            admin_session_login($admin);
            if (column_exists('admins','last_login_at')) db()->prepare('UPDATE admins SET last_login_at=NOW() WHERE id=?')->execute([(int)$admin['id']]);
            admin_audit_log('admin.login','admin',(int)$admin['id'],'Administrator signed in successfully.');
            redirect(admin_password_change_required($admin) ? 'password.php?required=1' : 'dashboard.php');
        }
        register_failed_login();
        $failedLeft = max(0, 7 - (int)($_SESSION['login_attempts'] ?? 0));
        admin_audit_log('admin.login_failed','admin',null,'Failed login for ' . $email);
        flash('error', $failedLeft > 0 ? 'Invalid login details. Please check and try again.' : 'Too many failed attempts. Please wait 15 minutes.');
    }
    redirect('login.php');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Secure Institute Login | <?= e($siteName) ?></title>
<?php if (defined('APP_REMOTE_FONTS') && APP_REMOTE_FONTS): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<?php endif; ?>
<?php if($siteFavicon !== ''): ?><link rel="icon" href="../<?= e($siteFavicon) ?>"><?php else: ?><link rel="icon" href="../assets/uploads/brand/wf-favicon.ico"><?php endif; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/wf-design-tokens.css'))) ?>">
<link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase146-admin-login.css'))) ?>">
</head>
<body class="admin-login-page">
<main class="admin-login-shell">
  <a class="admin-login-home" href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i><span>Back to website</span></a>

  <section class="admin-login-card" aria-label="Institute administrator login">
    <aside class="admin-login-brand-panel">
      <div class="admin-login-brand-top">
        <span class="admin-login-logo">
          <?php if($siteLogo !== ''): ?><img src="../<?= e($siteLogo) ?>" alt="<?= e($siteName) ?>"><?php else: ?><span><?= e($brandShort) ?></span><?php endif; ?>
        </span>
        <div class="admin-login-brand-name"><b><?= e($siteName) ?></b><small>Institute Control Centre</small></div>
      </div>

      <div class="admin-login-brand-copy">
        <span class="admin-login-brand-kicker"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> One secure workspace</span>
        <h2>Run the institute with clarity.</h2>
        <p><?= e($loginSubtitle) ?></p>
      </div>

      <div class="admin-login-feature-list" aria-label="Control centre highlights">
        <span><i class="fa-solid fa-user-graduate" aria-hidden="true"></i><b>Students</b><small>Admissions & progress</small></span>
        <span><i class="fa-solid fa-route" aria-hidden="true"></i><b>Learning</b><small>Roadmap & practice</small></span>
        <span><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i><b>Tests</b><small>Attempts & results</small></span>
      </div>

      <div class="admin-login-brand-foot"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span>Rate-limited access · No page caching</span></div>
    </aside>

    <form class="admin-login-form-panel" method="post" autocomplete="on" novalidate>
      <?php if ($setupNeeded): ?>
        <div class="admin-login-alert" role="status"><i class="fa-solid fa-user-shield" aria-hidden="true"></i><span><strong>First-time owner setup is pending.</strong> Institute Login will always stay on this login page and will never redirect visitors to setup automatically.
        <?php if ((defined('APP_RUNTIME_ENV') && APP_RUNTIME_ENV !== 'live') || (defined('ADMIN_SETUP_KEY') && trim((string)ADMIN_SETUP_KEY) !== '')): ?>
          <a href="setup.php" style="display:block;margin-top:8px;font-weight:800;color:inherit;text-decoration:underline">Open one-time owner setup</a>
        <?php else: ?>
          <small style="display:block;margin-top:8px">For a brand-new live install only, set <code>ADMIN_SETUP_KEY</code> in the server <code>.env</code>, open <code>/admin/setup.php</code> once, create the owner, then remove/rotate the key.</small>
        <?php endif; ?>
        </span></div>
      <?php endif; ?>
      <header class="admin-login-form-head">
        <span class="admin-login-secure-badge"><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure access</span>
        <span class="admin-login-kicker"><?= $mfaMode ? 'Second security step' : 'Authorised staff only' ?></span>
        <h1><?= $mfaMode ? 'Verify Authenticator' : 'Welcome back' ?></h1>
        <p><?= $mfaMode ? 'Enter the 6-digit code from your authenticator app.' : 'Enter your institute credentials to continue.' ?></p>
      </header>

      <?php if ($msg = flash('error')): ?>
        <div class="admin-login-alert" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span><?= e($msg) ?></span></div>
      <?php endif; ?>

      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <?php if ($mfaMode): ?>
        <input type="hidden" name="action" value="verify_mfa">
        <label class="admin-login-field" for="adminMfaCode"><span>Authenticator code</span><input id="adminMfaCode" type="text" name="mfa_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" autocomplete="one-time-code" required autofocus></label>
        <button class="admin-login-submit" type="submit"><span>Verify & Continue</span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></button>
        <div class="admin-login-meta"><a href="login.php?cancel_mfa=1" onclick="sessionStorage.clear()"><span><i class="fa-solid fa-arrow-left"></i>Use another account</span></a><span><i class="fa-solid fa-clock"></i>Code changes every 30 sec</span></div>
      <?php else: ?>
        <input type="hidden" name="action" value="login">
        <label class="admin-login-field" for="adminEmail"><span>Email address</span><input id="adminEmail" type="email" name="email" value="<?= e((string)($_POST['email'] ?? '')) ?>" placeholder="Enter authorised email" autocomplete="username" inputmode="email" required autofocus></label>
        <label class="admin-login-field" for="adminPassword"><span>Password</span><span class="admin-login-password-wrap"><input id="adminPassword" type="password" name="password" placeholder="Enter password" autocomplete="current-password" required><button type="button" id="togglePassword" class="admin-login-eye" aria-label="Show password"><i class="fa-solid fa-eye" aria-hidden="true"></i></button></span><small id="capsHint" class="admin-login-caps">Caps Lock is on</small></label>
        <input class="admin-login-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button class="admin-login-submit" type="submit" <?= $setupNeeded ? 'disabled aria-disabled="true"' : '' ?>><span>Enter Control Centre</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        <div class="admin-login-meta"><span><i class="fa-solid fa-shield" aria-hidden="true"></i><?= e((string)$failedLeft) ?> attempts remaining</span><span><i class="fa-solid fa-laptop" aria-hidden="true"></i>Trusted device only</span></div>
      <?php endif; ?>

      <div class="admin-login-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span>Always sign out after completing institute work.</span></div>
    </form>
  </section>
</main>
<script>
(function(){
 const p=document.getElementById('adminPassword'), t=document.getElementById('togglePassword'), c=document.getElementById('capsHint');
 if(t&&p){t.addEventListener('click',function(){const show=p.type==='password';p.type=show?'text':'password';t.classList.toggle('is-visible',show);t.setAttribute('aria-label',show?'Hide password':'Show password');const i=t.querySelector('i');if(i){i.className=show?'fa-solid fa-eye-slash':'fa-solid fa-eye';}});}
 if(p&&c){['keyup','keydown'].forEach(function(type){p.addEventListener(type,function(e){c.style.display=e.getModifierState&&e.getModifierState('CapsLock')?'block':'none';});});}
})();
</script>
</body>
</html>
