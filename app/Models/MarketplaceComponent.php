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
        'pricing_model',
        'setup_fee',
        'usage_fee_per_student',
        'short_description',
        'description',
        'category',
        'icon',
        'is_active',
        'rating_avg',
        'rating_count',
        'installs',
    ];

    protected $casts = [
        'price'                 => 'decimal:2',
        'setup_fee'             => 'decimal:2',
        'usage_fee_per_student' => 'decimal:2',
        'rating_avg'            => 'decimal:2',
        'is_active'             => 'boolean',
        'rating_count'          => 'integer',
        'installs'              => 'integer',
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_marketplace_components')
            ->using(TenantMarketplaceComponent::class)
            ->withPivot('installed_at', 'price_paid', 'student_count_at_install', 'uninstalled_at', 'setup_fee', 'usage_fee_per_student', 'allowed_class_ids', 'status')
            ->withTimestamps();
    }

    public function bills()
    {
        return $this->hasMany(TenantPluginBill::class);
    }

    public function reviews()
    {
        return $this->hasMany(PluginReview::class, 'component_slug', 'slug');
    }

    /**
     * Calculate total price for a given student count.
     */
    public function calculatePrice(int $studentCount): float
    {
        if ((float) $this->price <= 0) return 0.0;
        if ($this->pricing_model === 'per_student') {
            return (float) $this->price * $studentCount;
        }
        return (float) $this->price;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ((float) $this->price <= 0) return 'FREE';
        return config('myacademy.currency_symbol', '₦') . number_format($this->price, 2);
    }
}
