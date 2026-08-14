# Phase 169 - Admin Materials, Roadmap, Weekly Copies and Spoken Voice Flow

This cumulative phase is built on Phase 168 and preserves the Phase 168 Weekly Test/Batch reliability repairs.

## Admin Materials
- Reworked into an easy dependency flow: **Create Category -> Create Topic -> Upload -> Add Sentence -> Manage**.
- Category is created first, then selected from a dropdown before creating a topic.
- Upload and manual sentence entry require a valid Category + Topic combination.
- Normal bulk upload is locked to the selected topic so an accidental Topic/Tense cell cannot scatter rows; advanced admins can explicitly allow row-level topics.
- Backend verifies the selected topic belongs to the selected category before save/import.
- Optional sentence metadata is collapsed under Advanced settings to keep normal entry small.

## Admin Roadmap
- **Create Step / Topic** and **Add Record** are compact cards by default.
- Clicking a card expands the existing full form.
- Edit URLs automatically open the correct card, preserving the existing editing flow.

## Admin Weekly Tests
- Removed the extra 3-step management guide card.
- The existing Upload Questions section still contains the 3-example Excel and blank Excel downloads.
- Student Answer Copies cards now have clearer student identity, scope, counts, last activity and Open Copies action.

## Admin Navigation
- **Batch Timing Management** is now directly after **Enquiries** in Main navigation.
- The active Admin menu item is automatically centered inside the sidebar scroll area on page load and when the mobile drawer opens.

## Spoken Materials Frontend
- Added focused mobile layout safety for the practice workspace and fixed bottom mobile navigation stacking/touch area.
- Voice Coach mastery loop:
  - Correct voice answer -> confirmation -> automatic next sentence.
  - Wrong voice answer -> correct sentence is spoken -> microphone opens again on the same sentence.
  - Manual typing/checking keeps the existing manual Next behavior.
- Existing repeat commands and manual Listen/Speak controls remain available.

## PWA
- Service worker cache bumped to v169.
- New Phase 169 CSS assets are pre-cached.
- Removed four stale banner paths from the pre-cache list because those files are not present in the cumulative project.
