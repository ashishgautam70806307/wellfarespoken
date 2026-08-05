# Phase 58 - Restore hero stat cards without duplicates

Changes:
- Restored home hero cards like Daily and Basic+.
- Added PHP dedupe logic so duplicate Daily / Basic+ cards do not repeat.
- If no admin hero stats exist, frontend shows clean defaults:
  - Daily / Speaking Practice
  - Basic+ / Grammar to Fluency
- Hero image overlay dummy text remains removed.
- Mobile footer nav colors from Phase 57 preserved.
