<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 422,
  'title' => 'Please check the submitted details',
  'message' => 'The request was received, but one or more values could not be accepted.',
  'hint' => 'Review the form fields and correct the highlighted information.',
);
require __DIR__ . '/template.php';
