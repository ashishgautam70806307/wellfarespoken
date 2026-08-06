# Phase 140 — Premium Contact Page Redesign

Phase 140 is a cumulative visual update built on Phase 139. It changes only the public Contact page and its page-scoped assets; existing student, test, roadmap, admission, admin and database workflows remain unchanged.

## What changed

- Replaced the old generic page hero and three flat contact cards with a unique premium contact experience.
- Added a navy/gold communication hero with direct Call and WhatsApp actions.
- Added a compact contact console for phone, email, address and guidance timing.
- Added three user-intent paths: course guidance, admission and existing-student access.
- Added a visual institute-location card and a four-step counselling flow.
- Added a final compact CTA and dynamic social links when saved in Admin Site Settings.
- Added a dedicated page-scoped stylesheet and production minified copy.
- Updated the service-worker cache namespace to `wellfare-spoken-static-v140`.

## Files

- `contact.php`
- `assets/css/phase140-contact-page.css`
- `assets/css/phase140-contact-page.min.css`
- `sw.js`

## Compatibility

All dynamic settings remain connected: page title, page subtitle, phone, WhatsApp, email, address, map URL, office time and social-media URLs.

After installation, clear the old service worker/site cache and perform a hard refresh.
