<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM nav_menus WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'update') {
        $menu_area = ($_POST['menu_area'] ?? 'header') === 'footer' ? 'footer' : 'header';
        $label = trim($_POST['label'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $is_cta = ($_POST['is_cta'] ?? 'No') === 'Yes' ? 'Yes' : 'No';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
        if ($label === '' || $url === '') {
            flash('error', 'Menu label and URL are required.');
            redirect('nav-menus.php' . ($action === 'update' ? '?edit=' . (int)($_POST['id'] ?? 0) : ''));
        }
        if ($action === 'add') {
            $stmt = db()->prepare('INSERT INTO nav_menus (menu_area, label, url, is_cta, sort_order, published) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$menu_area, $label, $url, $is_cta, $sort_order, $published]);
            flash('success', 'Menu item added.');
        } else {
            $stmt = db()->prepare('UPDATE nav_menus SET menu_area=?, label=?, url=?, is_cta=?, sort_order=?, published=? WHERE id=?');
            $stmt->execute([$menu_area, $label, $url, $is_cta, $sort_order, $published, (int)($_POST['id'] ?? 0)]);
            flash('success', 'Menu item updated.');
        }
        redirect('nav-menus.php');
    }
    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM nav_menus WHERE id=?');
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        flash('success', 'Menu item deleted.');
        redirect('nav-menus.php');
    }
}
$area = ($_GET['area'] ?? '') === 'footer' ? 'footer' : '';
$params = [];$where='';
if ($area !== '') { $where = ' WHERE menu_area=?'; $params[]=$area; }
$stmt = db()->prepare('SELECT * FROM nav_menus' . $where . ' ORDER BY menu_area ASC, sort_order ASC, id ASC');
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="admin-top"><div><h1>Navigation Menus</h1><p>Header/footer extra links publish here. Duplicate links are automatically skipped on frontend.</p></div><div class="admin-actions"><a class="btn btn-soft" href="../index.php" target="_blank">View Website</a></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<form class="form-box" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-section-title"><span>🧭</span><?= $edit ? 'Edit Menu Item' : 'Add Menu Item' ?></div>
        <div class="field"><label>Menu Area</label><select name="menu_area"><option value="header" <?= ($edit['menu_area'] ?? 'header') === 'header' ? 'selected' : '' ?>>Header</option><option value="footer" <?= ($edit['menu_area'] ?? '') === 'footer' ? 'selected' : '' ?>>Footer</option></select></div>
        <div class="field"><label>Label *</label><input name="label" value="<?= e($edit['label'] ?? '') ?>" placeholder="Example: Admission" required></div>
        <div class="field full"><label>URL *</label><input name="url" value="<?= e($edit['url'] ?? '') ?>" placeholder="Example: admission.php or https://..." required><small class="help">Use local page names for internal pages. External links are also supported.</small></div>
        <div class="field"><label>CTA Style</label><select name="is_cta"><option value="No" <?= ($edit['is_cta'] ?? 'No') === 'No' ? 'selected' : '' ?>>No</option><option value="Yes" <?= ($edit['is_cta'] ?? '') === 'Yes' ? 'selected' : '' ?>>Yes</option></select></div>
        <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div>
        <div class="field"><label>Published</label><select name="published"><option value="Yes" <?= ($edit['published'] ?? 'Yes') === 'Yes' ? 'selected' : '' ?>>Yes</option><option value="No" <?= ($edit['published'] ?? '') === 'No' ? 'selected' : '' ?>>No</option></select></div>
        <div class="field full"><button class="btn btn-primary"><?= $edit ? 'Update Menu Item' : 'Add Menu Item' ?></button><?php if ($edit): ?><a class="btn btn-soft" href="nav-menus.php">Cancel</a><?php endif; ?></div>
    </div>
</form><br>
<div class="panel-card">
    <div class="toolbar"><div><h2 style="margin:0;color:var(--navy)">Menu Items</h2><p style="margin:4px 0 0;color:var(--muted)">Publish/unpublish links and reorder them for header or footer.</p></div><form method="get"><select name="area"><option value="">All Areas</option><option value="header" <?= $area==='' && ($_GET['area'] ?? '')==='header'?'selected':'' ?>>Header</option><option value="footer" <?= $area==='footer'?'selected':'' ?>>Footer</option></select><button class="btn btn-sm btn-dark">Filter</button></form></div>
    <div class="table-wrap"><table><thead><tr><th>Area</th><th>Label</th><th>URL</th><th>CTA</th><th>Sort</th><th>Published</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($rows as $row): ?><tr>
        <td data-label="Area"><span class="badge badge-gray"><?= e(ucfirst($row['menu_area'])) ?></span></td>
        <td data-label="Label"><strong><?= e($row['label']) ?></strong></td>
        <td data-label="URL"><span class="help"><?= e($row['url']) ?></span></td>
        <td data-label="CTA"><?= e($row['is_cta']) ?></td>
        <td data-label="Sort"><?= e((string)$row['sort_order']) ?></td>
        <td data-label="Published"><form method="post" action="toggle-publish.php" class="inline-toggle"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="table" value="nav_menus"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><input type="hidden" name="return" value="nav-menus.php"><button class="badge <?= $row['published']==='Yes'?'badge-green':'badge-gray' ?>" type="submit"><?= e($row['published']) ?></button></form></td>
        <td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-soft" href="nav-menus.php?edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" data-confirm="Delete this menu item?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td>
    </tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="empty-state">No menu items found.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
