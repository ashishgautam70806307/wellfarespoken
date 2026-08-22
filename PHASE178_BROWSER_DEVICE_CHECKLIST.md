# Phase 178 - Mobile Browser / Device Checklist

Test the real project after deployment with browser cache/service-worker refresh.

## Required widths

- 320px wide / short height around 455px
- 360px wide
- 390px wide
- 412px wide

## Shared structure

- [ ] Existing common header is visible and works.
- [ ] Existing common mobile menu works.
- [ ] Existing common footer/bottom navigation works.
- [ ] No duplicate/alternate app header appears.
- [ ] Contact/help control never covers a learning button or answer field.
- [ ] No horizontal page scroll.

## `spoken-materials.php`

- [ ] Speak Daily opens and works.
- [ ] Hindi to English opens and works.
- [ ] English to Hindi opens and works.
- [ ] Revision opens and works.
- [ ] Change Mode works.
- [ ] Continuous Voice Coach toggle remains accessible.
- [ ] Listen, Speak and Stop are all visible/tappable.
- [ ] Answer field does not sit under another control when keyboard opens.
- [ ] Check Answer and Clear are both clearly labelled and usable.
- [ ] Previous and Next remain visible and usable.
- [ ] At timer/mic/network recovery, Phase 170 continuous voice behavior remains intact.

## `free-ai-english-practice.php`

- [ ] Quick practice helper is fully visible.
- [ ] Correct Sentence / Hindi to English / English to Hindi can all be selected.
- [ ] Quick microphone works.
- [ ] Practice Now and Clear work.
- [ ] Every category and lesson remains accessible.
- [ ] Long lesson names wrap inside their cards.
- [ ] Lesson loading gives a result or a useful timeout instead of hanging forever.
- [ ] Text-answer and option-answer questions work.
- [ ] Speak Answer works when supported.
- [ ] Previous / Check Answer / Next stay usable above the mobile nav.
- [ ] Result, accepted answers, feedback and score remain readable.

## `learning-roadmap.php`

- [ ] Progress ring and summary are readable.
- [ ] Every stage remains visible.
- [ ] Current level is visually obvious.
- [ ] Completed levels remain reviewable.
- [ ] Locked levels remain visibly locked.
- [ ] Description and level metadata are readable instead of hidden.
- [ ] Continue works.
- [ ] Reset Progress works with confirmation.
- [ ] Authorized Admin still sees Manage Roadmap.

## Touch / performance

- [ ] Main action buttons feel immediate on first tap.
- [ ] No hover animation causes a delayed mobile tap.
- [ ] 2-3 fast taps do not accidentally duplicate an AJAX action.
- [ ] Slow network error returns control to the user.
- [ ] Test with keyboard open and closed.
- [ ] Test portrait orientation on a physical Android phone.
- [ ] Test Safari/PWA on an iPhone if available.
