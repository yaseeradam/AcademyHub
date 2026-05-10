<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\TenantDataAdopter;
use Illuminate\Console\Command;

class TenantAdoptNullData extends Command
{
    protected $signature = 'tenant:adopt-null-data
                            {tenantId : Tenant ID to assign}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Assign tenant_id to existing rows where tenant_id IS NULL (for migrating from single-school installs).';

    public function handle(TenantDataAdopter $adopter): int
    {
        $tenantId = (int) $this->argument('tenantId');
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return 1;
        }

        if (! $this->option('force')) {
            $this->warn('This will update many tables by setting tenant_id where it is currently NULL.');
            if (! $this->confirm("Proceed to adopt NULL tenant_id rows into tenant #{$tenant->id} ({$tenant->name})?")) {
                $this->info('Aborted.');
                return 0;
            }
        }

        $totalUpdated = $adopter->adoptNullTenantData($tenantId);
        $this->info("Done. Total rows updated: {$totalUpdated}");

        return 0;
    }
}
