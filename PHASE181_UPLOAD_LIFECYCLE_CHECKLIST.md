# Phase 181 Upload Lifecycle Checklist

## Replace tests
- Course: upload A, edit and upload B -> B displays; A is removed when unreferenced.
- Faculty: upload A, replace B -> B displays; A removed when unreferenced.
- Gallery: upload A, replace B -> B displays; A removed when unreferenced.
- Review: upload A, replace B -> B displays; A removed when unreferenced.
- Logo/Favicon/Director: replace each -> old generated upload removed when unreferenced.
- Hero: replace fallback/desktop/mobile independently -> old variant removed only when no other field uses it.
- Admission: replace student photo -> old generated upload removed when unreferenced.

## Explicit remove tests
- Course/Faculty/Gallery/Review/Admission remove checkbox clears current image.
- Settings remove checkbox clears logo/favicon/director photo.
- Hero remove controls clear custom fallback/desktop/mobile variants according to fallback rules.

## Delete tests
- Course hard delete removes image after DB delete.
- Faculty single and bulk delete remove unreferenced photos.
- Gallery hard delete removes unreferenced image.
- Review hard delete removes unreferenced photo.
- Admission soft delete retains photo intentionally.
- Hero unpublish retains images intentionally.

## Safety tests
- Same physical image referenced by two records -> deleting/replacing one must NOT remove file.
- Remote HTTPS image URL -> never unlinked locally.
- Static/default asset -> never auto-deleted.
- System Check cleanup -> only generated, 48+ hour, unreferenced files are candidates.
- DB reference check failure -> no physical deletion.
