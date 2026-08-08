# Phase 151 — Security Upload + Hero Flexibility

Built cumulatively on Phase 150.

Main changes:

- all Admin image uploads now accept only JPG/JPEG/PNG/WEBP with central server-side allowlist, MIME/extension/signature/dimension checks, randomized names and non-executable upload directories;
- Faculty image upload now uses the central secure uploader;
- Hero Banners support image-only, text-only, or combined content without an individually required banner field;
- image-only is auto-detected when only artwork is supplied;
- Overlay Darkness supports true 0%;
- homepage renderer supports text-only hero records and honours 0% overlay on responsive breakpoints;
- no database schema or unrelated business workflow changes.

See `PHASE151_SECURITY_UPLOAD_HERO_REPORT.md` and `PHASE151_BROWSER_SECURITY_CHECKLIST.md`.
