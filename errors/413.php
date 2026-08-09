<?php
define('WF_ERROR_PAGE', true);
$wfError = array (
  'code' => 413,
  'title' => 'The upload is too large',
  'message' => 'The submitted file or request is larger than the server allows.',
  'hint' => 'Choose a smaller supported file and try again.',
);
require __DIR__ . '/template.php';
