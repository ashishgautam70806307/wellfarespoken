# Phase 175 Mobile Device Checklist

Test on a real Android Chrome device and, if available, iPhone Safari.

## Spoken Materials
1. Open `spoken-materials.php` at 320px, 360px, 390px and 430px widths.
2. Search `Hindi`, clear search, and confirm the four mode cards return.
3. Tap each mode once and confirm loading feedback appears immediately.
4. Confirm the chooser disappears after practice opens and Change mode returns to it.
5. Confirm Listen, Speak, Stop, Check, Clear, Previous and Next are all reachable without overlap.
6. Keep Continuous Voice Coach running for multiple sentences and confirm Phase 170 behavior remains intact.

## Practice Materials
1. Open `free-ai-english-practice.php` and confirm Quick Help is closed by default.
2. Open and close Quick Help; test translator/correction and microphone.
3. Select a lesson from the mobile selector and confirm it begins immediately.
4. Tap Start again to retry/reload the currently selected lesson.
5. Check MCQ and textarea questions.
6. Confirm Check Answer gives immediate busy feedback and returns after success/failure.
7. Confirm Previous / Check / Next remain above the bottom navigation.

## Learning Roadmap
1. Open `learning-roadmap.php` with fresh progress and existing progress.
2. Tap My Progress and confirm the current level is brought into view.
3. Tap All Levels and confirm the level list is reachable.
4. Confirm completed/current/locked states match backend/local progress rules.
5. Confirm the sticky Continue action opens the same level as the real roadmap Continue link.

## Common
1. Confirm Home / Materials / Practice / Tests / Roadmap bottom nav works with one tap.
2. Confirm no sticky action overlaps the bottom nav or browser safe area.
3. Rotate portrait/landscape and return to portrait.
4. Test slow network and offline transitions; the UI must recover instead of staying in an endless loading state.
