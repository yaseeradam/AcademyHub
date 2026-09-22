<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sanctum:prune-expired --hours=168')->daily();

        // ── Automated Backups ─────────────────────────────────────────────
        // Full DB + files backup every night at 2 AM
        $schedule->command('backup:run')->daily()->at('02:00');
        // Clean up old backups per retention policy at 3 AM
        $schedule->command('backup:clean')->daily()->at('03:00');
        // Health check: verify latest backup exists and isn't too old
        $schedule->command('backup:monitor')->daily()->at('03:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
