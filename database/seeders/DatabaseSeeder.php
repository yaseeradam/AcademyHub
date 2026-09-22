<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder is safe to run in production.
     * It only creates/updates the platform superadmin account and
     * seeds the marketplace component catalogue (plugin definitions).
     *
     * All tenant-specific data (classes, sections, subjects, students,
     * fee structures, etc.) is managed through the admin UI — never seeded.
     */
    public function run(): void
    {
        $adminEmail = app()->environment('testing')
            ? 'admin@academyhub.local'
            : env('ACADEMYHUB_ADMIN_EMAIL', 'admin@academyhub.local');

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'           => 'Super Admin',
                'password'       => Hash::make(env('ACADEMYHUB_ADMIN_PASSWORD', 'password')),
                'role'           => 'admin',
                'is_active'      => true,
                'is_super_admin' => true,
                'tenant_id'      => null,
            ]
        );

        // Marketplace component catalogue (plugin definitions only — no tenant data).
        $this->call(MarketplaceComponentSeeder::class);

        // Seed demo tenant for testing or when explicitly enabled
        if (env('ACADEMYHUB_SEED_DEMO_TENANT', false) || env('MYACADEMY_SEED_DEMO_TENANT', false) || app()->environment('testing')) {
            $this->call(DemoTenantSeeder::class);
        }
    }
}
