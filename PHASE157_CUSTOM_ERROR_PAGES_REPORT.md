# Phase 157 — Branded Error Pages

## Scope
A focused error-handling pass on top of Phase 156. No database schema, learning logic, payments, roles, tests, PWA install flow, or unrelated UI was changed.

## Added branded error pages
Standalone Well Fare English Spoken pages now exist for:

- 400 Bad Request
- 401 Sign in required
- 403 Forbidden
- 404 Not Found
- 405 Method Not Allowed
- 408 Request Timeout
- 409 Conflict
- 410 Gone
- 413 Payload Too Large
- 414 URI Too Long
- 415 Unsupported Media Type
- 419 Session Expired (application-level helper page)
- 422 Unprocessable Content
- 429 Too Many Requests
- 500 Internal Server Error
- 501 Not Implemented
- 502 Bad Gateway
- 503 Service Unavailable
- 504 Gateway Timeout

Each status has its own PHP entry page under `errors/`, while a single database-independent template keeps the visual design consistent and maintainable.

## Design
- Existing Well Fare logo and favicon
- Navy / gold / white brand palette
- Responsive desktop/tablet/mobile card layout
- Status-specific heading and help text
- Back to Home, Go Back, and direct Call Institute actions
- No external font/CDN dependency
- `noindex`, `noarchive`, and `no-store` response headers

The template intentionally does not load the database or the normal site header/footer, so a database failure can still display a branded 500 page.

## Server routing
Root `.htaccess` now maps Apache/LiteSpeed error responses to the custom pages for supported server status codes. A rewrite fallback sends unknown URLs to the custom 404 page and protected file requests to the branded 403 page.

`.well-known/acme-challenge/` is explicitly exempted from dot-path blocking so SSL certificate validation is not broken.

HTTP 419 is not a standard Apache ErrorDocument status and therefore is not registered in `.htaccess`; the dedicated 419 page remains available for application-level session-expired handling.

## Application integration
Branded HTML errors are also used for relevant application-generated errors in:

- `faculty-profile.php`
- `weekly-result.php`
- `weekly-exam-room.php`
- Admin permission denials from `includes/phase148_backend.php`
- Database failures for normal browser pages via `includes/db.php`

API/AJAX database failures remain machine-readable JSON instead of being replaced with HTML.

## Hosting note
The live site is served from the domain root, so `/errors/<code>.php` ErrorDocument routes are correct for Hostinger/LiteSpeed. 404/protected-route rewrites also work when the project is installed in a local subdirectory such as `/spoken/`.

Errors generated outside the application web server (for example some CDN/proxy-level 502/504 failures) can still be replaced by a hosting-provider page because they occur before `.htaccess` is reached.

## Validation
- All PHP files syntax checked
- All JavaScript files syntax checked
- 19 branded error pages returned their intended HTTP status in direct HTTP smoke tests
- Well Fare branding present in all error pages
- `.htaccess` parsed successfully in an Apache syntax test with rewrite/header/deflate/expires modules loaded
- Production ErrorDocument directives contain only Apache-supported status codes
- 403/404/500 application integration markers verified
