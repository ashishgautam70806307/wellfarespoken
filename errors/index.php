<?php
http_response_code(404);
define('WF_ERROR_PAGE', true);
$wfError = [
    'code' => 404,
    'title' => 'We could not find that page',
    'message' => 'The error-page directory is not a public destination.',
    'hint' => 'Return to the home page to continue.',
];
require __DIR__ . '/template.php';
