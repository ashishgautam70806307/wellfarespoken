<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 401,
  'title' => 'Sign in required',
  'message' => 'You need to sign in before accessing this area.',
  'hint' => 'Use the appropriate Student or Institute login, then return to the page.',
);
require __DIR__ . '/template.php';
