# Phase 65 - Spoken auto-next + Admin Materials cleanup

Frontend spoken-materials.php:
- Correct answer now auto-moves to the next sentence after 1 second.
- Long voice message removed.
- Correct answer voice now says only: "Correct. Next sentence."
- Wrong answer voice is shorter: "Correction. [correct sentence]"
- Manual Check remains as backup.

Mobile footer nav:
- Active icon background changed to light premium background.

Admin materials.php:
- Removed unnecessary Hindi/developer helper text and replaced with clean admin-friendly English.
- Added bulk delete with checkbox selection.
- Added server-side pagination for uploaded sentence records.
- Added reset search button.
- Improved form grid alignment, spacing, input height and button alignment.
- Improved table selection column and pagination UI.

API:
- admin/materials-ajax.php now supports:
  - bulk_delete_sentences
  - paginated list_sentences
