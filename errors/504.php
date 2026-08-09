<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 504,
  'title' => 'The server response timed out',
  'message' => 'A service needed by this page did not respond in time.',
  'hint' => 'Please try again after a short wait.',
);
require __DIR__ . '/template.php';
