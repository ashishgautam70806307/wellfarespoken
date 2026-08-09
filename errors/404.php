<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 404,
  'title' => 'We could not find that page',
  'message' => 'The page may have moved, the link may be outdated, or the address may have been typed incorrectly.',
  'hint' => 'Use the home page or main navigation to continue learning.',
);
require __DIR__ . '/template.php';
