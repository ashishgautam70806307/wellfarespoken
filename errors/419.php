<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 419,
  'title' => 'Your session has expired',
  'message' => 'For your security, this action can no longer use the old session or form token.',
  'hint' => 'Reload the page, sign in again if needed, and submit the form once more.',
);
require __DIR__ . '/template.php';
