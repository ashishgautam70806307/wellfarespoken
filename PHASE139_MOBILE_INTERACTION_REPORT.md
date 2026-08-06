# Phase 139 Mobile Interaction Report

## Scope

Current base: Phase 138 Universal Mobile UX.

Phase 139 addresses the next mobile usability pass requested for Roadmap Lesson, Roadmap, Test Center, Weekly Exam, Spoken Practice, universal buttons, mobile form icons, compact inputs, public menu trigger, and dynamic footer social links.

## Implemented repairs

### Roadmap Lesson

- Converted the phone page into a bounded `100dvh` learning workspace.
- Compact progress bar, lesson title, and Learn/Practice/Finish tabs.
- Practice question and four answer choices fit as a two-by-two grid on normal phones.
- The active panel owns overflow when content is longer; the whole page does not need repeated top-to-bottom scrolling.
- Learn cards, pronoun data, word options, result feedback, and completion controls receive compact phone sizing.

### Spoken Practice

- Four modes are shown together in a two-by-two grid.
- Lesson, topic, optional search, and Start controls use a compact guided layout.
- When the AJAX practice app opens, the hero, heading, and selector area collapse from view.
- A compact Practice Active / Change control is inserted without modifying existing API IDs or form names.
- The current question, microphone actions, answer box, checking actions, and progress remain inside a focused mobile session frame.

### Weekly Test Center

- Basic, Previous, and Upcoming choices are displayed together in a three-column selector.
- Mobile-only short action labels prevent button overflow.
- Test setup opens directly below the selector with compact paper/student fields, security indicators, and Start Test action.
- Existing start endpoint, CSRF, login requirement, and test type selection remain unchanged.

### Weekly Exam Room

- Mobile exam app uses a full-height workspace.
- Timer and paper title remain visible in a compact topbar.
- Question navigator is compacted above the paper.
- Candidate detail, duplicate side-submit area, and long legend are hidden on the active phone workspace to prioritize the question.
- Question, four answer options, text answer, Previous, Next, Report, and Submit controls receive compact sizing.
- Start-test rules and summary are reduced to a readable phone card.

### Learning Roadmap

- Reduced hero copy and made Learn / Practice / Complete / Unlock visible together.
- Progress and four summary values use compact grids.
- Stage headers and individual level cards are shortened.
- Long descriptions are hidden on phones while title, items, points, level, lock state, and action remain available.

### Universal button correction

- Removed mobile ellipsis/cropping from reusable button labels.
- Added safe wrapping and minimum-width rules.
- Corrected course action labels such as View Details and Join Course.

### Form controls

- Mobile form control height reduced to 44px with balanced radius and padding.
- Leading icons reduced in size.
- Icons fade when the field is focused or contains text, preventing visual conflict with the entered value.
- Text remains 16px to avoid automatic iOS browser zoom.

### Mobile menu trigger

- Removed background, border, outline decoration, and shadow from the public hamburger trigger.
- Right-side drawer design and spacing from Phase 138 remain preserved.

### Dynamic social links

- Footer already reads social links dynamically from `site_settings`.
- Added a dedicated Admin -> Site Settings -> Social Media Links group.
- Added URL input types, placeholders, server-side URL normalization/validation, and Visible/Hidden previews.
- Saved Facebook, Instagram, YouTube, LinkedIn, and X URLs automatically show their footer icons; blank values remain hidden.

## Code strategy

- Added one reusable CSS layer: `assets/css/phase139-mobile-learning.css`.
- Added one small enhancement script: `assets/js/phase139-mobile-learning.js`.
- Existing Phase 138 files and backend workflows are preserved.
- No database schema change.
- Production minified CSS supplied.
- Service worker upgraded to v139.

## Static validation

- 68 PHP files: syntax PASS.
- 9 JavaScript/service-worker files: syntax PASS.
- 44 CSS files: parser PASS.
- 181 genuine literal local references: 0 missing.
- Duplicate literal IDs: 0.
- 32 service-worker assets: 0 missing.
- Phase 139 public/admin/login/exam/social integration checks: PASS.

## Remaining real-environment checks

- Actual 360px, 390px, 412px and 430px devices.
- Android Chrome and iPhone Safari keyboard behavior.
- Logged-in roadmap progress and locked-level flow.
- Spoken microphone permissions, recognition, auto-listen and repeat commands.
- Weekly Test start, autosave, timer, submit and result.
- Real social profile links opened from the footer.
