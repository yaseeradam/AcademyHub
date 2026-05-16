<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplaceComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'route_name',
        'price',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_marketplace_components')
            ->withPivot('installed_at')
            ->withTimestamps();
    }
}
