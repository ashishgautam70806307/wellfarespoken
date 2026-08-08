# Well Fare Reusable Design System

This project now has a central UI foundation. New page CSS should not redefine brand colors, cards, buttons, inputs, badges, alerts or common spacing.

## Files

- `assets/css/wf-design-tokens.css` — brand colors, spacing, radius, shadows, control heights and font tokens.
- `assets/css/wf-components.css` — reusable cards, buttons, form fields, badges, alerts, grids, tables and responsive rules.
- `includes/ui-components.php` — reusable PHP render helpers.
- `admin/ui-library.php` — internal component preview retained for developers, intentionally hidden from the normal Admin navigation.

Both public and admin headers load these files. Production automatically loads the `.min.css` copies.

## Design rule

1. Use a shared component first.
2. Use a component variant when the visual purpose is different.
3. Create page-specific CSS only for layout or behavior that is truly unique to that page.
4. Never copy the same button, card or field CSS into another page file.
5. Never hard-code brand colors inside page CSS. Use design tokens.

## Brand tokens

```css
color: var(--wf-color-navy-900);
background: var(--wf-color-gold-500);
border-color: var(--wf-color-line);
box-shadow: var(--wf-shadow-sm);
border-radius: var(--wf-radius-lg);
gap: var(--wf-space-4);
```

To update the total project theme later, change the values in `wf-design-tokens.css` only.

## Buttons

Preferred PHP usage:

```php
<?= wf_button('Save', '', 'primary', 'fa-solid fa-floppy-disk', [
    'type' => 'submit',
    'size' => 'sm',
]) ?>
```

Available variants:

- `primary`
- `secondary`
- `gold`
- `success`
- `danger`
- `ghost`
- `link`

Available sizes: `sm`, `md`, `lg`.

Direct HTML is also supported:

```html
<button class="wf-btn wf-btn--primary" type="submit">Save</button>
```

## Cards

PHP template usage:

```php
<?php wf_card_start([
    'title' => 'Student Details',
    'text' => 'Update the main profile information.',
    'variant' => 'default',
    'size' => 'md',
]); ?>
    <p>Card body</p>
<?php wf_card_end(); ?>
```

Variants: `default`, `soft`, `gold`, `navy`, `flat`.

Direct HTML:

```html
<section class="wf-card wf-card--gold">
    <div class="wf-card__body">Featured content</div>
</section>
```

## Form fields

```php
<?= wf_form_field([
    'name' => 'student_name',
    'label' => 'Student Name',
    'placeholder' => 'Enter full name',
    'required' => true,
    'full' => true,
]) ?>
```

Supported types: text, email, tel, number, date, time, password, URL, search, file, textarea and select.

```php
<?= wf_form_field([
    'name' => 'course',
    'label' => 'Course',
    'type' => 'select',
    'value' => $course,
    'options' => [
        '' => 'Select course',
        'Basic English Spoken' => 'Basic English Spoken',
        'Interview Training' => 'Interview Training',
    ],
]) ?>
```

## Badges and alerts

```php
<?= wf_badge('Active', 'success', 'fa-solid fa-check') ?>
<?= wf_alert('Saved', 'The record has been updated.', 'success') ?>
```

## Page-specific CSS rule

A page file may add classes such as `.weekly-exam-question` or `.roadmap-node` because those components are unique. It should not redefine `.wf-btn`, `.wf-card`, input fields or brand colors.

## Backward compatibility

Legacy classes such as `.btn`, `.btn-primary`, `.panel-card`, `.form-box`, `.form-grid` and `.field` receive the shared component baseline. Pages can be migrated gradually without a sudden total redesign.
