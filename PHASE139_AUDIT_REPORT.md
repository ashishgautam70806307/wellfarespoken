# Phase 139 Audit Report

## Problems confirmed

1. Mobile Roadmap Lesson kept too much lesson chrome above Practice, so a student selected a tab and then had to continue scrolling before answering.
2. Four practice answers were not consistently kept in one compact decision frame.
3. Weekly Test selection left the hero and all test cards above the setup form, producing the same select-then-scroll problem.
4. Weekly Exam Room prioritized side information before the active question on small screens.
5. Spoken Materials used too much vertical space for mode selection, progress and actions.
6. Shared CTA rules could clip labels such as `View Details` because the label was constrained while the decorative action circle still occupied the right side.
7. Leading input icons were visually too large and focus/filled states could scale them, making typed text feel crowded.
8. Mobile menu trigger retained a tile-style background/border from older CSS.
9. Footer supported dynamic social links, but the public list did not include X and needed an explicit five-network verification.
10. The previous regex-style CSS minification removed required descendant whitespace before selectors such as `:is(...)`. This silently prevented some high-specificity mobile input rules from matching in production minified CSS.

## Corrections

- Practice, test and material workspaces now use compact mobile-only arrangements with the current decision/action close to the current content.
- Long content is allowed to grow; it is not clipped merely to force an artificial no-scroll screen.
- Shared CTA labels use normal wrapping/visible overflow rules and were measured in Chromium at mobile width.
- Input controls use a fixed icon rail and smaller icon size; focus changes color without scaling.
- Mobile input/select target is 46px while entered text remains 16px to avoid iOS auto-zoom.
- Menu trigger is transparent and borderless but keeps a 40px touch target.
- Footer reads Facebook, Instagram, YouTube, LinkedIn and X from Admin Settings and hides blank values.
- Phase 139 replaces the Phase 138 UI/materials layer rather than stacking another override.
- Production CSS is minified through an AST-safe process that preserves meaningful selector whitespace.

## Preserved

- Approved desktop layout and Admin Login design.
- Form names, IDs, actions and JavaScript/API hooks.
- Weekly Test security, autosave, timer, result and ownership logic.
- Roadmap progress and Spoken Materials backend contracts.
- The single canonical SQL file.
