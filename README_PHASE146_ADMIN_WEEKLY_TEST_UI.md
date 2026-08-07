# Phase 146 — Admin Login and Weekly Test UI Repair

Base: `spoken_phase145_student_test_ux.zip`

## Scope

This phase changes only the Admin Login presentation and the public Weekly Test page layout/interaction. Database schema, authentication rules, test eligibility, attempt creation, scoring and result history logic are unchanged.

## Admin Login

- Rebuilt as one unified premium card.
- Integrated navy brand panel and focused login form.
- Dynamic site logo, site name and configured login subtitle preserved.
- Security, CSRF, honeypot, password verification, rate limiting, Caps Lock warning and show/hide password preserved.
- Removed unrelated public mobile CSS from this standalone page.
- Added a dedicated lightweight page stylesheet.

## Weekly Test

- Removed Phase 139/141/142 learning-page assets from this page only because they contained competing mobile layouts.
- Added a final page stylesheet loaded after the cumulative design stack.
- Rebuilt mobile hero, test carousel, cards, setup form, login gate, safety row, result history and guest callout.
- One readable test card is shown per mobile slide.
- Full and short status labels are separated to prevent overflow.
- Carousel calculations use actual card positions and ResizeObserver where available.
- Existing secure native POST start flow is preserved.

## Cache

Service-worker namespace: `wellfare-spoken-static-v146`.
