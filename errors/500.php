<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 500,
  'title' => 'Something went wrong on our side',
  'message' => 'The server encountered an unexpected problem while processing this page.',
  'hint' => 'Your data may still be safe. Please try again shortly; if the problem continues, contact the institute.',
);
require __DIR__ . '/template.php';
