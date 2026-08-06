# Phase 137 Browser Test Checklist

Use localhost or staging with the Phase 136 database.

## Cache

- [ ] Hard refresh with Ctrl + F5.
- [ ] Confirm `sw.js` installs cache `wellfare-spoken-static-v137`.
- [ ] Confirm no older CSS is served after reload.

## Heading contrast

- [ ] Shared page heroes have white headings.
- [ ] Home learning-path, batch and final CTA headings are visible.
- [ ] About teaching-promise heading is visible.
- [ ] Admission and Weekly Test hero headings are visible.
- [ ] Course Summary heading is visible.
- [ ] Student auth information heading is visible.
- [ ] Roadmap lesson header is visible.
- [ ] Student dashboard/revision hero headings are visible.
- [ ] Spoken Practice dark question panel and CTA are visible.
- [ ] Footer headings, links and contact text are visible.

## Spoken Practice Room

- [ ] All four mode cards switch correctly.
- [ ] Active mode gets selected styling and `aria-selected=true`.
- [ ] Lesson and topic filters load data.
- [ ] Search works.
- [ ] Start Practice loads a sentence.
- [ ] Read Question works.
- [ ] Hands-free and manual mic controls work.
- [ ] Stop Auto stops listening.
- [ ] Manual Check and Finish & Check work.
- [ ] Result state displays correctly.
- [ ] Next sentence works.
- [ ] Mobile layout has no horizontal page overflow.

## Admin Login

- [ ] Only one login card is visible.
- [ ] Logo and institute name load.
- [ ] Invalid login message is visible.
- [ ] Password show/hide works.
- [ ] Caps Lock notice works.
- [ ] Correct login reaches dashboard.
- [ ] Rate-limit attempts still work.
- [ ] Back-to-website link works.
- [ ] Mobile layout remains usable.

## Footer

- [ ] Dynamic logo/name/tagline load.
- [ ] Navigation links work.
- [ ] Phone, email and map links work.
- [ ] WhatsApp appears only with a number.
- [ ] Social icons appear only for saved URLs.
- [ ] Footer is readable on desktop, tablet and mobile.
