<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$blade = app('view')->getEngineResolver()->resolve('blade')->getCompiler();

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
$files = new RegexIterator($files, '/\.blade\.php$/');

$hasError = false;
foreach ($files as $file) {
    try {
        $compiled = $blade->compileString(file_get_contents($file->getPathname()));
        $path = "test_".md5($file->getPathname()).".php";
        file_put_contents($path, $compiled);
        exec('php -l ' . $path, $out, $ret);
        if($ret !== 0) {
            echo "Error in " . $file->getPathname() . ":\\n";
            echo implode("\\n", $out) . "\\n";
            $hasError = true;
        }
        unlink($path);
    } catch (\Exception $e) {
        echo "Exception in " . $file->getPathname() . ": " . $e->getMessage() . "\\n";
    }
}

if (!$hasError) {
    echo "All Blade files compiled without syntax errors!\\n";
}
