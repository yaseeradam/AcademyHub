<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class TenantDataAdopter
{
    /**
     * Assign tenant_id to existing rows where tenant_id IS NULL.
     *
     * Intended for migrating a single-school database into the first tenant.
     * Run this BEFORE provisioning default tenant data to preserve IDs/relations.
     */
    public function adoptNullTenantData(int $tenantId): int
    {
        $tables = collect(DB::select(
            "SELECT DISTINCT TABLE_NAME AS name
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'tenant_id'"
        ))->pluck('name')->filter()->values();

        $totalUpdated = 0;

        foreach ($tables as $table) {
            $table = (string) $table;

            if ($table === 'tenants') {
                continue;
            }

            if ($table === 'users') {
                $updated = DB::table('users')
                    ->whereNull('tenant_id')
                    ->where(function ($q) {
                        $q->whereNull('is_super_admin')->orWhere('is_super_admin', false);
                    })
                    ->update(['tenant_id' => $tenantId]);
            } else {
                $updated = DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }

            $totalUpdated += (int) $updated;
        }

        return $totalUpdated;
    }
}

