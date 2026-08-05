<?php
require_once __DIR__ . '/_header.php';
online_class_ensure_schema();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM online_classes WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        flash('error', 'Security token expired. Please try again.');
        redirect('online-classes.php');
    }

    $action = trim((string)($_POST['action'] ?? ''));
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM online_class_attendance WHERE online_class_id = ?')->execute([$id]);
        db()->prepare('DELETE FROM online_classes WHERE id = ?')->execute([$id]);
        flash('success', 'Online class deleted.');
        redirect('online-classes.php');
    }

    $classTitle = trim((string)($_POST['class_title'] ?? ''));
    $courseName = trim((string)($_POST['course_name'] ?? ''));
    $batchName = trim((string)($_POST['batch_name'] ?? ''));
    $teacherName = trim((string)($_POST['teacher_name'] ?? ''));
    $classDate = trim((string)($_POST['class_date'] ?? '')) ?: null;
    $startTime = trim((string)($_POST['start_time'] ?? '')) ?: null;
    $endTime = trim((string)($_POST['end_time'] ?? '')) ?: null;
    $durationMinutes = max(15, min(300, (int)($_POST['duration_minutes'] ?? 60)));
    $platform = trim((string)($_POST['platform'] ?? 'Google Meet')) ?: 'Google Meet';
    $meetingUrl = trim((string)($_POST['meeting_url'] ?? ''));
    $recordingUrl = trim((string)($_POST['recording_url'] ?? ''));
    $classStatus = trim((string)($_POST['class_status'] ?? 'Scheduled'));
    $shortDescription = trim((string)($_POST['short_description'] ?? ''));
    $studentNote = trim((string)($_POST['student_note'] ?? ''));
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $published = ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes';

    if (!in_array($classStatus, ['Scheduled', 'Live', 'Completed', 'Cancelled'], true)) {
        $classStatus = 'Scheduled';
    }
    if ($classTitle === '') {
        flash('error', 'Class title is required.');
        redirect($action === 'update' ? 'online-classes.php?edit=' . (int)($_POST['id'] ?? 0) : 'online-classes.php');
    }
    foreach ([$meetingUrl, $recordingUrl] as $urlValue) {
        if ($urlValue !== '' && !filter_var($urlValue, FILTER_VALIDATE_URL)) {
            flash('error', 'Meeting and recording links must be valid URLs.');
            redirect($action === 'update' ? 'online-classes.php?edit=' . (int)($_POST['id'] ?? 0) : 'online-classes.php');
        }
    }

    $values = [$classTitle, $courseName, $batchName, $teacherName, $classDate, $startTime, $endTime, $durationMinutes, $platform, $meetingUrl, $recordingUrl, $classStatus, $shortDescription, $studentNote, $sortOrder, $published];
    if ($action === 'update') {
        $values[] = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('UPDATE online_classes SET class_title=?, course_name=?, batch_name=?, teacher_name=?, class_date=?, start_time=?, end_time=?, duration_minutes=?, platform=?, meeting_url=?, recording_url=?, class_status=?, short_description=?, student_note=?, sort_order=?, published=? WHERE id=?');
        $stmt->execute($values);
        flash('success', 'Online class updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO online_classes (class_title, course_name, batch_name, teacher_name, class_date, start_time, end_time, duration_minutes, platform, meeting_url, recording_url, class_status, short_description, student_note, sort_order, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute($values);
        flash('success', 'Online class added.');
    }
    redirect('online-classes.php');
}

$q = trim((string)($_GET['q'] ?? ''));
$statusFilter = trim((string)($_GET['status'] ?? ''));
$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(class_title LIKE ? OR course_name LIKE ? OR batch_name LIKE ? OR teacher_name LIKE ? OR platform LIKE ?)';
    for ($i = 0; $i < 5; $i++) { $params[] = '%' . $q . '%'; }
}
if (in_array($statusFilter, ['Scheduled', 'Live', 'Completed', 'Cancelled'], true)) {
    $where[] = 'class_status = ?';
    $params[] = $statusFilter;
}
$sql = 'SELECT * FROM online_classes' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY class_date IS NULL, class_date DESC, start_time DESC, id DESC LIMIT 250';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$courses = db()->query('SELECT title FROM courses WHERE published = "Yes" ORDER BY sort_order ASC, title ASC')->fetchAll();
$batches = db()->query('SELECT batch_name FROM batch_timings WHERE published = "Yes" ORDER BY sort_order ASC, batch_name ASC')->fetchAll();
?>
<div class="admin-top">
    <div>
        <span class="admin-kicker">Future Learning</span>
        <h1>Online Class Management</h1>
        <p>Schedule live spoken English classes, publish meeting links, manage recordings and show upcoming classes to students.</p>
    </div>
    <a class="btn btn-soft" href="../online-classes.php" target="_blank">View Public Page</a>
</div>
<?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>

