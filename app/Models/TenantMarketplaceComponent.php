<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TenantMarketplaceComponent extends Pivot
{
    protected $table = 'tenant_marketplace_components';

    protected $fillable = [
        'tenant_id',
        'marketplace_component_id',
        'installed_at',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
    ];
}
