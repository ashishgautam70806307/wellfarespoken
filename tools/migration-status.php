<?php
require_once __DIR__ . '/../includes/functions.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only.\n"); }
if (!table_exists('schema_migrations')) { fwrite(STDERR,"schema_migrations is missing. Import the latest migration first.\n"); exit(1); }
$rows = db()->query('SELECT version,description,applied_at FROM schema_migrations ORDER BY applied_at,version')->fetchAll();
if (!$rows) { echo "No registered migrations.\n"; exit(0); }
foreach ($rows as $row) echo $row['version'] . " | " . $row['applied_at'] . " | " . $row['description'] . "\n";
