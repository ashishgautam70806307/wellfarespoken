# Phase 125 Home Page Test Checklist

## Desktop — 1366 / 1440 / 1920

- [ ] Logo is clear and uses the original navy/gold identity.
- [ ] No red header, active menu or floating action styling remains on Home.
- [ ] Hero image fills the banner without stretching.
- [ ] Hero title stays within two or three lines.
- [ ] Slider changes automatically and transitions smoothly.
- [ ] Previous, next, dots and pause/play controls work.
- [ ] Quick actions stay in one balanced row.
- [ ] Online Class visual and copy use balanced two-column spacing.
- [ ] Four course cards have equal height.
- [ ] Footer begins after all home sections without overlap.

## Tablet — 768 / 1024

- [ ] Header switches cleanly to hamburger at the existing breakpoint.
- [ ] Hero text remains readable over the image.
- [ ] Slider controls do not cover title/buttons.
- [ ] Quick actions form a 2 × 2 layout.
- [ ] Online Class section becomes one column without overflow.
- [ ] Courses form two columns.
- [ ] Learning steps remain readable.

## Mobile — 320 / 360 / 390 / 430

- [ ] Top information bar is hidden.
- [ ] Logo and hamburger fit on one line.
- [ ] Hero image uses the mobile crop.
- [ ] Hero title is not clipped.
- [ ] Primary action remains visible without scrolling inside the banner.
- [ ] Swipe left/right changes banner.
- [ ] Dots and pause/play remain tap-friendly.
- [ ] Quick actions show compact cards and no horizontal overflow.
- [ ] Online feature labels stay short.
- [ ] Course cards become compact horizontal cards.
- [ ] Review cards scroll horizontally.
- [ ] Bottom navigation and floating contact button do not cover CTAs.

## Performance

- [ ] Network loads `phase123-shell.min.css`, not legacy `style.min.css`, on Home.
- [ ] Network loads `phase125-home.min.css` in production.
- [ ] First hero image is WebP in a modern browser.
- [ ] Secondary hero/story images are lazy loaded.
- [ ] Service Worker cache name is `wellfare-spoken-static-v125`.
- [ ] Hard refresh shows the new CSS after deployment.

## Admin

- [ ] Add two published Home Hero banners and verify both rotate.
- [ ] Change banner sort order and verify frontend order.
- [ ] Unpublish a banner and verify it disappears.
- [ ] Add Online Class feature cards from Dynamic Content Blocks.
- [ ] Enter Font Awesome classes instead of emoji.
