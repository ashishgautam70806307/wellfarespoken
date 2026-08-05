# Phase 126 — Home, Responsive Banners, Roadmap Path and Brand System

## Why the screenshot was not final-quality

The Phase 125 screenshot was moving in the right direction, but it still had clear UI problems:

- Hero heading was oversized and visually cut off.
- Banner was inside rounded side gutters instead of behaving like a full-width hero.
- Desktop and mobile artwork were not independently manageable in the existing database.
- Home page did not contain enough useful student actions and institute sections.
- Mobile feature cards needed a more compact structure.
- The roadmap showed cards, but the path, node connection and unlock process were not visually strong enough.
- Earlier shared styles introduced colors outside the logo palette.

Phase 126 corrects these issues without replacing the existing PHP/business logic.

## Locked brand formula

Use these colors throughout public pages:

- Deep navy: `#061a38`
- Navy: `#092653`
- Royal blue: `#1f5c9f`
- Logo gold: `#d8a62d`
- Light gold: `#f1c861`
- White and very light blue surfaces
- Green only for successful/completed states
- Red only for errors, warnings or destructive actions

Do not add random red, purple, orange or neon gradients to normal page decoration.

## Home page changes

The home page now follows this student-first order:

1. Full-width responsive banner slider
2. Four direct student actions
3. Trust/benefit strip
4. Why Well Fare
5. Online Class
6. Courses
7. Four-step learning path
8. Daily practice tools
9. Available batches
10. Student reviews
11. Admission call-to-action

The page intentionally uses short descriptions. Detailed information remains on dedicated pages.

## Responsive hero banner management

Admin path:

```text
/admin/hero-banners.php
```

Each banner now supports:

- Separate desktop/laptop image
- Separate mobile image
- Legacy fallback image
- Text overlay on/off
- Left, center or right text position
- Overlay darkness control
- Sort order and publish status

Recommended dimensions:

- Desktop/laptop: `1920 × 720` or `1600 × 600`
- Mobile: `900 × 1200` or `750 × 1000`
- Format: WebP preferred, then JPG/PNG

When artwork already includes text, select **No — image only** to avoid duplicate text.

## Database upgrade

Run once:

```text
sql/phase126_home_roadmap_upgrade.sql
```

On an existing installation, take a database backup first.

Alternative for the existing controlled schema updater:

```env
APP_ALLOW_SCHEMA_UPDATES=true
```

Open the admin System Check once, verify the columns, and then set:

```env
APP_ALLOW_SCHEMA_UPDATES=false
```

## Roadmap changes

`learning-roadmap.php` now includes:

- A visible Learn → Practice → Complete → Unlock process
- Central connected path on desktop
- Alternating left/right level cards
- Single connected path on tablet/mobile
- Current, completed and locked node states
- Next-action progress card
- Compact mobile level cards
- Existing local progress key preserved

## CSS organization

New files:

```text
assets/css/phase126-brand-system.css
assets/css/phase126-brand-system.min.css
assets/css/phase126-home.css
assets/css/phase126-home.min.css
assets/css/phase126-roadmap.css
assets/css/phase126-roadmap.min.css
assets/js/phase126-home.js
```

Production automatically uses minified CSS through the existing asset helper.

## Deployment

1. Back up current files and database.
2. Extract the replace-only ZIP into the current project root.
3. Run the Phase 126 SQL migration once.
4. Clear browser cache.
5. Unregister/update the old service worker.
6. Hard refresh with `Ctrl + Shift + R`.
7. Upload at least two published Home Hero banners from admin.
8. Test the supplied checklist.

## Important scope

Phase 126 improves:

- Global public brand consistency
- Home page
- Banner management
- Learning roadmap

Other pages keep their existing logic. The shared brand CSS normalizes their common header, actions and heading colors, but page-by-page redesign should continue only after this direction is approved.
