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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<?php if($siteFavicon !== ''): ?><link rel="icon" href="../<?= e($siteFavicon) ?>"><?php else: ?><link rel="icon" href="../assets/uploads/brand/wf-favicon.ico"><?php endif; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
<link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/style.css'))) ?>">
<link rel="stylesheet" href="../<?= e(app_asset_versioned(app_css_asset_path('assets/css/phase123-ui-core.css'))) ?>">
</head>
<body class="login-page admin-login-v2">
<main class="login-shell-v2">
  <section class="login-info-panel">
    <div class="login-brand-lock">
      <?php if($siteLogo !== ''): ?><img src="../<?= e($siteLogo) ?>" alt="<?= e($siteName) ?>"><?php else: ?><span><?= e($brandShort) ?></span><?php endif; ?>
      <div><b><?= e($siteName) ?></b><small>Institute Security Portal</small></div>
    </div>
    <h1>Welcome to your institute control panel.</h1>
    <p><?= e($loginSubtitle) ?></p>
    <div class="login-admin-tools">
      <span><i class="fa-solid fa-inbox" aria-hidden="true"></i> Enquiries</span>
      <span><i class="fa-solid fa-book-open" aria-hidden="true"></i> Courses</span>
      <span><i class="fa-solid fa-route" aria-hidden="true"></i> Learning Roadmap</span>
      <span><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Weekly Tests</span>
      <span><i class="fa-solid fa-star" aria-hidden="true"></i> Reviews</span>
      <span><i class="fa-solid fa-gears" aria-hidden="true"></i> Website Settings</span>
    </div>
  </section>
  <form class="login-card premium-login login-card-v2" method="post" autocomplete="on" novalidate>
    <div class="login-form-top">
      <span class="login-mark">
        <?php if($siteLogo !== ''): ?><img src="../<?= e($siteLogo) ?>" alt="Logo"><?php else: ?><?= e($brandShort) ?><?php endif; ?>
      </span>
      <div>
        <h2>Institute Login</h2>
        <p>Enter authorised institute credentials to continue.</p>
      </div>
    </div>
    <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label class="field">Email Address
      <input type="email" name="email" placeholder="admin@example.com" autocomplete="username" inputmode="email" required autofocus>
    </label>
    <label class="field password-field">Password
      <div class="password-wrap">
        <input id="adminPassword" type="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
        <button type="button" id="togglePassword" class="eye-toggle" aria-label="Show password">
          <i class="fa-solid fa-eye" aria-hidden="true"></i>
        </button>
      </div>
      <small id="capsHint" class="caps-hint">Caps Lock is on</small>
    </label>
    <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
    <button class="btn btn-primary btn-full" type="submit"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Institute Login</button>
    <div class="login-mini-row">
      <small>Attempts left: <?= e((string)$failedLeft) ?></small>
      <small>Use trusted device only</small>
    </div>
  </form>
</main>
<script>
(function(){
 const p=document.getElementById('adminPassword'), t=document.getElementById('togglePassword'), c=document.getElementById('capsHint');
 if(t&&p){t.onclick=function(){const show=p.type==='password';p.type=show?'text':'password';t.classList.toggle('is-visible',show);t.setAttribute('aria-label',show?'Hide password':'Show password');const i=t.querySelector('i');if(i){i.className=show?'fa-solid fa-eye-slash':'fa-solid fa-eye';}};}
 if(p&&c){p.addEventListener('keyup',function(e){c.style.display=e.getModifierState&&e.getModifierState('CapsLock')?'block':'none';});}
})();
</script>
</body>
</html>
