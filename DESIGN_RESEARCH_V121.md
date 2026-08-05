# Design Research Applied in V121

## 1. Website and application are different experiences

The public pages are designed for discovery, trust, course comparison and admission. Student learning pages are designed as a task-focused application with persistent navigation, progress information and fewer distracting marketing blocks.

## 2. Responsive typography is fluid

Headings and spacing use `clamp()` so they scale continuously instead of jumping between many unrelated font sizes. Large display text is limited to hero areas; card and application headings use smaller, consistent levels.

## 3. Touch interactions are intentionally larger

Buttons, fields, tabs and mobile navigation are built around comfortable touch dimensions. Small labels remain secondary and are not used for essential instructions.

## 4. Content width is controlled

Long descriptions are kept inside readable content widths. Cards do not stretch text across the entire large desktop screen.

## 5. Learning flow is explicit

The homepage now explains the learning journey: level identification, pattern learning, daily speaking, testing and revision. The student application then uses the same sequence through Study, Roadmap, Practice, Test and Account navigation.

## 6. Mobile app navigation is limited

The previous oversized horizontally scrolling navigation has been replaced with five priority actions. Secondary learning pages remain available through the compact Learning App navigation.

## Reference standards used during planning

- Material Design responsive layout guidance
- WCAG 2.2 target-size guidance
- web.dev responsive typography guidance using `clamp()`, `min()` and `max()`
