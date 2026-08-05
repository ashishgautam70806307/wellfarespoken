# Phase 128 — Localhost / Staging Test Checklist

## Preparation

- [ ] Back up files and database.
- [ ] Set `APP_ENV=production` only after testing.
- [ ] Clear browser cache and old service worker.
- [ ] Test while logged out and logged in.

## Header and navigation

- [ ] Header is identical on Home, Contact, Student Login, Courses, Roadmap and Test.
- [ ] Learn dropdown stays open while moving the pointer into it.
- [ ] Test & Practice dropdown links open correctly.
- [ ] Student and About dropdowns open by click and hover.
- [ ] Clicking outside closes the dropdown.
- [ ] Escape closes the dropdown.
- [ ] Mobile drawer opens/closes and only one submenu stays open.
- [ ] No menu is hidden behind a page section.

## Home banner

- [ ] No `content_position` warning appears.
- [ ] Desktop image loads at 768px and above.
- [ ] Mobile image loads below 768px.
- [ ] Multiple published banners autoplay smoothly.
- [ ] Previous/Next buttons work.
- [ ] Dots and pause/play work.
- [ ] Finger swipe works on Android/iPhone.
- [ ] Long admin text does not overflow.
- [ ] Image-only banner works when overlay content is disabled.

## Student Reviews

- [ ] First row moves right-to-left.
- [ ] Second row moves left-to-right.
- [ ] Both rows loop without a large empty gap.
- [ ] Hover pauses on desktop.
- [ ] Cards do not touch each other on mobile.

## Home and public sections

- [ ] Online Class page opens from navigation and footer.
- [ ] Course cards and course details open correctly.
- [ ] Cards have visible, equal gaps at 320, 390, 768, 1024 and 1440 widths.
- [ ] Headings are not oversized or clipped.
- [ ] Primary and secondary buttons follow one common format.

## Student authentication

- [ ] No large blank space appears above the header.
- [ ] Login and Register tabs work.
- [ ] Login, registration, validation and error messages work.
- [ ] Password fields remain accessible on mobile.
- [ ] Floating contact button does not cover the submit button.

## Roadmap

- [ ] Guest completion saves locally.
- [ ] Logged-in completion saves after refresh and on another browser session.
- [ ] Locked lesson cannot be opened directly by logged-in student.
- [ ] Completing the previous lesson unlocks the next lesson.
- [ ] Reset clears the correct guest/account progress.
- [ ] Desktop path, tablet timeline and mobile path remain connected.

## Practice and test

- [ ] Practice Room AJAX filters/actions work.
- [ ] AI Teacher requests work or show a controlled error.
- [ ] Weekly Test card selection and setup work.
- [ ] Autosave works after refresh.
- [ ] Server timer expiry works.
- [ ] Submit only happens once.
- [ ] Result ownership/token checks work.
- [ ] Revision opens the correct weak answers.

## Footer

- [ ] No wave is visible above the footer.
- [ ] Footer links open valid pages.
- [ ] Call, WhatsApp, email and map links use configured values.
- [ ] Mobile footer columns and bottom navigation do not overlap.

## Final browser/device matrix

- [ ] Chrome desktop
- [ ] Edge desktop
- [ ] Android Chrome
- [ ] iPhone Safari
- [ ] 320px width
- [ ] 360/390px width
- [ ] 768px tablet
- [ ] 1366/1440px desktop
