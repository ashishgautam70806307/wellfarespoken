<?php
require_once __DIR__ . '/includes/functions.php';
private_no_store();
student_session_logout();
redirect('student-auth.php?logged_out=1');
