<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'status',
        'contact_email',
        'contact_phone',
        'settings',
        'feature_flags',
        'max_disk_usage_mb',
        'active_broadcast_banner',
        'logo',
        'primary_color',
        'max_students',
        'max_teachers',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'settings'      => 'array',
        'feature_flags' => 'array',
        'activated_at'  => 'datetime',
        'expires_at'    => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function marketplaceComponents()
    {
        return $this->belongsToMany(MarketplaceComponent::class, 'tenant_marketplace_components')
            ->using(TenantMarketplaceComponent::class)
            ->withPivot('installed_at', 'price_paid', 'student_count_at_install', 'uninstalled_at', 'setup_fee', 'usage_fee_per_student', 'allowed_class_ids', 'status')
            ->withTimestamps();
    }

    public function activeMarketplaceComponents()
    {
        return $this->marketplaceComponents()
            ->wherePivotNotNull('installed_at')
            ->wherePivotNull('uninstalled_at')
            ->wherePivot('status', 'active');
    }

    public function bills()
    {
        return $this->hasMany(TenantPluginBill::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
