<?php
if (!defined('WF_ERROR_PAGE')) {
    http_response_code(404);
    exit;
}

$code = isset($wfError['code']) ? (int)$wfError['code'] : 500;
$title = (string)($wfError['title'] ?? 'Something went wrong');
$message = (string)($wfError['message'] ?? 'We could not complete this request.');
$hint = (string)($wfError['hint'] ?? 'You can return to the home page or try the previous page again.');
$primaryLabel = (string)($wfError['primary_label'] ?? 'Back to Home');
$secondaryLabel = (string)($wfError['secondary_label'] ?? 'Go Back');

http_response_code($code);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
header('X-Content-Type-Options: nosniff', true);

$appBase = '';
$documentRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
$projectRoot = realpath(__DIR__ . '/..');
if ($documentRoot && $projectRoot) {
    $doc = rtrim(str_replace('\\', '/', $documentRoot), '/');
    $project = rtrim(str_replace('\\', '/', $projectRoot), '/');
    if ($doc !== '' && str_starts_with(strtolower($project), strtolower($doc))) {
        $relative = trim(substr($project, strlen($doc)), '/');
        $appBase = $relative !== '' ? '/' . $relative : '';
    }
}
if ($appBase === '') {
    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/errors/' . $code . '.php'));
    $errorPos = strrpos($scriptName, '/errors/');
    if ($errorPos !== false) $appBase = rtrim(substr($scriptName, 0, $errorPos), '/');
}
$homeUrl = ($appBase !== '' ? $appBase : '') . '/';
$contactUrl = ($appBase !== '' ? $appBase : '') . '/contact.php';
$supportPhoneUrl = 'tel:+919506617831';
$logoUrl = ($appBase !== '' ? $appBase : '') . '/assets/uploads/brand/logo_20260708_164300_66b228d8.png';
$faviconUrl = ($appBase !== '' ? $appBase : '') . '/assets/uploads/brand/favicon_20260708_164300_b0f0d6ff.png';

