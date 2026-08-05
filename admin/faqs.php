<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM faqs WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM faqs WHERE id=?');
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        flash('success', 'FAQ deleted.');
        redirect('faqs.php');
    }
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';
    if ($question === '' || $answer === '') {
        flash('error', 'Question and answer are required.');
        redirect('faqs.php' . ($action === 'update' ? '?edit=' . (int)($_POST['id'] ?? 0) : ''));
    }
    if ($action === 'add') {
        $stmt = db()->prepare('INSERT INTO faqs (question, answer, sort_order, published) VALUES (?, ?, ?, ?)');
        $stmt->execute([$question, $answer, $sort_order, $published]);
        flash('success', 'FAQ added.');
        redirect('faqs.php');
    }
    if ($action === 'update') {
        $stmt = db()->prepare('UPDATE faqs SET question=?, answer=?, sort_order=?, published=? WHERE id=?');
        $stmt->execute([$question, $answer, $sort_order, $published, (int)($_POST['id'] ?? 0)]);
        flash('success', 'FAQ updated.');
        redirect('faqs.php');
    }
}
$q = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($q !== '') { $where = ' WHERE question LIKE ? OR answer LIKE ?'; $params = ["%$q%", "%$q%"]; }
$stmt = db()->prepare('SELECT * FROM faqs' . $where . ' ORDER BY sort_order ASC, id DESC LIMIT 200');
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="admin-top"><div><h1>FAQ Management</h1><p>Create helpful answers that reduce repeated calls and improve student confidence.</p></div><a class="btn btn-soft" href="../admission.php" target="_blank">View Admission Page</a></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?><?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<form class="form-box" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>"><?php if ($edit): ?><input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>"><?php endif; ?><div class="form-grid"><div class="form-section-title"><span>❓</span><?= $edit ? 'Edit FAQ' : 'Add FAQ' ?></div><div class="field full"><label>Question *</label><input name="question" value="<?= e($edit['question'] ?? '') ?>" placeholder="Example: Can beginners join this course?" required></div><div class="field full"><label>Answer *</label><textarea name="answer" required placeholder="Write a clear, student-friendly answer."><?= e($edit['answer'] ?? '') ?></textarea></div><div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div><div class="field"><label>Published</label><select name="published"><option value="Yes" <?= ($edit['published'] ?? 'Yes') === 'Yes' ? 'selected' : '' ?>>Yes</option><option value="No" <?= ($edit['published'] ?? '') === 'No' ? 'selected' : '' ?>>No</option></select></div><div class="field full"><button class="btn btn-primary"><?= $edit ? 'Update FAQ' : 'Add FAQ' ?></button><?php if ($edit): ?><a class="btn btn-soft" href="faqs.php">Cancel</a><?php endif; ?></div></div></form><br>
<div class="panel-card"><div class="toolbar"><div><h2 style="margin:0;color:var(--navy)">FAQs</h2><p style="margin:4px 0 0;color:var(--muted)">Published FAQs appear on homepage and admission page.</p></div><form method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search FAQs"><button class="btn btn-sm btn-dark">Search</button></form></div><div class="table-wrap"><table><thead><tr><th>Question</th><th>Sort</th><th>Published</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td data-label="Question"><strong><?= e($row['question']) ?></strong><br><span class="help"><?= e(substr($row['answer'], 0, 130)) ?>...</span></td><td data-label="Sort"><?= e((string)$row['sort_order']) ?></td><td data-label="Published"><span class="badge <?= $row['published'] === 'Yes' ? 'badge-converted' : 'badge-notinterested' ?>"><?= e($row['published']) ?></span></td><td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-soft" href="faqs.php?edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this FAQ?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="4" class="empty-state">No FAQs yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
