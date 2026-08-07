# Well Fare English Spoken - Phase 143 Practice Logic Report

## Scope

This phase repairs only the practice-related instability reported in:

- `spoken-materials.php`
- the Practice tab of `roadmap-lesson.php`

It also introduces safe page-asset isolation so old mobile/practice layers do not compete with the corrected page logic.

## Root-cause analysis

### Spoken Materials

The unstable behaviour was not a single CSS problem. It was caused by orchestration complexity:

1. A filter form wrapped controls that did not need native form submission.
2. Initial load, mode changes, filters, search and observers could all trigger new practice work.
3. Older requests were able to complete after newer requests and update the screen out of order.
4. Speech voices and hidden-state observers could restart activity.
5. Speech recognition was previously capable of repeated lifecycle activity.
6. Multiple hidden question/form blocks increased DOM work and state duplication.
7. Several cumulative mobile scripts and styles targeted the same page.

The combination could present as blinking, controls apparently not working, continuous browser activity or the wrong practice state appearing.

### Roadmap Lesson practice

The practice page design was already approved, but its inline engine had maintainability and interaction risks:

- a large script embedded in PHP;
- a large question payload stored in an HTML data attribute;
- random option generation that was harder to reason about;
- automatic question speech;
- feedback positioning inherited from multiple phase styles;
- excessive celebration nodes.

## Implemented corrections

### `spoken-materials.php`

- Removed `practiceFilterForm` completely.
- Removed lesson-group, topic and search controls from the primary practice journey.
- Replaced the previous page engine with four direct mode buttons.
- Added explicit Ready, Loading, Error and Active states.
- Rendered only one current question.
- Kept Listen, microphone, checking and navigation under direct student control.
- Removed inline JavaScript.
- Opted the page out of conflicting Phase 139/141/142 practice assets.
- Loaded one page-scoped late stylesheet and one dedicated script.

### `assets/js/phase143-spoken-practice.js`

- One list request per mode selection.
- `AbortController` cancels stale list and answer-check requests.
- Request-version guards prevent late responses from overwriting the current mode.
- Speech recognition uses one-shot mode (`continuous = false`).
- No `onend` restart loop.
- A microphone timeout prevents an open recognition session from running indefinitely.
- Speech synthesis is explicitly cancelled before a new utterance and during cleanup.
- No automatic microphone start, speech start or typing check.
- Cleanup runs on mode changes, page hide and document visibility changes.

### `material-practice-list-api.php`

- Normal modes continue to read published teacher-approved translation pairs.
- Revision mode now reads the authenticated student's latest incorrect attempts.
- Revision no longer returns ordinary material when the student is not logged in.
- The API returns `requires_login` so the page can show the correct action.
- No table/schema change was made.

### `roadmap-lesson.php`

- Preserved approved page markup and visual design.
- Removed the old inline practice engine.
- Replaced the large data attribute with a safely encoded JSON configuration block.
- Loaded a dedicated external roadmap-practice script.
- Preserved unit access checks, prerequisites, completion endpoint and local guest progress.

### `assets/js/phase143-roadmap-practice.js`

- One question at a time.
- Up to four unique options are assembled with bounded array logic.
- No indefinite choice-generation loop.
- Audio runs only after a sound-button tap.
- Repeated answer clicks are locked.
- Continue/complete actions are guarded against duplicate requests.
- Celebration output was reduced to lightweight particles.

### `assets/css/phase143-roadmap-practice.css`

- `.duo-result-box`, including `.bad` and `.ok`, uses normal document flow.
- Feedback cannot cover the answer grid.
- The mobile answer grid remains compact without hiding labels.

### Shared asset isolation

- `includes/header.php` supports page-specific skip flags for cumulative phase styles.
- It supports `$page_late_styles`, loaded after shared phase CSS.
- `includes/footer.php` supports skipping the obsolete Phase 139 learning script per page.

## Business-flow outcome

### Spoken Practice

```text
Choose mode -> fetch a small published/student-specific set -> show one question
-> optional Listen/Speak -> explicit Check -> Previous/Next
```

### Roadmap Practice

```text
Open permitted lesson -> Learn -> Start Practice -> answer one question
-> in-flow feedback -> Continue -> Finish -> save/unlock through existing server logic
```

## Files added

- `assets/css/phase143-practice-stability.css`
- `assets/css/phase143-practice-stability.min.css`
- `assets/css/phase143-roadmap-practice.css`
- `assets/css/phase143-roadmap-practice.min.css`
- `assets/js/phase143-spoken-practice.js`
- `assets/js/phase143-roadmap-practice.js`

## Files changed

- `spoken-materials.php`
- `roadmap-lesson.php`
- `material-practice-list-api.php`
- `includes/header.php`
- `includes/footer.php`
- `sw.js`
- project documentation and validation files

## Static validation result

- 68 PHP files passed syntax validation.
- 11 JavaScript/service-worker files passed syntax validation.
- 54 CSS files passed parser validation.
- 183 literal local asset/link references were checked with no missing target.
- No duplicate literal HTML IDs were found.
- All 39 service-worker precache targets exist.
- Phase-specific regression checks passed.

## Honest runtime limitations

The execution environment did not provide:

- a running MySQL/MariaDB server for API/database flow tests;
- a reliable interactive browser session for microphone permission and real clicks;
- an authenticated student's historical wrong-answer data.

These items are **pending**, not marked as passed. Use the included browser checklist on localhost/staging.
