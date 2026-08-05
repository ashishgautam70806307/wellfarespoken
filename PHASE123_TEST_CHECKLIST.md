# Phase 123 — UI, Responsive and Performance Test Checklist

## A. Deployment checks

- [ ] Existing website files and database backed up
- [ ] Replace-only package extracted in the correct website root
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_REMOTE_FONTS=false`
- [ ] Old `assets/css/phase122-test-center.css` deleted if present
- [ ] Browser cache cleared
- [ ] Old Service Worker unregistered or updated

## B. Test Center

Test at widths: **1440, 1024, 768, 430, 390, 360 and 320 px**.

- [ ] Hero heading does not overflow
- [ ] Four process steps remain readable
- [ ] Desktop shows three action cards
- [ ] Tablet shows two cards and full-width Results card
- [ ] Mobile shows compact cards with icon, heading, status and one action
- [ ] Long test title is clamped rather than overflowing
- [ ] Practice Test opens setup panel
- [ ] Weekly Exam opens setup panel for a logged-in student
- [ ] Guest Weekly Exam redirects to login
- [ ] Guest fields appear only for Practice Test
- [ ] Setup panel close button works
- [ ] Start button remains disabled until a valid paper is selected
- [ ] Results history opens and closes
- [ ] Result links open only the current student's records
- [ ] No horizontal page scrolling

## C. Learning Roadmap

- [ ] Desktop uses three-column level grid
- [ ] Tablet uses two-column level grid
- [ ] Mobile uses compact one-row level cards
- [ ] Long level heading is ellipsized/clamped
- [ ] Description is hidden on mobile
- [ ] Current level is visually highlighted
- [ ] Completed level shows completed icon
- [ ] Locked level cannot be opened
- [ ] Continue Level button points to the correct current lesson
- [ ] Summary counts fit at 320 px
- [ ] Learn → Practice → Complete → Unlock flow remains visible
- [ ] Reset progress confirmation works
- [ ] No horizontal page scrolling

## D. Shared public pages

Check Home, Courses, Course Detail, About, Contact, Admission, Login, Dashboard and Practice Room.

- [ ] Brand colors remain navy, gold and green
- [ ] Main headings wrap naturally
- [ ] Cards have consistent radius and spacing
- [ ] Course cards show 3/2/1 responsive columns
- [ ] Mobile course card is compact and readable
- [ ] About and Contact cards show 3/2/1 responsive columns
- [ ] Admission form is one column on mobile
- [ ] Input icons align correctly
- [ ] Login card is not sticky on mobile
- [ ] Student dashboard statistics show two columns on mobile
- [ ] Dashboard primary actions are full width on mobile
- [ ] Practice Room buttons do not overflow
- [ ] Font icons appear instead of broken squares

## E. Header and footer on lightweight pages

- [ ] Logo size is correct
- [ ] Desktop menu and dropdowns work
- [ ] Mobile menu opens and closes
- [ ] Mobile dropdowns expand
- [ ] WhatsApp floating action does not cover content
- [ ] Mobile bottom navigation scrolls horizontally
- [ ] Current mobile navigation item is highlighted
- [ ] Footer is readable at all widths
- [ ] Install Web App button behavior remains correct

## F. Performance checks

Open browser DevTools → Network with cache disabled.

### Test Center must load

- [ ] `phase123-shell.min.css`
- [ ] `phase123-ui-core.min.css`
- [ ] `phase123-test-center.min.css`
- [ ] It must **not** load `style.min.css`

### Learning Roadmap must load

- [ ] `phase123-shell.min.css`
- [ ] `phase123-ui-core.min.css`
- [ ] `phase123-roadmap.min.css`
- [ ] It must **not** load `style.min.css`

### General checks

- [ ] CSS and JS responses use compression when server modules are enabled
- [ ] Static assets return long cache headers
- [ ] Dynamic PHP pages are not served from Service Worker cache
- [ ] Images below the first screen load lazily
- [ ] No repeated Google Font request when `APP_REMOTE_FONTS=false`

## G. Browser checks

- [ ] Chrome Android
- [ ] Chrome Windows
- [ ] Edge Windows
- [ ] Firefox
- [ ] Safari/iPhone when available
- [ ] Installed PWA mode

## H. Final live checks

- [ ] Student registration/login/logout
- [ ] Student Dashboard
- [ ] Practice Room microphone flow
- [ ] Practice Test start/submit/result
- [ ] Weekly Exam start/submit/result
- [ ] Roadmap lesson open and completion
- [ ] Admission form submission
- [ ] Admin login and main pages
