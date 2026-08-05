# Phase 119 - Global Font Size Fix

Issue: At browser 90% zoom the website looked perfect, but at 100% zoom fonts felt large.

Fix:
- Reduced actual CSS typography scale instead of using CSS zoom/transform.
- Frontend headings capped around 2.05rem.
- Body text normalized around 14px.
- Admin panel fonts normalized around 13.5px.
- Mobile heading caps added.
- CSS cache updated to style.css?v=119.

This keeps the browser at normal 100% zoom while making the design visually similar to the earlier 90% zoom view.
