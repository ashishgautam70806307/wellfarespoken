<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 501,
  'title' => 'This action is not available',
  'message' => 'The server does not currently support the requested operation.',
  'hint' => 'Use the available site features or contact the institute if you need help.',
);
require __DIR__ . '/template.php';
