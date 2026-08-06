<?php
require_once __DIR__ . '/_header.php';
ensure_schema_updates();

$statuses = ['New','Pending','Approved','Not Approved','Joined','Cancelled'];
$paymentStatuses = ['Unpaid','Partial','Paid','Refund'];
$levels = ['Zero Level','Basic','Intermediate','Advanced'];
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM admissions WHERE id=? AND status_deleted=0 LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? 'save';
    try {
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            db()->prepare('UPDATE admissions SET status_deleted=1, deleted_at=NOW() WHERE id=?')->execute([$id]);
            flash('success', 'Admission record safely hidden.');
            redirect('admissions.php');
        }
        if ($action === 'status') {
            $id = (int)($_POST['id'] ?? 0);
            $status = in_array($_POST['admission_status'] ?? 'New', $statuses, true) ? $_POST['admission_status'] : 'New';
            db()->prepare('UPDATE admissions SET admission_status=? WHERE id=?')->execute([$status, $id]);
            flash('success', 'Admission status updated.');
            redirect('admissions.php');
        }
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $photo = trim((string)($_POST['existing_student_photo'] ?? ''));
            if (!empty($_FILES['student_photo']['name'])) {
                $uploaded = upload_admission_photo($_FILES['student_photo']);
                if ($uploaded) $photo = $uploaded;
            }
            $name = trim((string)($_POST['student_name'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            if ($name === '' || $phone === '') throw new RuntimeException('Student name and phone are required.');
            $status = in_array($_POST['admission_status'] ?? 'New', $statuses, true) ? $_POST['admission_status'] : 'New';
            $payStatus = in_array($_POST['payment_status'] ?? 'Unpaid', $paymentStatuses, true) ? $_POST['payment_status'] : 'Unpaid';
            $admissionDate = trim((string)($_POST['admission_date'] ?? '')) ?: null;
            $dob = trim((string)($_POST['dob'] ?? '')) ?: null;
            $dueDate = trim((string)($_POST['due_date'] ?? '')) ?: null;
            $follow = trim((string)($_POST['next_follow_up'] ?? '')) ?: null;
            $data = [
                $photo, $name, $phone, trim((string)($_POST['alt_phone'] ?? '')), trim((string)($_POST['email'] ?? '')),
                trim((string)($_POST['gender'] ?? '')), $dob, trim((string)($_POST['guardian_name'] ?? '')), trim((string)($_POST['address'] ?? '')),
                trim((string)($_POST['course_interest'] ?? '')), trim((string)($_POST['batch_preference'] ?? '')), trim((string)($_POST['current_level'] ?? '')),
                trim((string)($_POST['source_label'] ?? '')), $status, trim((string)($_POST['fee_plan_name'] ?? '')),
                (float)($_POST['total_fee'] ?? 0), (float)($_POST['discount_amount'] ?? 0), (float)($_POST['paid_amount'] ?? 0),
                $payStatus, trim((string)($_POST['payment_mode'] ?? '')), trim((string)($_POST['receipt_no'] ?? '')), $admissionDate, $dueDate, $follow,
                trim((string)($_POST['documents_received'] ?? '')), trim((string)($_POST['counselor_name'] ?? '')), trim((string)($_POST['admin_note'] ?? '')), ($_POST['published'] ?? 'Yes') === 'No' ? 'No' : 'Yes'
            ];
            if ($id > 0) {
                $data[] = $id;
                $stmt = db()->prepare('UPDATE admissions SET student_photo=?, student_name=?, phone=?, alt_phone=?, email=?, gender=?, dob=?, guardian_name=?, address=?, course_interest=?, batch_preference=?, current_level=?, source_label=?, admission_status=?, fee_plan_name=?, total_fee=?, discount_amount=?, paid_amount=?, payment_status=?, payment_mode=?, receipt_no=?, admission_date=?, due_date=?, next_follow_up=?, documents_received=?, counselor_name=?, admin_note=?, published=? WHERE id=?');
                $stmt->execute($data);
                flash('success', 'Admission updated successfully.');
            } else {
                $stmt = db()->prepare('INSERT INTO admissions (student_photo, student_name, phone, alt_phone, email, gender, dob, guardian_name, address, course_interest, batch_preference, current_level, source_label, admission_status, fee_plan_name, total_fee, discount_amount, paid_amount, payment_status, payment_mode, receipt_no, admission_date, due_date, next_follow_up, documents_received, counselor_name, admin_note, published, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
                $stmt->execute($data);
                flash('success', 'Admission record added successfully.');
            }
            redirect('admissions.php');
        }
    } catch (Throwable $e) { error_log('[admin-admissions] ' . $e->__toString()); flash('error', 'Admission record could not be saved. Check required fields and database setup.'); redirect('admissions.php' . (!empty($_POST['id']) ? '?edit='.(int)$_POST['id'] : '')); }
}

$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$pay = trim((string)($_GET['payment_status'] ?? ''));
$course = trim((string)($_GET['course'] ?? ''));
$where = ['status_deleted=0']; $params = [];
if ($q !== '') { $where[] = '(student_name LIKE ? OR phone LIKE ? OR email LIKE ? OR guardian_name LIKE ? OR receipt_no LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%", "%$q%", "%$q%"); }
if ($status !== '') { $where[] = 'admission_status=?'; $params[] = $status; }
if ($pay !== '') { $where[] = 'payment_status=?'; $params[] = $pay; }
if ($course !== '') { $where[] = 'course_interest LIKE ?'; $params[] = "%$course%"; }
$stmt = db()->prepare('SELECT * FROM admissions WHERE '.implode(' AND ', $where).' ORDER BY COALESCE(admission_date, DATE(created_at)) DESC, id DESC');
$stmt->execute($params);
$rows = $stmt->fetchAll();
$stats = ['total'=>0,'approved'=>0,'joined'=>0,'partial'=>0,'due'=>0];
try {
  $stats['total'] = (int)db()->query("SELECT COUNT(*) FROM admissions WHERE status_deleted=0")->fetchColumn();
  $stats['approved'] = (int)db()->query("SELECT COUNT(*) FROM admissions WHERE status_deleted=0 AND admission_status='Approved'")->fetchColumn();
  $stats['joined'] = (int)db()->query("SELECT COUNT(*) FROM admissions WHERE status_deleted=0 AND admission_status='Joined'")->fetchColumn();
  $stats['partial'] = (int)db()->query("SELECT COUNT(*) FROM admissions WHERE status_deleted=0 AND payment_status='Partial'")->fetchColumn();
  $stats['due'] = (int)db()->query("SELECT COUNT(*) FROM admissions WHERE status_deleted=0 AND (total_fee-discount_amount-paid_amount)>0")->fetchColumn();
} catch(Throwable $e) {}
function adm_badge(string $v): string { $x=strtolower($v); if(in_array($x,['approved','joined','paid'],true)) return 'badge-yes'; if(in_array($x,['not approved','cancelled','unpaid'],true)) return 'badge-no'; if(in_array($x,['partial','pending','new'],true)) return 'badge-contacted'; return 'badge-muted'; }
function adm_initials(array $r): string { $n=trim((string)($r['student_name']??'A')); $p=preg_split('/\s+/', $n); $o=''; foreach(array_slice($p?:[],0,2) as $x){$o.=mb_substr($x,0,1);} return mb_strtoupper($o?:'A'); }
function adm_money($v): string { return '₹' . number_format((float)$v, 0); }
$courses = fetch_courses(100);
?>
<div class="admin-page-head admission-admin-head"><div><span class="eyebrow">Admission CRM</span><h1>Admissions</h1><p>Advanced admin-only admission records with photo, course, batch, fee plan, payment and follow-up management.</p></div><div class="head-actions"><a class="btn btn-soft" href="admissions.php">Add New</a><a class="btn btn-primary" href="../admission.php" target="_blank">View Public Form</a></div></div>
<?php if ($msg=flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?><?php if ($msg=flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="student-crm-stats admission-stats"><a href="admissions.php"><b><?= e((string)$stats['total']) ?></b><span>Total Admission</span></a><a href="admissions.php?status=Approved"><b><?= e((string)$stats['approved']) ?></b><span>Approved</span></a><a href="admissions.php?status=Joined"><b><?= e((string)$stats['joined']) ?></b><span>Joined</span></a><a href="admissions.php?payment_status=Partial"><b><?= e((string)$stats['partial']) ?></b><span>Partial Fee</span></a><a href="admissions.php"><b><?= e((string)$stats['due']) ?></b><span>Fee Due</span></a></div>

<form class="admission-master-form" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>"><input type="hidden" name="existing_student_photo" value="<?= e($edit['student_photo'] ?? '') ?>">
<div class="admission-editor-grid">
  <section class="panel-card"><div class="form-section-title"><span><?= $edit?'✎':'+' ?></span><?= $edit?'Edit Admission':'Add Admission' ?></div><div class="admission-photo-row"><div class="admission-photo-preview"><?php $photo=site_asset_url($edit['student_photo'] ?? ''); if($photo): ?><img src="../<?= e($photo) ?>" alt="Student photo"><?php else: ?><span><?= e($edit?adm_initials($edit):'AD') ?></span><?php endif; ?></div><label>Student Photo<input type="file" name="student_photo" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif"></label></div><div class="form-grid"><div class="field full"><label>Student Name *</label><input name="student_name" required value="<?= e($edit['student_name'] ?? '') ?>" placeholder="Student full name"></div><div class="field"><label>Phone *</label><input name="phone" required value="<?= e($edit['phone'] ?? '') ?>" placeholder="Mobile number"></div><div class="field"><label>Alt Phone</label><input name="alt_phone" value="<?= e($edit['alt_phone'] ?? '') ?>"></div><div class="field"><label>Email</label><input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>"></div><div class="field"><label>Gender</label><select name="gender"><option value="">Select</option><?php foreach(['Male','Female','Other'] as $g): ?><option <?= ($edit['gender']??'')===$g?'selected':'' ?>><?= e($g) ?></option><?php endforeach; ?></select></div><div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?= e($edit['dob'] ?? '') ?>"></div><div class="field"><label>Guardian Name</label><input name="guardian_name" value="<?= e($edit['guardian_name'] ?? '') ?>"></div><div class="field"><label>Source</label><input name="source_label" value="<?= e($edit['source_label'] ?? '') ?>" placeholder="Walk-in / Facebook / Google"></div><div class="field full"><label>Address</label><textarea name="address" rows="3" placeholder="Full address"><?= e($edit['address'] ?? '') ?></textarea></div></div></section>
  <section class="panel-card"><div class="form-section-title"><span>📚</span>Course, Fee & Follow-up</div><div class="form-grid"><div class="field"><label>Course Interest</label><input name="course_interest" list="courseList" value="<?= e($edit['course_interest'] ?? '') ?>" placeholder="Spoken English"><datalist id="courseList"><?php foreach($courses as $c): ?><option value="<?= e($c['title']) ?>"><?php endforeach; ?></datalist></div><div class="field"><label>Batch Preference</label><input name="batch_preference" value="<?= e($edit['batch_preference'] ?? '') ?>" placeholder="Morning / Evening"></div><div class="field"><label>Current Level</label><select name="current_level"><option value="">Select</option><?php foreach($levels as $lv): ?><option <?= ($edit['current_level']??'')===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select></div><div class="field"><label>Admission Status</label><select name="admission_status"><?php foreach($statuses as $st): ?><option <?= ($edit['admission_status']??'New')===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select></div><div class="field"><label>Fee Plan Name</label><input name="fee_plan_name" value="<?= e($edit['fee_plan_name'] ?? '') ?>" placeholder="Monthly / Full Course / Installment"></div><div class="field"><label>Total Fee</label><input type="number" step="0.01" name="total_fee" value="<?= e((string)($edit['total_fee'] ?? 0)) ?>"></div><div class="field"><label>Discount</label><input type="number" step="0.01" name="discount_amount" value="<?= e((string)($edit['discount_amount'] ?? 0)) ?>"></div><div class="field"><label>Paid Amount</label><input type="number" step="0.01" name="paid_amount" value="<?= e((string)($edit['paid_amount'] ?? 0)) ?>"></div><div class="field"><label>Payment Status</label><select name="payment_status"><?php foreach($paymentStatuses as $ps): ?><option <?= ($edit['payment_status']??'Unpaid')===$ps?'selected':'' ?>><?= e($ps) ?></option><?php endforeach; ?></select></div><div class="field"><label>Payment Mode</label><input name="payment_mode" value="<?= e($edit['payment_mode'] ?? '') ?>" placeholder="Cash / UPI / Bank"></div><div class="field"><label>Receipt No.</label><input name="receipt_no" value="<?= e($edit['receipt_no'] ?? '') ?>"></div><div class="field"><label>Admission Date</label><input type="date" name="admission_date" value="<?= e($edit['admission_date'] ?? date('Y-m-d')) ?>"></div><div class="field"><label>Due Date</label><input type="date" name="due_date" value="<?= e($edit['due_date'] ?? '') ?>"></div><div class="field"><label>Next Follow-up</label><input type="date" name="next_follow_up" value="<?= e($edit['next_follow_up'] ?? '') ?>"></div><div class="field"><label>Counselor</label><input name="counselor_name" value="<?= e($edit['counselor_name'] ?? '') ?>"></div><div class="field"><label>Visible</label><select name="published"><option value="Yes" <?= ($edit['published']??'Yes')==='Yes'?'selected':'' ?>>Yes</option><option value="No" <?= ($edit['published']??'Yes')==='No'?'selected':'' ?>>No</option></select></div><div class="field full"><label>Documents Received</label><textarea name="documents_received" rows="2" placeholder="Photo, Aadhaar, marksheet, receipt etc."><?= e($edit['documents_received'] ?? '') ?></textarea></div><div class="field full"><label>Admin Note</label><textarea name="admin_note" rows="3" placeholder="Internal note / promise / fee discussion"><?= e($edit['admin_note'] ?? '') ?></textarea></div><div class="field full"><button class="btn btn-primary" type="submit"><?= $edit?'Update Admission':'Save Admission' ?></button><?php if($edit): ?><a class="btn btn-soft" href="admissions.php">Cancel Edit</a><?php endif; ?></div></div></section>
</div>
</form>

<div class="panel-card admission-filter-panel"><form method="get" class="student-filter-form"><input name="q" value="<?= e($q) ?>" placeholder="Search name, phone, email, guardian, receipt"><select name="status"><option value="">All Status</option><?php foreach($statuses as $st): ?><option value="<?= e($st) ?>" <?= $status===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select><select name="payment_status"><option value="">All Payment</option><?php foreach($paymentStatuses as $ps): ?><option value="<?= e($ps) ?>" <?= $pay===$ps?'selected':'' ?>><?= e($ps) ?></option><?php endforeach; ?></select><input name="course" value="<?= e($course) ?>" placeholder="Course filter"><button class="btn btn-dark">Filter</button><a class="btn btn-soft" href="admissions.php">Reset</a></form></div>

<div class="admission-card-grid">
<?php if(!$rows): ?><div class="admin-card empty-state">No admission records found.</div><?php endif; ?>
<?php foreach($rows as $r): $photo=site_asset_url($r['student_photo'] ?? ''); $balance=max(0,(float)$r['total_fee']-(float)$r['discount_amount']-(float)$r['paid_amount']); $phoneDigits=preg_replace('/\D+/', '', (string)$r['phone']); ?>
  <article class="admission-card" onclick="if(!event.target.closest('a,button,form,select')) location.href='admission-view.php?id=<?= e((string)$r['id']) ?>'">
    <div class="admission-card-top"><div class="admission-avatar"><?php if($photo): ?><img src="../<?= e($photo) ?>" alt="<?= e($r['student_name']) ?>"><?php else: ?><span><?= e(adm_initials($r)) ?></span><?php endif; ?></div><div><h2><?= e($r['student_name']) ?></h2><p><?= e($r['phone']) ?><?= $r['guardian_name']?' • '.e($r['guardian_name']):'' ?></p></div></div>
    <div class="admission-card-badges"><span class="badge <?= e(adm_badge($r['admission_status'])) ?>"><?= e($r['admission_status']) ?></span><span class="badge <?= e(adm_badge($r['payment_status'])) ?>"><?= e($r['payment_status']) ?></span></div>
    <div class="admission-mini-lines"><div><b><?= e($r['course_interest'] ?: '-') ?></b><span>Course</span></div><div><b><?= e(adm_money($balance)) ?></b><span>Balance</span></div><div><b><?= e($r['batch_preference'] ?: '-') ?></b><span>Batch</span></div><div><b><?= e($r['next_follow_up'] ?: '-') ?></b><span>Follow-up</span></div></div>
    <div class="admission-card-actions"><a class="btn btn-sm btn-primary" href="admission-view.php?id=<?= e((string)$r['id']) ?>">View</a><a class="btn btn-sm btn-soft" href="admissions.php?edit=<?= e((string)$r['id']) ?>">Edit</a><?php if($phoneDigits): ?><a class="btn btn-sm btn-green" target="_blank" href="https://wa.me/<?= e($phoneDigits) ?>">WhatsApp</a><?php endif; ?><form method="post" data-confirm="Delete/hide this admission record?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$r['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div>
  </article>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
