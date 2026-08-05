<?php
require_once __DIR__ . '/../includes/functions.php';
private_no_store();
admin_session_logout();
redirect('login.php');
