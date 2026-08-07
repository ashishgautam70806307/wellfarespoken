# Phase 146 Browser Test Checklist

## Cache reset

1. Unregister the old service worker.
2. Clear site data for localhost.
3. Reload with Ctrl+F5.

## Admin Login

- Open `admin/login.php` at desktop, tablet and 320px mobile widths.
- Confirm it remains one unified card.
- Confirm logo/name/subtitle render from Settings.
- Test invalid email, invalid password and valid login.
- Test password show/hide and Caps Lock warning.
- Confirm no horizontal scrolling.

## Weekly Test — guest

- Open `weekly-test.php` at 320, 360, 390 and 430px widths.
- Swipe through all three cards.
- Use both arrow controls and all dots.
- Confirm titles, statuses, meta values and buttons stay inside each card.
- Open Basic and Previous setup.
- Enter invalid and valid mobile numbers.
- Confirm native POST opens the secure test room for an available paper.
- Open Upcoming and confirm the login gate.

## Weekly Test — logged-in student

- Confirm selected card is visible after returning from login.
- Confirm verified-student block renders correctly.
- Start an available test and verify only one attempt is created.
- Confirm unavailable/scheduled papers remain disabled.
- Confirm result-history cards show date/time/score and open the correct result.

## Navigation and overlap

- Confirm bottom navigation never covers the Start button.
- Confirm no floating support button covers the carousel or setup form.
- Test browser back/forward after selecting each test type.
