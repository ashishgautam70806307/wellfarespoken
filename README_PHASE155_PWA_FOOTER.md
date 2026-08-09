# Phase 155 - PWA Footer Install

Phase 155 is a narrow PWA usability update on top of Phase 154.

## HTML placeholder files
The project contains only empty `index.html` placeholders inside upload/storage directories. They are not frontend pages and should not be converted to PHP. The upload directories deliberately disable PHP/script execution, while root/upload `.htaccess` rules disable directory indexes. Keeping the empty HTML files is harmless defense-in-depth and avoids creating executable files inside upload locations.

## PWA install footer
The public footer now contains a compact **Install Well Fare App** block using the existing PWA infrastructure (`manifest.webmanifest`, `sw.js`, and `assets/js/main.js`).

- Chrome/Edge/Android: uses `beforeinstallprompt` when available.
- iPhone/iPad: shows the Safari `Share -> Add to Home Screen` instruction.
- Already installed: shows Installed state.
- Non-HTTPS production: explains that HTTPS is required.
- No APK is downloaded; this installs the PWA from the browser.

No database schema or business logic changed.
