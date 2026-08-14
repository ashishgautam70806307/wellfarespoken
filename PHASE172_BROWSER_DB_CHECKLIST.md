# Phase 172 Browser / DB Checklist

1. Open Admin > Weekly Tests on desktop, tablet and mobile.
2. Confirm Basic / Previous / Upcoming tabs remain reachable and the requested scope stays active.
3. In Test Setup confirm only Paper Details is open initially; Opening and Optional Settings are collapsed.
4. Create or edit a paper; verify Paper, Title, Batch and Duration save as before.
5. Open Opening; verify Manual works and Schedule reveals Date / Start Time / Entry Window.
6. Open Optional Settings; verify Instructions and Advanced settings still load/save.
7. Confirm Sample Excel (3 Q), Blank Excel and Add Manually actions work.
8. Upload XLSX/CSV to the selected paper and confirm question totals update.
9. In Question Bank, verify navigation reads `Questions X-Y of N`, Previous/Next works and no duplicate numbered pager appears.
10. In Student Answer Copies, verify navigation reads `Students X-Y of N`, Previous/Next works and no duplicate numbered pager appears.
11. Test a stale `qpage`/`apage` URL after applying a filter; page should clamp to a valid page instead of showing an empty invalid page.
12. Verify Publish, Close/Pending, Finalize Top 3, answer release and existing student test behavior remain unchanged.
