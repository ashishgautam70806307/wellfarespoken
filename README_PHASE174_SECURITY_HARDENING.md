# Phase 174 - Security Hardening

This phase closes the security gaps found in the Phase 173 production audit while preserving business logic and UI behavior.

## Fixed
- Removed live database credentials from source-code defaults. Live DB values are `.env` only.
- Production PHP error display is forced off; errors remain server-logged.
- Added missing Admin permissions for Practice Lab and Online Classes. Practice Lab settings additionally require `settings.manage`.
- Practice Lab deletes are POST + CSRF; destructive CSRF tokens are no longer placed in URLs.
- OpenAI API key and endpoint are environment-only. Legacy DB API-key values are purged; endpoint must be HTTPS and match `OPENAI_ALLOWED_HOSTS`.
- Added rate limiting to the legacy practice-session answer-check API.
- Added HTTPS-only validation for online-class external links and social URLs, plus safer gallery remote-image handling.
- TOTP MFA secrets are encrypted at rest using AES-256-GCM. `APP_SECRET_KEY` is preferred; a persistent private-storage key file is a compatibility fallback. Encryption refuses an ephemeral/non-persistent key.
- Protected Super Admin MFA can be required with `ADMIN_REQUIRE_OWNER_MFA` (default true on live).
- `/tests` and `/tools` are denied at the web-server level.
- Added HTTPS-only HSTS from application bootstrap while preserving the existing compatibility-safe CSP (strict script/style CSP is deferred until inline assets are consolidated).
- Added Admin System Check security checks.

## Required live-server actions
1. Rotate the previously exposed database password in hosting/MySQL.
2. Put the new DB credentials only in `.env` (`DB_LIVE_*`).
3. Set `APP_SECRET_KEY` to a long random value and keep it stable.
4. Set `SESSION_SECURE_COOKIE=true` on HTTPS production.
5. If AI is used, set `OPENAI_API_KEY` in `.env`; never enter it in Admin UI.
6. Keep `ADMIN_REQUIRE_OWNER_MFA=true` and configure Authenticator MFA for the protected owner.

No database schema change is required.

## Intentionally not combined into this security patch
- Large-scale CSS consolidation and splitting the 5k+ line helper file are maintainability/performance refactors. They are not security hotfixes and should be done in a separate regression-heavy phase so working UI/business logic is not destabilized.
