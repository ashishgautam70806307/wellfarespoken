# Phase 123 — Professional UI, Responsive Cards and CSS Performance

## Purpose

This phase keeps the existing Well Fare English Spoken navy, gold and green identity while improving clarity, responsive layout and frontend loading.

The main focus is:

- Test Center clarity
- Learning Roadmap clarity
- Mobile headings and compact cards
- Shared professional UI rules for public/student pages
- Safer CSS organization for future changes
- Smaller CSS downloads on Test and Roadmap pages

No database migration is required for this UI phase.

---

## Main improvements

### 1. Test Center redesign

The first screen now presents only three clear actions:

1. Practice Test
2. Weekly Exam
3. My Results

The setup form stays hidden until the student selects a test card. This prevents the page from showing cards, instructions and form fields at the same time.

Mobile behavior:

- Compact horizontal cards
- Short heading and status
- One primary action per card
- Reduced metadata
- Setup panel opens after selection
- Results remain collapsed until requested

### 2. Learning Roadmap redesign

The old narrow app-style layout was replaced with a responsive stage layout.

- Desktop: three level cards per row
- Tablet: two level cards per row
- Mobile: one compact horizontal level card
- Current, completed and locked states use distinct icons and colors
- Long descriptions are clamped on desktop and hidden on mobile
- The main action becomes an arrow button on narrow screens
- Progress, points and item counts remain visible without dominating the page

### 3. Shared professional UI layer

`assets/css/phase123-ui-core.css` provides controlled overrides for:

- Home cards
- Course catalogue
- Course detail
- About
- Contact
- Admission
- Student login
- Student dashboard
- Practice Room
- Admin surface rhythm

The existing design is preserved. New code should be added to a page-specific CSS file rather than appended to the large legacy stylesheet.

### 4. Lightweight CSS shell

Test Center and Learning Roadmap use:

- `phase123-shell.min.css`
- `phase123-ui-core.min.css`
- Their own page-specific minified CSS

They do not load the legacy `style.min.css` file.

Approximate production CSS payload, excluding external Font Awesome:

| Page | CSS files | Approximate total |
|---|---|---:|
| Test Center | Shell + Core + Test | 41 KB |
| Learning Roadmap | Shell + Core + Roadmap | 38 KB |
| Legacy public pages | Legacy minified + Core | 448 KB |

This is a staged refactor. The legacy stylesheet is retained for older pages to avoid breaking their existing layout. Future page redesigns should be moved to lightweight page bundles one by one.

### 5. Asset loading improvements

- Production automatically uses `.min.css` files.
- CSS and JavaScript URLs include file modification versions.
- Apache compression and long-term static cache rules were added.
- Service Worker pre-cache is now small and does not download the large legacy CSS during installation.
- Dynamic student, admin, result and API pages remain network-only.
- Below-the-fold images use lazy loading where applicable.
- Google Inter font is disabled by default for faster loading.
- System fonts are used unless `APP_REMOTE_FONTS=true` is set.

### 6. Font icon consistency

Prominent public/student actions were converted from emoji to Font Awesome icons, including:

- Course badges
- About highlights
- Admission benefits and form fields
- Course detail checks
- Practice Room actions
- Test Center
- Learning Roadmap

Dynamic content icons are passed through a fixed icon mapping helper rather than rendered as arbitrary HTML.

---

## CSS file structure

```text
assets/css/
├── style.css                         Legacy source stylesheet
├── style.min.css                     Production legacy bundle
├── phase123-shell.css                Lightweight header/footer/base shell
├── phase123-shell.min.css
├── phase123-ui-core.css              Shared professional responsive layer
├── phase123-ui-core.min.css
├── phase123-test-center.css          Test Center only
├── phase123-test-center.min.css
├── phase123-roadmap.css              Learning Roadmap only
└── phase123-roadmap.min.css
```

See `assets/css/README.md` before adding future CSS.

---

## Environment setting

Recommended production value:

```env
APP_ENV=production
APP_DEBUG=false
APP_REMOTE_FONTS=false
```

Set `APP_REMOTE_FONTS=true` only when Google Inter must be loaded. The default system-font mode is faster and still uses a professional platform-native font.

---

## Installation

1. Back up the current website files and database.
2. Upload and extract the replace-only ZIP in the website root.
3. Overwrite matching files.
4. Delete the unused old file if it remains:

```text
assets/css/phase122-test-center.css
```

5. Update `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_REMOTE_FONTS=false
```

6. Confirm these Apache modules are enabled when available:

```text
mod_headers
mod_deflate
mod_expires
```

7. Clear browser cache and update/remove the old Service Worker once.
8. Test all breakpoints using `PHASE123_TEST_CHECKLIST.md`.

---

## Important cache note

After replacing CSS or `sw.js`, the old PWA can keep old files temporarily.

Use one of these methods:

- Browser DevTools → Application → Service Workers → Unregister
- Clear site data/cache
- Hard reload the website
- Close and reopen an installed PWA

The new file-version query strings and Service Worker cache name will then load the current assets.

---

## Validation completed

- 68 PHP files passed PHP syntax lint.
- `sw.js` passed JavaScript syntax check.
- `assets/js/main.js` passed JavaScript syntax check.
- All generated minified CSS files were parsed again after minification.
- ZIP integrity is checked before delivery.

A live MySQL database, production hosting and unrestricted visual browser were not available in this environment. Complete the supplied device and workflow checklist on staging before replacing the live website.
