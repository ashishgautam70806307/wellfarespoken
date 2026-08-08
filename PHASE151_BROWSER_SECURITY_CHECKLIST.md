# Phase 151 Browser / Security Checklist

After replacing the project, clear the old service worker/site cache and hard reload.

## Image upload tests

For each Admin image form (Admission, Course, Faculty, Testimonial, Gallery, Settings, Hero Banners):

1. Upload a normal JPG — must save.
2. Upload a normal PNG — must save.
3. Upload a normal WEBP — must save.
4. Try GIF — must be rejected.
5. Try SVG — must be rejected.
6. Try ICO — must be rejected.
7. Try a `.php` file — must be rejected.
8. Rename a PHP/text file to `.jpg` — server must reject it.
9. Try a filename such as `shell.php.jpg` — server must reject it.
10. Confirm saved image has a randomized server filename.

## Hero Banner tests

1. Upload desktop image only, leave text blank, Save — should work as image-only.
2. Upload mobile image only, leave text blank, Save — should work and safely fall back across devices.
3. Enter title/subtitle only, no image, Save — should render text-only gradient hero.
4. Add image + text — both should render.
5. Set Overlay Darkness = 0 — homepage image should have no forced dark overlay.
6. Test 0, 30, 58 and 85 overlay values on desktop and mobile.
7. Confirm banner slider remains functional when mixing image-only and text-only slides.

## Regression checks

- Admin owner / role permissions still enforce Phase 150 behavior.
- Student login/register unchanged.
- Admissions unchanged except image-file restriction.
- Weekly Tests unchanged.
- Roadmap/Practice/Voice Coach unchanged.
- Payments/enrollment lifecycle unchanged.
