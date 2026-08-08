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
            db()->prepare('UPDATE admissions SET admission_status=?, next_follow_up=?, admin_note=? WHERE id=?')->execute([$status, trim((string)($_POST['next_follow_up'] ?? '')) ?: null, trim((string)($_POST['admin_note'] ?? '')), $id]);
            lifecycle_sync_admission($id);
            admin_audit_log('admission.status','admission',$id,'Admission status changed to '.$status.'.');
            flash('success','Admission quick update saved.');
            redirect('admission-view.php?id=' . $id);
        }
        if ($action === 'add_payment') {
            $type = in_array($_POST['entry_type'] ?? 'Payment', ['Payment','Refund','Adjustment'], true) ? $_POST['entry_type'] : 'Payment';
            $amount = (float)($_POST['amount'] ?? 0);
            admission_add_payment($id,$type,$amount,trim((string)($_POST['payment_mode']??'')),trim((string)($_POST['reference_no']??'')),trim((string)($_POST['receipt_no']??'')),trim((string)($_POST['note']??'')),trim((string)($_POST['entry_date']??'')) ?: null);
            flash('success',$type.' entry saved and admission balance recalculated.');
            redirect('admission-view.php?id=' . $id . '#payments');
        }
    } catch (Throwable $e) { error_log('[admin-admission-view] ' . $e->__toString()); flash('error', ($e instanceof RuntimeException && !($e instanceof PDOException)) ? $e->getMessage() : 'Admission update could not be completed.'); redirect('admission-view.php?id=' . $id); }
}
$photo = site_asset_url($row['student_photo'] ?? '');
$balance = max(0, (float)$row['total_fee'] - (float)$row['discount_amount'] - (float)$row['paid_amount']);
$payments = admission_payments($id);
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
  <section class="panel-card"><h2>Quick Update</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="quick_update"><div class="field"><label>Admission Status</label><select name="admission_status"><?php foreach($statuses as $st): ?><option <?= $row['admission_status']===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select></div><div class="field"><label>Next Follow-up</label><input type="date" name="next_follow_up" value="<?= e($row['next_follow_up'] ?? '') ?>"></div><div class="field full"><div class="alert alert-info">Payment status and paid amount are calculated automatically from the payment ledger below.</div></div><div class="field full"><label>Admin Note</label><textarea name="admin_note" rows="4"><?= e($row['admin_note'] ?? '') ?></textarea></div><div class="field full"><button class="btn btn-primary">Save Quick Update</button></div></form></section>
  <section class="panel-card"><h2>Admission Note</h2><p class="muted"><?= nl2br(e($row['admin_note'] ?: 'No internal note saved.')) ?></p><br><div class="admission-card-actions"><?php $phoneDigits=preg_replace('/\D+/', '', (string)$row['phone']); if($phoneDigits): ?><a class="btn btn-green" href="https://wa.me/<?= e($phoneDigits) ?>" target="_blank">WhatsApp</a><?php endif; ?><a class="btn btn-soft" href="tel:<?= e($row['phone']) ?>">Call</a><a class="btn btn-primary" href="admissions.php?edit=<?= e((string)$id) ?>">Edit</a></div></section>
</div>

<section class="panel-card" id="payments" style="margin-top:18px"><div class="toolbar"><div><h2 style="margin:0">Payment & Receipt Ledger</h2><p class="muted">Payments are append-only. Use Refund/Adjustment entries instead of overwriting historical amounts.</p></div><span class="badge <?= e(admv_badge($row['payment_status'])) ?>"><?= e($row['payment_status']) ?> · <?= e(admv_money($row['paid_amount'])) ?> paid</span></div>
<form method="post" class="form-grid" style="margin-top:16px"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="add_payment"><div class="field"><label>Entry Type</label><select name="entry_type"><option>Payment</option><option>Refund</option><option>Adjustment</option></select></div><div class="field"><label>Amount</label><input type="number" min="0.01" step="0.01" name="amount" required></div><div class="field"><label>Mode</label><select name="payment_mode"><option value="Cash">Cash</option><option value="UPI">UPI</option><option value="Bank">Bank</option><option value="Other">Other</option></select></div><div class="field"><label>Reference / UPI ID</label><input name="reference_no"></div><div class="field"><label>Receipt No.</label><input name="receipt_no" placeholder="Auto generated if blank"></div><div class="field"><label>Entry Date & Time</label><input type="datetime-local" name="entry_date" value="<?=e(date('Y-m-d\TH:i'))?>"></div><div class="field full"><label>Note</label><input name="note" placeholder="Installment / refund reason / adjustment note"></div><div class="field full"><button class="btn btn-primary">Add Ledger Entry</button></div></form>
<div class="table-wrap" style="margin-top:18px"><table><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Receipt</th><th>By</th><th>Note</th></tr></thead><tbody><?php foreach($payments as $p):?><tr><td data-label="Date"><?=e($p['entry_date'])?></td><td data-label="Type"><span class="badge <?= $p['entry_type']==='Refund'?'badge-no':'badge-yes'?>"><?=e($p['entry_type'])?></span></td><td data-label="Amount"><?=e(admv_money($p['amount']))?></td><td data-label="Mode"><?=e($p['payment_mode']??'-')?></td><td data-label="Reference"><?=e($p['reference_no']??'-')?></td><td data-label="Receipt"><?=e($p['receipt_no']??'-')?></td><td data-label="By"><?=e($p['admin_name']??'Migration/System')?></td><td data-label="Note"><?=e($p['note']??'')?></td></tr><?php endforeach;?><?php if(!$payments):?><tr><td colspan="8" class="empty-state">No payment entries yet.</td></tr><?php endif;?></tbody></table></div></section>

<?php require_once __DIR__ . '/_footer.php'; ?>
