# Phase 83 - Speed + Footer + Topbar Fix

Fixes:
1. Website speed
   - ensure_schema_updates() now runs only once per request.
   - Heavy schema ALTER/CREATE checks now run once per project version.
   - A schema_marker is saved in site_settings after first upgrade.
   - This reduces slow loading on all frontend/admin pages.
   - Loader now hides on DOMContentLoaded with a fallback timeout.

2. Footer typography
   - Footer normal content font weight set to normal/500.
   - Only footer headings/brand name remain bold.
   - Copyright strip padding reduced to 4px top/bottom.

3. Topbar alignment
   - container topbar-inner content vertically centered.
   - Topbar text font weight made normal.
   - Marquee/social/phone vertical alignment fixed.
