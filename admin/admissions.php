<?php
$admin_page_final_styles = ['assets/css/phase173-admissions-cards.css'];
require_once __DIR__ . '/_header.php';
ensure_schema_updates();

$statuses = ['New','Pending','Approved','Not Approved','Joined','Cancelled'];
$paymentStatuses = ['Unpaid','Partial','Paid','Refund'];
$levels = ['Zero Level','Basic','Intermediate','Advanced'];
$admissionBackendReady = table_exists('admission_payments') && table_exists('student_enrollments') && table_exists('student_batch_memberships') && column_exists('admissions','enquiry_id') && column_exists('admissions','student_id') && column_exists('admissions','course_id') && column_exists('admissions','batch_id');
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM admissions WHERE id=? AND status_deleted=0 LIMIT 1');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_validate($_POST['csrf_token'] ?? '')) {
    $draft = $_POST;
    unset($draft['csrf_token'], $draft['action']);
    $_SESSION['wf149_admission_draft'] = ['saved_at'=>time(), 'values'=>$draft];
    flash('error','Security token expired. Your form entries were kept; refresh the page and submit again.');
    $suffix = !empty($_POST['id']) ? '?edit='.(int)$_POST['id'].'&restore=1' : '?restore=1';
    redirect('admissions.php'.$suffix);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? 'save';
    $draftPhoto = '';
    try {
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            db()->prepare("UPDATE admissions SET admission_status='Cancelled' WHERE id=? AND status_deleted=0")->execute([$id]);
            lifecycle_sync_admission($id);
            db()->prepare('UPDATE admissions SET status_deleted=1, deleted_at=NOW() WHERE id=?')->execute([$id]);
            admin_audit_log('admission.hidden','admission',$id,'Admission hidden and linked enrollment cancelled.');
            flash('success', 'Admission record safely hidden and its linked enrollment was cancelled.');
            redirect('admissions.php');
        }
        if ($action === 'status') {
            $id = (int)($_POST['id'] ?? 0);
            $status = in_array($_POST['admission_status'] ?? 'New', $statuses, true) ? $_POST['admission_status'] : 'New';
            db()->prepare('UPDATE admissions SET admission_status=? WHERE id=?')->execute([$status, $id]);
            lifecycle_sync_admission($id);
            admin_audit_log('admission.status','admission',$id,'Admission status changed to '.$status.'.');
            flash('success', 'Admission status updated.');
            redirect('admissions.php');
        }
        if ($action === 'save') {
            if (!$admissionBackendReady) throw new RuntimeException('Database upgrade required before saving admissions. Import sql/phase148_critical_backend_hardening.sql and run System Check. Your entered form values will be restored.');
            $id = (int)($_POST['id'] ?? 0);
            $oldPhoto = trim((string)($_POST['existing_student_photo'] ?? ''));
            if ($id > 0) {
                $oldPhotoStmt = db()->prepare('SELECT student_photo FROM admissions WHERE id=? AND status_deleted=0 LIMIT 1');
                $oldPhotoStmt->execute([$id]);
                $oldPhoto = (string)($oldPhotoStmt->fetchColumn() ?: '');
            }
            $photo = (($_POST['remove_student_photo'] ?? 'No') === 'Yes') ? '' : $oldPhoto;
            $draftPhoto = $photo;
            if (!empty($_FILES['student_photo']['name'])) {
                $uploaded = upload_admission_photo($_FILES['student_photo']);
                if ($uploaded) { $photo = $uploaded; $draftPhoto = $uploaded; }
            }
            $name = trim((string)($_POST['student_name'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            if ($name === '' || $phone === '') throw new RuntimeException('Student name and phone are required.');
            $status = in_array($_POST['admission_status'] ?? 'New', $statuses, true) ? $_POST['admission_status'] : 'New';
            $admissionDate = trim((string)($_POST['admission_date'] ?? '')) ?: null;
            $dob = trim((string)($_POST['dob'] ?? '')) ?: null;
            $dueDate = trim((string)($_POST['due_date'] ?? '')) ?: null;
            $follow = trim((string)($_POST['next_follow_up'] ?? '')) ?: null;
            $courseId=(int)($_POST['course_id']??0); $batchId=(int)($_POST['batch_id']??0);
            $courseTitle='';
            if($courseId>0){$c=db()->prepare("SELECT title FROM courses WHERE id=? AND published='Yes' LIMIT 1");$c->execute([$courseId]);$courseTitle=(string)($c->fetchColumn()?:'');if($courseTitle==='')throw new RuntimeException('Please select a valid active course.');}
            $batchLabel='';
            if($batchId>0){$b=db()->prepare("SELECT batch_name,timing,course_id FROM batch_timings WHERE id=? AND published='Yes' LIMIT 1");$b->execute([$batchId]);$br=$b->fetch();if(!$br)throw new RuntimeException('Please select a valid active batch.');$batchCourseId=(int)($br['course_id']??0);if($courseId>0&&$batchCourseId>0&&$batchCourseId!==$courseId)throw new RuntimeException('Selected batch does not belong to the selected course.');$batchLabel=trim((string)$br['batch_name'].(!empty($br['timing'])?' - '.$br['timing']:''));if($courseId<=0)$courseId=$batchCourseId;}
            if($courseId>0&&$courseTitle===''){$c=db()->prepare("SELECT title FROM courses WHERE id=? AND published='Yes' LIMIT 1");$c->execute([$courseId]);$courseTitle=(string)($c->fetchColumn()?:'');if($courseTitle==='')throw new RuntimeException('The batch is not linked to a valid active course.');}
            $totalFee=max(0.0,(float)($_POST['total_fee']??0));$discount=max(0.0,(float)($_POST['discount_amount']??0));if($discount>$totalFee)throw new RuntimeException('Discount cannot be greater than the total fee.');
            $digits=clean_phone_digits($phone);if(strlen($digits)>10)$digits=substr($digits,-10);$studentId=0;$enquiryId=(int)($_POST['enquiry_id']??0);
            if(strlen($digits)===10){$studentSql=column_exists('students','identity_status') ? "SELECT id FROM students WHERE status_deleted=0 AND identity_status='Verified' AND RIGHT(phone,10)=? LIMIT 1" : 'SELECT id FROM students WHERE status_deleted=0 AND RIGHT(phone,10)=? LIMIT 1';$st=db()->prepare($studentSql);$st->execute([$digits]);$studentId=(int)($st->fetchColumn()?:0);if($enquiryId<=0){$eq=db()->prepare("SELECT id FROM enquiries WHERE status_deleted=0 AND RIGHT(phone,10)=? ORDER BY id DESC LIMIT 1");$eq->execute([$digits]);$enquiryId=(int)($eq->fetchColumn()?:0);}}
            $common=[ $photo,$name,$phone,trim((string)($_POST['alt_phone']??'')),trim((string)($_POST['email']??'')),trim((string)($_POST['gender']??'')),$dob,trim((string)($_POST['guardian_name']??'')),trim((string)($_POST['address']??'')),$courseTitle?:null,$batchLabel?:null,trim((string)($_POST['current_level']??'')),trim((string)($_POST['source_label']??'')),$status,trim((string)($_POST['fee_plan_name']??'')),$totalFee,$discount,$admissionDate,$dueDate,$follow,trim((string)($_POST['documents_received']??'')),trim((string)($_POST['counselor_name']??'')),trim((string)($_POST['admin_note']??'')),($_POST['published']??'Yes')==='No'?'No':'Yes',$enquiryId?:null,$studentId?:null,$courseId?:null,$batchId?:null ];
            if ($id > 0) {
                $common[]=$id;
                $stmt=db()->prepare('UPDATE admissions SET student_photo=?,student_name=?,phone=?,alt_phone=?,email=?,gender=?,dob=?,guardian_name=?,address=?,course_interest=?,batch_preference=?,current_level=?,source_label=?,admission_status=?,fee_plan_name=?,total_fee=?,discount_amount=?,admission_date=?,due_date=?,next_follow_up=?,documents_received=?,counselor_name=?,admin_note=?,published=?,enquiry_id=?,student_id=?,course_id=?,batch_id=? WHERE id=?');$stmt->execute($common);if($oldPhoto!==''&&$oldPhoto!==$photo){managed_upload_cleanup($oldPhoto);$oldPhoto=$photo;}admission_recalculate_ledger($id);lifecycle_sync_admission($id);admin_audit_log('admission.updated','admission',$id,'Admission profile/lifecycle updated.');flash('success','Admission updated successfully. Payments remain in the immutable ledger.');
            } else {
                $stmt=db()->prepare("INSERT INTO admissions (student_photo,student_name,phone,alt_phone,email,gender,dob,guardian_name,address,course_interest,batch_preference,current_level,source_label,admission_status,fee_plan_name,total_fee,discount_amount,admission_date,due_date,next_follow_up,documents_received,counselor_name,admin_note,published,enquiry_id,student_id,course_id,batch_id,paid_amount,payment_status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,'Unpaid',NOW())");$stmt->execute($common);$id=(int)db()->lastInsertId();if($oldPhoto!==''&&$oldPhoto!==$photo){managed_upload_cleanup($oldPhoto);$oldPhoto=$photo;}
                $initialPaid=(float)($_POST['paid_amount']??0);if($initialPaid>0)admission_add_payment($id,'Payment',$initialPaid,trim((string)($_POST['payment_mode']??'')),trim((string)($_POST['reference_no']??'')),trim((string)($_POST['receipt_no']??'')),'Initial admission payment');
                if($enquiryId>0)db()->prepare("UPDATE enquiries SET enquiry_status='Converted',converted_admission_id=?,converted_at=NOW() WHERE id=?")->execute([$id,$enquiryId]);lifecycle_sync_admission($id);admin_audit_log('admission.created','admission',$id,'Admission created.');flash('success','Admission record added successfully.');
            }
            unset($_SESSION['wf149_admission_draft']);
            redirect('admission-view.php?id='.$id);
        }
    } catch (Throwable $e) {
        error_log('[admin-admissions] ' . $e->__toString());
        if ($action === 'save') {
            $draft = $_POST;
            unset($draft['csrf_token'], $draft['action']);
            if ($draftPhoto !== '') $draft['student_photo'] = $draftPhoto;
            $_SESSION['wf149_admission_draft'] = ['saved_at'=>time(), 'values'=>$draft];
        }
        flash('error', ($e instanceof RuntimeException && !($e instanceof PDOException)) ? $e->getMessage() : 'Admission record could not be saved. Your entered values were kept. Check System Check/database setup and submit again.');
        $suffix = !empty($_POST['id']) ? '?edit='.(int)$_POST['id'].'&restore=1' : '?restore=1';
        redirect('admissions.php' . $suffix);
    }
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
$admissionWhere = implode(' AND ', $where);
$countStmt = db()->prepare('SELECT COUNT(*) FROM admissions WHERE ' . $admissionWhere);
$countStmt->execute($params);
$admissionPager = admin_pagination_state((int)$countStmt->fetchColumn(), 24);
$stmt = db()->prepare('SELECT * FROM admissions WHERE ' . $admissionWhere . ' ORDER BY COALESCE(admission_date, DATE(created_at)) DESC, id DESC LIMIT ' . $admissionPager['per_page'] . ' OFFSET ' . $admissionPager['offset']);
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
$batches = fetch_batch_timings(200);
$formData = $edit ?: [];
$restoredAdmissionDraft = false;
if (isset($_GET['restore'], $_SESSION['wf149_admission_draft']['values']) && is_array($_SESSION['wf149_admission_draft']['values'])) {
    $age = time() - (int)($_SESSION['wf149_admission_draft']['saved_at'] ?? 0);
    if ($age >= 0 && $age <= 3600) {
        $formData = array_merge($formData, $_SESSION['wf149_admission_draft']['values']);
        $restoredAdmissionDraft = true;
    } else { unset($_SESSION['wf149_admission_draft']); }
}
?>
<div class="admin-page-head admission-admin-head"><div><span class="eyebrow">Admission CRM</span><h1>Admissions</h1><p>Advanced admin-only admission records with photo, course, batch, fee plan, payment and follow-up management.</p></div><div class="head-actions"><a class="btn btn-soft" href="admissions.php">Add New</a><a class="btn btn-primary" href="../admission.php" target="_blank">View Public Form</a></div></div>
<?php if ($msg=flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?><?php if ($msg=flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<?php if (!$admissionBackendReady): ?><div class="wf149-schema-warning"><i class="fa-solid fa-database"></i><div><b>Admission database upgrade is required before Save.</b><span>Import <code>sql/phase148_critical_backend_hardening.sql</code>. The form can still be filled, and Phase 149 will restore your entries if a save fails.</span></div><a class="btn btn-sm btn-dark" href="system-check.php">System Check</a></div><?php endif; ?>
<?php if ($restoredAdmissionDraft): ?><div class="wf149-form-restored"><b>Form restored:</b> your previous admission entries were kept after the save error. Correct the issue and submit again.</div><?php endif; ?>

<div class="student-crm-stats admission-stats"><a href="admissions.php"><b><?= e((string)$stats['total']) ?></b><span>Total Admission</span></a><a href="admissions.php?status=Approved"><b><?= e((string)$stats['approved']) ?></b><span>Approved</span></a><a href="admissions.php?status=Joined"><b><?= e((string)$stats['joined']) ?></b><span>Joined</span></a><a href="admissions.php?payment_status=Partial"><b><?= e((string)$stats['partial']) ?></b><span>Partial Fee</span></a><a href="admissions.php"><b><?= e((string)$stats['due']) ?></b><span>Fee Due</span></a></div>

<form class="admission-master-form" method="post" enctype="multipart/form-data" data-form-draft-key="admission-main">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string)($formData['id'] ?? '')) ?>"><input type="hidden" name="existing_student_photo" value="<?= e($formData['student_photo'] ?? '') ?>">
<div class="admission-editor-grid">
  <section class="panel-card"><div class="form-section-title"><span><?= $edit?'✎':'+' ?></span><?= $edit?'Edit Admission':'Add Admission' ?></div><div class="admission-photo-row"><div class="admission-photo-preview" id="admissionPhotoPreview"><?php $photo=site_asset_url($formData['student_photo'] ?? ''); if($photo): ?><img src="../<?= e($photo) ?>" alt="Student photo"><?php else: ?><span><?= e($edit?adm_initials($formData):'AD') ?></span><?php endif; ?></div><label>Student Photo<input type="file" name="student_photo" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" data-preview-target="admissionPhotoPreview"><?php if (!empty($formData['student_photo'])): ?><small class="help"><input type="checkbox" name="remove_student_photo" value="Yes"> Remove current photo</small><?php endif; ?></label></div><div class="form-grid"><div class="field full"><label>Student Name *</label><input name="student_name" required value="<?= e($formData['student_name'] ?? '') ?>" placeholder="Student full name"></div><div class="field"><label>Phone *</label><input name="phone" required value="<?= e($formData['phone'] ?? '') ?>" placeholder="Mobile number"></div><div class="field"><label>Alt Phone</label><input name="alt_phone" value="<?= e($formData['alt_phone'] ?? '') ?>"></div><div class="field"><label>Email</label><input type="email" name="email" value="<?= e($formData['email'] ?? '') ?>"></div><div class="field"><label>Gender</label><select name="gender"><option value="">Select</option><?php foreach(['Male','Female','Other'] as $g): ?><option <?= ($formData['gender']??'')===$g?'selected':'' ?>><?= e($g) ?></option><?php endforeach; ?></select></div><div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?= e($formData['dob'] ?? '') ?>"></div><div class="field"><label>Guardian Name</label><input name="guardian_name" value="<?= e($formData['guardian_name'] ?? '') ?>"></div><div class="field"><label>Source</label><input name="source_label" value="<?= e($formData['source_label'] ?? '') ?>" placeholder="Walk-in / Facebook / Google"></div><div class="field full"><label>Address</label><textarea name="address" rows="3" placeholder="Full address"><?= e($formData['address'] ?? '') ?></textarea></div></div></section>
  <section class="panel-card"><div class="form-section-title"><span>📚</span>Course, Fee & Follow-up</div><div class="form-grid"><input type="hidden" name="enquiry_id" value="<?= e((string)($formData['enquiry_id'] ?? ($_GET['enquiry_id'] ?? 0))) ?>"><div class="field"><label>Course</label><select name="course_id"><option value="0">Select course</option><?php foreach($courses as $c): ?><option value="<?= e((string)$c['id']) ?>" <?= (int)($formData['course_id']??0)===(int)$c['id']?'selected':'' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Batch</label><select name="batch_id"><option value="0">Select batch</option><?php foreach($batches as $b): ?><option value="<?= e((string)$b['id']) ?>" <?= (int)($formData['batch_id']??0)===(int)$b['id']?'selected':'' ?>><?= e($b['batch_name']) ?><?= !empty($b['timing'])?' · '.e($b['timing']):'' ?></option><?php endforeach; ?></select></div><div class="field"><label>Current Level</label><select name="current_level"><option value="">Select</option><?php foreach($levels as $lv): ?><option <?= ($formData['current_level']??'')===$lv?'selected':'' ?>><?= e($lv) ?></option><?php endforeach; ?></select></div><div class="field"><label>Admission Status</label><select name="admission_status"><?php foreach($statuses as $st): ?><option <?= ($formData['admission_status']??'New')===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select></div><div class="field"><label>Fee Plan Name</label><input name="fee_plan_name" value="<?= e($formData['fee_plan_name'] ?? '') ?>" placeholder="Monthly / Full Course / Installment"></div><div class="field"><label>Total Fee</label><input type="number" step="0.01" name="total_fee" value="<?= e((string)($formData['total_fee'] ?? 0)) ?>"></div><div class="field"><label>Discount</label><input type="number" step="0.01" name="discount_amount" value="<?= e((string)($formData['discount_amount'] ?? 0)) ?>"></div><?php if(!$edit): ?><div class="field"><label>Initial Payment</label><input type="number" step="0.01" min="0" name="paid_amount" value="<?= e((string)($formData['paid_amount'] ?? 0)) ?>"><small class="help">Saved as the first immutable payment-ledger entry.</small></div><div class="field"><label>Payment Mode</label><input name="payment_mode" value="<?= e($formData['payment_mode'] ?? '') ?>" placeholder="Cash / UPI / Bank"></div><div class="field"><label>Reference / UPI ID</label><input name="reference_no" value="<?= e($formData['reference_no'] ?? '') ?>"></div><div class="field"><label>Receipt No. (optional)</label><input name="receipt_no" value="<?= e($formData['receipt_no'] ?? '') ?>" placeholder="Auto generated if blank"></div><?php else: ?><div class="field full"><div class="alert alert-info">Paid amount and payment status are calculated from the Payment Ledger. Add/refund money from the admission detail page instead of overwriting history.</div></div><?php endif; ?><div class="field"><label>Admission Date</label><input type="date" name="admission_date" value="<?= e($formData['admission_date'] ?? date('Y-m-d')) ?>"></div><div class="field"><label>Due Date</label><input type="date" name="due_date" value="<?= e($formData['due_date'] ?? '') ?>"></div><div class="field"><label>Next Follow-up</label><input type="date" name="next_follow_up" value="<?= e($formData['next_follow_up'] ?? '') ?>"></div><div class="field"><label>Counselor</label><input name="counselor_name" value="<?= e($formData['counselor_name'] ?? '') ?>"></div><div class="field"><label>Visible</label><select name="published"><option value="Yes" <?= ($formData['published']??'Yes')==='Yes'?'selected':'' ?>>Yes</option><option value="No" <?= ($formData['published']??'Yes')==='No'?'selected':'' ?>>No</option></select></div><div class="field full"><label>Documents Received</label><textarea name="documents_received" rows="2" placeholder="Photo, Aadhaar, marksheet, receipt etc."><?= e($formData['documents_received'] ?? '') ?></textarea></div><div class="field full"><label>Admin Note</label><textarea name="admin_note" rows="3" placeholder="Internal note / promise / fee discussion"><?= e($formData['admin_note'] ?? '') ?></textarea></div><div class="field full"><button class="btn btn-primary" type="submit"><?= $edit?'Update Admission':'Save Admission' ?></button><?php if($edit): ?><a class="btn btn-soft" href="admissions.php">Cancel Edit</a><?php endif; ?></div></div></section>
</div>
</form>

<div class="panel-card admission-filter-panel"><form method="get" class="student-filter-form"><input name="q" value="<?= e($q) ?>" placeholder="Search name, phone, email, guardian, receipt"><select name="status"><option value="">All Status</option><?php foreach($statuses as $st): ?><option value="<?= e($st) ?>" <?= $status===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select><select name="payment_status"><option value="">All Payment</option><?php foreach($paymentStatuses as $ps): ?><option value="<?= e($ps) ?>" <?= $pay===$ps?'selected':'' ?>><?= e($ps) ?></option><?php endforeach; ?></select><input name="course" value="<?= e($course) ?>" placeholder="Course filter"><button class="btn btn-dark">Filter</button><a class="btn btn-soft" href="admissions.php">Reset</a></form></div>

<div class="admission-card-grid">
<?php if(!$rows): ?><div class="admin-card empty-state">No admission records found.</div><?php endif; ?>
<?php foreach($rows as $r): $photo=site_asset_url($r['student_photo'] ?? ''); $balance=max(0,(float)$r['total_fee']-(float)$r['discount_amount']-(float)$r['paid_amount']); $phoneDigits=preg_replace('/\D+/', '', (string)$r['phone']); ?>
  <article class="admission-card" onclick="if(!event.target.closest('a,button,form,select')) location.href='admission-view.php?id=<?= e((string)$r['id']) ?>'">
    <div class="admission-card-top"><div class="admission-avatar"><?php if($photo): ?><img src="../<?= e($photo) ?>" alt="<?= e($r['student_name']) ?>"><?php else: ?><span><?= e(adm_initials($r)) ?></span><?php endif; ?></div><div class="admission-card-identity"><h2><?= e($r['student_name']) ?></h2><p class="admission-contact-line"><span><?= e($r['phone']) ?></span><?php if($r['guardian_name']): ?><span><?= e($r['guardian_name']) ?></span><?php endif; ?></p></div></div>
    <div class="admission-card-badges"><span class="badge <?= e(adm_badge($r['admission_status'])) ?>"><?= e($r['admission_status']) ?></span><span class="badge <?= e(adm_badge($r['payment_status'])) ?>"><?= e($r['payment_status']) ?></span></div>
    <div class="admission-mini-lines"><div><b><?= e($r['course_interest'] ?: '-') ?></b><span>Course</span></div><div><b><?= e(adm_money($balance)) ?></b><span>Balance</span></div><div><b><?= e($r['batch_preference'] ?: '-') ?></b><span>Batch</span></div><div><b><?= e($r['next_follow_up'] ?: '-') ?></b><span>Follow-up</span></div></div>
    <div class="admission-card-actions"><a class="btn btn-sm btn-primary" href="admission-view.php?id=<?= e((string)$r['id']) ?>">View</a><a class="btn btn-sm btn-soft" href="admissions.php?edit=<?= e((string)$r['id']) ?>">Edit</a><?php if($phoneDigits): ?><a class="btn btn-sm btn-green" target="_blank" href="https://wa.me/<?= e($phoneDigits) ?>">WhatsApp</a><?php endif; ?><form method="post" data-confirm="Delete/hide this admission record?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$r['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div>
  </article>
<?php endforeach; ?>
</div>
<?= admin_pagination_html($admissionPager) ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
