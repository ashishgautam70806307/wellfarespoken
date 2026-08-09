<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 429,
  'title' => 'Too many attempts',
  'message' => 'Too many requests were made in a short period of time.',
  'hint' => 'Wait a little while before trying again. This protects student and institute accounts.',
);
require __DIR__ . '/template.php';
