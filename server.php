<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Emulate Apache "mod_rewrite" for the PHP built-in server.
// Important: only return false for real files (not directories), otherwise
// public folders like `/certificates` can shadow Laravel routes.
if ($uri !== '/') {
    $path = $publicPath.$uri;

    if (is_file($path)) {
        return false;
    }
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$remoteAddress = ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0').':'.($_SERVER['REMOTE_PORT'] ?? '0');

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';

