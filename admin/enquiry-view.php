<?php require_once __DIR__ . '/_header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM enquiries WHERE id=? AND status_deleted=0');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { flash('error', 'Enquiry not found.'); redirect('enquiries.php'); }
$phoneDigits = clean_phone_digits($row['phone']);
if ($_SERVER['REQUEST_METHOD']==='POST' && csrf_validate($_POST['csrf_token']??'')) {
    try {
        if (($_POST['action']??'')==='convert_admission') {
            $admissionId=lifecycle_convert_enquiry($id);
            flash('success','Enquiry converted to an admission record.');
            redirect('admission-view.php?id='.$admissionId);
        }
    } catch(Throwable $e) {
        error_log('[enquiry-convert] '.$e->__toString());
        flash('error', ($e instanceof RuntimeException && !($e instanceof PDOException)) ? $e->getMessage() : 'Enquiry could not be converted right now. Please check System Check and try again.');
        redirect('enquiry-view.php?id='.$id);
    }
}
?>
<div class="admin-top"><div><h1>Enquiry Detail</h1><p>View student details clearly before calling, WhatsApp messaging or converting to admission.</p></div><div class="admin-actions"><?php if(empty($row['converted_admission_id'])):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="convert_admission"><button class="btn btn-primary" type="submit">Convert to Admission</button></form><?php else:?><a class="btn btn-primary" href="admission-view.php?id=<?=e((string)$row['converted_admission_id'])?>">Open Admission</a><?php endif;?><a class="btn btn-soft" href="enquiries.php?edit=<?= e((string)$row['id']) ?>">Update Status</a><a class="btn btn-green" target="_blank" href="https://wa.me/<?= e($phoneDigits) ?>?text=Hello%20<?= e(rawurlencode($row['name'])) ?>,%20this%20is%20Well%20Fare%20English%20Spoken.">WhatsApp</a></div></div><?php if($m=flash('success')):?><div class="alert alert-success"><?=e($m)?></div><?php endif;?><?php if($m=flash('error')):?><div class="alert alert-danger"><?=e($m)?></div><?php endif;?>
<div class="detail-layout">
    <section class="panel-card">
        <div class="student-profile"><div class="student-avatar"><?= e(strtoupper(substr($row['name'],0,1))) ?></div><div><h2><?= e($row['name']) ?></h2><p><?= e($row['course_interest'] ?: 'Course interest not selected') ?></p></div></div>
        <div class="detail-grid">
            <div><span>Phone</span><strong><a href="tel:<?= e($row['phone']) ?>"><?= e($row['phone']) ?></a></strong></div>
            <div><span>Status</span><strong><span class="badge <?= e(badge_class_for_status($row['enquiry_status'] ?? 'New')) ?>"><?= e($row['enquiry_status'] ?? 'New') ?></span></strong></div>
            <div><span>Priority</span><strong><?= e($row['lead_priority'] ?? 'Normal') ?></strong></div>
            <div><span>Current Level</span><strong><?= e($row['current_level'] ?? '-') ?></strong></div>
            <div><span>Preferred Batch</span><strong><?= e($row['preferred_batch'] ?? '-') ?></strong></div>
            <div><span>Lead Source</span><strong><?= e($row['lead_source'] ?? 'Website') ?></strong></div>
            <div><span>Follow-up Date</span><strong><?= e($row['follow_up_date'] ?? '-') ?></strong></div>
            <div><span>Last Contacted</span><strong><?= e($row['last_contacted_at'] ?? '-') ?></strong></div>
            <div><span>Submitted</span><strong><?= e($row['created_at']) ?></strong></div>
            <div><span>IP Address</span><strong><?= e($row['ip_address'] ?? '-') ?></strong></div>
        </div>
    </section>
    <aside class="panel-card">
        <h2 style="margin-top:0;color:var(--navy)">Message & Notes</h2>
        <div class="note-box"><strong>Student Message</strong><p><?= nl2br(e($row['message'] ?? 'No message added.')) ?></p></div>
        <div class="note-box"><strong>Admin Note</strong><p><?= nl2br(e($row['admin_note'] ?? 'No admin note yet.')) ?></p></div>
        <div class="stack-actions"><a class="btn btn-green" href="tel:<?= e($row['phone']) ?>">Call Student</a><a class="btn btn-soft" href="enquiries.php">Back to Enquiries</a></div>
    </aside>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
