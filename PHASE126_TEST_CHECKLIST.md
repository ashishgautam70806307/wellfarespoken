# Phase 126 Test Checklist

## Before testing

- [ ] Database and project files backed up
- [ ] `sql/phase126_home_roadmap_upgrade.sql` executed
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Browser cache cleared
- [ ] Old service worker updated/unregistered

## Home hero

- [ ] Hero reaches both screen edges with no rounded outer container
- [ ] Desktop image appears at 1366px, 1440px and 1920px
- [ ] Mobile image appears at 320px, 360px, 390px and 430px
- [ ] Heading stays inside the hero and is not cut off
- [ ] Subtitle uses no more than two lines
- [ ] Slider auto-plays smoothly when two or more banners are published
- [ ] Previous/next and dots work on desktop
- [ ] Touch swipe works on mobile
- [ ] Pause/play button works
- [ ] Image-only banner displays no duplicate HTML text

## Admin banner management

- [ ] Desktop image upload saves correctly
- [ ] Mobile image upload saves correctly
- [ ] Existing image remains when no replacement is uploaded
- [ ] Text position setting works
- [ ] Overlay slider works
- [ ] Sort order changes slider order
- [ ] Unpublished banner disappears from public home
- [ ] Invalid file type is rejected

## Home sections

- [ ] Quick actions link correctly
- [ ] Trust cards remain readable at all widths
- [ ] Why Well Fare cards are compact on mobile
- [ ] Online Class section is single-column on mobile
- [ ] Course cards do not overflow at 320px
- [ ] Learning path is easy to understand without reading long paragraphs
- [ ] Reviews scroll horizontally on mobile
- [ ] Final CTA buttons do not overlap the mobile bottom navigation

## Roadmap

- [ ] Desktop has a visible central path with alternating cards
- [ ] Tablet changes to one connected left path
- [ ] Mobile path and nodes remain visible
- [ ] Current level is gold
- [ ] Completed level is green
- [ ] Locked levels cannot open
- [ ] Completing a level unlocks the next level
- [ ] Continue button opens the correct next level
- [ ] Progress percentage and points update
- [ ] Reset progress works only after confirmation

## Global brand consistency

- [ ] Public header uses navy/gold/white
- [ ] Active navigation uses gold, not random red
- [ ] Student Login remains navy/blue
- [ ] Green appears only for success/completed states
- [ ] Red appears only for errors/destructive actions

## Technical checks

- [ ] No PHP errors in server logs
- [ ] No JavaScript errors in browser console
- [ ] No horizontal page scrolling
- [ ] Dynamic PHP/student/admin pages are not cached by service worker
- [ ] Images load as WebP where provided
