<?php require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$edit=null;
if (isset($_GET['edit'])) { $stmt=db()->prepare('SELECT * FROM testimonials WHERE id=?');$stmt->execute([(int)$_GET['edit']]);$edit=$stmt->fetch(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action=$_POST['action'] ?? 'save';
    if ($action==='delete') { $stmt=db()->prepare('DELETE FROM testimonials WHERE id=?');$stmt->execute([(int)($_POST['id']??0)]);flash('success','Review deleted.');redirect('testimonials.php'); }
    if ($action==='save') {
        $id=(int)($_POST['id']??0);
        $student=trim((string)($_POST['student_name']??''));
        $role=trim((string)($_POST['reviewer_role']??'Student'));
        $msg=trim((string)($_POST['message']??''));
        $rating=max(1,min(5,(int)($_POST['rating']??5)));
        $date=trim((string)($_POST['review_date']??''));
        $source=trim((string)($_POST['source_label']??'Google'));
        $initials=trim((string)($_POST['avatar_initials']??''));
        $sort=(int)($_POST['sort_order']??0);
        $published=$_POST['published']??'Yes';
        $imagePath=trim((string)($edit['student_image']??''));
        if (!empty($_FILES['student_image']['tmp_name'])) {
            try {
                $uploaded = secure_image_upload($_FILES['student_image'], 'reviews', 'review', 2 * 1024 * 1024);
                if ($uploaded) $imagePath = $uploaded;
            } catch (RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('testimonials.php'.($id?'?edit='.$id:''));
            }
        }
        if ($student==='' || $msg==='') { flash('error','Student name and review message are required.'); redirect('testimonials.php'.($id?'?edit='.$id:'')); }
        if ($id>0) {
            $stmt=db()->prepare('UPDATE testimonials SET student_name=?, reviewer_role=?, message=?, rating=?, review_date=?, source_label=?, avatar_initials=?, student_image=?, sort_order=?, published=? WHERE id=?');
            $stmt->execute([$student,$role,$msg,$rating,$date,$source,$initials,$imagePath,$sort,$published,$id]);
            flash('success','Review updated.');
        } else {
            $stmt=db()->prepare('INSERT INTO testimonials (student_name, reviewer_role, message, rating, review_date, source_label, avatar_initials, student_image, sort_order, published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$student,$role,$msg,$rating,$date,$source,$initials,$imagePath,$sort,$published]);
            flash('success','Review added.');
        }
        redirect('testimonials.php');
    }
}
$rows=db()->query('SELECT * FROM testimonials ORDER BY sort_order ASC, id DESC')->fetchAll();
?>
<div class="admin-top"><div><h1>Review Manager</h1><p>Create Google-style review cards for the homepage. Reviews page/menu is not used.</p></div><div class="admin-actions"><a class="btn btn-soft" href="testimonials.php">Add New</a><a class="btn btn-primary" href="../index.php" target="_blank">View Homepage</a></div></div>
<?php if ($msg=flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg=flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<div class="admin-form-layout">
<form class="form-box review-admin-form" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string)($edit['id']??'')) ?>">
<h2><?= $edit?'Edit Review':'Add Review' ?></h2>
<div class="form-grid">
<label>Student Name <input name="student_name" value="<?= e($edit['student_name']??'') ?>" required placeholder="Example: Shubham Maurya"></label>
<label>Role / Time <input name="reviewer_role" value="<?= e($edit['reviewer_role']??'Student') ?>" placeholder="Example: a year ago"></label>
<label>Rating <select name="rating"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>" <?= ((int)($edit['rating']??5)===$i)?'selected':'' ?>><?= $i ?> Star</option><?php endfor; ?></select></label>
<label>Source Label <input name="source_label" value="<?= e($edit['source_label']??'Google') ?>" placeholder="Google"></label>
<label>Avatar Initials <input name="avatar_initials" value="<?= e($edit['avatar_initials']??'') ?>" placeholder="SM"></label>
<label>Sort Order <input name="sort_order" type="number" value="<?= e((string)($edit['sort_order']??0)) ?>"></label>
<label>Review Date <input name="review_date" value="<?= e($edit['review_date']??'') ?>" placeholder="11 months ago"></label>
<label>Status <select name="published"><option <?= (($edit['published']??'Yes')==='Yes')?'selected':'' ?>>Yes</option><option <?= (($edit['published']??'Yes')==='No')?'selected':'' ?>>No</option></select></label>
<label class="full">Student Photo optional <input type="file" name="student_image" accept=".png,.jpg,.jpeg,.gif"></label>
<label class="full">Review Message <textarea name="message" rows="5" required placeholder="Write student feedback..."><?= e($edit['message']??'') ?></textarea></label>
</div>
<button class="btn btn-primary">Save Review</button>
</form>
<div class="admin-card"><h2>Homepage Review Cards</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Rating</th><th>Message</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td data-label="Name"><b><?= e($r['student_name']) ?></b><br><small><?= e($r['reviewer_role'] ?? '') ?></small></td><td data-label="Rating"><?= str_repeat('★', max(1,min(5,(int)($r['rating']??5)))) ?></td><td data-label="Message"><?= e(mb_substr($r['message'],0,100)) ?>...</td><td data-label="Status"><span class="badge <?= ($r['published'] ?? 'Yes') === 'Yes' ? 'badge-yes' : 'badge-no' ?>"><?= e($r['published']) ?></span></td><td data-label="Action"><div class="table-actions"><a class="btn btn-soft btn-sm" href="?edit=<?= e((string)$r['id']) ?>">Edit</a><form method="post" data-confirm="Delete this review?" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$r['id']) ?>"><button class="btn btn-danger btn-sm">Delete</button></form></div></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="5" class="empty-state">No reviews yet.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
