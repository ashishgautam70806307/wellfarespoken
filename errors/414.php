<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 414,
  'title' => 'The address is too long',
  'message' => 'The requested URL is longer than the server accepts.',
  'hint' => 'Return to the previous page and use the normal navigation or form controls.',
);
require __DIR__ . '/template.php';
