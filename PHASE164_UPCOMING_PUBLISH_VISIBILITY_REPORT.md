# Phase 164 — Upcoming Test Publish / Visibility Repair

Date: 2026-08-12
Base: Phase 163

## Scope

Focused only on the Weekly Test publish → student visibility → secure start → exam submit flow. No database schema, scoring, answer-release, ranking, offline-paper, dashboard-theme or unrelated module logic was changed.

## Root cause confirmed from the reported screenshots

Admin correctly had a batch-specific Upcoming paper in `Published / Active` state with active questions. On the student Test Center, Phase 163 removed every batch-denied Upcoming paper from `$testPools['upcoming']`. When the logged-in account had no recognized matching membership/admission, the frontend therefore displayed **No paper / 0 questions**, which made a real published paper look missing.

That behavior was misleading and made diagnosis difficult.

## Repairs

### 1. Published Upcoming papers are no longer silently hidden

For logged-in students, Upcoming papers are now divided into:

- eligible papers — first in the list;
- locked papers — still visible, but start remains disabled.

The card now shows:

- `Exam open` when the paper is ready and the student is eligible;
- `Batch access needed` when the paper is published for another/unlinked batch;
- `Temporarily locked` for the anti-repeat/cooldown account lock;
- the existing schedule/pending states otherwise.

The secure API still refuses any batch-denied/cooldown-denied start. This is a visibility/flow repair, not a security bypass.

### 2. Safe batch lifecycle sync on publish

When a batch-specific Upcoming paper is activated/published, the project now re-syncs admissions that are already explicitly linked to a student account and the same batch. This repairs older lifecycle membership rows without touching admission data.

It deliberately does **not** auto-link an unverified phone-only student account to an admission. Those accounts still require the existing Admin → Student Accounts → Upcoming Test Batch Access / verified-identity flow.

### 3. Secure start re-check after DB row lock

A new Upcoming start now re-fetches the full test row `FOR UPDATE` and rechecks:

- Active/Published state;
- start time;
- end time;
- batch authorization.

This closes a race where Admin could press **Close Entry** at almost the same time a student start request was being processed.

### 4. Final-submit loop guard

The exam room now has one final-submit guard and explicit timer/autosave cleanup. When time reaches zero or the student presses Final Submit, repeated timer ticks/double-clicks cannot fire multiple parallel final-submit requests while the first response is pending.

If a network submit fails, the guard is released and the timer/autosave loops resume safely.

## Expected live behavior

### Student belongs to the published batch

`Publish Now` → Test Center shows `Exam open` → open Upcoming → Start Test enabled.

### Student account is not linked to that batch

The published paper is visible instead of `No paper`, but shows `Batch access needed`. Admin should use:

`Admin → Student Accounts → Student → Upcoming Test Batch Access`

and select the same batch. This preserves batch-wise result/ranking security.

### Student completed another Upcoming test too recently

The paper remains visible but shows `Temporarily locked` and the setup displays the exact cooldown message/time.

## Regression checks

- Basic Test unchanged.
- Previous Test unchanged.
- Upcoming batch isolation preserved.
- Common / All Batches behavior preserved.
- one-attempt-per-paper protection preserved.
- anti-repeat gap preserved.
- existing started attempt resume preserved.
- Close Entry / Finalize Top 3 behavior preserved.
- master-answer release safety unchanged.
- offline Student Paper / Answer Key unchanged.

## Validation

- PHP syntax: 112 files passed.
- JavaScript/Service Worker syntax: 16 files passed.
- Phase 148/149/150/151/158/159/160/161/162/163/164 static suites passed.
- Phase 164 focused checks: 20 passed.
- Service Worker cache: v164.

Real MySQL-backed batch-membership data and live authenticated student sessions are not available in the build environment, so the final live account/batch mapping must be verified on localhost/staging using the Phase 164 checklist.
