# Phase 136 Remaining Limitations

1. The build environment has no `pdo_mysql` PHP driver and no MySQL/MariaDB server. Real database integration could not be executed here.
2. Fixture/browser checks validate templates and interactions with controlled data, not the user's current production records.
3. File upload and admin publishing require a writable localhost/staging filesystem and authenticated admin session.
4. Browser audio/speech behavior depends on browser support, microphone permission, and device policy.
5. Service Worker updates require one cache/site-data clear after deployment when an older worker is still controlling the page.
6. Live HTTPS and certificate configuration are hosting/server responsibilities, not PHP application code.
7. Reverse-proxy header trust must remain disabled unless the deployment actually uses a controlled proxy/CDN.

No new design or feature was added in Phase 136. Remaining items are environment-dependent verification tasks, not hidden design work.
