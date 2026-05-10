<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\TenantProvisioner;
use Illuminate\Console\Command;

class TenantProvision extends Command
{
    protected $signature = 'tenant:provision {tenantId : Tenant ID to provision}';

    protected $description = 'Provision baseline data for a tenant (settings, session/term, classes, sections, subjects).';

    public function handle(TenantProvisioner $provisioner): int
    {
        $tenantId = (int) $this->argument('tenantId');
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");
            return 1;
        }

        $provisioner->provision($tenant);

        $this->info("Provisioned tenant #{$tenant->id} ({$tenant->name}).");
        $this->line('Settings file: '.storage_path('app/myacademy/tenants/'.$tenant->id.'/settings.json'));

        return 0;
    }
}

