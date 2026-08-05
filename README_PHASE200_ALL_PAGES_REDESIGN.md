# Phase 200 - Fresh All-Pages Frontend Redesign

## Base
This build starts from the original `spoken(2).zip`. Previous v121-v127 theme patches were not used.

## Design direction
- Reference: approved Design Option 2
- Brand palette: Well Fare navy, royal blue, white and controlled gold
- Education institute trust + smooth learning application feel
- Page-specific styling instead of universal text/background overrides
- Responsive typography with strict mobile limits
- Animated wave, dotted accent and connected learning-flow treatment

## Architecture
- Public frontend no longer loads the 400KB+ legacy `style.css`.
- New clean frontend stylesheet: `assets/css/site-v200.css`
- New public interactions: `assets/js/site-v200.js`
- Admin panel continues using the original admin stylesheet and structure.
- `weekly-exam-room.php` retains its dedicated exam styling.

## Redesigned public areas
- Shared topbar, desktop header and dropdowns
- Right-side full-height mobile drawer
- Five-item mobile bottom navigation
- Homepage hero, stats, features, approved course cards and learning process
- Online Classes page
- Courses and Course Detail
- Admission form
- Student Login/Register
- Student Dashboard
- Weekly Test, Result and Revision
- Study Materials / Practice Room
- AI Teacher and Free Practice
- Learning Roadmap and Lesson
- Faculty profile
- About, Gallery and Contact
- Shared pre-footer and footer

## Important fixes
- No global white text rules on light surfaces
- Explicit dark text for every light card/form/hero
- White text limited to intentional navy surfaces
- Mobile H1/H2 sizes use `clamp()` and page-specific limits
- Course cards preserve the approved navy/gold icon-card direction
- Forms, tables, tests and student tools have independent responsive layouts
- PWA cache updated to `wellfare-spoken-pwa-v200`
