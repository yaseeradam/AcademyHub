<?php

namespace App\Support;

class TenantSettings
{
    public static function tenantId(): ?int
    {
        if (! app()->bound('currentTenant')) {
            return null;
        }

        $tenant = app('currentTenant');
        if (! $tenant || ! isset($tenant->id)) {
            return null;
        }

        $id = (int) $tenant->id;

        return $id > 0 ? $id : null;
    }

    public static function settingsPath(): string
    {
        $tenantId = self::tenantId();

        return $tenantId
            ? storage_path('app/myacademy/tenants/'.$tenantId.'/settings.json')
            : storage_path('app/myacademy/settings.json');
    }

    public static function settingsCacheKey(?\App\Models\Tenant $tenant = null): string
    {
        $tenantId = $tenant?->id ?? self::tenantId();

        return $tenantId
            ? 'myacademy_settings_cache_tenant_'.$tenantId
            : 'myacademy_settings_cache_global';
    }

    public static function uploadsSubdir(string $baseDir): string
    {
        $tenantId = self::tenantId();
        if (! $tenantId) {
            return $baseDir;
        }

        return rtrim($baseDir, '/').'/tenant_'.$tenantId;
    }
}

