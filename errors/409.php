<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 409,
  'title' => 'We found a request conflict',
  'message' => 'The request conflicts with the current state of this record.',
  'hint' => 'Refresh the page before trying the action again so you are working with the latest data.',
);
require __DIR__ . '/template.php';
