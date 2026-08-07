# Phase 143 - Practice Logic Reset

Phase 143 is a focused stability and business-flow correction built cumulatively on Phase 142.

The phase does not add a database migration and does not redesign unrelated pages. It replaces the unstable practice orchestration on `spoken-materials.php` and simplifies the practice engine inside `roadmap-lesson.php` while preserving the existing material APIs, teacher-approved answers, roadmap access checks, student progress storage, and approved Roadmap Lesson visual direction.

## Why this phase was required

The previous spoken-practice implementation had too many behaviours coupled to the same screen:

- a filter form that was not required for the core student task;
- automatic loading during initial render and control changes;
- multiple request sources capable of updating the same workspace;
- speech synthesis, microphone and UI observers starting or restarting work automatically;
- many hidden item/form nodes instead of one active question;
- overlapping mobile phase scripts controlling the same page.

That made the page vulnerable to repeated requests, stale responses, blinking, long browser activity and difficult-to-predict state changes.

## New Spoken Materials flow

The page now follows one explicit flow:

1. Student chooses one practice mode.
2. The browser sends one list request.
3. One current question is rendered.
4. Listen starts only after tapping **Listen**.
5. Microphone starts only after tapping **Speak answer**.
6. Answer checking starts only after tapping **Check Answer**.
7. Student moves with **Previous** or **Next Sentence**.

There is no `practiceFilterForm`, no automatic background practice start, no continuous microphone loop, no automatic typing check and no page refresh.

### Modes

- Speak Daily
- Hindi to English
- English to Hindi
- Revision

Revision is student-specific. It requires login and loads the student's latest incorrect material-practice attempts instead of silently falling back to ordinary content.

## Roadmap Lesson practice correction

The approved `roadmap-lesson.php` structure and design remain intact. Its large inline practice engine was replaced with a page script that:

- reads a safely encoded JSON configuration;
- shows one question at a time;
- builds up to four unique answer choices without an endless random loop;
- speaks a question only after the student taps the sound control;
- prevents double-answer actions;
- keeps result feedback in normal document flow;
- preserves local guest progress and authenticated server progress;
- keeps lesson access and completion checks on the existing server endpoints.

## Reusable loading controls

`includes/header.php` and `includes/footer.php` now support page-level skip flags and late styles. These allow a problem page to opt out of older overlapping phase assets without removing those assets from other pages that still need them.

## Cache update

The service-worker cache namespace is now:

```text
wellfare-spoken-static-v143
```

Clear the old site data/service worker after deployment before testing.

## Database

No schema migration is included in Phase 143. Keep the existing canonical Phase 136 database and later cumulative data.

## Required real-environment test

Static validation passed, but MySQL-backed API execution, authenticated revision data, browser microphone permissions and real-device interaction must be verified using `PHASE143_BROWSER_TEST_CHECKLIST.md`.
