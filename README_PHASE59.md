# Phase 59 - PWA install fix

Fixes:
- Added real 192x192 and 512x512 PWA icons.
- Added maskable icons with safe padding.
- Rebuilt manifest with correct display, scope, start_url, id, theme color.
- Updated service worker cache logic so failed assets do not break installability.
- Updated install button script with clearer ready/installed states.
- Added mobile web app meta tags and apple touch icon.

Testing:
1. Replace files.
2. Open site on localhost or HTTPS.
3. Open Chrome DevTools > Application > Manifest and check installability.
4. Clear old service worker/cache once if Chrome cached old manifest.
