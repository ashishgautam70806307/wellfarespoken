<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM gallery_images WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $image_alt = trim($_POST['image_alt'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';

    if (($action === 'add' || $action === 'update') && $title === '') {
        flash('error', 'Gallery title is required.');
        redirect('gallery.php' . ($action === 'update' ? '?edit=' . (int)($_POST['id'] ?? 0) : ''));
    }

    try {
        $uploadedPath = upload_gallery_image($_FILES['image_file'] ?? []);
        if ($uploadedPath) {
            $image_url = $uploadedPath;
        }
        if ($action === 'add') {
            $stmt = db()->prepare('INSERT INTO gallery_images (title, category, image_url, image_alt, description, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $category, $image_url, $image_alt, $description, $sort_order, $published]);
            flash('success', 'Gallery item added.');
            redirect('gallery.php');
        }
        if ($action === 'update') {
            if ($image_url === '' && !empty($_POST['existing_image'])) {
                $image_url = trim($_POST['existing_image']);
            }
            $stmt = db()->prepare('UPDATE gallery_images SET title=?, category=?, image_url=?, image_alt=?, description=?, sort_order=?, published=? WHERE id=?');
            $stmt->execute([$title, $category, $image_url, $image_alt, $description, $sort_order, $published, (int)($_POST['id'] ?? 0)]);
            flash('success', 'Gallery item updated.');
            redirect('gallery.php');
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('gallery.php' . ($action === 'update' ? '?edit=' . (int)($_POST['id'] ?? 0) : ''));
    }

    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM gallery_images WHERE id=?');
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        flash('success', 'Gallery item deleted.');
        redirect('gallery.php');
    }
}
$q = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($q !== '') {
    $where = ' WHERE title LIKE ? OR category LIKE ? OR description LIKE ?';
    $params = ["%$q%", "%$q%", "%$q%"];
}
$stmt = db()->prepare('SELECT * FROM gallery_images' . $where . ' ORDER BY sort_order ASC, id DESC LIMIT 200');
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="admin-top">
    <div><h1>Gallery Management</h1><p>Upload real classroom, batch, event and student activity photos for a more trustworthy website.</p></div>
    <div class="admin-actions"><a class="btn btn-soft" href="../gallery.php" target="_blank">View Gallery</a></div>
</div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<form class="form-box" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>"><input type="hidden" name="existing_image" value="<?= e($edit['image_url'] ?? '') ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-section-title"><span>🖼</span><?= $edit ? 'Edit Gallery Item' : 'Add Gallery Item' ?></div>
        <div class="field"><label>Title *</label><input name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="Example: Morning Batch Speaking Practice" required></div>
        <div class="field"><label>Category</label><input name="category" value="<?= e($edit['category'] ?? '') ?>" placeholder="Classroom, Event, Activity"></div>
        <div class="field full"><label>Upload Image</label><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" data-preview="#galleryPreview"><small class="help">Recommended: real horizontal classroom photo. JPG, PNG or WEBP. Max 2 MB.</small><div id="galleryPreview" class="image-preview"><?php if ($edit && !empty($edit['image_url'])): $previewSrc = preg_match('/^https?:\\/\//', $edit['image_url']) ? $edit['image_url'] : '../' . ltrim($edit['image_url'], '/'); ?><img src="<?= e($previewSrc) ?>" alt="Current image preview"><?php else: ?><span>No preview selected</span><?php endif; ?></div></div>
        <div class="field full"><label>Image Path / URL</label><input name="image_url" value="<?= e($edit['image_url'] ?? '') ?>" placeholder="Optional: assets/images/classroom-1.jpg or https://..."><small class="help">Leave blank when uploading a file. Existing path remains during edit unless replaced.</small></div>
        <div class="field full"><label>Image Alt Text</label><input name="image_alt" value="<?= e($edit['image_alt'] ?? '') ?>" placeholder="Example: Students practicing spoken English in classroom"></div>
        <div class="field full"><label>Description</label><textarea name="description" placeholder="Small caption shown on gallery card."><?= e($edit['description'] ?? '') ?></textarea></div>
        <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div>
        <div class="field"><label>Published</label><select name="published"><option value="Yes" <?= ($edit['published'] ?? 'Yes') === 'Yes' ? 'selected' : '' ?>>Yes</option><option value="No" <?= ($edit['published'] ?? '') === 'No' ? 'selected' : '' ?>>No</option></select></div>
        <div class="field full"><button class="btn btn-primary"><?= $edit ? 'Update Gallery Item' : 'Add Gallery Item' ?></button><?php if ($edit): ?><a class="btn btn-soft" href="gallery.php">Cancel</a><?php endif; ?></div>
    </div>
</form>
<br>
<div class="panel-card">
    <div class="toolbar"><div><h2 style="margin:0;color:var(--navy)">Gallery Items</h2><p style="margin:4px 0 0;color:var(--muted)">Uploaded images are saved in assets/uploads/gallery.</p></div><form method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search gallery"><button class="btn btn-sm btn-dark">Search</button></form></div>
    <div class="table-wrap"><table><thead><tr><th>Preview</th><th>Title</th><th>Category</th><th>Sort</th><th>Published</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($rows as $row): ?><tr>
        <td data-label="Preview"><?php if (!empty($row['image_url'])): $src = preg_match('/^https?:\/\//', $row['image_url']) ? $row['image_url'] : '../' . ltrim($row['image_url'], '/'); ?><img class="admin-thumb" src="<?= e($src) ?>" loading="lazy" decoding="async" alt="<?= e($row['image_alt'] ?: $row['title']) ?>"><?php else: ?><span class="gallery-placeholder-mini">WF</span><?php endif; ?></td>
        <td data-label="Title"><strong><?= e($row['title']) ?></strong><br><span class="help"><?= e($row['description'] ?? '') ?></span></td>
        <td data-label="Category"><?= e($row['category'] ?? '-') ?></td>
        <td data-label="Sort"><?= e((string)$row['sort_order']) ?></td>
        <td data-label="Published"><span class="badge <?= $row['published'] === 'Yes' ? 'badge-converted' : 'badge-notinterested' ?>"><?= e($row['published']) ?></span></td>
        <td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-soft" href="gallery.php?edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" data-confirm="Delete this gallery item?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td>
    </tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="empty-state">No gallery items yet. Add classroom or activity photos above.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
