# Phase 154 — Mobile Final Submit + Home YouTube Slider

## Scope
This phase is intentionally narrow and cumulative on top of Phase 153. No database schema, test scoring, RBAC, registration, roadmap, practice, voice-coach, or Admin business logic was changed.

## 1. Weekly exam mobile Final Submit regression

### Root cause
`weekly-exam-room.php` loaded the old Phase 139 mobile learning stylesheet after its inline exam rules. On phone widths, the old stylesheet hid `.exam-side-submit`, while the inline exam rules also hid `.submit-near-answer`. This left mobile students with no visible final submission control.

### Fix
- Added a final page-owned stylesheet `assets/css/phase154-exam-mobile.css` after Phase 139.
- The paper action bar now keeps **Previous / Next / Report / Final Submit** visible on phones.
- **Final Submit** is a high-contrast gold CTA and opens the existing confirmation modal before using the existing secure submit API.
- The old sidebar submit stays hidden on compact screens to avoid duplicate controls; the visible paper action bar is the canonical mobile control.
- 320–360px layouts use compact two-column actions without clipping.
- 420px and below use a single-column start/cancel entry layout and a more touch-friendly five-column question palette.
- The submit confirmation modal is viewport bounded and scrollable on small devices.
- Removed one stray closing `</div>` in the exam entry markup.

No answer saving, timer, warning, attempt ownership, scoring, or finalization logic changed.

## 2. Mobile action audit
A static action-visibility audit was performed across the primary student flows:

- Student Login / Registration — submit + password show/hide controls present.
- Admission — Submit Enquiry present.
- Spoken Materials — Voice Coach, Listen/Speak, Check Answer, Previous, Next present.
- Learning Roadmap — Continue/Open Level actions present.
- Roadmap Lesson — practice continue/complete controls remain governed only by their real state.
- Weekly Test Center — Login/Start Test actions present.
- Weekly Exam Room — **Final Submit restored and guaranteed visible on mobile**.
- Weekly Result — Test Center, Dashboard and Logout actions present.
- Student Dashboard — Practice, Roadmap, Weekly Test and Logout actions present.

The only critical hidden-action conflict found in this pass was the weekly exam final-submit conflict fixed above. Optional explanatory text/search elements that are intentionally hidden on very small screens were not converted into required actions.

## 3. Dynamic YouTube Videos restored to Home
The existing Admin `Videos` module and published `videos` table were already present but the current Home page did not render them.

Home now:
- fetches up to 8 published YouTube videos dynamically;
- uses Admin Settings `home_videos_title` and `home_videos_subtitle`;
- shows **one video card at a time**;
- supports swipe, left/right arrows, keyboard arrows and position dots;
- works on desktop, tablet and mobile;
- uses a YouTube thumbnail first and loads the actual iframe **only after Play is clicked** for faster initial page loading;
- stops/removes the previous embedded player when the student changes slide;
- optionally shows a dynamic YouTube Channel link from Site Settings.

No new YouTube API key or external JavaScript library is required.

## Performance notes
- YouTube iframes are not loaded during initial Home render.
- Thumbnails use lazy loading and async image decoding.
- The video slider reuses the existing Home JavaScript file and theme CSS instead of adding a slider library.
- The exam fix is a tiny page-specific stylesheet and does not alter other pages.

## Cache
Service Worker cache namespace: `wellfare-spoken-static-v154`.
