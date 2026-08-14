# Phase 170 Mobile Voice Checklist

Use this short checklist on the live server after upload:

1. Open `spoken-materials.php` on Android Chrome and allow microphone permission.
2. Select **Speak Daily** or **Hindi to English** with **Continuous Voice Coach** enabled.
3. Give 3 correct answers: each should move to the next sentence automatically.
4. Give 2 wrong answers: the same sentence should stay open, play the correct answer, and listen again.
5. Stay silent once: the microphone should reopen automatically instead of stopping the session.
6. Switch to another app for 5-10 seconds and return: the Voice Coach should resume on the same sentence.
7. Continue past sentence 20: sentence 21 should load without showing Practice Complete.
8. Temporarily disconnect/reconnect internet during an answer check: the same sentence should recover instead of leaving the UI stuck on Checking.
9. Press **Stop**: automatic recovery should remain paused until **Speak answer** is tapped or Voice Coach is toggled.
10. Confirm the student's practice attempts still appear in tracking/revision data.
