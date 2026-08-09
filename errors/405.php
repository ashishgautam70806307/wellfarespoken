<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 405,
  'title' => 'That action is not allowed',
  'message' => 'This page does not accept the request method that was used.',
  'hint' => 'Return to the previous page and use the available button or form action.',
);
require __DIR__ . '/template.php';
