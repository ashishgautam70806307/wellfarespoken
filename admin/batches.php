<?php
$admin_page_final_styles = ['assets/css/phase168-weekly-admin-easy.css'];
require_once __DIR__ . '/_header.php';
batch_ensure_schema();

$batchHasCourseId = false;
try { $batchHasCourseId = table_exists('batch_timings') && column_exists('batch_timings', 'course_id'); } catch (Throwable $e) {}

$edit = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = db()->prepare('SELECT * FROM batch_timings WHERE id=? LIMIT 1');
        $stmt->execute([(int)$_GET['edit']]);
        $edit = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('[batch-edit] ' . $e->getMessage());
        flash('error', 'Batch could not be loaded. Please refresh once.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security check failed. Refresh the page and try again.');
        redirect('batches.php');
    }
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = db()->prepare('DELETE FROM batch_timings WHERE id=?');
                $stmt->execute([$id]);
            }
            flash('success', 'Batch deleted.');
            redirect('batches.php');
        }

        $batch_name = trim((string)($_POST['batch_name'] ?? ''));
        $course_id = (int)($_POST['course_id'] ?? 0);
        $course_name = '';
        if ($course_id > 0) {
            $cs = db()->prepare('SELECT title FROM courses WHERE id=? LIMIT 1');
            $cs->execute([$course_id]);
            $course_name = (string)($cs->fetchColumn() ?: '');
        }
        $timing = trim((string)($_POST['timing'] ?? ''));
        $days = trim((string)($_POST['days'] ?? ''));
        $seats_note = trim((string)($_POST['seats_note'] ?? ''));
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';

        if ($batch_name === '') {
            flash('error', 'Batch name is required.');
            redirect('batches.php' . ($action === 'update' ? '?edit=' . (int)($_POST['id'] ?? 0) : ''));
        }

        if ($action === 'add') {
            if ($batchHasCourseId) {
                $stmt = db()->prepare('INSERT INTO batch_timings (course_id, batch_name, course_name, timing, days, seats_note, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$course_id ?: null, $batch_name, $course_name, $timing, $days, $seats_note, $sort_order, $published]);
            } else {
                $stmt = db()->prepare('INSERT INTO batch_timings (batch_name, course_name, timing, days, seats_note, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$batch_name, $course_name, $timing, $days, $seats_note, $sort_order, $published]);
            }
            flash('success', 'Batch created successfully. You can now select it in Upcoming Test.');
            redirect('batches.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            if ($batchHasCourseId) {
                $stmt = db()->prepare('UPDATE batch_timings SET course_id=?, batch_name=?, course_name=?, timing=?, days=?, seats_note=?, sort_order=?, published=? WHERE id=?');
                $stmt->execute([$course_id ?: null, $batch_name, $course_name, $timing, $days, $seats_note, $sort_order, $published, $id]);
            } else {
                $stmt = db()->prepare('UPDATE batch_timings SET batch_name=?, course_name=?, timing=?, days=?, seats_note=?, sort_order=?, published=? WHERE id=?');
                $stmt->execute([$batch_name, $course_name, $timing, $days, $seats_note, $sort_order, $published, $id]);
            }
            flash('success', 'Batch updated successfully.');
            redirect('batches.php');
        }
    } catch (Throwable $e) {
        error_log('[batch-save] ' . $e->__toString());
        flash('error', 'Batch could not be saved. The database structure may be old; refresh once and try again.');
        redirect('batches.php' . (!empty($_POST['id']) ? '?edit=' . (int)$_POST['id'] : ''));
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$where = '';
$params = [];
if ($q !== '') {
    $where = ' WHERE batch_name LIKE ? OR course_name LIKE ? OR timing LIKE ? OR days LIKE ?';
    $params = ["%$q%", "%$q%", "%$q%", "%$q%"];
}
$stmt = db()->prepare('SELECT * FROM batch_timings' . $where . ' ORDER BY sort_order ASC, id DESC LIMIT 200');
$stmt->execute($params);
$rows = $stmt->fetchAll();
$courses = db()->query("SELECT id,title FROM courses WHERE published='Yes' ORDER BY sort_order ASC, title ASC")->fetchAll();
?>

<div class="admin-top wf168-admin-top">
  <div>
    <span class="wf168-eyebrow"><i class="fa-solid fa-people-group"></i> Easy Batch Setup</span>
    <h1>Batch Management</h1>
    <p>Create a batch with only the details students/admin actually need. Advanced fields stay optional.</p>
  </div>
  <a class="btn btn-soft" href="weekly-tests.php?type=upcoming#setup"><i class="fa-solid fa-clipboard-check"></i> Upcoming Tests</a>
</div>

<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="wf168-quick-note">
  <i class="fa-solid fa-circle-info"></i>
  <div><b>Example</b><span>Morning Batch • Spoken English • 7:00 AM - 8:00 AM • Mon to Sat</span></div>
</div>

<form class="form-box wf168-batch-form" method="post">
  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>"><?php endif; ?>

  <div class="wf168-section-head">
    <div><span class="wf168-step-no">1</span><div><h2><?= $edit ? 'Edit Batch' : 'Create Batch' ?></h2><p>Four simple fields are enough for normal use.</p></div></div>
    <?php if ($edit): ?><a class="btn btn-soft btn-sm" href="batches.php">Cancel Edit</a><?php endif; ?>
  </div>

  <div class="wf168-simple-grid">
    <div class="field full"><label>Batch Name *</label><input name="batch_name" value="<?= e($edit['batch_name'] ?? '') ?>" placeholder="Example: Morning Batch A" required></div>
    <div class="field"><label>Course <small>optional</small></label><select name="course_id"><option value="0">All / No specific course</option><?php foreach ($courses as $course): ?><option value="<?= e((string)$course['id']) ?>" <?= (int)($edit['course_id'] ?? 0) === (int)$course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Timing <small>optional</small></label><input name="timing" value="<?= e($edit['timing'] ?? '') ?>" placeholder="7:00 AM - 8:00 AM"></div>
    <div class="field full"><label>Days <small>optional</small></label><input name="days" value="<?= e($edit['days'] ?? '') ?>" placeholder="Mon to Sat"></div>
  </div>

  <details class="wf168-advanced-box">
    <summary><i class="fa-solid fa-sliders"></i> Advanced settings <span>optional</span></summary>
    <div class="wf168-simple-grid">
      <div class="field full"><label>Seats / Note</label><input name="seats_note" value="<?= e($edit['seats_note'] ?? '') ?>" placeholder="Example: Limited seats available"></div>
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div>
      <div class="field"><label>Published</label><select name="published"><option value="Yes" <?= ($edit['published'] ?? 'Yes') === 'Yes' ? 'selected' : '' ?>>Yes</option><option value="No" <?= ($edit['published'] ?? '') === 'No' ? 'selected' : '' ?>>No</option></select></div>
    </div>
  </details>

  <div class="wf168-form-actions"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-check"></i> <?= $edit ? 'Update Batch' : 'Create Batch' ?></button></div>
</form>

<div class="panel-card wf168-batch-list">
  <div class="toolbar wf168-list-toolbar">
    <div><h2>Saved Batches</h2><p><?= e((string)count($rows)) ?> batch(es). Published batches are available in Upcoming Test.</p></div>
    <form method="get" class="wf168-search-form"><input name="q" value="<?= e($q) ?>" placeholder="Search batch"><button class="btn btn-sm btn-dark" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button></form>
  </div>
  <div class="table-wrap wf168-table-wrap">
    <table class="wf168-responsive-table">
      <thead><tr><th>Batch</th><th>Course</th><th>Timing</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
        <tr>
          <td data-label="Batch"><strong><?= e($row['batch_name']) ?></strong><small><?= e(trim((string)($row['days'] ?? ''))) ?><?= !empty($row['seats_note']) ? ' • '.e($row['seats_note']) : '' ?></small></td>
          <td data-label="Course"><?= e($row['course_name'] ?: 'All / Common') ?></td>
          <td data-label="Timing"><?= e($row['timing'] ?: 'Not set') ?></td>
          <td data-label="Status"><span class="badge <?= $row['published'] === 'Yes' ? 'badge-converted' : 'badge-notinterested' ?>"><?= $row['published'] === 'Yes' ? 'Published' : 'Hidden' ?></span></td>
          <td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-soft" href="batches.php?edit=<?= e((string)$row['id']) ?>"><i class="fa-solid fa-pen"></i> Edit</a><form method="post" onsubmit="return confirm('Delete this batch?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit"><i class="fa-solid fa-trash"></i> Delete</button></form></div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="empty-state">No batch yet. Create your first batch above.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
