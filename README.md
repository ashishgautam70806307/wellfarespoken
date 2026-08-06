# Well Fare English Spoken - Phase 139 Compact Mobile Learning

This is the cumulative working package built on Phase 138 Universal Mobile UX and all earlier Phase 135/136/137 fixes.

Phase 139 introduces an interaction-first phone workspace for Roadmap Lesson, Learning Roadmap, Spoken Practice, Weekly Test Center, and Weekly Exam Room. It also fixes cropped mobile button labels, improves form icon behavior, reduces mobile input height, removes decoration from the hamburger trigger, and makes footer social configuration clearer in Admin Settings.

## Main improvements

- Roadmap Lesson uses a phone-height workspace with four answer options in a two-by-two grid.
- Practice, test, and roadmap screens prioritize the current action instead of requiring repeated whole-page scrolling.
- Spoken Practice shows all four modes together and changes into a focused session after Start Practice.
- Weekly Test shows all three test types together with compact setup controls.
- Weekly Exam keeps timer, navigator, question, answer, and controls in the mobile workspace.
- Reusable button labels wrap safely and no longer crop View Details or similar text.
- Leading form icons fade after focus/value and mobile controls use a compact 44px height.
- Public hamburger trigger has no background, border, or shadow.
- Admin Site Settings includes a dedicated Social Media Links group with validation and visibility previews.
- Footer Facebook, Instagram, YouTube, LinkedIn, and X icons remain dynamic from saved settings.
- Service-worker cache namespace updated to `wellfare-spoken-static-v139`.

## Local setup

1. Back up the current project and database.
2. Extract this cumulative ZIP into the project web root.
3. Phase 139 has no database migration; keep the Phase 136 canonical database.
4. Open Admin -> Site Settings -> Social Media Links and save real URLs.
5. Clear the old service worker/site cache and perform Ctrl + F5.
6. Complete `PHASE139_BROWSER_TEST_CHECKLIST.md` using the real database and phones.

## Static validation

- 68 PHP files passed syntax validation.
- 9 JavaScript/service-worker files passed syntax validation.
- 44 CSS files passed parser validation.
- 181 genuine literal local links/assets were checked with no missing target.
- No duplicate literal HTML IDs were found.
- All 32 service-worker pre-cache targets exist.
- Phase 139 integration checks passed.

Interactive browser rendering, authenticated database workflows, speech recognition, iOS keyboard behavior, and real-device touch testing remain mandatory real-environment checks and are not represented as passed.

See:

- `README_PHASE139_COMPACT_MOBILE.md`
- `PHASE139_MOBILE_INTERACTION_REPORT.md`
- `PHASE139_BROWSER_TEST_CHECKLIST.md`
- `PHASE139_VALIDATION.json`
