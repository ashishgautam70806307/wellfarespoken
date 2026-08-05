# CSS Architecture Rules

## Files

- `style.css`: legacy source used by pages not yet migrated.
- `style.min.css`: generated production version of the legacy source.
- `phase123-shell.css`: lightweight header, footer, buttons and mobile navigation.
- `phase123-ui-core.css`: shared professional responsive overrides.
- `phase123-test-center.css`: Test Center only.
- `phase123-roadmap.css`: Learning Roadmap only.

## Rules for future work

1. Do not append new page designs to `style.css`.
2. Create one page CSS file and register it through `$page_styles`.
3. Use `$lightweight_layout = true` only after the page works with `phase123-shell.css` and its own page CSS.
4. Use the existing CSS variables for navy, gold, green, spacing, radius and shadows.
5. Avoid `!important` unless overriding an unavoidable third-party inline rule.
6. Use mobile-first breakpoints and test at 320, 360, 390, 430, 768, 1024 and 1440 px.
7. Keep card headings to one or two lines and clamp optional descriptions.
8. On mobile, show one primary action and hide secondary explanatory text when necessary.
9. Use Font Awesome icons through fixed classes or `app_icon_html()`.
10. Regenerate `.min.css` files before deployment.

## Page setup example

```php
$page_styles = ['assets/css/my-page.css'];
$lightweight_layout = true;
require_once __DIR__ . '/includes/header.php';
```

Use lightweight mode only when all required legacy styles have been replaced for that page.
