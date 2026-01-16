<?php
// Some hosting setups prioritize index.php over index.html.
// This keeps the site working even on PHP-first configurations.

$path = __DIR__ . DIRECTORY_SEPARATOR . 'index.html';
if (!is_file($path)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "index.html not found";
    exit;
}

header('Content-Type: text/html; charset=utf-8');
readfile($path);
