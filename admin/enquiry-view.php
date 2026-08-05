<?php require_once __DIR__ . '/_header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM enquiries WHERE id=?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { flash('error', 'Enquiry not found.'); redirect('enquiries.php'); }
$phoneDigits = clean_phone_digits($row['phone']);
?>
<div class="admin-top"><div><h1>Enquiry Detail</h1><p>View student details clearly before calling, WhatsApp messaging or updating follow-up.</p></div><div class="admin-actions"><a class="btn btn-soft" href="enquiries.php?edit=<?= e((string)$row['id']) ?>">Update Status</a><a class="btn btn-primary" target="_blank" href="https://wa.me/<?= e($phoneDigits) ?>?text=Hello%20<?= e(rawurlencode($row['name'])) ?>,%20this%20is%20Well%20Fare%20English%20Spoken.">WhatsApp</a></div></div>
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
