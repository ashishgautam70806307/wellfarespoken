# Well Fare English Spoken — Phase 151 Security / Upload / Hero Report

## Scope

Phase 151 is intentionally narrow and is built on `spoken_phase150_access_control_fix.zip`.

Only these areas were changed:

1. Security review focused on code-execution/file-upload attack paths, dangerous PHP execution primitives, CSRF/RBAC regression tests and obvious upload loops.
2. Every Admin **image upload** now permits only JPG, JPEG, PNG and WEBP.
3. Hero Banner management now supports image-only, text-only, or image + text, with no individually mandatory banner field.
4. Hero overlay darkness can be set to a true `0%` and the frontend respects it.

No database schema, student workflow, weekly-test scoring, roadmap progression, payment, RBAC model, admissions lifecycle or other business logic was changed.

---

## 1. Image upload hardening

The central `secure_image_upload()` pipeline now performs layered server-side validation:

- accepts only `jpg`, `jpeg`, `png`, `webp` filename extensions;
- explicitly rejects executable/dangerous filename patterns such as `.php`, `.phtml`, `.phar`, `.cgi`, `.pl`, `.py`, `.sh`, `.jsp`, `.asp`, `.aspx`, `.shtml`, `.htaccess`, including double-extension patterns such as `shell.php.jpg`;
- checks that PHP received a real HTTP upload with `is_uploaded_file()`;
- checks upload size;
- validates MIME using Fileinfo;
- verifies extension and MIME agree;
- decodes image dimensions and rejects invalid/damaged files;
- caps extreme width/height/pixel counts to reduce decompression-bomb risk;
- validates the image signature with `exif_imagetype()` when available;
- generates a random server filename instead of preserving the user filename;
- when GD is available, decodes and re-encodes the image before saving, which strips trailing/polyglot data;
- upload folders disable directory listing and CGI/script execution and explicitly block server-side script extensions;
- upload responses retain `nosniff` / sandbox-style security headers where applicable.

### Admin image forms covered

- Admissions student photo
- Courses image
- Faculty photo
- Testimonials/review photo
- Gallery image
- Site logo
- Site favicon
- Director photo
- Hero desktop image
- Hero mobile image
- Hero fallback image

All image pickers now show only JPG/JPEG/PNG/WEBP. GIF, SVG, ICO and generic `image/*` upload acceptance were removed.

### HTTP upload smoke test

A temporary local PHP upload endpoint was used to exercise the real multipart upload path:

- valid JPG: accepted
- valid WEBP: accepted
- `shell.php`: rejected
- PHP/text content renamed to `.jpg`: rejected
- valid JPEG named `shell.php.jpg`: rejected
- `.gif`: rejected

The temporary endpoint and test uploads were deleted after the test.

---

## 2. Faculty upload duplication removed

`admin/faculty.php` previously had its own image-upload implementation. It now calls the same central `secure_image_upload()` function as the other Admin image forms, so upload rules cannot silently drift between modules.

---

## 3. Hero Banner business logic

`admin/hero-banners.php` now supports three valid modes without requiring a specific field:

### Image only

Admin can upload only desktop/mobile/fallback artwork and save. If there is no visible text content, the backend automatically stores it as image-only so an unnecessary dark overlay/text container is not applied.

### Text only

Admin can leave all banner images blank and save useful hero text/buttons. The homepage renders a premium navy/blue text-only hero rather than discarding the row.

### Image + text

Admin can upload artwork and optionally add eyebrow/title/subtitle/buttons. The existing Show Content and text-position behavior remains available.

A completely blank record is still rejected because it would create an invisible slider item; this is a record-level sanity rule, not an individually required form field.

---

## 4. Overlay darkness 0%

The Admin range now supports `0–85` instead of `15–85`.

Both backend validation and the homepage renderer clamp the minimum to `0`, and the homepage CSS now uses the dynamic overlay variable in desktop/tablet/mobile rules. Therefore `0%` no longer gets silently turned back into a dark overlay by older CSS.

---

## 5. Security review results

### Passed static checks

- Phase 148 security suite: PASS
- Phase 149 resilience suite: PASS
- Phase 150 access-control suite: PASS
- Phase 151 upload/hero suite: PASS
- No direct `eval()`, `shell_exec()`, `system()`, `passthru()`, `proc_open()` or `popen()` call found in project PHP.
- No variable/dynamic PHP include pattern found in the focused scan.
- Existing Admin RBAC owner-lock behavior remains unchanged.
- Existing CSRF protections remain unchanged; the previous security suite continues to pass.
- Only two `move_uploaded_file()` call sites remain: the hardened central image uploader and private learning-material uploader.
- Private learning files continue to be served through the authenticated `material-file.php` gate.

### Review note intentionally not changed in this phase

The Admin CSV/XLSX import modules are separate from image upload. Their extensions are restricted and they are Admin-only, but the custom XLSX fallback parser can inflate ZIP entries and does not currently impose a dedicated uncompressed-entry ceiling. This is not an image RCE path, but a deliberately malicious spreadsheet could be used for resource exhaustion by an already-authorized Admin. It was documented rather than changed because Phase 151 was requested to avoid unrelated backend behavior changes.

---

## 6. Validation

- PHP files linted: 80 — PASS
- JavaScript files checked: 15 — PASS
- CSS files parsed: 68 — PASS / zero parser errors
- Service Worker precache assets: 47 — all present
- Phase 148 static suite: PASS
- Phase 149 static suite: PASS
- Phase 150 static suite: PASS
- Phase 151 static suite: PASS
- Service Worker cache: `wellfare-spoken-static-v151`
- Database schema changes: **none**

### Runtime limitation

A complete real-world penetration test cannot be proven by static code review alone. Production/staging should still test real Apache/PHP upload behavior, role sessions, malicious multipart requests and hosting-specific MIME/handler configuration.
