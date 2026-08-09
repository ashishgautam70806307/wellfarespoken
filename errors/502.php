<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 502,
  'title' => 'The server received a bad response',
  'message' => 'A temporary upstream service problem prevented this page from loading.',
  'hint' => 'Please wait a moment and try again.',
);
require __DIR__ . '/template.php';
