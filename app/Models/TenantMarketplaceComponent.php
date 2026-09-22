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
        'price_paid',
        'student_count_at_install',
        'uninstalled_at',
        'setup_fee',
        'usage_fee_per_student',
        'allowed_class_ids',
        'status',
    ];

    protected $casts = [
        'installed_at'             => 'datetime',
        'uninstalled_at'           => 'datetime',
        'price_paid'               => 'decimal:2',
        'setup_fee'                => 'decimal:2',
        'usage_fee_per_student'    => 'decimal:2',
        'student_count_at_install' => 'integer',
        'allowed_class_ids'        => 'array',
    ];
}
