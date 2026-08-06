<?php
require_once __DIR__ . '/../includes/functions.php';
private_no_store();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            security_rate_limit_clear($rateKey);
            clear_login_attempts();
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_name'] = (string)$admin['name'];
            $_SESSION['admin_last_activity'] = time();
            redirect('dashboard.php');
        }
        register_failed_login();
        $failedLeft = max(0, 7 - (int)($_SESSION['login_attempts'] ?? 0));
        flash('error', $failedLeft > 0 ? 'Invalid login details. Please check and try again.' : 'Too many failed attempts. Please wait 15 minutes.');
    }
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
<link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase137-admin-login.css'))) ?>">
<link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase138-mobile-ux.css'))) ?>">
</head>
<body class="admin-login-single wf138-admin-login wf138-mobile-ui">
<main class="admin-login-shell">
  <a class="admin-login-home" href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to website</a>

  <form class="admin-login-card admin-login-form" method="post" autocomplete="on" novalidate>
    <header class="admin-login-brand">
      <span class="admin-login-logo">
        <?php if($siteLogo !== ''): ?><img src="../<?= e($siteLogo) ?>" alt="<?= e($siteName) ?>"><?php else: ?><span><?= e($brandShort) ?></span><?php endif; ?>
      </span>
      <div>
        <b><?= e($siteName) ?></b>
        <small>Institute control centre</small>
      </div>
      <span class="admin-login-secure-badge"><i class="fa-solid fa-lock" aria-hidden="true"></i> Secure access</span>
    </header>

    <section class="admin-login-intro">
      <span class="admin-login-kicker"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Authorised staff only</span>
      <h1>Welcome back.</h1>
      <p><?= e($loginSubtitle) ?></p>
    </section>

    <?php if ($msg = flash('error')): ?>
      <div class="admin-login-alert" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span><?= e($msg) ?></span></div>
    <?php endif; ?>

    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <label class="admin-login-field" for="adminEmail">Email address
      <span class="admin-login-control">
        <input id="adminEmail" type="email" name="email" value="<?= e((string)($_POST['email'] ?? '')) ?>" placeholder="Enter authorised email" autocomplete="username" inputmode="email" required autofocus>
      </span>
    </label>

    <label class="admin-login-field" for="adminPassword">Password
      <span class="admin-login-control">
        <input id="adminPassword" type="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
        <button type="button" id="togglePassword" class="admin-login-eye" aria-label="Show password">
          <i class="fa-solid fa-eye" aria-hidden="true"></i>
        </button>
      </span>
      <small id="capsHint" class="admin-login-caps">Caps Lock is on</small>
    </label>

    <input class="admin-login-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

    <button class="admin-login-submit" type="submit"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Enter Control Centre</button>

    <div class="admin-login-meta">
      <span><i class="fa-solid fa-shield" aria-hidden="true"></i> Attempts remaining: <?= e((string)$failedLeft) ?></span>
      <span><i class="fa-solid fa-laptop" aria-hidden="true"></i> Use a trusted device</span>
    </div>

    <div class="admin-login-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span>For your security, this page is not cached. Always sign out after completing institute work.</span></div>
  </form>
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
