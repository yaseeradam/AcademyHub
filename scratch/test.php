<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$teachers = User::withoutGlobalScopes()->where('role', 'teacher')->get();
echo "Total Teachers: " . $teachers->count() . "\n";
foreach ($teachers as $teacher) {
    echo "ID: {$teacher->id} | Name: {$teacher->name} | Email: {$teacher->email} | Photo: '{$teacher->profile_photo}' | Photo URL: '{$teacher->profile_photo_url}'\n";
}
