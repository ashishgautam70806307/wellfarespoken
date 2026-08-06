<?php
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../includes/ui-components.php';
?>
<div class="admin-page-head">
    <div>
        <span class="eyebrow">Reusable Design System</span>
        <h1>UI Component Library</h1>
        <p>Use these shared classes and PHP helpers instead of writing new CSS for every page.</p>
    </div>
    <a class="wf-btn wf-btn--secondary wf-btn--sm" href="../index.php" target="_blank" rel="noopener"><span class="wf-btn-label"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>View Website</span></span></a>
</div>

<?= wf_alert('Safe migration rule', 'Existing pages keep working through compatibility classes. New pages should use only the wf-* component classes shown below.', 'info') ?>
<br>

<?php wf_card_start(['eyebrow' => 'Buttons', 'title' => 'One button system', 'text' => 'Use wf_button() so color, spacing, icons and accessibility remain consistent.']); ?>
<div class="wf-inline">
    <?= wf_button('Primary Action', '#', 'primary', 'fa-solid fa-arrow-right') ?>
    <?= wf_button('Secondary', '#', 'secondary', 'fa-solid fa-circle-info') ?>
    <?= wf_button('Gold Action', '#', 'gold', 'fa-solid fa-star') ?>
    <?= wf_button('WhatsApp', '#', 'success', 'fa-brands fa-whatsapp') ?>
    <?= wf_button('Delete', '', 'danger', 'fa-solid fa-trash', ['type' => 'button']) ?>
    <?= wf_button('Ghost', '#', 'ghost', 'fa-solid fa-eye') ?>
</div>
<br>
<div class="wf-inline">
    <?= wf_button('Small Button', '#', 'primary', 'fa-solid fa-arrow-right', ['size' => 'sm']) ?>
    <?= wf_button('Large Button', '#', 'gold', 'fa-solid fa-graduation-cap', ['size' => 'lg']) ?>
</div>
<?php wf_card_end(); ?>
<br>

<div class="wf-grid wf-grid--3">
    <?php wf_card_start(['variant' => 'default', 'title' => 'Default Card', 'text' => 'Main white card for forms, information and lists.', 'interactive' => true]); ?>
    <p>Class: <code>wf-card</code></p>
    <?= wf_badge('Default', 'neutral') ?>
    <?php wf_card_end(); ?>

    <?php wf_card_start(['variant' => 'gold', 'title' => 'Gold Card', 'text' => 'Use for featured or admission-related information.', 'interactive' => true]); ?>
    <p>Class: <code>wf-card wf-card--gold</code></p>
    <?= wf_badge('Featured', 'gold', 'fa-solid fa-star') ?>
    <?php wf_card_end(); ?>

    <?php wf_card_start(['variant' => 'navy', 'title' => 'Navy Card', 'text' => 'Use sparingly for a premium highlighted block.']); ?>
    <p>Class: <code>wf-card wf-card--navy</code></p>
    <?= wf_badge('Premium', 'gold') ?>
    <?php wf_card_end(); ?>
</div>
<br>

<?php wf_card_start(['eyebrow' => 'Form Controls', 'title' => 'One input design everywhere', 'text' => 'The same field system works on public and admin pages.']); ?>
<form class="wf-form-grid" action="#" method="post" onsubmit="return false;">
    <?= wf_form_field(['name' => 'sample_name', 'label' => 'Student Name', 'placeholder' => 'Enter full name', 'required' => true]) ?>
    <?= wf_form_field(['name' => 'sample_phone', 'label' => 'Mobile Number', 'type' => 'tel', 'placeholder' => '10-digit number']) ?>
    <?= wf_form_field(['name' => 'sample_course', 'label' => 'Course', 'type' => 'select', 'value' => 'Basic English Spoken', 'options' => ['' => 'Select course', 'Basic English Spoken' => 'Basic English Spoken', 'Interview Training' => 'Interview Training', 'News Commentary' => 'News Commentary', 'Advance Grammar' => 'Advance Grammar']]) ?>
    <?= wf_form_field(['name' => 'sample_date', 'label' => 'Preferred Date', 'type' => 'date']) ?>
    <?= wf_form_field(['name' => 'sample_message', 'label' => 'Message', 'type' => 'textarea', 'placeholder' => 'Write a short message', 'rows' => 4, 'full' => true, 'help' => 'Keep the message clear and short.']) ?>
    <div class="wf-form-actions wf-field--full">
        <?= wf_button('Save Sample', '', 'primary', 'fa-solid fa-floppy-disk', ['type' => 'submit']) ?>
        <?= wf_button('Reset', '', 'secondary', 'fa-solid fa-rotate-left', ['type' => 'reset']) ?>
    </div>
</form>
<?php wf_card_end(); ?>
<br>

<div class="wf-grid wf-grid--2">
    <?php wf_card_start(['title' => 'Badges', 'text' => 'Use for short statuses only.']); ?>
    <div class="wf-inline">
        <?= wf_badge('Active', 'success', 'fa-solid fa-check') ?>
        <?= wf_badge('Pending', 'warning', 'fa-solid fa-clock') ?>
        <?= wf_badge('Information', 'info', 'fa-solid fa-circle-info') ?>
        <?= wf_badge('Inactive', 'danger', 'fa-solid fa-xmark') ?>
        <?= wf_badge('Featured', 'gold', 'fa-solid fa-star') ?>
    </div>
    <?php wf_card_end(); ?>

    <?php wf_card_start(['title' => 'Alerts', 'text' => 'Use for important feedback and validation.']); ?>
    <div class="wf-stack wf-stack--sm">
        <?= wf_alert('Saved successfully', 'The record has been updated.', 'success') ?>
        <?= wf_alert('Check required fields', 'Some information is missing.', 'warning') ?>
        <?= wf_alert('Action failed', 'Please try again or contact the administrator.', 'danger') ?>
    </div>
    <?php wf_card_end(); ?>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
