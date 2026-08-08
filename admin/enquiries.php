<?php require_once __DIR__ . '/../includes/functions.php'; require_admin(); ensure_schema_updates();
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $q = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $where=['status_deleted=0'];$params=[];
    if ($q !== '') { $where[]='(name LIKE ? OR phone LIKE ? OR course_interest LIKE ? OR message LIKE ?)'; $params[]="%$q%";$params[]="%$q%";$params[]="%$q%";$params[]="%$q%"; }
    if ($status !== '') { $where[]='enquiry_status=?'; $params[]=$status; }
    $sql='SELECT * FROM enquiries'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY id DESC';
    $stmt=db()->prepare($sql);$stmt->execute($params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=enquiries-' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Date','Name','Phone','Course Interest','Current Level','Preferred Batch','Lead Source','Status','Priority','Follow-up Date','Last Contacted','Message','Admin Note']);
    foreach ($stmt->fetchAll() as $row) {
        fputcsv($out, array_map('csv_safe_cell', [$row['id'],$row['created_at'],$row['name'],$row['phone'],$row['course_interest'] ?? '',$row['current_level'] ?? '',$row['preferred_batch'] ?? '',$row['lead_source'] ?? '',$row['enquiry_status'] ?? '',$row['lead_priority'] ?? 'Normal',$row['follow_up_date'] ?? '',$row['last_contacted_at'] ?? '',$row['message'] ?? '',$row['admin_note'] ?? '']));
    }
    fclose($out);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $stmt = db()->prepare('UPDATE enquiries SET enquiry_status=?, lead_priority=?, follow_up_date=?, last_contacted_at=?, admin_note=? WHERE id=? AND status_deleted=0');
        $stmt->execute([$_POST['enquiry_status'] ?? 'New', $_POST['lead_priority'] ?? 'Normal', $_POST['follow_up_date'] ?: null, $_POST['last_contacted_at'] ?: null, trim($_POST['admin_note'] ?? ''), (int)($_POST['id'] ?? 0)]);
        flash('success', 'Enquiry updated.'); redirect('enquiries.php');
    }
    if ($action === 'delete') {
        $stmt = db()->prepare("UPDATE enquiries SET status_deleted=1, deleted_at=NOW() WHERE id=? AND status_deleted=0");
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        admin_audit_log('enquiry.hidden','enquiry',(int)($_POST['id'] ?? 0),'Enquiry soft deleted; lifecycle history preserved.');
        flash('success', 'Enquiry hidden safely.'); redirect('enquiries.php');
    }
}
$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$where=['status_deleted=0'];$params=[];
if ($q !== '') { $where[]='(name LIKE ? OR phone LIKE ? OR course_interest LIKE ? OR message LIKE ?)'; $params[]="%$q%";$params[]="%$q%";$params[]="%$q%";$params[]="%$q%"; }
if ($status !== '') { $where[]='enquiry_status=?'; $params[]=$status; }
$whereSql=$where?' WHERE '.implode(' AND ',$where):'';
$countStmt=db()->prepare('SELECT COUNT(*) FROM enquiries'.$whereSql);$countStmt->execute($params);$enquiryPager=admin_pagination_state((int)$countStmt->fetchColumn(),30);
$sql='SELECT * FROM enquiries'.$whereSql.' ORDER BY id DESC LIMIT '.$enquiryPager['per_page'].' OFFSET '.$enquiryPager['offset'];
$stmt=db()->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
$edit=null;
$statusOptions = fetch_form_options('enquiry_status');
if (!$statusOptions) { $statusOptions = [['option_label'=>'New','option_value'=>'New'],['option_label'=>'Contacted','option_value'=>'Contacted'],['option_label'=>'Converted','option_value'=>'Converted'],['option_label'=>'Not Interested','option_value'=>'Not Interested']]; }
if (isset($_GET['edit'])) { $s=db()->prepare('SELECT * FROM enquiries WHERE id=? AND status_deleted=0');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch(); }
require_once __DIR__ . '/_header.php';
?>
<div class="admin-top"><div><h1>Admission Enquiries</h1><p>Track every lead with call, WhatsApp, follow-up date, status and admin note.</p></div><div class="admin-actions"><a class="btn btn-soft" href="enquiries.php?export=csv&q=<?= e(rawurlencode($q ?? '')) ?>&status=<?= e(rawurlencode($status ?? '')) ?>">Export CSV</a><a class="btn btn-primary" href="../admission.php" target="_blank">Open Public Form</a></div></div>
<?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($edit): ?>
<form class="form-box" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>">
    <div class="form-grid"><div class="form-section-title"><span>✎</span>Update Enquiry: <?= e($edit['name']) ?></div>
        <div class="field"><label>Status</label><select name="enquiry_status"><?php foreach ($statusOptions as $option): $value = $option['option_value'] ?: $option['option_label']; ?><option value="<?= e($value) ?>" <?= ($edit['enquiry_status'] ?? 'New') === $value ? 'selected' : '' ?>><?= e($option['option_label']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Priority</label><select name="lead_priority"><option value="Normal" <?= ($edit['lead_priority'] ?? 'Normal') === 'Normal' ? 'selected' : '' ?>>Normal</option><option value="High" <?= ($edit['lead_priority'] ?? '') === 'High' ? 'selected' : '' ?>>High</option><option value="Low" <?= ($edit['lead_priority'] ?? '') === 'Low' ? 'selected' : '' ?>>Low</option></select></div>
        <div class="field"><label>Last Contacted</label><input type="datetime-local" name="last_contacted_at" value="<?= e(!empty($edit['last_contacted_at']) ? date('Y-m-d\TH:i', strtotime($edit['last_contacted_at'])) : '') ?>"></div>
        <div class="field"><label>Follow-up Date</label><input type="date" name="follow_up_date" value="<?= e($edit['follow_up_date'] ?? '') ?>"></div>
        <div class="field full"><label>Admin Note</label><textarea name="admin_note" placeholder="Example: Asked for evening batch fee details."><?= e($edit['admin_note'] ?? '') ?></textarea></div>
        <div class="field full"><button class="btn btn-primary">Update Enquiry</button><a class="btn btn-soft" href="enquiries.php">Cancel</a></div>
    </div>
</form><br>
<?php endif; ?>
<div class="panel-card">
    <div class="toolbar"><div><h2 style="margin:0;color:var(--navy)">Lead List</h2><p style="margin:4px 0 0;color:var(--muted)">Use filters to find new, contacted or converted students.</p></div><form method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search name, phone, course"><select name="status"><option value="">All Status</option><?php foreach ($statusOptions as $option): $st = $option['option_value'] ?: $option['option_label']; ?><option value="<?= e($st) ?>" <?= $status===$st?'selected':'' ?>><?= e($option['option_label']) ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-dark">Filter</button></form></div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Student</th><th>Contact</th><th>Interest</th><th>Status</th><th>Follow-up</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $row): $phoneDigits=preg_replace('/\D+/', '', $row['phone']); ?><tr><td data-label="Date"><?= e($row['created_at']) ?></td><td data-label="Student"><strong><?= e($row['name']) ?></strong><br><span class="help"><?= e($row['current_level'] ?? '') ?></span></td><td data-label="Contact"><a href="tel:<?= e($row['phone']) ?>"><?= e($row['phone']) ?></a></td><td data-label="Interest"><strong><?= e($row['course_interest'] ?? '-') ?></strong><br><span class="help"><?= e($row['preferred_batch'] ?? '') ?></span><br><?= e($row['message'] ?? '') ?></td><td data-label="Status"><span class="badge <?= e(badge_class_for_status($row['enquiry_status'] ?? 'New')) ?>"><?= e($row['enquiry_status'] ?? 'New') ?></span></td><td data-label="Follow-up"><?= e($row['follow_up_date'] ?? '-') ?><br><span class="badge badge-gray"><?= e($row['lead_priority'] ?? 'Normal') ?></span><br><span class="help"><?= e($row['admin_note'] ?? '') ?></span></td><td data-label="Actions"><div class="table-actions"><a class="btn btn-sm btn-green" target="_blank" href="https://wa.me/<?= e($phoneDigits) ?>?text=Hello%20<?= e(rawurlencode($row['name'])) ?>,%20this%20is%20Well%20Fare%20English%20Spoken.">WhatsApp</a><a class="btn btn-sm btn-soft" href="enquiry-view.php?id=<?= e((string)$row['id']) ?>">View</a><a class="btn btn-sm btn-soft" href="enquiries.php?edit=<?= e((string)$row['id']) ?>">Update</a><form method="post" onsubmit="return confirm('Delete this enquiry?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="btn btn-sm btn-danger">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="7" class="empty-state">No enquiries found.</td></tr><?php endif; ?></tbody></table></div>
    <?= admin_pagination_html($enquiryPager) ?>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
