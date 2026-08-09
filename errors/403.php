<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 403,
  'title' => 'This area is protected',
  'message' => 'You do not have permission to open this page or resource.',
  'hint' => 'If you believe you should have access, sign in with the correct account or contact the institute.',
);
require __DIR__ . '/template.php';
