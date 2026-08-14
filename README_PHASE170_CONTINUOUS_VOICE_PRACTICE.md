# Phase 170 - Continuous Voice Practice Recovery

Phase 170 is a focused repair on top of Phase 169 for `spoken-materials.php`.

## Why the voice flow could stop

1. The page loaded only 20 sentences. After the 20th sentence, the client intentionally entered the completion state.
2. Android/Chrome speech recognition can end with `no-speech`, temporary `network`, or `aborted` events. The old hands-free flow showed an error and stopped instead of reopening the microphone.
3. Moving the browser to the background, locking the phone, switching apps, or returning from another page triggered voice cleanup but there was no resume path when the page became visible again.
4. An answer-check request had no client timeout. A slow or hanging request could leave the voice flow waiting too long.
5. The practice endpoint used a relatively small rate bucket for a hands-free loop and wrote a dashboard activity row for every wrong retry.

## Phase 170 behavior

- Normal practice is continuous. It loads 20 lightweight rows at a time and automatically loads the next set. When the end of the published collection is reached, the next round starts from the beginning instead of stopping. Revision mode can still finish when there are no more saved wrong answers.
- Correct voice answer -> next sentence automatically.
- Wrong voice answer -> correct answer is played -> same sentence opens the microphone again.
- No speech / temporary recognition network interruption -> microphone automatically retries.
- Several consecutive microphone recoveries -> the question is read again and the voice cycle restarts.
- App/tab becomes hidden -> voice resources are cleaned up. Returning to the page -> Voice Coach resumes automatically unless the student explicitly pressed Stop.
- Student pressing Stop creates an intentional paused state so automatic recovery does not fight the student's choice.
- Answer checks use a 12-second watchdog. A transient failure returns to the same sentence and voice practice continues.
- A 419 session/CSRF response refreshes the practice token and retries.
- Practice rate limiting remains enabled, but logged-in students now have enough room for a long hands-free learning session.
- All retries are still stored in `material_practice_attempts`; only successful practice milestones are added to the general student activity stream to keep long sessions lightweight.

## Files changed

- `spoken-materials.php`
- `assets/js/phase170-spoken-practice.js` (new, based on the Phase 169 controller)
- `material-practice-list-api.php`
- `material-practice-api.php`
- `includes/functions.php`
- `sw.js`
- `tests/run_phase170_static.php`

No database schema change is required.
