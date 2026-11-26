<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$blocked = ['/db-config.php', '/schema.sql', '/README.md', '/.htaccess', '/router.php'];
if (in_array($path, $blocked, true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Forbidden';
    exit;
}

if ($path === '/') {
    $path = '/index.html';
}

$file = __DIR__ . $path;
if (is_file($file)) {
    return false;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__ . '/404.html');
