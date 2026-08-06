<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
ensure_schema_updates();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf_token'] ?? '')) {
    flash('error', 'Invalid request.');
    redirect('dashboard.php');
}
$table = safe_admin_table($_POST['table'] ?? '');
$id = (int)($_POST['id'] ?? 0);
$return = safe_local_redirect((string)($_POST['return'] ?? 'dashboard.php'), 'dashboard.php');
if (!$table || $id <= 0) {
    flash('error', 'Invalid publish toggle request.');
    redirect($return);
}
$stmt = db()->prepare("SELECT published FROM `$table` WHERE id=?");
$stmt->execute([$id]);
$current = $stmt->fetchColumn();
if ($current !== false) {
    $next = $current === 'Yes' ? 'No' : 'Yes';
    $up = db()->prepare("UPDATE `$table` SET published=? WHERE id=?");
    $up->execute([$next, $id]);
    flash('success', 'Published status updated.');
}
redirect($return);