<form class="form-box admin-online-class-form" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-section-title"><span>🎥</span><?= $edit ? 'Edit Online Class' : 'Schedule New Online Class' ?></div>
        <div class="field full"><label>Class Title *</label><input name="class_title" value="<?= e($edit['class_title'] ?? '') ?>" placeholder="Daily Conversation Practice" required></div>
        <div class="field"><label>Course</label><input list="onlineCourseOptions" name="course_name" value="<?= e($edit['course_name'] ?? '') ?>" placeholder="Spoken English Basic"><datalist id="onlineCourseOptions"><?php foreach ($courses as $course): ?><option value="<?= e($course['title']) ?>"><?php endforeach; ?></datalist></div>
        <div class="field"><label>Batch</label><input list="onlineBatchOptions" name="batch_name" value="<?= e($edit['batch_name'] ?? '') ?>" placeholder="Evening Online Batch"><datalist id="onlineBatchOptions"><?php foreach ($batches as $batch): ?><option value="<?= e($batch['batch_name']) ?>"><?php endforeach; ?></datalist></div>
        <div class="field"><label>Teacher Name</label><input name="teacher_name" value="<?= e($edit['teacher_name'] ?? '') ?>" placeholder="Trainer name"></div>
        <div class="field"><label>Platform</label><select name="platform"><?php foreach (['Google Meet','Zoom','Microsoft Teams','YouTube Live','Website Live Room','Other'] as $platform): ?><option value="<?= e($platform) ?>" <?= ($edit['platform'] ?? 'Google Meet') === $platform ? 'selected' : '' ?>><?= e($platform) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Class Date</label><input type="date" name="class_date" value="<?= e($edit['class_date'] ?? '') ?>"></div>
        <div class="field"><label>Start Time</label><input type="time" name="start_time" value="<?= e(isset($edit['start_time']) ? substr((string)$edit['start_time'], 0, 5) : '') ?>"></div>
        <div class="field"><label>End Time</label><input type="time" name="end_time" value="<?= e(isset($edit['end_time']) ? substr((string)$edit['end_time'], 0, 5) : '') ?>"></div>
        <div class="field"><label>Duration (minutes)</label><input type="number" min="15" max="300" name="duration_minutes" value="<?= e((string)($edit['duration_minutes'] ?? 60)) ?>"></div>
        <div class="field full"><label>Meeting URL</label><input type="url" name="meeting_url" value="<?= e($edit['meeting_url'] ?? '') ?>" placeholder="https://meet.google.com/..."><small class="help">Students see this button only when the class is published.</small></div>
        <div class="field full"><label>Recording URL</label><input type="url" name="recording_url" value="<?= e($edit['recording_url'] ?? '') ?>" placeholder="https://youtube.com/... or private recording link"></div>
        <div class="field full"><label>Short Description</label><textarea name="short_description" rows="3" placeholder="Topic, learning goal and speaking activity."><?= e($edit['short_description'] ?? '') ?></textarea></div>
        <div class="field full"><label>Student Note</label><textarea name="student_note" rows="2" placeholder="Keep notebook ready, join 5 minutes early, use headphones..."><?= e($edit['student_note'] ?? '') ?></textarea></div>
        <div class="field"><label>Status</label><select name="class_status"><?php foreach (['Scheduled','Live','Completed','Cancelled'] as $status): ?><option value="<?= e($status) ?>" <?= ($edit['class_status'] ?? 'Scheduled') === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Published</label><select name="published"><option value="Yes" <?= ($edit['published'] ?? 'Yes') === 'Yes' ? 'selected' : '' ?>>Yes</option><option value="No" <?= ($edit['published'] ?? '') === 'No' ? 'selected' : '' ?>>No</option></select></div>
        <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div>
        <div class="field admin-form-actions"><button class="btn btn-primary" type="submit"><?= $edit ? 'Update Class' : 'Schedule Class' ?></button><?php if ($edit): ?><a class="btn btn-soft" href="online-classes.php">Cancel</a><?php endif; ?></div>
    </div>
</form>

<div class="panel-card online-class-list-panel">
    <div class="toolbar">
        <div><h2>Scheduled and Previous Classes</h2><p>Use status and publish controls to keep the student view accurate.</p></div>
        <form method="get" class="admin-filter-form"><input name="q" value="<?= e($q) ?>" placeholder="Search class, batch or teacher"><select name="status"><option value="">All statuses</option><?php foreach (['Scheduled','Live','Completed','Cancelled'] as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-dark">Filter</button></form>
    </div>
    <div class="table-wrap"><table><thead><tr><th>Class</th><th>Schedule</th><th>Platform</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td data-label="Class"><strong><?= e($row['class_title']) ?></strong><br><span class="help"><?= e(trim(($row['course_name'] ?? '') . (($row['course_name'] ?? '') && ($row['batch_name'] ?? '') ? ' • ' : '') . ($row['batch_name'] ?? ''))) ?></span><?php if (!empty($row['teacher_name'])): ?><br><small>Teacher: <?= e($row['teacher_name']) ?></small><?php endif; ?></td>
            <td data-label="Schedule"><?= e(online_class_display_date($row)) ?><br><small><?= e((string)$row['duration_minutes']) ?> minutes</small></td>
            <td data-label="Platform"><?= e($row['platform']) ?><?php if (!empty($row['meeting_url'])): ?><br><a href="<?= e($row['meeting_url']) ?>" target="_blank" rel="noopener">Open meeting</a><?php endif; ?></td>
            <td data-label="Status"><span class="badge online-admin-status <?= e(online_class_status_class((string)$row['class_status'])) ?>"><?= e($row['class_status']) ?></span></td>
            <td data-label="Published"><span class="badge <?= $row['published'] === 'Yes' ? 'badge-converted' : 'badge-not' ?>"><?= e($row['published']) ?></span></td>
            <td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-soft" href="online-classes.php?edit=<?= e((string)$row['id']) ?>">Edit</a><form method="post" data-confirm="Delete this online class and its attendance records?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></div></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="6" class="empty-state">No online classes found. Schedule your first class above.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
