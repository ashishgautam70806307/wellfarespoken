<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 415,
  'title' => 'Unsupported file or content type',
  'message' => 'The server cannot process the submitted file or content format.',
  'hint' => 'Use one of the supported formats and try again.',
);
require __DIR__ . '/template.php';
