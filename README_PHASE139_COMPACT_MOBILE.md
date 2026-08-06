# Phase 139 - Compact Mobile Learning Workspace

Phase 139 is a cumulative compatibility-first update built on Phase 138. It does not change database schema, student authentication, roadmap progression, weekly-test grading, material APIs, or admin business logic.

## Purpose

The update applies an interaction-first phone pattern to learning pages: students should see the current choice, question, answer options, and primary action in one screen whenever practical. Longer learning content remains available through an internal panel scroll instead of forcing the student to repeatedly scroll the whole page.

## Main changes

- `roadmap-lesson.php` uses a viewport-sized mobile lesson frame.
- Four practice answers use a compact two-by-two grid.
- Learn, Practice, and Finish panels scroll internally only when content cannot fit.
- Spoken Practice modes use a visible two-by-two layout instead of horizontal mode scrolling.
- Starting Spoken Practice switches the page into a focused one-sentence session and adds a compact Change control.
- Weekly Test shows Basic, Previous, and Upcoming choices together in a three-column mobile selector.
- Weekly Test setup fields and security indicators are compacted below the selected test.
- Weekly Exam Room keeps the timer, navigator, question, answer, and navigation controls inside a phone workspace.
- Learning Roadmap hero, process, statistics, stage headers, and level cards are shortened without changing progression logic.
- Mobile button labels no longer use ellipsis or crop text such as View Details.
- Form icons shrink and fade after focus or typing so entered text stays visually clear.
- Mobile input controls use a smaller 44px control height while preserving 16px text to avoid iOS input zoom.
- The public mobile menu trigger now has no background, border, or shadow.
- Social links have a dedicated Admin Settings group with URL validation and Visible/Hidden status previews.
- Footer Facebook, Instagram, YouTube, LinkedIn, and X icons remain fully dynamic: saved URL shows the icon; blank URL hides it.
- Service-worker cache namespace updated to `wellfare-spoken-static-v139`.

## Installation

1. Back up files and database.
2. Replace the project with the cumulative Phase 139 package, or apply the replace-only ZIP over Phase 138.
3. No SQL import is required for this phase.
4. Open Admin -> Site Settings -> Social Media Links and save the real profile URLs.
5. Clear the localhost/browser service worker and perform Ctrl + F5.
6. Complete `PHASE139_BROWSER_TEST_CHECKLIST.md` on real mobile widths and the real database.

## Important limitation

Static source validation is complete. A MySQL-backed authenticated browser, microphone session, iOS keyboard, and real touch-device rendering were unavailable in the delivery environment and remain real-environment checks.
