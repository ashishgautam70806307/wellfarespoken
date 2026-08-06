<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$edit = null;
$variants = [];
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM courses WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
    if ($edit) {
        $variants = fetch_course_variants((int)$edit['id']);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? 'add';
    try {
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new RuntimeException('Invalid course record.');
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('DELETE FROM course_variants WHERE course_id=?');
                $stmt->execute([$id]);
                $stmt = $pdo->prepare('DELETE FROM courses WHERE id=?');
                $stmt->execute([$id]);
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
            flash('success', 'Course deleted.'); redirect('courses.php');
        }
        if ($action === 'save') {
            $imagePath = trim($_POST['existing_course_image'] ?? '');
            if (!empty($_FILES['course_image']['name'])) {
                $imagePath = upload_course_image($_FILES['course_image']);
            }
            $data = [
                trim($_POST['title'] ?? ''),
                trim($_POST['short_description'] ?? ''),
                trim($_POST['duration'] ?? ''),
                trim($_POST['level'] ?? ''),
                (float)($_POST['price'] ?? 0),
                trim($_POST['pay_url'] ?? ''),
                $imagePath,
                trim($_POST['class_time'] ?? ''),
                trim($_POST['class_days'] ?? ''),
                (int)($_POST['total_tests'] ?? 0),
                (int)($_POST['lessons_count'] ?? 0),
                trim($_POST['course_details'] ?? ''),
                trim($_POST['outcomes'] ?? ''),
                trim($_POST['includes_text'] ?? ''),
                (int)($_POST['sort_order'] ?? 0),
                $_POST['published'] ?? 'Yes'
            ];
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $wasUpdate = !empty($_POST['id']);
                if ($wasUpdate) {
                    $courseId = (int)$_POST['id'];
                    if ($courseId <= 0) throw new RuntimeException('Invalid course record.');
                    $data[] = $courseId;
                    $stmt = $pdo->prepare('UPDATE courses SET title=?, short_description=?, duration=?, level=?, price=?, pay_url=?, course_image=?, class_time=?, class_days=?, total_tests=?, lessons_count=?, course_details=?, outcomes=?, includes_text=?, sort_order=?, published=? WHERE id=?');
                    $stmt->execute($data);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO courses (title, short_description, duration, level, price, pay_url, course_image, class_time, class_days, total_tests, lessons_count, course_details, outcomes, includes_text, sort_order, published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                    $stmt->execute($data);
                    $courseId = (int)$pdo->lastInsertId();
                }

                $stmt = $pdo->prepare('DELETE FROM course_variants WHERE course_id=?');
                $stmt->execute([$courseId]);
                $variantTitles = is_array($_POST['variant_title'] ?? null) ? $_POST['variant_title'] : [];
                $insertVariant = $pdo->prepare('INSERT INTO course_variants (course_id, variant_title, price, class_time, class_days, total_tests, details, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                foreach ($variantTitles as $i => $title) {
                    $title = trim((string)$title);
                    if ($title === '') continue;
                    $insertVariant->execute([
                        $courseId,
                        mb_substr($title, 0, 180),
                        max(0, (float)($_POST['variant_price'][$i] ?? 0)),
                        mb_substr(trim((string)($_POST['variant_class_time'][$i] ?? '')), 0, 120),
                        mb_substr(trim((string)($_POST['variant_class_days'][$i] ?? '')), 0, 120),
                        max(0, (int)($_POST['variant_total_tests'][$i] ?? 0)),
                        mb_substr(trim((string)($_POST['variant_details'][$i] ?? '')), 0, 2000),
                        (int)($_POST['variant_sort_order'][$i] ?? $i)
                    ]);
                }
                $pdo->commit();
                flash('success', $wasUpdate ? 'Course updated.' : 'Course added.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
            redirect('courses.php');
        }
    } catch (Throwable $e) {
        error_log('[admin-courses] ' . $e->__toString());
        flash('error', 'Course could not be saved. Check the fields, upload and database setup.');
    }
}
$q = trim($_GET['q'] ?? '');
$published = trim($_GET['published'] ?? '');
$where = [];$params=[];
if ($q !== '') { $where[] = '(title LIKE ? OR short_description LIKE ? OR level LIKE ?)'; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
if ($published !== '') { $where[] = 'published=?'; $params[]=$published; }
$sql = 'SELECT * FROM courses' . ($where ? ' WHERE '.implode(' AND ', $where) : '') . ' ORDER BY sort_order ASC, id DESC';
$stmt = db()->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
?>
<div class="admin-top"><div><h1>Courses</h1><p>Manage course price, timing, tests, details, image and multiple variants.</p></div><div class="admin-actions"><a class="btn btn-soft" href="courses.php">Add New</a><a class="btn btn-primary" target="_blank" href="../courses.php">View Courses</a></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<div class="admin-form-layout course-admin-layout">
<form class="form-box" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>"><input type="hidden" name="existing_course_image" value="<?= e($edit['course_image'] ?? '') ?>">
    <div class="form-grid">
        <div class="form-section-title"><span><?= $edit ? '✎' : '+' ?></span><?= $edit ? 'Edit Course' : 'Add New Course' ?></div>
        <div class="field full"><label>Course Title *</label><input name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="Basic Spoken English"></div>
        <div class="field"><label>Price / Fee</label><input type="number" step="0.01" name="price" value="<?= e((string)($edit['price'] ?? 0)) ?>" placeholder="2999"></div>
        <div class="field"><label>Pay Now Link</label><input name="pay_url" value="<?= e($edit['pay_url'] ?? '') ?>" placeholder="Payment link or admission.php"></div>
        <div class="field"><label>Duration</label><input name="duration" value="<?= e($edit['duration'] ?? '') ?>" placeholder="3 Months"></div>
        <div class="field"><label>Level</label><input name="level" value="<?= e($edit['level'] ?? '') ?>" placeholder="Beginner"></div>
        <div class="field"><label>Class Time</label><input name="class_time" value="<?= e($edit['class_time'] ?? '') ?>" placeholder="7:00 AM - 8:00 AM"></div>
        <div class="field"><label>Class Days</label><input name="class_days" value="<?= e($edit['class_days'] ?? '') ?>" placeholder="Mon to Sat"></div>
        <div class="field"><label>Total Tests</label><input type="number" name="total_tests" value="<?= e((string)($edit['total_tests'] ?? 0)) ?>"></div>
        <div class="field"><label>Lessons / Classes</label><input type="number" name="lessons_count" value="<?= e((string)($edit['lessons_count'] ?? 0)) ?>"></div>
        <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= e((string)($edit['sort_order'] ?? 0)) ?>"></div>
        <div class="field"><label>Published</label><select name="published"><option <?= (($edit['published'] ?? 'Yes')==='Yes')?'selected':'' ?>>Yes</option><option <?= (($edit['published'] ?? '')==='No')?'selected':'' ?>>No</option></select></div>
        <div class="field"><label>Course Image</label><input type="file" name="course_image" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif"><?php if (!empty($edit['course_image'])): ?><small class="help">Current: <?= e($edit['course_image']) ?></small><?php endif; ?></div>
        <div class="field full"><label>Short Description</label><textarea name="short_description" placeholder="Explain the result students will get."><?= e($edit['short_description'] ?? '') ?></textarea></div>
        <div class="field full"><label>Detailed Description</label><textarea name="course_details" placeholder="Full details for course detail page."><?= e($edit['course_details'] ?? '') ?></textarea></div>
        <div class="field full"><label>Learning Outcomes</label><textarea name="outcomes" placeholder="One point per line. Example: Speak daily sentences confidently"><?= e($edit['outcomes'] ?? '') ?></textarea></div>
        <div class="field full"><label>Included In Course</label><textarea name="includes_text" placeholder="One point per line. Example: Weekly test, revision class, speaking practice"><?= e($edit['includes_text'] ?? '') ?></textarea></div>

        <div class="field full">
            <div class="form-section-title"><span>▣</span>Course Variants / Batches</div>
            <p class="help">Add multiple options like Morning Batch, Evening Batch, Fast Track, Weekend Course etc.</p>
            <div id="variantRows" class="variant-admin-list">
                <?php $variantRows = $variants ?: [['variant_title'=>'','price'=>'','class_time'=>'','class_days'=>'','total_tests'=>'','details'=>'','sort_order'=>0]]; foreach ($variantRows as $v): ?>
                <div class="variant-admin-row">
                    <input name="variant_title[]" placeholder="Variant title e.g. Morning Batch" value="<?= e($v['variant_title'] ?? '') ?>">
                    <input name="variant_price[]" type="number" step="0.01" placeholder="Price" value="<?= e((string)($v['price'] ?? '')) ?>">
                    <input name="variant_class_time[]" placeholder="Class time" value="<?= e($v['class_time'] ?? '') ?>">
                    <input name="variant_class_days[]" placeholder="Days" value="<?= e($v['class_days'] ?? '') ?>">
                    <input name="variant_total_tests[]" type="number" placeholder="Tests" value="<?= e((string)($v['total_tests'] ?? '')) ?>">
                    <input name="variant_sort_order[]" type="number" placeholder="Order" value="<?= e((string)($v['sort_order'] ?? 0)) ?>">
                    <textarea name="variant_details[]" placeholder="Short variant details"><?= e($v['details'] ?? '') ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-sm btn-soft" type="button" id="addVariantRow">+ Add Variant</button>
        </div>
        <div class="field full"><button class="btn btn-primary"><?= $edit ? 'Update Course' : 'Save Course' ?></button><?php if ($edit): ?><a class="btn btn-soft" href="courses.php">Cancel Edit</a><?php endif; ?></div>
    </div>
</form>
<div class="panel-card"><h3>Client-friendly course setup</h3><p>Fill price, class timing, days, tests and outcomes. These details will show on course cards and detail pages.</p><div class="premium-list"><div><span>₹</span><p><b>Price visible</b> Student can compare courses quickly.</p></div><div><span>🖼</span><p><b>Image allowed</b> Only PNG, JPG, JPEG and GIF are accepted.</p></div><div><span>🧩</span><p><b>Variants</b> Add batch-wise options without creating separate pages.</p></div></div></div>
</div>
<br>
<div class="panel-card">
    <div class="toolbar"><div><h2 style="margin:0;color:var(--navy)">Course List</h2></div><form method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search course"><select name="published"><option value="">All</option><option value="Yes" <?= $published==='Yes'?'selected':'' ?>>Published</option><option value="No" <?= $published==='No'?'selected':'' ?>>Unpublished</option></select><button class="btn btn-sm btn-dark">Filter</button></form></div>
    <div class="table-wrap"><table><thead><tr><th>Course</th><th>Price</th><th>Duration</th><th>Time</th><th>Tests</th><th>Published</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td data-label="Course"><strong><?= e($row['title']) ?></strong><br><span class="help"><?= e($row['short_description']) ?></span></td><td data-label="Price"><?= e(course_money_label($row['price'] ?? 0)) ?></td><td data-label="Duration"><?= e($row['duration']) ?></td><td data-label="Time"><?= e($row['class_time'] ?? '') ?></td><td data-label="Tests"><?= e((string)($row['total_tests'] ?? 0)) ?></td><td data-label="Published"><span class="badge <?= $row['published']==='Yes'?'badge-yes':'badge-no' ?>"><?= e($row['published']) ?></span></td><td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-soft" href="courses.php?edit=<?= e((string)$row['id']) ?>">Edit</a><a class="btn btn-sm btn-primary" target="_blank" href="../course-detail.php?id=<?= e((string)$row['id']) ?>">Details</a><form method="post" data-confirm="Delete this course?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="7" class="empty-state">No courses found.</td></tr><?php endif; ?></tbody></table></div>
</div>
<script>
document.getElementById('addVariantRow')?.addEventListener('click', function(){
    const box = document.getElementById('variantRows');
    const div = document.createElement('div');
    div.className = 'variant-admin-row';
    div.innerHTML = `<input name="variant_title[]" placeholder="Variant title e.g. Evening Batch"><input name="variant_price[]" type="number" step="0.01" placeholder="Price"><input name="variant_class_time[]" placeholder="Class time"><input name="variant_class_days[]" placeholder="Days"><input name="variant_total_tests[]" type="number" placeholder="Tests"><input name="variant_sort_order[]" type="number" placeholder="Order"><textarea name="variant_details[]" placeholder="Short variant details"></textarea>`;
    box.appendChild(div);
});
</script>
<?php require_once __DIR__ . '/_footer.php'; ?>
