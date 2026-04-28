<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = self::currentTenantId();
            if (! $tenantId) {
                return;
            }

            $model = $builder->getModel();
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        });

        static::creating(function (Model $model) {
            if (! empty($model->tenant_id)) {
                return;
            }

            $tenantId = self::currentTenantId();
            if ($tenantId) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    private static function currentTenantId(): ?int
    {
        if (! app()->bound('currentTenant')) {
            return null;
        }

        $tenant = app('currentTenant');
        if (! $tenant || ! isset($tenant->id)) {
            return null;
        }

        return (int) $tenant->id ?: null;
    }
}

