# Phase 113 - Weekly Test Admin Reliability

Implemented:
- Custom premium toast/confirm modal. Native browser confirm/alert is no longer used for weekly admin actions and weekly test submit/cancel.
- AJAX success/error notifications now show as premium toast.
- CSV/XLSX parser improved for UTF-8 CSV, XLSX shared strings and inline strings.
- Upload now gives clear error if columns/rows are invalid.
- Publish Now now enforces one active paper per test type. Other Basic/Previous/Upcoming papers move to Pending automatically.
- Save Test with Active status also enforces one active paper for that type.
- Admin weekly tabs moved near top and current test type gets a light scoped background.
- Batch cards spacing/published state improved.
- Demo batch creation creates two papers but keeps only the first paper active and the second pending to avoid student confusion.

Admin flow:
1. Weekly Tests -> select Basic/Previous/Upcoming tab.
2. Create or select a paper.
3. Upload CSV/XLSX answer sheet.
4. Click Publish Now. This paper becomes the only active paper for that type.
