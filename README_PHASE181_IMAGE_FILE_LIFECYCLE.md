# Phase 181 - Whole Project Image/File Lifecycle Hardening

Phase 181 audits every persistent image/file upload flow in the current project and adds safe physical cleanup for replaced, explicitly removed, and hard-deleted managed uploads.

## Covered persistent media fields

- Courses: `courses.course_image`
- Reviews: `testimonials.student_image`
- Faculty: `faculty_members.image_url`
- Gallery: `gallery_images.image_url`
- Hero banners: `hero_banners.image_url`, `desktop_image_url`, `mobile_image_url`
- Admissions: `admissions.student_photo`
- Site settings: logo, favicon, director photo and any matching setting value
- Materials: `material_collections.cover_image`, `material_assets.file_path` are included in reference protection/orphan detection

Temporary CSV/XLSX/TXT uploads used only for imports are not persistent media files and are left to PHP temporary-file cleanup.

## Safe cleanup rules

1. Only app-managed paths under `assets/uploads/` or `private/materials/` are eligible.
2. Remote URLs and traversal paths are never unlinked.
3. Automatic physical deletion is limited to server-generated upload filenames. Static/default assets are not auto-deleted.
4. Before unlinking, the file is checked against all current DB media references.
5. Runtime/static project and canonical SQL references are also protected.
6. Realpath containment is checked before `unlink()` to prevent path escape/symlink mistakes.
7. If DB reference verification fails, cleanup fails closed and leaves the file in place.
8. Failed image re-encoding removes any partial target file.

## Replace/remove/delete behavior

- Course image: replace, explicit remove, hard course delete -> old unreferenced managed image cleaned.
- Faculty photo: replace, explicit remove, single delete, bulk delete -> old unreferenced managed images cleaned.
- Gallery image: replace, explicit remove, hard gallery delete -> old unreferenced managed image cleaned.
- Review photo: replace, explicit remove, hard review delete -> old unreferenced managed image cleaned.
- Site logo/favicon/director photo: upload replacement, manual path change, explicit remove -> old unreferenced managed upload cleaned.
- Hero fallback/desktop/mobile images: replace or explicit variant removal -> old unreferenced managed image cleaned.
- Admission photo: replace or explicit remove -> old unreferenced managed image cleaned.

## Intentionally retained files

- Admission `Delete` is a soft/historical hide, so its photo is retained.
- Hero `Unpublish` retains images because the banner can be republished.
- Any file still referenced by another DB row/field is retained.
- Static/default/manual filenames that do not match the generated-upload naming pattern are retained automatically.

## Existing orphan cleanup

Admin > System Check now includes **Clean Orphan Uploads**.

It scans only server-generated uploads older than 48 hours and removes them only when no DB/static reference remains. The 48-hour grace period protects recent upload/draft workflows.

## Database

No schema change is required.