function wf_error_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow,noarchive">
<title><?= wf_error_e((string)$code . ' — ' . $title . ' | Well Fare English Spoken') ?></title>
<link rel="icon" href="<?= wf_error_e($faviconUrl) ?>" type="image/png">
<style>
:root{--wf-navy:#082a59;--wf-navy-2:#123f7a;--wf-ink:#071b37;--wf-gold:#e3ad24;--wf-gold-soft:#fff4cf;--wf-white:#fff;--wf-muted:#66758b;--wf-line:#dfe8f3;--wf-bg:#f4f8fd;--wf-shadow:0 30px 80px rgba(7,27,55,.17)}
*{box-sizing:border-box}html,body{min-height:100%;margin:0}body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:radial-gradient(circle at 12% 15%,rgba(227,173,36,.18),transparent 26rem),radial-gradient(circle at 90% 88%,rgba(18,63,122,.15),transparent 30rem),linear-gradient(135deg,#f9fbff 0%,#eef5fd 100%);color:var(--wf-ink);display:grid;place-items:center;padding:clamp(18px,4vw,48px);overflow-x:hidden}
.wf-error-shell{width:min(1080px,100%);position:relative}.wf-error-card{display:grid;grid-template-columns:minmax(280px,.86fr) minmax(0,1.4fr);min-height:560px;background:rgba(255,255,255,.96);border:1px solid rgba(8,42,89,.10);border-radius:32px;box-shadow:var(--wf-shadow);overflow:hidden}.wf-error-brand{position:relative;padding:clamp(30px,4vw,56px);color:#fff;background:linear-gradient(150deg,#061d3e 0%,#0b356d 58%,#174f92 100%);display:flex;flex-direction:column;justify-content:space-between;isolation:isolate}.wf-error-brand:before,.wf-error-brand:after{content:"";position:absolute;border-radius:50%;border:42px solid rgba(255,255,255,.08);z-index:-1}.wf-error-brand:before{width:300px;height:300px;right:-165px;top:-135px}.wf-error-brand:after{width:230px;height:230px;left:-130px;bottom:-125px;border-color:rgba(227,173,36,.16)}.wf-brand-lockup{display:flex;align-items:center;gap:14px}.wf-brand-logo{width:156px;max-width:62%;height:auto;display:block;filter:drop-shadow(0 8px 18px rgba(0,0,0,.12))}.wf-code-wrap{margin-top:44px}.wf-code-label{font-size:.78rem;letter-spacing:.18em;text-transform:uppercase;color:#ffda68;font-weight:800}.wf-code{font-size:clamp(5rem,12vw,9rem);line-height:.82;font-weight:900;letter-spacing:-.07em;margin:12px 0 16px;color:#fff}.wf-brand-note{max-width:280px;color:#d9e7f8;line-height:1.7;font-size:.95rem}.wf-error-content{padding:clamp(34px,6vw,76px);display:flex;flex-direction:column;justify-content:center}.wf-kicker{display:inline-flex;align-items:center;gap:8px;width:max-content;max-width:100%;padding:8px 12px;border-radius:999px;background:var(--wf-gold-soft);color:#8d6300;font-weight:800;font-size:.74rem;text-transform:uppercase;letter-spacing:.1em}.wf-kicker-dot{width:8px;height:8px;border-radius:50%;background:var(--wf-gold)}h1{font-size:clamp(2rem,5vw,4.25rem);line-height:1.04;letter-spacing:-.045em;margin:22px 0 18px;max-width:760px}.wf-message{font-size:clamp(1rem,1.7vw,1.18rem);line-height:1.75;color:#44536a;max-width:700px;margin:0}.wf-hint{margin:24px 0 0;padding:16px 18px;border-left:4px solid var(--wf-gold);background:#f8fbff;border-radius:0 14px 14px 0;color:#5b687a;line-height:1.6}.wf-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:34px}.wf-btn{min-height:48px;padding:12px 20px;border-radius:14px;text-decoration:none;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:9px;border:1px solid transparent;transition:transform .18s ease,box-shadow .18s ease,background .18s ease}.wf-btn:hover{transform:translateY(-1px)}.wf-btn-primary{background:linear-gradient(135deg,var(--wf-navy),var(--wf-navy-2));color:#fff;box-shadow:0 12px 28px rgba(8,42,89,.18)}.wf-btn-secondary{background:#fff;color:var(--wf-navy);border-color:#cfdbea}.wf-btn-link{color:#7d5c09;background:#fff8e4;border-color:#eedb9f}.wf-footer-note{margin-top:34px;padding-top:22px;border-top:1px solid var(--wf-line);font-size:.86rem;color:var(--wf-muted);display:flex;flex-wrap:wrap;gap:8px 18px;align-items:center}.wf-footer-note strong{color:var(--wf-navy)}
@media(max-width:760px){body{padding:14px}.wf-error-card{grid-template-columns:1fr;min-height:0;border-radius:24px}.wf-error-brand{min-height:250px;padding:24px 24px 28px}.wf-brand-logo{width:132px}.wf-code-wrap{margin-top:28px}.wf-code{font-size:4.6rem;margin:10px 0}.wf-brand-note{font-size:.88rem}.wf-error-content{padding:28px 24px 30px}h1{font-size:clamp(1.9rem,9vw,2.8rem);margin-top:18px}.wf-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:26px}.wf-btn{padding:11px 12px;min-width:0}.wf-btn-link{grid-column:1/-1}.wf-footer-note{margin-top:26px}}
@media(max-width:390px){.wf-actions{grid-template-columns:1fr}.wf-btn-link{grid-column:auto}.wf-error-brand{min-height:220px}.wf-code{font-size:4rem}.wf-error-content{padding:24px 20px}}
@media(prefers-reduced-motion:reduce){.wf-btn{transition:none}}
</style>
</head>
<body>
<main class="wf-error-shell" aria-labelledby="wfErrorTitle">
  <section class="wf-error-card">
    <aside class="wf-error-brand" aria-label="Well Fare English Spoken">
      <div class="wf-brand-lockup">
        <img class="wf-brand-logo" src="<?= wf_error_e($logoUrl) ?>" alt="Well Fare English Spoken" onerror="this.style.display='none'">
      </div>
      <div class="wf-code-wrap">
        <div class="wf-code-label">Well Fare English Spoken</div>
        <div class="wf-code" aria-hidden="true"><?= wf_error_e((string)$code) ?></div>
        <p class="wf-brand-note">Speak • Learn • Grow. We keep error pages inside the Well Fare experience so you always have a clear way forward.</p>
      </div>
    </aside>

    <div class="wf-error-content">
      <span class="wf-kicker"><span class="wf-kicker-dot"></span> Error <?= wf_error_e((string)$code) ?></span>
      <h1 id="wfErrorTitle"><?= wf_error_e($title) ?></h1>
      <p class="wf-message"><?= wf_error_e($message) ?></p>
      <div class="wf-hint"><?= wf_error_e($hint) ?></div>

      <div class="wf-actions">
        <a class="wf-btn wf-btn-primary" href="<?= wf_error_e($homeUrl) ?>" aria-label="<?= wf_error_e($primaryLabel) ?>">⌂ <?= wf_error_e($primaryLabel) ?></a>
        <a class="wf-btn wf-btn-secondary" href="javascript:history.back()" aria-label="<?= wf_error_e($secondaryLabel) ?>">← <?= wf_error_e($secondaryLabel) ?></a>
        <a class="wf-btn wf-btn-link" href="<?= wf_error_e($supportPhoneUrl) ?>">Call Institute</a>
      </div>

      <div class="wf-footer-note">
        <span><strong>Well Fare English Spoken</strong></span>
        <span>Station Road, Mariahu, Jaunpur</span>
      </div>
    </div>
  </section>
</main>
</body>
</html>
