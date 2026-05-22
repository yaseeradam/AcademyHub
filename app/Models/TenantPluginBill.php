<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPluginBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'marketplace_component_id',
        'bill_type',
        'term_name',
        'session_name',
        'student_count',
        'setup_fee',
        'usage_fee_per_student',
        'total_due',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'student_count'         => 'integer',
        'setup_fee'             => 'decimal:2',
        'usage_fee_per_student' => 'decimal:2',
        'total_due'             => 'decimal:2',
        'paid_at'               => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function marketplaceComponent()
    {
        return $this->belongsTo(MarketplaceComponent::class);
    }

    public function getFormattedTotalDueAttribute(): string
    {
        return config('myacademy.currency_symbol', '₦') . number_format($this->total_due, 2);
    }
}
