# Phase 140 Contact Page Browser Test Checklist

Open `contact.php` after installing the cumulative ZIP.

## Before testing

- Clear the old site Service Worker and cache.
- Hard refresh the page.
- Confirm the application is using the expected local/staging database.

## Desktop

- [ ] Hero heading and paragraph are readable on the navy background.
- [ ] Call and WhatsApp actions show complete labels.
- [ ] Contact console shows phone, email, address and guidance time.
- [ ] Three purpose cards align to equal height.
- [ ] Google Maps card and four-step guidance card align correctly.
- [ ] Final CTA and dynamic social icons are visible when configured.
- [ ] Header and footer remain unchanged and functional.

## Tablet

- [ ] Hero stacks without overlap.
- [ ] Contact console remains readable.
- [ ] Purpose cards do not overflow.
- [ ] Map and guidance cards stack in the correct order.

## Mobile 360–430 px

- [ ] Heading fits without clipped words.
- [ ] Call and WhatsApp cards remain compact and tappable.
- [ ] Email and address wrap without horizontal scrolling.
- [ ] Purpose cards use the compact icon/content layout.
- [ ] Map card text and pin do not overlap.
- [ ] Talk Now and Send Enquiry buttons show full text.
- [ ] Bottom navigation does not cover final content.

## Dynamic data

- [ ] Phone opens the dialler.
- [ ] WhatsApp opens the configured number with a prepared message.
- [ ] Email opens the mail client.
- [ ] Map opens the configured Google Maps URL.
- [ ] Office time matches Admin Site Settings.
- [ ] Only saved social platforms appear.

## Regression

- [ ] Admission link opens `admission.php`.
- [ ] Existing-student link opens `student-auth.php`.
- [ ] Public mobile drawer remains functional.
- [ ] Footer contact dock remains functional.
