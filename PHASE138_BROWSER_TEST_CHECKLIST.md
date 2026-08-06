# Phase 138 Browser and Mobile Test Checklist

Test on the real localhost/staging installation with the actual database. Record each item as PASS, FAIL, FIXED, or LIMITATION.

## Test viewports

- 360 x 800 Android phone
- 375 x 812 iPhone-sized phone
- 390 x 844 modern phone
- 412 x 915 large Android phone
- 768 x 1024 tablet portrait
- Phone landscape orientation

## Global checks on every public page

- [ ] No horizontal scrollbar.
- [ ] Header logo, account button, and menu button align correctly.
- [ ] Announcement text does not overlap header actions.
- [ ] Main heading is readable and does not clip.
- [ ] Paragraph spacing and section gaps feel consistent.
- [ ] Cards have equal padding and do not touch screen edges.
- [ ] Buttons are readable, touch-friendly, and do not become unnecessarily tall.
- [ ] Forms have readable labels, leading icons, focus states, and validation messages.
- [ ] Existing custom-icon fields do not show duplicate icons.
- [ ] Footer contact information and social icons are visible and tappable.
- [ ] Fixed bottom navigation does not cover final page content.

## Public right-side drawer

- [ ] Drawer opens and closes smoothly.
- [ ] Background page cannot scroll while drawer is open.
- [ ] Every menu/group title is visible.
- [ ] Child options display in two columns on normal phones.
- [ ] Child options become one column on very narrow phones.
- [ ] Long menu text truncates safely without breaking cards.
- [ ] Student Login, Admission, and Call Now all fit in the action area.
- [ ] Institute Login remains visible.
- [ ] Drawer content scrolls independently when menu content is long.
- [ ] Outside click and close button work.

## Page-by-page public review

- [ ] Home: banner, quick actions, courses, roadmap, tools, batches, reviews, CTA.
- [ ] About: hero, story/cards, headings, statistics, CTA.
- [ ] Contact: quick contact cards, form, map/location area, footer.
- [ ] Courses: course cards, filters/content, CTA buttons.
- [ ] Course Detail: hero, details, sticky summary converted correctly on phone.
- [ ] Admission: all fields, selectors, icons, validation, submit action.
- [ ] Gallery: two-column grid and one-column narrow-phone fallback, lightbox.
- [ ] Reviews: cards/slider and submission flow where available.
- [ ] Faculty Profile: hero image/text and details.
- [ ] Online Class: cards, schedules, links, and CTA.

## Student area

- [ ] Student Registration form and all validation errors.
- [ ] Student Login and password visibility.
- [ ] Student Dashboard hero, stats, progress, actions, and logout.
- [ ] Student Revision list/cards.
- [ ] Learning Roadmap progress and locked/unlocked levels.
- [ ] Roadmap Lesson controls, content, and completion actions.
- [ ] Weekly Test list and setup controls.
- [ ] Weekly Result summary and question review.

## Spoken Materials

- [ ] Four practice modes scroll and select correctly.
- [ ] Selected mode remains visually clear.
- [ ] Lesson group, topic, search, and Start controls work.
- [ ] Progress panel is readable and no longer sticky on phone.
- [ ] Question and translation text remain visible on dark surfaces.
- [ ] Read Question works.
- [ ] Speak/Listening/Stop controls fit without overlap.
- [ ] Text answer field, Check, Next, Finish, and result states work.
- [ ] 360px and 375px layouts use the narrow-phone fallback correctly.

## Admin Login

- [ ] Single card fits within the viewport without side scrolling.
- [ ] Email/password fields and icons align.
- [ ] Password show/hide works.
- [ ] Caps Lock warning works.
- [ ] Error and rate-limit messages are readable.
- [ ] Login succeeds and redirects correctly.

## Admin dashboard and CRUD pages

- [ ] Admin drawer opens/closes and page scroll locks.
- [ ] All menu items remain visible and compact.
- [ ] Search field/results work on phone.
- [ ] Dashboard statistic cards fit at 360px.
- [ ] Toolbars and filters stack correctly.
- [ ] Form controls show appropriate icons without changing submitted values.
- [ ] Dynamic/AJAX-added controls receive icons correctly.
- [ ] Tables become readable mobile cards with labels.
- [ ] View/Edit/Delete/WhatsApp actions wrap without clipping.
- [ ] Empty-state table rows do not display incorrect labels.
- [ ] Test Courses, Batches, Students, Admissions, Enquiries, Weekly Tests, Materials, Gallery, Reviews, Settings, and Banners.

## Weekly Exam Room

- [ ] Entry instructions fit and scroll inside the viewport.
- [ ] Start/resume exam works.
- [ ] Timer stays visible without covering content.
- [ ] Question palette is usable.
- [ ] Answer options are touch-friendly.
- [ ] Previous/Next/Save/Submit controls fit.
- [ ] Browser refresh resumes the attempt.
- [ ] Timer expiry submits through the correct result flow.

## Performance and cache

- [ ] Hard refresh loads Phase 138 assets.
- [ ] Old Phase 137 drawer styling is not cached.
- [ ] Service worker reports cache `wellfare-spoken-static-v138`.
- [ ] Images below the fold lazy-load where configured.
- [ ] Scrolling remains smooth on a mid-range Android phone.
- [ ] No repeated console errors.
