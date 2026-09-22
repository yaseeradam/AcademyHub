<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function download(Request $request)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        // Check mysqldump availability
        $mysqldump = shell_exec('which mysqldump');
        if (empty(trim((string)$mysqldump))) {
            return back()->with('error', 'mysqldump is not available on this server. Please contact your hosting provider.');
        }

        $db       = config('database.connections.mysql.database');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename  = 'backup_' . $db . '_' . date('Y-m-d_His') . '.sql';
        $tmpPath   = storage_path('app/backups/' . $filename);

        // Create directory if needed
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg((string)$port),
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($db),
            escapeshellarg($tmpPath)
        );

        shell_exec($cmd);

        if (!file_exists($tmpPath) || filesize($tmpPath) === 0) {
            return back()->with('error', 'Backup failed. Check server permissions or DB credentials.');
        }

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/octet-stream',
        ])->deleteFileAfterSend(true);
    }
}
