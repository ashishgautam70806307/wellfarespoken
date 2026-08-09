<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 503,
  'title' => 'We are temporarily unavailable',
  'message' => 'The website is currently busy, under maintenance, or temporarily unable to serve this request.',
  'hint' => 'Please try again shortly.',
);
require __DIR__ . '/template.php';
