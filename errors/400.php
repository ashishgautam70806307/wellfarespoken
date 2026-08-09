<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 400,
  'title' => 'Bad Request',
  'message' => 'The request could not be understood or contained invalid information.',
  'hint' => 'Check the address or form data and try again.',
);
require __DIR__ . '/template.php';
