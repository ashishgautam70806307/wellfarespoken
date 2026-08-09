<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 408,
  'title' => 'The request took too long',
  'message' => 'The server stopped waiting before the request could finish.',
  'hint' => 'Check your connection and try the action again.',
);
require __DIR__ . '/template.php';
