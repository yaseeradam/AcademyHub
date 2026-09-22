<?php

/**
 * AcademyHub Remote Deploy Helper
 * 
 * Clears all Laravel caches after a git pull.
 * Access via: https://academyhub.com.ng/deploy.php?key=YOUR_SECRET_KEY
 * 
 * WARNING: Delete this file or change the key after use in production.
 */

// ── Secret key (change this to something only you know) ──
$SECRET_KEY = 'AcHub2026DeployX9k';

// ── Validate ──
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// ── Find artisan ──
$basePath = dirname(__DIR__); // /var/www/myacademy
$artisan  = $basePath . '/artisan';

if (!file_exists($artisan)) {
    echo json_encode(['error' => 'artisan not found at ' . $artisan]);
    exit;
}

// ── Run cache-clearing commands ──
$commands = [
    'php ' . $artisan . ' view:clear',
    'php ' . $artisan . ' cache:clear',
    'php ' . $artisan . ' config:clear',
    'php ' . $artisan . ' route:clear',
    'php ' . $artisan . ' optimize:clear',
    'php ' . $artisan . ' optimize',
    'php ' . $artisan . ' view:cache',
];

header('Content-Type: application/json');

$results = [];
foreach ($commands as $cmd) {
    $output = [];
    $code   = 0;
    exec($cmd . ' 2>&1', $output, $code);
    $results[] = [
        'command' => $cmd,
        'output'  => implode("\n", $output),
        'exit'    => $code,
    ];
}

echo json_encode([
    'status'  => 'done',
    'results' => $results,
], JSON_PRETTY_PRINT);
