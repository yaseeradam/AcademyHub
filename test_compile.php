<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$blade = app('view')->getEngineResolver()->resolve('blade')->getCompiler();

foreach (glob('resources/views/livewire/imports/*.blade.php') as $file) {
    $compiled = $blade->compileString(file_get_contents($file));
    $path = "test_".basename($file, '.blade.php').".php";
    file_put_contents($path, $compiled);
    exec('php -l ' . $path, $out, $ret);
    if($ret !== 0) {
        echo "Error in $file:\\n";
        echo implode("\\n", $out);
    }
}
echo "Done.";
