# Well Fare English Spoken — Phase 140 Contact Page Redesign Report

## Scope

Phase 140 completely redesigns `contact.php` on top of the Phase 139 cumulative base. No database schema, authentication, admission processing, student workflow, roadmap, practice or weekly-test business logic was changed.

## Design changes

### Premium communication hero

- Replaced the generic reusable page hero with a page-specific navy/gold visual direction.
- Added clear primary actions for Call and WhatsApp.
- Added compact reassurance points for level guidance, batch timing and free counselling.
- Added a glass-style contact console with live dynamic institute information.

### Intent-based navigation

The old equal contact cards were replaced with three purpose-driven routes:

1. Course selection guidance
2. Admission and batch registration
3. Existing-student portal access

This reduces decision friction and sends the visitor directly to the correct workflow.

### Visit and counselling section

- Added a premium map/location composition without a heavy embedded map.
- Added a four-step counselling flow: goal, level, batch and start.
- Preserved the configured Google Maps URL, address and office-time setting.

### Final contact CTA

- Added a compact final Call/WhatsApp panel.
- Dynamically displays Facebook, Instagram, YouTube, LinkedIn and X only when URLs are saved in Admin Site Settings.

## Responsive behaviour

- Desktop: asymmetric hero, contact console, three-card intent grid and two-column visit section.
- Tablet: hero and visit sections stack safely.
- Mobile: compact single-column hero actions, condensed contact console, horizontal-space-efficient intent cards, reduced typography and touch-friendly actions.
- Very small phones: assurance points and CTA buttons fall back to safe single-column layouts.

## Reusability and isolation

The redesign uses a dedicated page-scoped stylesheet:

- `assets/css/phase140-contact-page.css`
- `assets/css/phase140-contact-page.min.css`

Every selector is scoped through `body.page-contact`, so other project pages are not visually affected.

## Performance

- No new JavaScript dependency.
- No heavy map iframe or additional image dependency.
- Minified CSS gzip size is approximately 4.2 KB.
- Service-worker namespace updated to `wellfare-spoken-static-v140`.

## Static validation

- 68 PHP files: syntax PASS
- 9 JavaScript/service-worker files: syntax PASS
- 46 CSS files: structural PASS
- 33 service-worker precache assets: present
- New Contact page legacy markup: removed
- New page CSS and minified CSS: present

## Pending real-environment checks

The environment did not contain the PDO MySQL driver or a connected project database, so these remain pending on localhost/staging:

- Dynamic settings rendering from MySQL
- Real Call, WhatsApp, email and Google Maps links
- Social URL visibility based on saved Admin Settings
- Header/footer integration in an authenticated browser session
- Visual checks at 360, 390, 768, 1024 and desktop widths
- Chrome/Android and Safari/iPhone testing
