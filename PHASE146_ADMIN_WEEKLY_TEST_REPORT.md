# Well Fare English Spoken — Phase 146 Report

## Completed work

### Admin Login

The previous login screen was replaced with a professional single-card control-centre layout. The card contains an integrated navy brand area and a clean credential area rather than two disconnected cards. Dynamic institute branding and the configured admin-login description remain in use.

Preserved security behaviour:

- CSRF validation
- Honeypot protection
- Per-email rate limiting
- Password hashing verification
- Session regeneration
- No-store/no-cache response headers
- Remaining-attempt display
- Caps Lock warning
- Password visibility control

The page no longer loads the universal public mobile stylesheet. It now loads only design tokens, Font Awesome and its dedicated Phase 146 stylesheet.

### Weekly Test mobile repair

The principal cause of the broken phone layout was competing cumulative CSS. Phase 139 and Phase 141 both forced the same cards into different compact grid patterns, while Phase 145 attempted to convert them into a slider. The page now skips those older learning-page styles/scripts only for `weekly-test.php`, then loads one final page-owned stylesheet after the cumulative stack.

Mobile improvements:

- Compact readable Test Center hero
- One test card per swipe rather than three squeezed cards
- Stable card width at 320px and larger phones
- Separate short mobile status labels
- Readable titles, descriptions, duration and question metadata
- Small edge arrows and position dots
- Card text and buttons remain inside the card
- Setup form has a clean one-column phone layout
- Guest fields, verified-student state and paper selector are bounded
- Auto-save/timer/result indicators cannot overflow
- Login-required gate is responsive
- Result-history cards are swipeable and readable
- Floating support control is hidden on this page at mobile widths to avoid covering actions
- Bottom-navigation clearance is maintained

The carousel script now uses each card's real offset instead of assuming a fixed width. It also responds to resize/layout changes and preserves the selected card.

## No business-logic change

The following were not changed:

- Test availability rules
- Guest Basic/Previous access
- Upcoming/login-required access
- CSRF handling
- Native secure POST to `weekly-test-api.php`
- Attempt creation
- Timer/scoring/result logic
- Student result history query
- Database schema

## Validation

- PHP syntax: 67 files passed
- JavaScript syntax: 12 asset files plus `sw.js` passed
- CSS parse: 60 stylesheets passed
- Duplicate literal IDs in changed pages: none
- Service-worker assets: all present
- Service-worker cache namespace: v146

## Runtime limitations

A live MySQL database, authenticated browser session and physical mobile device were not available in this environment. Final checks should therefore include login, actual attempt creation, 320/360/390/430px swipe behaviour and browser back/forward navigation on localhost/staging.
