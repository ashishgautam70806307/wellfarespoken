# Phase 126 — Home Refinement + Visual Roadmap Path

This phase corrects the large typography and crowded mobile layout shown in the supplied screenshot. It updates only the Home Page and Learning Roadmap design direction; existing database, student, test and admin logic remains unchanged.

## Screenshot review

The submitted design was moving in the right direction, but it was not final-ready because:

- Hero heading occupied four to five lines and pushed the useful actions below the first screen.
- The hero was too tall for laptop screens.
- Long admin-managed content was displayed without a safe compact fallback.
- Mobile feature cards became too small and some supporting text disappeared.
- Home Page had too few direct learning entry points.
- The Roadmap page displayed grouped cards, but did not visually look like a path.

## Home Page changes

### Hero

- Maximum desktop heading is approximately 52px instead of 76px.
- Mobile heading is approximately 28–34px.
- Long configured headline automatically receives a concise fallback.
- Hero height is reduced on laptop and mobile.
- Subtitle is limited to two readable lines.
- Buttons and slider controls remain visible inside the first screen.
- Existing desktop/mobile banner images and autoplay logic are preserved.

### New Practice Tools section

Six direct actions were added:

- Word Meaning
- Grammar
- Translation
- Speaking
- AI Teacher
- Weekly Test

The cards use a 3-column desktop, 2-column mobile and 1-column extra-small layout.

### Online Class mobile correction

- All three online features remain visible.
- Feature descriptions are no longer hidden on normal mobile screens.
- Cards use one clear row per feature.
- The class visual and copy stack correctly.

### New Why Well Fare section

Four short visual reasons were added:

- Live Teacher
- Clear Roadmap
- Daily Speaking
- Weekly Feedback

No long marketing paragraphs were added.

## Learning Roadmap changes

The old stage-card grid was replaced with a real journey design.

### Desktop

- Central vertical learning path
- Alternating left/right level cards
- Stage flags
- Numbered circular path nodes
- Connector lines between cards and path
- Completed, current and locked visual states
- Final confidence goal

### Mobile

- Vertical path remains visible on the left
- Every level card appears on the right
- Compact headings and metadata
- One-line description
- Locked/current/completed states remain clear
- No horizontal overflow

## Files changed

- `index.php`
- `learning-roadmap.php`
- `assets/css/phase126-home.css`
- `assets/css/phase126-home.min.css`
- `assets/css/phase126-roadmap.css`
- `assets/css/phase126-roadmap.min.css`
- `assets/js/phase126-home.js`
- `sw.js`

## Installation

1. Back up website files and database.
2. Extract the replace-only ZIP into the directory that contains the existing `spoken/` folder.
3. Allow overwrite for the listed files.
4. Clear browser cache.
5. Unregister or update the old service worker.
6. Hard refresh with `Ctrl + Shift + R`.
7. Test Home and Roadmap at the widths listed in the test checklist.

No database migration is required.

## Production settings

```env
APP_ENV=production
APP_DEBUG=false
APP_REMOTE_FONTS=false
```

Production automatically loads the minified Phase 126 CSS files.
