# Phase 177 Visual QA

Reference goal: improve mobile usability without replacing the project's common header/footer or removing/changing existing page features.

Verified render targets:
- Spoken Materials browse: 390x844
- Spoken Materials active practice: 390x844
- Spoken Materials active practice short viewport: 320x455
- Practice Materials: 390x844
- Learning Roadmap: 390x844

Checks performed:
- Common header remains the real shared project header.
- Existing common bottom navigation remains present.
- No Phase175/176 alternate app header/navigation remains.
- No floating contact button overlaps answer, Check, Previous, Next, lesson, or roadmap controls.
- 320x455 short viewport moves bottom navigation into normal document flow.
- Spoken answer input remains readable and touch-friendly.
- Continuous Voice Coach, Listen, Speak, Stop, Check, Clear, Previous, Next remain visible in original source.
- Practice Translator, all three quick modes, microphone, all lesson buttons and question controls remain in original source.
- Roadmap current/completed/locked levels and progress settings remain in original source.
- No desktop layout rules were intentionally changed; Phase177 rules are scoped to max-width 760px.
