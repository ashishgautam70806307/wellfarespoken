<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 410,
  'title' => 'This page is no longer available',
  'message' => 'The requested page or resource has been permanently removed.',
  'hint' => 'Please use the current navigation to find the latest page.',
);
require __DIR__ . '/template.php';
