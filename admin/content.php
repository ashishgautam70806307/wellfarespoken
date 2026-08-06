<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$types = [
    'home_feature' => 'Homepage Feature Cards',
    'hero_stat' => 'Homepage Hero Stats',
    'online_class_feature' => 'Homepage Online Class Features',
    'about_highlight' => 'About Page Highlight Cards',
    'admission_benefit' => 'Admission Page Benefit Points'
];
$type = $_GET['type'] ?? 'home_feature';
if (!isset($types[$type])) $type = 'home_feature';
$edit = null;
if (!empty($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM content_blocks WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security check failed. Refresh and try again.');
    } else {
        db()->prepare('DELETE FROM content_blocks WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('success', 'Content block deleted.');
    }
    redirect('content.php?type=' . urlencode($type));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_validate($_POST['csrf_token'] ?? '')) {
    flash('error', 'Security check failed. Refresh and try again.');
    redirect('content.php?type=' . urlencode($type));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        trim($_POST['block_type'] ?? $type),
        trim($_POST['block_key'] ?? ''),
        trim($_POST['icon'] ?? ''),
        trim($_POST['eyebrow'] ?? ''),
        trim($_POST['title'] ?? ''),
        trim($_POST['subtitle'] ?? ''),
        trim($_POST['body'] ?? ''),
        trim($_POST['link_text'] ?? ''),
        trim($_POST['link_url'] ?? ''),
        (int)($_POST['sort_order'] ?? 0),
        ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes'
    ];
    if ($data[4] === '') {
        flash('error', 'Title is required.');
    } elseif ($id > 0) {
        $stmt = db()->prepare('UPDATE content_blocks SET block_type=?, block_key=?, icon=?, eyebrow=?, title=?, subtitle=?, body=?, link_text=?, link_url=?, sort_order=?, published=? WHERE id=?');
        $stmt->execute([...$data, $id]);
        flash('success', 'Content block updated.');
        redirect('content.php?type=' . urlencode($data[0]));
    } else {
        $stmt = db()->prepare('INSERT INTO content_blocks (block_type, block_key, icon, eyebrow, title, subtitle, body, link_text, link_url, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute($data);
        flash('success', 'Content block added.');
        redirect('content.php?type=' . urlencode($data[0]));
    }
}
$stmt = db()->prepare('SELECT * FROM content_blocks WHERE block_type = ? ORDER BY sort_order ASC, id DESC');
$stmt->execute([$type]);
$rows = $stmt->fetchAll();
?>
<div class="admin-top"><div><h1>Dynamic Content Blocks</h1><p>Manage repeated frontend cards, stats, benefits and page sections without editing PHP files.</p></div><div class="admin-actions"><a class="btn btn-soft" href="../index.php" target="_blank">View Website</a></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
<div class="filter-tabs"><?php foreach ($types as $key => $label): ?><a class="pill <?= $type === $key ? 'active-pill' : '' ?>" href="content.php?type=<?= e($key) ?>"><?= e($label) ?></a><?php endforeach; ?></div>
<form class="form-box" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '0')) ?>">
    <div class="form-grid">
        <div class="form-section-title"><span>✍️</span><?= $edit ? 'Edit Content Block' : 'Add Content Block' ?></div>
        <div class="field"><label>Section Type</label><select name="block_type"><?php foreach ($types as $key => $label): ?><option value="<?= e($key) ?>" <?= (($edit['block_type'] ?? $type) === $key) ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Unique Key</label><input name="block_key" value="<?= e($edit['block_key'] ?? '') ?>" placeholder="example: confidence"></div>
        <div class="field"><label>Font Icon Class</label><input name="icon" value="<?= e($edit['icon'] ?? '') ?>" placeholder="fa-solid fa-video"></div>
        <div class="field"><label>Eyebrow / Small Label</label><input name="eyebrow" value="<?= e($edit['eyebrow'] ?? '') ?>"></div>
        <div class="field full"><label>Title *</label><input name="title" required value="<?= e($edit['title'] ?? '') ?>"></div>
        <div class="field full"><label>Subtitle</label><textarea name="subtitle"><?= e($edit['subtitle'] ?? '') ?></textarea></div>
        <div class="field full"><label>Body / Extra Text</label><textarea name="body"><?= e($edit['body'] ?? '') ?></textarea></div>
        <div class="field"><label>Button Text</label><input name="link_text" value="<?= e($edit['link_text'] ?? '') ?>"></div>
        <div class="field"><label>Button URL</label><input name="link_url" value="<?= e($edit['link_url'] ?? '') ?>"></div>
        <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div>
        <div class="field"><label>Published</label><select name="published"><option <?= (($edit['published'] ?? 'Yes') === 'Yes') ? 'selected' : '' ?>>Yes</option><option <?= (($edit['published'] ?? '') === 'No') ? 'selected' : '' ?>>No</option></select></div>
        <div class="field full"><button class="btn btn-primary"><?= $edit ? 'Update Block' : 'Save Block' ?></button><?php if ($edit): ?><a class="btn btn-soft" href="content.php?type=<?= e($type) ?>">Cancel</a><?php endif; ?></div>
    </div>
</form>
<div class="table-card"><table class="admin-table"><thead><tr><th>Order</th><th>Content</th><th>Type</th><th>Published</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string)$row['sort_order']) ?></td><td><strong><?= e(($row['icon'] ? $row['icon'] . ' ' : '') . $row['title']) ?></strong><br><small><?= e($row['subtitle'] ?: $row['body'] ?: 'No extra text') ?></small></td><td><?= e($types[$row['block_type']] ?? $row['block_type']) ?></td><td><span class="badge <?= $row['published'] === 'Yes' ? 'badge-green' : 'badge-gray' ?>"><?= e($row['published']) ?></span></td><td><a class="btn btn-sm btn-soft" href="content.php?type=<?= e($type) ?>&edit=<?= e((string)$row['id']) ?>">Edit</a> <form method="post" class="inline-form" onsubmit="return confirm('Delete this content block?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="5">No content blocks found.</td></tr><?php endif; ?></tbody></table></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
