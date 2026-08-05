# Phase 127 — Unified Design System, Reusable Navigation and Project Flow

## Purpose

Phase 127 removes the page-by-page navigation/design mismatch and converts the public website to one reusable design system based on the approved Home Page direction.

The phase keeps the existing PHP/database logic, Phase 122 security fixes, Phase 126 Home banner management, Weekly Test engine and connected Roadmap logic.

## Main architectural change

The public UI now has one source of truth:

```text
includes/ui-components.php   Shared navigation data, page hero and section heading
includes/header.php          One desktop navigation and one mobile drawer
includes/footer.php          One footer, mobile bottom navigation and contact dock
assets/css/phase127-design-system.css
assets/js/phase127-ui.js
```

Desktop navigation and mobile drawer are generated from the same PHP navigation array. A menu cannot be changed in one place and accidentally remain different elsewhere.

## Locked brand formula

```text
Deep Navy      #061a38
Navy           #08244c
Royal Blue     #1f5c9f
Logo Gold      #d8a62d
Light Gold     #efc75f
White / Light Blue backgrounds
Green          Completed/success only
Red            Error/danger only
```

Rules:

- Navigation, primary structure and strong actions use navy/blue.
- Active menu, current progress and premium highlights use logo gold.
- Green is used only for completed/success states.
- Red is not used as random decoration.
- Public page cards use one radius, border and shadow language.

## Common navigation

### Desktop

```text
Home
Learn
  Courses
  Practice Room
  Learning Roadmap
Test & Practice
  Weekly Test
  Quick Practice
  Revision
Student
  Admission
  Student Login / My Dashboard
About
  About Institute
  Gallery
  Student Reviews
Contact
```

### Mobile bottom navigation

```text
Home | Roadmap | Practice | Test | Account
```

The complete navigation remains available in the mobile drawer.

## Common page components

### Page hero

```php
wf_page_hero([
    'eyebrow' => 'Courses',
    'title' => 'Choose your English learning level.',
    'text' => 'Short supporting text only.',
    'icon' => 'fa-solid fa-book-open',
    'actions' => [
        ['label' => 'View Courses', 'url' => 'courses.php', 'icon' => 'fa-solid fa-arrow-right'],
    ],
    'steps' => ['Choose', 'Learn', 'Practice', 'Improve'],
]);
```

### Section heading

```php
wf_section_heading(
    'Popular Courses',
    'Choose the right starting point.',
    'Only important supporting text.',
    ['label' => 'View All', 'url' => 'courses.php']
);
```

New public pages should use these shared components instead of creating another hero/header/card language.

## Pages aligned with the approved Home design

The common header, footer, brand variables, buttons, forms, mobile navigation and shared card language now apply to all public pages that load `includes/header.php`:

- Home
- Courses
- Course Detail
- About
- Contact
- Admission
- Gallery
- Student Reviews
- Practice Room
- Learning Roadmap
- Roadmap Lesson
- Weekly Test
- Weekly Result
- Student Login/Register
- Student Dashboard
- Student Revision
- AI Teacher
- Quick AI Practice
- Faculty Profile

Important content-heavy pages retain their working logic, while Phase 127 provides the common visual shell and responsive rules.

## Student Reviews slider

Home Page reviews now use two continuous rows:

- First row moves right-to-left.
- Second row moves left-to-right.
- Both rows loop continuously.
- Hover pauses movement on desktop.
- Mobile speed is slower for readability.
- `prefers-reduced-motion` stops continuous animation for accessibility.
- The full Reviews page remains available through `reviews.php`.

No third-party slider library or jQuery is required.

## Project flow review

The previous project flow had too many entry points with inconsistent menu names. Phase 127 organizes it into these journeys.

### New student journey

```text
Home
  -> Courses
  -> Admission
  -> Student Login/Register
  -> Learning Roadmap
  -> Practice Room
  -> Weekly Test
  -> Dashboard / Result / Revision
```

### Existing student daily journey

```text
Student Dashboard
  -> Continue Roadmap
  -> Practice
  -> Weekly Test
  -> Result
  -> Revision of weak answers
```

### Guest journey

```text
Home
  -> View Courses
  -> Try Practice
  -> View Roadmap
  -> Admission / Student Login
```

### Content discovery journey

```text
About / Gallery / Reviews
  -> Courses
  -> Admission
```

This flow is technically sound for the current project. The next functional phase should connect Enquiry -> Admission -> Student -> Batch automatically in admin; Phase 127 does not alter that database process.

## Text and content rules

- One page must have one clear primary action.
- Hero text should stay short; long explanations belong below the fold.
- Student cards should use heading + one short status + one action.
- Mobile cards should not show full desktop descriptions.
- Duplicate explanations should not appear on Home, Roadmap and Test pages.
- Menu labels must remain exactly the same across desktop, mobile drawer and bottom navigation.

## Performance decisions

- No new UI framework was added.
- No jQuery or slider package was added.
- Shared CSS/JS is loaded once through the common header/footer.
- Production automatically uses `.min.css` through the existing asset helper.
- Deleted obsolete Phase 123–126 public UI patch files are listed in `DELETE_OLD_FILES_PHASE127.txt`.
- Service Worker cache version is updated to `v127`.
- Private dynamic pages remain excluded from public caching by the existing security rules.

## Installation

1. Back up current files and database.
2. Extract the Phase 127 replace-only ZIP in the website root and overwrite matching files.
3. Delete every path listed in `DELETE_OLD_FILES_PHASE127.txt`.
4. No database migration is required.
5. Clear browser cache and unregister/update the previous Service Worker.
6. Hard refresh with `Ctrl + Shift + R`.
7. Complete `PHASE127_TEST_CHECKLIST.md` on localhost/staging.

## Important deployment note

The source code passed PHP/JavaScript/CSS structural checks and ZIP integrity tests. The real production database, uploaded content and all physical mobile devices were not available in this environment, so final visual and functional QA should be completed on localhost/staging with actual data before replacing the live website.
