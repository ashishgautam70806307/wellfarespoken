<?php
require_once __DIR__ . '/_header.php';
ensure_schema_updates();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM admissions WHERE id=? AND status_deleted=0 LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { flash('error','Admission record not found.'); redirect('admissions.php'); }
$statuses = ['New','Pending','Approved','Not Approved','Joined','Cancelled'];
$paymentStatuses = ['Unpaid','Partial','Paid','Refund'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'quick_update') {
            $status = in_array($_POST['admission_status'] ?? 'New', $statuses, true) ? $_POST['admission_status'] : 'New';
            $pay = in_array($_POST['payment_status'] ?? 'Unpaid', $paymentStatuses, true) ? $_POST['payment_status'] : 'Unpaid';
            db()->prepare('UPDATE admissions SET admission_status=?, payment_status=?, paid_amount=?, next_follow_up=?, admin_note=? WHERE id=?')->execute([$status, $pay, (float)($_POST['paid_amount'] ?? 0), trim((string)($_POST['next_follow_up'] ?? '')) ?: null, trim((string)($_POST['admin_note'] ?? '')), $id]);
            flash('success','Admission quick update saved.');
            redirect('admission-view.php?id=' . $id);
        }
    } catch (Throwable $e) { error_log('[admin-admission-view] ' . $e->__toString()); flash('error', 'Admission status could not be updated.'); redirect('admission-view.php?id=' . $id); }
}
$photo = site_asset_url($row['student_photo'] ?? '');
$balance = max(0, (float)$row['total_fee'] - (float)$row['discount_amount'] - (float)$row['paid_amount']);
function admv_badge(string $v): string { $x=strtolower($v); if(in_array($x,['approved','joined','paid'],true)) return 'badge-yes'; if(in_array($x,['not approved','cancelled','unpaid'],true)) return 'badge-no'; if(in_array($x,['partial','pending','new'],true)) return 'badge-contacted'; return 'badge-muted'; }
function admv_money($v): string { return '₹' . number_format((float)$v, 0); }
function admv_initials(array $r): string { $n=trim((string)($r['student_name']??'A')); $p=preg_split('/\s+/', $n); $o=''; foreach(array_slice($p?:[],0,2) as $x){$o.=mb_substr($x,0,1);} return mb_strtoupper($o?:'A'); }
?>
<div class="admin-page-head admission-detail-head"><div><span class="eyebrow">Admission Details</span><h1><?= e($row['student_name']) ?></h1><p><?= e($row['phone']) ?><?= $row['course_interest'] ? ' • '.e($row['course_interest']) : '' ?></p></div><div class="head-actions"><a class="btn btn-soft" href="admissions.php">Back Admissions</a><a class="btn btn-primary" href="admissions.php?edit=<?= e((string)$id) ?>">Edit Full Record</a></div></div>
<?php if ($msg=flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?><?php if ($msg=flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="admission-view-hero panel-card">
  <div class="admission-view-photo"><?php if($photo): ?><img src="../<?= e($photo) ?>" alt="<?= e($row['student_name']) ?>"><?php else: ?><span><?= e(admv_initials($row)) ?></span><?php endif; ?></div>
  <div><div class="admission-card-badges"><span class="badge <?= e(admv_badge($row['admission_status'])) ?>"><?= e($row['admission_status']) ?></span><span class="badge <?= e(admv_badge($row['payment_status'])) ?>"><?= e($row['payment_status']) ?></span></div><h2><?= e($row['student_name']) ?></h2><p><?= e($row['guardian_name'] ? 'Guardian: '.$row['guardian_name'] : 'Guardian not saved') ?></p></div>
  <div class="fee-balance-card"><b><?= e(admv_money($balance)) ?></b><span>Balance Due</span><small>Total <?= e(admv_money($row['total_fee'])) ?> • Paid <?= e(admv_money($row['paid_amount'])) ?></small></div>
</div>

<div class="student-crm-stats admission-view-stats"><div><b><?= e(admv_money($row['total_fee'])) ?></b><span>Total Fee</span></div><div><b><?= e(admv_money($row['discount_amount'])) ?></b><span>Discount</span></div><div><b><?= e(admv_money($row['paid_amount'])) ?></b><span>Paid</span></div><div><b><?= e($row['due_date'] ?: '-') ?></b><span>Due Date</span></div><div><b><?= e($row['next_follow_up'] ?: '-') ?></b><span>Follow-up</span></div></div>

<div class="admission-detail-grid">
  <section class="panel-card"><h2>Student Details</h2><div class="detail-grid"><div><b>Phone</b><span><?= e($row['phone']) ?></span></div><div><b>Alt Phone</b><span><?= e($row['alt_phone'] ?: '-') ?></span></div><div><b>Email</b><span><?= e($row['email'] ?: '-') ?></span></div><div><b>Gender</b><span><?= e($row['gender'] ?: '-') ?></span></div><div><b>DOB</b><span><?= e($row['dob'] ?: '-') ?></span></div><div><b>Source</b><span><?= e($row['source_label'] ?: '-') ?></span></div><div class="full"><b>Address</b><span><?= nl2br(e($row['address'] ?: '-')) ?></span></div></div></section>
  <section class="panel-card"><h2>Course & Fee Details</h2><div class="detail-grid"><div><b>Course</b><span><?= e($row['course_interest'] ?: '-') ?></span></div><div><b>Batch</b><span><?= e($row['batch_preference'] ?: '-') ?></span></div><div><b>Level</b><span><?= e($row['current_level'] ?: '-') ?></span></div><div><b>Fee Plan</b><span><?= e($row['fee_plan_name'] ?: '-') ?></span></div><div><b>Mode</b><span><?= e($row['payment_mode'] ?: '-') ?></span></div><div><b>Receipt</b><span><?= e($row['receipt_no'] ?: '-') ?></span></div><div class="full"><b>Documents</b><span><?= nl2br(e($row['documents_received'] ?: '-')) ?></span></div></div></section>
</div>

<div class="admission-detail-grid">
  <section class="panel-card"><h2>Quick Update</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="quick_update"><div class="field"><label>Admission Status</label><select name="admission_status"><?php foreach($statuses as $st): ?><option <?= $row['admission_status']===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select></div><div class="field"><label>Payment Status</label><select name="payment_status"><?php foreach($paymentStatuses as $ps): ?><option <?= $row['payment_status']===$ps?'selected':'' ?>><?= e($ps) ?></option><?php endforeach; ?></select></div><div class="field"><label>Paid Amount</label><input type="number" step="0.01" name="paid_amount" value="<?= e((string)$row['paid_amount']) ?>"></div><div class="field"><label>Next Follow-up</label><input type="date" name="next_follow_up" value="<?= e($row['next_follow_up'] ?? '') ?>"></div><div class="field full"><label>Admin Note</label><textarea name="admin_note" rows="4"><?= e($row['admin_note'] ?? '') ?></textarea></div><div class="field full"><button class="btn btn-primary">Save Quick Update</button></div></form></section>
  <section class="panel-card"><h2>Admission Note</h2><p class="muted"><?= nl2br(e($row['admin_note'] ?: 'No internal note saved.')) ?></p><br><div class="admission-card-actions"><?php $phoneDigits=preg_replace('/\D+/', '', (string)$row['phone']); if($phoneDigits): ?><a class="btn btn-green" href="https://wa.me/<?= e($phoneDigits) ?>" target="_blank">WhatsApp</a><?php endif; ?><a class="btn btn-soft" href="tel:<?= e($row['phone']) ?>">Call</a><a class="btn btn-primary" href="admissions.php?edit=<?= e((string)$id) ?>">Edit</a></div></section>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
