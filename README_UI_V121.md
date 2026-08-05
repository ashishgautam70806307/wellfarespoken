# Well Fare English Spoken — Professional UI V121

This edition keeps the original `spoken/` Core PHP structure, database logic, forms, APIs, admin modules and learning workflows. The UI has been rebuilt as one consistent design system instead of applying unrelated page-by-page styling.

## Main design file

- `assets/css/wellfare-design-system-v121.css`
- `assets/js/wellfare-ui-v121.js`

The old V120 surface theme is no longer loaded. V121 is the only upgrade layer loaded after the original functional stylesheet.

## Two coordinated experiences

### Public website

Home, About, Courses, Course Details, Gallery, Faculty, Contact and Admission use a premium educational website layout with spacious content, clear CTAs, proof sections and readable information hierarchy.

### Learning application

Student Dashboard, Study Material, Learning Roadmap, Lesson, AI Practice, Weekly Test, Result and Revision use a denser application layout with persistent learning navigation, progress blocks, practice controls and a five-item mobile app navigation.

## Responsive ranges

- Small phone: 320–459px
- Large phone: 460–759px
- Tablet: 760–979px
- Laptop: 980–1179px
- Desktop: 1180–1599px
- Large desktop: 1600px+

The design uses fluid `clamp()` typography and spacing between these ranges.

## Maintenance

For visual changes, edit the V121 CSS and JS files first. Avoid adding page-specific inline CSS unless the component has unique functional requirements.
