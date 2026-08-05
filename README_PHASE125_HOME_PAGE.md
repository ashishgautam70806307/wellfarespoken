# Phase 125 — Professional Home Page

This phase changes **only the public home page and its supporting home-page management code**. Other public pages, student modules, tests, roadmap logic, database structure and security fixes remain unchanged.

## Design direction

The home page now follows the actual Well Fare logo palette:

- Deep navy
- Royal/navy blue
- Gold
- White and very light blue surfaces

Red accent styling from the earlier reference phase is overridden on the home page. Gold is used only as an accent, not as a large background across every section.

## Home page structure

1. Admin-aware hero banner slider
2. Four direct student actions
3. New Online Class section
4. Compact course cards
5. Three-step learning process
6. Short student review section
7. Final admission call-to-action

Long faculty, gallery, video, batch and FAQ blocks were removed from the home page. Their separate pages and admin data remain available.

## Hero slider

- Smooth autoplay every 5.8 seconds
- Previous/next controls on laptop and desktop
- Dots, slide counter and pause/play control
- Touch swipe on mobile
- Pauses on hover, keyboard focus and inactive browser tabs
- Respects `prefers-reduced-motion`
- First image loads with high priority
- Later slides use lazy loading
- Mobile-specific crop for the main banner
- CSS-based wave animation below the banner

### Manage banners

Open:

```text
/admin/hero-banners.php
```

All published banners assigned to `Home Hero` rotate on the home page. Sort order controls their order.

Recommended uploaded image:

```text
1600 × 900 pixels
JPG, PNG or WebP
Maximum 3 MB
```

Keep faces and important visual details near the centre because the same uploaded image is cropped responsively.

## Online Class section

The new section includes:

- Live-class laptop visual
- Animated voice waveform
- Live teacher status
- Student participant indicators
- Direct admission action
- Current batch timing chips when batch data exists
- Three short feature cards

### Manage Online Class features

Open:

```text
/admin/content.php?type=online_class_feature
```

Use Font Awesome classes such as:

```text
fa-solid fa-video
fa-solid fa-microphone-lines
fa-solid fa-chart-line
```

Keep each title short and each subtitle to one short sentence.

## Performance

The home page now uses the lightweight frontend shell instead of the large legacy stylesheet.

Approximate production CSS payload:

```text
phase123-shell.min.css          11 KB
phase123-ui-core.min.css        14 KB
phase124-reference-ui.min.css   12 KB
phase125-home.min.css           34 KB
-------------------------------------
Total                           71 KB
```

The main responsive hero files are approximately:

```text
Desktop WebP   66 KB
Mobile WebP    55 KB
Story WebP     36 KB
```

Additional performance behavior:

- Production automatically loads `phase125-home.min.css`.
- Below-fold sections use `content-visibility` where supported.
- Service Worker cache version is updated to `v125`.
- Home CSS, JS and optimized hero images are cached as static assets.
- Slider uses transforms and opacity instead of layout-heavy animation.
- No slider library, jQuery or extra animation library is used.

## Files changed

```text
index.php
includes/functions.php
includes/footer.php
admin/content.php
admin/hero-banners.php
sw.js
assets/css/phase125-home.css
assets/css/phase125-home.min.css
assets/js/phase125-home.js
assets/uploads/banners/home-banner-speaking-desktop.webp
assets/uploads/banners/home-banner-speaking-mobile.webp
assets/uploads/banners/home-banner-student-success.webp
README_PHASE125_HOME_PAGE.md
PHASE125_CHANGED_FILES.txt
```

## Replace instructions

1. Back up the current files and database.
2. Use the Phase 125 replace-only ZIP on top of Phase 124.
3. Preserve the folder structure while extracting.
4. Overwrite matching files.
5. Clear browser cache.
6. Unregister or update the old Service Worker once.
7. Hard refresh using `Ctrl + Shift + R`.
8. Test the page at 320, 360, 390, 768, 1024 and 1440 pixel widths.

No database migration is required.

## Required checks

- Main banner changes automatically.
- Pause/play button works.
- Previous/next buttons work.
- Swipe changes slide on mobile.
- Online Class button opens admission.
- Course cards open correct course details.
- Student action cards open correct modules.
- Header and floating buttons use navy/gold, not red.
- Mobile bottom navigation does not cover page actions.
- Reduced-motion system preference disables nonessential animation.

## Validation completed

- All 68 PHP files passed `php -l`.
- Home JavaScript passed `node --check`.
- Service Worker JavaScript passed `node --check`.
- Minified and source CSS have matching brace counts.
- Full and replace-only ZIP integrity are checked during packaging.
