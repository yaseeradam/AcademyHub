<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\BiometricSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncZkTecoAttendance extends Command
{
    /**
     * The name and signature of the console command.
     * Usage: php artisan zkteco:sync
     *        php artisan zkteco:sync 192.168.1.201 4370
     *        php artisan zkteco:sync --days=7
     */
    protected $signature = 'zkteco:sync {ip?} {port?} {--days=0 : Number of past days to include beyond today (0 = today only)} {--tenant= : Specific tenant ID}';

    protected $description = 'Sync biometric attendance records from ZKTeco K40 hardware devices over LAN';

    public function handle()
    {
        $argIp = $this->argument('ip');
        $argPort = $this->argument('port') ? (int) $this->argument('port') : 4370;
        $days = max(0, (int) $this->option('days'));
        $targetTenantId = $this->option('tenant') ? (int) $this->option('tenant') : null;

        // If a specific IP was provided via CLI, sync only that IP
        if ($argIp) {
            $this->info("Connecting to ZKTeco terminal at {$argIp}:{$argPort}...");
            $result = BiometricSyncService::syncFromTerminal($argIp, $argPort, $days, $targetTenantId);
            if ($result['success']) {
                $this->info($result['message']);
                return 0;
            } else {
                $this->error($result['message']);
                return 1;
            }
        }

        // Otherwise (e.g. automated scheduled run), discover all configured terminals across active tenants
        $tenants = $targetTenantId
            ? Tenant::where('id', $targetTenantId)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            // Standalone or default tenant 1
            $tenants = collect([(object) ['id' => 1, 'name' => 'Default School']]);
        }

        $totalSynced = 0;

        foreach ($tenants as $tenant) {
            $terminals = Cache::get("tenant_{$tenant->id}_k40_terminals", [
                [
                    'name' => 'K40 Terminal (Main Gate)',
                    'ip'   => '192.168.0.201',
                    'port' => 4370,
                ],
            ]);

            foreach ($terminals as $term) {
                $ip = $term['ip'] ?? '';
                $port = (int) ($term['port'] ?? 4370);
                if (empty($ip)) {
                    continue;
                }

                $this->line("Checking {$term['name']} ({$ip}:{$port}) for Tenant #{$tenant->id}...");
                $res = BiometricSyncService::syncFromTerminal($ip, $port, $days, $tenant->id);

                if ($res['success']) {
                    $this->info("  [SUCCESS] " . $res['message']);
                    $totalSynced += (int) ($res['count'] ?? 0);
                } else {
                    $this->warn("  [SKIPPED] " . $res['message']);
                }
            }
        }

        $this->info("Biometric sync finished. Total records synced: {$totalSynced}");
        return 0;
    }
}
