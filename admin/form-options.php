<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$groups = [
    'current_level' => 'Admission Current Level Options',
    'enquiry_status' => 'Enquiry Status Options',
    'preferred_time' => 'Preferred Time Options'
];
$group = $_GET['group'] ?? 'current_level';
if (!isset($groups[$group])) $group = 'current_level';
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM form_options WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security check failed. Refresh and try again.');
    } else {
        db()->prepare('DELETE FROM form_options WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('success', 'Form option deleted.');
    }
    redirect('form-options.php?group=' . urlencode($group));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_validate($_POST['csrf_token'] ?? '')) {
    flash('error', 'Security check failed. Refresh and try again.');
    redirect('form-options.php?group=' . urlencode($group));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [trim($_POST['option_group'] ?? $group), trim($_POST['option_label'] ?? ''), trim($_POST['option_value'] ?? ''), trim($_POST['helper_text'] ?? ''), (int)($_POST['sort_order'] ?? 0), ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes'];
    if ($data[1] === '') {
        flash('error', 'Option label is required.');
    } elseif ($id > 0) {
        db()->prepare('UPDATE form_options SET option_group=?, option_label=?, option_value=?, helper_text=?, sort_order=?, published=? WHERE id=?')->execute([...$data, $id]);
        flash('success', 'Form option updated.');
        redirect('form-options.php?group=' . urlencode($data[0]));
    } else {
        db()->prepare('INSERT INTO form_options (option_group, option_label, option_value, helper_text, sort_order, published) VALUES (?, ?, ?, ?, ?, ?)')->execute($data);
        flash('success', 'Form option added.');
        redirect('form-options.php?group=' . urlencode($data[0]));
    }
}
$stmt = db()->prepare('SELECT * FROM form_options WHERE option_group = ? ORDER BY sort_order ASC, id ASC');
$stmt->execute([$group]);
$rows = $stmt->fetchAll();
?>
<div class="admin-top"><div><h1>Admission Form Options</h1><p>Control dropdown choices and enquiry workflow labels from admin.</p></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
<div class="filter-tabs"><?php foreach ($groups as $key => $label): ?><a class="pill <?= $group === $key ? 'active-pill' : '' ?>" href="form-options.php?group=<?= e($key) ?>"><?= e($label) ?></a><?php endforeach; ?></div>
<form class="form-box" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '0')) ?>"><div class="form-grid"><div class="form-section-title"><span>☑️</span><?= $edit ? 'Edit Option' : 'Add Option' ?></div><div class="field"><label>Option Group</label><select name="option_group"><?php foreach ($groups as $key => $label): ?><option value="<?= e($key) ?>" <?= (($edit['option_group'] ?? $group) === $key) ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><div class="field"><label>Label *</label><input name="option_label" required value="<?= e($edit['option_label'] ?? '') ?>"></div><div class="field"><label>Saved Value</label><input name="option_value" value="<?= e($edit['option_value'] ?? '') ?>"><small class="help">Leave blank to use label as value.</small></div><div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div><div class="field full"><label>Helper Text</label><input name="helper_text" value="<?= e($edit['helper_text'] ?? '') ?>"></div><div class="field"><label>Published</label><select name="published"><option <?= (($edit['published'] ?? 'Yes') === 'Yes') ? 'selected' : '' ?>>Yes</option><option <?= (($edit['published'] ?? '') === 'No') ? 'selected' : '' ?>>No</option></select></div><div class="field"><button class="btn btn-primary"><?= $edit ? 'Update Option' : 'Save Option' ?></button></div></div></form>
<div class="table-card"><table class="admin-table"><thead><tr><th>Order</th><th>Label</th><th>Value</th><th>Published</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string)$row['sort_order']) ?></td><td><strong><?= e($row['option_label']) ?></strong><br><small><?= e($row['helper_text'] ?: 'No helper text') ?></small></td><td><?= e($row['option_value'] ?: $row['option_label']) ?></td><td><span class="badge <?= $row['published'] === 'Yes' ? 'badge-green' : 'badge-gray' ?>"><?= e($row['published']) ?></span></td><td><a class="btn btn-sm btn-soft" href="form-options.php?group=<?= e($group) ?>&edit=<?= e((string)$row['id']) ?>">Edit</a> <form method="post" class="inline-form" onsubmit="return confirm('Delete this option?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="5">No options found.</td></tr><?php endif; ?></tbody></table></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
