<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'PWA Check | ' . app_setting('site_name', APP_NAME);
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="card" style="max-width:900px;margin:auto;">
      <h1>PWA Live Server Check</h1>
      <p>Use this page after uploading to live server. Green status means install setup is okay.</p>
      <div id="pwaCheckBox" style="display:grid;gap:12px;margin-top:20px;"></div>
      <button class="btn btn-primary" type="button" data-install-webapp>Install Web App</button>
      <p class="install-help" data-install-help></p>
    </div>
  </div>
</section>
<script>
(async function(){
  const box = document.getElementById('pwaCheckBox');
  const row = (label, ok, note) => {
    const div = document.createElement('div');
    div.style.cssText = 'padding:12px 14px;border-radius:14px;border:1px solid '+(ok?'#bbf7d0':'#fecaca')+';background:'+(ok?'#f0fdf4':'#fff1f2')+';font-weight:800;';
    div.textContent = (ok?'✅ ':'❌ ') + label + (note ? ' - ' + note : '');
    box.appendChild(div);
  };
  row('Secure context / HTTPS', window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1', location.protocol);
  row('Service Worker supported', 'serviceWorker' in navigator, '');
  try {
    const m = await fetch('./manifest.webmanifest', {cache:'no-store'});
    row('Manifest fetch', m.ok, m.status + ' ' + (m.headers.get('content-type') || ''));
    await m.json();
    row('Manifest JSON valid', true, '');
  } catch(e) { row('Manifest JSON valid', false, e.message); }
  try {
    const s = await fetch('./sw.js', {cache:'no-store'});
    row('Service worker file fetch', s.ok, s.status + ' ' + (s.headers.get('content-type') || ''));
  } catch(e) { row('Service worker file fetch', false, e.message); }
  if ('serviceWorker' in navigator) {
    try {
      const reg = await navigator.serviceWorker.getRegistration('./');
      row('Service worker registered', !!reg, reg ? reg.scope : 'not registered yet');
    } catch(e) { row('Service worker registered', false, e.message); }
  }
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
