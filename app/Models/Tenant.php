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

    protected static function booted()
    {
        static::saved(function (Tenant $tenant) {
            $path = storage_path('app/academyhub/tenants/' . $tenant->id . '/settings.json');
            
            if (\Illuminate\Support\Facades\File::exists($path)) {
                $existing = json_decode(\Illuminate\Support\Facades\File::get($path), true);
                if (!is_array($existing)) {
                    $existing = [];
                }
                
                $existing['school_name'] = $tenant->name;
                $existing['school_email'] = $tenant->contact_email;
                $existing['school_phone'] = $tenant->contact_phone;
                
                if ($tenant->logo !== null) {
                    $existing['school_logo'] = $tenant->logo;
                }
                
                $existing['subscription_due_date'] = $tenant->expires_at
                    ? $tenant->expires_at->toDateString()
                    : null;
                
                \Illuminate\Support\Facades\File::put($path, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            
            // Flush the settings cache for this specific tenant
            \Illuminate\Support\Facades\Cache::forget(\App\Support\TenantSettings::settingsCacheKey($tenant));
        });
    }

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
