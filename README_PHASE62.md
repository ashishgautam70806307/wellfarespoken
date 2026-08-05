# Phase 62 - Home + Courses page final polish

Home page:
- Deduped hero stats so Daily/Basic+ appear once only.
- Added auto-moving Student Reviews carousel.
- Review cards show circular student image if uploaded, otherwise first-letter avatar.
- Optimized spacing for home review section.

Courses page:
- Course cards now show image, price, details button and Pay Now button.
- Admin Courses module now supports:
  - price
  - payment link
  - course image
  - class time
  - class days
  - total tests
  - lessons/classes
  - detailed description
  - outcomes
  - included features
  - multiple course variants/batches
- Course detail page redesigned with price, timing, days, tests, image, outcomes, includes and variants.

Images:
- Course image upload accepts PNG, JPG, JPEG, GIF only.
- Gallery validation also changed to PNG, JPG, JPEG, GIF only.
- Max upload size remains 2 MB.

After upload:
1. Open admin/system-check.php once.
2. Open admin/courses.php and update courses with price/details.
3. Ctrl + F5 on frontend.
