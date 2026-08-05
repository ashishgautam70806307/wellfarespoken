# Phase 127 — Responsive and Flow Test Checklist

## 1. Common navigation

- [ ] Header is identical on Home, Courses, About, Contact, Admission, Gallery, Roadmap and Test pages.
- [ ] Only one menu item/group is active for the current page.
- [ ] Desktop dropdown opens by mouse and keyboard click.
- [ ] Clicking outside closes the desktop dropdown.
- [ ] Mobile drawer opens and closes without horizontal page movement.
- [ ] Active mobile group is expanded automatically.
- [ ] Mobile drawer uses the same menu labels and links as desktop.
- [ ] Student CTA changes to My Dashboard after login.
- [ ] Mobile bottom navigation shows Home, Roadmap, Practice, Test and Account.

## 2. Brand consistency

- [ ] Main public pages use navy, blue, gold, white and light blue only.
- [ ] Green is limited to completed/success states.
- [ ] Red appears only for errors/danger, not decorative cards/buttons.
- [ ] Buttons, cards, forms and section headings have the same radius/shadow system.
- [ ] No old red reference navigation remains on any public page.

## 3. Home Page

- [ ] Desktop banner uses the desktop image uploaded in admin.
- [ ] Mobile banner uses the mobile image uploaded in admin.
- [ ] Slider autoplay, arrows, dots, swipe and pause controls work.
- [ ] Hero heading does not overflow at 320px or large desktop widths.
- [ ] Quick Actions link to Practice, Roadmap, Test and Student Account.
- [ ] Online Class section remains readable on mobile.
- [ ] Course cards do not overflow.
- [ ] Reviews first row moves right-to-left.
- [ ] Reviews second row moves left-to-right.
- [ ] Reviews loop without an empty gap or sudden jump.
- [ ] Hover pauses review rows on desktop.
- [ ] View All Reviews opens `reviews.php`.

## 4. Main student flow

- [ ] Home -> Courses -> Course Detail works.
- [ ] Course/Admission action opens the Admission page.
- [ ] Admission form saves successfully.
- [ ] Student Login/Register works.
- [ ] Dashboard links to Roadmap, Practice, Test and Revision.
- [ ] Roadmap current/completed/locked states remain correct.
- [ ] Roadmap Lesson opens only according to existing access logic.
- [ ] Practice Room actions work.
- [ ] Weekly Test practice and official exam paths remain secure.
- [ ] Result and Revision links work.

## 5. Public pages

- [ ] Courses: 3 columns desktop, 2 tablet, 1 mobile.
- [ ] Course Detail: summary, variants and action buttons fit mobile.
- [ ] About: director and institute cards remain readable.
- [ ] Contact: Call, WhatsApp and Map links work.
- [ ] Admission: form becomes one column on mobile.
- [ ] Gallery: images retain aspect ratio and captions do not overflow.
- [ ] Reviews: cards display correctly with and without student photos.
- [ ] AI Teacher and Quick Practice retain working forms/actions.
- [ ] Faculty Profile, Weekly Result and Roadmap Lesson use the common header/footer.

## 6. Responsive widths

Test at minimum:

- [ ] 320 x 568
- [ ] 360 x 640
- [ ] 390 x 844
- [ ] 430 x 932
- [ ] 768 x 1024
- [ ] 1024 x 768
- [ ] 1366 x 768
- [ ] 1440 x 900
- [ ] 1920 x 1080

At every width verify:

- [ ] No horizontal scrolling.
- [ ] No clipped heading/button.
- [ ] Floating contact controls do not cover primary actions.
- [ ] Mobile bottom navigation does not cover final page content.
- [ ] Tap targets are comfortable.

## 7. Cache and deployment

- [ ] Files from `DELETE_OLD_FILES_PHASE127.txt` are deleted.
- [ ] Browser cache is cleared.
- [ ] Previous Service Worker is updated/unregistered.
- [ ] New Service Worker cache name is `well-fare-spoken-v127`.
- [ ] Logout followed by Back does not reveal private student data.
- [ ] Production loads minified Phase 127 CSS.

## 8. Regression checks

- [ ] Admin panel still opens.
- [ ] Admin banner management still saves desktop and mobile banners.
- [ ] No database migration error appears.
- [ ] Student authentication and sessions still work.
- [ ] Weekly Test token/timer security remains functional.
- [ ] PHP error log has no new warnings/notices after visiting all public pages.
