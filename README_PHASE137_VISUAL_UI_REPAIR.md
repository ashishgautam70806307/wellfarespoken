# Well Fare English Spoken - Phase 137

Phase 137 is the visual consistency, contrast and focused page-redesign update built on Phase 136.

## Main results

- Dark-card headings and supporting text now use a reusable contrast-safe surface system.
- Spoken Practice Room has a new premium practice workflow without changing its APIs.
- Admin login is now a unique single-card secure screen.
- Footer is redesigned and social links are dynamically controlled from Admin Settings.
- Obsolete legacy admin-login CSS was removed and affected production CSS was regenerated.
- Service worker cache version is `wellfare-spoken-static-v137`.

## Dynamic social links

Open Admin > Settings and fill any of these fields:

- Facebook URL
- Instagram URL
- YouTube Channel URL
- Twitter / X URL
- LinkedIn URL

Only completed URLs appear in the public footer.

## Important post-install test

1. Hard refresh the browser with `Ctrl + F5`.
2. If an older design remains, clear the site's service-worker/cache once and reload.
3. Check Home, Contact, About, Admission, Course Detail, Student Login, Roadmap Lesson, Weekly Test, Spoken Practice Room and the footer.
4. Test Admin Login at desktop and mobile widths.
5. Confirm practice modes, microphone controls, answer checking and next-sentence actions.

No SQL import is required specifically for Phase 137.
