<?php
require_once __DIR__ . '/../includes/functions.php';
private_no_store();
if (is_admin()) admin_audit_log('auth.logout', 'admin', (int)($_SESSION['admin_id'] ?? 0), 'Administrator signed out.');
admin_session_logout();
redirect('login.php');
