# Phase 61 - Live server PWA install fix

Changes:
- Manifest id/scope/start_url changed to relative paths for localhost, subfolder, and live server.
- Added .htaccess MIME headers for manifest.webmanifest and sw.js.
- Service worker registration now uses ./sw.js with ./ scope.
- Install button message updated for live server diagnostics.
- Added pwa-check.php to test HTTPS, manifest, service worker fetch and registration.

After live upload:
1. Open https://your-domain/path/pwa-check.php
2. Check all rows green.
3. Clear Chrome site data once.
4. Reload index.php and click Install Web App.
