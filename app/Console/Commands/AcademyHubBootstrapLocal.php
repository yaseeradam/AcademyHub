<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantDataAdopter;
use App\Support\TenantProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AcademyHubBootstrapLocal extends Command
{
    protected $signature = 'academyhub:bootstrap-local
                            {--main-host= : Main host you will use locally (e.g. academyhub.test)}
                            {--tenant-name=Demo School : Tenant/school name}
                            {--tenant-slug=demo : Tenant slug (used as subdomain)}
                            {--tenant-domain= : Optional custom tenant domain (exact host match)}
                            {--admin-name=School Admin : Tenant admin name}
                            {--admin-email=admin@school.local : Tenant admin email}
                            {--admin-password=password : Tenant admin password}
                            {--super-name=Super Admin : Superadmin name}
                            {--super-email=admin@academyhub.local : Superadmin email}
                            {--super-password=password : Superadmin password}
                            {--adopt-null-data : Assign NULL tenant_id rows to the created tenant}
                            {--force : Allow running outside local env}';

    protected $description = 'Bootstrap local multi-school setup (superadmin + demo tenant + tenant admin + baseline data).';

    public function handle(TenantProvisioner $provisioner, TenantDataAdopter $adopter): int
    {
        if (! app()->environment('local') && ! $this->option('force')) {
            $this->error('This command is intended for local environment only. Re-run with --force if you really mean it.');
            return 1;
        }

        $mainHost = trim((string) $this->option('main-host'));
        if ($mainHost !== '') {
            $this->line('Local host hints (Windows hosts file):');
            $this->line("  127.0.0.1  {$mainHost}");
            $this->line("  127.0.0.1  ".$this->option('tenant-slug').'.'.$mainHost);
            $this->newLine();
        }

        $superEmail = (string) $this->option('super-email');
        $tenantSlug = Str::slug((string) $this->option('tenant-slug'));
        if ($tenantSlug === '') {
            $tenantSlug = 'demo';
        }

        DB::transaction(function () use ($provisioner, $adopter, $superEmail, $tenantSlug) {
            $super = User::query()->firstOrCreate(
                ['email' => $superEmail],
                [
                    'name' => (string) $this->option('super-name'),
                    'password' => Hash::make((string) $this->option('super-password')),
                    'role' => 'admin',
                    'is_active' => true,
                    'is_super_admin' => true,
                ]
            );

            if (! $super->is_super_admin) {
                $super->forceFill(['is_super_admin' => true])->save();
            }

            $tenant = Tenant::query()->where('slug', $tenantSlug)->first();
            if (! $tenant) {
                $tenant = Tenant::query()->create([
                    'name' => (string) $this->option('tenant-name'),
                    'slug' => $tenantSlug,
                    'domain' => $this->option('tenant-domain') ?: null,
                    'plan' => 'pro',
                    'status' => 'active',
                    'max_students' => 500,
                    'max_teachers' => 50,
                    'contact_email' => null,
                    'contact_phone' => null,
                ]);
            }

            if ($this->option('adopt-null-data')) {
                $adopter->adoptNullTenantData($tenant->id);
            }

            $adminEmail = (string) $this->option('admin-email');
            User::query()->updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => (string) $this->option('admin-name'),
                    'password' => Hash::make((string) $this->option('admin-password')),
                    'role' => 'admin',
                    'is_active' => true,
                    'tenant_id' => $tenant->id,
                    'is_super_admin' => false,
                ]
            );

            $provisioner->provision($tenant);
        });

        $this->info('Bootstrap complete.');
        $this->line('Login flows:');
        $this->line('- Superadmin logs in on the main host and uses `/superadmin`.');
        $this->line('- School admin logs in on the tenant host/subdomain.');

        return 0;
    }
}
